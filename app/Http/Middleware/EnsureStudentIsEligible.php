<?php

namespace App\Http\Middleware;

use App\Services\PenaltyService;
use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentIsEligible
{
    protected PenaltyService $penaltyService;

    public function __construct(PenaltyService $penaltyService)
    {
        $this->penaltyService = $penaltyService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */ // Ajouter ce PHPDoc pour aider l'IDE
        $user = Auth::user();

        if ($user && $user->hasRole('Etudiant')) {
            // Vérifier si la relation 'student' existe sur l'instance de User
            if (!$user->student) { // Cette ligne est correcte si $user est bien App\Models\User
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Votre profil étudiant n\'est pas correctement lié. Veuillez contacter l\'administration.');
            }

            if (!$this->penaltyService->checkStudentEligibility($user->student)) {
                return redirect()->route('filament.app.pages.dashboard')->with('error', 'Vous avez des pénalités en attente de régularisation. Veuillez les régler pour accéder à cette fonctionnalité.');
            }
        }

        return $next($request);
    }
}
