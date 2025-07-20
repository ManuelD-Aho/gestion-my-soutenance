<?php

namespace App\Filament\AppPanel\Pages;

use Filament\Pages\Page;

class MyDocuments extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.app-panel.pages.my-documents';
    protected static ?string $navigationLabel = 'Mes Documents';
}
