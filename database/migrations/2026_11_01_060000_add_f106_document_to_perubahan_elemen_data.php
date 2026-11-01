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
     * Add mandatory Formulir F-1.06 (Surat Pernyataan Perubahan Elemen Data Kependudukan)
     * specifically for "Perubahan Elemen Data KTP-el" service with download link to Download Center.
     */
    public function up(): void
    {
        if (!Schema::hasTable('queue_service_documents') || !Schema::hasTable('queue_service_types')) {
            return;
        }

        $perubahanService = ServiceType::where('slug', 'perubahan-elemen-data')->first();
        if (!$perubahanService) {
            return;
        }

        // Find F1.06 download form from Download Center if available
        $downloadItem = Download::where(function ($q) {
            $q->where('title', 'like', '%F1.06%')
              ->orWhere('title', 'like', '%F-1.06%')
              ->orWhere('title', 'like', '%Perubahan Elemen Data%');
        })->first();

        $downloadUrl = $downloadItem ? url("/downloads/stream/{$downloadItem->id}") : 'https://dukcapil.dompukab.go.id/downloads';
        $hasTemplateUrl = Schema::hasColumn('queue_service_documents', 'template_url');

        $f106Label = 'Formulir F-1.06 (Surat Pernyataan Perubahan Elemen Data Kependudukan)';
        $f106Desc = 'Formulir resmi surat pernyataan perubahan elemen data kependudukan yang telah diisi lengkap dan ditandatangani. Blanko formulir dapat diunduh pada link di bawah.';
        $f106Hint = 'Foto atau scan seluruh lembar Formulir F-1.06 yang telah diisi dan ditandatangani secara jelas dan tidak terpotong.';

        $existing = ServiceDocument::where('service_type_id', $perubahanService->id)
            ->where(function ($q) {
                $q->where('label', 'like', '%F-1.06%')
                  ->orWhere('label', 'like', '%F1.06%')
                  ->orWhere('label', 'like', '%Perubahan Elemen Data%');
            })
            ->first();

        $payload = [
            'service_type_id'  => $perubahanService->id,
            'label'            => $f106Label,
            'description'      => $f106Desc,
            'is_required'      => true,
            'applicable_to'    => 'all',
            'accepted_formats' => 'jpg,jpeg,png,pdf',
            'max_size_kb'      => 5120,
            'capture_hint'     => $f106Hint,
            'capture_mode'     => 'environment',
            'sort_order'       => 2,
        ];

        if ($hasTemplateUrl) {
            $payload['template_url'] = $downloadUrl;
        }

        if (!$existing) {
            ServiceDocument::create($payload);
        } else {
            $existing->update($payload);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
