<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('queue_applications')->cascadeOnDelete();
            $table->foreignId('service_document_id')->constrained('queue_service_documents');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('disk', 20)->default('local');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->boolean('is_compressed')->default(false);
            $table->unsignedBigInteger('original_size_bytes')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'REVISION_REQUIRED'])->default('PENDING');
            $table->text('reviewer_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['application_id', 'service_document_id'], 'q_app_docs_app_doc_idx');
            $table->index(['application_id', 'status'], 'q_app_docs_app_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_application_documents');
    }
};
