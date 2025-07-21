<?php

namespace App\Listeners;

use App\Events\AuditActionShouldBeLogged;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogAuditAction implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function handle(AuditActionShouldBeLogged $event): void
    {
        $this->auditService->performLog(
            $event->actionCode,
            $event->auditable,
            $event->details
        );
    }
}