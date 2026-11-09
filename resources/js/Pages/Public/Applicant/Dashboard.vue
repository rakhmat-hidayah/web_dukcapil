<template>
  <Head title="Dashboard Pemohon - Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="py-8 sm:py-12 max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
      <!-- Header bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Portal Pemohon KTP-el</span>
          <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
            Selamat Datang, {{ applicant.name }}
          </h1>
        </div>

        <Link
          v-if="applicant.is_verified && applicant.is_active"
          :href="route('queue.apply.create')"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-lg shadow-blue-600/20 cursor-pointer self-start sm:self-auto"
        >
          <Plus class="w-4 h-4" />
          <span>Ajukan Permohonan KTP-el</span>
        </Link>
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
            Alamat email Anda telah berhasil diverifikasi. Sesuai kebijakan keamanan data kependudukan Disdukcapil Kabupaten Dompu, pengajuan permohonan baru KTP-el akan aktif setelah data identitas NIK Anda diverifikasi oleh petugas verifikator.
          </p>
        </div>
      </div>

      <!-- Section: Applications List -->
      <div class="space-y-4 pt-2">
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
