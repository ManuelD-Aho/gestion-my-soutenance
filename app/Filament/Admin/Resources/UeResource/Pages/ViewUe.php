<?php

namespace App\Filament\Admin\Resources\UeResource\Pages;

use App\Filament\Admin\Resources\UeResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUe extends ViewRecord
{
    protected static string $resource = UeResource::class;

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
