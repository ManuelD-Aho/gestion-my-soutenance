<?php

namespace App\Filament\Admin\Resources\JuryRoleResource\Pages;

use App\Filament\Admin\Resources\JuryRoleResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewJuryRole extends ViewRecord
{
    protected static string $resource = JuryRoleResource::class;

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
