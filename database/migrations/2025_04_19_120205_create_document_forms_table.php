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
        Schema::create('document_forms', function (Blueprint $table) {
            $table->bigIncrements('document_form_id');
            $table->string('document_form_code')->unique();
            $table->string('payee_name');
            $table->double('amount');
            $table->string('tin_number');
            $table->string('year');
            // $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('source_type_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes(); // adds deleted_at column


            // $table->foreign('source_id')->references('source_id')->on('sources');
            $table->foreign('source_type_id')->references('source_type_id')->on('source_types');
            $table->foreign('category_id')->references('category_id')->on('categories');
            $table->foreign('document_type_id')->references('document_type_id')->on('document_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_forms');
    }
};