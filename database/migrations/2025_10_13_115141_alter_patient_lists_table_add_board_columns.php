<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_lists', function (Blueprint $table) {
            $table->string('reference_number')->unique()->nullable()->after('patient_list_id');
            $table->enum('board_type', ['Emergency', 'Routine'])->nullable()->after('reference_number');
            $table->unsignedInteger('no_of_patients')->default(0)->after('board_type');
            $table->string('board_date')->nullable()->after('no_of_patients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_lists', function (Blueprint $table) {
            $table->dropColumn(['board_type', 'no_of_patients', 'board_date', 'reference_number']);
        });
    }
};
