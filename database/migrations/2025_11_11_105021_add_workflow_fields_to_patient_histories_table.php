<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_histories', function (Blueprint $table) {
            // --- Workflow status ---
            $table->enum('status', [
                'pending', // from hospital to mkurugenzi
                'reviewed', // from mkurugenzi to medical board
                'requested', // from medical board to mkurugenzi
                'approved', // from mkurugenzi to DG
                'confirmed', // from DG
                'rejected', // from DG
            ])->default('pending')->after('history_file');
            
            $table->text('board_diagnosis_ids')->nullable();
            $table->text('board_reason')->nullable();

            // --- Comments per reviewer ---
            $table->text('mkurugenzi_tiba_comments')->nullable()->after('status');
            $table->text('dg_comments')->nullable()->after('medical_board_comments');

            // --- Reviewer IDs ---
            $table->unsignedBigInteger('mkurugenzi_tiba_id')->nullable()->after('dg_comments');
            $table->unsignedBigInteger('dg_id')->nullable()->after('medical_board_id');

            // --- Foreign key relationships ---
            $table->foreign('mkurugenzi_tiba_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('dg_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('patient_histories', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['mkurugenzi_tiba_id']);
            $table->dropForeign(['medical_board_id']);
            $table->dropForeign(['dg_id']);

            // Then drop the added columns
            $table->dropColumn([
                'status',
                'mkurugenzi_tiba_comments',
                'dg_comments',
                'mkurugenzi_tiba_id',
                'dg_id',
            ]);
        });
    }
};

