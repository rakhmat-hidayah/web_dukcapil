<template>
  <Head :title="maintenance?.title || 'Pemeliharaan Layanan KTP-el - Disdukcapil Kabupaten Dompu'" />

  <PublicLayout>
    <div class="py-12 sm:py-20 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-12 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-8 text-center relative overflow-hidden">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-amber-500/10 dark:bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Maintenance Illustration / Icon -->
        <div class="relative z-10 flex flex-col items-center">
          <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/80 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-lg shadow-amber-500/10 mb-6">
            <svg class="w-10 h-10 sm:w-12 sm:h-12 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>

          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-100 dark:bg-amber-950/70 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs font-bold uppercase tracking-wider mb-3">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
            Sedang Dalam Pemeliharaan / Update Sistem
          </div>

          <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ maintenance?.title || 'Layanan Pendaftaran Online KTP-el Sedang Dinonaktifkan Sementara' }}
          </h1>

          <p class="mt-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed max-w-xl">
            {{ maintenance?.message || 'Mohon maaf atas ketidaknyamanan Anda. Saat ini layanan pendaftaran online KTP-el Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu sedang dalam proses pemeliharaan berkala atau pembaruan sistem untuk meningkatkan kualitas layanan.' }}
          </p>

          <!-- Estimated End Time -->
          <div v-if="maintenance?.estimated_end" class="mt-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 flex items-center justify-center gap-2.5 text-xs text-amber-900 dark:text-amber-200">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><strong>Estimasi Selesai / Dibuka Kembali:</strong> {{ maintenance.estimated_end }}</span>
          </div>
        </div>

        <!-- Offline Counter Availability Info Box -->
        <div class="relative z-10 text-left bg-slate-50 dark:bg-slate-900/80 rounded-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-700/80 space-y-2">
          <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-slate-900 dark:text-white">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Pelayanan Tatap Muka di Kantor Tetap Buka</span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Masyarakat yang memiliki keperluan mendesak dapat datang langsung ke loket pelayanan Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu di:
          </p>
          <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 font-mono">
            {{ maintenance?.office_address || 'Jl. Soekarno Hatta No. 09, Dompu - NTB 84211' }}
          </p>
        </div>

        <!-- Action Links & Helpdesk -->
        <div class="relative z-10 flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
          <a
            v-if="whatsappUrl"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0012.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.41a8.17 8.17 0 012.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 01-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.75-.67-1.26-1.5-1.41-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
            <span>Hubungi Helpdesk WhatsApp</span>
          </a>

          <Link
            :href="route('queue.history')"
            class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 text-blue-700 dark:text-blue-300 font-bold text-xs border border-blue-200 dark:border-blue-900 transition flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Riwayat Permohonan Saya</span>
          </Link>

          <Link
            :href="route('home')"
            class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs transition flex items-center justify-center gap-1.5"
          >
            <span>Beranda Utama</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  maintenance: Object,
});

const whatsappUrl = computed(() => {
  const phone = props.maintenance?.helpdesk_phone;
  if (!phone) return null;
  const cleanPhone = phone.replace(/[^0-9]/g, '');
  const target = cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : cleanPhone;
  const message = encodeURIComponent('Halo Admin Disdukcapil Kab. Dompu, saya ingin menanyakan terkait informasi layanan KTP-el.');
  return `https://wa.me/${target}?text=${message}`;
});
</script>

<style scoped>
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin-slow {
  animation: spin-slow 12s linear infinite;
}
</style>
