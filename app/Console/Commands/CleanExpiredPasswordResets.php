<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanExpiredPasswordResets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-password-resets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired password reset records.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('password_resets')
            ->where('expires_at', '<', now())
            ->delete();

        return 0;
    }

}
