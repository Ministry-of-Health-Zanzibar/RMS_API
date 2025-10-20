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
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->bigIncrements('bill_payment_id');
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('payment_id');
            $table->decimal('allocated_amount', 15, 2); // Amount from payment applied to this bill
            $table->string('allocation_date')->nullable();
            $table->enum('status', ['Pending', 'Partially Paid', 'Paid'])->default('Pending');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('bill_id')->references('bill_id')->on('bills');
            $table->foreign('payment_id')->references('payment_id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};
