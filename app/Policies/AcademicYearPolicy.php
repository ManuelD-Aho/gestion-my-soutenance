<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcademicYearPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, AcademicYear $AcademicYear): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, AcademicYear $AcademicYear): bool { return true; }
    public function delete(User $user, AcademicYear $AcademicYear): bool { return true; }
    public function restore(User $user, AcademicYear $AcademicYear): bool { return true; }
    public function forceDelete(User $user, AcademicYear $AcademicYear): bool { return true; }
}
