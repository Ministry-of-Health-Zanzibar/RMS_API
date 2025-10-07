<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // Add new column for disease
            $table->unsignedBigInteger('disease_id')->nullable()->after('reason_id');

            // Add foreign key constraint to diseases table
            $table->foreign('disease_id')
                  ->references('disease_id')
                  ->on('diseases')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['disease_id']);
            $table->dropColumn('disease_id');
        });
    }
};
