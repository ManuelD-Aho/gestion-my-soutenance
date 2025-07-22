<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\CommissionSessionResource\Pages;

use App\Enums\CommissionSessionStatusEnum;
use App\Enums\ReportStatusEnum;
use App\Enums\VoteDecisionEnum;
use App\Filament\AppPanel\Resources\CommissionSessionResource;
use App\Models\Report;
use App\Models\Teacher; // Ajout de l'import Teacher
use Filament\Actions;
use Filament\Infolists\Components\Badge; // Ajout de l'import Badge
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ViewCommissionSession extends \Filament\Resources\Pages\ViewRecord
{
    protected static string $resource = CommissionSessionResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $isPresident = $user->hasRole('President Commission');
        $record = $this->getRecord();

        return [
            Actions\EditAction::make()
                ->visible(fn () => $isPresident && $record->status === CommissionSessionStatusEnum::PLANNED),
            Actions\Action::make('start_session')
                ->label('Démarrer Session')
                ->icon('heroicon-o-play')
                ->color('success')
                ->visible(fn () => $isPresident && $record->status === CommissionSessionStatusEnum::PLANNED)
                ->requiresConfirmation()
                ->action(function () use ($record, $user) {
                    try {
                        app(\App\Services\CommissionFlowService::class)->startSession($record, $user);
                        \Filament\Notifications\Notification::make()->title('Session démarrée')->body('La session est maintenant en cours.')->success()->send();
                        $this->refreshFormData(['status']);
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                    }
                }),
            Actions\Action::make('close_session')
                ->label('Clôturer Session')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->visible(fn () => $isPresident && $record->status === CommissionSessionStatusEnum::IN_PROGRESS)
                ->requiresConfirmation()
                ->action(function () use ($record, $user) {
                    try {
                        app(\App\Services\CommissionFlowService::class)->closeSession($record, $user);
                        \Filament\Notifications\Notification::make()->title('Session clôturée')->body('La session a été clôturée et les décisions finalisées.')->success()->send();
                        $this->refreshFormData(['status']);
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                    }
                }),
            Actions\Action::make('generate_pv')
                ->label('Générer PV')
                ->icon('heroicon-o-document-text')
                ->color('secondary')
                ->visible(fn () => $isPresident && $record->status === CommissionSessionStatusEnum::CLOSED)
                ->requiresConfirmation()
                ->action(function () use ($record, $user) {
                    try {
                        app(\App\Services\CommissionFlowService::class)->generatePv($record, $user);
                        \Filament\Notifications\Notification::make()->title('PV généré')->body('Le Procès-Verbal a été généré et est en attente d\'approbation.')->success()->send();
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
                Section::make('Détails de la Session')
                    ->schema([
                        TextEntry::make('session_id')->label('ID Session'),
                        TextEntry::make('name')->label('Nom de la Session'),
                        TextEntry::make('start_date')->label('Début')->dateTime(),
                        TextEntry::make('end_date_planned')->label('Fin Prévue')->dateTime(),
                        TextEntry::make('president.full_name')->label('Président'),
                        TextEntry::make('mode')->label('Mode'),
                        TextEntry::make('status')->label('Statut')
                            ->badge()
                            ->colors([
                                'info' => CommissionSessionStatusEnum::PLANNED->value,
                                'warning' => CommissionSessionStatusEnum::IN_PROGRESS->value,
                                'success' => CommissionSessionStatusEnum::CLOSED->value,
                            ]),
                        TextEntry::make('required_voters_count')->label('Votants Requis'),
                    ])->columns(2),

                Section::make('Membres de la Commission')
                    ->schema([
                        RepeatableEntry::make('teachers')
                            ->label('Membres')
                            ->schema([
                                TextEntry::make('full_name')->label('Nom'),
                                TextEntry::make('professional_email')->label('Email'),
                            ])->columns(2),
                    ]),

                Section::make('Rapports Évalués')
                    ->schema([
                        RepeatableEntry::make('reports')
                            ->label('Rapports')
                            ->schema([
                                TextEntry::make('report_id')->label('ID Rapport'),
                                TextEntry::make('title')->label('Titre'),
                                TextEntry::make('student.full_name')->label('Étudiant'),
                                TextEntry::make('status')->label('Statut Final')
                                    ->badge()
                                    ->colors([
                                        'success' => ReportStatusEnum::VALIDATED->value,
                                        'danger' => ReportStatusEnum::REJECTED->value,
                                        'warning' => ReportStatusEnum::NEEDS_CORRECTION->value,
                                    ]),
                                // Afficher les votes pour ce rapport dans cette session
                                RepeatableEntry::make('votes')
                                    ->label('Votes')
                                    ->query(fn (Builder $query, Report $report) => $query->where('report_id', $report->id)->where('commission_session_id', $this->getRecord()->id))
                                    ->schema([
                                        TextEntry::make('teacher.full_name')->label('Votant'),
                                        Badge::make('voteDecision.name')->label('Décision')
                                            ->colors(fn (string $state): string => VoteDecisionEnum::from($state)?->getColor() ?? 'gray'), // Utilisation de getColor()
                                        TextEntry::make('comment')->label('Commentaire'),
                                        TextEntry::make('vote_date')->label('Date Vote')->dateTime(),
                                        TextEntry::make('vote_round')->label('Tour'),
                                    ])->columns(2),
                            ])->columns(2),
                    ]),
            ]);
    }
}