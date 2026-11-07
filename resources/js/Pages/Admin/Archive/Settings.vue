<template>
  <Head title="Pengaturan Pengarsipan - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6 max-w-5xl">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-indigo-900/50">
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Konfigurasi Kebijakan & Adapter
          </div>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
            Pengaturan Pengarsipan
          </h1>
          <p class="text-xs sm:text-sm text-indigo-200 mt-1 max-w-2xl leading-relaxed">
            Pengaturan mode operasional, kebijakan retensi, kriteria status permohonan yang dapat diarsipkan, serta parameter koneksi ke server media penyimpanan permanen instansi.
          </p>
        </div>
      </div>

      <!-- Settings Form -->
      <form @submit.prevent="submitSettings" class="space-y-6">
        <!-- 1. Mode Operasional -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">1. Mode Operasional Pengarsipan</h2>
            <p class="text-xs text-slate-500">Pilih mode operasional yang diizinkan sesuai kesiapan infrastruktur instansi</p>
          </div>

          <div class="space-y-3">
            <!-- Mode A -->
            <label class="flex items-start gap-3 p-4 rounded-2xl border transition cursor-pointer" :class="form.operation_mode === 'ARSIP_SAJA' ? 'bg-blue-50/50 dark:bg-blue-950/20 border-blue-500 dark:border-blue-500' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
              <input type="radio" v-model="form.operation_mode" value="ARSIP_SAJA" class="mt-1 text-blue-600 focus:ring-blue-500" />
              <div>
                <span class="text-xs font-bold text-slate-900 dark:text-white block">Mode A — ARSIP_SAJA (Direkomendasikan Tahap Awal)</span>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">
                  Paket ZIP dibuat dan diunduh manual oleh operator arsip. Tidak ada penghapusan otomatis file sumber di hosting. Menjaga keselamatan seluruh dokumen.
                </p>
              </div>
            </label>

            <!-- Mode B -->
            <label class="flex items-start gap-3 p-4 rounded-2xl border transition cursor-pointer" :class="form.operation_mode === 'ARSIP_DAN_TRANSFER' ? 'bg-blue-50/50 dark:bg-blue-950/20 border-blue-500 dark:border-blue-500' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
              <input type="radio" v-model="form.operation_mode" value="ARSIP_DAN_TRANSFER" class="mt-1 text-blue-600 focus:ring-blue-500" />
              <div>
                <span class="text-xs font-bold text-slate-900 dark:text-white block">Mode B — ARSIP_DAN_TRANSFER (Integrasi Media Permanen)</span>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">
                  Paket ZIP dibuat dan otomatis/manual ditransfer ke media arsip instansi dengan verifikasi checksum SHA-256. File sumber hosting tetap dipertahankan.
                </p>
              </div>
            </label>

            <!-- Mode C (Locked) -->
            <div class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40 opacity-75">
              <input type="radio" disabled value="ARSIP_TRANSFER_DAN_PEMBERSIHAN" class="mt-1 text-slate-400 cursor-not-allowed" />
              <div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Mode C — ARSIP_TRANSFER_DAN_PEMBERSIHAN</span>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Terkunci / Nonaktif</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">
                  Mode pembersihan file sumber dinonaktifkan secara ketat pada tahap awal sampai prosedur pencadangan dan pemulihan arsip permanen instansi selesai diuji.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Kriteria Permohonan & Retensi -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">2. Kriteria Status & Kebijakan Retensi</h2>
            <p class="text-xs text-slate-500">Tentukan status permohonan yang sah diarsipkan dan batasan waktu retensi</p>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Status Permohonan yang Memenuhi Kriteria Pengarsipan:</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label v-for="(label, st) in allStatuses" :key="st" class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs cursor-pointer">
                  <input
                    type="checkbox"
                    :value="st"
                    v-model="form.eligible_statuses"
                    class="rounded text-blue-600 focus:ring-blue-500"
                  />
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ label }}</span>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Masa Retensi Arsip Digital (Hari)</label>
                <input
                  type="number"
                  v-model="form.retention_policy_days"
                  min="1"
                  max="3650"
                  class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-2.5"
                />
                <p class="text-[11px] text-slate-400 mt-1">Dihitung dalam hari (bebas ditentukan, minimal 1 hari)</p>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Masa Tenggang Pembersihan Aman (Hari)</label>
                <input
                  type="number"
                  v-model="form.cleaning_grace_period_days"
                  min="1"
                  max="1825"
                  class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-2.5"
                />
                <p class="text-[11px] text-slate-400 mt-1">Masa tenggang setelah konfirmasi / verifikasi penyimpanan permanen (minimal 1 hari)</p>
              </div>
            </div>

            <div class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
              <div class="space-y-0.5">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  Pembersihan Otomatis Arsip Berdasarkan Retensi
                </span>
                <p class="text-[11px] text-slate-500">
                  Paket arsip yang telah melewati batas masa retensi (<strong>{{ form.retention_policy_days }} hari</strong>) atau masa tenggang (<strong>{{ form.cleaning_grace_period_days }} hari</strong>) akan dihapus otomatis dari server lokal.
                </p>
              </div>
              <button
                type="button"
                @click="runManualCleanup"
                :disabled="isCleaning"
                class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5 shrink-0 cursor-pointer disabled:opacity-50"
              >
                <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isCleaning }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>{{ isCleaning ? 'Memproses...' : 'Jalankan Pembersihan Sekarang' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- 3. Konfigurasi Media Penyimpanan Permanen -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">3. Adapter & Konfigurasi Penyimpanan Permanen Instansi</h2>
            <p class="text-xs text-slate-500">Tentukan adapter penyimpanan tujuan pemindahan berkas ZIP</p>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Driver Penyimpanan Tujuan</label>
              <select
                v-model="form.permanent_storage_driver"
                class="w-full text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-2.5"
              >
                <option value="null">Belum Dikonfigurasi (Manual Offline / Unduh Lokal)</option>
                <option value="local_path">Local Mount Path / Secondary Disk / NAS Mounted</option>
                <option value="sftp">SFTP Server Instansi Disdukcapil (SSH File Transfer)</option>
              </select>
            </div>

            <!-- Parameters for local_path -->
            <div v-if="form.permanent_storage_driver === 'local_path'" class="space-y-3 p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700">
              <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Parameter Local Disk / Mount Path</h4>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Jalur Direktori Tujuan Absolut *</label>
                <input
                  v-model="form.permanent_storage_config.target_path"
                  type="text"
                  placeholder="Contoh: /mnt/arsip_dukcapil/ atau D:\ArsipPermanen\Dokumen"
                  class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5"
                />
              </div>
            </div>

            <!-- Parameters for sftp -->
            <div v-if="form.permanent_storage_driver === 'sftp'" class="space-y-3 p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700">
              <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Parameter Koneksi SFTP Server Instansi</h4>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                  <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Host SFTP *</label>
                  <input
                    v-model="form.permanent_storage_config.host"
                    type="text"
                    placeholder="192.168.1.xxx atau arsip.dukcapil.dompukab.go.id"
                    class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Port</label>
                  <input
                    v-model="form.permanent_storage_config.port"
                    type="number"
                    placeholder="22"
                    class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Username *</label>
                  <input
                    v-model="form.permanent_storage_config.username"
                    type="text"
                    placeholder="operator_arsip"
                    class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Password</label>
                  <input
                    v-model="form.permanent_storage_config.password"
                    type="password"
                    placeholder="••••••••"
                    class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5"
                  />
                </div>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Remote Target Path *</label>
                <input
                  v-model="form.permanent_storage_config.target_path"
                  type="text"
                  placeholder="/var/archive/dukcapil/ktpel/"
                  class="w-full text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <span v-if="isSubmitting">Menyimpan...</span>
            <span v-else>Simpan Pengaturan Pengarsipan</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  settings: Object,
  allStatuses: Object,
});

const form = ref({
  operation_mode: props.settings.operation_mode || 'ARSIP_SAJA',
  eligible_statuses: [...(props.settings.eligible_statuses || ['READY_FOR_PICKUP', 'COMPLETED'])],
  retention_policy_days: props.settings.retention_policy_days || 365,
  cleaning_grace_period_days: props.settings.cleaning_grace_period_days || 90,
  permanent_storage_driver: props.settings.permanent_storage_driver || 'null',
  permanent_storage_config: { ...(props.settings.permanent_storage_config || {}) },
});

const isSubmitting = ref(false);
const isCleaning = ref(false);

function submitSettings() {
  isSubmitting.value = true;
  router.post(route('admin.archive.settings.update'), form.value, {
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
}

function runManualCleanup() {
  if (!confirm('Jalankan pembersihan arsip sekarang sesuai masa retensi yang tersimpan? Paket arsip yang telah melewati masa retensi akan dihapus dari server.')) {
    return;
  }
  isCleaning.value = true;
  router.post(route('admin.archive.settings.cleanup-retention'), {}, {
    onFinish: () => {
      isCleaning.value = false;
    },
  });
}
</script>
