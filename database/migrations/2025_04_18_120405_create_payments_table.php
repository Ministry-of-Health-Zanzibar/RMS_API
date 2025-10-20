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
            $table->string('payer'); // e.g., NHIF, Patient, Insurance, Patient, Employer, Ministry of Health (GOV)
            $table->decimal('amount_paid', 15, 2);
            $table->string('currency')->default('TZS');
            $table->enum('payment_method', [
                'Cash',
                'Bank Transfer / Wire Transfer',
                'Mobile Money',
                'Credit Card',
                'Debit Card',
                'Cheque / Check',
                'Online Payment Platforms',
                'Cryptocurrency',
                'Gift Card / Voucher',
                'Direct Debit / Standing Order'
            ])->nullable(); // e.g., Bank Transfer, Cash
            $table->string('reference_number')->nullable(); // external payment reference
            $table->string('voucher_number')->nullable(); // internal ledger/voucher number
            $table->string('payment_date');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users'); 
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
