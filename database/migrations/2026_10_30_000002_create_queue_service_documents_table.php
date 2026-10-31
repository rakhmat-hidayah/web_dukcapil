<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_service_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained('queue_service_types')->cascadeOnDelete();
            $table->string('label');
            $table->string('description', 500)->nullable();
            $table->string('accepted_formats', 100)->default('jpg,jpeg,png,pdf')->comment('comma-separated extensions');
            $table->integer('max_size_kb')->default(5120)->comment('5MB default');
            $table->boolean('is_required')->default(true);
            $table->string('capture_hint', 300)->nullable()->comment('Panduan posisi & pencahayaan');
            $table->enum('capture_mode', ['environment', 'user', 'any'])->default('environment');
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['service_type_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_service_documents');
    }
};
