<?php

namespace App\Filament\Admin\Resources\ConformityStatusResource\Pages;

use App\Filament\Admin\Resources\ConformityStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConformityStatuss extends ListRecords
{
    protected static string $resource = ConformityStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
