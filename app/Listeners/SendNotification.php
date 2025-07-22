<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReportStatusChanged;

class SendNotification
{
    public function handle(ReportStatusChanged $event): void
    {
        // Send notification based on event
    }
}
