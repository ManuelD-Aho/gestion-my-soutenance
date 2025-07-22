<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\DocumentTypeResource\Pages;
    use App\Models\DocumentType;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Toggle;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\IconColumn;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class DocumentTypeResource extends Resource
    {
        protected static ?string $model = DocumentType::class;
        protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
        protected static ?string $navigationGroup = 'Référentiels';
        protected static ?string $modelLabel = 'Type de Document';
        protected static ?string $pluralModelLabel = 'Types de Documents';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('name')
                        ->label('Nom du type')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),
                    Toggle::make('is_required')
                        ->label('Requis pour un processus')
                        ->helperText('Indique si ce type de document est obligatoire pour certains workflows (ex: Rapport de Soutenance).'),
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
                    IconColumn::make('is_required')
                        ->label('Requis')
                        ->boolean(),
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
                'index' => Pages\ListDocumentTypes::route('/'),
                'create' => Pages\CreateDocumentType::route('/create'),
                'edit' => Pages\EditDocumentType::route('/{record}/edit'),
                'view' => Pages\ViewDocumentType::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name'];
        }
    }