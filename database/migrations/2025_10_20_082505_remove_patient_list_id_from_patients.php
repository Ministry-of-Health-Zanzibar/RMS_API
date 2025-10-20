<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['patient_list_id']);
            $table->dropColumn('patient_list_id');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_list_id')->nullable();
            $table->foreign('patient_list_id')->references('patient_list_id')->on('patient_lists');
        });
    }
};
