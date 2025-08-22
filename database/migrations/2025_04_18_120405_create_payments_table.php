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
            $table->unsignedBigInteger('monthly_bill_id');
            $table->decimal('amount_paid', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable(); // external payment reference
            $table->string('voucher_number')->nullable(); // internal ledger/voucher number
            $table->unsignedBigInteger('paid_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('monthly_bill_id')->references('monthly_bill_id')->on('monthly_bills');
            $table->foreign('paid_by')->references('id')->on('users'); 
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
