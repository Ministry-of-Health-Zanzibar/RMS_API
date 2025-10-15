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
        Schema::create('patient_histories', function (Blueprint $table) {
            $table->id('patient_histories_id');

            $table->unsignedBigInteger('patient_id');
            $table->string('referring_doctor')->nullable();
            $table->string('file_number')->nullable();
            $table->date('referring_date')->nullable();
            $table->unsignedBigInteger('reason_id');
            // Medical details
            $table->text('history_of_presenting_illness')->nullable();
            $table->text('physical_findings')->nullable();
            $table->text('investigations')->nullable();
            $table->text('management_done')->nullable();
            $table->text('board_comments')->nullable();
            // Medical details
            $table->string('history_file')->nullable();// Attachments
            $table->foreign('patient_id')->references('patient_id')->on('patients')->onDelete('cascade');
            $table->foreign('reason_id')->references('reason_id')->on('reasons')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_histories');
    }
};
