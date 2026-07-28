/**
 * Helper untuk menghasilkan URL storage yang benar.
 * Menghindari double /storage/ prefix.
 *
 * Penggunaan:
 *   import { storageUrl } from '@/utils/storage'
 *   :src="storageUrl(official.photo)"
 */

/**
 * Konversi path file ke URL publik yang benar.
 * Menangani berbagai format input:
 * - null / undefined / ''  → null
 * - '/images/xxx.jpg'      → '/images/xxx.jpg' (biarkan)
 * - '/storage/xxx.png'     → '/storage/xxx.png' (sudah benar)
 * - 'http://...'           → 'http://...' (biarkan)
 * - 'officials/xxx.png'    → '/storage/officials/xxx.png'
 */
export function storageUrl(path, fallback = null) {
  if (!path) return fallback
  const p = String(path).trim()
  if (!p) return fallback
  // Sudah berupa URL absolut atau path /images/
  if (p.startsWith('http://') || p.startsWith('https://')) return p
  if (p.startsWith('/images/')) return p
  // Sudah ada prefix /storage/
  if (p.startsWith('/storage/')) return p
  // Relative path → tambah /storage/
  return '/storage/' + p.replace(/^\/+/, '')
}
