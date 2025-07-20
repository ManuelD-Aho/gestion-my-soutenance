<?php

namespace App\Filament\Admin\Resources\ConformityStatusResource\Pages;

use App\Filament\Admin\Resources\ConformityStatusResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewConformityStatus extends ViewRecord
{
    protected static string $resource = ConformityStatusResource::class;

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
