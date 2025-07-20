<?php

namespace App\Filament\AppPanel\Pages;

use Filament\Pages\Page;

class MyProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.app-panel.pages.my-profile';
    protected static ?string $navigationLabel = 'Mon Profil';
}
