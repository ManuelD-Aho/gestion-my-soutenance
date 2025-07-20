<?php

namespace App\Filament\AppPanel\Resources;

use App\Filament\AppPanel\Resources\CommissionSessionResource\Pages;
use App\Models\CommissionSession;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class CommissionSessionResource extends Resource
{
    protected static ?string $model = \App\Models\CommissionSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            'index' => Pages\ListCommissionSessions::route('/'),
            'create' => Pages\CreateCommissionSession::route('/create'),
            'edit' => Pages\EditCommissionSession::route('/{record}/edit'),
            'view' => Pages\ViewCommissionSession::route('/{record}'),
        ];
    }
}
