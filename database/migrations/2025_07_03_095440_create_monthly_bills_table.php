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
        Schema::create('monthly_bills', function (Blueprint $table) {
            $table->bigIncrements('monthly_bill_id');
            $table->decimal('current_monthly_bill_amount', 15, 2);
            $table->decimal('after_audit_monthly_bill_amount', 15, 2)->nullable();
            $table->unsignedBigInteger('hospital_id');
            $table->date('bill_date');
            $table->string('bill_file')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hospital_id')->references('hospital_id')->on('hospitals');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_bills');
    }
};