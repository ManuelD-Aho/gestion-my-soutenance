<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources;

use App\Enums\GenderEnum;
use App\Enums\UserAccountStatusEnum;
use App\Filament\AppPanel\Resources\StudentResource\Pages;
use App\Models\Student;
use App\Services\PenaltyService;
use App\Services\UserManagementService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Étudiants';

    protected static ?string $pluralLabel = 'Étudiants';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        if ($user->hasRole('Admin')) {
            return parent::getEloquentQuery();
        }

        if ($user->hasRole('Responsable Scolarite')) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('id', null);
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isRS = $user->hasRole('Responsable Scolarite');
        $isAdmin = $user->hasRole('Admin');

        return $form
            ->schema([
                Section::make('Informations d\'Identification')
                    ->schema([
                        TextInput::make('student_card_number')
                            ->label('Numéro Carte Étudiant')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->disabled(! $isAdmin),
                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(191)
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(191)
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('email_contact_personnel')
                            ->label('Email Personnel (pour le compte)')
                            ->email()
                            ->unique(ignoreRecord: true) // Simplification ici
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isAdmin && ! $isRS),
                    ])->columns(2),

                Section::make('Informations Personnelles')
                    ->schema([
                        DatePicker::make('date_of_birth')
                            ->label('Date de naissance')
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('place_of_birth')
                            ->label('Lieu de naissance')
                            ->maxLength(100)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('country_of_birth')
                            ->label('Pays de naissance')
                            ->maxLength(50)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('nationality')
                            ->label('Nationalité')
                            ->maxLength(50)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        Select::make('gender')
                            ->label('Genre')
                            ->options(GenderEnum::class)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                    ])->columns(2),

                Section::make('Coordonnées et Contact d\'Urgence')
                    ->schema([
                        Textarea::make('address')
                            ->label('Adresse')
                            ->maxLength(255)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(100)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('postal_code')
                            ->label('Code Postal')
                            ->maxLength(20)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('phone')
                            ->label('Téléphone Personnel')
                            ->tel()
                            ->maxLength(20)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('secondary_email')
                            ->label('Email Secondaire')
                            ->email()
                            ->maxLength(255)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('emergency_contact_name')
                            ->label('Contact d\'Urgence (Nom)')
                            ->maxLength(100)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('emergency_contact_phone')
                            ->label('Contact d\'Urgence (Téléphone)')
                            ->tel()
                            ->maxLength(20)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                        TextInput::make('emergency_contact_relation')
                            ->label('Contact d\'Urgence (Relation)')
                            ->maxLength(50)
                            ->nullable()
                            ->disabled(! $isAdmin && ! $isRS),
                    ])->columns(2),

                Section::make('Statut du Compte Utilisateur')
                    ->visible(fn (?Student $record) => $record && ($isRS || $isAdmin))
                    ->schema([
                        Select::make('user_id')
                            ->label('Compte Utilisateur Lié')
                            ->relationship('user', 'email')
                            ->disabled(),
                        Toggle::make('is_active')
                            ->label('Profil Actif')
                            ->disabled(! $isAdmin),
                        DatePicker::make('end_date')
                            ->label('Date de Fin d\'Activité')
                            ->nullable()
                            ->disabled(! $isAdmin),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isRS = $user->hasRole('Responsable Scolarite');
        $isAdmin = $user->hasRole('Admin');

        return $table
            ->columns([
                TextColumn::make('student_card_number')
                    ->label('Numéro Carte')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email_contact_personnel')
                    ->label('Email Personnel')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Compte Utilisateur')
                    ->searchable()
                    ->visible($isAdmin || $isRS),
                IconColumn::make('user.status')
                    ->label('Statut Compte')
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
                    ->tooltip(fn (string $state): string => $state)
                    ->visible($isAdmin || $isRS),
                IconColumn::make('is_active')
                    ->label('Profil Actif')
                    ->boolean()
                    ->visible($isAdmin || $isRS),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        true => 'Actif',
                        false => 'Inactif',
                    ])
                    ->label('Statut du Profil'),
                \Filament\Tables\Filters\SelectFilter::make('user_status')
                    ->options(UserAccountStatusEnum::class)
                    ->query(fn (Builder $query, array $data) => $query->whereHas('user', fn ($q) => $q->where('status', $data['value'])))
                    ->label('Statut du Compte Utilisateur'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => $isAdmin || $isRS),
                Action::make('activate_account')
                    ->label('Activer Compte')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (Student $record) => $isRS && ! $record->user)
                    ->requiresConfirmation()
                    ->action(function (Student $record, UserManagementService $userManagementService, PenaltyService $penaltyService) {
                        try {
                            if (! $penaltyService->checkStudentEligibility($record)) {
                                Notification::make()->title('Activation impossible')->body('L\'étudiant a des pénalités en attente de régularisation.')->danger()->send();

                                return;
                            }
                            if (! $record->enrollments()->whereHas('academicYear', fn ($q) => $q->where('is_active', true))->exists()) {
                                Notification::make()->title('Activation impossible')->body('L\'étudiant n\'est pas inscrit pour l\'année académique active.')->danger()->send();

                                return;
                            }
                            if (! $record->internships()->where('is_validated', true)->exists()) {
                                Notification::make()->title('Activation impossible')->body('L\'étudiant n\'a pas de stage validé.')->danger()->send();

                                return;
                            }

                            $userManagementService->activateStudentAccount($record);
                            Notification::make()->title('Compte activé')->body('Le compte de l\'étudiant a été activé et les identifiants envoyés par email.')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Erreur d\'activation')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'view' => Pages\ViewStudent::route('/{record}'),
        ];
    }
}