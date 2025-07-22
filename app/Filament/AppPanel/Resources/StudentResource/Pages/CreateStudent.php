<?php

    namespace App\Filament\AppPanel\Resources\StudentResource\Pages;

    use App\Filament\AppPanel\Resources\StudentResource;
    use Filament\Resources\Pages\CreateRecord;
    use Illuminate\Support\Facades\Auth;
    use App\Services\UniqueIdGeneratorService;
    use Filament\Notifications\Notification;

    class CreateStudent extends CreateRecord
    {
        protected static string $resource = StudentResource::class;

        protected function mutateFormDataBeforeCreate(array $data): array
        {
            $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
            $data['student_card_number'] = $uniqueIdGeneratorService->generate('ETU', (int)date('Y'));
            $data['is_active'] = true; // New student profile is active by default

            return $data;
        }

        protected function getRedirectUrl(): string
        {
            return $this->getResource()::getUrl('index');
        }

        protected function getCreatedNotification(): ?Notification
        {
            return Notification::make()
                ->title('Fiche étudiant créée')
                ->body('La nouvelle fiche étudiant a été enregistrée avec succès.')
                ->success();
        }
    }