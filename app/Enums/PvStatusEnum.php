<?php

namespace App\Enums;

enum PvStatusEnum: string
{
    case DRAFT = 'Brouillon';
    case PENDING_APPROVAL = 'En attente d\'approbation';
    case APPROVED = 'Validé';
    case REJECTED = 'Rejeté';
    case ARCHIVED = 'Archivé'; // <-- AJOUTER CETTE LIGNE
    case IN_REVISION = 'En révision'; // Si vous avez ce statut pour les PVs
}
