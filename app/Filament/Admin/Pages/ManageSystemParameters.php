<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class ManageSystemParameters extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.admin.pages.manage-system-parameters';
    protected static ?string $navigationGroup = 'Configuration Système';
    protected static ?int $navigationSort = 100;
    protected static ?string $title = 'Paramètres Système';
}
