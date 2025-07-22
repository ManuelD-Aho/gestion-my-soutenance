<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\ReportStatusResource\Pages;
    use App\Models\ReportStatus;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class ReportStatusResource extends Resource
    {
        protected static ?string $model = ReportStatus::class;
        protected static ?string $navigationIcon = 'heroicon-o-tag';
        protected static ?string $navigationGroup = 'Référentiels';
        protected static ?string $modelLabel = 'Statut de Rapport';
        protected static ?string $pluralModelLabel = 'Statuts de Rapport';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('name')
                        ->label('Nom du statut')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),
                    TextInput::make('workflow_step')
                        ->label('Étape dans le workflow')
                        ->numeric()
                        ->nullable()
                        ->helperText('Numéro pour ordonner les statuts dans le workflow (ex: 10, 20, 30...).'),
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
                    TextColumn::make('workflow_step')
                        ->label('Étape Workflow')
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
                'index' => Pages\ListReportStatuss::route('/'),
                'create' => Pages\CreateReportStatus::route('/create'),
                'edit' => Pages\EditReportStatus::route('/{record}/edit'),
                'view' => Pages\ViewReportStatus::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name'];
        }
    }