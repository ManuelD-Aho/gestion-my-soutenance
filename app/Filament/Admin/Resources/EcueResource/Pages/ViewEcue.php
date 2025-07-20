<?php

namespace App\Filament\Admin\Resources\EcueResource\Pages;

use App\Filament\Admin\Resources\EcueResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEcue extends ViewRecord
{
    protected static string $resource = EcueResource::class;

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
