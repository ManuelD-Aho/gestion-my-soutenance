<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CleanupTempFiles extends Command
{
    protected $signature = 'cleanup:temp-files {--hours=24 : Nombre d\'heures après lesquelles supprimer les fichiers temporaires}';

    protected $description = 'Supprime les fichiers temporaires générés par le système.';

    public function handle(): int
    {
        try {
            $hours = (int) $this->option('hours');
            $directories = [
                'temp_imports', // Assumer un répertoire pour les imports temporaires
                'temp_exports', // Assumer un répertoire pour les exports temporaires
                'private/pvs', // Si des versions temporaires y sont stockées
                'private/reports', // Si des versions temporaires y sont stockées
                // Ajoutez d'autres répertoires temporaires si nécessaire
            ];

            $deletedCount = 0;
            $cutoffTimestamp = now()->subHours($hours)->timestamp;

            foreach ($directories as $directory) {
                if (Storage::exists($directory)) {
                    foreach (Storage::files($directory) as $file) {
                        if (Storage::lastModified($file) < $cutoffTimestamp) {
                            Storage::delete($file);
                            $deletedCount++;
                        }
                    }
                }
            }

            $this->info("{$deletedCount} fichiers temporaires supprimés.");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Erreur lors du nettoyage des fichiers temporaires: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
