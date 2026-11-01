<?php

use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensure Formulir F-1.07 (Surat Kuasa) is present and required for all service types
     * that allow representation (Cetak Hilang, Rusak, Perubahan Elemen Data, dll).
     */
    public function up(): void
    {
        if (!Schema::hasTable('queue_service_documents') || !Schema::hasTable('queue_service_types')) {
            return;
        }

        $downloadUrl = 'https://dukcapil.dompukab.go.id/downloads';
        $f107Label = 'Formulir F-1.07 / F.017 (Surat Kuasa dalam Pelayanan Administrasi Kependudukan)';
        $f107Desc = 'Wajib jika permohonan diwakilkan. Surat Kuasa bermeterai Rp 10.000 yang ditandatangani oleh pemberi dan penerima kuasa. Blanko formulir dapat diunduh pada link di bawah.';
        $hasTemplateUrl = Schema::hasColumn('queue_service_documents', 'template_url');

        $services = ServiceType::all();

        foreach ($services as $service) {
            $allowsRepresentation = (bool) (
                $service->allow_family_in_kk
                || $service->allow_family_out_kk
                || $service->allow_other
                || $service->require_power_of_attorney
            );

            if (!$allowsRepresentation) {
                continue;
            }

            // 1. Find all candidate Kuasa / F-1.07 documents for this service
            $kuasaDocs = ServiceDocument::where('service_type_id', $service->id)
                ->where(function ($q) {
                    $q->where('label', 'like', '%Kuasa%')
                      ->orWhere('label', 'like', '%F-1.07%')
                      ->orWhere('label', 'like', '%F1.07%')
                      ->orWhere('label', 'like', '%F.017%');
                })
                ->orderBy('id')
                ->get();

            if ($kuasaDocs->isEmpty()) {
                // Determine appropriate sort order (after regular docs)
                $maxSort = ServiceDocument::where('service_type_id', $service->id)->max('sort_order') ?? 5;
                $sortOrder = max(10, $maxSort + 1);

                $payload = [
                    'service_type_id'  => $service->id,
                    'label'            => $f107Label,
                    'description'      => $f107Desc,
                    'is_required'      => true,
                    'applicable_to'    => 'representative_only',
                    'accepted_formats' => 'jpg,jpeg,png,pdf',
                    'max_size_kb'      => 5120,
                    'capture_hint'     => 'Foto Formulir Surat Kuasa F-1.07 yang telah ditandatangani di atas meterai Rp 10.000 secara utuh dan jelas.',
                    'capture_mode'     => 'environment',
                    'sort_order'       => $sortOrder,
                ];

                if ($hasTemplateUrl) {
                    $payload['template_url'] = $downloadUrl;
                }

                ServiceDocument::create($payload);
            } else {
                $canonical = $kuasaDocs->first();
                $updateData = [
                    'label'            => $f107Label,
                    'description'      => $f107Desc,
                    'is_required'      => true,
                    'applicable_to'    => 'representative_only',
                    'accepted_formats' => 'jpg,jpeg,png,pdf',
                    'max_size_kb'      => 5120,
                    'capture_hint'     => 'Foto Formulir Surat Kuasa F-1.07 yang telah ditandatangani di atas meterai Rp 10.000 secara utuh dan jelas.',
                    'capture_mode'     => 'environment',
                ];

                if ($hasTemplateUrl) {
                    $updateData['template_url'] = $downloadUrl;
                }

                $canonical->update($updateData);

                // Consolidate duplicates if any existed
                if ($kuasaDocs->count() > 1) {
                    foreach ($kuasaDocs->slice(1) as $dup) {
                        DB::table('queue_application_documents')
                            ->where('service_document_id', $dup->id)
                            ->update(['service_document_id' => $canonical->id]);
                        $dup->delete();
                    }
                }
            }

            // 2. Consolidate duplicate "KTP-el Perwakilan" documents or create if missing
            $ktpPerwakilanDocs = ServiceDocument::where('service_type_id', $service->id)
                ->where(function ($q) {
                    $q->where('label', 'like', '%Perwakilan%')
                      ->orWhere('label', 'like', '%Penerima Kuasa%');
                })
                ->orderBy('id')
                ->get();

            if ($ktpPerwakilanDocs->isEmpty()) {
                ServiceDocument::create([
                    'service_type_id'  => $service->id,
                    'label'            => 'KTP-el Perwakilan (Jika Diwakilkan)',
                    'description'      => 'Foto KTP-el asli anggota keluarga atau pihak yang mewakili tampak depan.',
                    'is_required'      => true,
                    'applicable_to'    => 'representative_only',
                    'accepted_formats' => 'jpg,jpeg,png',
                    'max_size_kb'      => 5120,
                    'capture_hint'     => 'Foto KTP asli perwakilan tampak depan dengan jelas.',
                    'capture_mode'     => 'environment',
                    'sort_order'       => 5,
                ]);
            } elseif ($ktpPerwakilanDocs->count() > 1) {
                $canonicalKtp = $ktpPerwakilanDocs->first();
                $canonicalKtp->update([
                    'label'         => 'KTP-el Perwakilan (Jika Diwakilkan)',
                    'description'   => 'Foto KTP-el asli anggota keluarga atau pihak yang mewakili tampak depan.',
                    'is_required'   => true,
                    'applicable_to' => 'representative_only',
                ]);

                foreach ($ktpPerwakilanDocs->slice(1) as $dupKtp) {
                    DB::table('queue_application_documents')
                        ->where('service_document_id', $dupKtp->id)
                        ->update(['service_document_id' => $canonicalKtp->id]);
                    $dupKtp->delete();
                }
            } else {
                $ktpPerwakilanDocs->first()->update([
                    'is_required'   => true,
                    'applicable_to' => 'representative_only',
                ]);
            }

            // 3. Consolidate duplicate Surat Kehilangan if any exist for service 1
            if ($service->slug === 'cetak-ktp-hilang') {
                $kehilanganDocs = ServiceDocument::where('service_type_id', $service->id)
                    ->where('label', 'like', '%Kehilangan%')
                    ->orderBy('id')
                    ->get();

                if ($kehilanganDocs->count() > 1) {
                    $canonicalKehilangan = $kehilanganDocs->first();
                    foreach ($kehilanganDocs->slice(1) as $dupKehilangan) {
                        DB::table('queue_application_documents')
                            ->where('service_document_id', $dupKehilangan->id)
                            ->update(['service_document_id' => $canonicalKehilangan->id]);
                        $dupKehilangan->delete();
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op down to preserve document requirements
    }
};
