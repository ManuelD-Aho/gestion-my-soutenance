<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdministrativeStaff;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdministrativeStaffPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Responsable Scolarite');
    }

    public function view(User $user, AdministrativeStaff $administrativeStaff): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Responsable Scolarite') || ($user->id === $administrativeStaff->user_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, AdministrativeStaff $administrativeStaff): bool
    {
        return $user->hasRole('Admin') || ($user->id === $administrativeStaff->user_id);
    }

    public function delete(User $user, AdministrativeStaff $administrativeStaff): bool
    {
        return $user->hasRole('Admin'); // Opération très restreinte
    }

    public function restore(User $user, AdministrativeStaff $administrativeStaff): bool
    {
        return $user->hasRole('Admin');
    }

    public function forceDelete(User $user, AdministrativeStaff $administrativeStaff): bool
    {
        return $user->hasRole('Admin');
    }
}
