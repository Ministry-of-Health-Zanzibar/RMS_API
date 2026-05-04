<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_history_conversation_statuses', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
        
            // unread / read tracking
            $table->timestamp('read_at')->nullable();
        
            // notification control
            $table->boolean('is_notified')->default(false);
        
            $table->timestamps();
        
            $table->unique(['conversation_id', 'user_id']);
        
            $table->foreign('conversation_id')
                ->references('conversation_id')
                ->on('patient_history_conversations')
                ->onDelete('cascade');
        
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_history_conversation_statuses');
    }
};
