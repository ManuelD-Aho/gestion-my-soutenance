<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\CompanyResource\Pages;
    use App\Models\Company;
    use App\Services\UniqueIdGeneratorService;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class CompanyResource extends Resource
    {
        protected static ?string $model = Company::class;
        protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
        protected static ?string $navigationGroup = 'Gestion des Stages';
        protected static ?string $modelLabel = 'Entreprise';
        protected static ?string $pluralModelLabel = 'Entreprises';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('company_id')
                        ->label('ID Entreprise')
                        ->disabledOn('edit')
                        ->visibleOn('view')
                        ->dehydrateStateUsing(fn (?string $state): string => $state ?? app(UniqueIdGeneratorService::class)->generate('COMP', (int)date('Y'))),
                    TextInput::make('name')
                        ->label('Nom de l\'entreprise')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(200),
                    TextInput::make('activity_sector')
                        ->label('Secteur d\'activité')
                        ->maxLength(100)
                        ->nullable(),
                    Textarea::make('address')
                        ->label('Adresse')
                        ->columnSpanFull()
                        ->nullable(),
                    TextInput::make('contact_name')
                        ->label('Nom du contact')
                        ->maxLength(100)
                        ->nullable(),
                    TextInput::make('contact_email')
                        ->label('Email du contact')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),
                    TextInput::make('contact_phone')
                        ->label('Téléphone du contact')
                        ->maxLength(20)
                        ->nullable(),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('company_id')
                        ->label('ID Entreprise')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('name')
                        ->label('Nom')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('activity_sector')
                        ->label('Secteur d\'activité')
                        ->searchable(),
                    TextColumn::make('contact_name')
                        ->label('Contact'),
                    TextColumn::make('contact_email')
                        ->label('Email Contact'),
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
                'index' => Pages\ListCompanys::route('/'),
                'create' => Pages\CreateCompany::route('/create'),
                'edit' => Pages\EditCompany::route('/{record}/edit'),
                'view' => Pages\ViewCompany::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name', 'company_id', 'contact_email'];
        }
    }