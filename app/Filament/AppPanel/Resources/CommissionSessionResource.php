<?php

    namespace App\Filament\AppPanel\Resources;

    use App\Filament\AppPanel\Resources\CommissionSessionResource\Pages;
    use App\Models\CommissionSession;
    use App\Models\Teacher;
    use App\Models\Report;
    use App\Enums\CommissionSessionModeEnum;
    use App\Enums\CommissionSessionStatusEnum;
    use Filament\Forms\Form;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\DateTimePicker;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\Section;
    use Filament\Resources\Resource;
    use Filament\Tables\Table;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Columns\BadgeColumn;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\Action;
    use Filament\Notifications\Notification;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Database\Eloquent\Builder;
    use App\Services\CommissionFlowService;
    use App\Enums\ReportStatusEnum;
    use App\Enums\VoteDecisionEnum;

    class CommissionSessionResource extends Resource
    {
        protected static ?string $model = CommissionSession::class;
        protected static ?string $navigationIcon = 'heroicon-o-calendar';
        protected static ?string $navigationLabel = 'Sessions de Commission';
        protected static ?string $pluralLabel = 'Sessions de Commission';

        public static function getEloquentQuery(): Builder
        {
            $user = Auth::user();
            if ($user->hasRole('Admin')) {
                return parent::getEloquentQuery();
            }

            if ($user->hasAnyRole(['Membre Commission', 'President Commission'])) {
                return parent::getEloquentQuery()
                    ->whereHas('teachers', fn (Builder $query) => $query->where('teacher_id', $user->teacher->id))
                    ->orWhere('president_teacher_id', $user->teacher->id);
            }

            return parent::getEloquentQuery()->where('id', null); // No access for other roles
        }

        public static function form(Form $form): Form
        {
            $user = Auth::user();
            $isPresident = $user->hasRole('President Commission');

            return $form
                ->schema([
                    Section::make('Informations Générales de la Session')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nom de la Session')
                                ->required()
                                ->maxLength(255)
                                ->disabled(! $isPresident),
                            DateTimePicker::make('start_date')
                                ->label('Date et Heure de Début')
                                ->required()
                                ->disabled(! $isPresident),
                            DateTimePicker::make('end_date_planned')
                                ->label('Date et Heure de Fin Prévue')
                                ->required()
                                ->afterOrEqual('start_date')
                                ->disabled(! $isPresident),
                            Select::make('president_teacher_id')
                                ->label('Président de la Commission')
                                ->relationship('president', 'last_name')
                                ->getOptionLabelFromRecordUsing(fn (Teacher $record) => "{$record->first_name} {$record->last_name}")
                                ->required()
                                ->disabled(! $isPresident),
                            Select::make('mode')
                                ->label('Mode de la Session')
                                ->options(CommissionSessionModeEnum::class)
                                ->required()
                                ->disabled(! $isPresident),
                            TextInput::make('required_voters_count')
                                ->label('Nombre de Votants Requis')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->disabled(! $isPresident),
                            Select::make('status')
                                ->label('Statut de la Session')
                                ->options(CommissionSessionStatusEnum::class)
                                ->disabled() // Statut géré par le workflow
                                ->default(CommissionSessionStatusEnum::PLANNED),
                        ])->columns(2),

                    Section::make('Membres de la Commission')
                        ->description('Ajoutez les enseignants qui participeront à cette session.')
                        ->schema([
                            Select::make('teachers')
                                ->label('Sélectionner les Membres')
                                ->relationship('teachers', 'last_name')
                                ->getOptionLabelFromRecordUsing(fn (Teacher $record) => "{$record->first_name} {$record->last_name}")
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->disabled(! $isPresident),
                        ]),

                    Section::make('Rapports à Évaluer')
                        ->description('Ajoutez les rapports qui seront évalués lors de cette session. Seuls les rapports "En Commission" sont éligibles.')
                        ->schema([
                            Select::make('reports')
                                ->label('Sélectionner les Rapports')
                                ->relationship('reports', 'title', fn (Builder $query) => $query->where('status', ReportStatusEnum::IN_COMMISSION_REVIEW))
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->disabled(! $isPresident),
                        ]),
                ]);
        }

        public static function table(Table $table): Table
        {
            $user = Auth::user();
            $isPresident = $user->hasRole('President Commission');
            $isMember = $user->hasRole('Membre Commission');

            return $table
                ->columns([
                    TextColumn::make('session_id')
                        ->label('ID Session')
                        ->searchable(),
                    TextColumn::make('name')
                        ->label('Nom')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('start_date')
                        ->label('Début')
                        ->dateTime()
                        ->sortable(),
                    TextColumn::make('president.full_name')
                        ->label('Président')
                        ->searchable()
                        ->sortable(),
                    BadgeColumn::make('status')
                        ->label('Statut')
                        ->colors([
                            'info' => CommissionSessionStatusEnum::PLANNED->value,
                            'warning' => CommissionSessionStatusEnum::IN_PROGRESS->value,
                            'success' => CommissionSessionStatusEnum::CLOSED->value,
                        ]),
                    TextColumn::make('reports_count')
                        ->counts('reports')
                        ->label('Rapports'),
                    TextColumn::make('teachers_count')
                        ->counts('teachers')
                        ->label('Membres'),
                ])
                ->filters([
                    \Filament\Tables\Filters\SelectFilter::make('status')
                        ->options(CommissionSessionStatusEnum::class)
                        ->label('Filtrer par Statut'),
                    \Filament\Tables\Filters\SelectFilter::make('mode')
                        ->options(CommissionSessionModeEnum::class)
                        ->label('Filtrer par Mode'),
                ])
                ->actions([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (CommissionSession $record) => $isPresident && $record->status === CommissionSessionStatusEnum::PLANNED),
                    Action::make('start_session')
                        ->label('Démarrer Session')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn (CommissionSession $record) => $isPresident && $record->status === CommissionSessionStatusEnum::PLANNED)
                        ->requiresConfirmation()
                        ->action(function (CommissionSession $record, CommissionFlowService $commissionFlowService) use ($user) {
                            try {
                                $commissionFlowService->startSession($record, $user);
                                Notification::make()->title('Session démarrée')->body('La session est maintenant en cours.')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                            }
                        }),
                    Action::make('close_session')
                        ->label('Clôturer Session')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->visible(fn (CommissionSession $record) => $isPresident && $record->status === CommissionSessionStatusEnum::IN_PROGRESS)
                        ->requiresConfirmation()
                        ->action(function (CommissionSession $record, CommissionFlowService $commissionFlowService) use ($user) {
                            try {
                                $commissionFlowService->closeSession($record, $user);
                                Notification::make()->title('Session clôturée')->body('La session a été clôturée et les décisions finalisées.')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                            }
                        }),
                    Action::make('record_vote')
                        ->label('Enregistrer Vote')
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->visible(fn (CommissionSession $record) => $record->status === CommissionSessionStatusEnum::IN_PROGRESS && $isMember)
                        ->form([
                            Select::make('report_id')
                                ->label('Rapport')
                                ->options(fn (CommissionSession $record) => $record->reports->pluck('title', 'id'))
                                ->required(),
                            Select::make('decision')
                                ->label('Décision')
                                ->options(VoteDecisionEnum::class)
                                ->required()
                                ->reactive(),
                            Textarea::make('comment')
                                ->label('Commentaire')
                                ->visible(fn (\Filament\Forms\Get $get) => in_array($get('decision'), [VoteDecisionEnum::REJECTED->value, VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value]))
                                ->required(fn (\Filament\Forms\Get $get) => in_array($get('decision'), [VoteDecisionEnum::REJECTED->value, VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value])),
                        ])
                        ->action(function (array $data, CommissionSession $record, CommissionFlowService $commissionFlowService) use ($user) {
                            try {
                                $report = Report::find($data['report_id']);
                                $commissionFlowService->recordVote($record, $report, $user, VoteDecisionEnum::from($data['decision']), $data['comment']);
                                Notification::make()->title('Vote enregistré')->body('Votre vote a été enregistré avec succès.')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                            }
                        }),
                    Action::make('generate_pv')
                        ->label('Générer PV')
                        ->icon('heroicon-o-document-text')
                        ->color('secondary')
                        ->visible(fn (CommissionSession $record) => $record->status === CommissionSessionStatusEnum::CLOSED && $isPresident)
                        ->requiresConfirmation()
                        ->action(function (CommissionSession $record, CommissionFlowService $commissionFlowService) use ($user) {
                            try {
                                $commissionFlowService->generatePv($record, $user);
                                Notification::make()->title('PV généré')->body('Le Procès-Verbal a été généré et est en attente d\'approbation.')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                            }
                        }),
                ])
                ->bulkActions([
                    // Pas d'actions de masse pour les sessions de commission
                ]);
        }

        public static function getRelations(): array
        {
            return [
                // RelationManagers pour les rapports et les membres si nécessaire
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
    }