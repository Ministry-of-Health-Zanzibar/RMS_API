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
            $table->string('matibabu_card')->nullable()->unique(); // NOT NULL by default
            $table->string('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('location_id')->nullable();
            $table->string('job')->nullable();
            $table->string('position')->nullable();
            $table->unsignedBigInteger('patient_list_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('location_id')->references('location_id')->on('geographical_locations');
            $table->foreign('patient_list_id')->references('patient_list_id')->on('patient_lists');
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