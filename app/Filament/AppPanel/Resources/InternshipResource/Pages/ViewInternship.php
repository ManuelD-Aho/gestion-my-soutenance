<?php

    namespace App\Filament\AppPanel\Resources\InternshipResource\Pages;

    use App\Filament\AppPanel\Resources\InternshipResource;
    use Filament\Actions;
    use Filament\Infolists\Infolist;
    use Filament\Infolists\Components\TextEntry;
    use Filament\Infolists\Components\IconEntry;
    use Filament\Infolists\Components\Section;
    use Illuminate\Support\Facades\Auth;

    class ViewInternship extends \Filament\Resources\Pages\ViewRecord
    {
        protected static string $resource = InternshipResource::class;

        protected function getHeaderActions(): array
        {
            $user = Auth::user();
            $isRS = $user->hasRole('Responsable Scolarite');
            $record = $this->getRecord();

            return [
                Actions\EditAction::make()
                    ->visible(fn () => $isRS || (Auth::user()->student && Auth::user()->student->id === $record->student_id && !$record->is_validated)),
                Actions\Action::make('validate_internship')
                    ->label('Valider Stage')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn () => $isRS && !$record->is_validated)
                    ->requiresConfirmation()
                    ->action(function () use ($record, $user) {
                        try {
                            $record->is_validated = true;
                            $record->validation_date = now();
                            $record->validated_by_user_id = $user->id;
                            $record->save();
                            \Filament\Notifications\Notification::make()->title('Stage validé')->body('Le stage a été validé avec succès.')->success()->send();
                            $this->refreshFormData(['is_validated', 'validation_date', 'validated_by_user_id']);
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ];
        }

        public function infolist(Infolist $infolist): Infolist
        {
            return $infolist
                ->schema([
                    Section::make('Informations du Stage')
                        ->schema([
                            TextEntry::make('student.full_name')->label('Étudiant'),
                            TextEntry::make('company.name')->label('Entreprise'),
                            TextEntry::make('subject')->label('Sujet du Stage'),
                            TextEntry::make('start_date')->label('Date de Début')->date(),
                            TextEntry::make('end_date')->label('Date de Fin')->date(),
                            TextEntry::make('company_tutor_name')->label('Nom du Tuteur en Entreprise'),
                        ])->columns(2),

                    Section::make('Statut de Validation')
                        ->schema([
                            IconEntry::make('is_validated')->label('Stage Validé')->boolean(),
                            TextEntry::make('validation_date')->label('Date de Validation')->dateTime(),
                            TextEntry::make('validatedBy.email')->label('Validé par'),
                        ])->columns(2),
                ]);
        }
    }