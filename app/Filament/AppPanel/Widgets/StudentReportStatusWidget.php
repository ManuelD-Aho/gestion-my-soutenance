<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Widgets;

use App\Enums\ReportStatusEnum;
use App\Models\AcademicYear;
use App\Models\Report;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class StudentReportStatusWidget extends Widget
{
    protected static string $view = 'filament.app-panel.widgets.student-report-status-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getReportStatusData(): array
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student) {
            return [
                'status' => 'Non applicable',
                'message' => 'Votre profil étudiant n\'est pas lié ou actif.',
                'current_step' => 0,
                'total_steps' => 0,
                'report' => null,
                'timeline' => [],
            ];
        }

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        if (! $activeAcademicYear) {
            return [
                'status' => 'Non applicable',
                'message' => 'Aucune année académique active configurée.',
                'current_step' => 0,
                'total_steps' => 0,
                'report' => null,
                'timeline' => [],
            ];
        }

        $report = Report::where('student_id', $student->id)
            ->where('academic_year_id', $activeAcademicYear->id)
            ->orderByDesc('created_at')
            ->first();

        $timeline = [
            ['label' => 'Brouillon', 'status' => ReportStatusEnum::DRAFT, 'date' => null, 'is_current' => false, 'is_completed' => false],
            ['label' => 'Soumis', 'status' => ReportStatusEnum::SUBMITTED, 'date' => null, 'is_current' => false, 'is_completed' => false],
            ['label' => 'Vérification Conformité', 'status' => ReportStatusEnum::IN_CONFORMITY_CHECK, 'date' => null, 'is_current' => false, 'is_completed' => false],
            ['label' => 'En Commission', 'status' => ReportStatusEnum::IN_COMMISSION_REVIEW, 'date' => null, 'is_current' => false, 'is_completed' => false],
            ['label' => 'Validé', 'status' => ReportStatusEnum::VALIDATED, 'date' => null, 'is_current' => false, 'is_completed' => false],
            ['label' => 'Refusé', 'status' => ReportStatusEnum::REJECTED, 'date' => null, 'is_current' => false, 'is_completed' => false],
        ];

        $currentStepIndex = 0;
        $totalSteps = count($timeline);
        $statusMessage = 'Aucun rapport en cours pour l\'année académique '.$activeAcademicYear->label.'.';

        if ($report) {
            $statusMessage = 'Votre rapport est actuellement en statut : '.$report->status->value;

            foreach ($timeline as $index => &$step) {
                $step['is_completed'] = false;
                $step['is_current'] = false;

                if ($report->status->value === $step['status']->value) {
                    $step['is_current'] = true;
                    $currentStepIndex = $index;
                    $step['date'] = $report->last_modified_date; // Ou submission_date pour 'Soumis'
                }

                // Mark previous steps as completed
                if ($index < $currentStepIndex) {
                    $step['is_completed'] = true;
                    // Try to get actual date from audit logs or specific report fields
                    if ($step['status'] === ReportStatusEnum::SUBMITTED) {
                        $step['date'] = $report->submission_date;
                    } elseif ($step['status'] === ReportStatusEnum::DRAFT) {
                        $step['date'] = $report->created_at;
                    } else {
                        // For other statuses, you might need to query audit logs
                        // This is a simplified approach
                        $step['date'] = $report->last_modified_date;
                    }
                }
            }

            // Handle specific statuses that are "end states" or "re-entry points"
            if ($report->status === ReportStatusEnum::NEEDS_CORRECTION) {
                $statusMessage = 'Votre rapport nécessite des corrections. Veuillez le modifier et le re-soumettre.';
                // Find the 'Soumis' step and mark it as completed, then 'Nécessite Correction' as current
                foreach ($timeline as $index => &$step) {
                    if ($step['status'] === ReportStatusEnum::SUBMITTED) {
                        $step['is_completed'] = true;
                        $step['date'] = $report->submission_date;
                    }
                    if ($step['status'] === ReportStatusEnum::NEEDS_CORRECTION) {
                        $step['is_current'] = true;
                        $currentStepIndex = $index;
                        $step['date'] = $report->last_modified_date;
                    }
                }
            } elseif ($report->status === ReportStatusEnum::REJECTED) {
                $statusMessage = 'Votre rapport a été refusé. Veuillez consulter les détails.';
                // Mark all previous steps as completed, and REJECTED as current
                foreach ($timeline as $index => &$step) {
                    $step['is_completed'] = true;
                    if ($step['status'] === ReportStatusEnum::REJECTED) {
                        $step['is_current'] = true;
                        $currentStepIndex = $index;
                        $step['date'] = $report->last_modified_date;
                    }
                }
            } elseif ($report->status === ReportStatusEnum::ARCHIVED) {
                $statusMessage = 'Votre rapport a été archivé.';
                // Mark all steps as completed, or indicate it's an end state
                foreach ($timeline as $index => &$step) {
                    $step['is_completed'] = true;
                    $step['date'] = $report->last_modified_date;
                }
            }
        } else {
            $statusMessage = "Vous n'avez pas encore de rapport en cours pour l'année académique ".$activeAcademicYear->label.'.';
            $timeline[0]['is_current'] = true; // Draft is the implicit first step
        }

        return [
            'status' => $report ? $report->status->value : 'Aucun rapport',
            'message' => $statusMessage,
            'current_step' => $currentStepIndex,
            'total_steps' => $totalSteps,
            'report' => $report,
            'timeline' => $timeline,
        ];
    }
}
