<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\EnseignantResource\Pages;

use App\Filament\Admin\Resources\EnseignantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEnseignant extends CreateRecord
{
    protected static string $resource = EnseignantResource::class;
}
