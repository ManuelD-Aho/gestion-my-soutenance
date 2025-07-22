<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\StudentResource\Pages;

use App\Filament\AppPanel\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        if ($user->hasRole('Responsable Scolarite')) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        return [];
    }
}
