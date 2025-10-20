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
        Schema::create('insurances', function (Blueprint $table) {
            $table->bigIncrements('insurance_id');
            $table->unsignedBigInteger('patient_id');  // Foreign key from the patients table
            $table->string('insurance_provider_name')->nullable(); // Name of the insurance provider
            $table->string('card_number')->unique()->nullable();  // Unique card number for the insurance
            $table->date('valid_until')->nullable();  // Date until the insurance is valid
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
        Schema::dropIfExists('insurances');
    }
};
