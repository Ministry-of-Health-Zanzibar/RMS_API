<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1️⃣ Create the pivot table
        Schema::create('patient_list_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('patient_list_id');
            $table->timestamps();

            $table->foreign('patient_id')->references('patient_id')->on('patients')->onDelete('cascade');
            $table->foreign('patient_list_id')->references('patient_list_id')->on('patient_lists')->onDelete('cascade');

            $table->unique(['patient_id', 'patient_list_id']); // avoid duplicates
        });

        // 2️⃣ Migrate existing one-to-many data into the new pivot
        DB::statement('
            INSERT INTO patient_list_patient (patient_id, patient_list_id, created_at, updated_at)
            SELECT patient_id, patient_list_id, NOW(), NOW()
            FROM patients
            WHERE patient_list_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_list_patient');
    }
};

