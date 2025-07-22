<?php

declare(strict_types=1);

namespace App\Enums;

enum CommissionSessionStatusEnum: string
{
    case PLANNED = 'Planifiée';
    case IN_PROGRESS = 'En cours';
    case CLOSED = 'Clôturée';
    case ARCHIVED = 'Archivée';
}
