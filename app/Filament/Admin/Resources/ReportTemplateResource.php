<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReportTemplateResource\Pages;
use App\Filament\Admin\Resources\ReportTemplateResource\RelationManagers\SectionsRelationManager;
use App\Models\ReportTemplate;
use App\Services\UniqueIdGeneratorService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportTemplateResource extends Resource
{
    protected static ?string $model = ReportTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Gestion des Rapports';

    protected static ?string $modelLabel = 'Modèle de Rapport';

    protected static ?string $pluralModelLabel = 'Modèles de Rapports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('template_id')
                    ->label('ID Modèle')
                    ->disabledOn('edit')
                    ->visibleOn('view')
                    ->dehydrateStateUsing(fn (?string $state): string => $state ?? app(UniqueIdGeneratorService::class)->generate('TPL', (int) date('Y'))),
                TextInput::make('name')
                    ->label('Nom du modèle')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull()
                    ->nullable(),
                TextInput::make('version')
                    ->label('Version')
                    ->maxLength(10)
                    ->default('1.0'),
                Select::make('status')
                    ->label('Statut')
                    ->options([
                        'Active' => 'Actif',
                        'Draft' => 'Brouillon',
                        'Archived' => 'Archivé',
                    ])
                    ->required()
                    ->default('Draft'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('template_id')
                    ->label('ID Modèle')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('version')
                    ->label('Version'),
                TextColumn::make('status')
                    ->label('Statut'),
            ])
            ->filters([
                Select::make('status')
                    ->label('Filtrer par Statut')
                    ->options([
                        'Active' => 'Actif',
                        'Draft' => 'Brouillon',
                        'Archived' => 'Archivé',
                    ]),
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
            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportTemplates::route('/'),
            'create' => Pages\CreateReportTemplate::route('/create'),
            'edit' => Pages\EditReportTemplate::route('/{record}/edit'),
            'view' => Pages\ViewReportTemplate::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'template_id'];
    }
}
