<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PaymentStatusResource\Pages;

use App\Filament\Admin\Resources\PaymentStatusResource;
use Filament\Resources\Pages\EditRecord;

class EditPaymentStatus extends EditRecord
{
    protected static string $resource = PaymentStatusResource::class;
}
