<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ActionResource\Pages;

use App\Filament\Admin\Resources\ActionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAction extends CreateRecord
{
    protected static string $resource = ActionResource::class;
}
