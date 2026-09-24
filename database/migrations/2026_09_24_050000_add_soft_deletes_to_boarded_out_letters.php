<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('boarded_out_letters') && ! Schema::hasColumn('boarded_out_letters', 'deleted_at')) {
            Schema::table('boarded_out_letters', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('boarded_out_letters') && Schema::hasColumn('boarded_out_letters', 'deleted_at')) {
            Schema::table('boarded_out_letters', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};
