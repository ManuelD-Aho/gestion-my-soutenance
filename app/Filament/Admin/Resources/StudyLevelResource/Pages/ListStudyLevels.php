<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\StudyLevelResource\Pages;

use App\Filament\Admin\Resources\StudyLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudyLevels extends ListRecords
{
    protected static string $resource = StudyLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
