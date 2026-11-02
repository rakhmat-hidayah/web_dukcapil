<template>
  <Head title="Menunggu Ketersediaan KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-300 dark:border-amber-800 mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Tahap A: Pemeriksaan Ketersediaan Fisik Kartu
          </div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Menunggu Ketersediaan KTP-el
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Daftar permohonan yang berkas administrasinya telah diverifikasi. Petugas memeriksa fisik kartu KTP-el, lalu klik <strong>Tandai Siap Diambil</strong> jika kartu fisik benar-benar telah tersedia.
          </p>
        </div>

        <Link
          :href="route('admin.handover.ready')"
          class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition flex items-center gap-2 self-start sm:self-auto"
        >
          Lihat Daftar Siap Diambil &rarr;
        </Link>
      </div>

      <!-- Quick Search & Barcode/QR Scanner Bar -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
        <div class="flex items-center justify-between gap-3 text-[11px] pb-1 border-b border-slate-100 dark:border-slate-700">
          <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
            <span class="flex h-2.5 w-2.5 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
            </span>
            <span class="font-bold text-slate-800 dark:text-slate-200">Scanner QR Code Siap</span>
            <span class="text-slate-400">· Arahkan perangkat scanner langsung ke QR Code pada bukti pendaftaran pemohon</span>
          </div>
          <span class="text-slate-400 font-mono text-[10px] hidden sm:inline">Auto-Direct Scan ON</span>
        </div>

        <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row items-center gap-3">
          <div class="relative flex-1 w-full">
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              type="text"
              placeholder="Scan QR Code dengan perangkat scanner atau ketik No. Registrasi / Nama / NIK..."
              class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-inner font-mono"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
          </div>
          <button
            type="submit"
            class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition shadow-md shadow-amber-600/20 flex items-center justify-center gap-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span>Cari / Scan</span>
          </button>
          <button
            v-if="filters.search"
            type="button"
            @click="resetSearch"
            class="w-full sm:w-auto px-4 py-3 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 transition cursor-pointer"
          >
            Reset
          </button>
        </form>
      </div>

      <!-- Data Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div v-if="handovers.data.length === 0" class="py-16 text-center text-slate-400 text-xs space-y-2">
          <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <p class="font-bold text-sm text-slate-600 dark:text-slate-300">Tidak ada permohonan yang menunggu ketersediaan kartu.</p>
          <p class="text-slate-400">Semua permohonan yang lolos pra-verifikasi telah ditandai siap diambil atau belum ada data yang cocok.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[10px] uppercase font-bold text-slate-400 tracking-wider border-b border-slate-200 dark:border-slate-700">
                <th class="pb-3">No. Referensi</th>
                <th class="pb-3">Pemilik KTP-el</th>
                <th class="pb-3">Jenis Permohonan</th>
                <th class="pb-3">Tgl. Pengajuan</th>
                <th class="pb-3">Status Ketersediaan</th>
                <th class="pb-3 text-right">Aksi Petugas</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <tr v-for="item in handovers.data" :key="item.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-750 transition">
                <td class="py-4 font-mono font-bold text-blue-600 dark:text-blue-400">
                  {{ item.application?.reg_number }}
                </td>
                <td class="py-4 font-semibold text-slate-900 dark:text-white">
                  {{ item.application?.applicant_name }}
                  <span class="block text-[11px] text-slate-400 font-mono">NIK: {{ item.application?.masked_nik }}</span>
                </td>
                <td class="py-4 text-slate-700 dark:text-slate-300">
                  {{ item.application?.service_type?.name }}
                  <span v-if="item.application?.attendance_type === 'representative'" class="block text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                    (Dikuasakan ke: {{ item.application?.representative_name }})
                  </span>
                </td>
                <td class="py-4 text-slate-500 font-mono text-[11px]">
                  {{ formatDate(item.application?.created_at) }}
                </td>
                <td class="py-4">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Menunggu Kartu Fisik
                  </span>
                  <span v-if="item.ready_notes" class="block text-[10px] text-slate-500 dark:text-slate-400 mt-1 italic max-w-[220px] truncate" :title="item.ready_notes">
                    Catatan: {{ item.ready_notes }}
                  </span>
                </td>
                <td class="py-4 text-right">
                  <div class="inline-flex items-center gap-2">
                    <button
                      type="button"
                      @click="openMarkReadyModal(item)"
                      class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                      Tandai Siap Diambil
                    </button>
                    <Link
                      :href="route('admin.handover.show', item.id)"
                      class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs transition"
                    >
                      Detail
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="handovers.links?.length > 3" class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
          <p class="text-xs text-slate-400">
            Menampilkan {{ handovers.from || 0 }} - {{ handovers.to || 0 }} dari total {{ handovers.total }} permohonan
          </p>
          <div class="flex items-center gap-1">
            <Link
              v-for="(link, idx) in handovers.links"
              :key="idx"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold transition"
              :class="[
                link.active
                  ? 'bg-amber-600 text-white font-bold'
                  : link.url
                  ? 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'
                  : 'text-slate-300 dark:text-slate-600 pointer-events-none'
              ]"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Tandai Siap Diambil -->
    <div
      v-if="activeModalItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    >
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-700 space-y-5 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          </div>
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white">
              Konfirmasi Kartu Fisik Tersedia
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Pastikan Anda telah memegang dan memeriksa fisik kartu KTP-el ini.
            </p>
          </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 text-xs space-y-2">
          <div class="flex justify-between">
            <span class="text-slate-400">No. Registrasi:</span>
            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ activeModalItem.application?.reg_number }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Nama Pemilik KTP:</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ activeModalItem.application?.applicant_name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Jenis Layanan:</span>
            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ activeModalItem.application?.service_type?.name }}</span>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
            Catatan Pemeriksaan Fisik (Opsional):
          </label>
          <textarea
            v-model="readyNotes"
            rows="2"
            placeholder="Contoh: Kartu fisik mulus, NIK dan foto sesuai blangko siap di rak 01..."
            class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button
            type="button"
            @click="activeModalItem = null"
            :disabled="isSubmitting"
            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submitMarkReady"
            :disabled="isSubmitting"
            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <span v-if="isSubmitting">Memproses...</span>
            <span v-else>Ya, Kartu Sudah Siap &rarr;</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  handovers: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '' }),
  },
});

const searchQuery = ref(props.filters.search || '');
const searchInputRef = ref(null);
const activeModalItem = ref(null);
const readyNotes = ref('');
const isSubmitting = ref(false);

function extractRegCode(str) {
  if (!str) return '';
  const match = str.match(/REG-[A-Za-z0-9-]+/i);
  return match ? match[0].toUpperCase() : str.trim();
}

function handleSearch() {
  const clean = extractRegCode(searchQuery.value);
  if (clean && clean.startsWith('REG-')) {
    // Hardware scanner or direct reg number -> Direct fast lookup
    router.get(route('admin.handover.scan'), { code: clean });
    return;
  }
  router.get(route('admin.handover.pending'), { search: searchQuery.value }, { preserveState: true, replace: true });
}

function resetSearch() {
  searchQuery.value = '';
  router.get(route('admin.handover.pending'), {}, { preserveState: true, replace: true });
}

// Global Hardware Barcode / QR Scanner Keydown Buffer
let barcodeBuffer = '';
let lastKeyTime = Date.now();

function handleGlobalKeydown(e) {
  const tag = (e.target.tagName || '').toLowerCase();
  if (tag === 'textarea') return;

  const now = Date.now();
  if (now - lastKeyTime > 250) {
    barcodeBuffer = '';
  }
  lastKeyTime = now;

  if (e.key === 'Enter') {
    if (barcodeBuffer.length >= 6) {
      const clean = extractRegCode(barcodeBuffer);
      if (clean && clean.startsWith('REG-')) {
        e.preventDefault();
        searchQuery.value = clean;
        router.get(route('admin.handover.scan'), { code: clean });
        barcodeBuffer = '';
        return;
      }
    }
    barcodeBuffer = '';
  } else if (e.key && e.key.length === 1) {
    barcodeBuffer += e.key;
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleGlobalKeydown);
});

function openMarkReadyModal(item) {
  activeModalItem.value = item;
  readyNotes.value = '';
}

function submitMarkReady() {
  if (!activeModalItem.value) return;

  isSubmitting.value = true;
  router.post(
    route('admin.handover.mark-ready', activeModalItem.value.id),
    { notes: readyNotes.value },
    {
      onFinish: () => {
        isSubmitting.value = false;
        activeModalItem.value = null;
      },
    }
  );
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Asia/Makassar' });
}
</script>
