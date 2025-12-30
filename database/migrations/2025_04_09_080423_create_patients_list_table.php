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
            $table->string('reference_number')->unique()->nullable();
            $table->enum('board_type', ['Emergency', 'Routine'])->nullable();
            $table->unsignedInteger('no_of_patients')->default(0);
            $table->string('board_date')->nullable();
            $table->string('patient_list_title');
            $table->string('patient_list_file')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

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
