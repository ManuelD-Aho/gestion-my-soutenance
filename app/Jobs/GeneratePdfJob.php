<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
// Ajouter
use App\Services\NotificationService;
use App\Services\PdfGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // Un PDF qui échoue est souvent un problème de contenu ou de moteur, pas de transient error

    public int $timeout = 180; // 3 minutes de timeout pour les PDFs complexes

    public function __construct(
        protected string $viewName,
        protected array $data,
        protected string $documentType,
        protected \Illuminate\Database\Eloquent\Model $relatedEntity,
        protected int $generatedById,
        protected string $filename // Nom de fichier souhaité
    ) {
        $this->onQueue('pdfs'); // Assigner ce job à la queue 'pdfs'
    }

    public function handle(PdfGenerationService $pdfGenerationService, NotificationService $notificationService): void
    {
        $generatedBy = User::find($this->generatedById);
        if (! $generatedBy) {
            Log::error("GeneratePdfJob: User generating PDF (ID: {$this->generatedById}) not found.");

            return;
        }

        try {
            $document = $pdfGenerationService->generateAndRegisterDocument(
                $this->viewName,
                $this->data,
                $this->documentType,
                $this->relatedEntity,
                $generatedBy
            );

            $notificationService->sendInternalNotification(
                'DOCUMENT_GENERATED_SUCCESS',
                $generatedBy,
                [
                    'document_type' => $this->documentType,
                    'file_name' => $document->file_path,
                    'related_entity_id' => $this->relatedEntity->getKey(),
                ]
            );

        } catch (Throwable $e) {
            Log::error("GeneratePdfJob: Échec de la génération PDF pour {$this->documentType} (ID: {$this->relatedEntity->getKey()}): {$e->getMessage()}");
            $notificationService->sendInternalNotification(
                'DOCUMENT_GENERATED_FAILED',
                $generatedBy,
                [
                    'document_type' => $this->documentType,
                    'related_entity_id' => $this->relatedEntity->getKey(),
                    'error_message' => $e->getMessage(),
                ]
            );
            throw $e; // Ré-lancer l'exception pour que Laravel marque le job comme échoué
        }
    }
}
