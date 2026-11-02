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
     * Synchronize and cleanly order all requirements and form templates across all KTP-el service types.
     */
    public function up(): void
    {
        if (!Schema::hasTable('queue_service_documents') || !Schema::hasTable('queue_service_types')) {
            return;
        }

        $f102Download = Download::where(fn($q) => $q->where('title', 'like', '%F1.02%')->orWhere('title', 'like', '%F-1.02%'))->first();
        $f106Download = Download::where(fn($q) => $q->where('title', 'like', '%F1.06%')->orWhere('title', 'like', '%F-1.06%'))->first();
        $f107Download = Download::where(fn($q) => $q->where('title', 'like', '%F1.07%')->orWhere('title', 'like', '%F-1.07%'))->first();

        $f102Url = $f102Download ? url("/downloads/stream/{$f102Download->id}") : 'https://dukcapil.dompukab.go.id/downloads';
        $f106Url = $f106Download ? url("/downloads/stream/{$f106Download->id}") : 'https://dukcapil.dompukab.go.id/downloads';
        $f107Url = $f107Download ? url("/downloads/stream/{$f107Download->id}") : 'https://dukcapil.dompukab.go.id/downloads';

        $hasTemplateUrl = Schema::hasColumn('queue_service_documents', 'template_url');

        // 1. Cetak KTP-el Karena Hilang (cetak-ktp-hilang)
        $hilang = ServiceType::where('slug', 'cetak-ktp-hilang')->first();
        if ($hilang) {
            $this->syncDoc($hilang->id, 'Surat Keterangan Kehilangan Kepolisian', [
                'sort_order'       => 1,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Surat Tanda Penerimaan Laporan Kehilangan (STPLK) resmi dari Polsek / Polres terdekat yang masih berlaku.',
            ]);

            $this->syncDoc($hilang->id, 'Formulir F-1.02 (Pendaftaran Peristiwa Kependudukan)', [
                'sort_order'       => 2,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'template_url'     => $f102Url,
                'description'      => 'Formulir resmi permohonan pendaftaran peristiwa kependudukan yang telah diisi lengkap dan ditandatangani. Blanko formulir dapat diunduh pada tautan di bawah.',
            ], $hasTemplateUrl);

            $this->syncDoc($hilang->id, 'Kartu Keluarga (KK)', [
                'sort_order'       => 3,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Kartu Keluarga asli atau fotokopi/scan ber-barcode TTE Dukcapil.',
            ]);

            $this->syncDoc($hilang->id, 'Foto Wajah Pemohon (Selfie Verifikasi)', [
                'sort_order'       => 4,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png',
                'capture_mode'     => 'user',
                'description'      => 'Foto wajah tampak depan pemohon terbaru tanpa kacamata untuk verifikasi biometrik oleh petugas.',
            ]);

            $this->syncDoc($hilang->id, 'KTP-el Perwakilan (Jika Diwakilkan)', [
                'sort_order'       => 5,
                'is_required'      => true,
                'applicable_to'    => 'representative_only',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Foto KTP-el anggota keluarga yang mewakili dalam satu KK atau pihak yang diberi kuasa.',
            ]);

            $this->syncDoc($hilang->id, 'Formulir F-1.07 Surat Kuasa dalam Pelayanan Administrasi Kependudukan (Jika Dikuasakan)', [
                'sort_order'       => 6,
                'is_required'      => true,
                'applicable_to'    => 'representative_only',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'template_url'     => $f107Url,
                'description'      => 'Wajib jika permohonan dikuasakan ke pihak lain. Surat Kuasa bermeterai Rp 10.000 yang ditandatangani pemberi dan penerima kuasa. Blanko dapat diunduh di tautan di bawah.',
            ], $hasTemplateUrl);
        }

        // 2. Cetak KTP-el Karena Rusak (cetak-ktp-rusak)
        $rusak = ServiceType::where('slug', 'cetak-ktp-rusak')->first();
        if ($rusak) {
            $this->syncDoc($rusak->id, 'Foto Fisik KTP-el Lama yang Rusak', [
                'sort_order'       => 1,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png',
                'capture_mode'     => 'environment',
                'description'      => 'Foto fisik KTP-el lama yang mengalami kerusakan (patah, buram, terkelupas, dsb) secara jelas.',
            ]);

            $this->syncDoc($rusak->id, 'Formulir F-1.02 (Pendaftaran Peristiwa Kependudukan)', [
                'sort_order'       => 2,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'template_url'     => $f102Url,
                'description'      => 'Formulir resmi permohonan pendaftaran peristiwa kependudukan yang telah diisi lengkap dan ditandatangani. Blanko formulir dapat diunduh pada tautan di bawah.',
            ], $hasTemplateUrl);

            $this->syncDoc($rusak->id, 'Kartu Keluarga (KK)', [
                'sort_order'       => 3,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Kartu Keluarga asli atau fotokopi/scan ber-barcode TTE Dukcapil.',
            ]);

            $this->syncDoc($rusak->id, 'Foto Wajah Pemohon (Selfie Verifikasi)', [
                'sort_order'       => 4,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png',
                'capture_mode'     => 'user',
                'description'      => 'Foto wajah tampak depan pemohon terbaru tanpa kacamata untuk verifikasi biometrik oleh petugas.',
            ]);

            $this->syncDoc($rusak->id, 'KTP-el Perwakilan (Jika Diwakilkan)', [
                'sort_order'       => 5,
                'is_required'      => true,
                'applicable_to'    => 'representative_only',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Foto KTP-el anggota keluarga yang mewakili dalam satu KK atau pihak yang diberi kuasa.',
            ]);

            $this->syncDoc($rusak->id, 'Formulir F-1.07 Surat Kuasa dalam Pelayanan Administrasi Kependudukan (Jika Dikuasakan)', [
                'sort_order'       => 6,
                'is_required'      => true,
                'applicable_to'    => 'representative_only',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'template_url'     => $f107Url,
                'description'      => 'Wajib jika permohonan dikuasakan ke pihak lain. Surat Kuasa bermeterai Rp 10.000 yang ditandatangani pemberi dan penerima kuasa. Blanko dapat diunduh di tautan di bawah.',
            ], $hasTemplateUrl);
        }

        // 3. Perubahan Elemen Data KTP-el (perubahan-elemen-data)
        $perubahan = ServiceType::where('slug', 'perubahan-elemen-data')->first();
        if ($perubahan) {
            // Delete F-1.02 from Perubahan Elemen Data if present (it should strictly use F-1.06)
            ServiceDocument::where('service_type_id', $perubahan->id)
                ->where(function ($q) {
                    $q->where('label', 'like', '%F-1.02%')
                      ->orWhere('label', 'like', '%F1.02%')
                      ->orWhere('label', 'like', '%Pendaftaran Peristiwa%');
                })
                ->delete();

            $this->syncDoc($perubahan->id, 'KTP-el Lama Asli', [
                'sort_order'       => 1,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png',
                'capture_mode'     => 'environment',
                'description'      => 'Foto fisik KTP-el lama asli yang hendak dilakukan perubahan elemen data.',
            ]);

            $this->syncDoc($perubahan->id, 'Formulir F-1.06 (Surat Pernyataan Perubahan Elemen Data Kependudukan)', [
                'sort_order'       => 2,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'template_url'     => $f106Url,
                'description'      => 'Formulir resmi surat pernyataan perubahan elemen data kependudukan yang telah diisi lengkap dan ditandatangani. Blanko formulir dapat diunduh pada tautan di bawah.',
            ], $hasTemplateUrl);

            $this->syncDoc($perubahan->id, 'Kartu Keluarga (KK) Terbaru', [
                'sort_order'       => 3,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Kartu Keluarga terbaru ber-barcode TTE yang sudah memuat data yang telah diperbarui.',
            ]);

            $this->syncDoc($perubahan->id, 'Dokumen Bukti Peristiwa Pendukung', [
                'sort_order'       => 4,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Buku Nikah / Akta Cerai / Ijazah / Surat Keterangan Pindah / SK Pengangkatan sesuai elemen yang diubah.',
            ]);

            $this->syncDoc($perubahan->id, 'Foto Wajah Pemohon (Selfie Verifikasi)', [
                'sort_order'       => 5,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png',
                'capture_mode'     => 'user',
                'description'      => 'Foto wajah tampak depan pemohon terbaru tanpa kacamata untuk verifikasi identitas.',
            ]);

            $this->syncDoc($perubahan->id, 'KTP-el Perwakilan (Jika Diwakilkan)', [
                'sort_order'       => 6,
                'is_required'      => true,
                'applicable_to'    => 'representative_only',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Foto KTP-el anggota keluarga yang mewakili dalam satu KK atau pihak yang diberi kuasa.',
            ]);

            $this->syncDoc($perubahan->id, 'Formulir F-1.07 Surat Kuasa dalam Pelayanan Administrasi Kependudukan (Jika Dikuasakan)', [
                'sort_order'       => 7,
                'is_required'      => true,
                'applicable_to'    => 'representative_only',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'template_url'     => $f107Url,
                'description'      => 'Wajib jika permohonan dikuasakan ke pihak lain. Surat Kuasa bermeterai Rp 10.000 yang ditandatangani pemberi dan penerima kuasa. Blanko dapat diunduh di tautan di bawah.',
            ], $hasTemplateUrl);
        }

        // 4. Perekaman KTP-el Baru (Pemula / Usia 16-17 Tahun) (perekaman-ktp-el-baru)
        $rekam = ServiceType::where('slug', 'perekaman-ktp-el-baru')->first();
        if ($rekam) {
            $this->syncDoc($rekam->id, 'Kartu Keluarga (KK)', [
                'sort_order'       => 1,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Kartu Keluarga asli atau fotokopi/scan ber-barcode TTE Dukcapil.',
            ]);

            $this->syncDoc($rekam->id, 'Formulir F-1.02 (Pendaftaran Peristiwa Kependudukan)', [
                'sort_order'       => 2,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'template_url'     => $f102Url,
                'description'      => 'Formulir resmi permohonan pendaftaran peristiwa kependudukan yang telah diisi lengkap dan ditandatangani. Blanko formulir dapat diunduh pada tautan di bawah.',
            ], $hasTemplateUrl);

            $this->syncDoc($rekam->id, 'Akta Kelahiran / Surat Keterangan Lahir / Ijazah', [
                'sort_order'       => 3,
                'is_required'      => true,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Foto Akta Kelahiran atau Ijazah sekolah untuk pencocokan data nama, tempat, dan tanggal lahir.',
            ]);

            $this->syncDoc($rekam->id, 'Surat Keterangan Belum Pernah Rekam (Opsional)', [
                'sort_order'       => 4,
                'is_required'      => false,
                'applicable_to'    => 'all',
                'accepted_formats' => 'jpg,jpeg,png,pdf',
                'capture_mode'     => 'environment',
                'description'      => 'Surat keterangan dari desa/sekolah bahwa pemohon belum pernah melakukan perekaman KTP-el.',
            ]);
        }
    }

    /**
     * Find or create/update document by fuzzy label matching.
     */
    private function syncDoc(int $serviceTypeId, string $label, array $attributes, bool $hasTemplateUrl = false): void
    {
        $query = ServiceDocument::where('service_type_id', $serviceTypeId);

        if (str_contains($label, 'F-1.02') || str_contains($label, 'F1.02')) {
            $query->where(fn($q) => $q->where('label', 'like', '%F-1.02%')->orWhere('label', 'like', '%F1.02%'));
        } elseif (str_contains($label, 'F-1.06') || str_contains($label, 'F1.06')) {
            $query->where(fn($q) => $q->where('label', 'like', '%F-1.06%')->orWhere('label', 'like', '%F1.06%'));
        } elseif (str_contains($label, 'Kuasa') || str_contains($label, 'F-1.07') || str_contains($label, 'F1.07')) {
            $query->where(fn($q) => $q->where('label', 'like', '%Kuasa%')->orWhere('label', 'like', '%F-1.07%')->orWhere('label', 'like', '%F1.07%'));
        } elseif (str_contains($label, 'Perwakilan')) {
            $query->where('label', 'like', '%Perwakilan%');
        } elseif (str_contains($label, 'Kehilangan')) {
            $query->where('label', 'like', '%Kehilangan%');
        } elseif (str_contains($label, 'Rusak')) {
            $query->where('label', 'like', '%Rusak%');
        } elseif (str_contains($label, 'Lama Asli')) {
            $query->where('label', 'like', '%Lama Asli%');
        } elseif (str_contains($label, 'Pendukung')) {
            $query->where('label', 'like', '%Pendukung%');
        } elseif (str_contains($label, 'Selfie') || str_contains($label, 'Foto Wajah')) {
            $query->where(fn($q) => $q->where('label', 'like', '%Selfie%')->orWhere('label', 'like', '%Foto Wajah%'));
        } elseif (str_contains($label, 'Akta Kelahiran')) {
            $query->where('label', 'like', '%Akta Kelahiran%');
        } elseif (str_contains($label, 'Belum Pernah Rekam')) {
            $query->where('label', 'like', '%Belum Pernah Rekam%');
        } elseif (str_contains($label, 'Kartu Keluarga') || str_contains($label, '(KK)')) {
            $query->where(fn($q) => $q->where('label', 'like', '%Kartu Keluarga%')->orWhere('label', 'like', '%(KK)%'));
        } else {
            $query->where('label', $label);
        }

        $doc = $query->first();

        $payload = array_merge([
            'service_type_id' => $serviceTypeId,
            'label'           => $label,
            'max_size_kb'     => 5120,
        ], $attributes);

        if (!$hasTemplateUrl && isset($payload['template_url'])) {
            unset($payload['template_url']);
        }

        if ($doc) {
            $doc->update($payload);
        } else {
            ServiceDocument::create($payload);
        }
    }

    public function down(): void
    {
        // No need to reverse clean normalization
    }
};
