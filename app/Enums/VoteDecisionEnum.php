<?php

declare(strict_types=1);

namespace App\Enums;

enum VoteDecisionEnum: string
{
    case APPROVED = 'Approuvé';
    case REJECTED = 'Refusé';
    case APPROVED_WITH_RESERVATIONS = 'Approuvé sous réserve';
    case ABSTAIN = 'Abstention';

    public function getColor(): string
    {
        return match ($this) {
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::APPROVED_WITH_RESERVATIONS => 'warning',
            self::ABSTAIN => 'gray',
        };
    }
}
