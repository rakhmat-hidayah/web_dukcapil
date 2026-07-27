# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2026-07-28

### 🎉 Initial Production Release — Enterprise Government Website

**Disdukcapil Kabupaten Dompu — Website Resmi v1.0.0**

---

### Added

#### Core CMS Modules (16 Modules)
- **Manajemen Pengguna & Hak Akses** — RBAC dengan 5 role: Super Administrator, Website Administrator, Content Editor, Complaint Officer, PPID Admin
- **Theme Customizer** — Color palette & typography customizer with live preview
- **Profile Builder** — Drag-and-drop profile section builder (Hero, Vision/Mission, Officials, Org Chart, Gallery, dll)
- **Manajemen Berita** — Full CMS berita dengan TipTap WYSIWYG, kategori, tag, soft delete
- **Manajemen Pengumuman** — CMS pengumuman multi-status
- **Manajemen Halaman Statis** — Custom page builder
- **Manajemen Galeri** — Album-based gallery with bulk upload
- **Manajemen Unduhan** — Hierarchical download categories
- **Manajemen Banner** — Hero banner slider with drag-to-reorder
- **Manajemen FAQ** — Frequently Asked Questions CMS
- **Manajemen Menu** — Dynamic navigation menu builder
- **Sistem Pengaduan** — Complaint management dengan tracking ticket, assignment, reply
- **Survei Kepuasan Masyarakat (IKM)** — Full IKM survey lifecycle
- **Modul PPID** — PPID request management, document library, public information
- **Manajemen Demografi** — Dataset demografi kependudukan per wilayah/semester
- **File Manager** — Enterprise file manager dengan version control
- **API Gateway** — Full REST API v1 dengan API Key management, rate limiting, logging

#### Frontend Public Website
- Homepage dengan hero, berita terkini, statistik, inovasi, dan download shortcuts
- Halaman Profil Instansi, Struktur Organisasi, Direktori Pejabat
- Halaman Berita & Detail Berita
- Halaman Galeri & Album
- Halaman Unduhan (Download Center)
- Halaman FAQ
- Halaman Layanan & Persyaratan Layanan
- Halaman Inovasi Pelayanan
- Halaman Statistik Kependudukan
- Halaman Pengaduan Masyarakat (form + tracking)
- Halaman Survei Kepuasan (IKM)
- Portal PPID Lengkap
- Halaman Kontak
- Halaman Layanan Mobile
- Pencarian Global
- Sitemap XML & Robots.txt

#### Security
- CAPTCHA (image & math) pada form login
- Honeypot anti-bot pada form login
- Rate Limiting (5 attempts/5min) pada login
- Security Headers Middleware (X-Frame-Options, CSP, Referrer-Policy, Permissions-Policy)
- API Key authentication dengan header `X-API-KEY`
- CSRF protection pada semua form
- Signed URL pada download stream

#### Performance
- Vite production build (asset hashing, code splitting)
- Query caching untuk homepage data
- Eager loading pada semua relasi utama
- Database indexes pada kolom foreign key dan searchable columns

#### SEO
- Open Graph meta tags (Facebook/WhatsApp)
- Twitter Card meta tags
- Schema.org GovernmentOffice structured data
- Web App Manifest
- Dynamic sitemap.xml (via controller)
- Robots.txt dengan production domain

#### Monitoring & Health
- System Health Dashboard (`/admin/system/health`)
- Database, Cache, Storage, Queue health checks
- Disk usage monitoring
- Memory usage tracking
- Production Readiness Checklist

#### Logging & Audit
- Activity log (Spatie) pada semua aksi CMS
- Login/Logout/Failed Login tracking
- Visitor tracking dengan device detection
- API request/response logging

---

### Technical Stack

| Component | Version |
|-----------|---------|
| PHP | 8.3 |
| Laravel | 13.x |
| Vue.js | 3.5 |
| Inertia.js | 3.x |
| Tailwind CSS | 4.0 |
| Vite | 8.0 |
| MySQL | 8.0+ |
| Spatie Permission | 8.x |
| Spatie Activity Log | 4.x |
| TipTap | 3.x |
| ECharts | 6.x |

---

## [Unreleased]

*(For future development tracking)*
