<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedIn;

class LogUserActivity
{
    public function handle(UserLoggedIn $event): void
    {
        // Log user activity
    }
}
