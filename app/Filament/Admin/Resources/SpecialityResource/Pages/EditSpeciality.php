<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SpecialityResource\Pages;

use App\Filament\Admin\Resources\SpecialityResource;
use Filament\Resources\Pages\EditRecord;

class EditSpeciality extends EditRecord
{
    protected static string $resource = SpecialityResource::class;
}
