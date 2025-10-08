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

            // Patient reference
            $table->unsignedBigInteger('patient_id');

            // Referring doctor as text
            $table->string('referring_doctor')->nullable();

            // Referral / record info
            $table->string('file_number')->nullable();
            $table->date('referring_date')->nullable();

            // Medical details
            $table->text('history_of_presenting_illness')->nullable();
            $table->text('physical_findings')->nullable();
            $table->text('investigations')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('management_done')->nullable();

            // Attachments
            $table->string('history_file')->nullable();

            $table->foreign('patient_id')->references('patient_id')->on('patients')->onDelete('cascade');

            // Timestamps
            $table->timestamps();

            // Soft delete column
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
