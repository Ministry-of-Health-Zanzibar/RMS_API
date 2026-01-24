<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_histories', function (Blueprint $table) {
            // --- Case type / urgency ---
            $table->enum('case_type', ['Emergency', 'Routine'])->default('Routine')->after('reason_id');
        });
    }

    public function down(): void
    {
        Schema::table('patient_histories', function (Blueprint $table) {
            $table->dropColumn('case_type');
        });
    }
};
