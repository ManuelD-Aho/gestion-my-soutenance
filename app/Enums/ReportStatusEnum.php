<?php

declare(strict_types=1);

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
    // Ajout des constantes manquantes
    case IN_VOTE = 'IN_VOTE';
    case REJECTED_BY_COMMISSION = 'REJECTED_BY_COMMISSION';
    case ADMITTED = 'ADMITTED';
    case RETAKE = 'RETAKE';
    case FAILED = 'FAILED';
}
