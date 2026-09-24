<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** PostgreSQL CONCURRENTLY cannot run inside a transaction. */
    public $withinTransaction = false;

    /**
     * Source hospitals are resolved from the hospital assignment of the
     * referral creator. This index supports the source-hospital/date report
     * access path without changing existing data.
     */
    public function up(): void
    {
        if (! Schema::hasTable('referrals')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE INDEX CONCURRENTLY IF NOT EXISTS referrals_created_by_created_idx '
                .'ON referrals (created_by, created_at, referral_id)'
            );

            return;
        }

        Schema::table('referrals', function ($table): void {
            $table->index(['created_by', 'created_at', 'referral_id'], 'referrals_created_by_created_idx');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX CONCURRENTLY IF EXISTS referrals_created_by_created_idx');

            return;
        }

        if (Schema::hasTable('referrals')) {
            Schema::table('referrals', function ($table): void {
                $table->dropIndex('referrals_created_by_created_idx');
            });
        }
    }
};
