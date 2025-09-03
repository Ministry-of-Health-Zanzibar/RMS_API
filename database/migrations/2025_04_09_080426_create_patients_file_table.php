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
        Schema::create('patient_files', function (Blueprint $table) {
            $table->bigIncrements('file_id'); // Primary Key
            $table->unsignedBigInteger('patient_id'); // Foreign Key to patients
            $table->string('file_name'); // Original file name
            $table->string('file_path'); // Path where file is stored
            $table->string('file_type')->nullable(); // pdf, image, docx, etc.
            $table->text('description')->nullable(); // Optional description
            $table->unsignedBigInteger('uploaded_by')->nullable(); // User who uploaded
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('patient_id')->references('patient_id')->on('patients');
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_files');
    }
};
