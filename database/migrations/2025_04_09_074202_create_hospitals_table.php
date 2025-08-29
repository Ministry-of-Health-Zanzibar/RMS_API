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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->bigIncrements('hospital_id');
            $table->string('hospital_name');
            $table->string('hospital_code')->unique();
            $table->text('hospital_address')->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('hospital_email')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('referral_type_id');
            $table->timestamps();
            $table->softDeletes(); // adds deleted_at column

            $table->foreign('referral_type_id')->references('referral_type_id')->on('referral_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};