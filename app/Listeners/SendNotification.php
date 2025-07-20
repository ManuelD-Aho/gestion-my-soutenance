<?php

namespace App\Listeners;

use App\Events\ReportStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNotification
{
    public function handle(ReportStatusChanged $event): void
    {
        // Send notification based on event
    }
}
