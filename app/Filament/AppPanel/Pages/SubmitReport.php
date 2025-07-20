<?php

namespace App\Filament\AppPanel\Pages;

use Filament\Pages\Page;

class SubmitReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static string $view = 'filament.app-panel.pages.submit-report';
    protected static ?string $navigationLabel = 'Soumettre Rapport';
}
