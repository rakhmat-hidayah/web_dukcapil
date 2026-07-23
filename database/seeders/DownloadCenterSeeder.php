<?php

namespace Database\Seeders;

use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DownloadCenterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Disable FK checks and truncate tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Download::truncate();
        DownloadCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $adminUser = User::first();
        $adminId = $adminUser ? $adminUser->id : 1;

        // 2. Ensure storage/downloads directory exists
        Storage::disk('public')->makeDirectory('downloads');

        // Helper to generate dummy sample PDF file
        $createSamplePdf = function ($filename, $title) {
            $path = "downloads/{$filename}";
            $pdfContent = "%PDF-1.4\n1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n2 0 obj << /Type /Pages /Kinds [3 0 R] /Count 1 >> endobj\n3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >> endobj\n4 0 obj << /Length 55 >> stream\nBT /F1 18 Tf 50 700 Td (Dokumen Resmi Disdukcapil Dompu: {$title}) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000206 00000 n \ntrailer << /Size 5 /Root 1 0 R >>\nstartxref\n312\n%%EOF";
            Storage::disk('public')->put($path, $pdfContent);
            return [
                'file_path' => $path,
                'file_name' => $filename,
                'file_type' => 'pdf',
                'file_size' => strlen($pdfContent),
            ];
        };

        // 3. Define Categories & Downloads Data
        $categoriesData = [
            [
                'name' => 'Formulir Layanan Kependudukan',
                'slug' => 'formulir-layanan',
                'icon' => 'file-text',
                'sort_order' => 1,
                'downloads' => [
                    [
                        'title' => 'Formulir F-1.01: Biodata Keluarga',
                        'description' => 'Formulir isian biodata keluarga untuk pembuatan dan perubahan Kartu Keluarga (KK) baru maupun mutasi data keluarga.',
                        'filename' => 'Formulir_F-1.01_Biodata_Keluarga.pdf',
                        'document_number' => 'F-1.01/DUKCAPIL/2025',
                        'document_date' => '2025-01-10',
                    ],
                    [
                        'title' => 'Formulir F-1.02: Pendaftaran Peristiwa Kependudukan',
                        'description' => 'Formulir pendaftaran untuk permohonan Kartu Tanda Penduduk Elektronik (KTP-el), KIA, dan perpindahan penduduk.',
                        'filename' => 'Formulir_F-1.02_Pendaftaran_Adduk.pdf',
                        'document_number' => 'F-1.02/DUKCAPIL/2025',
                        'document_date' => '2025-01-10',
                    ],
                    [
                        'title' => 'Formulir F-1.03: Surat Kuasa Pengurusan Adduk',
                        'description' => 'Surat kuasa resmi untuk pelimpahan wewenang pengurusan dokumen adminduk kepada anggota keluarga lain.',
                        'filename' => 'Formulir_F-1.03_Surat_Kuasa.pdf',
                        'document_number' => 'F-1.03/DUKCAPIL/2025',
                        'document_date' => '2025-01-15',
                    ],
                    [
                        'title' => 'Formulir F-1.05: Permohonan Kartu Identitas Anak (KIA)',
                        'description' => 'Formulir pendaftaran penerbitan Kartu Identitas Anak (KIA) bagi anak usia 0 hingga 17 tahun kurang 1 hari.',
                        'filename' => 'Formulir_F-1.05_Permohonan_KIA.pdf',
                        'document_number' => 'F-1.05/DUKCAPIL/2025',
                        'document_date' => '2025-01-15',
                    ],
                    [
                        'title' => 'Formulir F-1.07: SPTJM Kebenaran Data Suami Istri',
                        'description' => 'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) kebenaran status perkawinan belum tercatat untuk pengurusan dokumen.',
                        'filename' => 'Formulir_F-1.07_SPTJM_Suami_Istri.pdf',
                        'document_number' => 'F-1.07/DUKCAPIL/2025',
                        'document_date' => '2025-02-01',
                    ],
                    [
                        'title' => 'Formulir F-2.01: SPTJM Kebenaran Data Kelahiran',
                        'description' => 'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) kelahiran bagi anak yang tidak memiliki surat keterangan lahir medis.',
                        'filename' => 'Formulir_F-2.01_SPTJM_Kelahiran.pdf',
                        'document_number' => 'F-2.01/DUKCAPIL/2025',
                        'document_date' => '2025-02-01',
                    ],
                ]
            ],
            [
                'name' => 'Peraturan & Regulasi Adduk',
                'slug' => 'peraturan-regulasi',
                'icon' => 'book-open',
                'sort_order' => 2,
                'downloads' => [
                    [
                        'title' => 'UU No. 24 Tahun 2013 tentang Administrasi Kependudukan',
                        'description' => 'Undang-Undang Republik Indonesia Nomor 24 Tahun 2013 tentang Perubahan atas UU Nomor 23 Tahun 2006 tentang Administrasi Kependudukan.',
                        'filename' => 'UU_No_24_Tahun_2013_Adduk.pdf',
                        'document_number' => 'UU No. 24/2013',
                        'document_date' => '2013-12-24',
                    ],
                    [
                        'title' => 'Perpres No. 96 Tahun 2018 tentang Persyaratan & Tata Cara Adduk',
                        'description' => 'Peraturan Presiden Nomor 96 Tahun 2018 tentang Persyaratan dan Tata Cara Pendaftaran Penduduk dan Pencatatan Sipil.',
                        'filename' => 'Perpres_No_96_Tahun_2018.pdf',
                        'document_number' => 'Perpres No. 96/2018',
                        'document_date' => '2018-10-18',
                    ],
                    [
                        'title' => 'Permendagri No. 73 Tahun 2022 tentang Pencatatan Nama Dokumen Kependudukan',
                        'description' => 'Peraturan Menteri Dalam Negeri tentang pedoman tata cara penulisan dan pencatatan nama pada dokumen kependudukan.',
                        'filename' => 'Permendagri_No_73_Tahun_2022.pdf',
                        'document_number' => 'Permendagri 73/2022',
                        'document_date' => '2022-04-11',
                    ],
                    [
                        'title' => 'Perda Kab. Dompu No. 4 Tahun 2021 tentang Penyelenggaraan Adduk',
                        'description' => 'Peraturan Daerah Kabupaten Dompu mengenai penyelenggaraan administrasi kependudukan daerah secara gratis dan akuntabel.',
                        'filename' => 'Perda_Kab_Dompu_No_4_Tahun_2021.pdf',
                        'document_number' => 'Perda No. 04/2021',
                        'document_date' => '2021-08-10',
                    ],
                ]
            ],
            [
                'name' => 'Buku Agregat & Laporan Demografi',
                'slug' => 'agregat',
                'icon' => 'bar-chart-2',
                'sort_order' => 3,
                'downloads' => [
                    [
                        'title' => 'Buku Data Agregat Kependudukan (DAK2) Kab. Dompu Semester II 2025',
                        'description' => 'Laporan publikasi resmi Buku Agregat Kependudukan per Kecamatan dan Desa di Kabupaten Dompu Semester II Tahun 2025.',
                        'filename' => 'Buku_Agregat_Kependudukan_Dompu_Sem2_2025.pdf',
                        'document_number' => '470/DAK2/DOMPU/2025',
                        'document_date' => '2025-12-31',
                    ],
                    [
                        'title' => 'Buku Data Agregat Kependudukan (DAK1) Kab. Dompu Semester I 2025',
                        'description' => 'Laporan publikasi resmi Buku Agregat Kependudukan per Kecamatan dan Desa di Kabupaten Dompu Semester I Tahun 2025.',
                        'filename' => 'Buku_Agregat_Kependudukan_Dompu_Sem1_2025.pdf',
                        'document_number' => '470/DAK1/DOMPU/2025',
                        'document_date' => '2025-06-30',
                    ],
                    [
                        'title' => 'Laporan Statistik Demografi Penduduk Kab. Dompu Tahun 2025',
                        'description' => 'Ringkasan komprehensif data statistik jumlah penduduk, rasio jenis kelamin, kelompok umur, serta persebaran wilayah.',
                        'filename' => 'Laporan_Demografi_Dompu_2025.pdf',
                        'document_number' => '470/LAP-DEM/2025',
                        'document_date' => '2025-12-15',
                    ],
                ]
            ],
            [
                'name' => 'Standar Pelayanan & SOP',
                'slug' => 'standar-sop',
                'icon' => 'shield-check',
                'sort_order' => 4,
                'downloads' => [
                    [
                        'title' => 'Maklumat Pelayanan Publik Disdukcapil Kabupaten Dompu',
                        'description' => 'Pernyataan komitmen resmi Kepala Dinas dan jajaran Disdukcapil Kabupaten Dompu dalam memberikan pelayanan prima.',
                        'filename' => 'Maklumat_Pelayanan_Disdukcapil_Dompu.pdf',
                        'document_number' => 'MAK/01/DUKCAPIL/2025',
                        'document_date' => '2025-01-02',
                    ],
                    [
                        'title' => 'SOP Penerbitan KTP-El & KIA Disdukcapil',
                        'description' => 'Standard Operating Procedure (SOP) tata cara dan alur penerbitan KTP-el baru, penggantian rusak/hilang, dan KIA.',
                        'filename' => 'SOP_Penerbitan_KTP_dan_KIA.pdf',
                        'document_number' => 'SOP/KTP/01/2025',
                        'document_date' => '2025-01-05',
                    ],
                    [
                        'title' => 'SOP Pelayanan Akta Kelahiran dan Akta Kematian',
                        'description' => 'Standard Operating Procedure (SOP) tata cara dan alur penerbitan kutipan Akta Kelahiran dan Akta Kematian.',
                        'filename' => 'SOP_Penerbitan_Akta_Pencatatan_Sipil.pdf',
                        'document_number' => 'SOP/AKTA/02/2025',
                        'document_date' => '2025-01-05',
                    ],
                    [
                        'title' => 'SOP Pelayanan Online Portal SANAI',
                        'description' => 'SOP pelayanan pengurusan administrasi kependudukan daring melalui Portal Layanan Online SANAI Dukcapil Dompu.',
                        'filename' => 'SOP_Pelayanan_Online_SANAI.pdf',
                        'document_number' => 'SOP/SANAI/03/2025',
                        'document_date' => '2025-01-08',
                    ],
                ]
            ],
        ];

        // 4. Insert Categories and Downloads
        foreach ($categoriesData as $catData) {
            $category = DownloadCategory::create([
                'name' => $catData['name'],
                'slug' => $catData['slug'],
                'icon' => $catData['icon'],
                'sort_order' => $catData['sort_order'],
                'is_active' => true,
            ]);

            foreach ($catData['downloads'] as $dlData) {
                $fileMeta = $createSamplePdf($dlData['filename'], $dlData['title']);

                Download::create([
                    'user_id' => $adminId,
                    'download_category_id' => $category->id,
                    'title' => $dlData['title'],
                    'description' => $dlData['description'],
                    'file_path' => $fileMeta['file_path'],
                    'file_name' => $fileMeta['file_name'],
                    'file_type' => $fileMeta['file_type'],
                    'file_size' => $fileMeta['file_size'],
                    'document_number' => $dlData['document_number'],
                    'document_date' => $dlData['document_date'],
                    'status' => 'published',
                    'published_at' => now(),
                ]);
            }
        }
    }
}
