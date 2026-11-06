<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbarui archive_settings agar status eligible_statuses mencakup READY_FOR_PICKUP dan COMPLETED
        if (Schema::hasTable('archive_settings')) {
            $setting = DB::table('archive_settings')->where('key', 'eligible_statuses')->first();
            if ($setting) {
                $statuses = json_decode($setting->value, true) ?: [];
                if (!in_array('READY_FOR_PICKUP', $statuses)) {
                    $statuses[] = 'READY_FOR_PICKUP';
                }
                if (!in_array('COMPLETED', $statuses)) {
                    $statuses[] = 'COMPLETED';
                }
                DB::table('archive_settings')->where('key', 'eligible_statuses')->update([
                    'value' => json_encode(array_values(array_unique($statuses))),
                    'description' => 'Daftar status permohonan yang dapat dimasukkan ke paket arsip (Siap Diambil & Selesai)',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('archive_settings')->insert([
                    'key' => 'eligible_statuses',
                    'value' => json_encode(['READY_FOR_PICKUP', 'COMPLETED']),
                    'description' => 'Daftar status permohonan yang dapat dimasukkan ke paket arsip (Siap Diambil & Selesai)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Sinkronisasi data permohonan dan serah terima:
        // Jika KTP fisik sudah ditandai SIAP_DIAMBIL, pastikan status permohonan adalah READY_FOR_PICKUP (jika belum COMPLETED)
        if (Schema::hasTable('ktp_handovers') && Schema::hasTable('queue_applications')) {
            DB::table('queue_applications')
                ->join('ktp_handovers', 'queue_applications.id', '=', 'ktp_handovers.application_id')
                ->where('ktp_handovers.status', 'SIAP_DIAMBIL')
                ->whereNotIn('queue_applications.status', ['READY_FOR_PICKUP', 'COMPLETED'])
                ->update([
                    'queue_applications.status' => 'READY_FOR_PICKUP',
                    'queue_applications.updated_at' => now(),
                ]);

            // Sebaliknya, jika queue_applications berstatus READY_FOR_PICKUP, pastikan ktp_handovers berstatus SIAP_DIAMBIL
            DB::table('ktp_handovers')
                ->join('queue_applications', 'ktp_handovers.application_id', '=', 'queue_applications.id')
                ->where('queue_applications.status', 'READY_FOR_PICKUP')
                ->where('ktp_handovers.status', 'MENUNGGU_KETERSEDIAAN')
                ->update([
                    'ktp_handovers.status' => 'SIAP_DIAMBIL',
                    'ktp_handovers.ready_marked_at' => now(),
                    'ktp_handovers.updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback to preserve application states
    }
};
