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
        Schema::create('user_hierarchies', function (Blueprint $table) {
            $table->bigIncrements('user_hierarche_id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('working_station_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('status');
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('working_station_id')->references('working_station_id')->on('working_stations');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_hierarchies');
    }
};