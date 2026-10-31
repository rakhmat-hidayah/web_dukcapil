<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ktp_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                ->unique()
                ->constrained('queue_applications')
                ->onDelete('cascade');

            $table->string('status', 40)->default('MENUNGGU_KETERSEDIAAN')->index();

            // Readiness verification
            $table->timestamp('ready_marked_at')->nullable();
            $table->foreignId('ready_marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('ready_notes')->nullable();

            // Actual recipient data
            $table->string('actual_receiver_type', 30)->nullable(); // self, representative
            $table->string('actual_receiver_name', 150)->nullable();
            $table->string('actual_receiver_nik', 16)->nullable();
            $table->string('actual_receiver_phone', 20)->nullable();
            $table->string('receiver_relationship', 50)->nullable(); // Sendiri, Suami/Istri, Anak, Orang Tua, Kuasa/Keluarga Lain

            // Handover completion
            $table->timestamp('handover_at')->nullable()->index();
            $table->foreignId('handover_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('handover_notes')->nullable();

            // Photo proof stored in private storage
            $table->string('photo_path', 255)->nullable();
            $table->string('photo_mime', 50)->nullable();
            $table->unsignedBigInteger('photo_size_bytes')->nullable();
            $table->string('photo_hash', 64)->nullable(); // SHA-256

            // Exception tracking
            $table->boolean('has_exception')->default(false)->index();
            $table->text('exception_reason')->nullable();
            $table->timestamp('exception_reported_at')->nullable();
            $table->foreignId('exception_reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('exception_resolved_at')->nullable();
            $table->foreignId('exception_resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('exception_resolution_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ktp_handover_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handover_id')->constrained('ktp_handovers')->onDelete('cascade');
            $table->foreignId('application_id')->constrained('queue_applications')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50); // CREATED, MARKED_READY, HANDOVER_COMPLETED, EXCEPTION_REPORTED, EXCEPTION_RESOLVED
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Seed initial handover records from existing queue_applications that are eligible
        $eligibleApplications = DB::table('queue_applications')
            ->whereIn('status', [
                'PRE_VERIFIED',
                'QUEUE_BOOKED',
                'ARRIVED',
                'PHYSICAL_VERIFICATION',
                'APPROVED_FOR_SERVICE',
                'PROCESSING',
                'READY_FOR_PICKUP',
                'COMPLETED',
            ])
            ->get();

        foreach ($eligibleApplications as $app) {
            $handoverStatus = 'MENUNGGU_KETERSEDIAAN';
            $readyAt = null;
            $handoverAt = null;
            $handoverBy = null;
            $receiverName = null;
            $receiverNik = null;
            $receiverType = null;

            if ($app->status === 'COMPLETED' && $app->handover_at) {
                $handoverStatus = 'SUDAH_DISERAHKAN';
                $readyAt = $app->updated_at ?: $app->handover_at;
                $handoverAt = $app->handover_at;
                $handoverBy = $app->handover_by;
                $receiverType = $app->received_by_type ?: ($app->attendance_type === 'representative' ? 'representative' : 'self');
                $receiverName = $app->receiver_name ?: ($receiverType === 'self' ? $app->applicant_name : $app->representative_name);
                $receiverNik = $app->receiver_nik ?: ($receiverType === 'self' ? $app->applicant_nik : $app->representative_nik);
            } elseif ($app->status === 'READY_FOR_PICKUP') {
                $handoverStatus = 'SIAP_DIAMBIL';
                $readyAt = $app->updated_at;
            }

            $handoverId = DB::table('ktp_handovers')->insertGetId([
                'application_id'        => $app->id,
                'status'                => $handoverStatus,
                'ready_marked_at'       => $readyAt,
                'ready_marked_by'       => $handoverBy,
                'actual_receiver_type'  => $receiverType,
                'actual_receiver_name'  => $receiverName,
                'actual_receiver_nik'   => $receiverNik,
                'actual_receiver_phone' => $app->applicant_phone,
                'receiver_relationship' => $receiverType === 'self' ? 'Sendiri' : ($app->representative_relationship ?: 'Keluarga/Kuasa'),
                'handover_at'           => $handoverAt,
                'handover_by'           => $handoverBy,
                'created_at'            => $app->created_at ?: now(),
                'updated_at'            => now(),
            ]);

            DB::table('ktp_handover_logs')->insert([
                'handover_id'    => $handoverId,
                'application_id' => $app->id,
                'user_id'        => $handoverBy,
                'action'         => 'MIGRATION_INIT',
                'from_status'    => null,
                'to_status'      => $handoverStatus,
                'notes'          => 'Inisialisasi otomatis data permohonan ke modul Serah Terima KTP-el.',
                'created_at'     => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ktp_handover_logs');
        Schema::dropIfExists('ktp_handovers');
    }
};
