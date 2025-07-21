<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SessionManagementService
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function invalidateAllUserSessions(User $user): void
    {
        try {
            DB::transaction(function () use ($user) {
                DB::table('sessions')->where('user_id', $user->id)->delete();

                $this->auditService->logAction("USER_SESSIONS_INVALIDATED", $user, ['user_email' => $user->email, 'reason' => 'Forced logout by system or admin.']);
            });
        } catch (Throwable $e) {
            Log::error("SessionManagementService: Échec de l'invalidation des sessions pour user {$user->id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
