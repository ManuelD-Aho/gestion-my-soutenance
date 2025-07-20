<?php

namespace App\Filament\Admin\Resources\VoteDecisionResource\Pages;

use App\Filament\Admin\Resources\VoteDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVoteDecisions extends ListRecords
{
    protected static string $resource = VoteDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
