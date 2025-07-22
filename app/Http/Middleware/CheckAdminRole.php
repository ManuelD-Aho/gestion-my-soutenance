<?php

namespace App\Http\Middleware;

use App\Filament\AppPanel\Pages\Dashboard; // <-- AJOUTEZ CET IMPORT
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasRole('Admin')) {
            // Redirige vers le panneau par défaut si l'utilisateur n'est pas Admin
            // ou n'est pas connecté
            return redirect(Dashboard::getUrl()); // <-- LA LIGNE CORRIGÉE
        }

        return $next($request);
    }
}
