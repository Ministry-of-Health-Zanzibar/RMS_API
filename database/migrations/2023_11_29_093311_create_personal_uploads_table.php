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
        Schema::create('personal_uploads', function (Blueprint $table) {
            $table->bigIncrements('personal_upload_id');
            $table->string('personal_information_id', 20);           
            $table->unsignedBigInteger('upload_type_id');
            $table->string('upload_file_name',30);
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('personal_information_id')->references('personal_information_id')->on('personal_informations');
            $table->foreign('upload_type_id')->references('upload_type_id')->on('upload_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_uploads');
    }
};
