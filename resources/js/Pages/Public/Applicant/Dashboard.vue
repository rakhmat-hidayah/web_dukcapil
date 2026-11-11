<template>
  <Head title="Dashboard Pemohon - Disdukcapil Kabupaten Dompu" />

  <PublicLayout :hide-footer="true">
    <div class="py-8 sm:py-12 max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
      <!-- Header bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Portal Pemohon KTP-el</span>
          <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
            Selamat Datang, {{ applicant.name }}
          </h1>
        </div>

        <div
          v-if="$page.props.queue_maintenance?.is_active"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-800 font-bold text-xs self-start sm:self-auto shadow-sm"
        >
          <AlertCircle class="w-4 h-4 text-amber-600 shrink-0" />
          <span>Pengajuan Ditutup Sementara</span>
        </div>
        <Link
          v-else-if="applicant.is_verified && applicant.is_active"
          :href="route('queue.apply.create')"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-lg shadow-blue-600/20 cursor-pointer self-start sm:self-auto"
        >
          <Plus class="w-4 h-4" />
          <span>Ajukan Permohonan KTP-el</span>
        </Link>
      </div>

      <!-- Maintenance Mode Alert Banner for Logged In Applicant -->
      <div
        v-if="$page.props.queue_maintenance?.is_active"
        class="p-4 sm:p-5 rounded-3xl bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-800 text-amber-950 dark:text-amber-200 shadow-sm space-y-2 animate-fade-in"
      >
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
            <AlertCircle class="w-4 h-4" />
          </div>
          <div class="space-y-1 flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="px-2 py-0.5 rounded-full text-[9.5px] font-black uppercase tracking-wider bg-rose-600 text-white leading-none">
                Layanan Ditutup Sementara
              </span>
              <span class="text-xs sm:text-sm font-black text-amber-900 dark:text-amber-100">
                {{ $page.props.queue_maintenance.title || 'Layanan Pendaftaran Online Sedang Dalam Pemeliharaan' }}
              </span>
            </div>
            <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
              {{ $page.props.queue_maintenance.message }}
            </p>
            <div v-if="$page.props.queue_maintenance.estimated_end" class="pt-1 flex items-center gap-1.5 text-xs text-amber-900 dark:text-amber-200 font-bold">
              <Clock class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" />
              <span>Estimasi Dibuka Kembali: <strong class="underline">{{ $page.props.queue_maintenance.estimated_end }}</strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Friendly Notice for Verified Applicants: Pengajuan Khusus Pemilik Akun & Alasan Kebijakan -->
      <div
        v-if="applicant.is_verified"
        class="p-5 sm:p-6 rounded-3xl bg-gradient-to-br from-blue-50/90 via-indigo-50/50 to-sky-50/90 dark:from-blue-950/40 dark:via-indigo-950/30 dark:to-slate-900 border border-blue-200/90 dark:border-blue-800/70 shadow-sm space-y-4"
      >
        <div class="flex items-start gap-3.5">
          <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-600/20 mt-0.5">
            <ShieldCheck class="w-5 h-5" />
          </div>
          <div class="space-y-1.5 flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300">
                Ketentuan Layanan Online
              </span>
              <span class="text-xs font-black text-slate-900 dark:text-white">
                Ketentuan Pengajuan Mandiri & Mewakili Keluarga 1 KK
              </span>
            </div>
            <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
              Halo Bapak/Ibu <strong class="font-bold text-slate-900 dark:text-white">{{ applicant.name }}</strong>, selamat akun Anda telah lolos verifikasi resmi oleh Dinas Dukcapil Kabupaten Dompu. Berdasarkan regulasi kependudukan, Anda dapat mengajukan permohonan KTP-el untuk <strong>diri sendiri</strong> atau <strong>mewakili anggota keluarga dalam 1 (satu) Kartu Keluarga (KK)</strong> dengan melampirkan formulir <strong>Surat Kuasa (F-1.07)</strong>.
            </p>
            <div class="p-3 rounded-2xl bg-blue-50/90 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 text-xs text-blue-900 dark:text-blue-200 leading-relaxed flex items-start gap-2.5">
              <span class="text-sm shrink-0">ℹ️</span>
              <div>
                <strong class="font-bold">Ketentuan Khusus:</strong>
                <p class="mt-0.5">
                  Khusus untuk <strong>Layanan Perekaman KTP-el Baru (Pemula)</strong>, permohonan wajib diajukan dan dihadiri langsung oleh pemilik dokumen karena memerlukan perekaman data biometrik (sidik jari, iris mata, pasfoto) di loket Disdukcapil dan tidak dapat diwakilkan.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- 3 Poin Alasan Kebijakan -->
        <div class="pt-3 border-t border-blue-200/60 dark:border-blue-800/60 space-y-2.5">
          <p class="text-[11.5px] font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
            <Info class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" />
            <span>Mengapa kebijakan ini diberlakukan demi kenyamanan Anda?</span>
          </p>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5">
            <div class="p-3.5 rounded-2xl bg-white/80 dark:bg-slate-900/80 border border-blue-100 dark:border-blue-900/50 shadow-xs space-y-1">
              <div class="flex items-center gap-1.5 text-blue-700 dark:text-blue-300">
                <Lock class="w-3.5 h-3.5 shrink-0" />
                <h4 class="text-xs font-bold">1. Perlindungan Data Pribadi</h4>
              </div>
              <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                Sesuai UU Perlindungan Data Pribadi (UU No. 27/2022), data kependudukan dan biometrik bersifat rahasia guna mencegah penyalahgunaan identitas Anda oleh pihak lain.
              </p>
            </div>

            <div class="p-3.5 rounded-2xl bg-white/80 dark:bg-slate-900/80 border border-blue-100 dark:border-blue-900/50 shadow-xs space-y-1">
              <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-300">
                <UserCheck class="w-3.5 h-3.5 shrink-0" />
                <h4 class="text-xs font-bold">2. Bebas Calo & 100% Gratis</h4>
              </div>
              <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                Menjamin seluruh masyarakat memperoleh pelayanan langsung, transparan, dan adil tanpa pungutan liar atau keterlibatan calo/perantara tidak resmi.
              </p>
            </div>

            <div class="p-3.5 rounded-2xl bg-white/80 dark:bg-slate-900/80 border border-blue-100 dark:border-blue-900/50 shadow-xs space-y-1">
              <div class="flex items-center gap-1.5 text-purple-700 dark:text-purple-300">
                <Sparkles class="w-3.5 h-3.5 shrink-0" />
                <h4 class="text-xs font-bold">3. Keabsahan Data SIAK</h4>
              </div>
              <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                Satu akun terikat tunggal dengan NIK pemilik di database kependudukan nasional, memastikan berkas dan pencetakan fisik KTP-el sah serta tepat sasaran.
              </p>
            </div>
          </div>

          <!-- Petunjuk ramah jika anggota keluarga butuh KTP-el -->
          <div class="p-3 rounded-2xl bg-blue-100/60 dark:bg-blue-900/30 text-[11px] text-blue-900 dark:text-blue-200 flex items-start gap-2">
            <span class="font-bold shrink-0">💡 Solusi Keluarga:</span>
            <span>
              Bagi anggota keluarga lainnya (seperti suami, istri, atau anak) yang membutuhkan pengajuan KTP-el, silakan <strong>mendaftarkan akun masing-masing</strong> menggunakan NIK dan email pribadi mereka agar hak data kependudukan tetap terlindungi.
            </span>
          </div>
        </div>
      </div>

      <!-- Identity & Account Status Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Profil Pemohon -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Identitas NIK</span>
            <UserCheck class="w-4 h-4 text-blue-600 dark:text-blue-400" />
          </div>
          <p class="text-lg font-black font-mono text-slate-900 dark:text-white">{{ applicant.masked_nik }}</p>
          <p class="text-xs text-slate-600 dark:text-slate-400 truncate">{{ applicant.masked_email }}</p>
        </div>

        <!-- Card 2: Status Verifikasi Identitas -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Status Identitas</span>
            <ShieldCheck v-if="applicant.is_verified" class="w-4 h-4 text-emerald-500" />
            <Clock v-else class="w-4 h-4 text-amber-500" />
          </div>
          <div v-if="applicant.is_verified" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-bold text-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>IDENTITAS TERVERIFIKASI</span>
          </div>
          <div v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 font-bold text-xs">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span>MENUNGGU VERIFIKASI PETUGAS</span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
            {{ applicant.is_verified ? 'Akun telah terverifikasi oleh Dinas Dukcapil.' : 'Verifikasi fisik/data kependudukan oleh petugas.' }}
          </p>
        </div>

        <!-- Card 3: Perangkat Terikat -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Perangkat Aktif</span>
            <Smartphone class="w-4 h-4 text-purple-600 dark:text-purple-400" />
          </div>
          <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
            {{ applicant.active_device?.device_label || 'Browser Ini' }}
          </p>
          <div class="flex items-center gap-1.5 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span>Sesi Persisten Terikat</span>
          </div>
        </div>
      </div>

      <!-- Notice if Identity is still PENDING -->
      <div v-if="!applicant.is_verified" class="p-4 sm:p-5 rounded-3xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 flex items-start gap-3">
        <AlertCircle class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
        <div class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed space-y-1">
          <p class="font-bold text-sm">Status Akun: Menunggu Verifikasi Identitas Petugas (IDENTITY_PENDING)</p>
          <p>
            Alamat email Anda telah berhasil diverifikasi. Sesuai kebijakan keamanan data kependudukan Disdukcapil Kabupaten Dompu, pengajuan permohonan baru KTP-el akan aktif setelah data identitas NIK Anda diverifikasi oleh petugas verifikator. Pemberitahuan akan dikirimkan secara otomatis ke email Anda begitu proses verifikasi selesai.
          </p>
        </div>
      </div>

      <!-- Section: Applications List -->
      <div id="riwayat" class="space-y-4 pt-2">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
            <FileText class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            <span>Riwayat & Status Permohonan Anda</span>
          </h2>
          <span class="text-xs text-slate-500 font-bold">Total: {{ applications.length }} Permohonan</span>
        </div>

        <div v-if="applications.length === 0" class="p-8 sm:p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
            <Inbox class="w-6 h-6" />
          </div>
          <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Belum Ada Permohonan KTP-el</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
            Anda belum memiliki pengajuan permohonan pelayanan KTP-el online yang terdaftar dengan NIK ini.
          </p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="app in applications"
            :key="app.id"
            class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-blue-300 dark:hover:border-blue-700 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4"
          >
            <div class="space-y-1.5">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-xs font-black text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-2.5 py-0.5 rounded-lg">
                  {{ app.reg_number }}
                </span>
                <span
                  class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg text-white"
                  :style="{ backgroundColor: app.status_color || '#2563eb' }"
                >
                  {{ app.status_label }}
                </span>
                <span v-if="app.queue_number" class="text-xs font-black text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-lg">
                  No. Antrean: {{ app.queue_number }}
                </span>
              </div>
              <h3 class="font-black text-sm text-slate-900 dark:text-white">{{ app.service_name }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Diajukan pada: {{ app.created_at_formatted }}
                <span v-if="app.schedule_date_formatted"> &bull; Jadwal: {{ app.schedule_date_formatted }} ({{ app.schedule_time }})</span>
              </p>
            </div>

            <Link
              :href="app.detail_url"
              class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs transition cursor-pointer self-start sm:self-center shrink-0"
            >
              <span>Pantau Status</span>
              <ChevronRight class="w-4 h-4" />
            </Link>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import {
  UserCheck,
  ShieldCheck,
  Clock,
  Smartphone,
  Plus,
  FileText,
  Inbox,
  ChevronRight,
  AlertCircle,
  Info,
  Lock,
  Sparkles,
} from '@lucide/vue'

defineProps({
  applicant: {
    type: Object,
    required: true,
  },
  applications: {
    type: Array,
    default: () => [],
  },
})
</script>
