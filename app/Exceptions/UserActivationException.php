<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class UserActivationException extends Exception
{
    public function __construct(string $message = "Erreur lors de l'activation du compte utilisateur.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
