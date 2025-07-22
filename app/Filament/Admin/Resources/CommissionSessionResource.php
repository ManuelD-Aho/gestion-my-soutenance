<?php

    namespace App\Filament\Admin\Resources;

    use App\Enums\CommissionSessionModeEnum;
    use App\Enums\CommissionSessionStatusEnum;
    use App\Filament\Admin\Resources\CommissionSessionResource\Pages;
    use App\Models\CommissionSession;
    use App\Services\CommissionFlowService;
    use App\Services\UniqueIdGeneratorService;
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
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;

    class CommissionSessionResource extends Resource
    {
        protected static ?string $model = CommissionSession::class;
        protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
        protected static ?string $navigationGroup = 'Gestion des Commissions';
        protected static ?string $modelLabel = 'Session de Commission';
        protected static ?string $pluralModelLabel = 'Sessions de Commission';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('session_id')
                        ->label('ID Session')
                        ->disabledOn('edit')
                        ->visibleOn('view')
                        ->dehydrateStateUsing(fn (?string $state): string => $state ?? app(UniqueIdGeneratorService::class)->generate('SESS', (int)date('Y'))),
                    TextInput::make('name')
                        ->label('Nom de la session')
                        ->required()
                        ->maxLength(255),
                    DateTimePicker::make('start_date')
                        ->label('Date de début')
                        ->required(),
                    DateTimePicker::make('end_date_planned')
                        ->label('Date de fin prévue')
                        ->required()
                        ->afterOrEqual('start_date'),
                    Select::make('president_teacher_id')
                        ->label('Président')
                        ->relationship('president', 'last_name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('mode')
                        ->label('Mode')
                        ->options(CommissionSessionModeEnum::class)
                        ->required(),
                    Select::make('status')
                        ->label('Statut')
                        ->options(CommissionSessionStatusEnum::class)
                        ->disabledOn('create')
                        ->default(CommissionSessionStatusEnum::PLANNED),
                    TextInput::make('required_voters_count')
                        ->label('Nombre de votants requis')
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                    MultiSelect::make('teachers')
                        ->label('Membres de la Commission')
                        ->relationship('teachers', 'last_name')
                        ->preload()
                        ->helperText('Sélectionnez les enseignants membres de cette session.'),
                    MultiSelect::make('reports')
                        ->label('Rapports de la Session')
                        ->relationship('reports', 'title', fn (Builder $query) => $query->where('status', \App\Enums\ReportStatusEnum::IN_COMMISSION_REVIEW))
                        ->preload()
                        ->helperText('Sélectionnez les rapports à évaluer dans cette session (uniquement ceux en statut "En Commission").'),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('session_id')
                        ->label('ID Session')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('name')
                        ->label('Nom')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('start_date')
                        ->label('Début')
                        ->dateTime()
                        ->sortable(),
                    TextColumn::make('president.first_name')
                        ->label('Prénom Président')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('president.last_name')
                        ->label('Nom Président')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('mode')
                        ->label('Mode'),
                    TextColumn::make('status')
                        ->label('Statut'),
                    TextColumn::make('reports_count')
                        ->counts('reports')
                        ->label('Nb Rapports'),
                ])
                ->filters([
                    //
                ])
                ->actions([
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('start_session')
                        ->label('Démarrer Session')
                        ->icon('heroicon-o-play')
                        ->color('primary')
                        ->visible(fn (CommissionSession $record): bool => $record->status === CommissionSessionStatusEnum::PLANNED)
                        ->action(function (CommissionSession $record) {
                            $record->status = CommissionSessionStatusEnum::IN_PROGRESS;
                            $record->save();
                            Notification::make()
                                ->title('Session démarrée')
                                ->body("La session '{$record->name}' est maintenant en cours.")
                                ->success()
                                ->send();
                        }),
                    Action::make('close_session')
                        ->label('Clôturer Session')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->visible(fn (CommissionSession $record): bool => $record->status === CommissionSessionStatusEnum::IN_PROGRESS)
                        ->requiresConfirmation()
                        ->action(function (CommissionSession $record) {
                            try {
                                app(CommissionFlowService::class)->closeSession($record, auth()->user());
                                Notification::make()
                                    ->title('Session clôturée avec succès')
                                    ->body("La session '{$record->name}' a été clôturée et les rapports finalisés.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Erreur lors de la clôture de la session')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('generate_pv')
                        ->label('Générer PV')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->visible(fn (CommissionSession $record): bool => $record->status === CommissionSessionStatusEnum::CLOSED && !$record->pvs()->exists())
                        ->action(function (CommissionSession $record) {
                            try {
                                app(CommissionFlowService::class)->generatePv($record, auth()->user());
                                Notification::make()
                                    ->title('PV généré')
                                    ->body("Le Procès-Verbal pour la session '{$record->name}' a été généré.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Erreur lors de la génération du PV')
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
                'index' => Pages\ListCommissionSessions::route('/'),
                'create' => Pages\CreateCommissionSession::route('/create'),
                'edit' => Pages\EditCommissionSession::route('/{record}/edit'),
                'view' => Pages\ViewCommissionSession::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name', 'session_id'];
        }
    }