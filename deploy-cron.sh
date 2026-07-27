#!/bin/bash
# =============================================================================
# deploy-cron.sh — Automated Backup Script (Cron Job)
# Disdukcapil Kabupaten Dompu — Website Resmi
# Server: Plesk Obsidian | Linux | MariaDB
# =============================================================================
# Plesk Cron: 0 18 * * * /path/to/httpdocs/deploy-cron.sh >> /var/log/dukcapil-backup.log 2>&1
# (18:00 UTC = 02:00 WIB)
# =============================================================================

# --- Configuration ---
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="${APP_DIR}/storage/backups"
DATE=$(date +"%Y%m%d_%H%M%S")
KEEP_DAYS=7  # Simpan backup selama 7 hari

# Load .env untuk mendapatkan DB credentials
if [ -f "${APP_DIR}/.env" ]; then
    export $(grep -v '^#' "${APP_DIR}/.env" | grep -E '^(DB_HOST|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' | xargs)
fi

# Fallback jika .env tidak lengkap
DB_HOST="${DB_HOST:-localhost}"
DB_DATABASE="${DB_DATABASE:-dukcapil_dompukab}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

# Create backup directory if not exists
mkdir -p "${BACKUP_DIR}/database"
mkdir -p "${BACKUP_DIR}/storage"

echo ""
echo "======================================="
echo "[BACKUP] Dimulai: $(date '+%Y-%m-%d %H:%M:%S')"
echo "======================================="

# --- Backup 1: Database ---
echo "[BACKUP] Membuat backup database..."
DB_BACKUP_FILE="${BACKUP_DIR}/database/db_${DATE}.sql.gz"

if mysqldump \
    --host="${DB_HOST}" \
    --user="${DB_USERNAME}" \
    --password="${DB_PASSWORD}" \
    --single-transaction \
    --routines \
    --triggers \
    "${DB_DATABASE}" 2>/dev/null | gzip > "${DB_BACKUP_FILE}"; then
    echo "[OK] Database backup: ${DB_BACKUP_FILE}"
    ls -lh "${DB_BACKUP_FILE}"
else
    echo "[ERROR] Database backup GAGAL!"
fi

# --- Backup 2: Storage (user-uploaded files) ---
echo "[BACKUP] Membuat backup storage..."
STORAGE_BACKUP_FILE="${BACKUP_DIR}/storage/storage_${DATE}.tar.gz"

if tar -czf "${STORAGE_BACKUP_FILE}" \
    -C "${APP_DIR}/storage/app" \
    public \
    2>/dev/null; then
    echo "[OK] Storage backup: ${STORAGE_BACKUP_FILE}"
    ls -lh "${STORAGE_BACKUP_FILE}"
else
    echo "[WARN] Storage backup GAGAL atau storage kosong"
fi

# --- Cleanup: Hapus backup yang lebih dari KEEP_DAYS hari ---
echo "[BACKUP] Membersihkan backup lama (> ${KEEP_DAYS} hari)..."
find "${BACKUP_DIR}/database" -name "*.sql.gz" -mtime "+${KEEP_DAYS}" -delete 2>/dev/null
find "${BACKUP_DIR}/storage" -name "*.tar.gz" -mtime "+${KEEP_DAYS}" -delete 2>/dev/null
echo "[OK] Cleanup selesai"

# --- Summary ---
echo ""
echo "======================================="
echo "[BACKUP] Selesai: $(date '+%Y-%m-%d %H:%M:%S')"
echo "DB Backups:"
ls -lh "${BACKUP_DIR}/database/" 2>/dev/null | tail -5
echo "Storage Backups:"
ls -lh "${BACKUP_DIR}/storage/" 2>/dev/null | tail -5
echo "======================================="
