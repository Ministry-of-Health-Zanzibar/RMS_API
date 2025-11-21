<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'Requested' to the referral_status enum type
        DB::statement("ALTER TYPE referral_status ADD VALUE IF NOT EXISTS 'Requested';");

        // Ensure the referrals.status column uses the updated enum type
        DB::statement("ALTER TABLE referrals ALTER COLUMN status TYPE referral_status USING status::referral_status;");
    }

    public function down(): void
    {
        // You cannot remove enum values in PostgreSQL easily, so leave empty
    }
};
