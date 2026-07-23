<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            // Unique ticket identifier — format: DKP-2024-000001
            $table->string('ticket_number', 20)->unique();

            // Submitter identity (anonymous-safe: no NIK required)
            $table->string('submitter_name');
            $table->string('submitter_phone', 20)->nullable();
            $table->string('submitter_email', 150)->nullable();
            $table->boolean('is_anonymous')->default(false);

            // Category FK
            $table->foreignId('complaint_category_id')->nullable()->constrained('complaint_categories')->nullOnDelete();

            // Complaint content
            $table->string('subject');
            $table->text('message');
            $table->string('attachment_path')->nullable()->comment('Optional supporting file upload');
            $table->string('attachment_name')->nullable();

            // Workflow status
            $table->enum('status', [
                'pending',      // Baru masuk, belum diproses
                'in_review',    // Sedang dikaji admin
                'in_progress',  // Proses penanganan
                'resolved',     // Selesai ditangani
                'rejected',     // Ditolak/tidak valid
            ])->default('pending');

            // CAPTCHA verification token (stored hash for server-side validation)
            $table->string('captcha_token', 64)->nullable();

            // Tracking data
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('read_at')->nullable()->comment('When admin first opened it');
            $table->timestamp('resolved_at')->nullable();

            // Assigned admin
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('ticket_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
