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
        Schema::create('service_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon', 50)->nullable()->comment('Emoji or Lucide icon name');
            $table->string('color', 20)->default('#2563eb');
            $table->string('description', 500)->nullable();
            $table->text('requirements')->comment('The detailed bullet-point list of requirements');
            $table->string('processing_time')->default('1 Hari Kerja');
            $table->string('cost')->default('Gratis / Rp 0');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requirements');
    }
};
