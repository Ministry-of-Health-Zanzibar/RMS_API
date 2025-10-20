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
            $table->unsignedBigInteger('bill_file_id');

            $table->decimal('total_amount', 12, 2);
            $table->date('bill_period_start');
            $table->date('bill_period_end');
            $table->enum('bill_status', ['Pending', 'Partially Paid', 'Paid'])->default('Pending');
    
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('referral_id')->references('referral_id')->on('referrals');
            $table->foreign('bill_file_id')->references('bill_file_id')->on('bill_files');
            $table->unique(['referral_id', 'bill_file_id']); // Ensure referral cannot be billed twice in the same bill_file
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
