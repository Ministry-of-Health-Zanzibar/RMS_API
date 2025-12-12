<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_histories', function (Blueprint $table) {
            $table->id('patient_histories_id');

            // Basic info
            $table->unsignedBigInteger('patient_id');
            $table->string('referring_doctor')->nullable();
            $table->string('file_number')->nullable();
            $table->string('referring_date')->nullable();

            // Reason
            $table->unsignedBigInteger('reason_id');

            // Medical details
            $table->text('history_of_presenting_illness')->nullable();
            $table->text('physical_findings')->nullable();
            $table->text('investigations')->nullable();
            $table->text('management_done')->nullable();
            $table->text('board_comments')->nullable();

            // Attachment
            $table->string('history_file')->nullable();

            // --- Workflow status ---
            $table->enum('status', [
                'pending',       // from hospital to mkurugenzi
                'reviewed',      // from mkurugenzi to medical board
                'requested',     // from medical board to mkurugenzi
                'approved',      // from mkurugenzi to DG
                'confirmed',     // from DG
                'rejected',      // from DG
            ])->default('pending');

            // --- Comments per reviewer ---
            $table->text('mkurugenzi_tiba_comments')->nullable();
            $table->text('dg_comments')->nullable();

            // --- Reviewer IDs ---
            $table->unsignedBigInteger('mkurugenzi_tiba_id')->nullable();
            $table->unsignedBigInteger('dg_id')->nullable();

            // --- Board Reason ---
            $table->unsignedBigInteger('board_reason_id')->nullable();

            // Foreign keys
            $table->foreign('patient_id')->references('patient_id')->on('patients')->onDelete('cascade');
            $table->foreign('reason_id')->references('reason_id')->on('reasons')->onDelete('cascade');
            $table->foreign('board_reason_id')->references('reason_id')->on('reasons')->onDelete('cascade');
            $table->foreign('mkurugenzi_tiba_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('dg_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_histories');
    }
};

