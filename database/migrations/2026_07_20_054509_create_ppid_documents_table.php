<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppid_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('category', [
                'informasi_publik',
                'prosedur',
                'layanan_informasi',
            ]);
            $table->string('subcategory')->nullable();  // e.g. "Informasi Berkala", "Informasi Serta Merta"
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();   // stored file path
            $table->string('file_url')->nullable();    // external URL alternative
            $table->string('file_type')->nullable();   // pdf, docx, xlsx
            $table->bigInteger('file_size')->nullable(); // bytes
            $table->year('year')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppid_documents');
    }
};
