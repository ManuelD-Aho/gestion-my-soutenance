<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\StudyLevelResource\Pages;
    use App\Models\StudyLevel;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class StudyLevelResource extends Resource
    {
        protected static ?string $model = StudyLevel::class;
        protected static ?string $navigationIcon = 'heroicon-o-bookmark';
        protected static ?string $navigationGroup = 'Référentiels';
        protected static ?string $modelLabel = 'Niveau d\'Étude';
        protected static ?string $pluralModelLabel = 'Niveaux d\'Étude';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('name')
                        ->label('Nom du niveau')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),
                    Textarea::make('description')
                        ->label('Description')
                        ->columnSpanFull()
                        ->nullable(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('name')
                        ->label('Nom')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('description')
                        ->label('Description')
                        ->limit(50),
                ])
                ->filters([
                    //
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
                'index' => Pages\ListStudyLevels::route('/'),
                'create' => Pages\CreateStudyLevel::route('/create'),
                'edit' => Pages\EditStudyLevel::route('/{record}/edit'),
                'view' => Pages\ViewStudyLevel::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name'];
        }
    }