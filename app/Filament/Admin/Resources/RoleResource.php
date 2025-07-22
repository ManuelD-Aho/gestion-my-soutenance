<?php

    namespace App\Filament\Admin\Resources;

    use App\Filament\Admin\Resources\RoleResource\Pages;
    use App\Models\Role;
    use Filament\Forms\Components\MultiSelect;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables\Actions\DeleteAction;
    use Filament\Tables\Actions\EditAction;
    use Filament\Tables\Actions\ViewAction;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;

    class RoleResource extends Resource
    {
        protected static ?string $model = Role::class;
        protected static ?string $navigationIcon = 'heroicon-o-finger-print';
        protected static ?string $navigationGroup = 'Gestion des Accès';
        protected static ?string $modelLabel = 'Rôle';
        protected static ?string $pluralModelLabel = 'Rôles';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('name')
                        ->label('Nom du rôle')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(125),
                    TextInput::make('guard_name')
                        ->label('Guard Name')
                        ->required()
                        ->default('web')
                        ->maxLength(125),
                    MultiSelect::make('permissions')
                        ->label('Permissions')
                        ->relationship('permissions', 'name')
                        ->preload()
                        ->searchable(),
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
                    TextColumn::make('guard_name')
                        ->label('Guard'),
                    TextColumn::make('permissions_count')
                        ->counts('permissions')
                        ->label('Nb Permissions'),
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
                'index' => Pages\ListRoles::route('/'),
                'create' => Pages\CreateRole::route('/create'),
                'edit' => Pages\EditRole::route('/{record}/edit'),
                'view' => Pages\ViewRole::route('/{record}'),
            ];
        }

        public static function getGloballySearchableAttributes(): array
        {
            return ['name'];
        }
    }