# Panduan Administrator CMS
## Disdukcapil Kabupaten Dompu — Website Resmi v1.0.0

---

## Akses CMS

**URL Admin Panel:** `https://dukcapil.dompukab.go.id/admin`

### Struktur Hak Akses (RBAC)

| Role | Akses |
|------|-------|
| **Super Administrator** | Akses penuh semua modul termasuk System Settings, User Management, Theme, Audit Logs |
| **Website Administrator** | Akses semua modul CMS kecuali User Management, Theme, System Settings |
| **Content Editor** | Manajemen konten: Berita, Galeri, Pengumuman, Halaman, Banner, FAQ, Download, Inovasi, Layanan, Demografi |
| **Complaint Officer** | Manajemen Pengaduan & Survei IKM |
| **PPID Admin** | Manajemen PPID: Dokumen, Permohonan, Kategori Download |

---

## Modul CMS

### 1. Manajemen Berita
- **Akses:** `/admin/news`
- **Fitur:** Tambah, edit, hapus, restore berita; kategorisasi; tagging; TipTap WYSIWYG editor; embed gambar inline
- **Status Berita:** `draft` | `published` | `archived`

### 2. Manajemen Pengumuman
- **Akses:** `/admin/announcements`
- **Fitur:** Tambah, edit, hapus pengumuman; status aktif/tidak aktif; tanggal berlaku

### 3. Manajemen Banner
- **Akses:** `/admin/banners`
- **Fitur:** Upload banner slider homepage; drag-and-drop reorder; link target; status

### 4. Manajemen Halaman Statis
- **Akses:** `/admin/pages`
- **Fitur:** Custom HTML/rich pages; slug management; status

### 5. Manajemen Galeri
- **Akses:** `/admin/gallery`
- **Fitur:** Buat album; bulk upload foto; drag-and-drop; deskripsi foto

### 6. Manajemen Unduhan
- **Akses:** `/admin/downloads`
- **Fitur:** Kategori bertingkat; upload dokumen; tracking jumlah unduhan

### 7. Manajemen FAQ
- **Akses:** `/admin/faqs`
- **Fitur:** Tambah/edit/hapus FAQ; drag-and-drop reorder

### 8. Manajemen Menu
- **Akses:** `/admin/menus`
- **Fitur:** Multi-level navigation menu builder; drag-and-drop item ordering

### 9. Manajemen Inovasi Pelayanan
- **Akses:** `/admin/innovations`
- **Fitur:** Tambah/edit/hapus inovasi; status; thumbnail; deskripsi lengkap

### 10. Manajemen Layanan & Persyaratan
- **Akses:** `/admin/service-requirements`
- **Fitur:** Tambah/edit layanan; list persyaratan; status

### 11. Sistem Pengaduan
- **Akses:** `/admin/complaints`
- **Fitur:** Lihat semua pengaduan; ubah status (pending/proses/selesai/ditolak); reply; assign officer; manajemen kategori

### 12. Survei IKM
- **Akses:** `/admin/surveys`
- **Fitur:** Kelola periode survei; tambah/edit pertanyaan & pilihan jawaban; lihat hasil; rekap rekomendasi; tindak lanjut

### 13. Modul PPID
- **Akses:** `/admin/ppid`
- **Fitur:** Edit halaman PPID; kelola dokumen informasi publik; respons permohonan informasi

### 14. Manajemen Demografi
- **Akses:** `/admin/demographics`
- **Fitur:** Kelola dataset demografi per wilayah/semester; visualisasi dashboard demografi; manajemen hierarki wilayah

### 15. Profil Instansi
- **Akses:** `/admin/profile`
- **Fitur:** Profile Builder drag-and-drop; kelola pejabat; edit struktur organisasi visual

### 16. File Manager
- **Akses:** `/admin/files`
- **Fitur:** Upload/hapus file; buat folder; version control file; preview dokumen

---

## Super Administrator Only

### User Management
- **Akses:** `/admin/users`
- Tambah/edit/hapus operator; assign role

### Theme Customizer
- **Akses:** `/admin/theme`
- Ubah primary/secondary/accent color palette; font; logo; site title; tagline

### Audit Logs
- **Akses:** `/admin/audit-logs`
- Log semua aksi CMS: login, create, update, delete, publish, dll.

### API Management
- **Akses:** `/admin/api-keys`
- Kelola API Key untuk akses REST API v1

### System Health Dashboard
- **Akses:** `/admin/system/health`
- Monitor status DB, cache, storage, disk, memory, queue, dan production readiness checklist

---

## Tips Penting

> [!WARNING]
> Selalu lakukan logout setelah selesai bekerja di CMS, terutama jika menggunakan komputer bersama.

> [!TIP]
> Gunakan fitur **Audit Logs** untuk melacak siapa yang mengubah apa dan kapan.

> [!IMPORTANT]
> Backup database secara berkala sebelum melakukan perubahan besar pada data.
