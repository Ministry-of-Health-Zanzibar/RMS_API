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
        Schema::create('referrals', function (Blueprint $table) {
            $table->string('referral_id')->primary();  // Define referral_id as the primary key and it's not auto-incremented
            $table->string('patient_id');
            $table->unsignedBigInteger('hospital_id');
            $table->unsignedBigInteger('referral_type_id');
            $table->unsignedBigInteger('reason_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['Pending', 'Confirmed', 'Cancelled', 'Expired']);
            $table->unsignedBigInteger('confirmed_by')->nullable();  // DG user ID who confirmed the referral
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints (if applicable)
            $table->foreign('patient_id')->references('patient_id')->on('patients');
            $table->foreign('hospital_id')->references('id')->on('hospitals');
            $table->foreign('referral_type_id')->references('id')->on('referral_types');
            $table->foreign('reason_id')->references('id')->on('reasons');
            $table->foreign('confirmed_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
