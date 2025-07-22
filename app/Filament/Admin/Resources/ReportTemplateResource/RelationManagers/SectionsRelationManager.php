<?php

namespace App\Filament\Admin\Resources\ReportTemplateResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';
    protected static ?string $recordTitleAttribute = 'title';

    public function form(Forms\Form $form): Forms\Form // Removed static
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Titre')
                ->required(),
            Forms\Components\RichEditor::make('content')
                ->label('Contenu')
                ->nullable()
                ->columnSpanFull(),
            Forms\Components\TextInput::make('order')
                ->label('Ordre')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_mandatory')
                ->label('Obligatoire')
                ->helperText('Indique si cette section est obligatoire pour le rapport final.')
                ->default(false),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table // Removed static
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')
                ->label('Titre')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('order')
                ->label('Ordre')
                ->sortable(),
            Tables\Columns\IconColumn::make('is_mandatory')
                ->label('Obligatoire')
                ->boolean(),
            Tables\Columns\TextColumn::make('content')
                ->label('Contenu')
                ->html()
                ->limit(50)
                ->tooltip(fn ($record): string => strip_tags($record->content)),
        ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
