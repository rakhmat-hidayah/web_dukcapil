<template>
  <Head title="Jadwal Mobil Pelayanan Keliling - Dukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="space-y-10 text-left max-w-7xl mx-auto pb-16">
      
      <!-- Hero Banner Header -->
      <div class="relative overflow-hidden bg-gradient-to-br from-blue-950 via-indigo-950 to-slate-950 text-white rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-12 shadow-2xl border border-blue-900/40">
        <!-- Background Overlay Decorative Pattern -->
        <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>

        <div class="relative z-10 space-y-4 max-w-3xl">
          <!-- Breadcrumb & Tag -->
          <div class="flex items-center gap-2 text-xs font-semibold text-blue-200/80 font-mono">
            <Link href="/" class="hover:underline">Beranda</Link>
            <span>/</span>
            <Link href="/layanan" class="hover:underline">Layanan</Link>
            <span>/</span>
            <span class="text-amber-400 font-bold">Pelayanan Mobile</span>
          </div>

          <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-black uppercase rounded-xl tracking-wider backdrop-blur-md">
            <span>🚐</span>
            <span>{{ scheduleSettings.mobile_service_hero_tag || 'Jemput Bola Adminduk' }}</span>
          </div>

          <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
            {{ scheduleSettings.mobile_service_hero_title || 'Jadwal Pelayanan Mobile Keliling' }}
          </h1>

          <p class="text-sm sm:text-base text-blue-100/90 leading-relaxed font-medium">
            {{ scheduleSettings.mobile_service_hero_description || 'Layanan jemput bola penerbitan dokumen kependudukan langsung ke desa, kelurahan, dan sekolah di seluruh wilayah Kabupaten Dompu secara gratis dan cepat.' }}
          </p>
        </div>
      </div>

      <!-- Main Schedule Highlight & Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Active Schedule Card & Available Services (Col Span 2) -->
        <div class="lg:col-span-2 space-y-8">
          
          <!-- Current Active Schedule Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-lg shadow-gray-100/70 dark:shadow-none space-y-6 relative overflow-hidden">
            <!-- Accent Top Line -->
            <div class="absolute top-0 left-8 right-8 h-[4px] bg-gradient-to-r from-blue-600 via-indigo-600 to-amber-500 rounded-b-full"></div>

            <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-zinc-800">
              <div>
                <span class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-widest">
                  Status Lokasi Pelayanan Saat Ini
                </span>
                <h2 class="text-lg font-black text-gray-900 dark:text-zinc-50 flex items-center gap-2 mt-0.5">
                  <span>📍</span>
                  <span>Jadwal Pelayanan Terakhir / Aktif</span>
                </h2>
              </div>

              <!-- Status Badge -->
              <span 
                class="px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border shadow-sm"
                :class="statusBadgeClasses"
              >
                {{ statusBadgeText }}
              </span>
            </div>

            <!-- Schedule Info Box -->
            <div class="bg-slate-50/80 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 rounded-2xl p-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- Location -->
              <div class="space-y-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">
                  Wilayah Kecamatan / Desa
                </p>
                <p class="text-base font-black text-gray-900 dark:text-zinc-50">
                  {{ scheduleSettings.mobile_service_kecamatan || 'Kecamatan Woja' }}
                </p>
                <p v-if="scheduleSettings.mobile_service_desa" class="text-xs font-semibold text-primary-600 dark:text-primary-400">
                  📍 {{ scheduleSettings.mobile_service_desa }}
                </p>
              </div>

              <!-- Date & Time -->
              <div class="space-y-3 sm:border-l sm:border-gray-200/60 dark:sm:border-zinc-700/60 sm:pl-6">
                <div>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">Tanggal</p>
                  <p class="text-sm font-extrabold text-gray-900 dark:text-zinc-100 flex items-center gap-1.5 mt-0.5">
                    <span>📅</span>
                    <span>{{ scheduleSettings.mobile_service_date || 'Senin, 28 Juli 2026' }}</span>
                  </p>
                </div>

                <div>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">Jam Operasional</p>
                  <p class="text-sm font-extrabold text-gray-900 dark:text-zinc-100 flex items-center gap-1.5 mt-0.5">
                    <span>⏰</span>
                    <span>{{ scheduleSettings.mobile_service_time || '08.00 - 14.00 WITA' }}</span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Notes (Kotak Biru Catatan Pelayanan) -->
            <div 
              v-if="scheduleSettings.mobile_service_notes" 
              class="p-4 bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 rounded-2xl flex items-start gap-3 text-xs text-blue-900 dark:text-blue-200 leading-relaxed font-medium"
            >
              <span class="text-base">💡</span>
              <p>
                <strong>Catatan Pelayanan:</strong> {{ scheduleSettings.mobile_service_notes }}
              </p>
            </div>
          </div>

          <!-- Services Offered Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
            <h3 class="text-lg font-black text-gray-900 dark:text-zinc-50 tracking-tight flex items-center gap-2">
              🛠️ Dokumen yang Dilayani di Pelayanan Mobile
            </h3>

            <div v-if="services && services.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <Link 
                v-for="service in services" 
                :key="service.id"
                :href="`/layanan/${service.slug}`"
                class="p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 hover:bg-gray-100 dark:hover:bg-zinc-800 border border-gray-100 dark:border-zinc-800/60 flex items-start gap-3 transition group"
              >
                <div 
                  class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-base shrink-0 shadow-sm transition-transform group-hover:scale-110"
                  :style="{ backgroundColor: service.color || '#2563eb', color: '#ffffff' }"
                >
                  {{ service.icon || '📜' }}
                </div>
                <div>
                  <h4 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                    {{ service.title }}
                  </h4>
                  <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1 leading-relaxed line-clamp-2">
                    {{ service.description }}
                  </p>
                </div>
              </Link>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- KTP-el -->
              <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/60 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                  💳
                </div>
                <div>
                  <h4 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm">Perekaman &amp; Cetak KTP-el</h4>
                  <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1 leading-relaxed">
                    Perekaman biometrik pemula &amp; penggantian KTP-el rusak atau hilang.
                  </p>
                </div>
              </div>

              <!-- Kartu Keluarga -->
              <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/60 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                  📄
                </div>
                <div>
                  <h4 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm">Kartu Keluarga (KK)</h4>
                  <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1 leading-relaxed">
                    Penerbitan KK baru, perubahan elemen data keluarga, atau penambahan anggota.
                  </p>
                </div>
              </div>

              <!-- KIA -->
              <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/60 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-pink-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                  👶
                </div>
                <div>
                  <h4 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm">Kartu Identitas Anak (KIA)</h4>
                  <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1 leading-relaxed">
                    Penerbitan kartu identitas anak usia 0 sampai 17 tahun kurang satu hari.
                  </p>
                </div>
              </div>

              <!-- Akta Kelahiran -->
              <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/60 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                  📜
                </div>
                <div>
                  <h4 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm">Akta Kelahiran &amp; Kematian</h4>
                  <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1 leading-relaxed">
                    Pencatatan sipil penerbitan kutipan akta kelahiran &amp; akta kematian warga.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Requirements & Request Mobile Visit (Col Span 1) -->
        <div class="space-y-8">
          
          <!-- Document Requirements Checklist (Fully Dynamic from CMS) -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight flex items-center gap-2">
              📋 Persyaratan Umum Berkas
            </h3>

            <ul class="space-y-2.5 text-xs text-gray-600 dark:text-zinc-300 font-medium">
              <li 
                v-for="(req, index) in requirementsList" 
                :key="index"
                class="flex items-start gap-2"
              >
                <span class="text-emerald-500 font-bold shrink-0">✓</span>
                <span>{{ req }}</span>
              </li>
            </ul>
          </div>

          <!-- Request Visit for Village / School (Fully Dynamic from CMS) -->
          <div class="bg-gradient-to-br from-amber-500 to-amber-400 text-slate-950 rounded-3xl p-6 shadow-xl space-y-4 relative overflow-hidden">
            <div class="space-y-1">
              <span class="px-2.5 py-0.5 bg-slate-950 text-amber-300 text-[9px] font-black uppercase rounded-md tracking-wider">
                {{ scheduleSettings.mobile_service_request_badge || 'Khusus Kepala Desa / Sekolah' }}
              </span>
              <h3 class="text-base font-extrabold tracking-tight mt-2">
                {{ scheduleSettings.mobile_service_request_title || 'Ajukan Kunjungan Mobil Pelayanan' }}
              </h3>
              <p class="text-xs text-slate-900/80 leading-relaxed font-semibold">
                {{ scheduleSettings.mobile_service_request_description || 'Pemerintah Desa, Kelurahan, atau Kepala Sekolah di Kabupaten Dompu dapat mengajukan surat permohonan kunjungan tim jemput bola Dukcapil ke wilayah masing-masing.' }}
              </p>
            </div>

            <a 
              :href="requestButtonUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="w-full text-center py-3 bg-slate-950 hover:bg-slate-900 text-white text-xs font-black rounded-xl transition flex items-center justify-center gap-2 shadow-lg"
            >
              <span>💬 {{ scheduleSettings.mobile_service_request_button_text || 'Hubungi Tim Jemput Bola via WA' }}</span>
              <span>→</span>
            </a>
          </div>

          <!-- Contact Dukcapil Direct Link (Fully Dynamic from CMS) -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm text-center space-y-3">
            <h4 class="text-xs font-extrabold text-gray-900 dark:text-zinc-50">
              {{ scheduleSettings.mobile_service_contact_title || 'Butuh Informasi Lebih Lanjut?' }}
            </h4>
            <p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">
              {{ scheduleSettings.mobile_service_contact_description || 'Hubungi petugas layanan Dukcapil Kabupaten Dompu melalui saluran kontak resmi.' }}
            </p>
            <Link 
              :href="scheduleSettings.mobile_service_contact_button_url || '/contact'" 
              class="inline-block px-5 py-2.5 bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 dark:hover:bg-zinc-700 text-gray-900 dark:text-zinc-100 text-xs font-extrabold rounded-xl transition border border-gray-200 dark:border-zinc-700"
            >
              {{ scheduleSettings.mobile_service_contact_button_text || 'Lihat Kontak Kantor' }} →
            </Link>
          </div>

        </div>

      </div>

    </div>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  scheduleSettings: Object,
  services: Array,
  innovations: Array,
});

const page = usePage();

const statusBadgeClasses = computed(() => {
  const status = props.scheduleSettings?.mobile_service_status || 'hari_ini';
  switch (status) {
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
  const status = props.scheduleSettings?.mobile_service_status || 'hari_ini';
  switch (status) {
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

const whatsappUrl = computed(() => {
  const wa = props.scheduleSettings?.quick_help_whatsapp || 'https://wa.me/6282340001111';
  if (wa.startsWith('http://') || wa.startsWith('https://')) return wa;
  const cleanNumber = wa.replace(/[^0-9]/g, '');
  return `https://wa.me/${cleanNumber}`;
});

const requestButtonUrl = computed(() => {
  const url = props.scheduleSettings?.mobile_service_request_button_url;
  if (!url) return whatsappUrl.value;
  if (url.startsWith('http://') || url.startsWith('https://')) return url;
  const cleanNumber = url.replace(/[^0-9]/g, '');
  return `https://wa.me/${cleanNumber}`;
});

const requirementsList = computed(() => {
  const reqs = props.scheduleSettings?.mobile_service_requirements;
  if (!reqs) {
    return [
      'Fotokopi Kartu Keluarga (KK) terbaru.',
      'KTP-el lama atau Surat Keterangan Pengganti (jika ubah data/rusak).',
      'Surat Keterangan Kehilangan dari Kepolisian (jika KTP/KK hilang).',
      'Buku Nikah / Kutipan Akta Perkawinan (jika ubah status perkawinan).',
      'Surat Keterangan Lahir dari Bidan/Klinik/RS (untuk Akta Kelahiran).',
    ];
  }
  return reqs.split('\n').map(item => item.trim()).filter(Boolean);
});
</script>
