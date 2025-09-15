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
        Schema::create('hospital_letters', function (Blueprint $table) {
            $table->bigIncrements('letter_id'); // Primary Key
            $table->unsignedBigInteger('referral_id'); // Foreign Key
            $table->text('content_summary')->nullable();
            $table->string('next_appointment_date')->nullable();
            $table->string('letter_file')->nullable(); // Path or filename for uploaded letter
            $table->enum('outcome', ['Follow-up', 'Finished', 'Transferred', 'Death']);
            $table->timestamps();
            $table->softDeletes(); 

            // Foreign Key Constraint
            $table->foreign('referral_id')->references('referral_id')->on('referrals'); // If referral deleted, delete related hospital letters
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_letters');
    }
};
