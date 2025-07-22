<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\ReportResource\Pages;

use App\Enums\ReportStatusEnum;
use App\Filament\AppPanel\Resources\ReportResource;
use Filament\Actions;
use Filament\Notifications\Notification; // Ajout de l'import
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth; // Ajout de l'import

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $record = $this->getRecord();

        $actions = [];

        // Only student can edit if draft or needs correction
        if ($user->hasRole('Etudiant') && $user->student && $record->student_id === $user->student->id && ($record->status === ReportStatusEnum::DRAFT || $record->status === ReportStatusEnum::NEEDS_CORRECTION)) {
            $actions[] = Actions\DeleteAction::make();
        }

        // Admin can always delete
        if ($user->hasRole('Admin')) {
            $actions[] = Actions\DeleteAction::make();
        }

        return $actions;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Rapport mis à jour')
            ->body('Les modifications de votre rapport ont été sauvegardées.')
            ->success();
    }
}