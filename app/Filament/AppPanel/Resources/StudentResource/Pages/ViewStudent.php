<?php

    namespace App\Filament\AppPanel\Resources\StudentResource\Pages;

    use App\Filament\AppPanel\Resources\StudentResource;
    use Filament\Actions;
    use Filament\Infolists\Infolist;
    use Filament\Infolists\Components\TextEntry;
    use Filament\Infolists\Components\IconEntry;
    use Filament\Infolists\Components\Section;
    use Filament\Infolists\Components\ImageEntry;
    use Illuminate\Support\Facades\Auth;
    use App\Enums\GenderEnum;
    use App\Enums\UserAccountStatusEnum;
    use App\Services\UserManagementService;
    use App\Services\PenaltyService;
    use Filament\Notifications\Notification;

    class ViewStudent extends \Filament\Resources\Pages\ViewRecord
    {
        protected static string $resource = StudentResource::class;

        protected function getHeaderActions(): array
        {
            $user = Auth::user();
            $isRS = $user->hasRole('Responsable Scolarite');
            $isAdmin = $user->hasRole('Admin');
            $record = $this->getRecord();

            return [
                Actions\EditAction::make()
                    ->visible(fn () => $isAdmin || $isRS),
                Actions\Action::make('activate_account')
                    ->label('Activer Compte')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn () => $isRS && !$record->user)
                    ->requiresConfirmation()
                    ->action(function () use ($record, $user) {
                        try {
                            // Check eligibility before activation
                            if (!app(PenaltyService::class)->checkStudentEligibility($record)) {
                                Notification::make()->title('Activation impossible')->body('L\'étudiant a des pénalités en attente de régularisation.')->danger()->send();
                                return;
                            }
                            // Check enrollment and internship prerequisites
                            if (!$record->enrollments()->whereHas('academicYear', fn($q) => $q->where('is_active', true))->exists()) {
                                Notification::make()->title('Activation impossible')->body('L\'étudiant n\'est pas inscrit pour l\'année académique active.')->danger()->send();
                                return;
                            }
                            if (!$record->internships()->where('is_validated', true)->exists()) {
                                Notification::make()->title('Activation impossible')->body('L\'étudiant n\'a pas de stage validé.')->danger()->send();
                                return;
                            }

                            app(UserManagementService::class)->activateStudentAccount($record);
                            Notification::make()->title('Compte activé')->body('Le compte de l\'étudiant a été activé et les identifiants envoyés par email.')->success()->send();
                            $this->refreshFormData(['user']); // Refresh user relation
                        } catch (\Throwable $e) {
                            Notification::make()->title('Erreur d\'activation')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ];
        }

        public function infolist(Infolist $infolist): Infolist
        {
            return $infolist
                ->schema([
                    Section::make('Informations d\'Identification')
                        ->schema([
                            TextEntry::make('student_card_number')->label('Numéro Carte Étudiant'),
                            TextEntry::make('first_name')->label('Prénom'),
                            TextEntry::make('last_name')->label('Nom'),
                            TextEntry::make('email_contact_personnel')->label('Email Personnel'),
                            ImageEntry::make('user.profile_photo_url')
                                ->label('Photo de profil')
                                ->circular(),
                        ])->columns(2),

                    Section::make('Informations Personnelles')
                        ->schema([
                            TextEntry::make('date_of_birth')->label('Date de naissance')->date(),
                            TextEntry::make('place_of_birth')->label('Lieu de naissance'),
                            TextEntry::make('country_of_birth')->label('Pays de naissance'),
                            TextEntry::make('nationality')->label('Nationalité'),
                            TextEntry::make('gender')->label('Genre'),
                        ])->columns(2),

                    Section::make('Coordonnées et Contact d\'Urgence')
                        ->schema([
                            TextEntry::make('address')->label('Adresse'),
                            TextEntry::make('city')->label('Ville'),
                            TextEntry::make('postal_code')->label('Code Postal'),
                            TextEntry::make('phone')->label('Téléphone Personnel'),
                            TextEntry::make('secondary_email')->label('Email Secondaire'),
                            TextEntry::make('emergency_contact_name')->label('Contact d\'Urgence (Nom)'),
                            TextEntry::make('emergency_contact_phone')->label('Contact d\'Urgence (Téléphone)'),
                            TextEntry::make('emergency_contact_relation')->label('Contact d\'Urgence (Relation)'),
                        ])->columns(2),

                    Section::make('Statut du Compte Utilisateur')
                        ->visible(fn () => Auth::user()->hasRole('Responsable Scolarite') || Auth::user()->hasRole('Admin'))
                        ->schema([
                            TextEntry::make('user.email')->label('Compte Utilisateur Lié'),
                            IconEntry::make('user.status')->label('Statut du Compte')
                                ->icon(fn (string $state): string => match ($state) {
                                    UserAccountStatusEnum::ACTIVE->value => 'heroicon-o-check-circle',
                                    UserAccountStatusEnum::INACTIVE->value => 'heroicon-o-x-circle',
                                    UserAccountStatusEnum::BLOCKED->value => 'heroicon-o-lock-closed',
                                    UserAccountStatusEnum::PENDING_VALIDATION->value => 'heroicon-o-clock',
                                    UserAccountStatusEnum::ARCHIVED->value => 'heroicon-o-archive-box',
                                    default => 'heroicon-o-question-mark-circle',
                                })
                                ->color(fn (string $state): string => match ($state) {
                                    UserAccountStatusEnum::ACTIVE->value => 'success',
                                    UserAccountStatusEnum::INACTIVE->value, UserAccountStatusEnum::BLOCKED->value => 'danger',
                                    UserAccountStatusEnum::PENDING_VALIDATION->value => 'warning',
                                    UserAccountStatusEnum::ARCHIVED->value => 'gray',
                                    default => 'info',
                                })
                                ->tooltip(fn (string $state): string => $state),
                            IconEntry::make('is_active')->label('Profil Actif')->boolean(),
                            TextEntry::make('end_date')->label('Date de Fin d\'Activité')->date(),
                        ])->columns(2),
                ]);
        }
    }