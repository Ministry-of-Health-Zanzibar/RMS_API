<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add the column as nullable so old data doesn't break
            $table->unsignedBigInteger('bill_file_id')->nullable()->after('payment_id');
            
            // Add the foreign key constraint
            $table->foreign('bill_file_id')->references('bill_file_id')->on('bill_files');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['bill_file_id']);
            $table->dropColumn('bill_file_id');
        });
    }
};