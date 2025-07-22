<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Admin et Responsable Scolarité peuvent voir tous les étudiants.
        return $user->hasAnyRole(['Admin', 'Responsable Scolarite']);
    }

    public function view(User $user, Student $student): bool
    {
        // Admin et RS peuvent voir n'importe quel étudiant.
        // Un étudiant peut voir sa propre fiche.
        return $user->hasAnyRole(['Admin', 'Responsable Scolarite']) || ($user->hasRole('Etudiant') && $user->student && $user->student->id === $student->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Responsable Scolarite');
    }

    public function update(User $user, Student $student): bool
    {
        // Admin et RS peuvent modifier n'importe quelle fiche étudiant.
        // Un étudiant peut modifier certaines informations de sa propre fiche (gérées dans le service/formulaire).
        return $user->hasAnyRole(['Admin', 'Responsable Scolarite']) || ($user->hasRole('Etudiant') && $user->student && $user->student->id === $student->id);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('Admin'); // Opération très restreinte
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->hasRole('Admin');
    }

    public function forceDelete(User $user, Student $student): bool
    {
        return $user->hasRole('Admin');
    }
}
