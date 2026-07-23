<?php

namespace Database\Seeders;

use App\Models\PpidDocument;
use App\Models\PpidPage;
use Illuminate\Database\Seeder;

class PpidSeeder extends Seeder
{
    public function run(): void
    {
        // ── STATIC PAGES ────────────────────────────────────────────
        $pages = [
            [
                'slug'  => 'pengertian',
                'title' => 'Pengertian PPID',
                'icon'  => 'BookOpen',
                'sort_order' => 1,
                'content' => <<<HTML
<h2>Apa itu PPID?</h2>
<p>
  <strong>Pejabat Pengelola Informasi dan Dokumentasi (PPID)</strong> adalah pejabat yang bertanggung jawab di bidang penyimpanan, pendokumentasian, penyediaan, dan/atau pelayanan informasi di badan publik. PPID dibentuk berdasarkan <strong>Undang-Undang Nomor 14 Tahun 2008</strong> tentang Keterbukaan Informasi Publik (KIP).
</p>
<h3>Dasar Hukum</h3>
<ul>
  <li>UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik</li>
  <li>PP No. 61 Tahun 2010 tentang Pelaksanaan UU No. 14 Tahun 2008</li>
  <li>Permendagri No. 35 Tahun 2010 tentang Pedoman Pengelolaan Pelayanan Informasi dan Dokumentasi di Lingkungan Kemendagri dan Pemda</li>
  <li>Peraturan Bupati Dompu tentang Pengelolaan Informasi Publik</li>
</ul>
<h3>Tujuan Keterbukaan Informasi Publik</h3>
<ol>
  <li>Menjamin hak warga negara untuk mengetahui rencana pembuatan kebijakan publik</li>
  <li>Mendorong partisipasi masyarakat dalam proses pengambilan kebijakan publik</li>
  <li>Meningkatkan peran aktif masyarakat dalam pengambilan kebijakan publik</li>
  <li>Mewujudkan penyelenggaraan negara yang baik (good governance)</li>
  <li>Mengetahui alasan kebijakan publik yang mempengaruhi hajat hidup orang banyak</li>
  <li>Mengembangkan ilmu pengetahuan dan mencerdaskan kehidupan bangsa</li>
</ol>
HTML,
            ],
            [
                'slug'  => 'profil',
                'title' => 'Profil Singkat PPID',
                'icon'  => 'User',
                'sort_order' => 2,
                'content' => <<<HTML
<h2>Profil PPID Disdukcapil Kabupaten Dompu</h2>
<p>
  PPID Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu dibentuk sebagai amanat dari Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik. PPID berperan sebagai penghubung antara masyarakat dengan informasi yang dimiliki oleh instansi.
</p>
<h3>Struktur PPID Disdukcapil Kab. Dompu</h3>
<table>
  <thead>
    <tr><th>Jabatan</th><th>Nama</th><th>NIP</th></tr>
  </thead>
  <tbody>
    <tr><td>Kepala Dinas</td><td>—</td><td>—</td></tr>
    <tr><td>PPID Utama</td><td>Sekretaris Dinas</td><td>—</td></tr>
    <tr><td>PPID Pembantu</td><td>Kepala Sub Bagian Umum</td><td>—</td></tr>
    <tr><td>Petugas Informasi</td><td>Staf Sub Bagian Umum</td><td>—</td></tr>
  </tbody>
</table>
<h3>Visi dan Misi PPID</h3>
<p><strong>Visi:</strong> Terwujudnya pelayanan informasi publik yang transparan, akuntabel, dan berkualitas di Disdukcapil Kabupaten Dompu.</p>
<p><strong>Misi:</strong></p>
<ul>
  <li>Mengelola dan mendokumentasikan informasi publik secara profesional</li>
  <li>Memberikan pelayanan informasi yang cepat, tepat, dan mudah</li>
  <li>Meningkatkan kompetensi SDM pengelola informasi</li>
  <li>Membangun sistem informasi yang terintegrasi dan transparan</li>
</ul>
HTML,
            ],
            [
                'slug'  => 'tugas-fungsi',
                'title' => 'Tugas dan Fungsi PPID',
                'icon'  => 'ClipboardList',
                'sort_order' => 3,
                'content' => <<<HTML
<h2>Tugas PPID</h2>
<p>Berdasarkan Undang-Undang No. 14 Tahun 2008 dan peraturan pelaksanaannya, PPID memiliki tugas:</p>
<ol>
  <li>Menyediakan, memberikan dan/atau menerbitkan informasi publik yang diminta pemohon</li>
  <li>Menyampaikan pemberitahuan tertulis perihal informasi yang dikecualikan</li>
  <li>Menolak permohonan yang tidak memenuhi ketentuan berdasarkan UU KIP</li>
  <li>Menyimpan, mendokumentasikan, menyediakan, dan memberi pelayanan informasi publik</li>
</ol>
<h2>Fungsi PPID</h2>
<ul>
  <li><strong>Perencanaan:</strong> Menyusun rencana dan kebijakan pengelolaan informasi publik</li>
  <li><strong>Pengorganisasian:</strong> Mengkoordinasikan dan mengkonsolidasikan pengumpulan bahan informasi</li>
  <li><strong>Penyimpanan:</strong> Menyimpan dan mendokumentasikan informasi publik dari unit kerja</li>
  <li><strong>Pelayanan:</strong> Memberikan pelayanan informasi kepada publik yang membutuhkan</li>
  <li><strong>Penetapan:</strong> Menetapkan informasi yang dikecualikan yang tidak dapat diakses publik</li>
  <li><strong>Pengujian:</strong> Melakukan pengujian tentang konsekuensi dari informasi yang dikecualikan</li>
</ul>
<h2>Jenis Informasi Publik</h2>
<ul>
  <li><strong>Informasi Berkala:</strong> Informasi yang wajib disediakan dan diumumkan secara berkala</li>
  <li><strong>Informasi Serta-merta:</strong> Informasi yang wajib diumumkan secara serta-merta</li>
  <li><strong>Informasi Setiap Saat:</strong> Informasi yang wajib tersedia setiap saat</li>
  <li><strong>Informasi Dikecualikan:</strong> Informasi yang dikecualikan berdasarkan UU KIP</li>
</ul>
HTML,
            ],
            [
                'slug'  => 'kontak',
                'title' => 'Kontak PPID',
                'icon'  => 'Phone',
                'sort_order' => 4,
                'content' => <<<HTML
<h2>Hubungi PPID Disdukcapil Kab. Dompu</h2>
<p>Untuk mengajukan permohonan informasi atau pertanyaan terkait layanan PPID, silakan hubungi kami melalui:</p>
<div class="contact-grid">
  <div class="contact-item">
    <h3>📍 Alamat</h3>
    <p>Dinas Kependudukan dan Pencatatan Sipil<br>Kabupaten Dompu<br>Jl. Soekarno-Hatta No. 1<br>Dompu, Nusa Tenggara Barat</p>
  </div>
  <div class="contact-item">
    <h3>📞 Telepon</h3>
    <p>(0373) 21000</p>
  </div>
  <div class="contact-item">
    <h3>📧 Email</h3>
    <p>ppid@dukcapil.dompukab.go.id</p>
  </div>
  <div class="contact-item">
    <h3>🕐 Jam Layanan</h3>
    <p>Senin – Kamis: 08.00 – 16.00 WITA<br>Jumat: 08.00 – 11.30 WITA</p>
  </div>
</div>
<h3>Mekanisme Permohonan Informasi</h3>
<ol>
  <li>Mengisi formulir permohonan informasi (online atau langsung)</li>
  <li>Menyertakan identitas diri (KTP/identitas lainnya)</li>
  <li>Menyebutkan tujuan penggunaan informasi</li>
  <li>PPID wajib merespons dalam <strong>10 hari kerja</strong></li>
  <li>Dapat diperpanjang <strong>7 hari kerja</strong> dengan pemberitahuan tertulis</li>
</ol>
HTML,
            ],
            [
                'slug'  => 'sk-ppid',
                'title' => 'SK PPID Dinas Dukcapil',
                'icon'  => 'FileCheck',
                'sort_order' => 5,
                'content' => <<<HTML
<h2>Surat Keputusan PPID Disdukcapil Kab. Dompu</h2>
<p>
  Berikut adalah daftar Surat Keputusan (SK) yang berkaitan dengan pembentukan dan pelaksanaan PPID di lingkungan Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu.
</p>
<p>
  Dokumen SK dapat diunduh pada halaman Dokumen PPID atau dengan mengajukan permohonan secara langsung ke kantor Disdukcapil Kabupaten Dompu.
</p>
<h3>Daftar SK PPID</h3>
<ul>
  <li>SK Kepala Dinas tentang Penetapan PPID Disdukcapil Kab. Dompu</li>
  <li>SK tentang Penetapan Daftar Informasi Publik</li>
  <li>SK tentang Penetapan Daftar Informasi yang Dikecualikan</li>
  <li>SK tentang SOP Pelayanan Informasi Publik</li>
</ul>
HTML,
            ],
        ];

        foreach ($pages as $page) {
            PpidPage::updateOrCreate(['slug' => $page['slug']], $page);
        }

        // ── SAMPLE DOCUMENTS ────────────────────────────────────────
        $documents = [
            [
                'category'    => 'informasi_publik',
                'subcategory' => 'Informasi Berkala',
                'title'       => 'Laporan Kinerja Disdukcapil Tahun 2024',
                'description' => 'Laporan Akuntabilitas Kinerja Instansi Pemerintah (LAKIP) Disdukcapil Kabupaten Dompu tahun anggaran 2024',
                'file_type'   => 'pdf',
                'year'        => 2024,
                'sort_order'  => 1,
            ],
            [
                'category'    => 'informasi_publik',
                'subcategory' => 'Informasi Berkala',
                'title'       => 'Rencana Strategis (Renstra) 2021-2026',
                'description' => 'Dokumen perencanaan strategis Disdukcapil Kabupaten Dompu periode 2021-2026',
                'file_type'   => 'pdf',
                'year'        => 2021,
                'sort_order'  => 2,
            ],
            [
                'category'    => 'informasi_publik',
                'subcategory' => 'Informasi Berkala',
                'title'       => 'Daftar Informasi Publik Tahun 2024',
                'description' => 'Daftar lengkap informasi publik yang tersedia di Disdukcapil Kabupaten Dompu',
                'file_type'   => 'pdf',
                'year'        => 2024,
                'sort_order'  => 3,
            ],
            [
                'category'    => 'informasi_publik',
                'subcategory' => 'Informasi Setiap Saat',
                'title'       => 'Daftar Pejabat Struktural Disdukcapil Kab. Dompu',
                'description' => 'Daftar nama dan jabatan pejabat struktural di lingkungan Disdukcapil Kabupaten Dompu',
                'file_type'   => 'pdf',
                'year'        => 2024,
                'sort_order'  => 4,
            ],
            [
                'category'    => 'prosedur',
                'subcategory' => 'SOP Pelayanan',
                'title'       => 'SOP Pengurusan Akta Kelahiran',
                'description' => 'Standar Operasional Prosedur permohonan pencatatan akta kelahiran',
                'file_type'   => 'pdf',
                'year'        => 2024,
                'sort_order'  => 1,
            ],
            [
                'category'    => 'prosedur',
                'subcategory' => 'SOP Pelayanan',
                'title'       => 'SOP Pengurusan KTP Elektronik',
                'description' => 'Standar Operasional Prosedur permohonan pembuatan KTP Elektronik',
                'file_type'   => 'pdf',
                'year'        => 2024,
                'sort_order'  => 2,
            ],
            [
                'category'    => 'prosedur',
                'subcategory' => 'SOP Pelayanan',
                'title'       => 'SOP Pengurusan Kartu Keluarga (KK)',
                'description' => 'Standar Operasional Prosedur permohonan pembuatan/perubahan Kartu Keluarga',
                'file_type'   => 'pdf',
                'year'        => 2024,
                'sort_order'  => 3,
            ],
            [
                'category'    => 'prosedur',
                'subcategory' => 'SOP PPID',
                'title'       => 'SOP Pelayanan Informasi Publik PPID',
                'description' => 'Prosedur pelayanan permohonan informasi publik melalui PPID',
                'file_type'   => 'pdf',
                'year'        => 2023,
                'sort_order'  => 4,
            ],
            [
                'category'    => 'layanan_informasi',
                'subcategory' => 'Formulir',
                'title'       => 'Formulir Permohonan Informasi Publik',
                'description' => 'Formulir standar untuk mengajukan permohonan informasi publik ke PPID',
                'file_type'   => 'docx',
                'year'        => 2024,
                'sort_order'  => 1,
            ],
            [
                'category'    => 'layanan_informasi',
                'subcategory' => 'Formulir',
                'title'       => 'Formulir Keberatan Informasi Publik',
                'description' => 'Formulir untuk mengajukan keberatan atas penolakan permohonan informasi',
                'file_type'   => 'docx',
                'year'        => 2024,
                'sort_order'  => 2,
            ],
            [
                'category'    => 'layanan_informasi',
                'subcategory' => 'Laporan',
                'title'       => 'Laporan Layanan Informasi Publik 2024',
                'description' => 'Rekapitulasi pelayanan informasi publik yang diterima dan diselesaikan selama tahun 2024',
                'file_type'   => 'pdf',
                'year'        => 2024,
                'sort_order'  => 3,
            ],
        ];

        foreach ($documents as $doc) {
            PpidDocument::create($doc);
        }
    }
}
