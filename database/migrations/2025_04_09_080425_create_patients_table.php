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
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('location_id')->nullable();
            $table->string(column: 'job')->nullable();
            $table->string(column: 'position')->nullable();
            $table->unsignedBigInteger('patient_list_id');
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