<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 300)->nullable();
            $table->text('long_description')->nullable();
            $table->string('icon', 100)->nullable()->comment('Lucide icon name or emoji');
            $table->string('color', 20)->default('#2563eb');
            $table->integer('quota_per_day')->default(20);
            $table->string('processing_days_label')->default('1-3 Hari Kerja');
            $table->string('cost_label')->default('Gratis / Rp 0');
            $table->boolean('requires_face_photo')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_service_types');
    }
};
