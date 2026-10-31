<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_applications', function (Blueprint $table) {
            // Data Pemohon Tambahan
            $table->string('applicant_birth_place', 100)->nullable()->after('applicant_nik');
            $table->date('applicant_birth_date')->nullable()->after('applicant_birth_place');

            // Data Kehadiran & Perwakilan
            $table->string('attendance_type', 30)->default('self')->after('applicant_district')
                ->comment('self, family_in_kk, family_out_kk, other');
            $table->string('representative_nik', 16)->nullable()->after('attendance_type');
            $table->string('representative_name', 150)->nullable()->after('representative_nik');
            $table->string('representative_phone', 20)->nullable()->after('representative_name');
            $table->string('representative_relationship', 100)->nullable()->after('representative_phone');
            $table->string('representative_kk_status', 100)->nullable()->after('representative_relationship');
            $table->text('absence_reason')->nullable()->after('representative_kk_status');

            // Pernyataan Integritas Pemohon (Tahap 5)
            $table->boolean('statement_agreed')->default(false)->after('absence_reason');
            $table->timestamp('statement_agreed_at')->nullable()->after('statement_agreed');

            // Verifikasi Fisik & Check-In di Loket
            $table->foreignId('checked_in_by')->nullable()->after('checked_in_at')->constrained('users')->nullOnDelete();
            $table->timestamp('physical_verified_at')->nullable()->after('checked_in_by');
            $table->foreignId('physical_verified_by')->nullable()->after('physical_verified_at')->constrained('users')->nullOnDelete();
            $table->string('physical_verification_decision', 50)->nullable()->after('physical_verified_by');
            $table->text('physical_verification_notes')->nullable()->after('physical_verification_decision');
            $table->boolean('physical_match_identity')->nullable()->after('physical_verification_notes');
            $table->boolean('physical_match_documents')->nullable()->after('physical_match_identity');
            $table->boolean('physical_match_authorization')->nullable()->after('physical_match_documents');
            $table->string('attended_person_type', 30)->nullable()->after('physical_match_authorization');
            $table->string('attended_person_name', 150)->nullable()->after('attended_person_type');
            $table->string('attended_person_nik', 16)->nullable()->after('attended_person_name');

            // Penyerahan Dokumen (Handover)
            $table->timestamp('handover_at')->nullable()->after('attended_person_nik');
            $table->foreignId('handover_by')->nullable()->after('handover_at')->constrained('users')->nullOnDelete();
            $table->string('received_by_type', 30)->nullable()->after('handover_by')->comment('self, representative');
            $table->string('receiver_name', 150)->nullable()->after('received_by_type');
            $table->string('receiver_nik', 16)->nullable()->after('receiver_name');
            $table->string('receiver_phone', 20)->nullable()->after('receiver_nik');
            $table->text('handover_notes')->nullable()->after('receiver_phone');

            // Mengubah status menjadi string(40) agar fleksibel mendukung seluruh enum alur baru
            $table->string('status', 40)->default('DRAFT')->change();
        });
    }

    public function down(): void
    {
        Schema::table('queue_applications', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->dropForeign(['physical_verified_by']);
            $table->dropForeign(['handover_by']);

            $table->dropColumn([
                'applicant_birth_place',
                'applicant_birth_date',
                'attendance_type',
                'representative_nik',
                'representative_name',
                'representative_phone',
                'representative_relationship',
                'representative_kk_status',
                'absence_reason',
                'statement_agreed',
                'statement_agreed_at',
                'checked_in_by',
                'physical_verified_at',
                'physical_verified_by',
                'physical_verification_decision',
                'physical_verification_notes',
                'physical_match_identity',
                'physical_match_documents',
                'physical_match_authorization',
                'attended_person_type',
                'attended_person_name',
                'attended_person_nik',
                'handover_at',
                'handover_by',
                'received_by_type',
                'receiver_name',
                'receiver_nik',
                'receiver_phone',
                'handover_notes',
            ]);
        });
    }
};
