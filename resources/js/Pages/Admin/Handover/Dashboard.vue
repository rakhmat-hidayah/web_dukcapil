<template>
  <Head title="Dashboard Serah Terima KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-sky-900/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-500/20 text-sky-300 border border-sky-400/30 mb-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Sistem Pengambilan & Serah Terima Fisik
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Serah Terima KTP-el
            </h1>
            <p class="text-xs sm:text-sm text-sky-200 mt-1 max-w-xl">
              Modul pencatatan kesiapan fisik kartu, verifikasi identitas penerima aktual, dan dokumentasi penyerahan KTP-el dengan bukti foto kamera HP.
            </p>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex flex-wrap items-center gap-2">
            <Link
              :href="route('admin.handover.ready')"
              class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 transition flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Siap Diambil ({{ stats.count_ready }})
            </Link>
            <Link
              :href="route('admin.handover.pending')"
              class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/30 transition flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Periksa Fisik Kartu ({{ stats.count_pending }})
            </Link>
          </div>
        </div>
      </div>

      <!-- Quick Barcode / QR Code Scanner Bar for Counter Officer -->
      <div class="bg-gradient-to-r from-blue-900 via-indigo-950 to-slate-900 rounded-3xl p-5 text-white shadow-lg border border-blue-800/60">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/20 text-blue-300 border border-blue-400/30 flex items-center justify-center shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="flex h-2 w-2 relative">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <h3 class="font-bold text-white text-sm">Pindai QR Code Bukti Pendaftaran Pemohon</h3>
              </div>
              <p class="text-xs text-blue-200 mt-0.5">Arahkan scanner ke QR Code pemohon untuk langsung membuka transaksi serah terima KTP-el</p>
            </div>
          </div>

          <form @submit.prevent="handleQuickScan" class="w-full md:w-auto flex items-center gap-2">
            <div class="relative w-full md:w-80">
              <input
                v-model="scanInput"
                type="text"
                placeholder="Scan QR atau ketik REG-..."
                class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-blue-300/60 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
              <svg class="w-4 h-4 text-blue-300 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button
              type="submit"
              class="px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-md shadow-blue-600/30 shrink-0 cursor-pointer"
            >
              Scan & Buka &rarr;
            </button>
          </form>
        </div>
      </div>

      <!-- 5 Metric Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Menunggu Ketersediaan -->
        <Link
          :href="route('admin.handover.pending')"
          class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-amber-400 transition group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Menunggu Fisik</span>
            <div class="w-9 h-9 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.count_pending }}</p>
          <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-semibold group-hover:underline">
            Perlu dicek ketersediaan &rarr;
          </p>
        </Link>

        <!-- 2. Siap Diambil -->
        <Link
          :href="route('admin.handover.ready')"
          class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-blue-400 transition group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Siap Diambil</span>
            <div class="w-9 h-9 rounded-2xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ stats.count_ready }}</p>
          <p class="text-[11px] text-blue-600 dark:text-blue-400 mt-1 font-semibold group-hover:underline">
            Kartu fisik tersedia di loket &rarr;
          </p>
        </Link>

        <!-- 3. Diserahkan Hari Ini -->
        <Link
          :href="route('admin.handover.history', { date: todayDateString })"
          class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-emerald-400 transition group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Diserahkan Hari Ini</span>
            <div class="w-9 h-9 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ stats.count_today_handed_over }}</p>
          <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-semibold group-hover:underline">
            Selesai diserahkan hari ini &rarr;
          </p>
        </Link>

        <!-- 4. Total Bulan Ini -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Bulan Ini</span>
            <div class="w-9 h-9 rounded-2xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ stats.count_month_handed_over }}</p>
          <p class="text-[11px] text-slate-400 mt-1 font-medium">
            Akumulasi serah terima KTP-el
          </p>
        </div>

        <!-- 5. Kendala Pengambilan -->
        <Link
          :href="route('admin.handover.exceptions')"
          class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-rose-400 transition group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Kendala</span>
            <div class="w-9 h-9 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-rose-600 dark:text-rose-400">{{ stats.count_exception }}</p>
          <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold group-hover:underline">
            {{ stats.count_exception > 0 ? 'Perlu tindak lanjut' : 'Tidak ada kendala' }} &rarr;
          </p>
        </Link>
      </div>

      <!-- Trend Chart & Quick Overview Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- 7-Day Trend (8 Cols) -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white text-base">
                Tren Penyerahan 7 Hari Terakhir
              </h3>
              <p class="text-xs text-slate-400">Jumlah fisik KTP-el yang selesai diserahkan per hari (Waktu Server WITA)</p>
            </div>
            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
              Data Aktual Database
            </span>
          </div>

          <div class="pt-4 grid grid-cols-7 gap-2 sm:gap-4 items-end h-48 border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <div
              v-for="item in stats.weekly_trend"
              :key="item.date"
              class="flex flex-col items-center justify-end h-full group"
            >
              <span class="text-[11px] font-black text-slate-700 dark:text-slate-300 mb-1 opacity-80 group-hover:opacity-100">
                {{ item.total }}
              </span>
              <div class="w-full max-w-[42px] bg-slate-100 dark:bg-slate-700/50 rounded-xl h-36 flex items-end overflow-hidden p-0.5">
                <div
                  class="w-full bg-gradient-to-t from-emerald-600 to-teal-400 rounded-lg transition-all duration-500 group-hover:from-emerald-500 group-hover:to-teal-300"
                  :style="{ height: `${Math.max(item.total > 0 ? 12 : 2, Math.min(100, (item.total / maxTrendTotal) * 100))}%` }"
                ></div>
              </div>
              <span class="text-[10px] font-semibold text-slate-400 mt-2 text-center truncate max-w-full">
                {{ item.label.split(',')[0] }}
              </span>
            </div>
          </div>
        </div>

        <!-- Right: SOP Guidelines & Principles (4 Cols) -->
        <div class="lg:col-span-4 bg-gradient-to-br from-slate-900 to-indigo-950 rounded-3xl p-6 text-white border border-indigo-900/50 shadow-sm space-y-4">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
            SOP Serah Terima KTP-el
          </div>
          <h3 class="text-base font-bold text-white">Ketentuan Pengambilan</h3>
          <ul class="space-y-3 text-xs text-indigo-200/90 leading-relaxed">
            <li class="flex items-start gap-2">
              <span class="w-5 h-5 rounded-full bg-indigo-500/30 text-indigo-300 flex items-center justify-center shrink-0 font-bold text-[10px]">1</span>
              <span><strong>Verifikasi Fisik Kartu:</strong> Pastikan fisik kartu KTP-el ada dan sesuai data sebelum menandai Siap Diambil.</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="w-5 h-5 rounded-full bg-indigo-500/30 text-indigo-300 flex items-center justify-center shrink-0 font-bold text-[10px]">2</span>
              <span><strong>Penerima Aktual:</strong> Catat nama, NIK, dan hubungan penerima jika diwakilkan (wajib sesuai SOP).</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="w-5 h-5 rounded-full bg-indigo-500/30 text-indigo-300 flex items-center justify-center shrink-0 font-bold text-[10px]">3</span>
              <span><strong>Foto Bukti Kamera:</strong> Wajib mengambil foto bukti fisik penerima memegang KTP-el melalui kamera browser HP.</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Recent Handovers Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base">
              Penyerahan KTP-el Terbaru
            </h3>
            <p class="text-xs text-slate-400">Transaksi pengambilan KTP-el yang baru saja diselesaikan</p>
          </div>
          <Link
            :href="route('admin.handover.history')"
            class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline"
          >
            Lihat Semua Riwayat &rarr;
          </Link>
        </div>

        <div v-if="stats.recent_handovers.length === 0" class="py-12 text-center text-slate-400 text-xs">
          Belum ada riwayat transaksi penyerahan KTP-el.
        </div>

        <div v-else class="divide-y divide-slate-100 dark:divide-slate-700/60 overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <th class="pb-3">No. Referensi</th>
                <th class="pb-3">Pemilik KTP-el</th>
                <th class="pb-3">Penerima Aktual</th>
                <th class="pb-3">Hubungan</th>
                <th class="pb-3">Layanan</th>
                <th class="pb-3">Waktu Penyerahan</th>
                <th class="pb-3">Petugas</th>
                <th class="pb-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <tr v-for="item in stats.recent_handovers" :key="item.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-750">
                <td class="py-3 font-mono font-bold text-blue-600 dark:text-blue-400">
                  {{ item.application?.reg_number }}
                </td>
                <td class="py-3 font-semibold text-slate-900 dark:text-white">
                  {{ item.application?.applicant_name }}
                  <span class="block text-[11px] text-slate-400 font-mono">NIK: {{ item.application?.masked_nik }}</span>
                </td>
                <td class="py-3 font-medium text-slate-800 dark:text-slate-200">
                  {{ item.actual_receiver_name || item.application?.applicant_name }}
                </td>
                <td class="py-3">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                    {{ item.receiver_relationship || 'Sendiri' }}
                  </span>
                </td>
                <td class="py-3 text-slate-600 dark:text-slate-400">
                  {{ item.application?.service_type?.name }}
                </td>
                <td class="py-3 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                  {{ item.handover_at_formatted }}
                </td>
                <td class="py-3 text-slate-600 dark:text-slate-400">
                  {{ item.handover_user?.name || '-' }}
                </td>
                <td class="py-3 text-right">
                  <Link
                    :href="route('admin.handover.show', item.id)"
                    class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold transition"
                  >
                    Detail
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
});

const scanInput = ref('');

function extractRegCode(str) {
  if (!str) return '';
  const match = str.match(/REG-[A-Za-z0-9-]+/i);
  return match ? match[0].toUpperCase() : str.trim();
}

function handleQuickScan() {
  const code = extractRegCode(scanInput.value);
  if (!code) return;
  router.get(route('admin.handover.scan'), { code });
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
        scanInput.value = clean;
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

const todayDateString = computed(() => {
  const d = new Date();
  return d.toISOString().split('T')[0];
});

const maxTrendTotal = computed(() => {
  const totals = props.stats.weekly_trend?.map(t => t.total) || [1];
  return Math.max(...totals, 5);
});
</script>
