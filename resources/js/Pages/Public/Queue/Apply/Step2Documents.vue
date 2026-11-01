<template>
  <Head title="Langkah 2: Unggah Persyaratan Digital - Antrian Online KTP-el" />

  <PublicLayout>
    <div class="py-8 sm:py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Wizard Progress -->
      <ProgressWizard :current-step="2" />

      <!-- Registration Banner -->
      <div class="mb-6 bg-gradient-to-r from-blue-900 to-indigo-900 rounded-3xl p-5 sm:p-6 text-white shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-blue-700/50">
        <div>
          <span class="text-[11px] font-semibold text-blue-300 uppercase tracking-wider block">
            Nomor Registrasi Sementara:
          </span>
          <span class="text-2xl sm:text-3xl font-black font-mono tracking-wider text-white">
            {{ application.reg_number }}
          </span>
          <p class="text-xs text-blue-200 mt-1">
            Layanan: <strong>{{ serviceType.name }}</strong> · Pemohon: <strong>{{ application.applicant_name }}</strong>
          </p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-blue-500/20 text-blue-200 border border-blue-400/30">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              Kehadiran: {{ attendanceLabel }}
            </span>
            <span v-if="application.attendance_type !== 'self'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-500/20 text-amber-200 border border-amber-400/30">
              Perwakilan: {{ application.representative_name }} ({{ application.representative_relationship }})
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-400/20 text-amber-200 border border-amber-400/30" title="Draft yang tidak diselesaikan dalam 1x24 jam sejak dibuat akan dihapus otomatis">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Batas Sesi: 1x24 Jam
            </span>
          </div>
        </div>

        <div class="px-4 py-2 rounded-2xl bg-white/10 backdrop-blur border border-white/20 text-xs text-blue-200">
          Kelengkapan Berkas:
          <span class="font-bold text-white ml-1">
            {{ uploadedCount }} / {{ requirements.length }} Dokumen
          </span>
        </div>
      </div>

      <!-- Main Upload Workspace -->
      <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-md">
          <div class="mb-6">
            <h3 class="text-xl font-black text-slate-900 dark:text-white">
              Foto Dokumen Persyaratan Langsung
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
              Seluruh dokumen persyaratan wajib difoto langsung menggunakan kamera handphone/perangkat Anda. Dokumen fisik akan otomatis dioptimasi dan dikompresi oleh sistem tanpa mengurangi keterbacaan teks demi kecepatan verifikasi.
            </p>
          </div>

          <!-- Document Cards Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="req in requirements"
              :key="req.id"
              :id="'doc_card_' + req.id"
              class="transition-all rounded-3xl"
            >
              <DocumentUploadCard
                :requirement="req"
                :uploaded-doc="getUploadedDoc(req.id)"
                :can-edit="true"
                @open-camera="openCameraModal(req)"
                @preview="openPreviewModal"
                @delete="handleDeleteDocument"
              />
            </div>
          </div>
        </div>

        <!-- Statement of Integrity & Physical Verification Agreement (Tahap 5) -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-blue-200 dark:border-blue-900 shadow-md relative overflow-hidden">
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950/80 text-blue-600 flex items-center justify-center shrink-0 mt-0.5 font-bold text-sm">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-900 dark:text-white">
                Pernyataan Pemohon & Integritas Dokumen
              </h4>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Harap baca dan setujui pernyataan kepatuhan berikut sebelum mengirimkan berkas pengajuan Anda ke meja verifikasi.
              </p>
            </div>
          </div>

          <div class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 space-y-2 leading-relaxed">
            <p>
              1. <strong>Keabsahan Data:</strong> Seluruh data identitas dan foto dokumen yang saya unggah adalah benar, sah, dan sesuai dengan dokumen aslinya yang diterbitkan oleh instansi berwenang.
            </p>
            <p>
              2. <strong>Bukan Pengganti Verifikasi Fisik:</strong> Saya memahami sepenuhnya bahwa verifikasi online ini <em>HANYA</em> merupakan pemeriksaan administrasi awal, dan <em>BUKAN</em> pengganti pemeriksaan identitas serta dokumen fisik di kantor.
            </p>
            <p>
              3. <strong>Kewajiban Kehadiran Fisik:</strong> Saya atau perwakilan yang telah ditunjuk berkomitmen untuk hadir langsung ke Kantor Disdukcapil Kabupaten Dompu sesuai jadwal yang telah ditentukan dengan membawa seluruh dokumen fisik asli untuk diverifikasi oleh petugas di loket.
            </p>
            <p>
              4. <strong>Konsekuensi Hukum:</strong> Apabila di kemudian hari ditemukan ketidaksesuaian data, dokumen palsu, atau pemalsuan identitas, saya bersedia menerima pembatalan permohonan dan dituntut sesuai dengan peraturan perundang-undangan yang berlaku.
            </p>
          </div>

          <div class="mt-4 flex items-start gap-3">
            <input
              id="statement_checkbox"
              v-model="statementAgreed"
              type="checkbox"
              class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 mt-0.5 cursor-pointer"
            />
            <label for="statement_checkbox" class="text-xs font-semibold text-slate-800 dark:text-slate-200 cursor-pointer select-none">
              Saya telah membaca, memahami, dan menyetujui seluruh ketentuan dan pernyataan di atas dengan penuh tanggung jawab. <span class="text-rose-500">*</span>
            </label>
          </div>
        </div>

        <!-- Validation Error Alert if any -->
        <div
          v-if="errorMessage"
          class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-200 flex items-start gap-2.5"
        >
          <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p class="font-medium leading-relaxed">{{ errorMessage }}</p>
        </div>

        <!-- Submission Actions Card -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div
              :class="[
                'w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 font-bold text-sm',
                canSubmit
                  ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600'
                  : 'bg-amber-100 dark:bg-amber-950/60 text-amber-600'
              ]"
            >
              <svg v-if="canSubmit" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              <span v-else>!</span>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-900 dark:text-white">
                {{ canSubmit ? 'Semua Berkas & Pernyataan Lengkap' : 'Lengkapi Berkas & Centang Pernyataan' }}
              </p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                {{ canSubmit ? 'Klik tombol di samping untuk mengirimkan berkas Anda ke meja verifikator.' : 'Pastikan seluruh dokumen wajib terunggah dan pernyataan pemohon telah dicentang.' }}
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="handleSubmitApplication"
            :disabled="isSubmitting"
            :class="[
              'w-full sm:w-auto px-8 py-3.5 rounded-2xl text-xs font-bold transition shadow-lg flex items-center justify-center gap-2 cursor-pointer',
              canSubmit && !isSubmitting
                ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/30 hover:scale-[1.02]'
                : 'bg-blue-600 hover:bg-blue-500 text-white shadow-blue-600/30'
            ]"
          >
            <span>{{ isSubmitting ? 'Mengirim Pendaftaran...' : 'Kirim Berkas untuk Pra-Verifikasi' }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Popup Notifikasi Dokumen Belum Lengkap -->
    <div
      v-if="showIncompleteDocsModal"
      class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full shadow-2xl border border-rose-200 dark:border-rose-900/50 overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="p-5 sm:p-6 bg-gradient-to-r from-rose-500/10 via-amber-500/10 to-transparent border-b border-rose-100 dark:border-rose-900/40 flex items-start gap-3.5">
          <div class="w-12 h-12 rounded-2xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
              Dokumen Persyaratan Belum Lengkap!
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
              Anda belum dapat mengirimkan pendaftaran karena masih ada persyaratan wajib yang belum diunggah atau belum dicentang:
            </p>
          </div>
          <button
            type="button"
            @click="showIncompleteDocsModal = false"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Modal Body: Missing Items List -->
        <div class="p-4 sm:p-6 overflow-y-auto space-y-2.5 max-h-[52vh]">
          <div
            v-for="(item, idx) in missingDocsList"
            :key="idx"
            @click="scrollToDoc(item.elementId)"
            class="p-3.5 rounded-2xl bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 hover:bg-rose-100/90 dark:hover:bg-rose-950/60 cursor-pointer transition flex items-start gap-3 group shadow-xs"
          >
            <span class="w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
              {{ idx + 1 }}
            </span>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2">
                <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition truncate">
                  {{ item.label }}
                </h4>
                <span class="text-[10px] text-rose-600 dark:text-rose-400 font-bold group-hover:underline flex items-center gap-0.5 shrink-0">
                  Foto / Unggah &rarr;
                </span>
              </div>
              <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5 leading-snug">
                {{ item.message }}
              </p>
            </div>
          </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
          <button
            type="button"
            @click="showIncompleteDocsModal = false"
            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
          >
            Tutup
          </button>

          <button
            type="button"
            @click="scrollToDoc(missingDocsList[0]?.elementId)"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span>Lengkapi Berkas Sekarang</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Camera Capture Modal -->
    <CameraCapture
      v-if="activeCameraReq"
      :document-label="activeCameraReq.label"
      :capture-mode="activeCameraReq.capture_mode"
      :capture-hint="activeCameraReq.capture_hint"
      :accepted-formats="activeCameraReq.accepted_formats"
      @close="activeCameraReq = null"
      @captured="handleCameraPhotoCaptured"
    />

    <!-- Document Viewer Modal -->
    <DocumentViewerModal
      v-if="activePreviewDoc"
      :src="activePreviewDoc.preview_url"
      :title="activePreviewDoc.original_filename"
      :mime-type="activePreviewDoc.mime_type"
      @close="activePreviewDoc = null"
    />

    <!-- Batch Compression & Upload Progress Modal -->
    <div
      v-if="uploadProgress.active"
      class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 text-center space-y-5">
        <div class="w-16 h-16 mx-auto rounded-3xl bg-blue-100 dark:bg-blue-950/80 text-blue-600 flex items-center justify-center relative">
          <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <div>
          <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
            {{ uploadProgress.step === 'compressing' ? 'Mengompresi Berkas Dokumen...' : (uploadProgress.step === 'uploading' ? 'Mengunggah ke Server...' : 'Memproses Pra-Verifikasi...') }}
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
            {{ uploadProgress.label }}
          </p>
        </div>

        <!-- Progress Bar -->
        <div class="space-y-2">
          <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200 dark:border-slate-700">
            <div
              class="bg-gradient-to-r from-blue-600 to-indigo-600 h-full rounded-full transition-all duration-300"
              :style="{ width: `${uploadProgress.percent}%` }"
            ></div>
          </div>
          <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400">
            <span>{{ uploadProgress.step === 'compressing' ? 'Tahap 1: Kompresi Gambar' : (uploadProgress.step === 'uploading' ? 'Tahap 2: Pengiriman Berkas' : 'Tahap 3: Finalisasi') }}</span>
            <span class="font-mono">{{ uploadProgress.percent }}%</span>
          </div>
        </div>

        <p class="text-[11px] text-slate-400 dark:text-slate-500 italic">
          Mohon jangan menutup halaman ini selama proses kompresi dan pengiriman berlangsung.
        </p>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ProgressWizard from '@/Components/Queue/ProgressWizard.vue';
import DocumentUploadCard from '@/Components/Queue/DocumentUploadCard.vue';
import CameraCapture from '@/Components/Queue/CameraCapture.vue';
import DocumentViewerModal from '@/Components/Queue/DocumentViewerModal.vue';
import { compressImage, formatFileSize } from '@/Components/Queue/ImageCompressor';

const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
  serviceType: {
    type: Object,
    required: true,
  },
  requirements: {
    type: Array,
    default: () => [],
  },
  uploadedDocs: {
    type: Array,
    default: () => [],
  },
});

const docs = ref([...props.uploadedDocs]);
const stagedFiles = ref({});
const statementAgreed = ref(false);
const activeCameraReq = ref(null);
const activePreviewDoc = ref(null);
const errorMessage = ref(null);
const isSubmitting = ref(false);
const uploadProgress = ref({
  active: false,
  step: 'compressing',
  current: 0,
  total: 0,
  label: '',
  percent: 0,
});

const uploadedCount = computed(() => {
  const ids = new Set();
  docs.value.forEach((d) => ids.add(d.service_document_id));
  Object.keys(stagedFiles.value).forEach((id) => ids.add(Number(id)));
  return ids.size;
});

const allRequiredUploaded = computed(() => {
  const required = props.requirements.filter((r) => r.is_required);
  const serverIds = new Set(docs.value.map((d) => d.service_document_id));
  const stagedIds = new Set(Object.keys(stagedFiles.value).map(Number));
  return required.every((r) => serverIds.has(r.id) || stagedIds.has(r.id));
});

const canSubmit = computed(() => {
  return allRequiredUploaded.value && statementAgreed.value;
});

const attendanceLabel = computed(() => {
  const map = {
    self: 'Saya Sendiri (Pemilik Dokumen)',
    family_in_kk: 'Keluarga (Satu KK)',
    family_out_kk: 'Keluarga (Beda KK)',
    other: 'Orang Lain (Penerima Kuasa)',
  };
  return map[props.application.attendance_type] || props.application.attendance_type;
});

function getUploadedDoc(serviceDocId) {
  // If staged in memory, return staged object for instant UI feedback
  if (stagedFiles.value[serviceDocId]) {
    return stagedFiles.value[serviceDocId];
  }
  // Otherwise check if persisted on server
  return docs.value.find((d) => d.service_document_id === serviceDocId) || null;
}

function openCameraModal(req) {
  errorMessage.value = null;
  activeCameraReq.value = req;
}

function handleCameraPhotoCaptured(rawFile) {
  if (!activeCameraReq.value) return;
  const req = activeCameraReq.value;
  activeCameraReq.value = null;

  // Revoke previous object URL if any
  if (stagedFiles.value[req.id]?.preview_url) {
    URL.revokeObjectURL(stagedFiles.value[req.id].preview_url);
  }

  const previewUrl = URL.createObjectURL(rawFile);
  stagedFiles.value[req.id] = {
    id: 'staged_' + req.id,
    service_document_id: req.id,
    original_filename: rawFile.name || `foto-${req.label.toLowerCase().replace(/[^a-z0-9]/g, '_')}.jpg`,
    formatted_size: formatFileSize(rawFile.size),
    is_image: rawFile.type ? rawFile.type.startsWith('image/') : true,
    preview_url: previewUrl,
    status: 'STAGED',
    is_staged: true,
    file: rawFile,
  };
}

async function handleDeleteDocument(doc) {
  // If staged in memory, delete directly from memory
  if (doc.is_staged) {
    if (doc.preview_url) {
      URL.revokeObjectURL(doc.preview_url);
    }
    delete stagedFiles.value[doc.service_document_id];
    return;
  }

  // If already on server
  if (!confirm(`Hapus dokumen "${doc.original_filename}"?`)) return;

  try {
    await axios.delete(
      route('queue.apply.remove-doc', {
        regNumber: props.application.reg_number,
        documentId: doc.id,
      })
    );

    docs.value = docs.value.filter((d) => d.id !== doc.id);
  } catch (err) {
    if (err.response?.status === 419 || err.response?.data?.session_expired) {
      alert('Sesi draft permohonan telah kedaluwarsa (1x24 jam). Sesi telah ditutup otomatis.');
      window.location.href = route('queue.apply.create');
      return;
    }
    alert('Gagal menghapus dokumen.');
  }
}

function openPreviewModal(doc) {
  if (doc.is_staged) {
    activePreviewDoc.value = {
      preview_url: doc.preview_url,
      original_filename: doc.original_filename,
      mime_type: doc.file?.type || 'image/jpeg',
    };
    return;
  }

  activePreviewDoc.value = {
    ...doc,
    preview_url: route('queue.apply.preview-doc', {
      regNumber: props.application.reg_number,
      documentId: doc.id,
    }),
  };
}

const showIncompleteDocsModal = ref(false);

const missingDocsList = computed(() => {
  const list = [];
  const required = props.requirements.filter((r) => r.is_required);
  const serverIds = new Set(docs.value.map((d) => d.service_document_id));
  const stagedIds = new Set(Object.keys(stagedFiles.value).map(Number));

  required.forEach((r) => {
    if (!serverIds.has(r.id) && !stagedIds.has(r.id)) {
      list.push({
        label: r.label,
        message: 'Dokumen wajib ini belum difoto / diunggah.',
        elementId: 'doc_card_' + r.id,
      });
    }
  });

  if (!statementAgreed.value) {
    list.push({
      label: 'Pernyataan Keabsahan Dokumen',
      message: 'Anda belum mencentang kotak persetujuan pernyataan keabsahan di bawah formulir.',
      elementId: 'statement_checkbox',
    });
  }

  return list;
});

function scrollToDoc(elementId) {
  showIncompleteDocsModal.value = false;
  setTimeout(() => {
    const el = document.getElementById(elementId);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      el.classList.add('ring-4', 'ring-rose-500/60', 'transition-all');
      if (typeof el.focus === 'function') {
        el.focus();
      }
      setTimeout(() => {
        el.classList.remove('ring-4', 'ring-rose-500/60');
      }, 3000);
    }
  }, 150);
}

async function handleSubmitApplication() {
  if (missingDocsList.value.length > 0) {
    errorMessage.value = 'Harap lengkapi semua dokumen bertanda Wajib dan centang pernyataan sebelum mengirimkan pendaftaran.';
    showIncompleteDocsModal.value = true;
    return;
  }

  const stagedEntries = Object.entries(stagedFiles.value);
  const totalStaged = stagedEntries.length;

  isSubmitting.value = true;
  errorMessage.value = null;

  if (totalStaged > 0) {
    uploadProgress.value = {
      active: true,
      step: 'compressing',
      current: 0,
      total: totalStaged,
      label: 'Mempersiapkan kompresi dokumen di memori...',
      percent: 5,
    };

    const compressedItems = [];

    // 1. Batch Compression in memory
    for (let i = 0; i < totalStaged; i++) {
      const [reqIdStr, staged] = stagedEntries[i];
      const reqId = Number(reqIdStr);
      const req = props.requirements.find((r) => r.id === reqId);
      const label = req ? req.label : `Dokumen #${i + 1}`;

      uploadProgress.value = {
        active: true,
        step: 'compressing',
        current: i + 1,
        total: totalStaged,
        label: `Mengompresi berkas ${i + 1} dari ${totalStaged}: ${label}`,
        percent: Math.round(5 + ((i + 1) / totalStaged) * 45), // 5% -> 50%
      };

      try {
        const result = await compressImage(staged.file);
        compressedItems.push({
          service_document_id: reqId,
          file: result.file,
          label,
        });
      } catch (compErr) {
        console.warn('Compress fallback to original file:', compErr);
        compressedItems.push({
          service_document_id: reqId,
          file: staged.file,
          label,
        });
      }
    }

    // 2. Batch Upload to server
    uploadProgress.value = {
      active: true,
      step: 'uploading',
      current: 0,
      total: totalStaged,
      label: 'Mengunggah seluruh berkas ke server Disdukcapil...',
      percent: 52,
    };

    for (let i = 0; i < compressedItems.length; i++) {
      const item = compressedItems[i];

      uploadProgress.value = {
        active: true,
        step: 'uploading',
        current: i + 1,
        total: totalStaged,
        label: `Mengunggah berkas ${i + 1} dari ${totalStaged}: ${item.label}`,
        percent: Math.round(52 + ((i + 1) / totalStaged) * 44), // 52% -> 96%
      };

      const formData = new FormData();
      formData.append('service_document_id', item.service_document_id);
      formData.append('file', item.file);

      try {
        const res = await axios.post(
          route('queue.apply.upload-doc', { regNumber: props.application.reg_number }),
          formData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        if (res.data && res.data.document) {
          docs.value = docs.value.filter((d) => d.service_document_id !== item.service_document_id);
          docs.value.push(res.data.document);
        }
      } catch (uploadErr) {
        isSubmitting.value = false;
        uploadProgress.value.active = false;
        if (uploadErr.response?.status === 419 || uploadErr.response?.data?.session_expired) {
          alert('Sesi draft permohonan telah kedaluwarsa (1x24 jam) dan dihapus otomatis. Sesi telah ditutup.');
          window.location.href = route('queue.apply.create');
          return;
        }
        errorMessage.value =
          uploadErr.response?.data?.message ||
          `Gagal mengunggah berkas "${item.label}". Pastikan koneksi internet stabil dan coba lagi.`;
        return;
      }
    }
  }

  // 3. Final submission
  uploadProgress.value = {
    active: true,
    step: 'submitting',
    current: totalStaged,
    total: totalStaged,
    label: 'Memfinalisasi pendaftaran antrean Anda...',
    percent: 98,
  };

  router.post(
    route('queue.apply.submit', { regNumber: props.application.reg_number }),
    { statement_agreed: true },
    {
      onFinish: () => {
        isSubmitting.value = false;
        uploadProgress.value.active = false;
      },
      onError: (errors) => {
        isSubmitting.value = false;
        uploadProgress.value.active = false;
        errorMessage.value =
          errors.statement_agreed || Object.values(errors)[0] || 'Terjadi kesalahan saat mengirim pendaftaran.';
      },
    }
  );
}
</script>
