<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\CommissionSessionResource\Pages;

use App\Filament\AppPanel\Resources\CommissionSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCommissionSessions extends ListRecords
{
    protected static string $resource = CommissionSessionResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        if ($user->hasRole('President Commission')) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        return [];
    }
}
