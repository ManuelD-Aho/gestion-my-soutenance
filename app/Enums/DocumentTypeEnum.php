<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentTypeEnum: string
{
    case RAPPORT = 'Rapport de Soutenance';
    case PV = 'Procès-Verbal';
    case BULLETIN = 'Bulletin de Notes';
    case ATTESTATION = 'Attestation';
    case RECU = 'Reçu de Paiement';
    case EXPORT = 'Export de Données';
}
