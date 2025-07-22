<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\ReportStatusEnum;
use App\Models\Report;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportStatusChanged
{
    use Dispatchable, SerializesModels;

    public Report $report;

    public ReportStatusEnum $newStatus;

    public ReportStatusEnum $oldStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Report $report, ReportStatusEnum $oldStatus, ReportStatusEnum $newStatus)
    {
        $this->report = $report;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
