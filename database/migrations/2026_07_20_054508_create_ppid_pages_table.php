<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppid_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // pengertian, profil, tugas-fungsi, kontak, sk-ppid
            $table->string('title');
            $table->string('icon')->nullable();        // lucide icon name
            $table->longText('content');               // rich HTML content
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppid_pages');
    }
};
