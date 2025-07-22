<?php

declare(strict_types=1);

namespace App\Enums;

enum UserAccountStatusEnum: string
{
    case ACTIVE = 'actif';
    case INACTIVE = 'inactif';
    case BLOCKED = 'bloqué';
    case PENDING_VALIDATION = 'en_attente_validation';
    case ARCHIVED = 'archivé';
}
