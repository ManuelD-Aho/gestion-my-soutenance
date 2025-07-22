<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcademicYearPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole('Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole('Admin');
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        // Une année académique ne peut être supprimée que si elle n'a aucune dépendance active (rapports, inscriptions, etc.)
        // La logique de vérification des dépendances doit être dans le service ou le modèle.
        return $user->hasRole('Admin');
    }

    public function restore(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole('Admin');
    }

    public function forceDelete(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole('Admin');
    }
}
