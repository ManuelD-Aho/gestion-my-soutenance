<?php

    namespace App\Enums;

    enum ReclamationStatusEnum: string
    {
        case OPEN = 'Ouverte';
        case IN_PROGRESS = 'En cours de traitement';
        case RESOLVED = 'Résolue';
        case CLOSED = 'Clôturée';
    }