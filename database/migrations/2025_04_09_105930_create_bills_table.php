<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('referral_id');
            $table->decimal('amount', 10, 2);  // Amount with two decimal places
            $table->text('notes')->nullable();  // Optional notes for the bill
            $table->enum('sent_to', ['Insurance', 'Accountant']);  // Sent to either Insurance or Accountant
            $table->date('sent_date');  // Date when the bill was sent
            $table->string('bill_file')->nullable();  // Path to the uploaded bill file (optional)
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('referral_id')->references('referral_id')->on('referrals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};