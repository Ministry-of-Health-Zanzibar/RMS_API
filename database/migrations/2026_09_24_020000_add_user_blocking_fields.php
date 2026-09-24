<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_blocked')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('is_blocked')->default(false);
                $table->timestamp('blocked_at')->nullable();
                $table->unsignedBigInteger('blocked_by')->nullable();
                $table->text('blocked_reason')->nullable();
                $table->index('is_blocked', 'users_is_blocked_idx');
                $table->foreign('blocked_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'is_blocked')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['blocked_by']);
            $table->dropIndex('users_is_blocked_idx');
            $table->dropColumn(['is_blocked', 'blocked_at', 'blocked_by', 'blocked_reason']);
        });
    }
};
