<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\GenderEnum;
use App\Filament\Admin\Resources\StudentResource\Pages;
use App\Models\Student;
use App\Services\UniqueIdGeneratorService;
use App\Services\UserManagementService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get; // Ajout de l'import Get
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Gestion des Personnes';

    protected static ?string $modelLabel = 'Étudiant';

    protected static ?string $pluralModelLabel = 'Étudiants';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('student_card_number')
                    ->label('Numéro Carte Étudiant')
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->disabledOn('edit')
                    ->visibleOn('view')
                    ->dehydrateStateUsing(fn (?string $state): string => $state ?? app(UniqueIdGeneratorService::class)->generate('ETU', (int) date('Y'))),
                TextInput::make('first_name')
                    ->label('Prénom')
                    ->required()
                    ->maxLength(191),
                TextInput::make('last_name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(191),
                TextInput::make('email_contact_personnel')
                    ->label('Email Personnel (pour compte)')
                    ->email()
                    ->unique(ignoreRecord: true) // Simplification ici
                    ->maxLength(255)
                    ->nullable(),
                Select::make('user_id')
                    ->label('Compte Utilisateur Lié')
                    ->relationship('user', 'email')
                    ->nullable()
                    ->disabledOn('edit')
                    ->helperText('Un compte utilisateur est nécessaire pour se connecter à la plateforme.'),
                DatePicker::make('date_of_birth')
                    ->label('Date de naissance')
                    ->nullable(),
                TextInput::make('place_of_birth')
                    ->label('Lieu de naissance')
                    ->maxLength(100)
                    ->nullable(),
                TextInput::make('country_of_birth')
                    ->label('Pays de naissance')
                    ->maxLength(50)
                    ->nullable(),
                TextInput::make('nationality')
                    ->label('Nationalité')
                    ->maxLength(50)
                    ->nullable(),
                Select::make('gender')
                    ->label('Genre')
                    ->options(GenderEnum::class)
                    ->nullable(),
                Textarea::make('address')
                    ->label('Adresse Postale')
                    ->columnSpanFull()
                    ->nullable(),
                TextInput::make('city')
                    ->label('Ville')
                    ->maxLength(100)
                    ->nullable(),
                TextInput::make('postal_code')
                    ->label('Code Postal')
                    ->maxLength(20)
                    ->nullable(),
                TextInput::make('phone')
                    ->label('Téléphone Personnel')
                    ->maxLength(20)
                    ->nullable(),
                TextInput::make('secondary_email')
                    ->label('Email Secondaire')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('emergency_contact_name')
                    ->label('Contact d\'urgence (Nom)')
                    ->maxLength(100)
                    ->nullable(),
                TextInput::make('emergency_contact_phone')
                    ->label('Contact d\'urgence (Téléphone)')
                    ->maxLength(20)
                    ->nullable(),
                TextInput::make('emergency_contact_relation')
                    ->label('Contact d\'urgence (Relation)')
                    ->maxLength(50)
                    ->nullable(),
                Toggle::make('is_active')
                    ->label('Profil Actif')
                    ->default(true)
                    ->live() // Ajout de live() pour la réactivité
                    ->helperText('Désactiver pour archiver le profil sans le supprimer.'),
                DatePicker::make('end_date')
                    ->label('Date de fin de scolarité')
                    ->nullable()
                    ->visible(fn (Get $get): bool => ! $get('is_active')), // Correction ici
            ]);
    }

    public static function table(Table $table): Table
    {
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
                    ->placeholder('Non lié')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('activate_user_account')
                    ->label('Activer Compte Utilisateur')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (Student $record): bool => ! $record->user)
                    ->action(function (Student $record) {
                        try {
                            app(UserManagementService::class)->activateAccount($record);
                            Notification::make()
                                ->title('Compte utilisateur activé')
                                ->body("Un compte a été créé et lié à {$record->first_name} {$record->last_name}. Les identifiants ont été envoyés par email.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erreur lors de l\'activation du compte')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
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

    public static function getGloballySearchableAttributes(): array
    {
        return ['student_card_number', 'first_name', 'last_name', 'email_contact_personnel'];
    }
}