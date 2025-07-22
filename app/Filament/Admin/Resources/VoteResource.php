<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\VoteResource\Pages;
    use App\Models\Vote;
    use Filament\Forms\Components\DateTimePicker;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class VoteResource extends Resource
    {
        protected static ?string $model = Vote::class;
        protected static ?string $navigationIcon = 'heroicon-o-check-circle';
        protected static ?string $navigationGroup = 'Gestion des Commissions';
        protected static ?string $modelLabel = 'Vote';
        protected static ?string $pluralModelLabel = 'Votes';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('vote_id')
                        ->label('ID Vote')
                        ->disabled()
                        ->visibleOn('view'),
                    Select::make('commission_session_id')
                        ->label('Session de Commission')
                        ->relationship('commissionSession', 'name')
                        ->required()
                        ->disabledOn('edit')
                        ->searchable()
                        ->preload(),
                    Select::make('report_id')
                        ->label('Rapport Voté')
                        ->relationship('report', 'title')
                        ->required()
                        ->disabledOn('edit')
                        ->searchable()
                        ->preload(),
                    Select::make('teacher_id')
                        ->label('Enseignant Votant')
                        ->relationship('teacher', 'last_name')
                        ->required()
                        ->disabledOn('edit')
                        ->searchable()
                        ->preload(),
                    Select::make('vote_decision_id')
                        ->label('Décision')
                        ->relationship('voteDecision', 'name')
                        ->required(),
                    Textarea::make('comment')
                        ->label('Commentaire')
                        ->columnSpanFull()
                        ->nullable(),
                    DateTimePicker::make('vote_date')
                        ->label('Date du Vote')
                        ->disabled(),
                    TextInput::make('vote_round')
                        ->label('Tour de Vote')
                        ->numeric()
                        ->default(1),
                    Select::make('status')
                        ->label('Statut')
                        ->options(['ACTIVE' => 'Actif', 'CANCELLED' => 'Annulé'])
                        ->required(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('vote_id')
                        ->label('ID Vote')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('commissionSession.name')
                        ->label('Session')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('report.title')
                        ->label('Rapport')
                        ->limit(30)
                        ->searchable(),
                    TextColumn::make('teacher.first_name')
                        ->label('Prénom Votant')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('teacher.last_name')
                        ->label('Nom Votant')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('voteDecision.name')
                        ->label('Décision')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('vote_round')
                        ->label('Tour'),
                    TextColumn::make('vote_date')
                        ->label('Date Vote')
                        ->dateTime()
                        ->sortable(),
                ])
                ->filters([
                    Select::make('commission_session_id')
                        ->label('Session de Commission')
                        ->relationship('commissionSession', 'name'),
                    Select::make('vote_decision_id')
                        ->label('Décision')
                        ->relationship('voteDecision', 'name'),
                    Select::make('status')
                        ->label('Statut')
                        ->options(['ACTIVE' => 'Actif', 'CANCELLED' => 'Annulé']),
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
                'index' => Pages\ListVotes::route('/'),
                'create' => Pages\CreateVote::route('/create'),
                'edit' => Pages\EditVote::route('/{record}/edit'),
                'view' => Pages\ViewVote::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['vote_id', 'report.title', 'teacher.first_name', 'teacher.last_name'];
        }
    }