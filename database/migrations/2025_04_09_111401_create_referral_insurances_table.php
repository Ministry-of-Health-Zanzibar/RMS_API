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
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();  // Auto-incrementing primary key
            $table->string('insurance_provider_name');  // Name of the insurance provider
            $table->string('policy_number')->unique();  // Unique policy number for the insurance
            $table->string('patient_id');  // Foreign key from the patients table
            $table->date('valid_until');  // Date until the insurance is valid
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('patient_id')->references('patient_id')->on('patients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_insurances');
    }
};
