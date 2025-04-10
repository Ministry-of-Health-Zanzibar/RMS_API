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
        Schema::create('patients', function (Blueprint $table) {
            $table->bigIncrements('patient_id');
            $table->string('name'); // NOT NULL by default
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('location')->nullable();
            $table->string(column: 'job')->nullable();
            $table->string(column: 'position')->nullable();
            $table->string('referral_letter_file')->nullable(); // path or filename
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes(); // if you want to allow soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};