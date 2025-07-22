<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ConformityStatusEnum;
use App\Enums\ReportStatusEnum;
use App\Models\ConformityCheckDetail;
use App\Models\ConformityCriterion;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class ConformityCheckService
{
    protected ReportFlowService $reportFlowService;

    protected AuditService $auditService;

    protected NotificationService $notificationService;

    protected PenaltyService $penaltyService; // Ajouté pour les vérifications automatiques avec pénalité

    public function __construct(
        ReportFlowService $reportFlowService,
        AuditService $auditService,
        NotificationService $notificationService,
        PenaltyService $penaltyService
    ) {
        $this->reportFlowService = $reportFlowService;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
        $this->penaltyService = $penaltyService;
    }

    public function checkConformity(Report $report, array $criteriaResults, User $agent): void
    {
        if ($report->status !== ReportStatusEnum::SUBMITTED && $report->status !== ReportStatusEnum::IN_CONFORMITY_CHECK) {
            throw new \InvalidArgumentException("Le rapport n'est pas dans un statut permettant la vérification de conformité.");
        }

        try {
            DB::transaction(function () use ($report, $criteriaResults, $agent) {
                $isGloballyConforme = true;
                $commentsForStudent = [];

                // Exécuter d'abord les vérifications automatiques
                $automaticCheckResults = $this->runAutomaticChecks($report);
                foreach ($automaticCheckResults as $criterionId => $result) {
                    $criteriaResults[$criterionId] = $result; // Intégrer les résultats automatiques
                }

                foreach ($criteriaResults as $criterionId => $resultData) {
                    $criterion = ConformityCriterion::find($criterionId);
                    if (! $criterion) {
                        throw new \InvalidArgumentException("Critère de conformité inconnu: {$criterionId}.");
                    }

                    ConformityCheckDetail::create([
                        'report_id' => $report->id,
                        'conformity_criterion_id' => $criterion->id,
                        'validation_status' => ConformityStatusEnum::from($resultData['status']),
                        'comment' => $resultData['comment'] ?? null,
                        'verification_date' => now(),
                        'verified_by_user_id' => $agent->id,
                        'criterion_label' => $criterion->label, // Snapshot
                        'criterion_description' => $criterion->description, // Snapshot
                        'criterion_version' => $criterion->version, // Snapshot
                    ]);

                    if (ConformityStatusEnum::from($resultData['status']) === ConformityStatusEnum::NON_CONFORME) {
                        $isGloballyConforme = false;
                        if (! empty($resultData['comment'])) {
                            $commentsForStudent[] = "{$criterion->label}: {$resultData['comment']}";
                        }
                    }
                }

                if ($isGloballyConforme) {
                    $this->reportFlowService->updateReportStatus($report, ReportStatusEnum::IN_COMMISSION_REVIEW, $agent, 'Rapport jugé conforme.');
                    $this->notificationService->processNotificationRules('REPORT_CONFORME_A_EVALUER', $report, ['report_title' => $report->title]);
                } else {
                    $aggregatedComments = "Votre rapport nécessite les corrections suivantes:\n".implode("\n", $commentsForStudent);
                    $this->reportFlowService->updateReportStatus($report, ReportStatusEnum::NEEDS_CORRECTION, $agent, $aggregatedComments);
                    $this->notificationService->processNotificationRules('CORRECTIONS_REQUISES', $report, ['report_title' => $report->title, 'comments' => $aggregatedComments]);
                }

                $this->auditService->logAction('REPORT_CONFORMITY_CHECKED', $report, [
                    'agent_id' => $agent->id,
                    'overall_status' => $isGloballyConforme ? 'Conforme' : 'Non Conforme',
                    'details' => $criteriaResults,
                ]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function getConformityChecklist(): \Illuminate\Database\Eloquent\Collection
    {
        return ConformityCriterion::where('is_active', true)->where('type', 'MANUAL')->orderBy('label')->get();
    }

    private function runAutomaticChecks(Report $report): array
    {
        $automaticResults = [];
        $automaticCriteria = ConformityCriterion::where('is_active', true)->where('type', 'AUTOMATIC')->get();

        foreach ($automaticCriteria as $criterion) {
            $status = ConformityStatusEnum::CONFORME;
            $comment = null;

            switch ($criterion->code) { // Assumer un champ 'code' sur ConformityCriterion
                case 'DEADLINE_RESPECTED':
                    $deadline = $report->academicYear->report_submission_deadline; // Récupérer la date limite de l'année académique
                    if ($report->submission_date && $report->submission_date->gt($deadline)) {
                        $status = ConformityStatusEnum::NON_CONFORME;
                        $comment = "Le rapport a été soumis après la date limite du {$deadline->format('d/m/Y')}.";
                        // Appliquer une pénalité automatique si non déjà fait
                        $this->penaltyService->applyPenalty(
                            $report->student,
                            'Financière',
                            "Pénalité pour soumission tardive du rapport ID: {$report->report_id}",
                            config('app.late_submission_penalty_amount', 10000)
                        );
                    }
                    break;
                case 'MIN_PAGE_COUNT':
                    $minPages = config('app.report_min_pages', 30); // Paramètre système
                    if ($report->page_count < $minPages) {
                        $status = ConformityStatusEnum::NON_CONFORME;
                        $comment = "Le rapport a moins de {$minPages} pages ({$report->page_count} pages).";
                    }
                    break;
                    // Ajouter d'autres cas pour les vérifications automatiques (ex: détection de plagiat via API externe)
                    // case 'PLAGIARISM_CHECK':
                    //     $plagiarismScore = $this->plagiarismService->check($report->content);
                    //     if ($plagiarismScore > config('app.plagiarism_threshold')) {
                    //         $status = ConformityStatusEnum::NON_CONFORME;
                    //         $comment = "Taux de plagiat détecté de {$plagiarismScore}%.";
                    //     }
                    //     break;
            }

            $automaticResults[$criterion->id] = [
                'status' => $status->value,
                'comment' => $comment,
            ];
        }

        return $automaticResults;
    }
}
