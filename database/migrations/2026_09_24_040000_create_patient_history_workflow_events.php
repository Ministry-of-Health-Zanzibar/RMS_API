<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_history_workflow_events')) {
            return;
        }

        Schema::create('patient_history_workflow_events', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_histories_id');
            $table->string('action', 100);
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('metadata');
            $table->timestamp('undone_at')->nullable();
            $table->unsignedBigInteger('undone_by')->nullable();
            $table->text('undo_reason')->nullable();
            $table->timestamps();

            $table->foreign('patient_histories_id')
                ->references('patient_histories_id')
                ->on('patient_histories')
                ->cascadeOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('undone_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['patient_histories_id', 'created_at']);
            $table->index(['patient_histories_id', 'undone_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_history_workflow_events');
    }
};
