<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VoteResource\Pages;

use App\Filament\Admin\Resources\VoteResource;
use Filament\Resources\Pages\EditRecord;

class EditVote extends EditRecord
{
    protected static string $resource = VoteResource::class;
}
