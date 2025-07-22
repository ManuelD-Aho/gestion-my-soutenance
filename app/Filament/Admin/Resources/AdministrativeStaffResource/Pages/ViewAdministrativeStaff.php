<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\AdministrativeStaffResource\Pages;

use App\Filament\Admin\Resources\AdministrativeStaffResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAdministrativeStaff extends ViewRecord
{
    protected static string $resource = AdministrativeStaffResource::class;

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
