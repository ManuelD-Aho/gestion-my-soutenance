<?php

    namespace App\Filament\AppPanel\Resources\CommissionSessionResource\Pages;

    use App\Filament\AppPanel\Resources\CommissionSessionResource;
    use Filament\Resources\Pages\CreateRecord;
    use Illuminate\Support\Facades\Auth;
    use App\Services\CommissionFlowService;
    use Filament\Notifications\Notification;

    class CreateCommissionSession extends CreateRecord
    {
        protected static string $resource = CommissionSessionResource::class;

        protected function mutateFormDataBeforeCreate(array $data): array
        {
            $user = Auth::user();
            $data['president_teacher_id'] = $user->teacher->id; // Auto-assign current user as president

            return $data;
        }

        protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
        {
            $commissionFlowService = app(CommissionFlowService::class);
            return $commissionFlowService->createSession($data, Auth::user());
        }

        protected function getRedirectUrl(): string
        {
            return $this->getResource()::getUrl('index');
        }

        protected function getCreatedNotification(): ?Notification
        {
            return Notification::make()
                ->title('Session de commission créée')
                ->body('La nouvelle session a été planifiée avec succès.')
                ->success();
        }
    }