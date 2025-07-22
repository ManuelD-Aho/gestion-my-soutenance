<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PvApprovalDecisionResource\Pages;

use App\Filament\Admin\Resources\PvApprovalDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPvApprovalDecisions extends ListRecords
{
    protected static string $resource = PvApprovalDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
