<template>
  <Head :title="`Paket ${package.package_code} - Disdukcapil Kab. Dompu`" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Breadcrumb Navigation -->
      <div class="flex items-center gap-2 text-xs text-slate-500">
        <Link :href="route('admin.archive.dashboard')" class="hover:underline">Dashboard Pengarsipan</Link>
        <span>/</span>
        <Link :href="route('admin.archive.packages.index')" class="hover:underline">Paket Arsip ZIP</Link>
        <span>/</span>
        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ package.package_code }}</span>
      </div>

      <!-- Header Card -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="px-3 py-1 rounded-full text-xs font-bold tracking-wide inline-flex items-center gap-1"
                :class="{
                  'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': package.status === 'READY_FOR_DOWNLOAD',
                  'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': package.status === 'PROCESSING' || package.status === 'QUEUED',
                  'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300': package.status === 'INCOMPLETE' || package.status === 'FAILED' || package.status === 'CORRUPTED',
                }"
              >
                {{ package.status_label }}
              </span>

              <span
                class="px-2.5 py-1 rounded-full text-xs font-semibold"
                :class="{
                  'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300': package.permanent_storage_status === 'NOT_CONFIGURED',
                  'bg-teal-100 text-teal-800': package.permanent_storage_status === 'PERMANENT_STORAGE_VERIFIED',
                  'bg-amber-100 text-amber-800': package.permanent_storage_status === 'PENDING_TRANSFER' || package.permanent_storage_status === 'TRANSFERRED',
                  'bg-rose-100 text-rose-800': package.permanent_storage_status === 'TRANSFER_FAILED' || package.permanent_storage_status === 'VERIFICATION_FAILED',
                }"
              >
                {{ package.permanent_storage_label }}
              </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-slate-900 dark:text-white">
              {{ package.package_code }}
            </h1>
            <p class="text-xs text-slate-500">
              Dibuat pada {{ new Date(package.created_at).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' }) }}
              <span v-if="package.creator"> oleh <strong class="text-slate-700 dark:text-slate-300">{{ package.creator.name }}</strong></span>
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-wrap items-center gap-2">
            <a
              v-if="package.status === 'READY_FOR_DOWNLOAD'"
              :href="package.download_url"
              class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 transition flex items-center gap-2 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              Download Arsip ZIP ({{ package.formatted_package_size }})
            </a>

            <button
              v-if="package.status === 'READY_FOR_DOWNLOAD'"
              @click="checkIntegrity"
              :disabled="isCheckingIntegrity"
              class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-bold text-xs shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              <span>{{ isCheckingIntegrity ? 'Menguji Hash...' : 'Uji Integritas Hash' }}</span>
            </button>

            <button
              v-if="package.status === 'READY_FOR_DOWNLOAD'"
              @click="showConfirmModal = true"
              class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm transition flex items-center gap-2 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>{{ package.is_operator_confirmed ? 'Ubah Catatan Konfirmasi' : 'Konfirmasi Arsip Tersimpan' }}</span>
            </button>
          </div>
        </div>

        <!-- SHA-256 Checksum Card -->
        <div class="bg-slate-50 dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
              <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              Checksum SHA-256 Resmi Paket ZIP
            </span>
            <button
              @click="copyChecksum"
              class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer"
            >
              <span v-if="copied">✓ Tersalin!</span>
              <span v-else>Salin Hash Checksum</span>
            </button>
          </div>
          <div class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200 break-all bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
            {{ package.sha256_checksum || 'Belum dihitung' }}
          </div>
          <p class="text-[11px] text-slate-400">
            Hash SHA-256 digunakan untuk memeriksa apakah berkas mengalami korupsi atau manipulasi setelah dibuat di server.
          </p>
        </div>

        <!-- 4 Summary Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
            <span class="text-slate-400 text-xs font-semibold block">Total Permohonan</span>
            <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ package.total_applications }}</span>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
            <span class="text-slate-400 text-xs font-semibold block">Total Berkas Dokumen</span>
            <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ package.total_files }}</span>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
            <span class="text-slate-400 text-xs font-semibold block">Ukuran Terkompresi (ZIP)</span>
            <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ package.formatted_package_size }}</span>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
            <span class="text-slate-400 text-xs font-semibold block">Ukuran Asli Dokumen</span>
            <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ package.formatted_uncompressed_size }}</span>
          </div>
        </div>

        <!-- Operator Confirmation Info Box -->
        <div v-if="package.is_operator_confirmed" class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 text-xs text-blue-900 dark:text-blue-200 space-y-1">
          <div class="flex items-center gap-2 font-bold text-sm text-blue-700 dark:text-blue-300">
            <span>✓ Arsip Terkonfirmasi Telah Disimpan oleh Operator</span>
          </div>
          <p>
            Dikonfirmasi oleh <strong>{{ package.confirmed_by_user?.name || 'Operator' }}</strong> pada {{ new Date(package.operator_confirmed_at).toLocaleString('id-ID') }}.
          </p>
          <p class="font-medium mt-1">
            <span class="text-blue-500">Lokasi Media Penyimpanan:</span> {{ package.operator_storage_location_notes }}
          </p>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 text-xs font-bold">
        <button
          @click="activeTab = 'manifest'"
          class="pb-3 px-3 transition border-b-2 cursor-pointer"
          :class="activeTab === 'manifest' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Manifest Berkas ZIP ({{ (package.manifest_data?.files || []).length }})
        </button>
        <button
          @click="activeTab = 'applications'"
          class="pb-3 px-3 transition border-b-2 cursor-pointer"
          :class="activeTab === 'applications' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Daftar Permohonan ({{ package.items.length }})
        </button>
        <button
          @click="activeTab = 'audit'"
          class="pb-3 px-3 transition border-b-2 cursor-pointer"
          :class="activeTab === 'audit' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Riwayat Audit Trail ({{ package.audit_logs.length }})
        </button>
      </div>

      <!-- Tab 1: Manifest Berkas -->
      <div v-if="activeTab === 'manifest'" class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Berkas Terarsip dalam ZIP</h3>
            <p class="text-xs text-slate-500">Struktur folder, nama berkas, ukuran, dan checksum SHA-256 masing-masing dokumen</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 uppercase tracking-wider font-bold border-y border-slate-100 dark:border-slate-700/60">
              <tr>
                <th class="py-3 px-4">Jalur Berkas Dalam ZIP</th>
                <th class="py-3 px-4">No. Registrasi</th>
                <th class="py-3 px-4">Nama Asli</th>
                <th class="py-3 px-4">Ukuran</th>
                <th class="py-3 px-4">SHA-256 Berkas</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-mono">
              <tr v-for="(file, idx) in package.manifest_data?.files || []" :key="idx" class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30">
                <td class="py-3 px-4 text-slate-900 dark:text-white font-bold">
                  {{ file.relative_path }}
                </td>
                <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                  {{ file.application_ref }}
                </td>
                <td class="py-3 px-4 text-slate-500 font-sans">
                  {{ file.original_filename }}
                </td>
                <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-sans">
                  {{ formatBytes(file.size_bytes) }}
                </td>
                <td class="py-3 px-4 text-[10px] text-slate-400 break-all max-w-[200px]" :title="file.sha256">
                  {{ file.sha256 }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab 2: Permohonan -->
      <div v-if="activeTab === 'applications'" class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Permohonan Tercakup dalam Paket</h3>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 uppercase tracking-wider font-bold border-y border-slate-100 dark:border-slate-700/60">
              <tr>
                <th class="py-3 px-4">No. Registrasi</th>
                <th class="py-3 px-4">Nama Pemohon</th>
                <th class="py-3 px-4">Jenis Layanan</th>
                <th class="py-3 px-4 text-center">Jumlah Dokumen</th>
                <th class="py-3 px-4">Ukuran Total</th>
                <th class="py-3 px-4">Status Item</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <tr v-for="item in package.items" :key="item.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30">
                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                  {{ item.reg_number }}
                </td>
                <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                  {{ item.applicant_name_masked }}
                </td>
                <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                  {{ item.application?.service_type?.name || 'KTP-el' }}
                </td>
                <td class="py-3 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                  {{ item.document_count }} berkas
                </td>
                <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                  {{ item.formatted_size }}
                </td>
                <td class="py-3 px-4">
                  <span
                    class="px-2 py-0.5 rounded text-[10px] font-bold"
                    :class="item.status === 'INCLUDED' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                  >
                    {{ item.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab 3: Audit Trail -->
      <div v-if="activeTab === 'audit'" class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Riwayat Audit Trail Pengarsipan</h3>

        <div class="space-y-3">
          <div v-if="package.audit_logs.length === 0" class="text-slate-400 text-xs py-4 text-center">
            Belum ada catatan log aktivitas.
          </div>
          <div
            v-for="log in package.audit_logs"
            :key="log.id"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs space-y-1"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-blue-600 dark:text-blue-400 font-mono">{{ log.action }}</span>
              <span class="text-[11px] text-slate-400">{{ new Date(log.created_at).toLocaleString('id-ID') }}</span>
            </div>
            <p class="text-slate-600 dark:text-slate-300">
              Oleh: <strong>{{ log.user?.name || 'Sistem' }}</strong> (IP: {{ log.ip_address || '-' }})
            </p>
            <pre v-if="log.details" class="p-2 bg-white dark:bg-slate-800 rounded-xl text-[10px] font-mono text-slate-600 dark:text-slate-300 overflow-x-auto">{{ JSON.stringify(log.details, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Storage Modal -->
    <div v-if="showConfirmModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-base font-bold text-slate-900 dark:text-white">Konfirmasi Arsip Telah Disimpan</h3>
          <button @click="showConfirmModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-2xl text-xs space-y-1.5 border border-slate-200 dark:border-slate-700">
          <p><span class="text-slate-400">Kode Paket:</span> <span class="font-mono font-bold">{{ package.package_code }}</span></p>
          <p><span class="text-slate-400">Ukuran:</span> <span class="font-bold">{{ package.formatted_package_size }}</span></p>
          <p><span class="text-slate-400">SHA-256:</span> <span class="font-mono text-[10px] break-all">{{ package.sha256_checksum }}</span></p>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            Lokasi Media Penyimpanan yang Digunakan *
          </label>
          <textarea
            v-model="confirmNotes"
            rows="3"
            placeholder="Contoh: Disimpan pada Harddisk Eksternal No. 02 Ruang Arsip, atau NAS Bidang PIAK"
            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-2xl border border-amber-200 dark:border-amber-900 text-[11px] text-amber-800 dark:text-amber-300">
          <strong>Perhatian:</strong> Konfirmasi manual ini merupakan pernyataan operator bahwa arsip telah diunduh dan diamankan pada media yang ditetapkan. Ini bukan bukti transfer atau verifikasi otomatis media permanen.
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button @click="showConfirmModal = false" class="px-4 py-2 text-xs font-bold border rounded-xl">Batal</button>
          <button @click="submitConfirm" :disabled="!confirmNotes.trim()" class="px-4 py-2 text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white rounded-xl disabled:opacity-50">
            Simpan Konfirmasi
          </button>
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
  package: Object,
});

const activeTab = ref('manifest');
const copied = ref(false);
const isCheckingIntegrity = ref(false);
const showConfirmModal = ref(false);
const confirmNotes = ref(props.package.operator_storage_location_notes || '');

function copyChecksum() {
  if (!props.package.sha256_checksum) return;
  navigator.clipboard.writeText(props.package.sha256_checksum);
  copied.value = true;
  setTimeout(() => { copied.value = false; }, 2500);
}

function checkIntegrity() {
  isCheckingIntegrity.value = true;
  router.post(route('admin.archive.packages.verify-integrity', props.package.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      isCheckingIntegrity.value = false;
    },
  });
}

function submitConfirm() {
  router.post(route('admin.archive.packages.confirm', props.package.id), {
    storage_location_notes: confirmNotes.value,
  }, {
    onSuccess: () => {
      showConfirmModal.value = false;
    },
  });
}

function formatBytes(bytes) {
  if (!bytes) return '0 B';
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(2) + ' MB';
}
</script>
