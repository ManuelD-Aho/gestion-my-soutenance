<?php

    namespace App\Enums;

    enum ConformityStatusEnum: string
    {
        case CONFORME = 'Conforme';
        case NON_CONFORME = 'Non Conforme';
        case NON_APPLICABLE = 'Non Applicable';
    }