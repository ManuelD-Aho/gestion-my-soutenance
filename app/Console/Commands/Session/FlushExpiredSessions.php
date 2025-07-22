<?php

declare(strict_types=1);

namespace App\Console\Commands\Session;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class FlushExpiredSessions extends Command
{
    protected $signature = 'session:flush-expired';

    protected $description = 'Supprime les sessions expirées de la base de données.';

    public function handle(): int
    {
        try {
            $lifetime = config('session.lifetime');
            $cutoff = now()->subMinutes($lifetime);

            $deleted = DB::table('sessions')->where('last_activity', '<', $cutoff->timestamp)->delete();

            $this->info("{$deleted} sessions expirées supprimées.");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Erreur lors de la suppression des sessions expirées: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
