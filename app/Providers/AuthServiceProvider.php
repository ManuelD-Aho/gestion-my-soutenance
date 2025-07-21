<?php

namespace App\Providers;

use App\Models\Report;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate; // Assurez-vous que cette façade est importée

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Report::class => ReportPolicy::class,
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Exemple par défaut de Laravel
        // Ajoutez ici toutes vos autres politiques (ex: User::class => UserPolicy::class)
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Vous pouvez définir des Gates ici si nécessaire, en complément des Policies.
        // Exemple de Gate pour l'administrateur (peut être défini dans une Policy ou un Service Provider)
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Admin')) { // Assurez-vous que Spatie est configuré pour les rôles
                return true; // L'administrateur a tous les droits
            }
        });
    }
}
