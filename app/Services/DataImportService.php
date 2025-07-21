<?php

namespace App\Services;

use App\Imports\ReportsImport;
use App\Imports\StudentsImport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class DataImportService
{
    protected UserManagementService $userManagementService;
    protected AuditService $auditService;
    protected NotificationService $notificationService;

    public function __construct(
        UserManagementService $userManagementService,
        AuditService $auditService,
        NotificationService $notificationService
    ) {
        $this->userManagementService = $userManagementService;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function processImport(string $filePath, string $entityType, array $mapping, User $importer): array
    {
        try {
            $fullFilePath = Storage::path($filePath);

            // Instancier l'importer pour la pré-lecture
            $tempImportInstance = $this->getImportClassForEntityType($entityType);
            $collection = Excel::toCollection(new $tempImportInstance($mapping, $importer), $fullFilePath)->first();

            // Assurez-vous que la collection n'est pas nulle avant d'appeler count()
            $rowCount = $collection ? ($collection->count() - 1) : 0; // -1 pour l'en-tête

            $asyncThreshold = config('app.import_async_threshold', 100);

            if ($rowCount > $asyncThreshold) {
                \App\Jobs\ProcessDataImportJob::dispatch($filePath, $entityType, $mapping, $importer->id);
                $this->auditService->logAction("IMPORT_INITIATED_ASYNC", $importer, ['file' => basename($filePath), 'entity_type' => $entityType, 'rows_count' => $rowCount]);
                return ['status' => 'pending', 'message' => "L'importation de {$rowCount} lignes a été lancée en arrière-plan. Vous serez notifié à la fin."];
            } else {
                $successCount = 0;
                $failedCount = 0;
                $errorsDetails = [];

                $importClass = $this->getImportClassForEntityType($entityType);
                $importInstance = new $importClass($mapping, $importer);

                DB::transaction(function () use ($importInstance, $fullFilePath, &$successCount, &$failedCount, &$errorsDetails) {
                    Excel::import($importInstance, $fullFilePath);

                    $results = $importInstance->getResults();
                    $successCount = $results['success'];
                    $failedCount = $results['failed'];
                    $errorsDetails = $results['errors'];
                });

                Storage::delete($filePath);

                $this->auditService->logAction("IMPORT_COMPLETED_SYNC", $importer, ['file' => basename($filePath), 'entity_type' => $entityType, 'success_count' => $successCount, 'failed_count' => $failedCount, 'errors_details' => $errorsDetails]);
                $this->notificationService->sendInternalNotification("IMPORT_COMPLETED", $importer, ['entity_type' => $entityType, 'success_count' => $successCount, 'failed_count' => $failedCount, 'file_name' => basename($filePath)]);

                return ['status' => 'completed', 'success_count' => $successCount, 'failed_count' => $failedCount, 'errors' => $errorsDetails];
            }
        } catch (Throwable $e) {
            Log::error("DataImportService: Échec de l'opération d'importation pour le fichier {$filePath}: {$e->getMessage()}");
            Storage::delete($filePath);
            throw $e;
        }
    }

    private function getImportClassForEntityType(string $entityType): string
    {
        return match ($entityType) {
            'student' => StudentsImport::class,
            'report' => ReportsImport::class,
            default => throw new \InvalidArgumentException("Classe d'importation non définie pour le type d'entité: {$entityType}"),
        };
    }
}
