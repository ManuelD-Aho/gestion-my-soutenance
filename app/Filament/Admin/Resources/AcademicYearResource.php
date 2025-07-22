<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AcademicYearResource\Pages;
use App\Models\AcademicYear;
use App\Rules\NotOverlappingAcademicYear;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Gestion Académique';

    protected static ?string $modelLabel = 'Année Académique';

    protected static ?string $pluralModelLabel = 'Années Académiques';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('academic_year_id')
                    ->label('ID Année')
                    ->disabledOn('edit')
                    ->visibleOn('view'),
                TextInput::make('label')
                    ->label('Libellé')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                DatePicker::make('start_date')
                    ->label('Date de début')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Date de fin')
                    ->required()
                    ->afterOrEqual('start_date')
                    ->rules([
                        fn ($record) => new NotOverlappingAcademicYear($record),
                    ]),
                Toggle::make('is_active')
                    ->label('Année active')
                    ->helperText('Une seule année académique peut être active à la fois.')
                    ->disabledOn('edit'),
                DateTimePicker::make('report_submission_deadline')
                    ->label('Date limite de soumission des rapports')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academic_year_id')
                    ->label('ID Année')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('label')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Début')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('report_submission_deadline')
                    ->label('Date limite rapports')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, AcademicYear $record) {
                        if ($record->enrollments()->exists() || $record->reports()->exists() || $record->penalties()->exists()) {
                            Notification::make()
                                ->title('Impossible de supprimer l\'année académique')
                                ->body('Cette année académique est liée à des inscriptions, rapports ou pénalités.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
                Action::make('activate')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AcademicYear $record): bool => ! $record->is_active)
                    ->action(function (AcademicYear $record) {
                        DB::beginTransaction();
                        try {
                            AcademicYear::where('is_active', true)->update(['is_active' => false]);
                            $record->is_active = true;
                            $record->save();
                            DB::commit();
                            Notification::make()
                                ->title('Année académique activée')
                                ->body("L'année {$record->label} est maintenant l'année académique active.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Erreur lors de l\'activation')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
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
            'index' => Pages\ListAcademicYears::route('/'),
            'create' => Pages\CreateAcademicYear::route('/create'),
            'edit' => Pages\EditAcademicYear::route('/{record}/edit'),
            'view' => Pages\ViewAcademicYear::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['label', 'academic_year_id'];
    }
}
