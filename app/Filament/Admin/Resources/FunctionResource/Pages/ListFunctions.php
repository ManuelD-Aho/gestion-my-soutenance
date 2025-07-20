<?php

namespace App\Filament\Admin\Resources\FunctionResource\Pages;

use App\Filament\Admin\Resources\FunctionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFunctions extends ListRecords
{
    protected static string $resource = FunctionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
