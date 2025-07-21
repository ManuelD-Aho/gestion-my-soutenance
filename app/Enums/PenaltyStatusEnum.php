<?php

    namespace App\Enums;

    enum PenaltyStatusEnum: string
    {
        case DUE = 'Due';
        case PAID = 'Réglée';
        case WAIVED = 'Annulée';
    }