<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('splash_screens', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->enum('content_type', ['ikm_widget', 'announcement', 'image', 'custom'])->default('announcement');
            $table->longText('custom_content')->nullable();   // HTML / teks untuk announcement/custom
            $table->string('image_path')->nullable();         // path gambar (untuk type image)
            $table->string('button_label')->nullable();       // teks tombol aksi
            $table->string('button_url')->nullable();         // URL tombol aksi
            $table->boolean('button_external')->default(false);
            $table->enum('show_frequency', ['once_per_session', 'once_per_day', 'always'])->default('once_per_day');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splash_screens');
    }
};
