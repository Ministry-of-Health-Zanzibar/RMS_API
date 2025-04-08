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
        Schema::create('working_departments', function (Blueprint $table) {
            $table->bigIncrements('working_department_id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('working_station_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('working_station_id')->references('working_station_id')->on('working_stations');
            $table->foreign('department_id')->references('department_id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_departments');
    }
};