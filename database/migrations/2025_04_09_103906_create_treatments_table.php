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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();  // Auto-incrementing primary key
            $table->string('referral_id');  // Foreign key from the referrals table
            $table->date('received_date');
            $table->date('started_date')->nullable();
            $table->date('ended_date')->nullable();
            $table->enum('treatment_status', ['Pending', 'In Progress', 'Completed', 'Cancelled']);
            $table->text('measurements')->nullable();  // Store measurements as text
            $table->unsignedBigInteger('disease_id');  // Foreign key to the disease table
            $table->string('treatment_file')->nullable();  // Path to uploaded treatment file
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('referral_id')->references('referral_id')->on('referrals');
            $table->foreign('disease_id')->references('disease_id')->on('diseases');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
