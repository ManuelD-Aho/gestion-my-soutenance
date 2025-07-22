<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\JuryRoleResource\Pages;

use App\Filament\Admin\Resources\JuryRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJuryRoles extends ListRecords
{
    protected static string $resource = JuryRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
