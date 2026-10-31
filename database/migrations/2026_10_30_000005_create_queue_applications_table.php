<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reg_number', 30)->unique()->comment('Nomor registrasi sementara');
            $table->string('access_token', 64)->unique()->comment('Token akses publik pemohon');
            $table->foreignId('service_type_id')->constrained('queue_service_types');
            $table->foreignId('queue_slot_id')->nullable()->constrained('queue_slots')->nullOnDelete();
            $table->string('queue_number', 20)->nullable()->comment('Nomor antrian final setelah disetujui');
            $table->enum('status', [
                'DRAFT', 'SUBMITTED', 'UNDER_REVIEW',
                'REVISION_REQUIRED', 'APPROVED', 'REJECTED',
                'SCHEDULED', 'CHECKED_IN', 'IN_SERVICE', 'COMPLETED', 'CANCELLED'
            ])->default('DRAFT');
            // Data Pemohon
            $table->string('applicant_name');
            $table->string('applicant_nik', 16);
            $table->string('applicant_phone', 20);
            $table->string('applicant_email', 150)->nullable();
            $table->text('applicant_address');
            $table->string('applicant_village', 100)->nullable();
            $table->string('applicant_district', 100)->nullable();
            // Notes & Reasons
            $table->text('notes')->nullable()->comment('Catatan internal petugas');
            $table->text('rejection_reason')->nullable();
            $table->text('revision_notes')->nullable();
            // Timestamps status
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'submitted_at']);
            $table->index('access_token');
            $table->index('reg_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_applications');
    }
};
