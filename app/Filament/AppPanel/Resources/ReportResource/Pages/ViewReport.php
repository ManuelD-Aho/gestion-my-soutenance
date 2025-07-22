<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources\ReportResource\Pages;

use App\Enums\ConformityStatusEnum;
use App\Enums\ReportStatusEnum;
use App\Enums\VoteDecisionEnum;
use App\Filament\AppPanel\Resources\ReportResource;
use App\Models\ConformityCriterion;
use App\Models\Report;
use App\Services\ConformityCheckService;
use App\Services\ReportFlowService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ViewReport extends \Filament\Resources\Pages\ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $record = $this->getRecord();
        $isStudent = $user->hasRole('Etudiant') && $user->student && $record->student_id === $user->student->id;
        $isConformityAgent = $user->hasRole('Agent de Conformite');
        $isCommissionMember = $user->hasAnyRole(['Membre Commission', 'President Commission']);
        $isAdmin = $user->hasRole('Admin');

        $actions = [];

        if ($isStudent && ($record->status === ReportStatusEnum::DRAFT || $record->status === ReportStatusEnum::NEEDS_CORRECTION)) {
            $actions[] = Actions\EditAction::make();
            $actions[] = Actions\Action::make('submit_report')
                ->label('Soumettre le Rapport')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    try {
                        // Re-fetch data from form to ensure latest content is used
                        $data = $this->form->getState();
                        $sectionsData = $data['sections'];
                        app(ReportFlowService::class)->submitReport($record, $sectionsData, $record->version);
                        Notification::make()->title('Rapport soumis avec succès !')->body('Votre rapport a été transmis pour vérification de conformité.')->success()->send();
                        $this->redirect(ReportResource::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()->title('Erreur lors de la soumission')->body($e->getMessage())->danger()->send();
                    }
                });
        }

        if ($isConformityAgent && ($record->status === ReportStatusEnum::SUBMITTED || $record->status === ReportStatusEnum::IN_CONFORMITY_CHECK)) {
            $actions[] = Actions\Action::make('check_conformity')
                ->label('Vérifier Conformité')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->form(function () {
                    $criteria = ConformityCriterion::where('is_active', true)->where('type', 'MANUAL')->get();
                    $schema = [];
                    foreach ($criteria as $criterion) {
                        $schema[] = Section::make($criterion->label)
                            ->description($criterion->description)
                            ->schema([
                                Select::make("criteria_results.{$criterion->id}.status")
                                    ->label('Statut')
                                    ->options(ConformityStatusEnum::class)
                                    ->required()
                                    ->default(ConformityStatusEnum::CONFORME->value),
                                Textarea::make("criteria_results.{$criterion->id}.comment")
                                    ->label('Commentaire')
                                    ->nullable()
                                    ->rows(3),
                            ]);
                    }

                    return $schema;
                })
                ->action(function (array $data) use ($record, $user) {
                    try {
                        app(ConformityCheckService::class)->checkConformity($record, $data['criteria_results'], $user);
                        Notification::make()->title('Vérification de conformité effectuée')->success()->send();
                        $this->refreshFormData(['status']);
                    } catch (\Throwable $e) {
                        Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                    }
                });
        }

        if (($isConformityAgent && $record->status === ReportStatusEnum::IN_CONFORMITY_CHECK) || ($isCommissionMember && $record->status === ReportStatusEnum::IN_COMMISSION_REVIEW) || $isAdmin) {
            $actions[] = Actions\Action::make('return_for_correction')
                ->label('Retourner pour Correction')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->form([
                    Textarea::make('comments')
                        ->label('Commentaires pour l\'étudiant')
                        ->required()
                        ->rows(5),
                ])
                ->action(function (array $data) use ($record, $user) {
                    try {
                        app(ReportFlowService::class)->updateReportStatus($record, ReportStatusEnum::NEEDS_CORRECTION, $user, $data['comments']);
                        Notification::make()->title('Rapport retourné pour correction')->body('L\'étudiant a été notifié des corrections requises.')->success()->send();
                        $this->refreshFormData(['status']);
                    } catch (\Throwable $e) {
                        Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                    }
                });
        }

        if ($isCommissionMember && $record->status === ReportStatusEnum::IN_COMMISSION_REVIEW) {
            $actions[] = Actions\Action::make('record_vote')
                ->label('Enregistrer Mon Vote')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->form([
                    Select::make('decision')
                        ->label('Décision')
                        ->options(VoteDecisionEnum::class)
                        ->required()
                        ->reactive(),
                    Textarea::make('comment')
                        ->label('Commentaire')
                        ->visible(fn (\Filament\Forms\Get $get) => in_array($get('decision'), [VoteDecisionEnum::REJECTED->value, VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value], true))
                        ->required(fn (\Filament\Forms\Get $get) => in_array($get('decision'), [VoteDecisionEnum::REJECTED->value, VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value], true)),
                ])
                ->action(function (array $data) use ($record, $user) {
                    try {
                        // Find the commission session this report is currently in
                        $commissionSession = $record->commissionSessions()->where('status', \App\Enums\CommissionSessionStatusEnum::IN_PROGRESS)->first();
                        if (! $commissionSession) {
                            throw new \Exception('Le rapport n\'est pas dans une session de commission active.');
                        }
                        app(\App\Services\CommissionFlowService::class)->recordVote($commissionSession, $record, $user, VoteDecisionEnum::from($data['decision']), $data['comment']);
                        Notification::make()->title('Vote enregistré')->body('Votre vote a été enregistré avec succès.')->success()->send();
                        $this->refreshFormData(['votes']); // Refresh votes section
                    } catch (\Throwable $e) {
                        Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                    }
                });
        }

        if ($record->status === ReportStatusEnum::VALIDATED || $record->status === ReportStatusEnum::ARCHIVED) {
            $actions[] = Actions\Action::make('download_pdf')
                ->label('Télécharger PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('secondary')
                ->action(function () use ($record) {
                    try {
                        $document = $record->documents()->whereHas('documentType', fn ($q) => $q->where('name', \App\Enums\DocumentTypeEnum::RAPPORT->value))->first();
                        if ($document && Storage::exists($document->file_path)) {
                            return Storage::download($document->file_path);
                        }
                        Notification::make()->title('Fichier non trouvé')->body('Le PDF du rapport n\'est pas encore disponible ou a été supprimé.')->danger()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                    }
                });
        }

        return $actions;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $user = Auth::user();
        $isConformityAgent = $user->hasRole('Agent de Conformite');
        $isCommissionMember = $user->hasAnyRole(['Membre Commission', 'President Commission']);
        $isAdmin = $user->hasRole('Admin');

        return $infolist
            ->schema([
                Section::make('Informations Générales du Rapport')
                    ->schema([
                        TextEntry::make('report_id')->label('ID Rapport'),
                        TextEntry::make('title')->label('Titre du Rapport'),
                        TextEntry::make('theme')->label('Thème Principal'),
                        TextEntry::make('abstract')->label('Résumé (Abstract)'),
                        TextEntry::make('student.full_name')->label('Étudiant'),
                        TextEntry::make('academicYear.label')->label('Année Académique'),
                        TextEntry::make('reportTemplate.name')->label('Modèle de Rapport'),
                        BadgeEntry::make('status')->label('Statut du Rapport')
                            ->colors([
                                'gray' => ReportStatusEnum::DRAFT->value,
                                'info' => ReportStatusEnum::SUBMITTED->value,
                                'warning' => ReportStatusEnum::NEEDS_CORRECTION->value,
                                'primary' => ReportStatusEnum::IN_CONFORMITY_CHECK->value,
                                'secondary' => ReportStatusEnum::IN_COMMISSION_REVIEW->value,
                                'success' => ReportStatusEnum::VALIDATED->value,
                                'danger' => ReportStatusEnum::REJECTED->value,
                                'dark' => ReportStatusEnum::ARCHIVED->value,
                            ]),
                        TextEntry::make('page_count')->label('Nombre de Pages'),
                        TextEntry::make('version')->label('Version'),
                        TextEntry::make('submission_date')->label('Date de Soumission')->dateTime(),
                        TextEntry::make('last_modified_date')->label('Dernière Modification')->dateTime(),
                    ])->columns(2),

                Section::make('Contenu du Rapport (Sections)')
                    ->schema([
                        RepeatableEntry::make('sections')
                            ->label('Sections du Rapport')
                            ->schema([
                                TextEntry::make('title')->label('Titre de la Section'),
                                TextEntry::make('content')->label('Contenu')->html(),
                            ])
                            ->collapsible()
                            ->defaultItems(0),
                    ]),

                Section::make('Détails de Vérification de Conformité')
                    ->visible(fn () => $isConformityAgent || $isAdmin)
                    ->schema([
                        RepeatableEntry::make('conformityCheckDetails')
                            ->label('Vérifications')
                            ->schema([
                                TextEntry::make('criterion.label')->label('Critère'),
                                BadgeEntry::make('validation_status')->label('Statut')
                                    ->colors([
                                        'success' => ConformityStatusEnum::CONFORME->value,
                                        'danger' => ConformityStatusEnum::NON_CONFORME->value,
                                        'info' => ConformityStatusEnum::NON_APPLICABLE->value,
                                    ]),
                                TextEntry::make('comment')->label('Commentaire'),
                                TextEntry::make('verification_date')->label('Date')->dateTime(),
                                TextEntry::make('verifiedBy.email')->label('Vérifié par'),
                            ])
                            ->collapsible()
                            ->defaultItems(0),
                    ]),

                Section::make('Votes de la Commission')
                    ->visible(fn () => $isCommissionMember || $isAdmin)
                    ->schema([
                        RepeatableEntry::make('votes')
                            ->label('Votes')
                            ->schema([
                                TextEntry::make('teacher.full_name')->label('Votant'),
                                BadgeEntry::make('voteDecision.name')->label('Décision')
                                    ->colors([
                                        'success' => VoteDecisionEnum::APPROVED->value,
                                        'danger' => VoteDecisionEnum::REJECTED->value,
                                        'warning' => VoteDecisionEnum::APPROVED_WITH_RESERVATIONS->value,
                                        'info' => VoteDecisionEnum::ABSTAIN->value,
                                    ]),
                                TextEntry::make('comment')->label('Commentaire'),
                                TextEntry::make('vote_date')->label('Date Vote')->dateTime(),
                                TextEntry::make('vote_round')->label('Tour'),
                            ])
                            ->collapsible()
                            ->defaultItems(0),
                    ]),
            ]);
    }
}
