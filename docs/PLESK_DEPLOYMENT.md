# Panduan Deployment Plesk
## Disdukcapil Kabupaten Dompu — Website Resmi v1.0.0

> **Server**: Plesk Obsidian · Linux · Apache+Nginx · PHP 8.3 · MariaDB  
> **Domain**: `https://dukcapil.dompukab.go.id`  
> **SSL**: Aktif (Let's Encrypt via Plesk)

---

## Prasyarat

Pastikan hal berikut sudah tersedia di Plesk sebelum deployment:

- [ ] Domain `dukcapil.dompukab.go.id` sudah ditambahkan di Plesk
- [ ] SSL/Let's Encrypt sudah aktif pada domain tersebut
- [ ] PHP 8.3 sudah dipilih sebagai PHP handler di Plesk
- [ ] Database MariaDB sudah dibuat (catat nama DB, user, password)
- [ ] SSH akses sudah aktif
- [ ] Composer tersedia di server (cek: `composer --version`)
- [ ] Git tersedia di server (cek: `git --version`)

---

## Langkah 1: Konfigurasi Document Root di Plesk

> [!IMPORTANT]
> Laravel membutuhkan `public/` sebagai Document Root, bukan root project.

1. Buka Plesk → **Websites & Domains** → `dukcapil.dompukab.go.id`
2. Klik **Hosting Settings**
3. Ubah **Document Root** dari `/httpdocs` menjadi `/httpdocs/public`
4. Klik **OK / Save**

---

## Langkah 2: Upload Project via Git (SSH)

```bash
# SSH ke server Plesk
ssh [USERNAME]@dukcapil.dompukab.go.id

# Masuk ke folder web
cd /var/www/vhosts/dukcapil.dompukab.go.id/httpdocs

# Clone repository
git clone [REPO_URL] .

# Atau jika sudah ada, pull update
git pull origin main
```

---

## Langkah 3: Install Dependencies PHP

```bash
# Install Composer dependencies (production only, no dev tools)
composer install --no-dev --optimize-autoloader --no-interaction
```

---

## Langkah 4: Konfigurasi Environment

```bash
# Salin template environment production
cp .env.production.example .env

# Generate application key
php artisan key:generate

# Edit .env dengan nilai production yang benar
nano .env
```

Nilai penting yang harus diisi:
```env
APP_KEY=        # akan di-generate otomatis
DB_DATABASE=    # nama database Plesk
DB_USERNAME=    # user database Plesk
DB_PASSWORD=    # password database Plesk (gunakan password kuat)
```

---

## Langkah 5: Database Migration & Seeding

```bash
# Jalankan migration
php artisan migrate --force

# Jalankan seeder untuk data awal (hanya fresh install)
php artisan db:seed --force
```

---

## Langkah 6: Storage & Permissions

```bash
# Buat symlink storage
php artisan storage:link

# Set permissions yang benar (Linux)
find storage -type f -exec chmod 664 {} \;
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

# Set ownership ke PHP-FPM user (sesuaikan dengan Plesk user)
chown -R $(whoami):$(whoami) storage bootstrap/cache
```

---

## Langkah 7: Build Frontend Assets

```bash
# Install Node.js dependencies (jika Node.js tersedia di server)
npm ci

# Build production assets
npm run build
```

> [!NOTE]
> Jika Node.js tidak tersedia di server Plesk, build assets secara **lokal** terlebih dahulu,
> lalu upload folder `public/build/` ke server via SFTP atau commit ke repository.

**Alternatif — Build lokal lalu upload:**
```bash
# Di komputer lokal (developer)
npm run build
git add public/build/
git commit -m "chore: add production build assets"
git push
```

---

## Langkah 8: Production Optimization

```bash
# Clear semua cache lama
php artisan optimize:clear

# Rebuild semua cache untuk production
php artisan optimize

# Verifikasi cache status
php artisan about
```

Output yang diharapkan:
```
Config    : CACHED
Events    : CACHED
Routes    : CACHED
Views     : CACHED
```

---

## Langkah 9: Konfigurasi PHP di Plesk

Di Plesk → **PHP Settings** untuk domain ini:

| Setting | Nilai |
|---------|-------|
| PHP Version | 8.3 |
| memory_limit | 256M atau lebih |
| upload_max_filesize | 50M |
| post_max_size | 55M |
| max_execution_time | 120 |
| max_input_time | 120 |
| date.timezone | Asia/Makassar |

---

## Langkah 10: Konfigurasi Nginx Additional Directives (Plesk)

Di Plesk → **Apache & nginx Settings** → **Additional nginx directives**:

```nginx
# Gzip compression (jika belum diaktifkan di .htaccess)
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml application/json application/javascript application/xml+rss text/javascript image/svg+xml;

# Static assets caching
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|pdf)$ {
    expires 6M;
    add_header Cache-Control "public, max-age=15552000";
    access_log off;
}

# Block access to sensitive files
location ~ /\. {
    deny all;
}
location ~ (\.env|composer\.(json|lock)|package\.json)$ {
    deny all;
}
```

---

## Langkah 11: Setup Cron Job di Plesk

Di Plesk → **Scheduled Tasks** → **Add Task**:

### Cron 1: Laravel Scheduler
```
* * * * * /usr/bin/php /var/www/vhosts/dukcapil.dompukab.go.id/httpdocs/artisan schedule:run >> /dev/null 2>&1
```

### Cron 2: Database Backup Harian (02:00 WIB = 18:00 UTC)
```
0 18 * * * /var/www/vhosts/dukcapil.dompukab.go.id/httpdocs/deploy-cron.sh >> /var/log/dukcapil-backup.log 2>&1
```

---

## Langkah 12: Verifikasi Akhir

```bash
# 1. Cek semua routes terdaftar
php artisan route:list | head -30

# 2. Cek health sistem
curl -I https://dukcapil.dompukab.go.id

# 3. Cek sitemap
curl https://dukcapil.dompukab.go.id/sitemap.xml | head -20

# 4. Cek security headers
curl -I https://dukcapil.dompukab.go.id | grep -i "x-frame\|strict-transport\|content-security"
```

### Akses Health Dashboard
Setelah login sebagai Super Administrator:
```
https://dukcapil.dompukab.go.id/admin/system/health
```

---

## Prosedur Update (Deploy Baru)

```bash
# Script: deploy.sh
# Jalankan: bash deploy.sh

php artisan down --message="Sedang Pemeliharaan Sistem" --retry=60

git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force

php artisan optimize:clear
php artisan optimize

php artisan up
```

---

## Troubleshooting Umum

| Masalah | Solusi |
|---------|--------|
| 500 Internal Server Error | Cek `storage/logs/laravel.log` |
| Storage tidak bisa ditulis | `chmod -R 775 storage bootstrap/cache` |
| Upload gagal | Cek `upload_max_filesize` di PHP Settings Plesk |
| Session tidak tersimpan | Pastikan `SESSION_DRIVER=database` dan migration sudah jalan |
| Assets tidak load | Jalankan `npm run build` dan commit `public/build/` |
| CSRF Token mismatch | Clear browser cache, cek `SESSION_DOMAIN` di `.env` |

---

## Catatan Penting Plesk

> [!WARNING]
> Plesk menggunakan kombinasi **Nginx sebagai reverse proxy + Apache** sebagai backend.
> Beberapa Apache modules mungkin tidak aktif. Jika `.htaccess` gzip/cache tidak berfungsi,
> gunakan **Nginx Additional Directives** di Plesk sebagai alternatif.

> [!NOTE]
> Jika SMTP belum dikonfigurasi, sistem berjalan normal — notifikasi email akan di-log
> ke `storage/logs/laravel.log` (karena `MAIL_MAILER=log` di `.env.production.example`).
