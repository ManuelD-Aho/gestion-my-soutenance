<?php

    namespace App\Filament\AppPanel\Pages;

    use Filament\Pages\Dashboard as BaseDashboard;
    use Filament\Widgets\Widget;
    use App\Filament\AppPanel\Widgets\StudentReportStatusWidget;
    use App\Filament\AppPanel\Widgets\CommissionVoteOverview;
    use Illuminate\Support\Facades\Auth;

    class Dashboard extends BaseDashboard
    {
        protected static ?string $navigationIcon = 'heroicon-o-home';
        protected static string $view = 'filament.app-panel.pages.dashboard';

        public function getWidgets(): array
        {
            $user = Auth::user();

            if ($user->hasRole('Etudiant')) {
                return [
                    StudentReportStatusWidget::class,
                ];
            }

            if ($user->hasAnyRole(['Membre Commission', 'President Commission'])) {
                return [
                    CommissionVoteOverview::class,
                ];
            }

            return [
                // Widgets communs ou pour d'autres rôles si nécessaire
            ];
        }

        public function getColumns(): int | array
        {
            return 2;
        }
    }