<?php

namespace App\Filament\Admin\Resources\ConformityCriterionResource\Pages;

use App\Filament\Admin\Resources\ConformityCriterionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConformityCriterions extends ListRecords
{
    protected static string $resource = ConformityCriterionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
