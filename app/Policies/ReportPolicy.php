<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Report;
use App\Enums\ReportStatusEnum;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Admin, Responsable Scolarité, Agent de Conformité, Membre Commission peuvent voir tous les rapports.
        // L'étudiant ne peut voir que les siens (géré par la requête dans la Resource ou une autre Policy).
        return $user->hasAnyRole(['Admin', 'Responsable Scolarite', 'Agent de Conformite', 'Membre Commission', 'President Commission', 'Etudiant']);
    }

    public function view(User $user, Report $report): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Étudiant peut voir son propre rapport.
        if ($user->hasRole('Etudiant') && $user->student && $report->student_id === $user->student->id) {
            return true;
        }

        // Agent de Conformité peut voir les rapports en vérification ou nécessitant correction.
        if ($user->hasRole('Agent de Conformite') && in_array($report->status, [ReportStatusEnum::SUBMITTED, ReportStatusEnum::IN_CONFORMITY_CHECK, ReportStatusEnum::NEEDS_CORRECTION])) {
            return true;
        }

        // Membre/Président Commission peut voir les rapports en commission ou validés/rejetés.
        if ($user->hasAnyRole(['Membre Commission', 'President Commission']) && in_array($report->status, [ReportStatusEnum::IN_COMMISSION_REVIEW, ReportStatusEnum::VALIDATED, ReportStatusEnum::REJECTED])) {
            // Logique plus fine: seulement si le rapport est assigné à leur session, etc.
            // Pour l'instant, une vue plus large est acceptée.
            return true;
        }

        // Responsable Scolarité peut voir tous les rapports liés à ses étudiants ou à son année académique.
        if ($user->hasRole('Responsable Scolarite')) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Seul un étudiant peut créer son rapport.
        return $user->hasRole('Etudiant');
    }

    public function update(User $user, Report $report): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Étudiant peut modifier son propre rapport s'il est en brouillon ou nécessite correction.
        if ($user->hasRole('Etudiant') && $user->student && $report->student_id === $user->student->id) {
            return in_array($report->status, [ReportStatusEnum::DRAFT, ReportStatusEnum::NEEDS_CORRECTION]);
        }

        return false;
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->hasRole('Admin');
    }

    public function restore(User $user, Report $report): bool
    {
        return $user->hasRole('Admin');
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Détermine si l'utilisateur peut changer le statut d'un rapport.
     * Cette méthode est spécifique pour `ReportFlowService->updateReportStatus`.
     */
    public function updateStatus(User $user, Report $report, ReportStatusEnum $newStatus): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->hasRole('Agent de Conformite')) {
            // L'Agent de Conformité peut faire passer un rapport soumis ou en vérification
            // vers 'En vérification conformité' ou 'Nécessite Correction' ou 'En Commission'.
            if (in_array($report->status, [ReportStatusEnum::SUBMITTED, ReportStatusEnum::IN_CONFORMITY_CHECK])) {
                return in_array($newStatus, [ReportStatusEnum::IN_CONFORMITY_CHECK, ReportStatusEnum::NEEDS_CORRECTION, ReportStatusEnum::IN_COMMISSION_REVIEW]);
            }
        }

        if ($user->hasAnyRole(['Membre Commission', 'President Commission'])) {
            // Les membres de la commission peuvent faire passer un rapport 'En Commission'
            // vers 'Validé', 'Rejeté', ou 'Nécessite Correction'.
            if ($report->status === ReportStatusEnum::IN_COMMISSION_REVIEW) {
                return in_array($newStatus, [ReportStatusEnum::VALIDATED, ReportStatusEnum::REJECTED, ReportStatusEnum::NEEDS_CORRECTION]);
            }
        }

        if ($user->hasRole('Responsable Scolarite')) {
            // Le Responsable Scolarité peut archiver un rapport (ex: si l'étudiant abandonne).
            return $newStatus === ReportStatusEnum::ARCHIVED;
        }

        // L'étudiant peut "soumettre" un rapport brouillon ou nécessitant correction.
        // Cette logique est gérée dans le service ReportFlowService->submitReport
        // et n'est pas une transition de statut générique par updateStatus.

        return false;
    }
}
