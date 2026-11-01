<template>
  <Head title="Status Penyimpanan Permanen - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-teal-900/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-300 border border-teal-400/30 mb-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7zm0 4h16M8 4v4m8-4v4"/></svg>
              Persiapan Integrasi Server Instansi
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Status Penyimpanan Permanen
            </h1>
            <p class="text-xs sm:text-sm text-teal-200 mt-1 max-w-2xl leading-relaxed">
              Pemantauan konektivitas dan kesiapan transfer paket arsip digital dari hosting website ke server penyimpanan permanen milik instansi (SFTP, SMB, atau NAS Lokal).
            </p>
          </div>

          <!-- Test Connection Button -->
          <button
            @click="testConnection"
            :disabled="isTesting"
            class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs shadow-md shadow-teal-600/30 transition flex items-center gap-2 cursor-pointer shrink-0 disabled:opacity-50"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>{{ isTesting ? 'Menguji Koneksi...' : 'Uji Koneksi Media Permanen' }}</span>
          </button>
        </div>
      </div>

      <!-- Current Storage Architecture Status Card -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-700 pb-5">
          <div class="flex items-center gap-3">
            <div
              class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
              :class="isConfigured ? 'bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400' : 'bg-slate-100 dark:bg-slate-900 text-slate-500'"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Media Penyimpanan Tujuan</h3>
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  :class="isConfigured ? 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                >
                  {{ isConfigured ? 'Terkonfigurasi' : 'Belum Dikonfigurasi' }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-0.5">
                Driver Aktif: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ driver }}</strong>
              </p>
            </div>
          </div>

          <Link
            v-if="$page.props.auth?.permissions?.includes('archive.settings.manage') || $page.props.auth?.roles?.includes('Super Administrator')"
            :href="route('admin.archive.settings.index')"
            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-xs font-bold text-slate-700 dark:text-slate-200 transition text-center"
          >
            Ubah Pengaturan Media Arsip &rarr;
          </Link>
        </div>

        <!-- Honest Information Box if NOT Configured -->
        <div v-if="!isConfigured" class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/60 text-xs text-amber-900 dark:text-amber-200 space-y-2">
          <div class="flex items-center gap-2 font-bold text-sm text-amber-800 dark:text-amber-300">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Media Penyimpanan Permanen Belum Dikonfigurasi</span>
          </div>
          <p class="leading-relaxed">
            Sesuai prinsip arsitektur, hosting website hanya merupakan tempat pemrosesan sementara. Karena server arsip permanen instansi belum dihubungkan, modul saat ini beroperasi penuh dalam <strong>Mode ARSIP_SAJA</strong>.
          </p>
          <ul class="list-disc list-inside space-y-1 text-amber-800/90 dark:text-amber-300/90 pl-1">
            <li>Paket ZIP dapat dibuat dan diunduh secara manual oleh Operator Arsip.</li>
            <li>Operator Arsip dapat mencatat konfirmasi lokasi penyimpanan offline yang digunakan.</li>
            <li>Status penyimpanan permanen tetap secara transparan berstatus <strong>BELUM_TERVERIFIKASI_DI_PENYIMPANAN_PERMANEN</strong>.</li>
            <li>Seluruh penghapusan otomatis file sumber di hosting <strong>dinonaktifkan 100%</strong>.</li>
          </ul>
        </div>

        <!-- Details if Configured -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
            <span class="text-slate-400 block font-semibold">Tipe Target</span>
            <span class="font-bold text-slate-800 dark:text-slate-200 mt-1 block uppercase">{{ driver }}</span>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
            <span class="text-slate-400 block font-semibold">Tujuan / Path</span>
            <span class="font-bold font-mono text-slate-800 dark:text-slate-200 mt-1 block truncate">{{ config.target_path || '-' }}</span>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
            <span class="text-slate-400 block font-semibold">Uji Koneksi Terakhir</span>
            <span class="font-bold text-slate-800 dark:text-slate-200 mt-1 block">
              {{ config.last_tested_at ? new Date(config.last_tested_at).toLocaleString('id-ID') : 'Belum diuji' }}
            </span>
          </div>
        </div>

        <!-- Connection Test Result Box -->
        <div v-if="testResult" class="p-4 rounded-2xl border text-xs" :class="testResult.success ? 'bg-teal-50 text-teal-800 border-teal-200' : 'bg-rose-50 text-rose-800 border-rose-200'">
          <div class="flex items-center gap-2 font-bold mb-1">
            <span>{{ testResult.success ? '✓ Hasil Uji Koneksi Berhasil' : '✕ Hasil Uji Koneksi Gagal' }}</span>
          </div>
          <p>{{ testResult.message }}</p>
        </div>
      </div>

      <!-- Packages Ready for Transfer Section -->
      <div v-if="readyForTransfer.length > 0" class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">Paket Arsip Menunggu Transfer Permanen</h3>
        <p class="text-xs text-slate-500">Paket yang telah siap di hosting namun belum ditransfer ke penyimpanan permanen instansi</p>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 uppercase tracking-wider font-bold border-y border-slate-100 dark:border-slate-700/60">
              <tr>
                <th class="py-3 px-4">Kode Paket</th>
                <th class="py-3 px-4">Ukuran</th>
                <th class="py-3 px-4">Status Media Permanen</th>
                <th class="py-3 px-4 text-right">Aksi Transfer</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-mono">
              <tr v-for="pkg in readyForTransfer" :key="pkg.id">
                <td class="py-3 px-4 font-bold text-blue-600 dark:text-blue-400">
                  <Link :href="route('admin.archive.packages.show', pkg.id)">{{ pkg.package_code }}</Link>
                </td>
                <td class="py-3 px-4 font-sans text-slate-600 dark:text-slate-300">
                  {{ pkg.formatted_package_size }}
                </td>
                <td class="py-3 px-4 font-sans">
                  <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600">
                    {{ pkg.permanent_storage_label }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right font-sans">
                  <button
                    v-if="isConfigured && ($page.props.auth?.permissions?.includes('archive.transfer.execute') || $page.props.auth?.roles?.includes('Super Administrator'))"
                    @click="executeTransfer(pkg.id)"
                    class="px-3 py-1.5 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer"
                  >
                    Transfer Sekarang &rarr;
                  </button>
                  <span v-else class="text-[11px] text-slate-400">Penyimpanan Belum Siap</span>
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
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';

const props = defineProps({
  driver: String,
  config: Object,
  isConfigured: Boolean,
  packages: Object,
  readyForTransfer: Array,
});

const isTesting = ref(false);
const testResult = ref(props.config.last_test_result || null);

async function testConnection() {
  isTesting.value = true;
  try {
    const res = await axios.post(route('admin.archive.permanent-storage.test'));
    testResult.value = res.data;
  } catch (err) {
    testResult.value = {
      success: false,
      message: err.response?.data?.message || 'Gagal menghubungi server tujuan.',
    };
  } finally {
    isTesting.value = false;
  }
}

function executeTransfer(pkgId) {
  if (!confirm('Transfer paket arsip ke penyimpanan permanen instansi?')) return;
  router.post(route('admin.archive.permanent-storage.transfer', pkgId));
}
</script>
