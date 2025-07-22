<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class StateConflictException extends Exception
{
    public function __construct(string $message = "Conflit d'état détecté. Les données ont été modifiées par un autre utilisateur.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
