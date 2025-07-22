<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\StudentResource\Pages;

use App\Filament\AppPanel\Resources\StudentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Admin');

        $actions = [];

        if ($isAdmin) {
            $actions[] = Actions\DeleteAction::make();
        }

        return $actions;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Fiche étudiant mise à jour')
            ->body('Les informations de l\'étudiant ont été modifiées avec succès.')
            ->success();
    }
}
