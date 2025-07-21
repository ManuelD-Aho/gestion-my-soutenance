<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PenaltyException extends Exception
{
    public function __construct(string $message = "Une erreur est survenue lors de la gestion des pénalités.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}