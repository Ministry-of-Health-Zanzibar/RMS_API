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
        Schema::create('working_stations', function (Blueprint $table) {
            $table->bigIncrements('working_station_id');
            $table->uuid('uuid');
            $table->string('working_station_name', 250);
            $table->string('admin_hierarchy_id',10);
            $table->string('location_id', 20);
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('admin_hierarchy_id')->references('admin_hierarchy_id')->on('admin_hierarchies');
            $table->foreign('location_id')->references('location_id')->on('geographical_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_stations');
    }
};
