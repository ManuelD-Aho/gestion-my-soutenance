<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\CommissionSessionStatusEnum;
use App\Enums\PenaltyStatusEnum;
use App\Enums\ReportStatusEnum;
use App\Models\CommissionSession;
use App\Models\Penalty;
use App\Models\Report;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Utilisateurs', User::count())
                ->description('Nombre total de comptes utilisateurs')
                ->color('info'),
            Stat::make('Rapports Soumis', Report::where('status', ReportStatusEnum::SUBMITTED)->count())
                ->description('Rapports en attente de traitement')
                ->color('warning'),
            Stat::make('Rapports Validés', Report::where('status', ReportStatusEnum::VALIDATED)->count())
                ->description('Rapports ayant obtenu une validation finale')
                ->color('success'),
            Stat::make('Pénalités Dues', Penalty::where('status', PenaltyStatusEnum::DUE)->count())
                ->description('Pénalités en attente de régularisation')
                ->color('danger'),
            Stat::make('Sessions Commission Actives', CommissionSession::where('status', CommissionSessionStatusEnum::IN_PROGRESS)->count())
                ->description('Sessions de commission en cours d\'évaluation')
                ->color('primary'),
        ];
    }
}
