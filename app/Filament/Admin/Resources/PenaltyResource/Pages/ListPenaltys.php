<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PenaltyResource\Pages;

use App\Filament\Admin\Resources\PenaltyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenaltys extends ListRecords
{
    protected static string $resource = PenaltyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
