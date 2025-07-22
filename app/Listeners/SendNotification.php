<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReportStatusChanged;

class SendNotification
{
    /**
     * Handle the event.
     */
    public function handle(ReportStatusChanged $event): void
    {
        // Logic to send notification
    }
}
