<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditActionShouldBeLogged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $actionCode,
        public ?Model $auditable,
        public array $details
    ) {}
}
