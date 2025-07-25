<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\InternshipResource\Pages;

use App\Filament\AppPanel\Resources\InternshipResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInternship extends EditRecord
{
    protected static string $resource = InternshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // L'admin seul peut supprimer
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Stage mis à jour')
            ->body('Les informations du stage ont été modifiées avec succès.')
            ->success();
    }
}
