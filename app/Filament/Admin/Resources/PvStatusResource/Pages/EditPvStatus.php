<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PvStatusResource\Pages;

use App\Filament\Admin\Resources\PvStatusResource;
use Filament\Resources\Pages\EditRecord;

class EditPvStatus extends EditRecord
{
    protected static string $resource = PvStatusResource::class;
}
