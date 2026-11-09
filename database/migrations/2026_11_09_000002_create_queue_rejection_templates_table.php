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
        if (!Schema::hasTable('queue_rejection_templates')) {
            Schema::create('queue_rejection_templates', function (Blueprint $table) {
                $table->id();
                $table->string('title', 191);
                $table->text('content');
                $table->string('type', 50)->default('all'); // 'rejection', 'revision', 'all'
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Seed the 8 standard formal & polite templates if empty
        if (DB::table('queue_rejection_templates')->count() === 0) {
            $now = now();
            $templates = [
                [
                    'title'      => 'Foto selfie pemohon tidak sesuai',
                    'content'    => 'Mohon maaf, permohonan Anda belum dapat diproses karena foto verifikasi wajah (selfie) yang diunggah tidak sesuai dengan identitas pemilik dokumen KTP-el yang diajukan. Demi keamanan data dan kepatuhan administrasi kependudukan, permohonan wajib diajukan oleh pemilik dokumen yang bersangkutan.',
                    'type'       => 'all',
                    'sort_order' => 1,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title'      => 'Berkas kurang jelas / buram (Foto kembali dokumen)',
                    'content'    => 'Mohon maaf, dokumen persyaratan yang Anda unggah tidak terbaca secara jelas (buram, terpotong, atau berbayang). Mohon lakukan pemotretan atau pemindaian ulang terhadap dokumen fisik asli secara tegak lurus dengan pencahayaan yang memadai dan seluruh tulisan terbaca dengan jelas.',
                    'type'       => 'all',
                    'sort_order' => 2,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title'      => 'Tidak melampirkan Surat Keterangan Kehilangan Kepolisian',
                    'content'    => 'Mohon maaf, permohonan pencetakan KTP-el karena hilang mewajibkan adanya Surat Tanda Penerimaan Laporan Kehilangan (STPLK) yang masih berlaku dari Polsek/Polres terdekat. Mohon lengkapi dan lampirkan dokumen resmi tersebut untuk melanjutkan pengajuan.',
                    'type'       => 'all',
                    'sort_order' => 3,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title'      => 'Tidak melampirkan foto fisik KTP-el yang rusak',
                    'content'    => 'Mohon maaf, untuk pengajuan cetak KTP-el dengan alasan rusak, Anda wajib melampirkan foto fisik kartu KTP-el lama yang mengalami kerusakan sebagai bukti verifikasi administrasi sebelum penerbitan kartu baru.',
                    'type'       => 'all',
                    'sort_order' => 4,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title'      => 'Tidak melampirkan Kartu Keluarga (KK) terbaru',
                    'content'    => 'Mohon maaf, dokumen Kartu Keluarga (KK) yang Anda lampirkan belum sesuai atau belum ber-barcode Tanda Tangan Elektronik (TTE) Dukcapil versi terbaru. Silakan lampirkan scan/foto Kartu Keluarga (KK) asli terbaru Anda.',
                    'type'       => 'all',
                    'sort_order' => 5,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title'      => 'Penduduk Luar Domisili Kab. Dompu (Layanan online tidak berlaku)',
                    'content'    => 'Mohon maaf, layanan pendaftaran online ini dikhususkan bagi penduduk yang terdaftar berdomisili di Kabupaten Dompu. Bagi warga luar domisili, pencetakan KTP-el luar domisili (KTP Nusantara) wajib dilakukan secara langsung dengan hadir di loket Kantor Disdukcapil Kabupaten Dompu.',
                    'type'       => 'all',
                    'sort_order' => 6,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title'      => 'Pengajuan ganda / KTP-el sudah pernah dicetak',
                    'content'    => 'Mohon maaf, permohonan Anda tidak dapat dilanjutkan karena terdeteksi adanya pengajuan ganda (duplikasi) atau fisik kartu KTP-el Anda telah tercatat selesai dicetak pada database kependudukan. Silakan lakukan pengecekan fisik di loket penyerahan KTP-el atau hubungi petugas layanan.',
                    'type'       => 'all',
                    'sort_order' => 7,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title'      => 'Surat Keterangan Kehilangan belum ditandatangani pelapor',
                    'content'    => 'Mohon maaf, dokumen Surat Tanda Penerimaan Laporan Kehilangan (STPLK) dari Kepolisian yang diunggah belum dibubuhi tanda tangan oleh pemohon/pelapor. Silakan lengkapi tanda tangan Anda pada surat tersebut terlebih dahulu lalu unggah kembali.',
                    'type'       => 'all',
                    'sort_order' => 8,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            DB::table('queue_rejection_templates')->insert($templates);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_rejection_templates');
    }
};
