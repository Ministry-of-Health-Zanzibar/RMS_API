<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_letters', function (Blueprint $table) {
            $table->timestamp('printed_at')->nullable()->after('is_printed');
            $table->unsignedBigInteger('printed_by')->nullable()->after('printed_at');
            $table->unsignedInteger('print_count')->default(0)->after('printed_by');
            $table->string('last_printed_language', 2)->nullable()->after('print_count');
            $table->foreign('printed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('hospital_letters', function (Blueprint $table) {
            $table->boolean('is_printed')->default(false)->after('outcome');
            $table->timestamp('printed_at')->nullable()->after('is_printed');
            $table->unsignedBigInteger('printed_by')->nullable()->after('printed_at');
            $table->unsignedInteger('print_count')->default(0)->after('printed_by');
            $table->string('last_printed_language', 2)->nullable()->after('print_count');
            $table->foreign('printed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('boarded_out_letters', function (Blueprint $table) {
            $table->boolean('is_printed')->default(false)->after('recommendations');
            $table->timestamp('printed_at')->nullable()->after('is_printed');
            $table->unsignedBigInteger('printed_by')->nullable()->after('printed_at');
            $table->unsignedInteger('print_count')->default(0)->after('printed_by');
            $table->string('last_printed_language', 2)->nullable()->after('print_count');
            $table->foreign('printed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('letter_print_events', function (Blueprint $table) {
            $table->bigIncrements('letter_print_event_id');
            $table->string('letter_type', 30);
            $table->unsignedBigInteger('letter_id');
            $table->string('language', 2);
            $table->unsignedBigInteger('printed_by')->nullable();
            $table->timestamp('printed_at');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('printed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['letter_type', 'letter_id']);
            $table->index(['printed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_print_events');

        Schema::table('hospital_letters', function (Blueprint $table) {
            $table->dropForeign(['printed_by']);
            $table->dropColumn([
                'is_printed',
                'printed_at',
                'printed_by',
                'print_count',
                'last_printed_language',
            ]);
        });

        Schema::table('boarded_out_letters', function (Blueprint $table) {
            $table->dropForeign(['printed_by']);
            $table->dropColumn([
                'is_printed',
                'printed_at',
                'printed_by',
                'print_count',
                'last_printed_language',
            ]);
        });

        Schema::table('referral_letters', function (Blueprint $table) {
            $table->dropForeign(['printed_by']);
            $table->dropColumn([
                'printed_at',
                'printed_by',
                'print_count',
                'last_printed_language',
            ]);
        });
    }
};
