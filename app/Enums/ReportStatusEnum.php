<?php

    namespace App\Enums;

    enum ReportStatusEnum: string
    {
        case DRAFT = 'Brouillon';
        case SUBMITTED = 'Soumis';
        case NEEDS_CORRECTION = 'Nécessite Correction';
        case IN_CONFORMITY_CHECK = 'En vérification conformité'; // Ajouté pour la clarté du workflow
        case IN_COMMISSION_REVIEW = 'En Commission';
        case VALIDATED = 'Validé';
        case REJECTED = 'Refusé';
        case ARCHIVED = 'Archivé';
    }