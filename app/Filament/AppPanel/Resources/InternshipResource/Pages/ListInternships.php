<?php

    namespace App\Filament\AppPanel\Resources\InternshipResource\Pages;

    use App\Filament\AppPanel\Resources\InternshipResource;
    use Filament\Actions;
    use Filament\Resources\Pages\ListRecords;
    use Illuminate\Support\Facades\Auth;

    class ListInternships extends ListRecords
    {
        protected static string $resource = InternshipResource::class;

        protected function getHeaderActions(): array
        {
            $user = Auth::user();
            if ($user->hasRole('Etudiant') || $user->hasRole('Responsable Scolarite')) {
                return [
                    Actions\CreateAction::make(),
                ];
            }
            return [];
        }
    }