<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** PostgreSQL CONCURRENTLY cannot run inside a transaction. */
    public $withinTransaction = false;

    /**
     * These indexes support the joins and date/facility predicates used by
     * the reporting module. They are deliberately limited to report access
     * paths instead of indexing every filterable column.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            if (Schema::hasTable('history_diagnosis')) {
                DB::statement(
                    'CREATE INDEX CONCURRENTLY IF NOT EXISTS history_diagnosis_board_history_idx '
                    .'ON history_diagnosis (added_by, patient_histories_id, diagnosis_id)'
                );
            }

            if (Schema::hasTable('patient_histories')) {
                DB::statement(
                    'CREATE INDEX CONCURRENTLY IF NOT EXISTS patient_histories_reporting_date_idx '
                    .'ON patient_histories (created_at, patient_histories_id, patient_id)'
                );
            }

            if (Schema::hasTable('referrals')) {
                DB::statement(
                    'CREATE INDEX CONCURRENTLY IF NOT EXISTS referrals_hospital_created_idx '
                    .'ON referrals (hospital_id, created_at, referral_id)'
                );
            }

            if (Schema::hasTable('patients')) {
                DB::statement(
                    'CREATE INDEX CONCURRENTLY IF NOT EXISTS patients_location_patient_idx '
                    .'ON patients (location_id, patient_id)'
                );
            }

            return;
        }

        // Keep local non-PostgreSQL environments usable without relying on
        // PostgreSQL-specific index syntax.
        if (Schema::hasTable('history_diagnosis')) {
            Schema::table('history_diagnosis', function ($table): void {
                $table->index(['added_by', 'patient_histories_id', 'diagnosis_id'], 'history_diagnosis_board_history_idx');
            });
        }

        if (Schema::hasTable('patient_histories')) {
            Schema::table('patient_histories', function ($table): void {
                $table->index(['created_at', 'patient_histories_id', 'patient_id'], 'patient_histories_reporting_date_idx');
            });
        }

        if (Schema::hasTable('referrals')) {
            Schema::table('referrals', function ($table): void {
                $table->index(['hospital_id', 'created_at', 'referral_id'], 'referrals_hospital_created_idx');
            });
        }

        if (Schema::hasTable('patients')) {
            Schema::table('patients', function ($table): void {
                $table->index(['location_id', 'patient_id'], 'patients_location_patient_idx');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            foreach ([
                'history_diagnosis_board_history_idx',
                'patient_histories_reporting_date_idx',
                'referrals_hospital_created_idx',
                'patients_location_patient_idx',
            ] as $index) {
                DB::statement('DROP INDEX CONCURRENTLY IF EXISTS '.$index);
            }

            return;
        }

        foreach ([
            ['history_diagnosis', 'history_diagnosis_board_history_idx'],
            ['patient_histories', 'patient_histories_reporting_date_idx'],
            ['referrals', 'referrals_hospital_created_idx'],
            ['patients', 'patients_location_patient_idx'],
        ] as [$table, $index]) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function ($blueprint) use ($index): void {
                    $blueprint->dropIndex($index);
                });
            }
        }
    }
};
