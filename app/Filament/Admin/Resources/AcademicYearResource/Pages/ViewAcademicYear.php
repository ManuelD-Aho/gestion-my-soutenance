<?php

namespace App\Filament\Admin\Resources\AcademicYearResource\Pages;

use App\Filament\Admin\Resources\AcademicYearResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAcademicYear extends ViewRecord
{
    protected static string $resource = AcademicYearResource::class;

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
