<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ActionResource\Pages;

use App\Filament\Admin\Resources\ActionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActions extends ListRecords
{
    protected static string $resource = ActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
