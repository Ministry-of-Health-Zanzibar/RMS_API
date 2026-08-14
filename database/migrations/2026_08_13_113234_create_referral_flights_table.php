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
        Schema::create('referral_flights', function (Blueprint $table) {
            $table->id('referral_flight_id');

            $table->foreignId('referral_id')->constrained('referrals', 'referral_id')->cascadeOnDelete();

            $table->date('arrival_date')->nullable();
            $table->time('arrival_time')->nullable();

            $table->string('arrival_airport')->nullable();

            $table->string('airline')->nullable();

            $table->string('flight_number')->nullable();

            $table->string('departure_city')->nullable();

            $table->string('departure_airport')->nullable();

            $table->string('arrival_city')->nullable();

            $table->string('terminal')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users', 'id')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_flights');
    }
};
