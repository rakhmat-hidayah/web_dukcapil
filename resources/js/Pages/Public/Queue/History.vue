<template>
  <Head title="Riwayat Permohonan KTP-el Saya - Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="py-8 sm:py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
      <!-- Breadcrumb & Back navigation -->
      <div class="flex items-center justify-between gap-4">
        <Link
          :href="route('queue.index')"
          class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-blue-600 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali ke Portal Antrian
        </Link>

        <Link
          :href="route('queue.apply.create')"
          class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300 dark:hover:bg-blue-900/60 transition"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          Ajukan Permohonan Baru
        </Link>
      </div>

      <!-- STATE A: NOT VERIFIED YET (Verification Challenge Form) -->
      <div v-if="!isVerified" class="max-w-xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-10 border border-slate-200 dark:border-slate-700 shadow-xl space-y-6">
          <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-inner">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">
              Riwayat Permohonan KTP-el Saya
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed max-w-md mx-auto">
              Temukan kembali seluruh Nomor Registrasi dan Nomor Antrean Anda tanpa perlu mencatat manual. Masukkan NIK dan nomor kontak yang Anda daftarkan.
            </p>
          </div>

          <form @submit.prevent="submitVerify" class="space-y-4 pt-2">
            <!-- NIK Input -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Nomor Induk Kependudukan (NIK Pemohon)
              </label>
              <div class="relative">
                <input
                  v-model="verifyForm.nik"
                  type="text"
                  maxlength="16"
                  inputmode="numeric"
                  placeholder="16 digit NIK sesuai KTP/KK..."
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm font-mono text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>
              <p v-if="verifyForm.errors.nik" class="text-xs text-rose-500 font-medium">
                {{ verifyForm.errors.nik }}
              </p>
            </div>

            <!-- WhatsApp / Phone Input -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Nomor WhatsApp / HP Terdaftar
              </label>
              <div class="relative">
                <input
                  v-model="verifyForm.phone"
                  type="tel"
                  placeholder="Contoh: 082340001234..."
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>
              <p class="text-[11px] text-slate-400">
                Gunakan nomor kontak yang Anda masukkan saat mengajukan permohonan pertama kali.
              </p>
              <p v-if="verifyForm.errors.phone" class="text-xs text-rose-500 font-medium">
                {{ verifyForm.errors.phone }}
              </p>
            </div>

            <!-- Remember on Device Option -->
            <div class="flex items-center gap-2.5 pt-1">
              <input
                id="remember_device"
                v-model="verifyForm.remember"
                type="checkbox"
                class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 dark:bg-slate-900"
              />
              <label for="remember_device" class="text-xs text-slate-600 dark:text-slate-400 select-none cursor-pointer">
                Ingat akses di perangkat pribadi ini selama 30 hari
              </label>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="verifyForm.processing"
              class="w-full mt-4 py-3.5 px-6 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg shadow-blue-600/30 transition disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <svg v-if="verifyForm.processing" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              <span>{{ verifyForm.processing ? 'Memeriksa Data...' : 'Buka Riwayat Permohonan Saya' }}</span>
              <svg v-if="!verifyForm.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </form>

          <!-- Privacy Guarantee Notice -->
          <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-start gap-2.5">
            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <p>
              <strong>Keamanan Terjamin:</strong> Informasi sensitif NIK Anda disamarkan. Dokumen pribadi hanya dapat diakses melalui verifikasi ganda yang sah.
            </p>
          </div>
        </div>
      </div>

      <!-- STATE B: VERIFIED (Application History List) -->
      <div v-else class="space-y-6">
        <!-- Applicant Verified Header Card -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="space-y-1">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              Identitas Pemohon Terverifikasi
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white pt-1">
              {{ applicantName }}
            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
              <span>NIK Terdaftar: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ maskedNik }}</strong></span>
              <span>·</span>
              <span>Total: <strong>{{ applications.length }} Permohonan</strong></span>
            </p>
          </div>

          <div class="flex items-center gap-3">
            <Link
              :href="route('queue.apply.create')"
              class="px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-md hover:scale-105"
            >
              + Pengajuan Baru
            </Link>

            <button
              type="button"
              @click="logoutSession"
              class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs transition"
              title="Tutup sesi di perangkat ini"
            >
              Tutup Sesi
            </button>
          </div>
        </div>

        <!-- Applications List -->
        <div v-if="applications.length > 0" class="space-y-4">
          <div
            v-for="app in applications"
            :key="app.id"
            class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 sm:p-7 shadow-md hover:shadow-xl transition-all duration-200 relative overflow-hidden"
          >
            <!-- Colored accent on left -->
            <div
              class="absolute top-0 bottom-0 left-0 w-2"
              :style="{ backgroundColor: app.service_color || '#2563eb' }"
            ></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pl-2">
              <!-- Left: Meta & Numbers -->
              <div class="space-y-3 flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                  <span class="text-xs font-semibold text-slate-400">
                    {{ app.created_at_formatted }}
                  </span>
                  <span>·</span>
                  <StatusBadge :status="app.status" size="sm" />
                  <span v-if="app.is_represented" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                    {{ app.attendance_label }}
                  </span>
                </div>

                <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                  {{ app.service_name }}
                </h3>

                <!-- Nomor Registrasi & Nomor Antrean Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                  <!-- Nomor Registrasi -->
                  <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3">
                    <div>
                      <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                        Nomor Registrasi
                      </p>
                      <p class="text-base font-black font-mono text-slate-800 dark:text-slate-100 mt-0.5">
                        {{ app.reg_number }}
                      </p>
                    </div>
                    <button
                      type="button"
                      @click="copyText(app.reg_number, 'Nomor Registrasi')"
                      class="px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-blue-600 text-[11px] font-bold transition flex items-center gap-1 shadow-xs"
                      title="Salin Nomor Registrasi"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                      <span>Salin</span>
                    </button>
                  </div>

                  <!-- Nomor Antrean -->
                  <div
                    class="p-3.5 rounded-2xl border flex items-center justify-between gap-3"
                    :class="app.queue_number ? 'bg-blue-50/80 dark:bg-blue-950/40 border-blue-200 dark:border-blue-900/60' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200/70 dark:border-slate-700/60'"
                  >
                    <div>
                      <p class="text-[10px] uppercase font-bold tracking-wider" :class="app.queue_number ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'">
                        Nomor Antrean
                      </p>
                      <p
                        v-if="app.queue_number"
                        class="text-xl font-black font-mono text-blue-700 dark:text-blue-300 mt-0.5"
                      >
                        {{ app.queue_number }}
                      </p>
                      <p v-else class="text-xs font-semibold text-slate-400 mt-0.5">
                        Belum Diterbitkan
                      </p>
                    </div>

                    <button
                      v-if="app.queue_number"
                      type="button"
                      @click="copyText(app.queue_number, 'Nomor Antrean')"
                      class="px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 hover:bg-blue-50 text-[11px] font-bold transition flex items-center gap-1 shadow-xs"
                      title="Salin Nomor Antrean"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                      <span>Salin</span>
                    </button>
                    <span v-else class="text-[10px] text-slate-400 text-right max-w-[120px] leading-tight">
                      Menunggu verifikasi & jadwal
                    </span>
                  </div>
                </div>

                <!-- Schedule Info if booked -->
                <div
                  v-if="app.schedule_date_formatted"
                  class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2 pt-1 font-medium"
                >
                  <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span>Jadwal Kedatangan: <strong>{{ app.schedule_date_formatted }}</strong> ({{ app.schedule_time || app.session_name }})</span>
                </div>
              </div>

              <!-- Right Action: Open Status Detail or Direct Booking -->
              <div class="lg:self-center shrink-0 flex flex-col sm:flex-row lg:flex-col gap-2">
                <Link
                  v-if="['PRE_VERIFIED', 'APPROVED'].includes(app.status) && !app.queue_number"
                  :href="route('queue.booking', { token: app.access_token })"
                  class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 hover:scale-[1.02] cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span>Pilih Jadwal Sekarang &rarr;</span>
                </Link>

                <Link
                  :href="app.detail_url"
                  :class="[
                    'px-6 py-3 rounded-2xl font-bold text-xs transition shadow-md flex items-center justify-center gap-2 hover:scale-[1.02] cursor-pointer',
                    (['PRE_VERIFIED', 'APPROVED'].includes(app.status) && !app.queue_number)
                      ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600'
                      : 'bg-blue-600 hover:bg-blue-500 text-white'
                  ]"
                >
                  <span>Lihat Detail & Lacak</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-8 space-y-4">
          <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center mx-auto text-slate-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">
            Belum Ada Permohonan Terdaftar
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
            Tidak ditemukan riwayat permohonan aktif atau selesai untuk NIK ini. Silakan buat permohonan baru untuk memulai.
          </p>
          <Link
            :href="route('queue.apply.create')"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold text-xs shadow-md hover:bg-blue-500 transition"
          >
            Mulai Pengajuan KTP-el Sekarang &rarr;
          </Link>
        </div>
      </div>

      <!-- Copy Toast Notification -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-2 opacity-0"
      >
        <div
          v-if="toastMessage"
          class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white text-xs font-bold px-4 py-3 rounded-2xl shadow-2xl border border-slate-700 flex items-center gap-2"
        >
          <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          <span>{{ toastMessage }}</span>
        </div>
      </transition>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StatusBadge from '@/Components/Queue/StatusBadge.vue';

const props = defineProps({
  isVerified: {
    type: Boolean,
    default: false,
  },
  maskedNik: {
    type: String,
    default: '',
  },
  applicantName: {
    type: String,
    default: 'Pemohon',
  },
  initialNik: {
    type: String,
    default: '',
  },
  applications: {
    type: Array,
    default: () => [],
  },
});

const verifyForm = useForm({
  nik: props.initialNik || '',
  phone: '',
  remember: true,
});

const toastMessage = ref('');
let toastTimer = null;

function submitVerify() {
  verifyForm.post(route('queue.history.verify'));
}

function logoutSession() {
  if (confirm('Apakah Anda ingin menutup sesi riwayat permohonan ini di perangkat Anda?')) {
    router.post(route('queue.history.forget'));
  }
}

function copyText(text, label) {
  if (!text) return;
  navigator.clipboard.writeText(text).then(() => {
    toastMessage.value = `${label} (${text}) berhasil disalin ke clipboard!`;
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toastMessage.value = '';
    }, 3000);
  }).catch(() => {
    toastMessage.value = `${label}: ${text}`;
  });
}
</script>
