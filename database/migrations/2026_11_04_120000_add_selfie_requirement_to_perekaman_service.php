<?php

use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('queue_service_documents') || !Schema::hasTable('queue_service_types')) {
            return;
        }

        // Find Perekaman KTP-el Baru service type
        $rekam = ServiceType::where('slug', 'perekaman-ktp-el-baru')
            ->orWhere('is_biometric_first_recording', true)
            ->orWhere('name', 'like', '%Perekaman%')
            ->first();

        if (!$rekam) {
            return;
        }

        // 1. Update requires_face_photo to true
        $rekam->update([
            'requires_face_photo' => true,
        ]);

        // 2. Add or update Foto Wajah Pemohon (Selfie Verifikasi)
        $selfieDoc = ServiceDocument::where('service_type_id', $rekam->id)
            ->where(function ($q) {
                $q->where('label', 'like', '%Selfie%')
                  ->orWhere('label', 'like', '%Foto Wajah%');
            })
            ->first();

        if (!$selfieDoc) {
            ServiceDocument::create([
                'service_type_id'  => $rekam->id,
                'label'            => 'Foto Wajah Pemohon (Selfie Verifikasi)',
                'description'      => 'Foto wajah tampak depan pemohon terbaru tanpa kacamata untuk verifikasi biometrik oleh petugas.',
                'accepted_formats' => 'jpg,jpeg,png',
                'max_size_kb'      => 5120,
                'is_required'      => true,
                'capture_hint'     => 'Gunakan kamera depan, tatap lurus ke kamera di tempat terang, tanpa kacamata hitam, masker, atau penutup wajah.',
                'capture_mode'     => 'user',
                'applicable_to'    => 'all',
                'sort_order'       => 3,
            ]);
        } else {
            $selfieDoc->update([
                'is_required'      => true,
                'capture_mode'     => 'user',
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png',
                'max_size_kb'      => 5120,
                'sort_order'       => 3,
            ]);
        }

        // 3. Normalize sort order for all documents under this service
        $docs = ServiceDocument::where('service_type_id', $rekam->id)->get();
        foreach ($docs as $doc) {
            $label = strtolower($doc->label);
            if (str_contains($label, 'kartu keluarga') || str_contains($label, '(kk)')) {
                $doc->update(['sort_order' => 1]);
            } elseif (str_contains($label, 'f-1.02') || str_contains($label, 'f1.02') || str_contains($label, 'peristiwa kependudukan')) {
                $doc->update(['sort_order' => 2]);
            } elseif (str_contains($label, 'selfie') || str_contains($label, 'foto wajah')) {
                $doc->update(['sort_order' => 3]);
            } elseif (str_contains($label, 'akta') || str_contains($label, 'ijazah')) {
                $doc->update(['sort_order' => 4]);
            } elseif (str_contains($label, 'belum pernah rekam')) {
                $doc->update(['sort_order' => 5]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('queue_service_documents') || !Schema::hasTable('queue_service_types')) {
            return;
        }

        $rekam = ServiceType::where('slug', 'perekaman-ktp-el-baru')
            ->orWhere('is_biometric_first_recording', true)
            ->first();

        if (!$rekam) {
            return;
        }

        $rekam->update([
            'requires_face_photo' => false,
        ]);

        ServiceDocument::where('service_type_id', $rekam->id)
            ->where(function ($q) {
                $q->where('label', 'like', '%Selfie%')
                  ->orWhere('label', 'like', '%Foto Wajah%');
            })
            ->delete();
    }
};
