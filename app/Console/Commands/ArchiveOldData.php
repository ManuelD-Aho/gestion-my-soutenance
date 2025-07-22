<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PvStatusEnum;
use App\Enums\UserAccountStatusEnum;
use App\Models\Pv;
use App\Models\Report;
use App\Models\User;
// <-- AJOUTER CETTE LIGNE
use App\Services\UserManagementService; // <-- AJOUTER CETTE LIGNE (pour updateUserStatus)
use Illuminate\Console\Command;
use Throwable;

class ArchiveOldData extends Command
{
    protected $signature = 'archive:old-data {--years=5 : Nombre d\'années après lesquelles archiver les données}';

    protected $description = 'Archive les anciennes données (rapports, PVs) pour améliorer les performances.';

    public function __construct(protected UserManagementService $userManagementService)
    {
        parent::__construct();
    }

    /**
     * Exécute la commande d'archivage des données.
     */
    public function handle(): int
    {
        try {
            $yearsToArchive = (int) $this->option('years');
            $archiveDate = now()->subYears($yearsToArchive);

            $this->info("Archivage des données antérieures à {$archiveDate->format('Y-m-d')}.");

            // Archivage des rapports
            $reportsArchived = 0;
            Report::where('submission_date', '<', $archiveDate)
                ->where('status', '!=', \App\Enums\ReportStatusEnum::ARCHIVED)
                ->chunkById(200, function ($reports) use (&$reportsArchived) {
                    foreach ($reports as $report) {
                        try {
                            $report->status = \App\Enums\ReportStatusEnum::ARCHIVED;
                            $report->save();
                            $reportsArchived++;
                        } catch (Throwable $e) {
                            $this->warn("Échec d'archivage du rapport {$report->report_id}: {$e->getMessage()}");
                        }
                    }
                });
            $this->info("{$reportsArchived} rapports archivés.");

            // Archivage des PVs
            $pvsArchived = 0;
            Pv::where('created_at', '<', $archiveDate)
                ->where('status', '!=', PvStatusEnum::ARCHIVED) // Assumer un statut ARCHIVED pour les PVs
                ->chunkById(200, function ($pvs) use (&$pvsArchived) {
                    foreach ($pvs as $pv) {
                        try {
                            $pv->status = PvStatusEnum::ARCHIVED;
                            $pv->save();
                            $pvsArchived++;
                        } catch (Throwable $e) {
                            $this->warn("Échec d'archivage du PV {$pv->pv_id}: {$e->getMessage()}");
                        }
                    }
                });
            $this->info("{$pvsArchived} PVs archivés.");

            // Optionnel: Archivage des comptes utilisateurs inactifs depuis longtemps
            $usersArchived = 0;
            User::where('last_activity_at', '<', $archiveDate) // Assumer une colonne last_activity_at
                ->where('status', '!=', UserAccountStatusEnum::ARCHIVED)
                ->chunkById(200, function ($users) use (&$usersArchived) {
                    foreach ($users as $user) {
                        try {
                            // Utiliser le service UserManagementService pour archiver le compte
                            $this->userManagementService->updateUserStatus($user, UserAccountStatusEnum::ARCHIVED, 'Archivage automatique car inactif depuis '.$user->last_activity_at->format('Y-m-d'));
                            $usersArchived++;
                        } catch (Throwable $e) {
                            $this->warn("Échec d'archivage de l'utilisateur {$user->email}: {$e->getMessage()}");
                        }
                    }
                });
            $this->info("{$usersArchived} utilisateurs archivés.");

            $this->info('Archivage des données terminé.');

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Erreur critique lors de l'archivage: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
