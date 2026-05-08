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
        Schema::create('boarded_out_letters', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('patient_histories_id')->nullable();
        
            $table->string('receiver')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('reference_date')->nullable();
        
            // store recommendations as JSON
            $table->json('recommendations')->nullable();
        
            $table->timestamps();
        
            $table->foreign('patient_histories_id')
                ->references('patient_histories_id')
                ->on('patient_histories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boarded_out_letters');
    }
};
