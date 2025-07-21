<?php

namespace App\Console\Commands\Penalties;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Services\PenaltyService;
use Illuminate\Console\Command;
use Throwable;

class ApplyLateSubmission extends Command
{
    protected $signature = 'penalties:apply-late-submission';
    protected $description = 'Applique les pénalités pour les rapports non soumis à temps.';

    public function __construct(protected PenaltyService $penaltyService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $activeYear = AcademicYear::where('is_active', true)->first();
            if (!$activeYear || !$activeYear->report_submission_deadline) {
                $this->error('Aucune année académique active ou date limite de soumission non définie.');
                return Command::FAILURE;
            }

            if (now()->lt($activeYear->report_submission_deadline)) {
                $this->info('La date limite de soumission n\'est pas encore passée.');
                return Command::SUCCESS;
            }

            $lateStudents = Student::whereHas('enrollments', fn($q) => $q->where('academic_year_id', $activeYear->id))
                                   ->whereDoesntHave('reports', fn($q) => $q->where('academic_year_id', $activeYear->id)
                                                                            ->where('submission_date', '<=', $activeYear->report_submission_deadline))
                                   ->get();

            $appliedCount = 0;
            foreach ($lateStudents as $student) {
                try {
                    // Vérifier si une pénalité de retard pour cette année existe déjà pour éviter les doublons
                    $existingPenalty = $student->penalties()
                                               ->where('academic_year_id', $activeYear->id)
                                               ->where('type', 'RETARD_SOUMISSION_RAPPORT')
                                               ->where('status', \App\Enums\PenaltyStatusEnum::DUE) // Seules les dues comptent
                                               ->first();

                    if (!$existingPenalty) {
                        $this->penaltyService->applyPenalty(
                            $student,
                            'Financière', // Ou 'Administrative', configurable
                            'Retard de soumission du rapport de soutenance pour l\'année académique ' . $activeYear->label,
                            config('app.late_submission_penalty_amount', 10000) // Montant configurable
                        );
                        $appliedCount++;
                    }
                } catch (Throwable $e) {
                    $this->error("Échec d'application de pénalité pour l'étudiant {$student->student_card_number}: {$e->getMessage()}");
                }
            }

            $this->info("Pénalités de retard appliquées à {$appliedCount} étudiants.");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Erreur critique lors de l'application des pénalités: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}