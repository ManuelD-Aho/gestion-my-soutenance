<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Report;
use App\Enums\ReportStatusEnum; // Assurez-vous d'importer l'Enum
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir n'importe quel rapport.
     */
    public function viewAny(User $user): bool
    {
        // Exemple: Admin, Responsable Scolarité, Agent de Conformité, Membre Commission peuvent voir tous les rapports.
        // L'étudiant ne peut voir que les siens (géré par la requête dans la Resource ou une autre Policy).
        return $user->hasAnyRole(['Admin', 'Responsable Scolarite', 'Agent de Conformite', 'Membre Commission', 'President Commission']);
    }

    /**
     * Détermine si l'utilisateur peut voir un rapport spécifique.
     */
    public function view(User $user, Report $report): bool
    {
        // Admin peut tout voir.
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
            return true; // Ou une logique plus fine si nécessaire
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut créer un rapport.
     */
    public function create(User $user): bool
    {
        // Seul un étudiant peut créer son rapport.
        return $user->hasRole('Etudiant');
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un rapport.
     */
    public function update(User $user, Report $report): bool
    {
        // Admin peut tout mettre à jour.
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Étudiant peut modifier son propre rapport s'il est en brouillon ou nécessite correction.
        if ($user->hasRole('Etudiant') && $user->student && $report->student_id === $user->student->id) {
            return in_array($report->status, [ReportStatusEnum::DRAFT, ReportStatusEnum::NEEDS_CORRECTION]);
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut changer le statut d'un rapport.
     * Cette méthode est spécifique pour `ReportFlowService->updateReportStatus`.
     */
    public function updateStatus(User $user, Report $report, ReportStatusEnum $newStatus): bool
    {
        // Admin peut toujours changer n'importe quel statut.
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Agent de Conformité:
        if ($user->hasRole('Agent de Conformite')) {
            // Peut passer de SUBMITTED ou NEEDS_CORRECTION à IN_CONFORMITY_CHECK, NEEDS_CORRECTION.
            if ($report->status === ReportStatusEnum::SUBMITTED && in_array($newStatus, [ReportStatusEnum::IN_CONFORMITY_CHECK, ReportStatusEnum::NEEDS_CORRECTION])) {
                return true;
            }
            if ($report->status === ReportStatusEnum::IN_CONFORMITY_CHECK && in_array($newStatus, [ReportStatusEnum::IN_COMMISSION_REVIEW, ReportStatusEnum::NEEDS_CORRECTION])) {
                return true;
            }
        }

        // Membre/Président Commission:
        if ($user->hasAnyRole(['Membre Commission', 'President Commission'])) {
            // Peut passer de IN_COMMISSION_REVIEW à VALIDATED, REJECTED, NEEDS_CORRECTION.
            if ($report->status === ReportStatusEnum::IN_COMMISSION_REVIEW && in_array($newStatus, [ReportStatusEnum::VALIDATED, ReportStatusEnum::REJECTED, ReportStatusEnum::NEEDS_CORRECTION])) {
                // Logique plus fine: seulement si le rapport est dans une session dont ils sont membres.
                return true;
            }
        }

        // Responsable Scolarité:
        if ($user->hasRole('Responsable Scolarite')) {
            // Peut archiver un rapport (ex: si l'étudiant abandonne).
            if ($newStatus === ReportStatusEnum::ARCHIVED) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer un rapport.
     */
    public function delete(User $user, Report $report): bool
    {
        // Opération très restreinte, généralement réservée à l'Admin.
        return $user->hasRole('Admin');
    }

    /**
     * Détermine si l'utilisateur peut restaurer un rapport.
     */
    public function restore(User $user, Report $report): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Détermine si l'utilisateur peut forcer la suppression d'un rapport.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $user->hasRole('Admin');
    }
}
