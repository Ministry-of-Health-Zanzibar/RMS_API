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
        Schema::create('geographical_locations', function (Blueprint $table) {
            $table->string('location_id')->primary()->uniqid();
            $table->string('location_name',50);
            $table->string('parent_id', 11)->nullable();
            $table->longText('label');
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geographical_locations');
    }
};
