<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReclamationResource\Pages;
use App\Models\Reclamation;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ReclamationResource extends Resource
{
    protected static ?string $model = \App\Models\Reclamation::class;
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
            'index' => Pages\ListReclamations::route('/'),
            'create' => Pages\CreateReclamation::route('/create'),
            'edit' => Pages\EditReclamation::route('/{record}/edit'),
            'view' => Pages\ViewReclamation::route('/{record}'),
        ];
    }
}
