<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JuryRoleResource\Pages;
use App\Models\JuryRole;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class JuryRoleResource extends Resource
{
    protected static ?string $model = \App\Models\JuryRole::class;
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
            'index' => Pages\ListJuryRoles::route('/'),
            'create' => Pages\CreateJuryRole::route('/create'),
            'edit' => Pages\EditJuryRole::route('/{record}/edit'),
            'view' => Pages\ViewJuryRole::route('/{record}'),
        ];
    }
}
