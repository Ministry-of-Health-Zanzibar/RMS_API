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
        Schema::table('patient_histories', function (Blueprint $table) {
            $table->text('board_diagnosis_ids')->nullable()->after('board_comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_histories', function (Blueprint $table) {
            $table->dropColumn('board_diagnosis');
        });
    }
};
