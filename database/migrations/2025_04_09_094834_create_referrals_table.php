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
        Schema::create('referrals', function (Blueprint $table) {
            $table->bigIncrements('referral_id');
            $table->unsignedBigInteger('parent_referral_id')->nullable();
            $table->string('referral_number');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('hospital_id')->nullable();
            $table->unsignedBigInteger('reason_id');
            $table->enum('status', ['Pending', 'Confirmed', 'Cancelled', 'Transferred', 'Expired', 'Closed']);
            $table->unsignedBigInteger('confirmed_by')->nullable();  // DG user ID who confirmed the referral
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints (if applicable)
            $table->foreign('parent_referral_id')->references('referral_id')->on('referrals');
            $table->foreign('patient_id')->references('patient_id')->on('patients');
            $table->foreign('hospital_id')->references('hospital_id')->on('hospitals');
            $table->foreign('reason_id')->references('reason_id')->on('reasons');
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