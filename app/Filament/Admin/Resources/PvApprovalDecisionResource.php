<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\PvApprovalDecisionResource\Pages;
    use App\Models\PvApprovalDecision;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class PvApprovalDecisionResource extends Resource
    {
        protected static ?string $model = PvApprovalDecision::class;
        protected static ?string $navigationIcon = 'heroicon-o-hand-thumb-up';
        protected static ?string $navigationGroup = 'Référentiels';
        protected static ?string $modelLabel = 'Décision d\'Approbation PV';
        protected static ?string $pluralModelLabel = 'Décisions d\'Approbation PV';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('name')
                        ->label('Nom de la décision')
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
                'index' => Pages\ListPvApprovalDecisions::route('/'),
                'create' => Pages\CreatePvApprovalDecision::route('/create'),
                'edit' => Pages\EditPvApprovalDecision::route('/{record}/edit'),
                'view' => Pages\ViewPvApprovalDecision::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name'];
        }
    }