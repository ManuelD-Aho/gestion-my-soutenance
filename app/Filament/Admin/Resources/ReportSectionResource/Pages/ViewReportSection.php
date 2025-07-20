<?php

namespace App\Filament\Admin\Resources\ReportSectionResource\Pages;

use App\Filament\Admin\Resources\ReportSectionResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewReportSection extends ViewRecord
{
    protected static string $resource = ReportSectionResource::class;

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
