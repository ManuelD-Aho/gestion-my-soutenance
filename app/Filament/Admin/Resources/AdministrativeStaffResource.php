<?php

    namespace App\Filament\Admin\Resources;

    use App\Enums\GenderEnum;
    use App\Filament\Admin\Resources\AdministrativeStaffResource\Pages;
    use App\Models\AdministrativeStaff;
    use App\Services\UniqueIdGeneratorService;
    use App\Services\UserManagementService;
    use Filament\Forms\Components\DatePicker;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Toggle;
    use Filament\Forms\Form;
    use Filament\Notifications\Notification;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\Action;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\IconColumn;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Model;

    class AdministrativeStaffResource extends Resource
    {
        protected static ?string $model = AdministrativeStaff::class;
        protected static ?string $navigationIcon = 'heroicon-o-user-group';
        protected static ?string $navigationGroup = 'Gestion des Personnes';
        protected static ?string $modelLabel = 'Personnel Administratif';
        protected static ?string $pluralModelLabel = 'Personnel Administratif';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('staff_id')
                        ->label('ID Personnel')
                        ->disabledOn('edit')
                        ->visibleOn('view')
                        ->dehydrateStateUsing(fn (?string $state): string => $state ?? app(UniqueIdGeneratorService::class)->generate('ADM', (int)date('Y'))),
                    TextInput::make('first_name')
                        ->label('Prénom')
                        ->required()
                        ->maxLength(191),
                    TextInput::make('last_name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(191),
                    TextInput::make('professional_email')
                        ->label('Email Professionnel')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->nullable(),
                    TextInput::make('professional_phone')
                        ->label('Téléphone Professionnel')
                        ->maxLength(20)
                        ->nullable(),
                    DatePicker::make('service_assignment_date')
                        ->label('Date d\'affectation')
                        ->nullable(),
                    Textarea::make('key_responsibilities')
                        ->label('Responsabilités clés')
                        ->columnSpanFull()
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
                        ->label('Adresse Personnelle')
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
                    TextInput::make('personal_phone')
                        ->label('Téléphone Personnel')
                        ->maxLength(20)
                        ->nullable(),
                    TextInput::make('personal_secondary_email')
                        ->label('Email Personnel Secondaire')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),
                    Toggle::make('is_active')
                        ->label('Profil Actif')
                        ->default(true)
                        ->helperText('Désactiver pour archiver le profil sans le supprimer.'),
                    DatePicker::make('end_date')
                        ->label('Date de fin d\'activité')
                        ->nullable()
                        ->visible(fn (Toggle $component) => !$component->getState()),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('staff_id')
                        ->label('ID Personnel')
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
                    TextColumn::make('professional_email')
                        ->label('Email Pro')
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
                        ->visible(fn (AdministrativeStaff $record): bool => !$record->user)
                        ->action(function (AdministrativeStaff $record) {
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
                'index' => Pages\ListAdministrativeStaffs::route('/'),
                'create' => Pages\CreateAdministrativeStaff::route('/create'),
                'edit' => Pages\EditAdministrativeStaff::route('/{record}/edit'),
                'view' => Pages\ViewAdministrativeStaff::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['staff_id', 'first_name', 'last_name', 'professional_email'];
        }
    }