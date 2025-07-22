<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\InternshipResource\Pages;

use App\Filament\AppPanel\Resources\InternshipResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInternship extends CreateRecord
{
    protected static string $resource = InternshipResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if ($user->hasRole('Etudiant') && $user->student) {
            $data['student_id'] = $user->student->id; // Auto-assign student if current user is student
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Stage enregistré')
            ->body('Le stage a été enregistré avec succès.')
            ->success();
    }
}
