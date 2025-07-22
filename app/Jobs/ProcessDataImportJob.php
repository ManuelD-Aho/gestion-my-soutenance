<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Imports\ReportsImport; // Peut être généralisé avec un paramètre $importClass
use App\Imports\StudentsImport;
use App\Models\User; // Ajouter
use App\Services\NotificationService; // Ajouter
use Illuminate\Bus\Queueable; // Ajouter si ReportsImport est dynamiquement appelé
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessDataImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300; // 5 minutes de timeout

    public function __construct(
        protected string $filePath,
        protected string $entityType,
        protected array $mapping,
        protected int $importerId
    ) {
        $this->onQueue('imports'); // Assigner ce job à la queue 'imports'
    }

    public function handle(NotificationService $notificationService): void
    {
        $importer = User::find($this->importerId);
        if (! $importer) {
            Log::error("ProcessDataImportJob: Importer user (ID: {$this->importerId}) not found.");

            return;
        }

        try {
            // Déterminer la classe d'importation basée sur entityType (peut être plus sophistiqué)
            $importClass = match ($this->entityType) {
                'student' => StudentsImport::class,
                'report' => ReportsImport::class,
                default => throw new \InvalidArgumentException("Import class not defined for entity type: {$this->entityType}"),
            };

            $importInstance = new $importClass($this->mapping, $importer);
            Excel::import($importInstance, storage_path('app/'.$this->filePath)); // Le chemin est relatif à storage/app

            $results = $importInstance->getResults();

            $notificationService->sendInternalNotification(
                'IMPORT_COMPLETED',
                $importer,
                [
                    'entity_type' => $this->entityType,
                    'success_count' => $results['success'],
                    'failed_count' => $results['failed'],
                    'file_name' => basename($this->filePath),
                ]
            );

            // Optionnel: Générer un rapport d'erreurs téléchargeable si $results['errors'] non vide
            // et envoyer un lien dans la notification.

        } catch (Throwable $e) {
            Log::error("ProcessDataImportJob: Échec de l'importation pour le fichier {$this->filePath}: {$e->getMessage()}");
            $notificationService->sendInternalNotification(
                'IMPORT_FAILED',
                $importer,
                [
                    'entity_type' => $this->entityType,
                    'file_name' => basename($this->filePath),
                    'error_message' => $e->getMessage(),
                ]
            );
            throw $e; // Ré-lancer l'exception pour que Laravel marque le job comme échoué
        } finally {
            // Nettoyer le fichier temporaire après traitement
            Storage::delete($this->filePath);
        }
    }
}
