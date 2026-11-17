<template>
  <Head :title="seo.title">
    <meta name="description" :content="seo.description" />
  </Head>

  <PublicLayout>
    <div class="min-h-screen py-8 sm:py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-8">
      <!-- Breadcrumb Navigation -->
      <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-zinc-400">
        <Link href="/" class="hover:text-primary-600 transition flex items-center gap-1">
          <Home class="w-3.5 h-3.5" />
          <span>Beranda</span>
        </Link>
        <span>/</span>
        <Link href="/layanan" class="hover:text-primary-600 transition">
          Layanan
        </Link>
        <span>/</span>
        <span class="text-gray-800 dark:text-zinc-200 font-bold">Ketersediaan Blangko KTP-el</span>
      </nav>

      <!-- Hero Header Section -->
      <div class="text-center space-y-3 max-w-2xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 shadow-2xs">
          <CreditCard class="w-4 h-4 text-sky-600 dark:text-sky-400" />
          <span>Status Persediaan Resmi KTP-el</span>
        </div>
        <h1 class="text-2xl sm:text-4xl font-black text-gray-950 dark:text-white tracking-tight">
          Ketersediaan Blangko KTP-el
        </h1>
        <p class="text-xs sm:text-sm text-gray-600 dark:text-zinc-400 leading-relaxed font-medium">
          Informasi resmi ketersediaan blangko KTP-el di Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu yang diperbarui otomatis secara real-time.
        </p>
      </div>

      <!-- Main Availability Status Card -->
      <div
        class="rounded-3xl p-6 sm:p-10 border-2 shadow-xl relative overflow-hidden transition-all duration-300"
        :class="statusCardStyles"
      >
        <!-- Soft background glow decoration -->
        <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full blur-3xl pointer-events-none opacity-20"
             :class="glowColorClass"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="space-y-4 max-w-xl">
            <!-- Availability Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs sm:text-sm font-black uppercase tracking-wider shadow-sm"
                 :class="badgeStyles">
              <span class="w-2.5 h-2.5 rounded-full animate-ping" :class="dotColorClass"></span>
              <span>{{ summary.status }}</span>
            </div>

            <!-- Main Stock Display -->
            <div>
              <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 block">
                Jumlah Persediaan Blangko
              </span>
              <div class="text-4xl sm:text-6xl font-black font-mono tracking-tight text-gray-950 dark:text-white mt-1">
                {{ formatNumber(summary.current_stock) }}
                <span class="text-base sm:text-xl font-bold font-sans text-gray-500 dark:text-zinc-400">Keping</span>
              </div>
            </div>

            <!-- Public Advisory Message -->
            <p class="text-xs sm:text-sm leading-relaxed text-gray-700 dark:text-zinc-300">
              {{ advisoryMessage }}
            </p>

            <!-- Valid Timestamp -->
            <div class="pt-2 flex items-center gap-2 text-[11px] text-gray-500 dark:text-zinc-400">
              <Clock class="w-3.5 h-3.5 text-gray-400 shrink-0" />
              <span>Data resmi diperbarui per: <strong>{{ summary.last_updated_formatted }}</strong></span>
            </div>
          </div>

          <!-- Quick Action Buttons on Hero Card -->
          <div class="flex flex-col gap-2.5 shrink-0 w-full md:w-auto">
            <Link
              href="/layanan/antrian"
              class="px-6 py-3.5 rounded-2xl bg-slate-950 hover:bg-slate-900 text-white dark:bg-white dark:text-slate-950 dark:hover:bg-gray-100 font-black text-xs sm:text-sm text-center shadow-lg transition flex items-center justify-center gap-2 cursor-pointer active:scale-95"
            >
              <Ticket class="w-4 h-4" />
              <span>Daftar Layanan KTP-el</span>
            </Link>

            <Link
              href="/layanan/antrian/riwayat"
              class="px-6 py-3 rounded-2xl bg-white/80 dark:bg-zinc-800/80 hover:bg-white dark:hover:bg-zinc-800 text-gray-800 dark:text-zinc-200 font-bold text-xs text-center border border-gray-200 dark:border-zinc-700 shadow-2xs transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <Search class="w-4 h-4" />
              <span>Lacak Status Permohonan</span>
            </Link>

            <Link
              href="/pengaduan"
              class="px-6 py-3 rounded-2xl bg-white/80 dark:bg-zinc-800/80 hover:bg-white dark:hover:bg-zinc-800 text-gray-800 dark:text-zinc-200 font-bold text-xs text-center border border-gray-200 dark:border-zinc-700 shadow-2xs transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <MessageSquare class="w-4 h-4" />
              <span>Pengaduan & Bantuan</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Public Monthly Statistics Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Penerimaan Bulan Berjalan -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-900">
            <CheckCircle2 class="w-6 h-6" />
          </div>
          <div>
            <span class="text-xs font-bold text-gray-500 dark:text-zinc-400 block">Penerimaan Blangko Bulan Ini</span>
            <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">
              +{{ formatNumber(summary.received_this_month) }} <span class="text-xs font-bold text-gray-400">keping</span>
            </div>
          </div>
        </div>

        <!-- Pemakaian Bulan Berjalan -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-900">
            <CreditCard class="w-6 h-6" />
          </div>
          <div>
            <span class="text-xs font-bold text-gray-500 dark:text-zinc-400 block">KTP-el Dicetak Bulan Ini</span>
            <div class="text-2xl sm:text-3xl font-black font-mono text-gray-900 dark:text-zinc-100 mt-0.5">
              {{ formatNumber(summary.used_this_month) }} <span class="text-xs font-bold text-gray-400">keping</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Informasi Edukasi & FAQ Layanan KTP-el -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        <div class="border-b border-gray-100 dark:border-zinc-800 pb-4">
          <h2 class="text-base sm:text-lg font-black text-gray-950 dark:text-white tracking-tight flex items-center gap-2">
            <HelpCircle class="w-5 h-5 text-sky-600 dark:text-sky-400" />
            <span>Tanya Jawab Seputar Persediaan & Pencetakan KTP-el</span>
          </h2>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Panduan resmi bagi masyarakat Kabupaten Dompu mengenai proses dan alternatif layanan KTP-el
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs sm:text-[13px] leading-relaxed text-gray-700 dark:text-zinc-300">
          <div class="p-4 rounded-2xl bg-gray-50 dark:bg-zinc-800/50 border border-gray-100 dark:border-zinc-800 space-y-1.5">
            <h3 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm">
              Apakah cetak KTP-el dipungut biaya?
            </h3>
            <p class="text-gray-600 dark:text-zinc-400">
              <strong>GRATIS (Rp 0,-).</strong> Seluruh pengurusan adminduk termasuk pencetakan KTP-el di Disdukcapil Kabupaten Dompu tidak dipungut biaya sepeser pun sesuai undang-undang.
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-gray-50 dark:bg-zinc-800/50 border border-gray-100 dark:border-zinc-800 space-y-1.5">
            <h3 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm">
              Bagaimana jika persediaan blangko habis?
            </h3>
            <p class="text-gray-600 dark:text-zinc-400">
              Masyarakat dapat langsung mengaktifkan <strong>Identitas Kependudukan Digital (IKD)</strong> di ponsel atau diterbitkan <strong>Biodata WNI / Surat Keterangan</strong> resmi yang sah digunakan untuk seluruh layanan perbankan, BPJS, dan instansi publik.
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-gray-50 dark:bg-zinc-800/50 border border-gray-100 dark:border-zinc-800 space-y-1.5">
            <h3 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm">
              Siapa yang diprioritaskan saat stok terbatas?
            </h3>
            <p class="text-gray-600 dark:text-zinc-400">
              Saat persediaan terbatas, pencetakan fisik diprioritaskan untuk pemohon pemula (warga yang baru menginjak usia 17 tahun) dan kebutuhan darurat kesehatan/pendidikan.
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-gray-50 dark:bg-zinc-800/50 border border-gray-100 dark:border-zinc-800 space-y-1.5">
            <h3 class="font-extrabold text-gray-900 dark:text-zinc-100 text-sm">
              Di mana saya bisa mengambil fisik KTP-el yang sudah dicetak?
            </h3>
            <p class="text-gray-600 dark:text-zinc-400">
              Pengambilan dilakukan di Loket Pengambilan KTP-el Kantor Disdukcapil Kabupaten Dompu, Jl. Soekarno-Hatta No. 09, pada hari kerja (Senin-Kamis 08:00-16:00, Jumat 08:00-16:30 WITA).
            </p>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { 
  Home, CreditCard, Clock, Ticket, Search, MessageSquare, 
  CheckCircle2, HelpCircle 
} from '@lucide/vue';

const props = defineProps({
  summary: {
    type: Object,
    required: true,
  },
  seo: {
    type: Object,
    default: () => ({
      title: 'Ketersediaan Blangko KTP-el - Disdukcapil Dompu',
      description: 'Status resmi persediaan blangko KTP-el Disdukcapil Kabupaten Dompu.',
    }),
  },
});

const statusCardStyles = computed(() => {
  switch (props.summary.status) {
    case 'TERSEDIA':
      return 'bg-gradient-to-br from-emerald-50/90 via-teal-50/50 to-white dark:from-emerald-950/30 dark:via-zinc-900 dark:to-zinc-900 border-emerald-300 dark:border-emerald-800/60 shadow-emerald-500/5';
    case 'PERSEDIAAN TERBATAS':
      return 'bg-gradient-to-br from-amber-50/90 via-orange-50/50 to-white dark:from-amber-950/30 dark:via-zinc-900 dark:to-zinc-900 border-amber-300 dark:border-amber-800/60 shadow-amber-500/5';
    default:
      return 'bg-gradient-to-br from-rose-50/90 via-red-50/50 to-white dark:from-rose-950/30 dark:via-zinc-900 dark:to-zinc-900 border-rose-300 dark:border-rose-800/60 shadow-rose-500/5';
  }
});

const badgeStyles = computed(() => {
  switch (props.summary.status) {
    case 'TERSEDIA':
      return 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700';
    case 'PERSEDIAAN TERBATAS':
      return 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-700';
    default:
      return 'bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 border border-rose-300 dark:border-rose-700';
  }
});

const dotColorClass = computed(() => {
  switch (props.summary.status) {
    case 'TERSEDIA':
      return 'bg-emerald-500';
    case 'PERSEDIAAN TERBATAS':
      return 'bg-amber-500';
    default:
      return 'bg-rose-500';
  }
});

const glowColorClass = computed(() => {
  switch (props.summary.status) {
    case 'TERSEDIA':
      return 'bg-emerald-400';
    case 'PERSEDIAAN TERBATAS':
      return 'bg-amber-400';
    default:
      return 'bg-rose-400';
  }
});

const advisoryMessage = computed(() => {
  switch (props.summary.status) {
    case 'TERSEDIA':
      return 'Pelayanan pencetakan fisik KTP-el di Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu saat ini berjalan normal. Pemohon dapat melakukan pengajuan layanan online atau mendatangi loket pelayanan.';
    case 'PERSEDIAAN TERBATAS':
      return 'Persediaan blangko KTP-el menipis. Pencetakan fisik diprioritaskan bagi pemohon pemula usia 17 tahun dan kebutuhan darurat. Warga disarankan juga mengaktifkan Identitas Kependudukan Digital (IKD).';
    default:
      return 'Persediaan blangko KTP-el fisik saat ini sedang kosong menunggu alokasi penerimaan baru dari Ditjen Dukcapil Kemendagri / Provinsi NTB. Sebagai alternatif dokumen sah, warga dapat mengaktifkan Identitas Kependudukan Digital (IKD) atau meminta Surat Keterangan resmi pengganti KTP-el.';
  }
});

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
};
</script>
