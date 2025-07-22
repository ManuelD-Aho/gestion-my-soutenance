<?php

    namespace App\Filament\Admin\Resources;

    use App\Enums\UserAccountStatusEnum;
    use App\Filament\Admin\Resources\UserResource\Pages;
    use App\Models\User;
    use App\Services\UserManagementService;
    use Filament\Forms\Components\DateTimePicker;
    use Filament\Forms\Components\MultiSelect;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Notifications\Notification;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\Action;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\IconColumn;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Forms\Components\Textarea;
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Str;
    use Lab404\Impersonate\Services\Impersonate;

    class UserResource extends Resource
    {
        protected static ?string $model = User::class;
        protected static ?string $navigationIcon = 'heroicon-o-users';
        protected static ?string $navigationGroup = 'Gestion des Accès';
        protected static ?string $modelLabel = 'Utilisateur';
        protected static ?string $pluralModelLabel = 'Utilisateurs';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('user_id')
                        ->label('ID Utilisateur')
                        ->disabled()
                        ->visibleOn('view'),
                    TextInput::make('name')
                        ->label('Nom d\'affichage')
                        ->required()
                        ->maxLength(191),
                    TextInput::make('email')
                        ->label('Email (Login)')
                        ->required()
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->maxLength(191),
                    TextInput::make('password')
                        ->label('Mot de passe')
                        ->password()
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->confirmed()
                        ->maxLength(191),
                    TextInput::make('password_confirmation')
                        ->label('Confirmer le mot de passe')
                        ->password()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(191),
                    Select::make('status')
                        ->label('Statut du compte')
                        ->options(UserAccountStatusEnum::class)
                        ->required(),
                    MultiSelect::make('roles')
                        ->label('Rôles')
                        ->relationship('roles', 'name')
                        ->preload()
                        ->searchable()
                        ->required(),
                    DateTimePicker::make('email_verified_at')
                        ->label('Email Vérifié Le')
                        ->nullable()
                        ->disabled(),
                    TextInput::make('failed_login_attempts')
                        ->label('Tentatives de connexion échouées')
                        ->numeric()
                        ->disabled(),
                    DateTimePicker::make('account_locked_until')
                        ->label('Compte Bloqué Jusqu\'à')
                        ->nullable()
                        ->disabled(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('user_id')
                        ->label('ID Utilisateur')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('name')
                        ->label('Nom')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('email')
                        ->label('Email')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('status')
                        ->label('Statut'),
                    TextColumn::make('roles.name')
                        ->label('Rôles')
                        ->badge(),
                    IconColumn::make('email_verified_at')
                        ->label('Email Vérifié')
                        ->boolean()
                        ->placeholder('Non'),
                ])
                ->filters([
                    Select::make('status')
                        ->label('Statut')
                        ->options(UserAccountStatusEnum::class),
                    Select::make('roles')
                        ->label('Rôle')
                        ->relationship('roles', 'name'),
                ])
                ->actions([
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('reset_password')
                        ->label('Réinitialiser Mot de Passe')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->form([
                            TextInput::make('new_password')
                                ->label('Nouveau mot de passe')
                                ->password()
                                ->required()
                                ->confirmed()
                                ->maxLength(191),
                            TextInput::make('new_password_confirmation')
                                ->label('Confirmer le nouveau mot de passe')
                                ->password()
                                ->required()
                                ->maxLength(191),
                        ])
                        ->action(function (array $data, User $record) {
                            try {
                                app(UserManagementService::class)->resetPassword($record, $data['new_password']);
                                Notification::make()
                                    ->title('Mot de passe réinitialisé')
                                    ->body("Le mot de passe de {$record->email} a été réinitialisé.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Erreur lors de la réinitialisation du mot de passe')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('change_status')
                        ->label('Changer Statut')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->form([
                            Select::make('new_status')
                                ->label('Nouveau Statut')
                                ->options(UserAccountStatusEnum::class)
                                ->required()
                                ->default(fn (User $record) => $record->status),
                            Textarea::make('reason')
                                ->label('Raison du changement')
                                ->nullable()
                                ->columnSpanFull(),
                        ])
                        ->action(function (array $data, User $record) {
                            try {
                                app(UserManagementService::class)->updateUserStatus($record, UserAccountStatusEnum::from($data['new_status']), $data['reason']);
                                Notification::make()
                                    ->title('Statut utilisateur mis à jour')
                                    ->body("Le statut de {$record->email} a été changé en {$data['new_status']}.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Erreur lors du changement de statut')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('impersonate')
                        ->label('Impersonate')
                        ->icon('heroicon-o-user-circle')
                        ->color('gray')
                        ->visible(fn (User $record): bool => auth()->user()->canImpersonate() && $record->canBeImpersonated())
                        ->action(function (User $record) {
                            app(Impersonate::class)->take(auth()->user(), $record);
                            return redirect()->route('filament.app.pages.dashboard');
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
                'index' => Pages\ListUsers::route('/'),
                'create' => Pages\CreateUser::route('/create'),
                'edit' => Pages\EditUser::route('/{record}/edit'),
                'view' => Pages\ViewUser::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['user_id', 'name', 'email'];
        }
    }
