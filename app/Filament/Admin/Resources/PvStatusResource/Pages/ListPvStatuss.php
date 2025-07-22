<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PvStatusResource\Pages;

use App\Filament\Admin\Resources\PvStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPvStatuss extends ListRecords
{
    protected static string $resource = PvStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
