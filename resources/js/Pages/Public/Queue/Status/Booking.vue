<template>
  <Head title="Pilih Jadwal & Ambil Antrean - Disdukcapil Kab. Dompu" />

  <PublicLayout>
    <div class="py-8 sm:py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Top Breadcrumb / Navigation -->
      <div class="mb-6 flex items-center justify-between">
        <Link
          :href="route('queue.status', { token: application.access_token })"
          class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali ke Status Pendaftaran
        </Link>
        <span class="text-xs text-slate-400">Tahap 3 dari 4: Penjadwalan</span>
      </div>

      <!-- Applicant Pre-Verified Banner -->
      <div class="mb-8 bg-gradient-to-r from-emerald-800 to-teal-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-emerald-600/40">
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-400/20 text-emerald-200 border border-emerald-400/30 mb-3">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              Pra-Verifikasi Dokumen Disetujui
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
              Pilih Jadwal Kedatangan ke Kantor
            </h1>
            <p class="text-xs sm:text-sm text-emerald-100 mt-2 max-w-xl leading-relaxed">
              Selamat! Dokumen persyaratan digital Anda telah diverifikasi oleh petugas. Silakan pilih hari dan sesi pelayanan untuk mendapatkan <strong>Nomor Antrean</strong> ke Kantor Disdukcapil Kabupaten Dompu.
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-emerald-200">
              <span>No. Registrasi: <strong class="text-white font-mono font-bold">{{ application.reg_number }}</strong></span>
              <span>·</span>
              <span>Layanan: <strong class="text-white">{{ application.service_type?.name }}</strong></span>
              <span>·</span>
              <span>Pemohon: <strong class="text-white">{{ application.applicant_name }}</strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Notification -->
      <div
        v-if="$page.props.flash?.error || errorMessage"
        class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-200 flex items-start gap-3"
      >
        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="font-medium leading-relaxed">{{ $page.props.flash?.error || errorMessage }}</p>
      </div>

      <!-- Schedule Selection Area -->
      <div v-if="schedules && schedules.length > 0" class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-md">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-lg font-black text-slate-900 dark:text-white">
                Jadwal & Kuota Pelayanan Tersedia
              </h2>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Pilih tanggal kedatangan, lalu klik salah satu sesi jam yang kuotanya masih tersedia.
              </p>
            </div>
          </div>

          <!-- Schedule Days Cards -->
          <div class="space-y-6">
            <div
              v-for="sched in schedules"
              :key="sched.id"
              class="border border-slate-200 dark:border-slate-700 rounded-2xl p-5 bg-slate-50/50 dark:bg-slate-900/30"
            >
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex flex-col items-center justify-center shrink-0">
                    <span class="text-[10px] font-bold uppercase leading-none">{{ getDayName(sched.schedule_date) }}</span>
                    <span class="text-sm font-black leading-none mt-0.5">{{ getDayNumber(sched.schedule_date) }}</span>
                  </div>
                  <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                      {{ sched.schedule_date_formatted || formatFullDate(sched.schedule_date) }}
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                      Jam Operasional: {{ sched.operational_hours_formatted || (formatTime(sched.open_time) + ' - ' + formatTime(sched.close_time) + ' WITA') }}
                    </p>
                  </div>
                </div>

                <div class="text-[11px] font-medium text-slate-500">
                  Total Kuota Layanan: <span class="font-bold text-slate-800 dark:text-slate-200">{{ sched.total_quota }} Pemohon</span>
                </div>
              </div>

              <!-- Slots within schedule -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <button
                  v-for="slot in sched.slots"
                  :key="slot.id"
                  type="button"
                  :disabled="slot.booked_count >= slot.quota"
                  @click="selectSlot(sched, slot)"
                  :class="[
                    'p-4 rounded-xl border text-left transition relative cursor-pointer',
                    selectedSlot?.id === slot.id
                      ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/60 ring-2 ring-blue-500/20'
                      : slot.booked_count >= slot.quota
                      ? 'border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/40 opacity-60 cursor-not-allowed'
                      : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-300 dark:hover:border-blue-700'
                  ]"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-900 dark:text-white">
                      {{ slot.name || 'Sesi Pelayanan' }}
                    </span>
                    <span
                      :class="[
                        'text-[10px] font-bold px-2 py-0.5 rounded-full',
                        slot.booked_count >= slot.quota
                          ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300'
                          : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300'
                      ]"
                    >
                      {{ slot.booked_count >= slot.quota ? 'Penuh' : `Sisa ${slot.quota - slot.booked_count} Slot` }}
                    </span>
                  </div>

                  <p class="text-xs font-mono font-semibold text-blue-600 dark:text-blue-400">
                    {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }} WITA
                  </p>

                  <div class="mt-3 w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                    <div
                      class="bg-blue-600 h-full rounded-full transition-all"
                      :style="{ width: `${Math.min(100, (slot.booked_count / slot.quota) * 100)}%` }"
                    ></div>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Selected Slot Confirmation Card -->
        <div
          v-if="selectedSlot"
          class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border-2 border-blue-600 dark:border-blue-500 shadow-xl"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
              <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider block mb-1">
                Jadwal Pilihan Anda:
              </span>
              <h3 class="text-xl font-black text-slate-900 dark:text-white">
                {{ formatFullDate(selectedSchedule.schedule_date) }}
              </h3>
              <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-1">
                {{ selectedSlot.name }} ({{ formatTime(selectedSlot.start_time) }} - {{ formatTime(selectedSlot.end_time) }} WITA)
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                Harap datang 15 menit sebelum sesi dimulai dengan membawa seluruh dokumen asli untuk verifikasi di loket.
              </p>
            </div>

            <button
              type="button"
              @click="handleConfirmBooking"
              :disabled="isSubmitting"
              class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-xl shadow-blue-600/30 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
            >
              <span v-if="isSubmitting">Menerbitkan Antrean...</span>
              <span v-else>Konfirmasi & Ambil Antrean &rarr;</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State if No Schedules Available -->
      <div v-else class="bg-white dark:bg-slate-800 rounded-3xl p-12 text-center border border-slate-200 dark:border-slate-700 shadow-md">
        <div class="w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="text-lg font-black text-slate-900 dark:text-white">
          Belum Ada Kuota Antrean Terbuka
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 max-w-md mx-auto leading-relaxed">
          Saat ini jadwal antrean untuk jenis layanan ini belum dibuka atau kuota hari pelayanan penuh. Harap pantau kembali secara berkala atau hubungi layanan bantuan kami.
        </p>
        <div class="mt-6">
          <Link
            :href="route('queue.status', { token: application.access_token })"
            class="px-6 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-200 transition"
          >
            Kembali ke Status
          </Link>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
  schedules: {
    type: Array,
    default: () => [],
  },
});

const selectedSchedule = ref(null);
const selectedSlot = ref(null);
const isSubmitting = ref(false);
const errorMessage = ref(null);

function selectSlot(sched, slot) {
  selectedSchedule.value = sched;
  selectedSlot.value = slot;
  errorMessage.value = null;
}

function handleConfirmBooking() {
  if (!selectedSlot.value) {
    errorMessage.value = 'Silakan pilih sesi jadwal antrean terlebih dahulu.';
    return;
  }

  isSubmitting.value = true;
  router.post(
    route('queue.booking.store', { token: props.application.access_token }),
    { slot_id: selectedSlot.value.id },
    {
      onError: (errors) => {
        isSubmitting.value = false;
        errorMessage.value = Object.values(errors)[0] || 'Gagal memproses pemesanan antrean.';
      },
    }
  );
}

const MONTH_NAMES_ID = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
const DAY_NAMES_ID = [
  'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
];

function parseDateSafe(dateString) {
  if (!dateString) return null;
  if (typeof dateString === 'string' && dateString.includes('T')) {
    const cleanDate = dateString.split('T')[0];
    const [y, m, day] = cleanDate.split('-').map(Number);
    return new Date(y, m - 1, day);
  }
  const clean = typeof dateString === 'string' ? dateString.split(' ')[0].trim() : String(dateString);
  if (/^\d{4}-\d{2}-\d{2}$/.test(clean)) {
    const [y, m, day] = clean.split('-').map(Number);
    return new Date(y, m - 1, day);
  }
  const fallback = new Date(dateString);
  return isNaN(fallback.getTime()) ? null : fallback;
}

function formatFullDate(dateStr) {
  if (!dateStr) return '';
  const d = parseDateSafe(dateStr);
  if (!d) return String(dateStr);
  const dayName = DAY_NAMES_ID[d.getDay()];
  const dayNum = String(d.getDate()).padStart(2, '0');
  const monthName = MONTH_NAMES_ID[d.getMonth()];
  const year = d.getFullYear();
  return `${dayName}, ${dayNum} ${monthName} ${year}`;
}

function getDayName(dateStr) {
  if (!dateStr) return '';
  const d = parseDateSafe(dateStr);
  if (!d) return '';
  return DAY_NAMES_ID[d.getDay()].substring(0, 3);
}

function getDayNumber(dateStr) {
  if (!dateStr) return '';
  const d = parseDateSafe(dateStr);
  if (!d) return '';
  return d.getDate();
}

function formatTime(timeStr) {
  if (!timeStr) return '';
  return timeStr.substring(0, 5);
}
</script>
