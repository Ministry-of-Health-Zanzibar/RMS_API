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
        Schema::create('personal_locations', function (Blueprint $table) {
            $table->bigIncrements('personal_location_id');
            $table->string('personal_information_id', 20);           
            $table->string('location_id', 20);
            $table->string('personal_location_status', 20);           
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('personal_information_id')->references('personal_information_id')->on('personal_informations');
            $table->foreign('location_id')->references('location_id')->on('geographical_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_locations');
    }
};
