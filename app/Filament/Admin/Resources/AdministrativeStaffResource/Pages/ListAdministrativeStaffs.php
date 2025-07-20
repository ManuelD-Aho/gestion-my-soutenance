<?php

namespace App\Filament\Admin\Resources\AdministrativeStaffResource\Pages;

use App\Filament\Admin\Resources\AdministrativeStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdministrativeStaffs extends ListRecords
{
    protected static string $resource = AdministrativeStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
