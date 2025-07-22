<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\ReclamationStatusResource\Pages;
    use App\Models\ReclamationStatus;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class ReclamationStatusResource extends Resource
    {
        protected static ?string $model = ReclamationStatus::class;
        protected static ?string $navigationIcon = 'heroicon-o-tag';
        protected static ?string $navigationGroup = 'Référentiels';
        protected static ?string $modelLabel = 'Statut de Réclamation';
        protected static ?string $pluralModelLabel = 'Statuts de Réclamation';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('name')
                        ->label('Nom du statut')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
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
                'index' => Pages\ListReclamationStatuss::route('/'),
                'create' => Pages\CreateReclamationStatus::route('/create'),
                'edit' => Pages\EditReclamationStatus::route('/{record}/edit'),
                'view' => Pages\ViewReclamationStatus::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name'];
        }
    }