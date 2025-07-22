<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PenaltyResource\Pages;

use App\Filament\Admin\Resources\PenaltyResource;
use Filament\Resources\Pages\EditRecord;

class EditPenalty extends EditRecord
{
    protected static string $resource = PenaltyResource::class;
}
