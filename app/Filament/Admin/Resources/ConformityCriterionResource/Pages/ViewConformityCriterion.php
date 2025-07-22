<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ConformityCriterionResource\Pages;

use App\Filament\Admin\Resources\ConformityCriterionResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewConformityCriterion extends ViewRecord
{
    protected static string $resource = ConformityCriterionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // Define infolist components here
        ]);
    }
}
