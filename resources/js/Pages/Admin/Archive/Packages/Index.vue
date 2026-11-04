<template>
  <Head title="Paket Arsip ZIP - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-indigo-900/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 mb-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
              Manajemen Paket Arsip Digital
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Paket Arsip ZIP
            </h1>
            <p class="text-xs sm:text-sm text-indigo-200 mt-1 max-w-2xl leading-relaxed">
              Daftar paket berkas terkompresi resmi dengan struktur terstandarisasi, dilengkapi manifest JSON, tabel CSV, dan kalkulasi hash SHA-256 untuk menjamin integritas arsip dokumen.
            </p>
          </div>

          <Link
            :href="route('admin.archive.ready-applications')"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2 cursor-pointer shrink-0"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Paket Arsip Baru &rarr;
          </Link>
        </div>
      </div>

      <!-- Filters Bar -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
        <form @submit.prevent="applyFilter" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Status Paket</label>
            <select
              v-model="filterForm.status"
              class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"
            >
              <option value="">Semua Status</option>
              <option value="READY_FOR_DOWNLOAD">Siap Diunduh</option>
              <option value="INCOMPLETE">Dokumen Tidak Lengkap</option>
              <option value="FAILED">Gagal Dibuat</option>
              <option value="CORRUPTED">Integritas Berubah / Corrupted</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Periode (YYYY-MM)</label>
            <input
              v-model="filterForm.period"
              type="text"
              placeholder="Contoh: 2026-10"
              class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"
            />
          </div>

          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Cari Kode Paket</label>
            <input
              v-model="filterForm.search"
              type="text"
              placeholder="ARSIP_PERMOHONAN_..."
              class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"
            />
          </div>

          <div class="flex items-end gap-2">
            <button
              type="submit"
              class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs rounded-xl transition cursor-pointer"
            >
              Filter
            </button>
            <button
              type="button"
              @click="resetFilter"
              class="p-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 rounded-xl transition cursor-pointer"
              title="Reset"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
          </div>
        </form>
      </div>

      <!-- Packages Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 uppercase tracking-wider font-bold border-y border-slate-100 dark:border-slate-700/60">
              <tr>
                <th class="py-3 px-4">Kode Paket</th>
                <th class="py-3 px-4">Periode</th>
                <th class="py-3 px-4 text-center">Permohonan</th>
                <th class="py-3 px-4 text-center">Berkas</th>
                <th class="py-3 px-4">Ukuran & SHA-256</th>
                <th class="py-3 px-4">Status Paket</th>
                <th class="py-3 px-4">Konfirmasi Operator</th>
                <th class="py-3 px-4">Penyimpanan Permanen</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <tr v-if="packages.data.length === 0">
                <td colspan="9" class="text-center py-10 text-slate-400">
                  Belum ada paket arsip yang cocok dengan kriteria filter.
                </td>
              </tr>
              <tr v-for="pkg in packages.data" :key="pkg.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition">
                <td class="py-4 px-4">
                  <Link :href="route('admin.archive.packages.show', pkg.id)" class="font-mono font-bold text-blue-600 dark:text-blue-400 hover:underline block">
                    {{ pkg.package_code }}
                  </Link>
                  <span class="text-[10px] text-slate-400">Dibuat {{ new Date(pkg.created_at).toLocaleString('id-ID') }}</span>
                </td>
                <td class="py-4 px-4 font-medium text-slate-600 dark:text-slate-300">
                  {{ pkg.archive_period || '-' }}
                </td>
                <td class="py-4 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                  {{ pkg.total_applications }}
                </td>
                <td class="py-4 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                  {{ pkg.total_files }}
                </td>
                <td class="py-4 px-4">
                  <p class="font-bold text-slate-800 dark:text-slate-200">{{ pkg.formatted_package_size }}</p>
                  <p v-if="pkg.sha256_checksum" class="font-mono text-[10px] text-slate-400 truncate max-w-[140px]" :title="pkg.sha256_checksum">
                    {{ pkg.sha256_checksum.substring(0, 14) }}...
                  </p>
                </td>
                <td class="py-4 px-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide inline-flex items-center gap-1"
                    :class="{
                      'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': pkg.status === 'READY_FOR_DOWNLOAD',
                      'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': pkg.status === 'PROCESSING' || pkg.status === 'QUEUED',
                      'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300': pkg.status === 'INCOMPLETE' || pkg.status === 'FAILED' || pkg.status === 'CORRUPTED',
                    }"
                  >
                    {{ pkg.status_label }}
                  </span>
                </td>
                <td class="py-4 px-4">
                  <span
                    v-if="pkg.is_operator_confirmed"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 inline-flex items-center gap-1"
                    :title="pkg.operator_storage_location_notes"
                  >
                    ✓ Disimpan Operator
                  </span>
                  <button
                    v-else-if="pkg.status === 'READY_FOR_DOWNLOAD'"
                    @click="openConfirmModal(pkg)"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 transition cursor-pointer"
                  >
                    + Konfirmasi
                  </button>
                  <span v-else class="text-[10px] text-slate-400">-</span>
                </td>
                <td class="py-4 px-4">
                  <span
                    class="px-2 py-0.5 rounded text-[10px] font-semibold"
                    :class="{
                      'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300': pkg.permanent_storage_status === 'NOT_CONFIGURED',
                      'bg-teal-100 text-teal-800': pkg.permanent_storage_status === 'PERMANENT_STORAGE_VERIFIED',
                      'bg-amber-100 text-amber-800': pkg.permanent_storage_status === 'PENDING_TRANSFER' || pkg.permanent_storage_status === 'TRANSFERRED',
                      'bg-rose-100 text-rose-800': pkg.permanent_storage_status === 'TRANSFER_FAILED' || pkg.permanent_storage_status === 'VERIFICATION_FAILED',
                    }"
                  >
                    {{ pkg.permanent_storage_label }}
                  </span>
                </td>
                <td class="py-4 px-4 text-right space-x-1.5 whitespace-nowrap">
                  <a
                    v-if="pkg.status === 'READY_FOR_DOWNLOAD'"
                    :href="pkg.download_url"
                    class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs inline-flex items-center gap-1 shadow-sm transition"
                    title="Download ZIP"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                  </a>
                  <Link
                    :href="route('admin.archive.packages.show', pkg.id)"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs inline-flex items-center gap-1 transition"
                  >
                    Detail
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="packages.links && packages.links.length > 3" class="flex flex-col sm:flex-row items-center justify-end pt-4 border-t border-slate-100 dark:border-slate-700">
          <div class="w-full sm:w-auto flex items-center justify-start sm:justify-end gap-1 overflow-x-auto max-w-full pb-1 sm:pb-0 scrollbar-thin select-none touch-pan-x">
            <template v-for="(link, i) in packages.links" :key="i">
              <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                preserve-state
                v-html="link.label"
                class="min-w-[36px] h-9 px-2.5 rounded-xl text-xs font-semibold transition flex items-center justify-center shrink-0 cursor-pointer active:scale-95 touch-manipulation"
                :class="link.active ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200'"
              />
              <span v-else v-html="link.label" class="min-w-[36px] h-9 px-2.5 text-xs text-slate-400 flex items-center justify-center shrink-0 cursor-not-allowed opacity-50 select-none" />
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Storage Modal -->
    <div v-if="showConfirmModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-base font-bold text-slate-900 dark:text-white">Konfirmasi Arsip Tersimpan (Manual)</h3>
          <button @click="showConfirmModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-2xl text-xs space-y-1.5 border border-slate-200 dark:border-slate-700">
          <p><span class="text-slate-400">Kode Paket:</span> <span class="font-mono font-bold">{{ activePackage?.package_code }}</span></p>
          <p><span class="text-slate-400">Ukuran:</span> <span class="font-bold">{{ activePackage?.formatted_package_size }}</span></p>
          <p><span class="text-slate-400">SHA-256:</span> <span class="font-mono text-[10px] break-all">{{ activePackage?.sha256_checksum }}</span></p>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            Lokasi Media Penyimpanan yang Digunakan *
          </label>
          <textarea
            v-model="confirmNotes"
            rows="3"
            placeholder="Contoh: Disimpan pada Harddisk Eksternal Arsip No. 02, Folder /ARSIP_2026/OKTOBER/ atau NAS Ruang Server"
            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-2xl border border-amber-200 dark:border-amber-900 text-[11px] text-amber-800 dark:text-amber-300">
          <strong>Perhatian:</strong> Konfirmasi manual ini merupakan pernyataan operator bahwa arsip telah diunduh dan diamankan pada media yang ditetapkan. Ini bukan bukti transfer atau verifikasi otomatis media permanen.
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button @click="showConfirmModal = false" class="px-4 py-2 text-xs font-bold border rounded-xl">Batal</button>
          <button @click="submitConfirm" :disabled="!confirmNotes.trim()" class="px-4 py-2 text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white rounded-xl disabled:opacity-50">
            Simpan Konfirmasi
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  packages: Object,
  filters: Object,
  serviceTypes: Array,
});

const filterForm = ref({
  status: props.filters.status || '',
  period: props.filters.period || '',
  search: props.filters.search || '',
});

const showConfirmModal = ref(false);
const activePackage = ref(null);
const confirmNotes = ref('');

function applyFilter() {
  router.get(route('admin.archive.packages.index'), filterForm.value, {
    preserveState: true,
    preserveScroll: true,
  });
}

function resetFilter() {
  filterForm.value = { status: '', period: '', search: '' };
  applyFilter();
}

function openConfirmModal(pkg) {
  activePackage.value = pkg;
  confirmNotes.value = pkg.operator_storage_location_notes || '';
  showConfirmModal.value = true;
}

function submitConfirm() {
  if (!activePackage.value) return;

  router.post(route('admin.archive.packages.confirm', activePackage.value.id), {
    storage_location_notes: confirmNotes.value,
  }, {
    onSuccess: () => {
      showConfirmModal.value = false;
      activePackage.value = null;
      confirmNotes.value = '';
    },
  });
}
</script>
