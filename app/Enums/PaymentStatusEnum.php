<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'En attente de paiement';
    case PAID = 'Payé';
    case PARTIAL = 'Paiement partiel';
    case OVERDUE = 'En retard de paiement';
}
