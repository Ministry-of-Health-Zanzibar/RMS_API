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
            $table->string('referral_number')->unique()->after('referral_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('hospital_id')->nullable();
            $table->unsignedBigInteger('reason_id');
            $table->enum('status', ['Pending', 'Confirmed', 'Cancelled', 'Expired']);
            $table->unsignedBigInteger('confirmed_by')->nullable();  // DG user ID who confirmed the referral
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints (if applicable)
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