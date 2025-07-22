<?php

declare(strict_types=1);

namespace App\Enums;

enum PvApprovalDecisionEnum: string
{
    case APPROVED = 'Approuvé';
    case CHANGES_REQUESTED = 'Modification Demandée';
    case REJECTED = 'Rejeté';
}
