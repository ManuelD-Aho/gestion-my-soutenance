<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AdministrativeStaff;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdministrativeStaffPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, AdministrativeStaff $AdministrativeStaff): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, AdministrativeStaff $AdministrativeStaff): bool { return true; }
    public function delete(User $user, AdministrativeStaff $AdministrativeStaff): bool { return true; }
    public function restore(User $user, AdministrativeStaff $AdministrativeStaff): bool { return true; }
    public function forceDelete(User $user, AdministrativeStaff $AdministrativeStaff): bool { return true; }
}
