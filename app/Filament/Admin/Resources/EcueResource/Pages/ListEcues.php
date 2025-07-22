<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\EcueResource\Pages;

use App\Filament\Admin\Resources\EcueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEcues extends ListRecords
{
    protected static string $resource = EcueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
