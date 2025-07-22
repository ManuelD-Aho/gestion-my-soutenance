<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReportStatusEnum;
use App\Exceptions\IncompleteSubmissionException;
use App\Exceptions\StateConflictException;
use App\Models\Report;
use App\Models\ReportSection;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReportFlowService
{
    protected UniqueIdGeneratorService $uniqueIdGeneratorService;

    protected AuditService $auditService;

    protected NotificationService $notificationService;

    public function __construct(
        UniqueIdGeneratorService $uniqueIdGeneratorService,
        AuditService $auditService,
        NotificationService $notificationService
    ) {
        $this->uniqueIdGeneratorService = $uniqueIdGeneratorService;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function submitReport(Report $report, array $contentData, int $expectedVersion): void
    {
        try {
            DB::transaction(function () use ($report, $contentData, $expectedVersion) {
                $reportFresh = Report::find($report->id);

                if ($reportFresh->version !== $expectedVersion) {
                    throw new StateConflictException(
                        'Le statut ou le contenu du rapport a été modifié par un autre utilisateur. Veuillez rafraîchir la page et réessayer.'
                    );
                }

                if ($reportFresh->status !== ReportStatusEnum::DRAFT && $reportFresh->status !== ReportStatusEnum::NEEDS_CORRECTION) {
                    throw new \InvalidArgumentException("Le rapport ne peut être soumis qu'en statut Brouillon ou Nécessite Correction.");
                }

                $this->validateReportCompleteness($reportFresh, $contentData);

                if (empty($reportFresh->report_id)) {
                    $reportFresh->report_id = $this->uniqueIdGeneratorService->generate('RAP', (int) date('Y'));
                }

                $reportFresh->status = ReportStatusEnum::SUBMITTED;
                $reportFresh->submission_date = now();
                $reportFresh->last_modified_date = now();
                $reportFresh->version++;
                $reportFresh->save();

                $existingSectionIds = $reportFresh->sections->pluck('id');
                $submittedSectionIds = [];
                foreach ($contentData as $sectionData) {
                    $section = ReportSection::updateOrCreate(
                        ['report_id' => $reportFresh->id, 'title' => $sectionData['title']],
                        ['content' => $sectionData['content'], 'order' => $sectionData['order'] ?? 0]
                    );
                    $submittedSectionIds[] = $section->id;
                }

                $sectionsToDelete = $existingSectionIds->diff($submittedSectionIds);
                ReportSection::whereIn('id', $sectionsToDelete)->delete();

                $this->auditService->logAction('REPORT_SUBMITTED', $reportFresh, ['report_id' => $reportFresh->report_id, 'student_id' => $reportFresh->student->id]);

                $this->notificationService->processNotificationRules('REPORT_SUBMITTED', $reportFresh, ['report_title' => $reportFresh->title]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function updateReportStatus(Report $report, ReportStatusEnum $newStatus, User $actor, ?string $reason = null, ?int $expectedVersion = null): void
    {
        try {
            DB::transaction(function () use ($report, $newStatus, $actor, $reason, $expectedVersion) {
                $oldStatus = $report->status;

                $reportFresh = Report::find($report->id);
                if ($expectedVersion !== null && $reportFresh->version !== $expectedVersion) {
                    throw new StateConflictException(
                        'Le statut du rapport a été modifié par un autre utilisateur. Veuillez rafraîchir la page.'
                    );
                }

                if ($actor->cannot('updateStatus', [$reportFresh, $newStatus])) {
                    throw new AuthorizationException("L'utilisateur n'est pas autorisé à effectuer cette transition de statut.");
                }

                if (! $this->isValidTransition($oldStatus, $newStatus)) {
                    throw new \InvalidArgumentException("Transition de statut invalide de {$oldStatus->value} à {$newStatus->value}");
                }

                if (($newStatus === ReportStatusEnum::NEEDS_CORRECTION || $newStatus === ReportStatusEnum::REJECTED) && (empty($reason))) {
                    throw new \InvalidArgumentException("Une raison est obligatoire pour le statut {$newStatus->value}.");
                }

                $reportFresh->status = $newStatus;
                $reportFresh->last_modified_date = now();
                $reportFresh->version++;
                $reportFresh->save();

                $this->auditService->logAction('REPORT_STATUS_UPDATED', $reportFresh, [
                    'old_status' => $oldStatus->value,
                    'new_status' => $newStatus->value,
                    'reason' => $reason,
                    'actor_id' => $actor->id,
                ]);

                $this->notificationService->processNotificationRules('REPORT_STATUS_UPDATED', $reportFresh, [
                    'old_status' => $oldStatus->value,
                    'new_status' => $newStatus->value,
                    'reason' => $reason,
                ]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function isValidTransition(ReportStatusEnum $oldStatus, ReportStatusEnum $newStatus): bool
    {
        $transitions = [
            ReportStatusEnum::DRAFT->value => [ReportStatusEnum::SUBMITTED],
            ReportStatusEnum::SUBMITTED->value => [ReportStatusEnum::IN_CONFORMITY_CHECK, ReportStatusEnum::NEEDS_CORRECTION],
            ReportStatusEnum::NEEDS_CORRECTION->value => [ReportStatusEnum::SUBMITTED, ReportStatusEnum::ARCHIVED],
            ReportStatusEnum::IN_CONFORMITY_CHECK->value => [ReportStatusEnum::IN_COMMISSION_REVIEW, ReportStatusEnum::NEEDS_CORRECTION],
            ReportStatusEnum::IN_COMMISSION_REVIEW->value => [ReportStatusEnum::VALIDATED, ReportStatusEnum::REJECTED, ReportStatusEnum::NEEDS_CORRECTION],
            ReportStatusEnum::VALIDATED->value => [ReportStatusEnum::ARCHIVED],
            ReportStatusEnum::REJECTED->value => [ReportStatusEnum::ARCHIVED],
            ReportStatusEnum::ARCHIVED->value => [],
        ];

        return in_array($newStatus, $transitions[$oldStatus->value] ?? [], true);
    }

    public function returnForCorrection(Report $report, string $comments, User $agent): void
    {
        if (empty($comments)) {
            throw new \InvalidArgumentException("Les commentaires sont obligatoires pour retourner un rapport en correction.");
        }

        $this->updateReportStatus($report, ReportStatusEnum::NEEDS_CORRECTION, $agent, $comments);

        $this->notificationService->sendEmail(
            \App\Mail\ReportNeedsCorrectionMail::class,
            $report->student->user,
            ['report' => $report, 'comments' => $comments]
        );

        $this->auditService->logAction("REPORT_RETURNED_FOR_CORRECTION", $report, ['report_id' => $report->report_id, 'comments' => $comments, 'agent_id' => $agent->id]);
    }

    private function validateReportCompleteness(Report $report, array $contentData): void
    {
        $template = ReportTemplate::find($report->report_template_id);
        if ($template) {
            $mandatorySections = $template->sections()->where('is_mandatory', true)->pluck('title')->toArray();
            $submittedSectionTitles = collect($contentData)->pluck('title')->toArray();

            $missingSections = array_diff($mandatorySections, $submittedSectionTitles);
            if (! empty($missingSections)) {
                throw new IncompleteSubmissionException(
                    'Soumission impossible. Les sections obligatoires suivantes sont manquantes : '.implode(', ', $missingSections)
                );
            }
        }

        foreach ($contentData as $section) {
            if (empty($section['content']) || trim(strip_tags($section['content'])) === '') {
                throw new IncompleteSubmissionException(
                    "Soumission impossible. La section '{$section['title']}' ne peut pas être vide."
                );
            }
        }
    }
}