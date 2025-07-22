<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\AuditActionShouldBeLogged;
use App\Models\Action;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditService
{
    protected array $redactedKeys = ['password', 'password_confirmation', 'token', 'secret'];

    protected UniqueIdGeneratorService $uniqueIdGeneratorService;

    public function __construct(UniqueIdGeneratorService $uniqueIdGeneratorService)
    {
        $this->uniqueIdGeneratorService = $uniqueIdGeneratorService;
    }

    public function logAction(string $actionCode, ?Model $auditable = null, array $details = []): void
    {
        AuditActionShouldBeLogged::dispatch($actionCode, $auditable, $details);
    }

    public function performLog(string $actionCode, ?Model $auditable = null, array $details = []): void
    {
        try {
            $actionModel = Action::where('code', $actionCode)->first();
            if (! $actionModel) {
                Log::warning("AuditService: Attempted to log an action with an unknown code: {$actionCode}");

                return;
            }

            $userId = Auth::id();
            $ipAddress = request()->ip() ?? 'CLI';
            $userAgent = request()->userAgent() ?? 'Artisan:'.implode(' ', request()->server('argv', []));

            $auditableId = $auditable ? $auditable->getKey() : null;
            $auditableType = $auditable ? get_class($auditable) : null;

            $safeDetails = $this->redact($details);

            $logId = $this->uniqueIdGeneratorService->generate('LOG', (int) date('Y'));

            AuditLog::create([
                'log_id' => $logId,
                'user_id' => $userId,
                'action_id' => $actionModel->id,
                'action_date' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'auditable_id' => $auditableId,
                'auditable_type' => $auditableType,
                'details' => $safeDetails,
            ]);
        } catch (Throwable $e) {
            Log::error("AuditService: Failed to log audit action {$actionCode}: {$e->getMessage()}");
        }
    }

    private function redact(array $details): array
    {
        foreach ($this->redactedKeys as $key) {
            if (array_key_exists($key, $details)) {
                $details[$key] = '********';
            }
        }

        return $details;
    }
}
