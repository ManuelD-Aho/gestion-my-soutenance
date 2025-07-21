<?php

    namespace App\Enums;

    enum VoteDecisionEnum: string
    {
        case APPROVED = 'Approuvé';
        case REJECTED = 'Refusé';
        case APPROVED_WITH_RESERVATIONS = 'Approuvé sous réserve';
        case ABSTAIN = 'Abstention';
    }