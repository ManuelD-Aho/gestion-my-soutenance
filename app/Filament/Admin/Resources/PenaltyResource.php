<?php

    namespace App\Filament\Admin\Resources;

    use App\Enums\PenaltyStatusEnum;
    use App\Filament\Admin\Resources\PenaltyResource\Pages;
    use App\Models\Penalty;
    use App\Services\PenaltyService;
    use App\Services\UniqueIdGeneratorService;
    use Filament\Forms\Components\DateTimePicker;
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

    class PenaltyResource extends Resource
    {
        protected static ?string $model = Penalty::class;
        protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
        protected static ?string $navigationGroup = 'Gestion Administrative';
        protected static ?string $modelLabel = 'Pénalité';
        protected static ?string $pluralModelLabel = 'Pénalités';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('penalty_id')
                        ->label('ID Pénalité')
                        ->disabledOn('edit')
                        ->visibleOn('view')
                        ->dehydrateStateUsing(fn (?string $state): string => $state ?? app(UniqueIdGeneratorService::class)->generate('PEN', (int)date('Y'))),
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
                    Select::make('type')
                        ->label('Type')
                        ->options(['Financière' => 'Financière', 'Administrative' => 'Administrative'])
                        ->required()
                        ->live(),
                    TextInput::make('amount')
                        ->label('Montant')
                        ->numeric()
                        ->nullable()
                        ->minValue(0)
                        ->visible(fn (Select $component) => $component->getState() === 'Financière'),
                    Textarea::make('reason')
                        ->label('Raison')
                        ->required()
                        ->columnSpanFull(),
                    Select::make('status')
                        ->label('Statut')
                        ->options(PenaltyStatusEnum::class)
                        ->disabledOn('create')
                        ->default(PenaltyStatusEnum::DUE),
                    DateTimePicker::make('creation_date')
                        ->label('Date de Création')
                        ->disabled(),
                    DateTimePicker::make('resolution_date')
                        ->label('Date de Résolution')
                        ->disabled()
                        ->nullable(),
                    Select::make('admin_staff_id')
                        ->label('Appliqué par')
                        ->relationship('administrativeStaff', 'last_name')
                        ->nullable()
                        ->disabledOn('create')
                        ->default(fn () => auth()->user()->administrativeStaff?->id),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('penalty_id')
                        ->label('ID Pénalité')
                        ->searchable()
                        ->sortable(),
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
                    TextColumn::make('type')
                        ->label('Type'),
                    TextColumn::make('amount')
                        ->label('Montant')
                        ->money('eur')
                        ->placeholder('N/A'),
                    TextColumn::make('status')
                        ->label('Statut'),
                    TextColumn::make('creation_date')
                        ->label('Date Création')
                        ->date()
                        ->sortable(),
                ])
                ->filters([
                    Select::make('academic_year_id')
                        ->label('Année Académique')
                        ->relationship('academicYear', 'label'),
                    Select::make('type')
                        ->label('Type de Pénalité')
                        ->options(['Financière' => 'Financière', 'Administrative' => 'Administrative']),
                    Select::make('status')
                        ->label('Statut')
                        ->options(PenaltyStatusEnum::class),
                ])
                ->actions([
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('mark_as_paid')
                        ->label('Marquer comme Payé')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (Penalty $record): bool => $record->status === PenaltyStatusEnum::DUE && $record->type === 'Financière')
                        ->requiresConfirmation()
                        ->action(function (Penalty $record) {
                            try {
                                app(PenaltyService::class)->recordPayment($record, $record->amount, 'Manuel', null, auth()->user());
                                Notification::make()
                                    ->title('Pénalité marquée comme payée')
                                    ->body("La pénalité {$record->penalty_id} a été marquée comme réglée.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Erreur lors du marquage de la pénalité')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('waive_penalty')
                        ->label('Annuler Pénalité')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn (Penalty $record): bool => $record->status === PenaltyStatusEnum::DUE)
                        ->form([
                            Textarea::make('reason')
                                ->label('Raison de l\'annulation')
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->action(function (array $data, Penalty $record) {
                            try {
                                app(PenaltyService::class)->waivePenalty($record, auth()->user(), $data['reason']);
                                Notification::make()
                                    ->title('Pénalité annulée')
                                    ->body("La pénalité {$record->penalty_id} a été annulée.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Erreur lors de l\'annulation de la pénalité')
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
                'index' => Pages\ListPenaltys::route('/'),
                'create' => Pages\CreatePenalty::route('/create'),
                'edit' => Pages\EditPenalty::route('/{record}/edit'),
                'view' => Pages\ViewPenalty::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['penalty_id', 'student.first_name', 'student.last_name'];
        }
    }
