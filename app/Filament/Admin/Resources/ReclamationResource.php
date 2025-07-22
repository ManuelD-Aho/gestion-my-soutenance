<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\ReclamationStatusEnum;
use App\Filament\Admin\Resources\ReclamationResource\Pages;
use App\Models\Penalty;
use App\Models\Reclamation;
use App\Models\Report;
use App\Services\UniqueIdGeneratorService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReclamationResource extends Resource
{
    protected static ?string $model = Reclamation::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Gestion Administrative';

    protected static ?string $modelLabel = 'Réclamation';

    protected static ?string $pluralModelLabel = 'Réclamations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('reclamation_id')
                    ->label('ID Réclamation')
                    ->disabledOn('edit')
                    ->visibleOn('view')
                    ->dehydrateStateUsing(fn (?string $state): string => $state ?? app(UniqueIdGeneratorService::class)->generate('RECLA', (int) date('Y'))),
                Select::make('student_id')
                    ->label('Étudiant')
                    ->relationship('student', 'last_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('subject')
                    ->label('Sujet')
                    ->required()
                    ->maxLength(191),
                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('submission_date')
                    ->label('Date de Soumission')
                    ->disabled(),
                Select::make('status')
                    ->label('Statut')
                    ->options(ReclamationStatusEnum::class)
                    ->required(),
                Textarea::make('response')
                    ->label('Réponse')
                    ->columnSpanFull()
                    ->nullable(),
                DateTimePicker::make('response_date')
                    ->label('Date de Réponse')
                    ->nullable(),
                Select::make('admin_staff_id')
                    ->label('Traité par')
                    ->relationship('administrativeStaff', 'last_name')
                    ->nullable()
                    ->searchable()
                    ->preload(),
                MorphToSelect::make('reclaimable')
                    ->label('Concerne l\'entité')
                    ->types([
                        MorphToSelect\Type::make(Penalty::class)
                            ->titleAttribute('penalty_id'),
                        MorphToSelect\Type::make(Report::class)
                            ->titleAttribute('title'),
                        // Add other reclaimable types here
                    ])
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reclamation_id')
                    ->label('ID Réclamation')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.first_name')
                    ->label('Prénom Étudiant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.last_name')
                    ->label('Nom Étudiant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Sujet')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut'),
                TextColumn::make('submission_date')
                    ->label('Date Soumission')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Select::make('status')
                    ->label('Statut')
                    ->options(ReclamationStatusEnum::class),
                Select::make('student_id')
                    ->label('Étudiant')
                    ->relationship('student', 'last_name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('process_reclamation')
                    ->label('Traiter Réclamation')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(fn (Reclamation $record): bool => $record->status !== ReclamationStatusEnum::CLOSED && $record->status !== ReclamationStatusEnum::RESOLVED)
                    ->form([
                        Select::make('status')
                            ->label('Nouveau Statut')
                            ->options(ReclamationStatusEnum::class)
                            ->required()
                            ->default(fn (Reclamation $record) => $record->status),
                        Textarea::make('response')
                            ->label('Réponse / Commentaires')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, Reclamation $record) {
                        $record->status = $data['status'];
                        $record->response = $data['response'];
                        $record->response_date = now();
                        $record->admin_staff_id = auth()->user()->administrativeStaff?->id; // <-- MODIFICATION ICI
                        $record->save();
                        Notification::make()
                            ->title('Réclamation traitée')
                            ->body("La réclamation {$record->reclamation_id} a été mise à jour.")
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListReclamations::route('/'),
            'create' => Pages\CreateReclamation::route('/create'),
            'edit' => Pages\EditReclamation::route('/{record}/edit'),
            'view' => Pages\ViewReclamation::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['reclamation_id', 'student.first_name', 'student.last_name', 'subject'];
    }
}
