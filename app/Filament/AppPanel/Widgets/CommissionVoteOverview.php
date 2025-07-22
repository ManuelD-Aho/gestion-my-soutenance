<?php

    namespace App\Filament\AppPanel\Widgets;

    use Filament\Widgets\Widget;
    use Filament\Tables\Table;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Columns\BadgeColumn;
    use Filament\Tables\Actions\Action;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Database\Eloquent\Builder;
    use App\Models\CommissionSession;
    use App\Models\Vote;
    use App\Enums\CommissionSessionStatusEnum;
    use App\Enums\VoteDecisionEnum;

    class CommissionVoteOverview extends Widget
    {
        protected static string $view = 'filament.app-panel.widgets.commission-vote-overview';
        protected int | string | array $columnSpan = 'full';
        protected static ?int $sort = 1;

        public function table(Table $table): Table
        {
            $user = Auth::user();
            $teacherId = $user->teacher->id ?? null;

            return $table
                ->query(
                    CommissionSession::query()
                        ->whereHas('teachers', fn (Builder $query) => $query->where('teacher_id', $teacherId))
                        ->where('status', CommissionSessionStatusEnum::IN_PROGRESS)
                )
                ->columns([
                    TextColumn::make('name')
                        ->label('Session')
                        ->searchable(),
                    TextColumn::make('start_date')
                        ->label('Date')
                        ->date(),
                    TextColumn::make('reports_count')
                        ->counts('reports')
                        ->label('Rapports à évaluer'),
                    TextColumn::make('my_vote_status')
                        ->label('Mon Statut de Vote')
                        ->badge()
                        ->getStateUsing(function (CommissionSession $record) use ($teacherId) {
                            $reportsInSession = $record->reports;
                            $totalReports = $reportsInSession->count();
                            if ($totalReports === 0) {
                                return 'N/A';
                            }

                            $votedReports = 0;
                            foreach ($reportsInSession as $report) {
                                if ($report->votes()->where('teacher_id', $teacherId)->where('commission_session_id', $record->id)->exists()) {
                                    $votedReports++;
                                }
                            }

                            if ($votedReports === $totalReports) {
                                return 'Tous votés';
                            } elseif ($votedReports > 0) {
                                return "{$votedReports}/{$totalReports} votés";
                            } else {
                                return 'En attente';
                            }
                        })
                        ->colors([
                            'success' => 'Tous votés',
                            'warning' => fn (string $state): bool => str_contains($state, '/'),
                            'info' => 'En attente',
                        ]),
                ])
                ->actions([
                    Action::make('view_session')
                        ->label('Voir Session')
                        ->url(fn (CommissionSession $record): string => CommissionSessionResource::getUrl('view', ['record' => $record])),
                    Action::make('record_missing_votes')
                        ->label('Voter les Rapports Restants')
                        ->visible(fn (CommissionSession $record) => $record->status === CommissionSessionStatusEnum::IN_PROGRESS)
                        ->form(function (CommissionSession $record) use ($teacherId) {
                            $reportsToVote = $record->reports->filter(function ($report) use ($teacherId, $record) {
                                return !$report->votes()->where('teacher_id', $teacherId)->where('commission_session_id', $record->id)->exists();
                            });

                            if ($reportsToVote->isEmpty()) {
                                return [
                                    \Filament\Forms\Components\Placeholder::make('no_reports')
                                        ->content('Tous les rapports de cette session ont été votés par vous.'),
                                ];
                            }

                            $schema = [];
                            foreach ($reportsToVote as $report) {
                                $schema[] = Section::make("Vote pour le rapport: {$report->title}")
                                    ->schema([
                                        TextInput::make("votes.{$report->id}.report_title")
                                            ->label('Rapport')
                                            ->default($report->title)
                                            ->disabled(),
                                        Select::make("votes.{$report->id}.decision")
                                            ->label('Décision')
                                            ->options(VoteDecisionEnum::class)
                                            ->required()
                                            ->reactive(),
                                        Textarea::make("votes.{$report->id}.comment")
                                            ->label('Commentaire')
                                            ->visible(fn (\Filament\Forms\Get $get) => in_array($get("votes.{$report->id}.decision"), [VoteDecisionEnum::REJECTED->value, VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value]))
                                            ->required(fn (\Filament\Forms\Get $get) => in_array($get("votes.{$report->id}.decision"), [VoteDecisionEnum::REJECTED->value, VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value])),
                                    ]);
                            }
                            return $schema;
                        })
                        ->action(function (array $data, CommissionSession $record, \App\Services\CommissionFlowService $commissionFlowService) use ($user) {
                            try {
                                $successCount = 0;
                                foreach ($data['votes'] as $reportId => $voteData) {
                                    $report = Report::find($reportId);
                                    if ($report) {
                                        $commissionFlowService->recordVote($record, $report, $user, VoteDecisionEnum::from($voteData['decision']), $voteData['comment'] ?? null);
                                        $successCount++;
                                    }
                                }
                                Notification::make()->title("{$successCount} votes enregistrés")->body('Vos votes ont été enregistrés avec succès.')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Erreur lors de l\'enregistrement des votes')->body($e->getMessage())->danger()->send();
                            }
                        }),
                ]);
        }
    }