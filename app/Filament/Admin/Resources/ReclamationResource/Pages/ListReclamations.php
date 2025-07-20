<?php

namespace App\Filament\Admin\Resources\ReclamationResource\Pages;

use App\Filament\Admin\Resources\ReclamationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReclamations extends ListRecords
{
    protected static string $resource = ReclamationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
