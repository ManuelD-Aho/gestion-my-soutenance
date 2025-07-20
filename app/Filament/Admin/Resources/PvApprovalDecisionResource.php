<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PvApprovalDecisionResource\Pages;
use App\Models\PvApprovalDecision;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PvApprovalDecisionResource extends Resource
{
    protected static ?string $model = \App\Models\PvApprovalDecision::class;
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
            'index' => Pages\ListPvApprovalDecisions::route('/'),
            'create' => Pages\CreatePvApprovalDecision::route('/create'),
            'edit' => Pages\EditPvApprovalDecision::route('/{record}/edit'),
            'view' => Pages\ViewPvApprovalDecision::route('/{record}'),
        ];
    }
}
