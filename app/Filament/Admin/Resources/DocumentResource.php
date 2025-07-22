<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\DocumentResource\Pages;
    use App\Models\Document;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\Action;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Storage;
    use Filament\Notifications\Notification;

    class DocumentResource extends Resource
    {
        protected static ?string $model = Document::class;
        protected static ?string $navigationIcon = 'heroicon-o-document';
        protected static ?string $navigationGroup = 'Gestion des Documents';
        protected static ?string $modelLabel = 'Document';
        protected static ?string $pluralModelLabel = 'Documents';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('document_id')
                        ->label('ID Document')
                        ->disabled()
                        ->visibleOn('view'),
                    Select::make('document_type_id')
                        ->label('Type de Document')
                        ->relationship('documentType', 'name')
                        ->required()
                        ->disabled(),
                    TextInput::make('file_path')
                        ->label('Chemin Fichier')
                        ->disabled()
                        ->columnSpanFull(),
                    TextInput::make('file_hash')
                        ->label('Hash Fichier')
                        ->disabled(),
                    TextInput::make('generation_date')
                        ->label('Date Génération')
                        ->disabled(),
                    TextInput::make('version')
                        ->label('Version')
                        ->numeric()
                        ->disabled(),
                    TextInput::make('related_entity_type')
                        ->label('Entité Liée Type')
                        ->disabled(),
                    TextInput::make('related_entity_id')
                        ->label('Entité Liée ID')
                        ->disabled(),
                    Select::make('generated_by_user_id')
                        ->label('Généré par')
                        ->relationship('generatedBy', 'email')
                        ->disabled(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('document_id')
                        ->label('ID Document')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('documentType.name')
                        ->label('Type')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('related_entity_type')
                        ->label('Entité Type')
                        ->limit(20)
                        ->sortable(),
                    TextColumn::make('related_entity_id')
                        ->label('Entité ID')
                        ->sortable(),
                    TextColumn::make('generation_date')
                        ->label('Date Génération')
                        ->dateTime()
                        ->sortable(),
                    TextColumn::make('version')
                        ->label('Version'),
                    TextColumn::make('generatedBy.name')
                        ->label('Généré par')
                        ->searchable(),
                ])
                ->filters([
                    //
                ])
                ->actions([
                    ViewAction::make(),
                    Action::make('download')
                        ->label('Télécharger')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Document $record) {
                            if (Storage::exists($record->file_path)) {
                                return Storage::download($record->file_path);
                            }
                            Notification::make()
                                ->title('Fichier non trouvé')
                                ->body('Le document n\'existe plus sur le serveur de stockage.')
                                ->danger()
                                ->send();
                            return null;
                        }),
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
                'index' => Pages\ListDocuments::route('/'),
                'view' => Pages\ViewDocument::route('/{record}'),
            ];
        }

        public static function canCreate(): bool
        {
            return false;
        }

        public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
        {
            return false;
        }

        public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
        {
            return true;
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['document_id', 'documentType.name', 'related_entity_type'];
        }
    }
