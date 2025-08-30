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
        Schema::create('followups', function (Blueprint $table) {
            $table->id('followup_id'); // Primary Key
            $table->unsignedBigInteger('patient_id'); // FK → patients
            $table->unsignedBigInteger('letter_id');  // FK → hospital_letters
            $table->date('followup_date');
            $table->enum('status', ['Ongoing', 'Closed'])->default('Ongoing');
            $table->timestamps();
            $table->softDeletes(); 

            // Foreign keys
            $table->foreign('patient_id')->references('patient_id')->on('patients');
            $table->foreign('letter_id')->references('letter_id')->on('hospital_letters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followups');
    }
};
