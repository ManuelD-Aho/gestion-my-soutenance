<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\EnrollmentResource\Pages;
    use App\Models\Enrollment;
    use Filament\Forms\Components\DatePicker;
    use Filament\Forms\Components\DateTimePicker;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class EnrollmentResource extends Resource
    {
        protected static ?string $model = Enrollment::class;
        protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
        protected static ?string $navigationGroup = 'Gestion Académique';
        protected static ?string $modelLabel = 'Inscription';
        protected static ?string $pluralModelLabel = 'Inscriptions';

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
                    Select::make('study_level_id')
                        ->label('Niveau d\'Étude')
                        ->relationship('studyLevel', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('academic_year_id')
                        ->label('Année Académique')
                        ->relationship('academicYear', 'label')
                        ->required()
                        ->searchable()
                        ->preload(),
                    TextInput::make('enrollment_amount')
                        ->label('Montant de l\'inscription')
                        ->numeric()
                        ->required()
                        ->minValue(0),
                    DateTimePicker::make('enrollment_date')
                        ->label('Date d\'inscription')
                        ->required(),
                    Select::make('payment_status_id')
                        ->label('Statut Paiement')
                        ->relationship('paymentStatus', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    DateTimePicker::make('payment_date')
                        ->label('Date de paiement')
                        ->nullable(),
                    TextInput::make('payment_receipt_number')
                        ->label('Numéro de reçu')
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->nullable(),
                    Select::make('academic_decision_id')
                        ->label('Décision Académique')
                        ->relationship('academicDecision', 'name')
                        ->nullable()
                        ->searchable()
                        ->preload(),
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
                    TextColumn::make('academicYear.label')
                        ->label('Année Académique')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('studyLevel.name')
                        ->label('Niveau')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('enrollment_amount')
                        ->label('Montant')
                        ->money('eur'),
                    TextColumn::make('paymentStatus.name')
                        ->label('Statut Paiement')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('academicDecision.name')
                        ->label('Décision Académique')
                        ->placeholder('N/A')
                        ->searchable()
                        ->sortable(),
                ])
                ->filters([
                    Select::make('academic_year_id')
                        ->label('Année Académique')
                        ->relationship('academicYear', 'label'),
                    Select::make('study_level_id')
                        ->label('Niveau d\'Étude')
                        ->relationship('studyLevel', 'name'),
                    Select::make('payment_status_id')
                        ->label('Statut Paiement')
                        ->relationship('paymentStatus', 'name'),
                ])
                ->actions([
                    EditAction::make(),
                    DeleteAction::make(),
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
                'index' => Pages\ListEnrollments::route('/'),
                'create' => Pages\CreateEnrollment::route('/create'),
                'edit' => Pages\EditEnrollment::route('/{record}/edit'),
                'view' => Pages\ViewEnrollment::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['student.first_name', 'student.last_name', 'academicYear.label'];
        }
    }