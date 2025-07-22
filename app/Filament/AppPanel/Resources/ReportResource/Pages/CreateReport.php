<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\ReportResource\Pages;

use App\Enums\ReportStatusEnum;
use App\Filament\AppPanel\Resources\ReportResource;
use App\Models\AcademicYear;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student) {
            Notification::make()->title('Erreur')->body('Profil étudiant non trouvé.')->danger()->send();
            $this->halt(); // Stop creation process
        }

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        if (! $activeAcademicYear) {
            Notification::make()->title('Erreur')->body('Année académique active non configurée.')->danger()->send();
            $this->halt(); // Stop creation process
        }

        $data['student_id'] = $student->id;
        $data['academic_year_id'] = $activeAcademicYear->id;
        $data['status'] = ReportStatusEnum::DRAFT; // New reports start as draft
        $data['last_modified_date'] = now();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Rapport créé en brouillon')
            ->body('Votre nouveau rapport a été enregistré en tant que brouillon.')
            ->success();
    }
}
