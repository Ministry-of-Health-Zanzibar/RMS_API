<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the existing check constraint (Laravel usually names it table_column_check)
        // 2. Add 'assigned' to the list of allowed values
        DB::statement("ALTER TABLE patient_histories DROP CONSTRAINT IF EXISTS patient_histories_status_check");

        DB::statement("ALTER TABLE patient_histories ADD CONSTRAINT patient_histories_status_check
            CHECK (status IN ('pending', 'reviewed', 'assigned', 'requested', 'approved', 'confirmed', 'rejected'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE patient_histories DROP CONSTRAINT IF EXISTS patient_histories_status_check");

        DB::statement("ALTER TABLE patient_histories ADD CONSTRAINT patient_histories_status_check
            CHECK (status IN ('pending', 'reviewed', 'requested', 'approved', 'confirmed', 'rejected'))");
    }
};
