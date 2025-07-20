<?php

namespace App\Filament\Admin\Resources\ReportSectionResource\Pages;

use App\Filament\Admin\Resources\ReportSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportSections extends ListRecords
{
    protected static string $resource = ReportSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
