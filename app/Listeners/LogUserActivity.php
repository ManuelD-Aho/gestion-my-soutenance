<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedIn;

class LogUserActivity
{
    /**
     * Handle the event.
     */
    public function handle(UserLoggedIn $event): void
    {
        // Logic to log user activity
    }
}
