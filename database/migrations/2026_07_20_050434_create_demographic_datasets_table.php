<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demographic_datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('year');
            $table->string('type', 100)->default('population');
            $table->string('file_path')->nullable()->comment('Uploaded PDF/Excel source file');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('file_type', 10)->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->json('data_json')->nullable()->comment('Structured chart-ready data');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['year', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demographic_datasets');
    }
};
