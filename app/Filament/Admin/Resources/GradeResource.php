<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\GradeResource\Pages;
    use App\Models\Grade;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class GradeResource extends Resource
    {
        protected static ?string $model = Grade::class;
        protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
        protected static ?string $navigationGroup = 'Référentiels';
        protected static ?string $modelLabel = 'Grade';
        protected static ?string $pluralModelLabel = 'Grades';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('name')
                        ->label('Nom du grade')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    TextInput::make('abbreviation')
                        ->label('Abréviation')
                        ->maxLength(10)
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
                    TextColumn::make('abbreviation')
                        ->label('Abréviation')
                        ->searchable(),
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
                'index' => Pages\ListGrades::route('/'),
                'create' => Pages\CreateGrade::route('/create'),
                'edit' => Pages\EditGrade::route('/{record}/edit'),
                'view' => Pages\ViewGrade::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name', 'abbreviation'];
        }
    }