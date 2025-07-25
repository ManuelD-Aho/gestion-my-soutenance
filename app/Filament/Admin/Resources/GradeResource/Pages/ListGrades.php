<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\GradeResource\Pages;

use App\Filament\Admin\Resources\GradeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGrades extends ListRecords
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
