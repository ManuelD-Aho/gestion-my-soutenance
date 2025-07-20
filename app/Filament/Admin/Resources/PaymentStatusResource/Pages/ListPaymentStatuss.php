<?php

namespace App\Filament\Admin\Resources\PaymentStatusResource\Pages;

use App\Filament\Admin\Resources\PaymentStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentStatuss extends ListRecords
{
    protected static string $resource = PaymentStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
