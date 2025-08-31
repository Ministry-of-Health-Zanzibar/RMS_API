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
            $table->unsignedBigInteger('referral_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('sent_to', ['Insurance', 'Accountant']);
            $table->date('sent_date');
            $table->unsignedBigInteger('bill_file_id');
            $table->enum('bill_status', ['Pending', 'Partially Paid', 'Paid'])->default('Pending');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('referral_id')->references('referral_id')->on('referrals');
            $table->foreign('bill_file_id')->references('bill_file_id')->on('bill_files');
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
