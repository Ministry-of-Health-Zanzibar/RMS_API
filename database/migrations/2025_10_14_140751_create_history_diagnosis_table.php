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
            $table->unsignedBigInteger('board_diagnosis_id')->nullable();
            $table->timestamps();

            $table->foreign('patient_histories_id')->references('patient_histories_id')->on('patient_histories')->onDelete('cascade');
            $table->foreign('diagnosis_id')->references('diagnosis_id')->on('diagnoses')->onDelete('cascade');
            $table->foreign('board_diagnosis_id')->references('diagnosis_id')->on('diagnoses')->onDelete('cascade');

            $table->unique(['patient_histories_id', 'diagnosis_id']); // Prevent duplicate entries
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
