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
        Schema::create('source_types', function (Blueprint $table) {
            $table->bigIncrements('source_type_id');
            $table->string('source_type_name');
            $table->string('source_type_code')->unique();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes(); // adds deleted_at column

            $table->foreign('source_id')->references('source_id')->on('sources');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_types');
    }
};