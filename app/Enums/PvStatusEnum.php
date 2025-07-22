<?php

declare(strict_types=1);

namespace App\Enums;

enum PvStatusEnum: string
{
    case DRAFT = 'Brouillon';
    case PENDING_APPROVAL = 'En attente d\'approbation';
    case APPROVED = 'Validé';
    case REJECTED = 'Rejeté';
    case ARCHIVED = 'Archivé';
    case IN_REVISION = 'En révision';
}