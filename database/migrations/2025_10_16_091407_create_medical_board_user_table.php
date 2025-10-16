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
        Schema::create('medical_board_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_list_id'); // reference to the board
            $table->unsignedBigInteger('user_id');         // reference to the user
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('patient_list_id')->references('patient_list_id')->on('patient_lists')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Ensure unique assignment
            $table->unique(['patient_list_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_board_user');
    }
};