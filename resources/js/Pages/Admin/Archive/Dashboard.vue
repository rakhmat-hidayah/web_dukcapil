<template>
  <Head title="Dashboard Pengarsipan Permohonan - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-indigo-900/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 mb-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
              Pengarsipan Dokumen Kependudukan
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Dashboard Pengarsipan
            </h1>
            <p class="text-xs sm:text-sm text-indigo-200 mt-1 max-w-2xl leading-relaxed">
              Pengelolaan arsip digital dokumen permohonan online, pengemasan paket ZIP dengan verifikasi SHA-256, serta pelacakan kesiapan pemindahan ke penyimpanan permanen instansi.
            </p>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex flex-wrap items-center gap-2">
            <Link
              :href="route('admin.archive.ready-applications')"
              class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 transition flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Permohonan Siap Arsip ({{ stats.unarchived_applications_count }})
            </Link>
            <Link
              :href="route('admin.archive.packages.index')"
              class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
              Paket Arsip ZIP ({{ stats.total_packages }})
            </Link>
          </div>
        </div>
      </div>

      <!-- Operational Mode Notice Banner -->
      <div class="rounded-3xl p-5 border shadow-sm transition" :class="stats.operation_mode === 'ARSIP_SAJA' ? 'bg-emerald-50/80 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/50 text-emerald-900 dark:text-emerald-200' : 'bg-amber-50/80 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/50 text-amber-900 dark:text-amber-200'">
        <div class="flex items-start gap-4">
          <div class="p-2.5 rounded-2xl shrink-0" :class="stats.operation_mode === 'ARSIP_SAJA' ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-300'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="font-extrabold text-sm tracking-wide">MODE PENGARSIPAN AKTIF: {{ stats.operation_mode }}</span>
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold" :class="stats.operation_mode === 'ARSIP_SAJA' ? 'bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200' : 'bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-200'">
                Penghapusan Otomatis File Sumber: Nonaktif (Aman)
              </span>
            </div>
            <p class="text-xs mt-1 leading-relaxed text-slate-600 dark:text-slate-300">
              Hosting website saat ini difungsikan murni sebagai tempat pemrosesan sementara dan generator paket ZIP. Seluruh file sumber permohonan tetap tersimpan aman di hosting sampai integrasi penyimpanan permanen instansi terverifikasi dan memenuhi kebijakan retensi.
            </p>
          </div>
        </div>
      </div>

      <!-- Metric Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Permohonan Belum Diarsipkan -->
        <Link
          :href="route('admin.archive.ready-applications')"
          class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-blue-400 transition group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Siap Diarsipkan</span>
            <div class="w-9 h-9 rounded-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.unarchived_applications_count }}</p>
          <p class="text-[11px] text-blue-600 dark:text-blue-400 mt-1 font-semibold group-hover:underline">
            Dari {{ stats.total_eligible_applications }} permohonan siap/selesai &rarr;
          </p>
        </Link>

        <!-- 2. Paket Siap Diunduh -->
        <Link
          :href="route('admin.archive.packages.index')"
          class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-indigo-400 transition group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Paket Arsip ZIP</span>
            <div class="w-9 h-9 rounded-2xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.ready_packages }}</p>
          <p class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-1 font-semibold group-hover:underline">
            Total Ukuran: {{ stats.total_package_size_formatted }} &rarr;
          </p>
        </Link>

        <!-- 3. Menunggu Konfirmasi Operator -->
        <Link
          :href="route('admin.archive.packages.index', { status: 'READY_FOR_DOWNLOAD' })"
          class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-amber-400 transition group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Konfirmasi Operator</span>
            <div class="w-9 h-9 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.awaiting_confirmation_count }}</p>
          <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-semibold group-hover:underline">
            {{ stats.confirmed_count }} paket terkonfirmasi disimpan &rarr;
          </p>
        </Link>

        <!-- 4. Status Penyimpanan Permanen -->
        <Link
          :href="route('admin.archive.permanent-storage')"
          class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-teal-400 transition group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Penyimpanan Permanen</span>
            <div class="w-9 h-9 rounded-2xl bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7zm0 4h16M8 4v4m8-4v4"/></svg>
            </div>
          </div>
          <p class="text-sm font-extrabold text-slate-800 dark:text-slate-200 truncate mt-2">
            {{ stats.permanent_storage_configured ? 'Terkoneksi' : 'Belum Dikonfigurasi' }}
          </p>
          <p class="text-[11px] text-teal-600 dark:text-teal-400 mt-1 font-semibold group-hover:underline">
            {{ stats.verified_permanent_count }} paket terverifikasi di server &rarr;
          </p>
        </Link>
      </div>

      <!-- Problem Alert if Any Incomplete Packages -->
      <div v-if="stats.incomplete_packages > 0 || recentProblems.length > 0" class="bg-rose-50 dark:bg-rose-950/30 rounded-3xl p-5 border border-rose-200 dark:border-rose-900/50 shadow-sm">
        <div class="flex items-start gap-3">
          <div class="p-2 rounded-2xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-rose-900 dark:text-rose-200">Perhatian: Ada Paket Arsip Bermasalah / Dokumen Tidak Lengkap ({{ stats.incomplete_packages }})</h3>
            <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">Sistem memblokir pengarsipan sah jika terdapat dokumen wajib yang hilang dari disk penyimpanan hosting.</p>
            <div class="mt-3 space-y-2">
              <div v-for="prob in recentProblems" :key="prob.id" class="flex items-center justify-between bg-white dark:bg-slate-900 p-3 rounded-2xl border border-rose-200 dark:border-rose-800 text-xs">
                <div>
                  <span class="font-mono font-bold text-rose-600 dark:text-rose-400">{{ prob.package_code }}</span>
                  <span class="text-slate-400 ml-2 text-[11px]">{{ prob.error_message }}</span>
                </div>
                <Link :href="route('admin.archive.packages.show', prob.id)" class="px-3 py-1 bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 rounded-lg font-bold hover:underline">
                  Periksa Masalah &rarr;
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Archive Packages Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Paket Arsip ZIP Terbaru</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar paket berkas terkompresi dengan manifest dan checksum SHA-256</p>
          </div>
          <Link
            :href="route('admin.archive.packages.index')"
            class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
          >
            Lihat Semua Paket ({{ stats.total_packages }}) &rarr;
          </Link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 uppercase tracking-wider font-bold border-y border-slate-100 dark:border-slate-700/60">
              <tr>
                <th class="py-3 px-4">Kode Paket</th>
                <th class="py-3 px-4">Periode Arsip</th>
                <th class="py-3 px-4 text-center">Permohonan</th>
                <th class="py-3 px-4 text-center">Berkas</th>
                <th class="py-3 px-4">Ukuran ZIP</th>
                <th class="py-3 px-4">Status Paket</th>
                <th class="py-3 px-4">Konfirmasi Operator</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <tr v-if="recentPackages.length === 0">
                <td colspan="8" class="text-center py-8 text-slate-400">
                  Belum ada paket arsip ZIP yang dibuat. Klik tombol di atas untuk membuat paket arsip pertama.
                </td>
              </tr>
              <tr v-for="pkg in recentPackages" :key="pkg.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                  {{ pkg.package_code }}
                </td>
                <td class="py-3.5 px-4">
                  <div class="space-y-0.5">
                    <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">
                      {{ pkg.period_label || pkg.archive_period || '-' }}
                    </span>
                    <span v-if="pkg.period_date_range" class="text-[10px] text-slate-400 font-mono block">
                      {{ pkg.period_date_range }}
                    </span>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                  {{ pkg.total_applications }}
                </td>
                <td class="py-3.5 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                  {{ pkg.total_files }}
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-600 dark:text-slate-300">
                  {{ pkg.formatted_package_size }}
                </td>
                <td class="py-3.5 px-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide inline-flex items-center gap-1"
                    :class="{
                      'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': pkg.status === 'READY_FOR_DOWNLOAD',
                      'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': pkg.status === 'PROCESSING' || pkg.status === 'QUEUED',
                      'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300': pkg.status === 'INCOMPLETE' || pkg.status === 'FAILED' || pkg.status === 'CORRUPTED',
                    }"
                  >
                    {{ pkg.status_label }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <span
                    v-if="pkg.is_operator_confirmed"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 inline-flex items-center gap-1"
                  >
                    ✓ Terkonfirmasi Disimpan
                  </span>
                  <span
                    v-else
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 inline-flex items-center gap-1"
                  >
                    Belum Dikonfirmasi
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right space-x-2">
                  <a
                    v-if="pkg.status === 'READY_FOR_DOWNLOAD'"
                    :href="pkg.download_url"
                    class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs inline-flex items-center gap-1 shadow-sm transition"
                    title="Download ZIP"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                  </a>
                  <Link
                    :href="route('admin.archive.packages.show', pkg.id)"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs inline-flex items-center gap-1 transition"
                  >
                    Detail &rarr;
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
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
  stats: Object,
  recentPackages: Array,
  recentProblems: Array,
});
</script>
