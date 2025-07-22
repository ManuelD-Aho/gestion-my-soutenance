<?php

declare(strict_types=1);

namespace App\Enums;

enum GenderEnum: string
{
    case MASCULIN = 'Masculin';
    case FEMININ = 'Féminin';
    case AUTRE = 'Autre';
}
