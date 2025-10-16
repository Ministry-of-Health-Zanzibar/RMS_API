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
        Schema::create('diagnosis_referral', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referral_id');
            $table->unsignedBigInteger('diagnosis_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('referral_id')->references('referral_id')->on('referrals')->onDelete('cascade');
            $table->foreign('diagnosis_id')->references('diagnosis_id')->on('diagnoses')->onDelete('cascade');

            // Ensure a referral cannot have the same diagnosis twice
            $table->unique(['referral_id', 'diagnosis_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosis_referral');
    }
};
