<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Stringable;

// Assumer l'existence d'une notification pour les échecs de cron
// use App\Notifications\CronJobFailed;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Application Automatique des Pénalités de Retard
        $schedule->command('penalties:apply-late-submission')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->onFailure(function (Stringable $output) {
                Log::error("Tâche planifiée 'penalties:apply-late-submission' a échoué: {$output}");
                // Notification::route('mail', config('app.admin_email'))->notify(new CronJobFailed('penalties:apply-late-submission', $output));
            })
            ->pingOnSuccess(env('HEALTHCHECKS_IO_PENALTY_URL'));

        // Archivage des Anciennes Données
        $schedule->command('archive:old-data')
            ->monthlyOn(1, '02:00')
            ->onSuccess(function () {
                Log::info("Tâche planifiée 'archive:old-data' exécutée avec succès.");
            })
            ->onFailure(function (Stringable $output) {
                Log::error("Tâche planifiée 'archive:old-data' a échoué: {$output}");
                // Notification::route('mail', config('app.admin_email'))->notify(new CronJobFailed('archive:old-data', $output));
            });

        // Nettoyage des Fichiers Temporaires
        $schedule->command('cleanup:temp-files')->dailyAt('03:00');

        // Purge des Sessions Expirées
        $schedule->command('session:flush')->daily();

        // Purge des Jobs Échoués
        $schedule->command('queue:prune-failed --hours=720')->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
