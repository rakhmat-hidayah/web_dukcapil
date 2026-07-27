<template>
  <div class="space-y-6 text-left">
    <!-- SECTION 1: Mobil Pelayanan (Only displayed if active and kecamatan provided) -->
    <div 
      v-if="showMobileService"
      class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4 relative overflow-hidden transition-all duration-200"
    >
      <!-- Top Subtle Gradient Accent Accent Line -->
      <div class="absolute top-0 left-6 right-6 h-[3px] bg-gradient-to-r from-blue-500 via-indigo-500 to-amber-500 rounded-b-full"></div>

      <!-- Section Header & Status Badge -->
      <div class="flex items-start justify-between gap-3 pt-1">
        <div>
          <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-900/40 text-[9px] font-black uppercase rounded tracking-wider">
            Jemput Bola
          </span>
          <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight flex items-center gap-1.5 mt-1">
            <span>🚐</span>
            <span>Mobil Pelayanan</span>
          </h3>
        </div>

        <!-- Dynamic Status Badge Colors: Green = Hari Ini, Blue = Besok, Gray = Selesai, Orange = Akan Datang -->
        <span 
          class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-2xs shrink-0"
          :class="statusBadgeClasses"
        >
          {{ statusBadgeText }}
        </span>
      </div>

      <!-- Service Location & Schedule Details -->
      <div class="bg-slate-50/80 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 space-y-3">
        <!-- Location (Kecamatan & Desa) -->
        <div class="space-y-0.5">
          <p class="text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-widest font-mono">
            Lokasi Pelayanan
          </p>
          <p class="text-xs font-black text-gray-900 dark:text-zinc-100 flex items-center gap-1.5">
            <span class="text-primary-600 dark:text-primary-400">📍</span>
            <span>{{ settings.mobile_service_kecamatan || 'Kecamatan Woja' }}</span>
            <span v-if="settings.mobile_service_desa" class="text-gray-400 font-normal">({{ settings.mobile_service_desa }})</span>
          </p>
        </div>

        <!-- Date & Time -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-gray-200/60 dark:border-zinc-700/60">
          <div class="space-y-0.5">
            <p class="text-[9px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider font-mono">Tanggal</p>
            <p class="text-[11px] font-bold text-gray-800 dark:text-zinc-200 flex items-center gap-1">
              <span>📅</span>
              <span class="truncate">{{ settings.mobile_service_date || '-' }}</span>
            </p>
          </div>

          <div class="space-y-0.5">
            <p class="text-[9px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider font-mono">Jam</p>
            <p class="text-[11px] font-bold text-gray-800 dark:text-zinc-200 flex items-center gap-1">
              <span>⏰</span>
              <span class="truncate">{{ settings.mobile_service_time || '-' }}</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Action Button Section 1 -->
      <a 
        :href="settings.mobile_service_button_url || '/layanan/mobil'" 
        class="w-full flex items-center justify-center gap-1.5 py-2.5 bg-blue-50 dark:bg-zinc-800 hover:bg-blue-100 dark:hover:bg-zinc-700 text-blue-700 dark:text-blue-300 text-xs font-extrabold rounded-xl transition border border-blue-200/80 dark:border-zinc-700"
      >
        <span>{{ settings.mobile_service_button_text || 'Lihat Jadwal Lengkap' }}</span>
        <span>→</span>
      </a>
    </div>

    <!-- SECTION 2: Bantuan Cepat (Always displayed) -->
    <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
      <div class="flex items-center justify-between">
        <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40 text-[9px] font-black uppercase rounded tracking-wider">
          Kontak Langsung
        </span>
      </div>

      <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight flex items-center gap-1.5">
        <span>🤝</span>
        <span>Bantuan Cepat</span>
      </h3>

      <!-- Quick Action Items List -->
      <div class="space-y-2">
        <!-- WhatsApp Pelayanan -->
        <a 
          v-if="whatsappUrl"
          :href="whatsappUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 hover:bg-emerald-100/80 dark:hover:bg-emerald-900/40 border border-emerald-100 dark:border-emerald-900/40 text-emerald-800 dark:text-emerald-300 transition group"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
              💬
            </span>
            <div class="min-w-0">
              <p class="text-xs font-bold truncate">WhatsApp Pelayanan</p>
              <p class="text-[10px] text-emerald-600 dark:text-emerald-400 truncate">Respon cepat via chat</p>
            </div>
          </div>
          <span class="text-xs font-bold group-hover:translate-x-0.5 transition-transform">→</span>
        </a>

        <!-- Call Center -->
        <a 
          v-if="settings.quick_help_call_center"
          :href="'tel:' + settings.quick_help_call_center"
          class="flex items-center justify-between p-2.5 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 hover:bg-blue-100/80 dark:hover:bg-blue-900/40 border border-blue-100 dark:border-blue-900/40 text-blue-800 dark:text-blue-300 transition group"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
              📞
            </span>
            <div class="min-w-0">
              <p class="text-xs font-bold truncate">Call Center</p>
              <p class="text-[10px] text-blue-600 dark:text-blue-400 truncate">{{ settings.quick_help_call_center }}</p>
            </div>
          </div>
          <span class="text-xs font-bold group-hover:translate-x-0.5 transition-transform">→</span>
        </a>

        <!-- Tracking Pengajuan -->
        <a 
          v-if="settings.quick_help_tracking_url"
          :href="settings.quick_help_tracking_url"
          class="flex items-center justify-between p-2.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 hover:bg-indigo-100/80 dark:hover:bg-indigo-900/40 border border-indigo-100 dark:border-indigo-900/40 text-indigo-800 dark:text-indigo-300 transition group"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
              🔍
            </span>
            <div class="min-w-0">
              <p class="text-xs font-bold truncate">Tracking Pengajuan</p>
              <p class="text-[10px] text-indigo-600 dark:text-indigo-400 truncate">Cek status berkas online</p>
            </div>
          </div>
          <span class="text-xs font-bold group-hover:translate-x-0.5 transition-transform">→</span>
        </a>

        <!-- Google Maps Kantor -->
        <a 
          v-if="settings.quick_help_google_maps_url"
          :href="settings.quick_help_google_maps_url"
          target="_blank"
          rel="noopener noreferrer"
          class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/60 dark:bg-amber-950/30 hover:bg-amber-100/80 dark:hover:bg-amber-900/40 border border-amber-100 dark:border-amber-900/40 text-amber-800 dark:text-amber-300 transition group"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-7 h-7 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
              📍
            </span>
            <div class="min-w-0">
              <p class="text-xs font-bold truncate">Google Maps Kantor</p>
              <p class="text-[10px] text-amber-600 dark:text-amber-400 truncate">Petunjuk arah ke lokasi</p>
            </div>
          </div>
          <span class="text-xs font-bold group-hover:translate-x-0.5 transition-transform">→</span>
        </a>

        <!-- Email Dukcapil -->
        <a 
          v-if="settings.quick_help_email"
          :href="'mailto:' + settings.quick_help_email"
          class="flex items-center justify-between p-2.5 rounded-xl bg-purple-50/60 dark:bg-purple-950/30 hover:bg-purple-100/80 dark:hover:bg-purple-900/40 border border-purple-100 dark:border-purple-900/40 text-purple-800 dark:text-purple-300 transition group"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
              ✉️
            </span>
            <div class="min-w-0">
              <p class="text-xs font-bold truncate">Email Dukcapil</p>
              <p class="text-[10px] text-purple-600 dark:text-purple-400 truncate">{{ settings.quick_help_email }}</p>
            </div>
          </div>
          <span class="text-xs font-bold group-hover:translate-x-0.5 transition-transform">→</span>
        </a>
      </div>

      <!-- Primary Action Button Section 2 -->
      <a 
        :href="settings.quick_help_primary_button_url || '/contact'" 
        class="w-full flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 text-xs font-black rounded-xl shadow-md shadow-amber-500/20 active:scale-95 transition"
      >
        <span>{{ settings.quick_help_primary_button_text || 'Hubungi Kami' }}</span>
        <span>→</span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const settings = computed(() => {
  const ws = page.props.website_settings || {};
  return {
    mobile_service_is_active: ws.mobile_service_is_active !== '0' && ws.mobile_service_is_active !== false,
    mobile_service_kecamatan: ws.mobile_service_kecamatan || '',
    mobile_service_desa: ws.mobile_service_desa || '',
    mobile_service_date: ws.mobile_service_date || '',
    mobile_service_time: ws.mobile_service_time || '',
    mobile_service_status: ws.mobile_service_status || 'hari_ini',
    mobile_service_button_text: ws.mobile_service_button_text || 'Lihat Jadwal Lengkap',
    mobile_service_button_url: ws.mobile_service_button_url || '/layanan/mobil',
    quick_help_whatsapp: ws.quick_help_whatsapp || 'https://wa.me/6282340001111',
    quick_help_call_center: ws.quick_help_call_center || '(0373) 21123',
    quick_help_tracking_url: ws.quick_help_tracking_url || '/layanan/tracking',
    quick_help_google_maps_url: ws.quick_help_google_maps_url || 'https://maps.google.com/?q=Dukcapil+Dompu',
    quick_help_email: ws.quick_help_email || 'dukcapilkabdompu@gmail.com',
    quick_help_primary_button_text: ws.quick_help_primary_button_text || 'Hubungi Kami',
    quick_help_primary_button_url: ws.quick_help_primary_button_url || '/contact',
  };
});

const showMobileService = computed(() => {
  return settings.value.mobile_service_is_active && Boolean(settings.value.mobile_service_kecamatan);
});

// Format WhatsApp URL cleanly
const whatsappUrl = computed(() => {
  const wa = settings.value.quick_help_whatsapp;
  if (!wa) return null;
  if (wa.startsWith('http://') || wa.startsWith('https://')) return wa;
  const cleanNumber = wa.replace(/[^0-9]/g, '');
  return `https://wa.me/${cleanNumber}`;
});

// Status Badge Colors: Green = Hari Ini, Blue = Besok, Gray = Selesai, Orange = Akan Datang
const statusBadgeClasses = computed(() => {
  switch (settings.value.mobile_service_status) {
    case 'hari_ini':
      return 'bg-emerald-500 text-white border-emerald-400 shadow-emerald-500/20';
    case 'besok':
      return 'bg-blue-500 text-white border-blue-400 shadow-blue-500/20';
    case 'akan_datang':
      return 'bg-amber-500 text-slate-950 border-amber-400 shadow-amber-500/20';
    case 'selesai':
      return 'bg-gray-400 text-white border-gray-300 dark:bg-zinc-700 dark:border-zinc-600';
    default:
      return 'bg-emerald-500 text-white border-emerald-400';
  }
});

const statusBadgeText = computed(() => {
  switch (settings.value.mobile_service_status) {
    case 'hari_ini':
      return 'Hari Ini';
    case 'besok':
      return 'Besok';
    case 'akan_datang':
      return 'Akan Datang';
    case 'selesai':
      return 'Selesai';
    default:
      return 'Hari Ini';
  }
});
</script>
