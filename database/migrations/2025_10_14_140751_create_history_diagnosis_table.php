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
        Schema::create('history_diagnosis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_histories_id');
            $table->unsignedBigInteger('diagnosis_id');

            // who added this diagnosis, default is 'doctor'
            $table->enum('added_by', ['doctor', 'medical_board'])->default('doctor');

            $table->timestamps();

            $table->foreign('patient_histories_id')->references('patient_histories_id')->on('patient_histories')->cascadeOnDelete();
            $table->foreign('diagnosis_id')->references('diagnosis_id')->on('diagnoses')->cascadeOnDelete();

            // prevent exact duplicates by same actor
            $table->unique(['patient_histories_id', 'diagnosis_id', 'added_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_diagnosis');
    }
};
