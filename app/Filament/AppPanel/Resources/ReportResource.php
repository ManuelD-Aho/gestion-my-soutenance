<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources;

use App\Enums\ConformityStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\ReportStatusEnum;
use App\Enums\VoteDecisionEnum;
use App\Filament\AppPanel\Resources\ReportResource\Pages;
use App\Models\ConformityCriterion;
use App\Models\Report;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ConformityCheckService;
use App\Services\PdfGenerationService;
use App\Services\ReportFlowService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationLabel = 'Mes Rapports';

    protected static ?string $pluralLabel = 'Rapports';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        if ($user->hasRole('Admin')) {
            return parent::getEloquentQuery();
        }

        if ($user->hasRole('Etudiant') && $user->student) {
            return parent::getEloquentQuery()->where('student_id', $user->student->id);
        }

        if ($user->hasRole('Agent de Conformite')) {
            return parent::getEloquentQuery()->whereIn('status', [
                ReportStatusEnum::SUBMITTED,
                ReportStatusEnum::IN_CONFORMITY_CHECK,
                ReportStatusEnum::NEEDS_CORRECTION,
            ]);
        }

        if ($user->hasAnyRole(['Membre Commission', 'President Commission']) && $user->teacher) {
            return parent::getEloquentQuery()
                ->whereIn('status', [
                    ReportStatusEnum::IN_COMMISSION_REVIEW,
                    ReportStatusEnum::VALIDATED,
                    ReportStatusEnum::REJECTED,
                ])
                ->whereHas('commissionSessions', function (Builder $query) use ($user) {
                    $query->whereHas('teachers', fn ($q) => $q->where('teacher_id', $user->teacher->id));
                });
        }

        return parent::getEloquentQuery()->where('id', null); // No access for other roles
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isStudent = $user->hasRole('Etudiant');
        $isConformityAgent = $user->hasRole('Agent de Conformite');
        $isCommissionMember = $user->hasAnyRole(['Membre Commission', 'President Commission']);
        $isAdmin = $user->hasRole('Admin');

        return $form
            ->schema([
                Section::make('Informations Générales du Rapport')
                    ->schema([
                        TextInput::make('report_id')
                            ->label('ID Rapport')
                            ->disabled()
                            ->visible(fn (?Report $record) => $record),
                        TextInput::make('title')
                            ->label('Titre du Rapport')
                            ->required()
                            ->maxLength(191)
                            ->disabled(fn (?Report $record) => $record && $record->status !== ReportStatusEnum::DRAFT && $record->status !== ReportStatusEnum::NEEDS_CORRECTION && ! $isAdmin),
                        TextInput::make('theme')
                            ->label('Thème Principal')
                            ->nullable()
                            ->maxLength(191)
                            ->disabled(fn (?Report $record) => $record && $record->status !== ReportStatusEnum::DRAFT && $record->status !== ReportStatusEnum::NEEDS_CORRECTION && ! $isAdmin),
                        Textarea::make('abstract')
                            ->label('Résumé (Abstract)')
                            ->nullable()
                            ->rows(5)
                            ->disabled(fn (?Report $record) => $record && $record->status !== ReportStatusEnum::DRAFT && $record->status !== ReportStatusEnum::NEEDS_CORRECTION && ! $isAdmin),
                        Select::make('student_id')
                            ->label('Étudiant')
                            ->relationship('student', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->first_name} {$record->last_name} ({$record->student_card_number})")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(true), // Always disabled, set by system
                        Select::make('academic_year_id')
                            ->label('Année Académique')
                            ->relationship('academicYear', 'label')
                            ->required()
                            ->disabled(true), // Always disabled, set by system
                        Select::make('report_template_id')
                            ->label('Modèle de Rapport')
                            ->relationship('reportTemplate', 'name')
                            ->nullable()
                            ->disabled(fn (?Report $record) => $record && $record->status !== ReportStatusEnum::DRAFT && ! $isAdmin),
                        Select::make('status')
                            ->label('Statut du Rapport')
                            ->options(ReportStatusEnum::class)
                            ->disabled(true), // Statut géré par le workflow et les actions
                        TextInput::make('page_count')
                            ->label('Nombre de Pages')
                            ->numeric()
                            ->nullable()
                            ->disabled(true), // Peut être mis à jour automatiquement ou par l'Admin
                        TextInput::make('version')
                            ->label('Version')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('submission_date')
                            ->label('Date de Soumission')
                            ->dateTime()
                            ->disabled(),
                        TextInput::make('last_modified_date')
                            ->label('Dernière Modification')
                            ->dateTime()
                            ->disabled(),
                    ])->columns(2),

                Section::make('Contenu du Rapport (Sections)')
                    ->description('Contenu détaillé du rapport.')
                    ->schema([
                        Repeater::make('sections')
                            ->relationship('sections')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre de la Section')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(fn (?Report $record) => $record && $record->status !== ReportStatusEnum::DRAFT && $record->status !== ReportStatusEnum::NEEDS_CORRECTION && ! $isAdmin),
                                RichEditor::make('content')
                                    ->label('Contenu de la Section')
                                    ->nullable()
                                    ->disabled(fn (?Report $record) => $record && $record->status !== ReportStatusEnum::DRAFT && $record->status !== ReportStatusEnum::NEEDS_CORRECTION && ! $isAdmin),
                                TextInput::make('order')
                                    ->label('Ordre')
                                    ->numeric()
                                    ->default(0)
                                    ->hidden(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->disabled(fn (?Report $record) => $record && $record->status !== ReportStatusEnum::DRAFT && $record->status !== ReportStatusEnum::NEEDS_CORRECTION && ! $isAdmin),
                    ]),

                Section::make('Détails de Vérification de Conformité')
                    ->visible(fn (?Report $record) => $record && ($isConformityAgent || $isAdmin))
                    ->schema([
                        Repeater::make('conformityCheckDetails')
                            ->relationship('conformityCheckDetails')
                            ->schema([
                                Select::make('conformity_criterion_id')
                                    ->label('Critère')
                                    ->relationship('criterion', 'label')
                                    ->disabled(),
                                Select::make('validation_status')
                                    ->label('Statut')
                                    ->options(ConformityStatusEnum::class)
                                    ->disabled(),
                                Textarea::make('comment')
                                    ->label('Commentaire')
                                    ->disabled(),
                                TextInput::make('verification_date')
                                    ->label('Date Vérification')
                                    ->dateTime()
                                    ->disabled(),
                                Select::make('verified_by_user_id')
                                    ->label('Vérifié par')
                                    ->relationship('verifiedBy', 'email')
                                    ->disabled(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => ConformityCriterion::find($state['conformity_criterion_id'])?->label ?? null)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false),
                    ]),

                Section::make('Votes de la Commission')
                    ->visible(fn (?Report $record) => $record && ($isCommissionMember || $isAdmin))
                    ->schema([
                        Repeater::make('votes')
                            ->relationship('votes')
                            ->schema([
                                Select::make('teacher_id')
                                    ->label('Votant')
                                    ->relationship('teacher', 'last_name')
                                    ->getOptionLabelFromRecordUsing(fn (Teacher $record) => "{$record->first_name} {$record->last_name}")
                                    ->disabled(),
                                Select::make('vote_decision_id')
                                    ->label('Décision')
                                    ->options(VoteDecisionEnum::class)
                                    ->disabled(),
                                Textarea::make('comment')
                                    ->label('Commentaire')
                                    ->disabled(),
                                TextInput::make('vote_date')
                                    ->label('Date Vote')
                                    ->dateTime()
                                    ->disabled(),
                                TextInput::make('vote_round')
                                    ->label('Tour')
                                    ->numeric()
                                    ->disabled(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => Teacher::find($state['teacher_id'])?->full_name ?? null)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isStudent = $user->hasRole('Etudiant');
        $isConformityAgent = $user->hasRole('Agent de Conformite');
        $isCommissionMember = $user->hasAnyRole(['Membre Commission', 'President Commission']);
        $isAdmin = $user->hasRole('Admin');

        return $table
            ->columns([
                TextColumn::make('report_id')
                    ->label('ID Rapport')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn (Report $record): ?string => $record->title),
                TextColumn::make('student.full_name')
                    ->label('Étudiant')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->visible(! $isStudent), // Étudiant n'a pas besoin de voir son propre nom
                TextColumn::make('academicYear.label')
                    ->label('Année Académique')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'gray' => ReportStatusEnum::DRAFT->value,
                        'info' => ReportStatusEnum::SUBMITTED->value,
                        'warning' => ReportStatusEnum::NEEDS_CORRECTION->value,
                        'primary' => ReportStatusEnum::IN_CONFORMITY_CHECK->value,
                        'secondary' => ReportStatusEnum::IN_COMMISSION_REVIEW->value,
                        'success' => ReportStatusEnum::VALIDATED->value,
                        'danger' => ReportStatusEnum::REJECTED->value,
                        'dark' => ReportStatusEnum::ARCHIVED->value,
                    ])
                    ->sortable(),
                TextColumn::make('submission_date')
                    ->label('Date Soumission')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options(ReportStatusEnum::class)
                    ->label('Filtrer par Statut'),
                \Filament\Tables\Filters\SelectFilter::make('academic_year_id')
                    ->relationship('academicYear', 'label')
                    ->label('Filtrer par Année Académique'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Report $record) => ($isStudent && ($record->status === ReportStatusEnum::DRAFT || $record->status === ReportStatusEnum::NEEDS_CORRECTION)) || $isAdmin),
                Action::make('check_conformity')
                    ->label('Vérifier Conformité')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->visible(fn (Report $record) => $isConformityAgent && ($record->status === ReportStatusEnum::SUBMITTED || $record->status === ReportStatusEnum::IN_CONFORMITY_CHECK))
                    ->form(function (Report $record, ConformityCheckService $conformityCheckService) {
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
                    ->action(function (array $data, Report $record, ConformityCheckService $conformityCheckService) use ($user) {
                        try {
                            // S'assurer que $user est bien une instance de \App\Models\User
                            $userModel = $user instanceof User ? $user : ($user ? User::find(method_exists($user, 'getAuthIdentifier') ? $user->getAuthIdentifier() : null) : null);
                            $conformityCheckService->checkConformity($record, $data['criteria_results'], $userModel);
                            Notification::make()->title('Vérification de conformité effectuée')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Action::make('return_for_correction')
                    ->label('Retourner pour Correction')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Report $record) => ($isConformityAgent && $record->status === ReportStatusEnum::IN_CONFORMITY_CHECK) || ($isCommissionMember && $record->status === ReportStatusEnum::IN_COMMISSION_REVIEW) || $isAdmin)
                    ->form([
                        Textarea::make('comments')
                            ->label('Commentaires pour l\'étudiant')
                            ->required()
                            ->rows(5),
                    ])
                    ->action(function (array $data, Report $record, ReportFlowService $reportFlowService) use ($user) {
                        try {
                            // S'assurer que $user est bien une instance de \App\Models\User
                            $userModel = $user instanceof User ? $user : ($user ? User::find(method_exists($user, 'getAuthIdentifier') ? $user->getAuthIdentifier() : null) : null);
                            $reportFlowService->updateReportStatus($record, ReportStatusEnum::NEEDS_CORRECTION, $userModel, $data['comments']);
                            Notification::make()->title('Rapport retourné pour correction')->body('L\'étudiant a été notifié des corrections requises.')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Action::make('download_pdf')
                    ->label('Télécharger PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('secondary')
                    ->visible(fn (Report $record) => $record->status === ReportStatusEnum::VALIDATED || $record->status === ReportStatusEnum::ARCHIVED)
                    ->action(function (Report $record, PdfGenerationService $pdfGenerationService) {
                        try {
                            // Find the generated document for this report
                            $document = $record->documents()->whereHas('documentType', fn ($q) => $q->where('name', DocumentTypeEnum::RAPPORT->value))->first();

                            if ($document && Storage::exists($document->file_path)) {
                                return Storage::download($document->file_path);
                            }

                            Notification::make()->title('Fichier non trouvé')->body('Le PDF du rapport n\'est pas encore disponible ou a été supprimé.')->danger()->send();
                            return null;
                        } catch (\Throwable $e) {
                            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                            return null;
                        }
                    }),
            ])
            ->bulkActions([
                // Pas d'actions de masse par défaut
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
            'view' => Pages\ViewReport::route('/{record}'),
        ];
    }
}
