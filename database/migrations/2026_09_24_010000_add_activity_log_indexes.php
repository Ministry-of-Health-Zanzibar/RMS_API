<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS activity_log_causer_created_idx ON activity_log (causer_id, created_at DESC)');
            DB::statement('CREATE INDEX IF NOT EXISTS activity_log_subject_created_idx ON activity_log (subject_type, subject_id, created_at DESC)');
            DB::statement('CREATE INDEX IF NOT EXISTS activity_log_created_idx ON activity_log (created_at DESC)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS activity_log_causer_created_idx');
        DB::statement('DROP INDEX IF EXISTS activity_log_subject_created_idx');
        DB::statement('DROP INDEX IF EXISTS activity_log_created_idx');
    }
};
