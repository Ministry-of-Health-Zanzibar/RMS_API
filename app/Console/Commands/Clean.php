<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backward-compatible alias for the legacy production cron entry
 * `php artisan clean`.
 */
class Clean extends Command
{
    protected $signature = 'clean';

    protected $description = 'Delete expired password reset records.';

    public function handle(): int
    {
        $deleted = DB::table('password_resets')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$deleted} expired password reset record(s).");

        return self::SUCCESS;
    }
}
