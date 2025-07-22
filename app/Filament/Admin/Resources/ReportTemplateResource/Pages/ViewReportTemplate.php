<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ReportTemplateResource\Pages;

use App\Filament\Admin\Resources\ReportTemplateResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewReportTemplate extends ViewRecord
{
    protected static string $resource = ReportTemplateResource::class;

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
