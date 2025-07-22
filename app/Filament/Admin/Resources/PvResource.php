<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\PvStatusEnum;
use App\Filament\Admin\Resources\PvResource\Pages;
use App\Models\Pv;
use App\Services\CommissionFlowService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
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
use Illuminate\Support\Facades\Storage;

class PvResource extends Resource
{
    protected static ?string $model = Pv::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Gestion des Commissions';

    protected static ?string $modelLabel = 'Procès-Verbal';

    protected static ?string $pluralModelLabel = 'Procès-Verbaux';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('pv_id')
                    ->label('ID PV')
                    ->disabled()
                    ->visibleOn('view'),
                Select::make('commission_session_id')
                    ->label('Session de Commission')
                    ->relationship('commissionSession', 'name')
                    ->required()
                    ->disabledOn('edit')
                    ->searchable()
                    ->preload(),
                Select::make('report_id')
                    ->label('Rapport Lié')
                    ->relationship('report', 'title')
                    ->nullable()
                    ->disabledOn('edit')
                    ->searchable()
                    ->preload(),
                Select::make('type')
                    ->label('Type de PV')
                    ->options(['session' => 'Session', 'report_specific' => 'Spécifique au Rapport'])
                    ->required()
                    ->disabledOn('edit'),
                RichEditor::make('content')
                    ->label('Contenu du PV')
                    ->required()
                    ->columnSpanFull(),
                Select::make('author_user_id')
                    ->label('Auteur')
                    ->relationship('author', 'email')
                    ->required()
                    ->disabledOn('edit')
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->label('Statut')
                    ->options(PvStatusEnum::class)
                    ->disabledOn('create'),
                DateTimePicker::make('approval_deadline')
                    ->label('Date limite d\'approbation')
                    ->nullable(),
                TextInput::make('version')
                    ->label('Version')
                    ->numeric()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pv_id')
                    ->label('ID PV')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commissionSession.name')
                    ->label('Session')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('report.title')
                    ->label('Rapport')
                    ->limit(30)
                    ->placeholder('N/A')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type'),
                TextColumn::make('author.name')
                    ->label('Auteur')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut'),
                TextColumn::make('approval_deadline')
                    ->label('Date limite')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Select::make('status')
                    ->label('Statut')
                    ->options(PvStatusEnum::class),
                Select::make('commission_session_id')
                    ->label('Session de Commission')
                    ->relationship('commissionSession', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('force_approval')
                    ->label('Forcer Approbation')
                    ->icon('heroicon-o-shield-check')
                    ->color('danger')
                    ->visible(fn (Pv $record): bool => $record->status !== PvStatusEnum::APPROVED)
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('reason')
                            ->label('Raison du forçage')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, Pv $record) {
                        try {
                            app(CommissionFlowService::class)->forcePvApproval($record, auth()->user(), $data['reason']);
                            Notification::make()
                                ->title('Approbation forcée réussie')
                                ->body("Le PV {$record->pv_id} a été forcé en statut 'Approuvé'.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erreur lors du forçage d\'approbation')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('download_pv')
                    ->label('Télécharger PV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Pv $record) {
                        $document = $record->documents()->whereHas('documentType', fn ($query) => $query->where('name', 'Procès-Verbal'))->first();
                        if ($document && Storage::exists($document->file_path)) {
                            return Storage::download($document->file_path);
                        }
                        Notification::make()
                            ->title('Fichier PV non trouvé')
                            ->body('Le fichier PDF du PV n\'a pas encore été généré ou n\'existe plus.')
                            ->warning()
                            ->send();

                        return null;
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
            'index' => Pages\ListPvs::route('/'),
            'create' => Pages\CreatePv::route('/create'),
            'edit' => Pages\EditPv::route('/{record}/edit'),
            'view' => Pages\ViewPv::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['pv_id', 'commissionSession.name', 'report.title'];
    }
}
