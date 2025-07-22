<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SpecialityResource\Pages;

use App\Filament\Admin\Resources\SpecialityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialitys extends ListRecords
{
    protected static string $resource = SpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
