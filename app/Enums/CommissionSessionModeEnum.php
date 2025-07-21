<?php

    namespace App\Enums;

    enum CommissionSessionModeEnum: string
    {
        case IN_PERSON = 'Présentiel';
        case ONLINE = 'En ligne';
        case HYBRID = 'Hybride';
    }