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
                'hospital_submitted',
                'mkurugenzi_tiba_review',
                'medical_board_review',
                'returned_to_mkurugenzi',
                'approved_by_mkurugenzi',
                'sent_to_dg',
                'approved_by_dg',
                'rejected',
            ])->default('hospital_submitted')->after('history_file');

            // --- Comments per reviewer ---
            $table->text('mkurugenzi_tiba_comments')->nullable()->after('status');
            // $table->text('medical_board_comments')->nullable()->after('mkurugenzi_tiba_comments');
            $table->text('dg_comments')->nullable()->after('medical_board_comments');

            // --- Reviewer IDs ---
            $table->unsignedBigInteger('mkurugenzi_tiba_id')->nullable()->after('dg_comments');
            // $table->unsignedBigInteger('medical_board_id')->nullable()->after('mkurugenzi_tiba_id');
            $table->unsignedBigInteger('dg_id')->nullable()->after('medical_board_id');

            // --- Foreign key relationships ---
            $table->foreign('mkurugenzi_tiba_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // $table->foreign('medical_board_id')
            //     ->references('id')
            //     ->on('users')
            //     ->onDelete('set null');

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
                // 'medical_board_comments',
                'dg_comments',
                'mkurugenzi_tiba_id',
                // 'medical_board_id',
                'dg_id',
            ]);
        });
    }
};

