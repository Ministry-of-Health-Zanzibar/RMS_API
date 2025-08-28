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
        Schema::create('patient_lists', function (Blueprint $table) {
            $table->bigIncrements('patient_list_id');
            $table->string('patient_list_title'); // NOT NULL by default
            $table->string('patient_list_file');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes(); // if you want to allow soft deletes

            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_lists');
    }
};