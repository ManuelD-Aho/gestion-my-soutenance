<?php

namespace App\Filament\Admin\Resources\EnseignantResource\Pages;

use App\Filament\Admin\Resources\EnseignantResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEnseignant extends ViewRecord
{
    protected static string $resource = EnseignantResource::class;

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
