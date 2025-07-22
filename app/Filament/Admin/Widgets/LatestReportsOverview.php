<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\ReportStatusEnum;
use App\Filament\Admin\Resources\ReportResource;
use App\Models\Report;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestReportsOverview extends BaseWidget
{
    protected static ?string $heading = 'Derniers Rapports Soumis ou en Attente';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Report::query()
                    ->whereIn('status', [
                        ReportStatusEnum::SUBMITTED,
                        ReportStatusEnum::IN_CONFORMITY_CHECK,
                        ReportStatusEnum::NEEDS_CORRECTION,
                        ReportStatusEnum::IN_COMMISSION_REVIEW,
                    ])
                    ->latest('submission_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('report_id')
                    ->label('ID Rapport'),
                TextColumn::make('title')
                    ->label('Titre')
                    ->limit(40),
                TextColumn::make('student.first_name')
                    ->label('Prénom Étudiant'),
                TextColumn::make('student.last_name')
                    ->label('Nom Étudiant'),
                TextColumn::make('status')
                    ->label('Statut'),
                TextColumn::make('submission_date')
                    ->label('Date Soumission')
                    ->date(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->label('Voir')
                    ->url(fn (Report $record): string => ReportResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
