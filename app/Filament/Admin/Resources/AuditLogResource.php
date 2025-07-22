<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\AuditLogResource\Pages;
    use App\Models\Action;
    use App\Models\AuditLog;
    use App\Models\User;
    use Filament\Forms\Components\KeyValue;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Filters\SelectFilter;
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Model;
    use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

    class AuditLogResource extends Resource
    {
        protected static ?string $model = AuditLog::class;
        protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
        protected static ?string $navigationGroup = 'Supervision & Audit';
        protected static ?string $modelLabel = 'Journal d\'Audit';
        protected static ?string $pluralModelLabel = 'Journaux d\'Audit';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('log_id')
                        ->label('ID Log')
                        ->disabled(),
                    Select::make('user_id')
                        ->label('Utilisateur')
                        ->relationship('user', 'email')
                        ->disabled(),
                    Select::make('action_id')
                        ->label('Action')
                        ->relationship('action', 'label')
                        ->disabled(),
                    TextInput::make('action_date')
                        ->label('Date Action')
                        ->disabled(),
                    TextInput::make('ip_address')
                        ->label('Adresse IP')
                        ->disabled(),
                    Textarea::make('user_agent')
                        ->label('User Agent')
                        ->columnSpanFull()
                        ->disabled(),
                    TextInput::make('auditable_type')
                        ->label('Type Entité')
                        ->disabled(),
                    TextInput::make('auditable_id')
                        ->label('ID Entité')
                        ->disabled(),
                    KeyValue::make('details')
                        ->label('Détails')
                        ->disabled()
                        ->columnSpanFull(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('log_id')
                        ->label('ID Log')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('user.email')
                        ->label('Utilisateur')
                        ->placeholder('N/A')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('action.label')
                        ->label('Action')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('action_date')
                        ->label('Date')
                        ->dateTime()
                        ->sortable(),
                    TextColumn::make('auditable_type')
                        ->label('Entité Type')
                        ->limit(20)
                        ->sortable(),
                    TextColumn::make('auditable_id')
                        ->label('Entité ID')
                        ->sortable(),
                    TextColumn::make('details')
                        ->label('Détails')
                        ->json()
                        ->limit(50)
                        ->tooltip(fn (AuditLog $record): string => json_encode($record->details, JSON_PRETTY_PRINT)),
                ])
                ->filters([
                    SelectFilter::make('user_id')
                        ->label('Utilisateur')
                        ->options(User::all()->pluck('email', 'id')->toArray()),
                    SelectFilter::make('action_id')
                        ->label('Action')
                        ->options(Action::all()->pluck('label', 'id')->toArray()),
                    SelectFilter::make('auditable_type')
                        ->label('Type d\'Entité')
                        ->options([
                            'App\Models\Report' => 'Rapport',
                            'App\Models\User' => 'Utilisateur',
                            'App\Models\CommissionSession' => 'Session Commission',
                            'App\Models\Pv' => 'Procès-Verbal',
                            'App\Models\Penalty' => 'Pénalité',
                            'App\Models\Document' => 'Document',
                            'App\Models\Student' => 'Étudiant',
                            'App\Models\Teacher' => 'Enseignant',
                            'App\Models\AdministrativeStaff' => 'Personnel Admin',
                        ]),
                    DateRangeFilter::make('action_date')
                        ->label('Date de l\'Action'),
                ])
                ->actions([
                    ViewAction::make(),
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
                'index' => Pages\ListAuditLogs::route('/'),
                'view' => Pages\ViewAuditLog::route('/{record}'),
            ];
        }

        public static function canCreate(): bool
        {
            return false;
        }

        public static function canEdit(Model $record): bool
        {
            return false;
        }

        public static function canDelete(Model $record): bool
        {
            return false;
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['log_id', 'user.email', 'action.label'];
        }
    }
