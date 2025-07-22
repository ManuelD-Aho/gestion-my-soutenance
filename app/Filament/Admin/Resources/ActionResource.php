<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\ActionResource\Pages;
    use App\Models\Action;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class ActionResource extends Resource
    {
        protected static ?string $model = Action::class;
        protected static ?string $navigationIcon = 'heroicon-o-bolt';
        protected static ?string $navigationGroup = 'Configuration Système';
        protected static ?string $modelLabel = 'Action';
        protected static ?string $pluralModelLabel = 'Actions';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    TextInput::make('label')
                        ->label('Libellé')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('category')
                        ->label('Catégorie')
                        ->maxLength(50)
                        ->nullable(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('code')
                        ->label('Code')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('label')
                        ->label('Libellé')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('category')
                        ->label('Catégorie')
                        ->searchable()
                        ->sortable(),
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
                'index' => Pages\ListActions::route('/'),
                'create' => Pages\CreateAction::route('/create'),
                'edit' => Pages\EditAction::route('/{record}/edit'),
                'view' => Pages\ViewAction::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['code', 'label'];
        }
    }