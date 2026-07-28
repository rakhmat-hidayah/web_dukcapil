/**
 * Helper untuk menghasilkan URL upload publik yang benar.
 * Mengarahkan seluruh aset publik ke /uploads/ tanpa ketergantungan storage symlink.
 *
 * Penggunaan:
 *   import { storageUrl } from '@/utils/storage'
 *   :src="storageUrl(official.photo)"
 */

/**
 * Konversi path file ke URL publik /uploads/ yang benar.
 * Menangani berbagai format input:
 * - null / undefined / ''   → fallback
 * - 'http://...'            → 'http://...' (biarkan)
 * - '/images/xxx.jpg'       → '/images/xxx.jpg' (biarkan)
 * - '/storage/officials/x'  → '/uploads/officials/x' (otomatis dikonversi)
 * - 'uploads/officials/x'   → '/uploads/officials/x'
 * - 'officials/xxx.png'     → '/uploads/officials/xxx.png'
 */
export function storageUrl(path, fallback = null) {
  if (!path) return fallback
  const p = String(path).trim()
  if (!p) return fallback

  // Return full HTTP/HTTPS or /images/ as is
  if (p.startsWith('http://') || p.startsWith('https://')) return p
  if (p.startsWith('/images/')) return p

  // Convert legacy /storage/ or storage/ to /uploads/
  let cleanPath = p.replace(/^\/?storage\//, 'uploads/')

  if (!cleanPath.startsWith('/uploads/') && !cleanPath.startsWith('uploads/')) {
    cleanPath = 'uploads/' + cleanPath.replace(/^\/+/, '')
  }

  if (!cleanPath.startsWith('/')) {
    cleanPath = '/' + cleanPath
  }

  return cleanPath
}
