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
        Schema::create('bill_files', function (Blueprint $table) {
            $table->bigIncrements('bill_file_id'); // Primary Key
            $table->unsignedBigInteger('hospital_id')->nullable();
            $table->string('bill_file_title');     // Title of the bill file
            $table->string('bill_file');           // Path or filename of the uploaded file
            $table->string('bill_file_amount');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();                  // created_at & updated_at
            $table->softDeletes();

            $table->foreign('hospital_id')->references('hospital_id')->on('hospitals');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_files');
    }
};
