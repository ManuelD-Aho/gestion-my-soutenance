<?php

namespace App\Filament\AppPanel\Resources\CommissionSessionResource\Pages;

use App\Filament\AppPanel\Resources\CommissionSessionResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCommissionSession extends ViewRecord
{
    protected static string $resource = CommissionSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // Define infolist components here
        ]);
    }
}
