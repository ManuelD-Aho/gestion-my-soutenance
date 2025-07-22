<?php

    namespace App\Filament\AppPanel\Resources\ReportResource\Pages;

    use App\Filament\AppPanel\Resources\ReportResource;
    use Filament\Actions;
    use Filament\Resources\Pages\ListRecords;
    use Illuminate\Support\Facades\Auth;

    class ListReports extends ListRecords
    {
        protected static string $resource = ReportResource::class;

        protected function getHeaderActions(): array
        {
            $user = Auth::user();
            if ($user->hasRole('Etudiant')) {
                return [
                    Actions\CreateAction::make(),
                ];
            }
            return [];
        }
    }