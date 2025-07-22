<?php

    namespace App\Filament\Admin\Resources;

    use App\Enums\ReportStatusEnum;
    use App\Filament\Admin\Resources\ReportResource\Pages;
    use App\Models\Report;
    use App\Services\ConformityCheckService;
    use App\Services\ReportFlowService;
    use Filament\Forms\Components\DateTimePicker;
    use Filament\Forms\Components\RichEditor;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\Textarea;
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
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Storage;

    class ReportResource extends Resource
    {
        protected static ?string $model = Report::class;
        protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
        protected static ?string $navigationGroup = 'Gestion des Rapports';
        protected static ?string $modelLabel = 'Rapport';
        protected static ?string $pluralModelLabel = 'Rapports';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('report_id')
                        ->label('ID Rapport')
                        ->disabled()
                        ->visibleOn('view'),
                    TextInput::make('title')
                        ->label('Titre')
                        ->required()
                        ->maxLength(191),
                    TextInput::make('theme')
                        ->label('Thème')
                        ->maxLength(191)
                        ->nullable(),
                    RichEditor::make('abstract')
                        ->label('Résumé')
                        ->columnSpanFull()
                        ->nullable(),
                    Select::make('student_id')
                        ->label('Étudiant')
                        ->relationship('student', 'last_name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('academic_year_id')
                        ->label('Année Académique')
                        ->relationship('academicYear', 'label')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('status')
                        ->label('Statut')
                        ->options(ReportStatusEnum::class)
                        ->disabledOn('create'),
                    TextInput::make('page_count')
                        ->label('Nombre de pages')
                        ->numeric()
                        ->nullable()
                        ->minValue(0),
                    DateTimePicker::make('submission_date')
                        ->label('Date de Soumission')
                        ->disabled(),
                    DateTimePicker::make('last_modified_date')
                        ->label('Dernière Modification')
                        ->disabled(),
                    TextInput::make('version')
                        ->label('Version')
                        ->numeric()
                        ->disabled(),
                    Select::make('report_template_id')
                        ->label('Modèle de Rapport')
                        ->relationship('reportTemplate', 'name')
                        ->nullable()
                        ->searchable()
                        ->preload(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('report_id')
                        ->label('ID Rapport')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('title')
                        ->label('Titre')
                        ->searchable()
                        ->limit(50),
                    TextColumn::make('student.first_name')
                        ->label('Prénom Étudiant')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('student.last_name')
                        ->label('Nom Étudiant')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('academicYear.label')
                        ->label('Année')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('status')
                        ->label('Statut'),
                    TextColumn::make('submission_date')
                        ->label('Date Soumission')
                        ->date()
                        ->sortable(),
                ])
                ->filters([
                    Select::make('academic_year_id')
                        ->label('Année Académique')
                        ->relationship('academicYear', 'label'),
                    Select::make('status')
                        ->label('Statut')
                        ->options(ReportStatusEnum::class),
                ])
                ->actions([
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('change_status')
                        ->label('Changer Statut')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->form([
                            Select::make('new_status')
                                ->label('Nouveau Statut')
                                ->options(ReportStatusEnum::class)
                                ->required(),
                            Textarea::make('reason')
                                ->label('Raison du changement')
                                ->nullable()
                                ->columnSpanFull(),
                        ])
                        ->action(function (array $data, Report $record) {
                            try {
                                app(ReportFlowService::class)->updateReportStatus($record, ReportStatusEnum::from($data['new_status']), auth()->user(), $data['reason']);
                                Notification::make()
                                    ->title('Statut du rapport mis à jour')
                                    ->body("Le statut du rapport {$record->report_id} a été changé en {$data['new_status']}.")
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
                    Action::make('return_for_correction')
                        ->label('Retourner pour Correction')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->visible(fn (Report $record): bool => in_array($record->status, [ReportStatusEnum::SUBMITTED, ReportStatusEnum::IN_CONFORMITY_CHECK, ReportStatusEnum::IN_COMMISSION_REVIEW]))
                        ->form([
                            Textarea::make('comments')
                                ->label('Commentaires pour l\'étudiant')
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->action(function (array $data, Report $record) {
                            try {
                                app(ReportFlowService::class)->returnForCorrection($record, $data['comments'], auth()->user());
                                Notification::make()
                                    ->title('Rapport retourné pour correction')
                                    ->body("Le rapport {$record->report_id} a été marqué 'Nécessite Correction'.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Erreur lors du retour pour correction')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('check_conformity')
                        ->label('Vérifier Conformité')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('primary')
                        ->visible(fn (Report $record): bool => in_array($record->status, [ReportStatusEnum::SUBMITTED, ReportStatusEnum::IN_CONFORMITY_CHECK]))
                        ->form(function (Report $record, ConformityCheckService $conformityCheckService) {
                            $criteria = $conformityCheckService->getConformityChecklist();
                            $fields = [];
                            foreach ($criteria as $criterion) {
                                $fields[] = Select::make("criteria_results.{$criterion->id}.status")
                                    ->label($criterion->label)
                                    ->options(\App\Enums\ConformityStatusEnum::class)
                                    ->required()
                                    ->default(\App\Enums\ConformityStatusEnum::CONFORME->value)
                                    ->helperText($criterion->description)
                                    ->columnSpan(1);
                                $fields[] = Textarea::make("criteria_results.{$criterion->id}.comment")
                                    ->label('Commentaire')
                                    ->nullable()
                                    ->columnSpan(1);
                            }
                            return $fields;
                        })
                        ->action(function (array $data, Report $record) {
                            try {
                                app(ConformityCheckService::class)->checkConformity($record, $data['criteria_results'], auth()->user());
                                Notification::make()
                                    ->title('Vérification de conformité effectuée')
                                    ->body("Le rapport {$record->report_id} a été évalué pour sa conformité.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Erreur lors de la vérification de conformité')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('download_report_pdf')
                        ->label('Télécharger PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Report $record) {
                            $document = $record->documents()->whereHas('documentType', fn ($query) => $query->where('name', 'Rapport de Soutenance'))->first();
                            if ($document && Storage::exists($document->file_path)) {
                                return Storage::download($document->file_path);
                            }
                            Notification::make()
                                ->title('Fichier PDF non trouvé')
                                ->body('Le fichier PDF du rapport n\'a pas encore été généré ou n\'existe plus.')
                                ->warning()
                                ->send();
                            return null;
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
                'index' => Pages\ListReports::route('/'),
                'create' => Pages\CreateReport::route('/create'),
                'edit' => Pages\EditReport::route('/{record}/edit'),
                'view' => Pages\ViewReport::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['report_id', 'title', 'student.first_name', 'student.last_name'];
        }
    }
