<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create ENUM type if not exists
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'referral_status') THEN
                    CREATE TYPE referral_status AS ENUM (
                        'Pending',
                        'Confirmed',
                        'Death',
                        'Cancelled',
                        'Transferred',
                        'Expired',
                        'Closed'
                    );
                END IF;
            END$$;
        ");

        // 2. Convert column from VARCHAR to ENUM
        DB::statement("ALTER TABLE referrals ALTER COLUMN status TYPE referral_status USING status::referral_status;");

        // 3. Add new ENUM value "Requested"
        DB::statement("ALTER TYPE referral_status ADD VALUE IF NOT EXISTS 'Requested';");
    }

    public function down(): void
    {
        // No automatic rollback for enum values
    }
};
