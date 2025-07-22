<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User; // Utiliser Teacher pour la cohérence
use Illuminate\Auth\Access\HandlesAuthorization;

class TeacherPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Admin, RS, Membre Commission peuvent voir la liste des enseignants
        return $user->hasAnyRole(['Admin', 'Responsable Scolarite', 'Membre Commission', 'President Commission']);
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $user->hasAnyRole(['Admin', 'Responsable Scolarite', 'Membre Commission', 'President Commission']) || ($user->id === $teacher->user_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->hasRole('Admin') || ($user->id === $teacher->user_id);
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->hasRole('Admin'); // Opération très restreinte
    }

    public function restore(User $user, Teacher $teacher): bool
    {
        return $user->hasRole('Admin');
    }

    public function forceDelete(User $user, Teacher $teacher): bool
    {
        return $user->hasRole('Admin');
    }
}
