<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('queue_service_documents')
            ->where('capture_mode', 'user')
            ->orWhere('label', 'like', '%Selfie%')
            ->update([
                'description'  => 'Foto wajah tampak depan pemohon asli (bukan yang diwakilkan atau dikuasakan) terbaru untuk verifikasi biometrik oleh petugas.',
                'capture_hint' => 'Gunakan kamera depan, tatap lurus ke kamera di tempat terang, tanpa kacamata hitam, masker, atau penutup wajah. Wajib foto selfie Pemohon asli, bukan foto pihak yang mewakili atau dikuasakan.',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('queue_service_documents')
            ->where('capture_mode', 'user')
            ->orWhere('label', 'like', '%Selfie%')
            ->update([
                'description'  => 'Foto wajah tampak depan pemohon terbaru tanpa kacamata untuk verifikasi biometrik oleh petugas.',
                'capture_hint' => 'Gunakan kamera depan, tatap lurus ke kamera di tempat terang, tanpa kacamata hitam, masker, atau penutup wajah.',
            ]);
    }
};
