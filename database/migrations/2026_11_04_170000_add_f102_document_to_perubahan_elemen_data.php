<?php

use App\Models\Download;
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

        $perubahan = ServiceType::where('slug', 'perubahan-elemen-data')->first();
        if (!$perubahan) {
            return;
        }

        $f102Download = null;
        if (Schema::hasTable('downloads')) {
            $f102Download = Download::where(function ($q) {
                $q->where('title', 'like', '%F1.02%')
                  ->orWhere('title', 'like', '%F-1.02%')
                  ->orWhere('title', 'like', '%Pendaftaran Peristiwa%');
            })->first();
        }

        $templateUrl = $f102Download ? url('/downloads/stream/' . $f102Download->id) : url('/downloads/stream/19');

        $f102Doc = ServiceDocument::where('service_type_id', $perubahan->id)
            ->where(function ($q) {
                $q->where('label', 'like', '%F-1.02%')
                  ->orWhere('label', 'like', '%F1.02%')
                  ->orWhere('label', 'like', '%Pendaftaran Peristiwa Kependudukan%');
            })
            ->first();

        $attributes = [
            'service_type_id'  => $perubahan->id,
            'label'            => 'Formulir F-1.02 (Pendaftaran Peristiwa Kependudukan)',
            'description'      => 'Formulir resmi permohonan pendaftaran peristiwa kependudukan yang telah diisi lengkap dan ditandatangani. Blanko formulir dapat diunduh pada link di bawah.',
            'accepted_formats' => 'jpg,jpeg,png,pdf',
            'max_size_kb'      => 5120,
            'is_required'      => true,
            'capture_hint'     => 'Foto seluruh lembar Formulir F-1.02 yang telah diisi dan ditandatangani. Pastikan tulisan terbaca jelas dan tidak terpotong.',
            'capture_mode'     => 'environment',
            'applicable_to'    => 'all',
            'template_url'     => $templateUrl,
            'sort_order'       => 2,
        ];

        if (!$f102Doc) {
            ServiceDocument::create($attributes);
        } else {
            $f102Doc->update($attributes);
        }

        // Normalize sort_order of all documents for Perubahan Elemen Data
        $docs = ServiceDocument::where('service_type_id', $perubahan->id)->get();
        foreach ($docs as $doc) {
            $label = strtolower($doc->label);
            if (str_contains($label, 'ktp-el lama') || str_contains($label, 'ktp lama')) {
                $doc->update(['sort_order' => 1]);
            } elseif (str_contains($label, 'f-1.02') || str_contains($label, 'f1.02') || str_contains($label, 'pendaftaran peristiwa')) {
                $doc->update(['sort_order' => 2]);
            } elseif (str_contains($label, 'kartu keluarga') || str_contains($label, '(kk)')) {
                $doc->update(['sort_order' => 3]);
            } elseif (str_contains($label, 'peristiwa pendukung') || str_contains($label, 'dokumen bukti')) {
                $doc->update(['sort_order' => 4]);
            } elseif (str_contains($label, 'selfie') || str_contains($label, 'foto wajah')) {
                $doc->update(['sort_order' => 5]);
            } elseif (str_contains($label, 'ktp-el perwakilan') || str_contains($label, 'perwakilan')) {
                $doc->update(['sort_order' => 6]);
            } elseif (str_contains($label, 'f-1.07') || str_contains($label, 'surat kuasa')) {
                $doc->update(['sort_order' => 7]);
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

        $perubahan = ServiceType::where('slug', 'perubahan-elemen-data')->first();
        if (!$perubahan) {
            return;
        }

        ServiceDocument::where('service_type_id', $perubahan->id)
            ->where(function ($q) {
                $q->where('label', 'like', '%F-1.02%')
                  ->orWhere('label', 'like', '%F1.02%')
                  ->orWhere('label', 'like', '%Pendaftaran Peristiwa Kependudukan%');
            })
            ->delete();
    }
};
