<?php

namespace App\Filament\Admin\Resources\UeResource\Pages;

use App\Filament\Admin\Resources\UeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUes extends ListRecords
{
    protected static string $resource = UeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
