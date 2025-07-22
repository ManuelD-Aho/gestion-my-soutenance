<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SpecialityResource\Pages;
use App\Models\Speciality;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpecialityResource extends Resource
{
    protected static ?string $model = Speciality::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Référentiels';

    protected static ?string $modelLabel = 'Spécialité';

    protected static ?string $pluralModelLabel = 'Spécialités';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nom de la spécialité')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                Select::make('responsible_teacher_id')
                    ->label('Enseignant Responsable')
                    ->relationship('responsibleTeacher', 'last_name')
                    ->nullable()
                    ->searchable()
                    ->preload(),
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
                TextColumn::make('responsibleTeacher.first_name')
                    ->label('Prénom Responsable')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('responsibleTeacher.last_name')
                    ->label('Nom Responsable')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Select::make('responsible_teacher_id')
                    ->label('Filtrer par Responsable')
                    ->relationship('responsibleTeacher', 'last_name'),
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
            'index' => Pages\ListSpecialitys::route('/'),
            'create' => Pages\CreateSpeciality::route('/create'),
            'edit' => Pages\EditSpeciality::route('/{record}/edit'),
            'view' => Pages\ViewSpeciality::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'responsibleTeacher.first_name', 'responsibleTeacher.last_name'];
    }
}
