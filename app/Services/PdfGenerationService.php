<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Ou Spatie\LaravelPdf\Facades\Pdf si configuré
use Throwable;

class PdfGenerationService
{
    protected UniqueIdGeneratorService $uniqueIdGeneratorService;

    protected AuditService $auditService;

    public function __construct(
        UniqueIdGeneratorService $uniqueIdGeneratorService,
        AuditService $auditService
    ) {
        $this->uniqueIdGeneratorService = $uniqueIdGeneratorService;
        $this->auditService = $auditService;
    }

    public function generateAndRegisterDocument(
        string $viewName,
        array $data,
        string $documentType,
        Model $relatedEntity,
        User $generatedBy
    ): Document {
        try {
            return DB::transaction(function () use ($viewName, $data, $documentType, $relatedEntity, $generatedBy) {
                $storagePathConfig = $this->getStoragePathForDocumentType($documentType);
                $filename = sprintf('%s_%s_%s.pdf', Str::slug($documentType), $relatedEntity->getKey(), Str::random(8));
                $fullStoragePath = $storagePathConfig.'/'.$filename;

                $pdfContent = Pdf::loadView($viewName, $data)->output();
                $fileHash = hash('sha256', $pdfContent);

                Storage::put($fullStoragePath, $pdfContent);

                $documentTypeModel = DocumentType::where('name', $documentType)->firstOrFail();

                $document = Document::create([
                    'document_id' => $this->uniqueIdGeneratorService->generate('DOC', (int) date('Y')),
                    'document_type_id' => $documentTypeModel->id,
                    'file_path' => $fullStoragePath,
                    'file_hash' => $fileHash,
                    'generation_date' => now(),
                    'version' => 1,
                    'related_entity_type' => get_class($relatedEntity),
                    'related_entity_id' => $relatedEntity->getKey(),
                    'generated_by_user_id' => $generatedBy->id,
                ]);

                $this->auditService->logAction('DOCUMENT_GENERATED', $document, [
                    'document_id' => $document->document_id,
                    'document_type' => $documentType,
                    'related_entity' => get_class($relatedEntity).':'.$relatedEntity->getKey(),
                    'generated_by' => $generatedBy->email,
                ]);

                return $document;
            });
        } catch (Throwable $e) {
            Log::error("PdfGenerationService: Failed to generate and register document for {$documentType}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function getStoragePathForDocumentType(string $documentType): string
    {
        return match ($documentType) {
            'PV' => 'private/pvs',
            'BULLETIN' => 'public/bulletins',
            'ATTESTATION' => 'public/attestations',
            'RECU' => 'private/recus',
            'RAPPORT' => 'private/reports',
            default => throw new \InvalidArgumentException("Type de document non configuré pour le stockage: {$documentType}"),
        };
    }
}
