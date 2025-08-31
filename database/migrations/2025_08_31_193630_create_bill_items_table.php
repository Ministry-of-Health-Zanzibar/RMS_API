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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->bigIncrements('bill_item_id');
            $table->unsignedBigInteger('bill_id');
            $table->string('description'); // e.g., Lab Test, Ward, Surgery
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->foreign('bill_id')->references('bill_id')->on('bills');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
