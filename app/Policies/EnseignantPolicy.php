<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Enseignant;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnseignantPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Enseignant $Enseignant): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Enseignant $Enseignant): bool { return true; }
    public function delete(User $user, Enseignant $Enseignant): bool { return true; }
    public function restore(User $user, Enseignant $Enseignant): bool { return true; }
    public function forceDelete(User $user, Enseignant $Enseignant): bool { return true; }
}
