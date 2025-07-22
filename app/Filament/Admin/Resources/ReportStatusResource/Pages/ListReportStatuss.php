<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ReportStatusResource\Pages;

use App\Filament\Admin\Resources\ReportStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportStatuss extends ListRecords
{
    protected static string $resource = ReportStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
