<?php

namespace App\Services;

class ModulePermissionService
{
    /**
     * Get all web modules and their associated permissions with labels and descriptions.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getModules(): array
    {
        return [
            'ktp_queue' => [
                'id'          => 'ktp_queue',
                'name'        => 'Antrian Online KTP-el',
                'icon'        => 'UserCheck',
                'badge_color' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                'description' => 'Layanan antrean pendaftaran online, verifikasi berkas NIK pemohon, dan pengaturan kuota',
                'permissions' => [
                    'manage_online_queue'       => [
                        'title' => 'Kelola Permohonan KTP-el',
                        'desc'  => 'Melihat, memeriksa, dan memproses permohonan antrean online pendaftar',
                    ],
                    'manage_queue_verification' => [
                        'title' => 'Verifikasi Berkas & Pendaftar Baru',
                        'desc'  => 'Validasi keaslian berkas persyaratan dan persetujuan akun pendaftar',
                    ],
                    'manage_queue_schedule'     => [
                        'title' => 'Jadwal, Sesi & Kuota Layanan',
                        'desc'  => 'Atur kuota harian pemohon, jam operasional, dan hari libur pelayanan antrean',
                    ],
                    'view_queue_reports'        => [
                        'title' => 'Laporan Rekapitulasi Antrean',
                        'desc'  => 'Melihat statistik, grafik permohonan, dan laporan agregat pendaftaran KTP-el',
                    ],
                ],
            ],

            'ktp_handover' => [
                'id'          => 'ktp_handover',
                'name'        => 'Serah Terima Fisik KTP-el',
                'icon'        => 'Clock',
                'badge_color' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                'description' => 'Loket penyerahan fisik kartu KTP-el, kamera foto penyerahan, dan penanganan kendala',
                'permissions' => [
                    'handover.dashboard.view'   => [
                        'title' => 'Dashboard Serah Terima',
                        'desc'  => 'Melihat statistik ringkasan kartu siap diambil, diserahkan, dan kendala',
                    ],
                    'handover.ready.view'       => [
                        'title' => 'Daftar KTP-el Siap Diambil',
                        'desc'  => 'Melihat daftar fisik KTP-el yang sudah dicetak dan siap diserahkan ke warga',
                    ],
                    'handover.ready.update'     => [
                        'title' => 'Perbarui Status Kesiapan Fisik',
                        'desc'  => 'Mengubah status permohonan menjadi siap diambil di loket serah terima',
                    ],
                    'handover.pending.view'     => [
                        'title' => 'Menunggu Ketersediaan Fisik',
                        'desc'  => 'Melihat daftar permohonan yang belum dicetak atau menunggu ketersediaan kartu',
                    ],
                    'handover.process'          => [
                        'title' => 'Proses Penyerahan Fisik KTP-el',
                        'desc'  => 'Mengeksekusi serah terima fisik kartu KTP-el kepada pemohon atau perwakilan',
                    ],
                    'handover.photo.view'       => [
                        'title' => 'Kamera Bukti Foto Pengambilan',
                        'desc'  => 'Mengambil foto dan memeriksa dokumentasi bukti serah terima pemohon',
                    ],
                    'handover.history.view'     => [
                        'title' => 'Riwayat Serah Terima KTP-el',
                        'desc'  => 'Melihat arsip rekam jejak KTP-el yang telah selesai diserahkan ke masyarakat',
                    ],
                    'handover.exceptions.view'  => [
                        'title' => 'Daftar Kendala Pengambilan',
                        'desc'  => 'Melihat permohonan bermasalah (diwakilkan tanpa surat kuasa, kartu cacat, dll)',
                    ],
                    'handover.exception.view'   => [
                        'title' => 'Detail Kendala Pengambilan',
                        'desc'  => 'Melihat rincian kronologi kendala pengambilan kartu KTP-el',
                    ],
                    'handover.exception.update' => [
                        'title' => 'Penyelesaian & Resolusi Kendala',
                        'desc'  => 'Menindaklanjuti dan menyelesaikan berkas permohonan yang berkendala',
                    ],
                ],
            ],

            'ktp_blank_stocks' => [
                'id'          => 'ktp_blank_stocks',
                'name'        => 'Persediaan Blangko KTP-el',
                'icon'        => 'Layers',
                'badge_color' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'description' => 'Buku mutasi persediaan, penerimaan BAST, pemakaian cetak, opname dan penyesuaian saldo',
                'permissions' => [
                    'blank_stocks.view'               => [
                        'title' => 'Akses Modul Persediaan Blangko',
                        'desc'  => 'Hak dasar mengakses fitur persediaan blangko KTP-el',
                    ],
                    'blank_stocks.dashboard'          => [
                        'title' => 'Dashboard Persediaan & Stok',
                        'desc'  => 'Memantau saldo gudang, ketersediaan publik, dan grafik penggunaan 14 hari',
                    ],
                    'blank_stocks.receipts.view'      => [
                        'title' => 'Lihat Riwayat Penerimaan BAST',
                        'desc'  => 'Melihat riwayat penerimaan keping blangko resmi dari Ditjen Dukcapil / Provinsi',
                    ],
                    'blank_stocks.receipts.create'    => [
                        'title' => 'Input Penerimaan Blangko Baru',
                        'desc'  => 'Bendahara Barang mencatat penambahan keping blangko bersumber dokumen BAST',
                    ],
                    'blank_stocks.usage.view_own'     => [
                        'title' => 'Lihat Laporan Pemakaian Pribadi',
                        'desc'  => 'Operator Cetak melihat laporan hasil cetak dan kartu rusak miliknya sendiri',
                    ],
                    'blank_stocks.usage.view_all'     => [
                        'title' => 'Lihat Semua Laporan Pemakaian Operator',
                        'desc'  => 'Melihat rekapan laporan cetak seluruh operator mesin cetak KTP-el',
                    ],
                    'blank_stocks.usage.create'       => [
                        'title' => 'Input Laporan Pemakaian Harian',
                        'desc'  => 'Operator mencatat hasil pencetakan KTP-el berhasil dan kartu rusak per sesi',
                    ],
                    'blank_stocks.usage.revise_own'   => [
                        'title' => 'Revisi Laporan Pemakaian Pribadi',
                        'desc'  => 'Mengubah atau mengoreksi laporan pemakaian draf sebelum disetujui pimpinan',
                    ],
                    'blank_stocks.usage.approve'      => [
                        'title' => 'Persetujuan Laporan Penggunaan',
                        'desc'  => 'Verifikasi dan memotong saldo gudang secara resmi dengan atomic transaction',
                    ],
                    'blank_stocks.ledger.view'        => [
                        'title' => 'Kartu Persediaan / Buku Mutasi Induk',
                        'desc'  => 'Melihat dan mencetak kartu mutasi running balance yang bersifat immutable',
                    ],
                    'blank_stocks.opname.view'        => [
                        'title' => 'Lihat Berita Acara Stok Opname',
                        'desc'  => 'Melihat riwayat berita acara perhitungan fisik keping blangko di brankas',
                    ],
                    'blank_stocks.opname.create'      => [
                        'title' => 'Input Berita Acara Stok Opname',
                        'desc'  => 'Mencatat hasil perhitungan fisik berkala keping blangko di brankas penyimpanan',
                    ],
                    'blank_stocks.opname.approve'     => [
                        'title' => 'Pengesahan Berita Acara Opname',
                        'desc'  => 'Pimpinan mengesahkan hasil rekonsiliasi fisik vs stok sistem',
                    ],
                    'blank_stocks.adjustment.create'  => [
                        'title' => 'Ajukan Usulan Koreksi Saldo',
                        'desc'  => 'Membuat usulan penyesuaian saldo persediaan berdasarkan temuan opname',
                    ],
                    'blank_stocks.adjustment.approve' => [
                        'title' => 'Pengesahan Koreksi Saldo',
                        'desc'  => 'Pimpinan menyetujui mutasi saldo penyesuaian resmi',
                    ],
                    'blank_stocks.reports.export'     => [
                        'title' => 'Cetak & Ekspor Rekapitulasi',
                        'desc'  => 'Mencetak dan mengekspor CSV/Excel rekapitulasi penggunaan dan penerimaan',
                    ],
                    'blank_stocks.settings.manage'    => [
                        'title' => 'Kelola Batas Stok Minimum',
                        'desc'  => 'Mengatur ambang batas peringatan stok menipis dan kritis',
                    ],
                ],
            ],

            'ktp_archive' => [
                'id'          => 'ktp_archive',
                'name'        => 'Pengarsipan Digital Permohonan',
                'icon'        => 'FolderArchive',
                'badge_color' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
                'description' => 'Paket arsip ZIP berkas pemohon, verifikasi hash SHA-256, dan penyimpanan permanen',
                'permissions' => [
                    'archive.dashboard.view'     => [
                        'title' => 'Dashboard Pengarsipan Digital',
                        'desc'  => 'Melihat statistik berkas terarsip, kapasitas disk, dan antrean paket',
                    ],
                    'archive.applications.view'  => [
                        'title' => 'Daftar Siap Diarsipkan',
                        'desc'  => 'Melihat permohonan selesai yang siap dibundel ke paket arsip digital',
                    ],
                    'archive.package.create'     => [
                        'title' => 'Buat Paket Arsip ZIP (SHA-256)',
                        'desc'  => 'Membundel berkas digital pemohon dengan segel checksum SHA-256',
                    ],
                    'archive.package.view'       => [
                        'title' => 'Lihat Detail Paket Arsip',
                        'desc'  => 'Melihat rincian dokumen dan daftar pemohon di dalam paket arsip',
                    ],
                    'archive.package.download'   => [
                        'title' => 'Unduh Berkas Paket Arsip ZIP',
                        'desc'  => 'Mengunduh paket arsip digital terenkripsi untuk backup eksternal',
                    ],
                    'archive.package.confirm'    => [
                        'title' => 'Konfirmasi Penyimpanan & Purge',
                        'desc'  => 'Mengonfirmasi penyimpanan aman dan menghapus file lokal server untuk hemat disk',
                    ],
                    'archive.history.view'       => [
                        'title' => 'Riwayat & Log Pengarsipan',
                        'desc'  => 'Melihat rekam jejak proses pengarsipan berkas kependudukan',
                    ],
                    'archive.storage.view'       => [
                        'title' => 'Status Penyimpanan Permanen',
                        'desc'  => 'Memeriksa kapasitas drive dan status media penyimpanan permanen',
                    ],
                    'archive.settings.manage'    => [
                        'title' => 'Pengaturan Retensi & Target Storage',
                        'desc'  => 'Mengatur jangka waktu retensi berkas lokal dan lokasi penyimpanan arsip',
                    ],
                    'archive.transfer.execute'   => [
                        'title' => 'Eksekusi Transfer Storage Eksternal',
                        'desc'  => 'Menjalankan transfer berkas arsip ke media penyimpanan eksternal',
                    ],
                ],
            ],

            'cms_content' => [
                'id'          => 'cms_content',
                'name'        => 'Konten, Berita & Media CMS',
                'icon'        => 'FileText',
                'badge_color' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'description' => 'Pengelolaan Berita, Artikel, Halaman Dinamis, Galeri, Banner, dan Media Digital',
                'permissions' => [
                    'manage_news'          => [
                        'title' => 'Berita & Artikel Dinas',
                        'desc'  => 'Membuat, menyunting, dan mempublikasikan liputan kegiatan resmi',
                    ],
                    'manage_pages'         => [
                        'title' => 'Halaman Dinamis & Profil',
                        'desc'  => 'Mengelola halaman profil instansi, visi misi, dan struktur organisasi',
                    ],
                    'manage_announcements' => [
                        'title' => 'Pengumuman Resmi',
                        'desc'  => 'Membuat dan mempublikasikan pengumuman penting bagi masyarakat',
                    ],
                    'manage_banners'       => [
                        'title' => 'Banner Slider Beranda',
                        'desc'  => 'Mengelola gambar carousel sorotan utama pada halaman depan website',
                    ],
                    'manage_gallery'       => [
                        'title' => 'Galeri Foto & Video',
                        'desc'  => 'Mengelola album dokumentasi kegiatan dan video pelayanan dinas',
                    ],
                    'manage_file_manager'  => [
                        'title' => 'Manajer Berkas & Media',
                        'desc'  => 'Mengunggah, mengorganisir, dan mengelola foto serta file dokumen CMS',
                    ],
                    'manage_downloads'     => [
                        'title' => 'Pusat Unduhan Formulir',
                        'desc'  => 'Mengelola formulir kependudukan resmi yang dapat diunduh oleh warga',
                    ],
                    'manage_faq'           => [
                        'title' => 'Tanya Jawab (FAQ)',
                        'desc'  => 'Mengelola panduan pertanyaan sering ditanyakan seputar layanan Dukcapil',
                    ],
                    'manage_publications'  => [
                        'title' => 'Dokumen Publikasi Resmi',
                        'desc'  => 'Mengelola laporan kinerja berkala dan dokumen publikasi kedinasan',
                    ],
                ],
            ],

            'public_services' => [
                'id'          => 'public_services',
                'name'        => 'Pengaduan Masyarakat & PPID',
                'icon'        => 'MessageSquareWarning',
                'badge_color' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                'description' => 'Penanganan aduan masyarakat, survei kepuasan (IKM), dan keterbukaan informasi PPID',
                'permissions' => [
                    'manage_complaints' => [
                        'title' => 'Pengaduan Masyarakat & IKM',
                        'desc'  => 'Merespons pengaduan warga, tindak lanjut saran, dan mengelola survei kepuasan',
                    ],
                    'manage_ppid'       => [
                        'title' => 'Layanan Informasi Publik PPID',
                        'desc'  => 'Mengelola permohonan informasi publik dan dokumen berkala PPID',
                    ],
                ],
            ],

            'demographics' => [
                'id'          => 'demographics',
                'name'        => 'Kependudukan & Demografi',
                'icon'        => 'DatabaseZap',
                'badge_color' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                'description' => 'Dataset kependudukan, agregat wilayah kecamatan/desa, dan dashboard statistik',
                'permissions' => [
                    'manage_demographic' => [
                        'title' => 'Dataset Kependudukan',
                        'desc'  => 'Mengelola data agregat jumlah penduduk, KK, dan demografi wilayah',
                    ],
                ],
            ],

            'notifications' => [
                'id'          => 'notifications',
                'name'        => 'Notifikasi & Gateway Pesan',
                'icon'        => 'Bell',
                'badge_color' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                'description' => 'Pengiriman pesan notifikasi otomatis WhatsApp/Email dan integrasi gateway',
                'permissions' => [
                    'notifications.view'   => [
                        'title' => 'Lihat Notifikasi Sistem',
                        'desc'  => 'Menerima pemberitahuan permohonan antrean dan peringatan sistem',
                    ],
                    'notifications.logs'   => [
                        'title' => 'Log Pengiriman Notifikasi',
                        'desc'  => 'Melihat rekam jejak pengiriman pesan WhatsApp dan Email ke warga',
                    ],
                    'notifications.manage' => [
                        'title' => 'Konfigurasi Template & Gateway',
                        'desc'  => 'Mengatur akun gateway WhatsApp, API key, dan template pesan dinas',
                    ],
                    'notifications.test'   => [
                        'title' => 'Uji Coba Pengiriman Notifikasi',
                        'desc'  => 'Melakukan pengujian kirim pesan langsung ke nomor telepon administrator',
                    ],
                ],
            ],

            'system' => [
                'id'          => 'system',
                'name'        => 'Pengaturan Sistem & Administrator',
                'icon'        => 'Settings',
                'badge_color' => 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-zinc-200 border-slate-300 dark:border-zinc-700',
                'description' => 'Konfigurasi global instansi, manajemen akun pengguna, tema visual, dan audit trail',
                'permissions' => [
                    'manage_settings' => [
                        'title' => 'Pengaturan Dasar Website',
                        'desc'  => 'Mengatur identitas instansi, kontak kantor, jam kerja, dan konfigurasi umum',
                    ],
                    'manage_themes'   => [
                        'title' => 'Kustomisasi Tema & Warna',
                        'desc'  => 'Menyesuaikan skema warna dinas, logo, dan tata letak antarmuka',
                    ],
                    'manage_users'    => [
                        'title' => 'Manajemen Pengguna & Operator',
                        'desc'  => 'Membuat, mengedit, menonaktifkan akun operator, dan reset 2FA',
                    ],
                    'manage_roles'    => [
                        'title' => 'Manajemen Peran & Hak Akses',
                        'desc'  => 'Membuat, mengubah, dan menghapus peran serta alokasi izin modul web',
                    ],
                    'view_audit_logs' => [
                        'title' => 'Log Audit Aktivitas (Audit Trail)',
                        'desc'  => 'Melihat riwayat log aktivitas seluruh operator untuk keamanan sistem',
                    ],
                    'view_analytics'  => [
                        'title' => 'Analisis Pengunjung Website',
                        'desc'  => 'Melihat grafik statistik kunjungan website dan retensi data analitik',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get system-protected role names that cannot be deleted or have their names modified.
     *
     * @return array<string>
     */
    public static function getProtectedRoles(): array
    {
        return [
            'Administrator Utama',
            'Super Administrator',
            'Administrator Website',
            'Supervisor Layanan KTP-el',
            'Bendahara Barang',
            'Operator Cetak KTP-el',
            'Petugas Verifikasi / Registrasi KTP-el',
            'Petugas Pengambilan KTP-el',
            'Petugas Pengarsipan',
            'Editor Konten',
            'Petugas Pengaduan',
            'Admin PPID',
        ];
    }
}
