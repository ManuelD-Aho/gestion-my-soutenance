<?php

namespace App\Filament\Admin\Resources\ReclamationStatusResource\Pages;

use App\Filament\Admin\Resources\ReclamationStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReclamationStatuss extends ListRecords
{
    protected static string $resource = ReclamationStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
