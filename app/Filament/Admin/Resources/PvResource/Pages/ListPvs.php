<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PvResource\Pages;

use App\Filament\Admin\Resources\PvResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPvs extends ListRecords
{
    protected static string $resource = PvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
