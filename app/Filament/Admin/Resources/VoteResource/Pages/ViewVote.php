<?php

namespace App\Filament\Admin\Resources\VoteResource\Pages;

use App\Filament\Admin\Resources\VoteResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewVote extends ViewRecord
{
    protected static string $resource = VoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // Define infolist components here
        ]);
    }
}
