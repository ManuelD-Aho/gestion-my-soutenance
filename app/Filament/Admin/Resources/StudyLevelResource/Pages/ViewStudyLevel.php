<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\StudyLevelResource\Pages;

use App\Filament\Admin\Resources\StudyLevelResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewStudyLevel extends ViewRecord
{
    protected static string $resource = StudyLevelResource::class;

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
