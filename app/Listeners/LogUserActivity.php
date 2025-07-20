<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserActivity
{
    public function handle(UserLoggedIn $event): void
    {
        // Log user activity
    }
}
