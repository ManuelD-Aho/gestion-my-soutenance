<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\CommissionSessionResource\Pages;

use App\Filament\AppPanel\Resources\CommissionSessionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCommissionSession extends EditRecord
{
    protected static string $resource = CommissionSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // L'admin seul peut supprimer
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Session de commission mise à jour')
            ->body('Les informations de la session ont été modifiées avec succès.')
            ->success();
    }
}
