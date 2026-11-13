<template>
  <Head title="Permohonan Siap Diarsipkan - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-sky-900/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-500/20 text-sky-300 border border-sky-400/30 mb-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Kriteria Pengarsipan Sesuai Workflow
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Permohonan Siap Diarsipkan
            </h1>
            <p class="text-xs sm:text-sm text-sky-200 mt-1 max-w-2xl leading-relaxed">
              Daftar permohonan yang telah siap diambil (KTP Siap Diambil) maupun yang telah selesai diserahkan. Berkas dokumen persyaratan diverifikasi keberadaan fisiknya di storage server sebelum dikemas ke dalam arsip ZIP.
            </p>
          </div>

          <!-- Top Bulk Action Button -->
          <div class="flex flex-wrap items-center gap-2">
            <button
              v-if="selectedIds.length > 0"
              @click="openCreateModal(false)"
              class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
              Buat Paket ZIP Dari Pilihan ({{ selectedIds.length }} Terpilih)
            </button>
            <button
              @click="openCreateModal(true)"
              class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-2 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              Buat Paket Berdasarkan Periode &rarr;
            </button>
          </div>
        </div>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <form @submit.prevent="applyFilter" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
          <!-- Filter Periode Bulan -->
          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Periode Bulan</label>
            <select
              v-model="filterForm.period"
              class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Semua Periode</option>
              <option v-for="(cnt, p) in availablePeriods" :key="p" :value="p">
                {{ formatPeriodWithDates(p) }} ({{ cnt }} permohonan)
              </option>
            </select>
          </div>

          <!-- Filter Jenis Layanan -->
          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Jenis Layanan</label>
            <select
              v-model="filterForm.service_type_id"
              class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Semua Layanan</option>
              <option v-for="st in serviceTypes" :key="st.id" :value="st.id">
                {{ st.name }}
              </option>
            </select>
          </div>

          <!-- Filter Status Permohonan -->
          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Status Permohonan</label>
            <select
              v-model="filterForm.status"
              class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Semua Status</option>
              <option value="READY_FOR_PICKUP">KTP Siap Diambil</option>
              <option value="COMPLETED">KTP Diserahkan</option>
            </select>
          </div>

          <!-- Pencarian -->
          <div class="lg:col-span-2">
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Cari No. Registrasi / Nama / NIK</label>
            <div class="relative">
              <input
                v-model="filterForm.search"
                type="text"
                placeholder="REG-2610... atau Nama Pemohon"
                class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5 pl-9 focus:ring-2 focus:ring-blue-500"
              />
              <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
          </div>

          <!-- Tombol Filter -->
          <div class="flex items-end gap-2">
            <button
              type="submit"
              class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer"
            >
              Filter Data
            </button>
            <button
              type="button"
              @click="resetFilter"
              class="p-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 rounded-xl transition cursor-pointer"
              title="Reset Filter"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
          </div>
        </form>
      </div>

      <!-- Applications Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <input
              type="checkbox"
              id="select-all"
              @change="toggleSelectAll"
              :checked="isAllSelected"
              class="w-4 h-4 rounded text-blue-600 border-slate-300 dark:border-slate-600 focus:ring-blue-500 cursor-pointer"
            />
            <label for="select-all" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
              Pilih Semua di Halaman Ini ({{ selectedIds.length }} dipilih)
            </label>
          </div>

          <div class="text-xs text-slate-500">
            Menampilkan {{ applications.from || 0 }} - {{ applications.to || 0 }} dari total {{ applications.total }} permohonan
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 uppercase tracking-wider font-bold border-y border-slate-100 dark:border-slate-700/60">
              <tr>
                <th class="py-3 px-3 w-8"></th>
                <th class="py-3 px-4">No. Registrasi</th>
                <th class="py-3 px-4">Nama Pemohon / Yang Dimohonkan</th>
                <th class="py-3 px-4">Jenis Layanan</th>
                <th class="py-3 px-4">Status & Tanggal</th>
                <th class="py-3 px-4">Kelengkapan Berkas Fisik</th>
                <th class="py-3 px-4">Jumlah & Ukuran</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <tr v-if="applications.data.length === 0">
                <td colspan="7" class="text-center py-10 text-slate-400">
                  Tidak ada permohonan yang memenuhi syarat pengarsipan untuk filter ini.
                </td>
              </tr>
              <tr
                v-for="app in applications.data"
                :key="app.id"
                class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition"
                :class="{ 'bg-blue-50/40 dark:bg-blue-950/20': selectedIds.includes(app.id) }"
              >
                <td class="py-3.5 px-3">
                  <input
                    type="checkbox"
                    :value="app.id"
                    v-model="selectedIds"
                    class="w-4 h-4 rounded text-blue-600 border-slate-300 dark:border-slate-600 focus:ring-blue-500 cursor-pointer"
                  />
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                  {{ app.reg_number }}
                </td>
                <td class="py-3.5 px-4">
                  <p class="font-bold text-slate-800 dark:text-slate-200">{{ app.applicant_name }}</p>
                  <p class="text-[11px] font-mono text-slate-400">{{ app.masked_nik }}</p>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-600 dark:text-slate-300">
                  {{ app.service_type?.name || 'KTP-el' }}
                </td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                  <div class="space-y-0.5">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="(app.status === 'READY_FOR_PICKUP' || app.handover?.status === 'SIAP_DIAMBIL') ? 'bg-violet-100 text-violet-800 dark:bg-violet-950/60 dark:text-violet-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'"
                    >
                      {{ (app.status === 'READY_FOR_PICKUP' || app.handover?.status === 'SIAP_DIAMBIL') ? 'KTP Siap Diambil' : 'KTP Diserahkan' }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-mono">
                      {{ (app.handover?.ready_marked_at || app.completed_at || app.updated_at) ? new Date(app.handover?.ready_marked_at || app.completed_at || app.updated_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-' }}
                    </p>
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <span
                    v-if="app.is_archive_ready"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 inline-flex items-center gap-1"
                  >
                    ✓ Lengkap & Terbaca di Storage
                  </span>
                  <div v-else class="space-y-1">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 inline-flex items-center gap-1">
                      ⚠ Berkas Belum Lengkap (Tetap Dapat Diarsipkan)
                    </span>
                    <ul class="text-[10px] text-amber-700 dark:text-amber-400 list-disc list-inside">
                      <li v-for="(iss, idx) in app.archive_issues" :key="idx">{{ iss }}</li>
                    </ul>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                  <span class="font-bold">{{ app.valid_doc_count }} berkas</span>
                  <span class="text-slate-400 block text-[11px]">({{ formatBytes(app.total_doc_size_bytes) }})</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="applications.links && applications.links.length > 3" class="flex flex-col sm:flex-row items-center justify-end pt-4 border-t border-slate-100 dark:border-slate-700">
          <div class="w-full sm:w-auto flex items-center justify-start sm:justify-end gap-1 overflow-x-auto max-w-full pb-1 sm:pb-0 scrollbar-thin select-none touch-pan-x">
            <template v-for="(link, i) in applications.links" :key="i">
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

    <!-- Create Package Confirmation Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-white">Buat Paket Arsip ZIP</h3>
              <p class="text-xs text-slate-500">Kemas berkas permohonan ke dalam arsip digital</p>
            </div>
          </div>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-4 text-xs space-y-2 border border-slate-100 dark:border-slate-700">
          <div class="flex justify-between">
            <span class="text-slate-500">Metode Pemilihan:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ isPeriodMode ? 'Berdasarkan Filter Periode' : 'Pilihan Seleksi Checkbox' }}</span>
          </div>
          <div v-if="!isPeriodMode" class="flex justify-between">
            <span class="text-slate-500">Jumlah Permohonan Terpilih:</span>
            <span class="font-bold text-blue-600 dark:text-blue-400">{{ selectedIds.length }} permohonan</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Periode Arsip:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ formatPeriodWithDates(filterForm.period || currentYearMonth) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Mode Operasional:</span>
            <span class="font-bold text-emerald-600 dark:text-emerald-400">ARSIP_SAJA (File sumber tetap aman di hosting)</span>
          </div>
        </div>

        <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-2xl border border-blue-200 dark:border-blue-900/60 text-xs text-blue-800 dark:text-blue-300 flex items-start gap-2.5">
          <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p>
            Sistem akan menyusun struktur folder <code class="font-mono font-bold">permohonan/REG-XXXX/</code>, menyalin seluruh berkas dokumen persyaratan, menghitung hash SHA-256 tiap file, serta menghasilkan <code class="font-mono font-bold">manifest.json</code> dan <code class="font-mono font-bold">daftar_arsip.csv</code>.
          </p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button
            type="button"
            @click="showModal = false"
            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submitCreatePackage"
            :disabled="isSubmitting"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <span v-if="isSubmitting">Sedang Mengemas ZIP...</span>
            <span v-else>Konfirmasi & Buat ZIP Sekarang</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  applications: Object,
  filters: Object,
  serviceTypes: Array,
  availablePeriods: Object,
});

const filterForm = ref({
  period: props.filters.period || '',
  service_type_id: props.filters.service_type_id || '',
  status: props.filters.status || '',
  search: props.filters.search || '',
});

const selectedIds = ref([]);
const showModal = ref(false);
const isPeriodMode = ref(false);
const isSubmitting = ref(false);

const currentYearMonth = computed(() => {
  const d = new Date();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  return `${d.getFullYear()}-${m}`;
});

const allAppsOnPage = computed(() => {
  return props.applications.data || [];
});

const isAllSelected = computed(() => {
  if (allAppsOnPage.value.length === 0) return false;
  return allAppsOnPage.value.every(app => selectedIds.value.includes(app.id));
});

function toggleSelectAll(e) {
  if (e.target.checked) {
    selectedIds.value = allAppsOnPage.value.map(app => app.id);
  } else {
    selectedIds.value = [];
  }
}

function applyFilter() {
  router.get(route('admin.archive.ready-applications'), filterForm.value, {
    preserveState: true,
    preserveScroll: true,
  });
}

function resetFilter() {
  filterForm.value = { period: '', service_type_id: '', status: '', search: '' };
  applyFilter();
}

function openCreateModal(periodMode = false) {
  isPeriodMode.value = periodMode;
  showModal.value = true;
}

function submitCreatePackage() {
  isSubmitting.value = true;

  const payload = {
    period: filterForm.value.period || currentYearMonth.value,
    service_type_id: filterForm.value.service_type_id || null,
  };

  if (!isPeriodMode.value) {
    payload.application_ids = selectedIds.value;
  }

  router.post(route('admin.archive.packages.store'), payload, {
    onFinish: () => {
      isSubmitting.value = false;
      showModal.value = false;
      selectedIds.value = [];
    },
  });
}

function formatPeriodWithDates(periodStr) {
  if (!periodStr) return '-';
  const parts = periodStr.split('-');
  if (parts.length === 2) {
    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const months = [
      '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const monthName = months[month] || periodStr;
    const lastDay = new Date(year, month, 0).getDate();
    return `${monthName} ${year} (01 - ${String(lastDay).padStart(2, '0')} ${monthName.substring(0, 3)} ${year})`;
  }
  return periodStr;
}

function formatBytes(bytes) {
  if (!bytes) return '0 B';
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(2) + ' MB';
}
</script>
