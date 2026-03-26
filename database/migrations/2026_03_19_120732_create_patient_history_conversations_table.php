<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_history_conversations', function (Blueprint $table) {
            $table->id('conversation_id');
            $table->unsignedBigInteger('patient_history_id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); 
            $table->text('message');
            $table->string('case_status_at_time')->nullable();
            $table->string('attachment_file')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('conversation_id')->on('patient_history_conversations')->onDelete('cascade');
            $table->foreign('patient_history_id')->references('patient_histories_id')->on('patient_histories')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('receiver_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_history_conversations');
    }
};
