# Panduan Deployment Production
## Disdukcapil Kabupaten Dompu — Website Resmi v1.0.0

---

## Persyaratan Server (Requirements)

| Komponen | Versi Minimal | Rekomendasi |
|----------|-------------|-------------|
| PHP | 8.2 | **8.3** |
| MySQL | 8.0 | 8.0+ |
| Nginx / Apache | Latest | Nginx 1.24+ |
| Node.js | 18 | 22 (build only) |
| Composer | 2.x | 2.7+ |
| RAM | 512 MB | **2 GB+** |
| Storage | 10 GB | 50 GB+ |
| SSL/TLS | Required | Let's Encrypt |

---

## Step-by-Step Deployment

### 1. Clone Repository

```bash
cd /var/www
git clone [REPO_URL] dukcapil.dompukab.go.id
cd dukcapil.dompukab.go.id
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Frontend (untuk build assets)
npm ci
npm run build
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env`:

```env
APP_NAME="Dukcapil Dompu"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dukcapil.dompukab.go.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dukcapil_dompukab
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

CACHE_STORE=file
SESSION_DRIVER=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

# Mail (opsional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@dukcapil.dompukab.go.id
MAIL_FROM_NAME="Dukcapil Dompu"
```

### 4. Database Migration & Seeding

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. Storage & Permissions

```bash
php artisan storage:link

# Set permissions (Linux)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Production Optimization

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7. Nginx Configuration

```nginx
server {
    listen 80;
    server_name dukcapil.dompukab.go.id;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name dukcapil.dompukab.go.id;
    root /var/www/dukcapil.dompukab.go.id/public;

    # SSL Configuration
    ssl_certificate     /etc/letsencrypt/live/dukcapil.dompukab.go.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/dukcapil.dompukab.go.id/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security: Block direct access to sensitive files
    location ~* \.(env|log|sql|bak|git)$ {
        deny all;
    }

    # Static asset caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
```

### 8. Verifikasi Akhir

Jalankan perintah-perintah berikut untuk verifikasi:

```bash
# Verifikasi route
php artisan route:list

# Cek health
curl -I https://dukcapil.dompukab.go.id

# Cek sitemap
curl https://dukcapil.dompukab.go.id/sitemap.xml

# Cek robots
curl https://dukcapil.dompukab.go.id/robots.txt
```

---

## Prosedur Backup

### Manual Backup

```bash
# Backup database
mysqldump -u [user] -p dukcapil_dompukab > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup storage (user-uploaded files)
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public
```

### Automated Backup (Crontab)

Tambahkan di crontab (`crontab -e`):

```cron
# Daily database backup at 02:00 WIB
0 2 * * * cd /var/www/dukcapil.dompukab.go.id && mysqldump -u root -p'password' dukcapil_dompukab > /backups/db_$(date +\%Y\%m\%d).sql

# Weekly storage backup
0 3 * * 0 tar -czf /backups/storage_$(date +\%Y\%m\%d).tar.gz /var/www/dukcapil.dompukab.go.id/storage/app/public
```

### Restore Database

```bash
mysql -u [user] -p dukcapil_dompukab < backup_YYYYMMDD_HHMMSS.sql
```

---

## Prosedur Update Production

```bash
cd /var/www/dukcapil.dompukab.go.id

# Enable maintenance mode
php artisan down --message="Sedang Pemeliharaan" --retry=60

# Pull changes
git pull origin main

# Install new dependencies
composer install --no-dev --optimize-autoloader

# Build new assets (jika ada perubahan frontend)
npm ci && npm run build

# Run new migrations
php artisan migrate --force

# Clear and reoptimize cache
php artisan optimize:clear
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Disable maintenance mode
php artisan up
```

---

## Security Checklist Production

- [ ] `APP_DEBUG=false` pada `.env`
- [ ] `APP_ENV=production` pada `.env`
- [ ] HTTPS aktif dengan sertifikat SSL valid
- [ ] Direktori `storage/`, `bootstrap/cache/` tidak dapat diakses publik
- [ ] File `.env` tidak dapat diakses publik
- [ ] Firewall aktif (iptables/UFW)
- [ ] SSH menggunakan key authentication (bukan password)
- [ ] Backup terjadwal aktif
- [ ] Log rotation aktif
- [ ] Database password kuat (min. 16 karakter, campuran)
