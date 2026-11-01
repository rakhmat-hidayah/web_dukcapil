<template>
  <div
    :class="[
      'rounded-3xl p-5 sm:p-6 transition-all duration-200 border flex flex-col justify-between relative overflow-hidden',
      isUploaded
        ? isRevision
          ? 'bg-orange-50/50 dark:bg-orange-950/20 border-orange-200 dark:border-orange-800'
          : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm'
        : 'bg-white dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-700'
    ]"
  >
    <!-- Card Header -->
    <div>
      <div class="flex items-start justify-between gap-3 mb-2">
        <div class="flex items-center gap-2 flex-wrap">
          <span
            v-if="requirement.is_required"
            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900"
          >
            Wajib
          </span>
          <span
            v-else
            class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold tracking-wide uppercase bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300"
          >
            Opsional
          </span>

          <span
            v-if="requirement.capture_mode === 'user'"
            class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300"
          >
            Foto Selfie
          </span>
        </div>

        <!-- Status Tag if uploaded -->
        <StatusBadge
          v-if="uploadedDoc"
          :status="uploadedDoc.status"
          size="sm"
        />
        <span
          v-else
          class="text-[11px] font-medium text-slate-400 dark:text-slate-500 flex items-center gap-1"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
          Belum Diunggah
        </span>
      </div>

      <h4 class="font-bold text-slate-900 dark:text-white text-base leading-snug">
        {{ requirement.label }}
      </h4>

      <p v-if="requirement.description" class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
        {{ requirement.description }}
      </p>

      <!-- Reviewer Revision Note if returned -->
      <div
        v-if="uploadedDoc && uploadedDoc.reviewer_note"
        class="mt-3 p-3 bg-orange-100/70 dark:bg-orange-950/50 rounded-2xl border border-orange-200 dark:border-orange-800 text-xs text-orange-900 dark:text-orange-200"
      >
        <span class="font-bold block mb-0.5">Catatan Verifikator:</span>
        <p class="leading-relaxed">{{ uploadedDoc.reviewer_note }}</p>
      </div>

      <!-- Download Template Link if available (Formulir F-1.02 & F-1.07 Surat Kuasa) -->
      <div v-if="downloadUrl" class="mt-3.5">
        <a
          :href="downloadUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs font-semibold hover:bg-amber-100 dark:hover:bg-amber-900/50 hover:border-amber-300 dark:hover:border-amber-700 transition shadow-sm group"
        >
          <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 group-hover:translate-y-0.5 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          <span>Unduh Blanko {{ downloadFormLabel }}</span>
          <svg class="w-3.5 h-3.5 text-amber-600/70 dark:text-amber-400/70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
          </svg>
        </a>
      </div>

      <!-- Capture Hint -->
      <p
        v-if="requirement.capture_hint"
        class="text-[11px] text-blue-600 dark:text-blue-400 mt-2 flex items-center gap-1.5"
      >
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="italic">{{ requirement.capture_hint }}</span>
      </p>
    </div>

    <!-- Uploaded File Details / Trigger Actions -->
    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/60">
      <!-- State 1: File is already uploaded -->
      <div v-if="uploadedDoc" class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
            <svg v-if="uploadedDoc.is_image" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <svg v-else class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          </div>
          <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">
              {{ uploadedDoc.original_filename }}
            </p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">
              {{ uploadedDoc.formatted_size }}
              <span v-if="uploadedDoc.is_compressed" class="text-emerald-600 dark:text-emerald-400 font-medium ml-1">· Terkompresi</span>
            </p>
          </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
          <!-- Preview Button -->
          <button
            type="button"
            @click="$emit('preview', uploadedDoc)"
            class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
            title="Pratinjau Dokumen"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>

          <!-- Replace / Retake Button -->
          <button
            v-if="canEdit"
            type="button"
            @click="$emit('open-camera', requirement)"
            class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
            title="Ganti / Foto Ulang"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </button>

          <!-- Delete Button -->
          <button
            v-if="canEdit"
            type="button"
            @click="$emit('delete', uploadedDoc)"
            class="p-2 rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
            title="Hapus Dokumen"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>
      </div>

      <!-- State 2: Document not yet uploaded -->
      <div v-else>
        <!-- Direct Camera Capture Trigger -->
        <button
          type="button"
          @click="$emit('open-camera', requirement)"
          class="w-full px-4 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 active:scale-[0.99] text-white text-xs font-bold shadow-md shadow-blue-600/20 transition flex items-center justify-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          {{ requirement.capture_mode === 'user' ? 'Ambil Foto Selfie Wajah' : 'Buka Kamera & Foto Dokumen' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  requirement: {
    type: Object,
    required: true,
  },
  uploadedDoc: {
    type: Object,
    default: null,
  },
  canEdit: {
    type: Boolean,
    default: true,
  },
});

defineEmits(['open-camera', 'preview', 'delete']);

const isUploaded = computed(() => !!props.uploadedDoc);
const isRevision = computed(() => props.uploadedDoc && (props.uploadedDoc.status === 'REVISION_REQUIRED' || props.uploadedDoc.status === 'REJECTED'));

const downloadUrl = computed(() => {
  if (props.requirement.template_url) {
    return props.requirement.template_url;
  }
  const label = (props.requirement.label || '').toLowerCase();
  if (label.includes('f-1.06') || label.includes('f1.06') || label.includes('perubahan elemen')) {
    return 'https://dukcapil.dompukab.go.id/downloads';
  }
  if (label.includes('f-1.02') || label.includes('f1.02') || label.includes('pendaftaran peristiwa')) {
    return 'https://dukcapil.dompukab.go.id/downloads';
  }
  if (label.includes('kuasa') || label.includes('f-1.07') || label.includes('f.017')) {
    return 'https://dukcapil.dompukab.go.id/downloads';
  }
  return null;
});

const downloadFormLabel = computed(() => {
  const label = (props.requirement.label || '').toLowerCase();
  if (label.includes('f-1.06') || label.includes('f1.06') || label.includes('perubahan elemen')) {
    return 'Formulir F-1.06';
  }
  if (label.includes('f-1.02') || label.includes('f1.02') || label.includes('pendaftaran peristiwa')) {
    return 'Formulir F-1.02';
  }
  if (label.includes('kuasa') || label.includes('f-1.07') || label.includes('f.017')) {
    return 'Formulir Surat Kuasa F-1.07';
  }
  return 'Formulir Resmi';
});
</script>
