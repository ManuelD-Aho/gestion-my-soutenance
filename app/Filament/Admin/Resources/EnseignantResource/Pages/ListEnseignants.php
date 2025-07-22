<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\EnseignantResource\Pages;

use App\Filament\Admin\Resources\EnseignantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnseignants extends ListRecords
{
    protected static string $resource = EnseignantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
