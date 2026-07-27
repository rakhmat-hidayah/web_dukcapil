#!/bin/bash
# =============================================================================
# deploy.sh — Automated Deployment Script
# Disdukcapil Kabupaten Dompu — Website Resmi
# Server: Plesk Obsidian | Linux | PHP 8.3 | MariaDB
# =============================================================================
# Usage: bash deploy.sh [--skip-build] [--force-seed]
# =============================================================================

set -e  # Exit immediately if a command exits with a non-zero status

# --- Configuration ---
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"
COMPOSER_BIN="${COMPOSER_BIN:-/usr/bin/composer}"
TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S WIB")
LOG_FILE="${APP_DIR}/storage/logs/deploy.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() { echo -e "${BLUE}[DEPLOY]${NC} $1" | tee -a "${LOG_FILE}"; }
success() { echo -e "${GREEN}[OK]${NC} $1" | tee -a "${LOG_FILE}"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1" | tee -a "${LOG_FILE}"; }
error() { echo -e "${RED}[ERROR]${NC} $1" | tee -a "${LOG_FILE}"; exit 1; }

echo "" | tee -a "${LOG_FILE}"
echo "============================================================" | tee -a "${LOG_FILE}"
log "Deployment dimulai: ${TIMESTAMP}"
echo "============================================================" | tee -a "${LOG_FILE}"

# --- Check environment ---
log "Memeriksa environment..."
[ -f "${APP_DIR}/.env" ] || error ".env tidak ditemukan! Buat .env dari .env.production.example terlebih dahulu."
[ -f "${PHP_BIN}" ] || PHP_BIN=$(which php) || error "PHP tidak ditemukan!"
success "PHP ditemukan: $(${PHP_BIN} -r 'echo PHP_VERSION;')"

# --- Step 1: Maintenance Mode ON ---
log "Mengaktifkan Maintenance Mode..."
${PHP_BIN} "${APP_DIR}/artisan" down \
    --message="🔧 Website sedang dalam pemeliharaan. Akan kembali dalam beberapa menit." \
    --retry=30 \
    --secret="dukcapil-secret-bypass" \
    2>/dev/null || warn "Maintenance mode mungkin sudah aktif"
success "Maintenance Mode: AKTIF"

# --- Step 2: Git Pull ---
log "Mengambil kode terbaru dari repository..."
cd "${APP_DIR}"
git fetch --all
git pull origin main
success "Git pull selesai"

# --- Step 3: Composer Install ---
log "Menginstall PHP dependencies..."
${COMPOSER_BIN} install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --quiet
success "Composer install selesai"

# --- Step 4: Database Migration ---
log "Menjalankan database migration..."
${PHP_BIN} "${APP_DIR}/artisan" migrate --force --no-interaction
success "Migration selesai"

# --- Step 5: Clear Caches ---
log "Membersihkan cache lama..."
${PHP_BIN} "${APP_DIR}/artisan" optimize:clear --quiet
success "Cache dibersihkan"

# --- Step 6: Rebuild Production Cache ---
log "Membangun ulang cache production..."
${PHP_BIN} "${APP_DIR}/artisan" optimize
success "Cache dibangun ulang"

# --- Step 7: Storage Link ---
log "Memeriksa storage symlink..."
if [ ! -L "${APP_DIR}/public/storage" ]; then
    ${PHP_BIN} "${APP_DIR}/artisan" storage:link
    success "Storage symlink dibuat"
else
    success "Storage symlink sudah ada"
fi

# --- Step 8: Set Permissions ---
log "Mengatur permissions..."
find "${APP_DIR}/storage" -type d -exec chmod 775 {} \;
find "${APP_DIR}/storage" -type f -exec chmod 664 {} \;
find "${APP_DIR}/bootstrap/cache" -type d -exec chmod 775 {} \;
find "${APP_DIR}/bootstrap/cache" -type f -exec chmod 664 {} \;
success "Permissions selesai"

# --- Step 9: Maintenance Mode OFF ---
log "Menonaktifkan Maintenance Mode..."
${PHP_BIN} "${APP_DIR}/artisan" up
success "Maintenance Mode: NONAKTIF"

# --- Done ---
echo "" | tee -a "${LOG_FILE}"
echo "============================================================" | tee -a "${LOG_FILE}"
success "✅ Deployment selesai pada: $(date +"%Y-%m-%d %H:%M:%S")"
echo "   Domain: https://dukcapil.dompukab.go.id" | tee -a "${LOG_FILE}"
echo "   Health: https://dukcapil.dompukab.go.id/admin/system/health" | tee -a "${LOG_FILE}"
echo "============================================================" | tee -a "${LOG_FILE}"
echo ""
