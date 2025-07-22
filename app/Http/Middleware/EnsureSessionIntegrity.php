<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User; // Assurez-vous d'importer le modèle User
use App\Services\SessionManagementService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable; // Ajouter si vous l'utilisez dans le catch

class EnsureSessionIntegrity
{
    protected SessionManagementService $sessionManagementService;

    public function __construct(SessionManagementService $sessionManagementService)
    {
        $this->sessionManagementService = $sessionManagementService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */ // Amélioration du PHPDoc pour la clarté
        $user = Auth::user();

        if ($user) { // Vérifier que l'utilisateur est bien connecté
            if ($request->session()->has('last_ip') && $request->session()->has('last_user_agent')) {
                $currentIp = $request->ip();
                $currentUserAgent = $request->userAgent();

                $lastIp = $request->session()->get('last_ip');
                $lastUserAgent = $request->session()->get('last_user_agent');

                if ($currentIp !== $lastIp || $currentUserAgent !== $lastUserAgent) {
                    Log::warning("Activité de session suspecte détectée pour l'utilisateur ".$user->id, [ // Utiliser $user->id
                        'user_id' => $user->id,
                        'old_ip' => $lastIp,
                        'new_ip' => $currentIp,
                        'old_ua' => $lastUserAgent,
                        'new_ua' => $currentUserAgent,
                    ]);

                    try {
                        $this->sessionManagementService->invalidateAllUserSessions($user);
                    } catch (Throwable $e) {
                        Log::error("Échec de l'invalidation de session lors d'une activité suspecte: {$e->getMessage()}");
                    }

                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->with('error', 'Votre session a été invalidée en raison d\'une activité suspecte. Veuillez vous reconnecter.');
                }
            }

            $request->session()->put('last_ip', $request->ip());
            $request->session()->put('last_user_agent', $request->userAgent());
        }

        return $next($request);
    }
}
