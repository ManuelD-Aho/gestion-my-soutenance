<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\DocumentTypeResource\Pages;

use App\Filament\Admin\Resources\DocumentTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentType extends CreateRecord
{
    protected static string $resource = DocumentTypeResource::class;
}
