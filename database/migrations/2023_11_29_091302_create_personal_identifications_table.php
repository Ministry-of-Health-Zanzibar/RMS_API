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
        Schema::create('personal_identifications', function (Blueprint $table) {
            $table->bigIncrements('personal_identification_id');
            $table->string('personal_information_id', 20);           
            $table->unsignedBigInteger('identification_id');
            $table->string('id_number',30);
            $table->string('upload_file_name',30)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('personal_information_id')->references('personal_information_id')->on('personal_informations');
            $table->foreign('identification_id')->references('identification_id')->on('identifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_identifications');
    }
};
