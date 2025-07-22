<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CommissionSessionModeEnum;
use App\Enums\CommissionSessionStatusEnum;
use App\Enums\PvApprovalDecisionEnum;
use App\Enums\PvStatusEnum;
use App\Enums\ReportStatusEnum;
use App\Enums\VoteDecisionEnum;
use App\Exceptions\CommissionException;
use App\Models\CommissionSession;
use App\Models\Pv;
use App\Models\Report;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommissionFlowService
{
    protected ReportFlowService $reportFlowService;

    protected AuditService $auditService;

    protected NotificationService $notificationService;

    protected PdfGenerationService $pdfGenerationService;

    protected UniqueIdGeneratorService $uniqueIdGeneratorService;

    public function __construct(
        ReportFlowService $reportFlowService,
        AuditService $auditService,
        NotificationService $notificationService,
        PdfGenerationService $pdfGenerationService,
        UniqueIdGeneratorService $uniqueIdGeneratorService
    ) {
        $this->reportFlowService = $reportFlowService;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
        $this->pdfGenerationService = $pdfGenerationService;
        $this->uniqueIdGeneratorService = $uniqueIdGeneratorService;
    }

    public function createSession(array $data, User $presidentUser): CommissionSession
    {
        if (! $presidentUser->hasRole('President Commission')) {
            throw new AuthorizationException('Seul un Président de Commission peut créer une session.');
        }

        $presidentTeacher = $presidentUser->teacher;
        if (! $presidentTeacher) {
            throw new \InvalidArgumentException("L'utilisateur président n'est pas lié à un profil enseignant.");
        }

        try {
            return DB::transaction(function () use ($data, $presidentTeacher) {
                $session = CommissionSession::create([
                    'session_id' => $this->uniqueIdGeneratorService->generate('SESS', (int) date('Y')),
                    'name' => $data['name'],
                    'start_date' => $data['start_date'],
                    'end_date_planned' => $data['end_date_planned'],
                    'president_teacher_id' => $presidentTeacher->id,
                    'mode' => CommissionSessionModeEnum::from($data['mode']),
                    'status' => CommissionSessionStatusEnum::PLANNED,
                    'required_voters_count' => $data['required_voters_count'] ?? 1,
                ]);

                $this->auditService->logAction('COMMISSION_SESSION_CREATED', $session, ['session_id' => $session->session_id, 'president_id' => $presidentTeacher->id]);

                return $session;
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function addReportToSession(CommissionSession $session, Report $report): void
    {
        try {
            DB::transaction(function () use ($session, $report) {
                if ($session->status !== CommissionSessionStatusEnum::PLANNED && $session->status !== CommissionSessionStatusEnum::IN_PROGRESS) {
                    throw new \InvalidArgumentException("Impossible d'ajouter un rapport à une session non planifiée ou non en cours.");
                }

                if ($report->status !== ReportStatusEnum::IN_COMMISSION_REVIEW) {
                    throw new \InvalidArgumentException("Le rapport n'est pas éligible pour être ajouté à une session de commission (statut: {$report->status->value}).");
                }

                if ($session->reports->contains($report->id)) {
                    throw new \InvalidArgumentException('Le rapport est déjà ajouté à cette session.');
                }

                $session->reports()->attach($report->id);

                $this->auditService->logAction('REPORT_ADDED_TO_SESSION', $session, ['session_id' => $session->session_id, 'report_id' => $report->report_id]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function recordVote(CommissionSession $session, Report $report, User $voterUser, VoteDecisionEnum $decision, ?string $comment = null): Vote
    {
        try {
            return DB::transaction(function () use ($session, $report, $voterUser, $decision, $comment) {
                $voterTeacher = $voterUser->teacher;
                if (! $voterTeacher || ! $session->teachers->contains($voterTeacher->id)) {
                    throw new AuthorizationException("L'utilisateur n'est pas un membre autorisé de cette commission.");
                }
                if ($session->status !== CommissionSessionStatusEnum::IN_PROGRESS) {
                    throw new \InvalidArgumentException('Impossible de voter dans une session non en cours.');
                }

                if (($decision === VoteDecisionEnum::REJECTED || $decision === VoteDecisionEnum::APPROVED_WITH_RESERVATIONS) && empty($comment)) {
                    throw new \InvalidArgumentException('Un commentaire est obligatoire pour cette décision de vote.');
                }

                $currentVoteRound = Vote::where('commission_session_id', $session->id)
                    ->where('report_id', $report->id)
                    ->max('vote_round') ?? 1;

                $vote = Vote::where('commission_session_id', $session->id)
                    ->where('report_id', $report->id)
                    ->where('teacher_id', $voterTeacher->id)
                    ->where('vote_round', $currentVoteRound)
                    ->first();

                if ($vote) {
                    $vote->vote_decision_id = $decision->value;
                    $vote->comment = $comment;
                    $vote->vote_date = now();
                    $vote->save();
                } else {
                    $vote = Vote::create([
                        'vote_id' => $this->uniqueIdGeneratorService->generate('VOTE', (int) date('Y')),
                        'commission_session_id' => $session->id,
                        'report_id' => $report->id,
                        'teacher_id' => $voterTeacher->id,
                        'vote_decision_id' => $decision->value,
                        'comment' => $comment,
                        'vote_date' => now(),
                        'vote_round' => $currentVoteRound,
                    ]);
                }

                $this->auditService->logAction('COMMISSION_VOTE_RECORDED', $vote, ['session_id' => $session->session_id, 'report_id' => $report->report_id, 'voter_id' => $voterUser->id, 'decision' => $decision->value, 'round' => $currentVoteRound]);

                return $vote;
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function closeSession(CommissionSession $session, User $presidentUser): void
    {
        if ($session->president_teacher_id !== $presidentUser->teacher->id) {
            throw new AuthorizationException('Seul le président de la session peut la clôturer.');
        }
        if ($session->status !== CommissionSessionStatusEnum::IN_PROGRESS) {
            throw new CommissionException('La session doit être en cours pour être clôturée.');
        }

        try {
            DB::transaction(function () use ($session, $presidentUser) {
                foreach ($session->reports as $report) {
                    $finalDecisionEnum = $this->calculateFinalDecisionForReport($report, $session);
                    if (! $finalDecisionEnum) {
                        throw new CommissionException("Le rapport {$report->report_id} n'a pas de décision finale. Impossible de clôturer la session.");
                    }
                    $this->reportFlowService->updateReportStatus($report, $finalDecisionEnum, $presidentUser, 'Décision finale de la commission.');
                }

                $session->status = CommissionSessionStatusEnum::CLOSED;
                $session->save();

                $this->generatePv($session, $presidentUser);

                $this->auditService->logAction('COMMISSION_SESSION_CLOSED', $session, ['session_id' => $session->session_id, 'president_id' => $presidentUser->id]);
                $this->notificationService->processNotificationRules('COMMISSION_SESSION_CLOSED', $session, ['session_id' => $session->session_id]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function calculateFinalDecisionForReport(Report $report, CommissionSession $session): ?ReportStatusEnum
    {
        $votes = $report->votes()->where('commission_session_id', $session->id)->get();
        $voteCounts = $votes->groupBy('vote_decision_id')->map(function ($group) {
            return $group->count();
        });
        $approvedCount = $voteCounts->get(VoteDecisionEnum::APPROVED->value, 0);
        $rejectedCount = $voteCounts->get(VoteDecisionEnum::REJECTED->value, 0);
        $approvedWithReservationsCount = $voteCounts->get(VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value, 0);
        $abstainCount = $voteCounts->get(VoteDecisionEnum::ABSTAIN->value, 0);

        $totalVoters = $session->teachers->count(); // Assumer que tous les enseignants de la session sont des votants
        $actualVoters = $votes->unique('teacher_id')->count();

        if ($actualVoters < $session->required_voters_count) {
            return null; // Quorum non atteint
        }

        if ($rejectedCount > 0) {
            return ReportStatusEnum::REJECTED;
        }

        if ($approvedCount + $approvedWithReservationsCount >= $session->required_voters_count) {
            return ReportStatusEnum::VALIDATED;
        }

        return null; // Décision non finale (ex: égalité, besoin d'un nouveau tour)
    }

    public function generatePv(CommissionSession $session, User $authorUser): Pv
    {
        $authorTeacher = $authorUser->teacher;
        if (! $authorTeacher || ! $session->teachers->contains($authorTeacher->id)) {
            throw new AuthorizationException("L'utilisateur n'est pas un membre autorisé de cette commission pour rédiger un PV.");
        }
        if ($session->status !== CommissionSessionStatusEnum::CLOSED) {
            throw new \InvalidArgumentException('Impossible de générer un PV pour une session non clôturée.');
        }

        try {
            return DB::transaction(function () use ($session, $authorUser) {
                $pvContent = $this->generatePvContentFromSessionData($session);

                $pv = Pv::create([
                    'pv_id' => $this->uniqueIdGeneratorService->generate('PV', (int) date('Y')),
                    'commission_session_id' => $session->id,
                    'type' => 'session',
                    'content' => $pvContent,
                    'author_user_id' => $authorUser->id,
                    'status' => PvStatusEnum::DRAFT,
                    'approval_deadline' => now()->addDays(config('app.pv_approval_deadline_days', 7)),
                ]);

                $this->auditService->logAction('PV_GENERATED', $pv, ['pv_id' => $pv->pv_id, 'session_id' => $session->session_id, 'author_id' => $authorUser->id]);
                $this->notificationService->processNotificationRules('PV_READY_FOR_APPROVAL', $pv, ['pv_id' => $pv->pv_id, 'session_id' => $session->session_id]);

                return $pv;
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function generatePvContentFromSessionData(CommissionSession $session): string
    {
        $content = "Procès-Verbal de la Session de Commission : {$session->name}\n";
        $content .= "Date de la session : {$session->start_date->format('d/m/Y')}\n";
        $content .= "Président : {$session->president->first_name} {$session->president->last_name}\n";
        $content .= "Membres présents : \n";
        foreach ($session->teachers as $teacher) {
            $content .= "- {$teacher->first_name} {$teacher->last_name}\n";
        }
        $content .= "\n";

        $content .= "Rapports évalués : \n";
        foreach ($session->reports as $report) {
            $finalDecision = $this->calculateFinalDecisionForReport($report, $session);
            $content .= "  - Rapport ID: {$report->report_id}, Titre: {$report->title}\n";
            $content .= '    Décision finale: '.($finalDecision ? $finalDecision->value : 'Non décidée')."\n";
            $content .= "    Commentaires des votes: \n";
            foreach ($report->votes()->where('commission_session_id', $session->id)->get() as $vote) {
                $content .= "      - {$vote->teacher->first_name} {$vote->teacher->last_name} ({$vote->voteDecision->name}): {$vote->comment}\n";
            }
            $content .= "\n";
        }

        return $content;
    }

    /**
     * @throws CommissionException
     */
    public function approvePv(Pv $pv, User $approverUser): void
    {
        if ($pv->status !== PvStatusEnum::PENDING_APPROVAL) {
            throw new CommissionException('Ce PV ne peut pas être approuvé ou rejeté car il n\'est pas en attente d\'approbation.');
        }

        $approval = $pv->approvals()->where('user_id', $approverUser->id)->first();

        if ($approval) {
            throw new CommissionException('Vous avez déjà approuvé ce PV.');
        }

        $pv->approvals()->create([
            'user_id' => $approverUser->id,
            'decision' => PvApprovalDecisionEnum::APPROVED,
            'comments' => 'Approuvé automatiquement via le système.',
        ]);

        // Check if all members have approved
        if ($pv->approvals()->where('decision', PvApprovalDecisionEnum::APPROVED)->count() === $pv->commissionSession->members()->count()) {
            $pv->status = PvStatusEnum::APPROVED;
            $pv->save();
            $this->notificationService->processNotificationRules('PV_FINALIZED', $pv);
        }
    }

    /**
     * @throws CommissionException
     */
    public function rejectPv(Pv $pv, User $rejectingUser, string $comments): void
    {
        if ($pv->status !== PvStatusEnum::PENDING_APPROVAL) {
            throw new CommissionException('Ce PV ne peut pas être rejeté car il n\'est pas en attente d\'approbation.');
        }

        $pv->approvals()->create([
            'user_id' => $rejectingUser->id,
            'decision' => PvApprovalDecisionEnum::CHANGES_REQUESTED,
            'comments' => $comments,
        ]);

        $pv->status = PvStatusEnum::CHANGES_REQUESTED;
        $pv->save();

        $this->notificationService->processNotificationRules('PV_REJECTED', $pv, ['rejecting_user' => $rejectingUser->name, 'comments' => $comments]);
    }

    public function forcePvApproval(Pv $pv, User $adminUser, string $reason): void
    {
        if (! $adminUser->hasRole('Admin')) {
            throw new AuthorizationException("Seul un administrateur peut forcer l'approbation d'un PV.");
        }
        if (empty($reason)) {
            throw new \InvalidArgumentException("Une raison est obligatoire pour forcer l'approbation d'un PV.");
        }

        try {
            DB::transaction(function () use ($pv, $adminUser, $reason) {
                $oldStatus = $pv->status->value;
                $pv->status = PvStatusEnum::APPROVED;
                $pv->save();

                $this->auditService->logAction('PV_APPROVAL_FORCED', $pv, [
                    'pv_id' => $pv->pv_id,
                    'admin_id' => $adminUser->id,
                    'reason' => $reason,
                    'previous_status' => $oldStatus,
                ]);

                $this->pdfGenerationService->generateAndRegisterDocument(
                    'pdf.pv_final',
                    ['pv' => $pv],
                    'PV',
                    $pv,
                    $adminUser
                );

                $this->notificationService->processNotificationRules('PV_APPROVAL_FORCED', $pv, ['pv_id' => $pv->pv_id, 'reason' => $reason]);
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
