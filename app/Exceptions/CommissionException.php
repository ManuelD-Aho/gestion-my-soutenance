<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class CommissionException extends Exception
{
    public function __construct(string $message = 'Soumission incomplète. Des informations obligatoires sont manquantes ou invalides.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
