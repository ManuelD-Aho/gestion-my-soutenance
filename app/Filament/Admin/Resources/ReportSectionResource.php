<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReportSectionResource\Pages;
use App\Models\ReportSection;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportSectionResource extends Resource
{
    protected static ?string $model = ReportSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestion des Rapports';

    protected static ?string $modelLabel = 'Section de Rapport';

    protected static ?string $pluralModelLabel = 'Sections de Rapport';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('report_id')
                    ->label('Rapport')
                    ->relationship('report', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('title')
                    ->label('Titre de la section')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('content')
                    ->label('Contenu')
                    ->columnSpanFull()
                    ->nullable(),
                TextInput::make('order')
                    ->label('Ordre d\'affichage')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report.title')
                    ->label('Rapport')
                    ->limit(30)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('content')
                    ->label('Contenu')
                    ->html()
                    ->limit(50)
                    ->tooltip(fn (ReportSection $record): string => strip_tags($record->content)),
            ])
            ->filters([
                Select::make('report_id')
                    ->label('Filtrer par Rapport')
                    ->relationship('report', 'title'),
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
            'index' => Pages\ListReportSections::route('/'),
            'create' => Pages\CreateReportSection::route('/create'),
            'edit' => Pages\EditReportSection::route('/{record}/edit'),
            'view' => Pages\ViewReportSection::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'report.title'];
    }
}
