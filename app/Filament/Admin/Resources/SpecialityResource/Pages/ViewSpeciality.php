<?php

namespace App\Filament\Admin\Resources\SpecialityResource\Pages;

use App\Filament\Admin\Resources\SpecialityResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSpeciality extends ViewRecord
{
    protected static string $resource = SpecialityResource::class;

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
