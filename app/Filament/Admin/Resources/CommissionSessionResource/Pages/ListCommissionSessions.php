<?php

namespace App\Filament\Admin\Resources\CommissionSessionResource\Pages;

use App\Filament\Admin\Resources\CommissionSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommissionSessions extends ListRecords
{
    protected static string $resource = CommissionSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
