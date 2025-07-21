<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\AdministrativeStaff;
use App\Models\Report;
use App\Models\Student;
use App\Models\Teacher; // Utiliser Teacher pour la cohérence
use App\Models\User;
use App\Policies\AcademicYearPolicy;
use App\Policies\AdministrativeStaffPolicy;
use App\Policies\ReportPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TeacherPolicy; // Utiliser TeacherPolicy
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AcademicYear::class => AcademicYearPolicy::class,
        AdministrativeStaff::class => AdministrativeStaffPolicy::class,
        Teacher::class => TeacherPolicy::class, // Enregistrement de TeacherPolicy
        Report::class => ReportPolicy::class,
        Student::class => StudentPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Gate pour l'impersonation (Lab404/Laravel-Impersonate)
        // L'administrateur a tous les droits et peut impersoner.
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Admin')) {
                return true;
            }
            return null;
        });
    }
}
