<?php

namespace App\Filament\Admin\Resources\PaymentStatusResource\Pages;

use App\Filament\Admin\Resources\PaymentStatusResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentStatus extends ViewRecord
{
    protected static string $resource = PaymentStatusResource::class;

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
