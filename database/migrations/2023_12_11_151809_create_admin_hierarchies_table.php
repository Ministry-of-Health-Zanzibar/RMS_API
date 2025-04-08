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
        Schema::create('admin_hierarchies', function (Blueprint $table) {
            $table->string('admin_hierarchy_id',10)->primary()->uniqid();
            $table->uuid('uuid');
            $table->string('admin_hierarchy_name', 150);
            $table->string('parent_id',10)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_hierarchies');
    }
};
