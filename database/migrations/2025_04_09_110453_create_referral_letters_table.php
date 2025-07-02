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
        Schema::create('referral_letters', function (Blueprint $table) {
            $table->bigIncrements('referral_letter_id');  // Auto-incrementing primary key
            $table->unsignedBigInteger('referral_id');  // Foreign key from the referrals table
            $table->string('referral_letter_code')->unique();
            $table->text('letter_text');
            $table->boolean('is_printed')->default(false);  // Default value for 'signed' is false
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('referral_id')->references('referral_id')->on('referrals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_letters');
    }
};