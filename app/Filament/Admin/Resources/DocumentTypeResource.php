<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DocumentTypeResource\Pages;
use App\Models\DocumentType;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class DocumentTypeResource extends Resource
{
    protected static ?string $model = \App\Models\DocumentType::class;
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
            'index' => Pages\ListDocumentTypes::route('/'),
            'create' => Pages\CreateDocumentType::route('/create'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
            'view' => Pages\ViewDocumentType::route('/{record}'),
        ];
    }
}
