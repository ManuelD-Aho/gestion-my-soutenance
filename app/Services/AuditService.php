<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function logAction(string $actionCode, $auditable = null, array $details = []): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action_id' => $actionCode,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'auditable_id' => $auditable ? $auditable->id : null,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'details' => $details,
        ]);
    }
}
