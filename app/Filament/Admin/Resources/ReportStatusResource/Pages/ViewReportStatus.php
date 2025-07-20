<?php

namespace App\Filament\Admin\Resources\ReportStatusResource\Pages;

use App\Filament\Admin\Resources\ReportStatusResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewReportStatus extends ViewRecord
{
    protected static string $resource = ReportStatusResource::class;

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
