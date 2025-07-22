<?php

declare(strict_types=1);

namespace App\Filament\AppPanel\Resources;

use App\Filament\AppPanel\Resources\InternshipResource\Pages;
use App\Models\Internship;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InternshipResource extends Resource
{
    protected static ?string $model = Internship::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Stages';

    protected static ?string $pluralLabel = 'Stages';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        if ($user->hasRole('Admin')) {
            return parent::getEloquentQuery();
        }

        if ($user->hasRole('Responsable Scolarite')) {
            return parent::getEloquentQuery();
        }

        if ($user->hasRole('Etudiant') && $user->student) {
            return parent::getEloquentQuery()->where('student_id', $user->student->id);
        }

        return parent::getEloquentQuery()->where('id', null);
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isRS = $user->hasRole('Responsable Scolarite');
        $isStudent = $user->hasRole('Etudiant');

        return $form
            ->schema([
                Section::make('Informations du Stage')
                    ->schema([
                        Select::make('student_id')
                            ->label('Étudiant')
                            ->relationship('student', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->first_name} {$record->last_name} ({$record->student_card_number})")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(! $isRS),
                        Select::make('company_id')
                            ->label('Entreprise')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->unique()->maxLength(200),
                                TextInput::make('activity_sector')->maxLength(100),
                                TextInput::make('contact_name')->maxLength(100),
                                TextInput::make('contact_email')->email()->maxLength(255),
                                TextInput::make('contact_phone')->tel()->maxLength(20),
                            ])
                            ->disabled(! $isRS && ! $isStudent),
                        DatePicker::make('start_date')
                            ->label('Date de Début')
                            ->required()
                            ->disabled(! $isRS && ! $isStudent),
                        DatePicker::make('end_date')
                            ->label('Date de Fin')
                            ->nullable()
                            ->afterOrEqual('start_date')
                            ->disabled(! $isRS && ! $isStudent),
                        TextInput::make('subject')
                            ->label('Sujet du Stage')
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isRS && ! $isStudent),
                        TextInput::make('company_tutor_name')
                            ->label('Nom du Tuteur en Entreprise')
                            ->maxLength(100)
                            ->nullable()
                            ->disabled(! $isRS && ! $isStudent),
                    ])->columns(2),

                Section::make('Validation du Stage')
                    ->visible(fn (?Internship $record) => $record && $isRS)
                    ->schema([
                        Toggle::make('is_validated')
                            ->label('Stage Validé')
                            ->disabled(fn (?Internship $record) => $record && $record->is_validated),
                        DateTimePicker::make('validation_date')
                            ->label('Date de Validation')
                            ->disabled(),
                        Select::make('validated_by_user_id')
                            ->label('Validé par')
                            ->relationship('validatedBy', 'email')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isRS = $user->hasRole('Responsable Scolarite');

        return $table
            ->columns([
                TextColumn::make('student.first_name') // Utilise first_name/last_name directement
                    ->label('Prénom Étudiant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.last_name') // Utilise first_name/last_name directement
                    ->label('Nom Étudiant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Sujet')
                    ->limit(50)
                    ->tooltip(fn (Internship $record): ?string => $record->subject),
                TextColumn::make('start_date')
                    ->label('Début')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_validated')
                    ->label('Validé')
                    ->boolean(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_validated')
                    ->options([
                        true => 'Validé',
                        false => 'Non Validé',
                    ])
                    ->label('Statut de Validation'),
                \Filament\Tables\Filters\SelectFilter::make('academic_year_id')
                    ->relationship('student.enrollments.academicYear', 'label')
                    ->label('Année Académique Étudiant'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Internship $record) => $isRS || (Auth::user()->student && Auth::user()->student->id === $record->student_id && ! $record->is_validated)),
                Action::make('validate_internship')
                    ->label('Valider Stage')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Internship $record): bool => $isRS && ! $record->is_validated)
                    ->requiresConfirmation()
                    ->action(function (Internship $record) {
                        try {
                            $user = Auth::user();
                            if (! $user) {
                                throw new \Exception('Utilisateur non authentifié.');
                            }
                            $record->is_validated = true;
                            $record->validation_date = now();
                            $record->validated_by_user_id = $user->id;
                            $record->save();
                            Notification::make()->title('Stage validé')->body('Le stage a été validé avec succès.')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                // Pas d'actions de masse par défaut
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
            'index' => Pages\ListInternships::route('/'),
            'create' => Pages\CreateInternship::route('/create'),
            'edit' => Pages\EditInternship::route('/{record}/edit'),
            'view' => Pages\ViewInternship::route('/{record}'),
        ];
    }
}