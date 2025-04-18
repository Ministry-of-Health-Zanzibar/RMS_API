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
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('payment_id');
            $table->unsignedBigInteger('bill_id');
            $table->decimal('amount_paid', 12, 2);
            $table->string('payment_method')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bill_id')->references('bill_id')->on('bills');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
