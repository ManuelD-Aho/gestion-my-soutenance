<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActionResource\Pages;
use App\Models\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ActionResource extends Resource
{
    protected static ?string $model = \App\Models\Action::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Gestion Admin';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Define form fields here
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            // Define table columns here
        ])->actions([
            // Define actions here
        ])->bulkActions([
            // Define bulk actions here
        ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define relations here
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
}
