<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\InternshipResource\Pages;
    use App\Models\Internship;
    use Filament\Forms\Components\DatePicker;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Toggle;
    use Filament\Forms\Form;
    use Filament\Forms\Components\DateTimePicker;
    use Filament\Notifications\Notification;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\Action;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\IconColumn;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class InternshipResource extends Resource
    {
        protected static ?string $model = Internship::class;
        protected static ?string $navigationIcon = 'heroicon-o-briefcase';
        protected static ?string $navigationGroup = 'Gestion des Stages';
        protected static ?string $modelLabel = 'Stage';
        protected static ?string $pluralModelLabel = 'Stages';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    Select::make('student_id')
                        ->label('Étudiant')
                        ->relationship('student', 'last_name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('company_id')
                        ->label('Entreprise')
                        ->relationship('company', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    DatePicker::make('start_date')
                        ->label('Date de début')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Date de fin')
                        ->nullable()
                        ->afterOrEqual('start_date'),
                    TextInput::make('subject')
                        ->label('Sujet du stage')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('company_tutor_name')
                        ->label('Nom du tuteur en entreprise')
                        ->maxLength(100)
                        ->nullable(),
                    Toggle::make('is_validated')
                        ->label('Stage Validé')
                        ->disabledOn('create')
                        ->helperText('Indique si le stage a été officiellement validé par le Responsable Scolarité.'),
                    DateTimePicker::make('validation_date')
                        ->label('Date de Validation')
                        ->disabled()
                        ->nullable(),
                    Select::make('validated_by_user_id')
                        ->label('Validé par')
                        ->relationship('validatedBy', 'email')
                        ->disabled()
                        ->nullable(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('student.first_name')
                        ->label('Prénom Étudiant')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('student.last_name')
                        ->label('Nom Étudiant')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('company.name')
                        ->label('Entreprise')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('subject')
                        ->label('Sujet')
                        ->limit(50),
                    TextColumn::make('start_date')
                        ->label('Date début')
                        ->date()
                        ->sortable(),
                    IconColumn::make('is_validated')
                        ->label('Validé')
                        ->boolean(),
                ])
                ->filters([
                    Select::make('company_id')
                        ->label('Filtrer par Entreprise')
                        ->relationship('company', 'name'),
                    Toggle::make('is_validated')
                        ->label('Validé'),
                ])
                ->actions([
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('validate_internship')
                        ->label('Valider Stage')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Internship $record): bool => !$record->is_validated)
                        ->requiresConfirmation()
                        ->action(function (Internship $record) {
                            $record->is_validated = true;
                            $record->validation_date = now();
                            $record->validated_by_user_id = auth()->id();
                            $record->save();
                            Notification::make()
                                ->title('Stage validé')
                                ->body("Le stage de {$record->student->first_name} {$record->student->last_name} a été validé.")
                                ->success()
                                ->send();
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
                'index' => Pages\ListInternships::route('/'),
                'create' => Pages\CreateInternship::route('/create'),
                'edit' => Pages\EditInternship::route('/{record}/edit'),
                'view' => Pages\ViewInternship::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['student.first_name', 'student.last_name', 'company.name', 'subject'];
        }
    }
