<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PvApprovalDecisionResource\Pages;

use App\Filament\Admin\Resources\PvApprovalDecisionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePvApprovalDecision extends CreateRecord
{
    protected static string $resource = PvApprovalDecisionResource::class;
}
