<template>
  <div class="fixed inset-0 z-50 overflow-hidden bg-slate-950/90 backdrop-blur-md flex flex-col">
    <!-- Top Bar Controls -->
    <div class="h-14 bg-slate-900/90 border-b border-slate-800 px-4 sm:px-6 flex items-center justify-between z-20 shrink-0">
      <div class="flex items-center gap-3 min-w-0">
        <h4 class="text-white text-sm font-semibold truncate">{{ title || 'Pratinjau Dokumen' }}</h4>
        <span v-if="isImage" class="text-xs text-slate-400 font-mono hidden sm:inline">
          Zoom: {{ Math.round(zoomLevel * 100) }}% | Rotasi: {{ rotation }}°
        </span>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Zoom Controls (Images only) -->
        <template v-if="isImage">
          <button
            @click="zoomIn"
            class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
            title="Perbesar (Zoom In)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          </button>
          <button
            @click="zoomOut"
            class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
            title="Perkecil (Zoom Out)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
          </button>
          <button
            @click="rotateCW"
            class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
            title="Putar Searah Jarum Jam (Rotate CW)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </button>
          <button
            @click="resetTransform"
            class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
            title="Reset Tampilan"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
          </button>
        </template>

        <!-- Direct Download -->
        <a
          v-if="resolvedSrc"
          :href="downloadUrl"
          download
          class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
          title="Unduh Berkas"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        </a>

        <!-- Close -->
        <button
          @click="$emit('close')"
          class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition ml-2"
          title="Tutup (Esc)"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <!-- Viewer Canvas Area -->
    <div
      class="flex-1 overflow-hidden relative flex items-center justify-center p-4 cursor-grab active:cursor-grabbing select-none"
      @mousedown="startPan"
      @mousemove="pan"
      @mouseup="stopPan"
      @mouseleave="stopPan"
      @wheel.prevent="handleWheel"
    >
      <!-- Image Viewer -->
      <img
        v-if="isImage"
        :src="resolvedSrc"
        :alt="title || 'Dokumen'"
        class="max-w-full max-h-full object-contain transition-transform duration-75 shadow-2xl"
        :style="{
          transform: `translate(${panX}px, ${panY}px) scale(${zoomLevel}) rotate(${rotation}deg)`
        }"
        draggable="false"
      />

      <!-- PDF Viewer (iframe) -->
      <iframe
        v-else
        :src="resolvedSrc"
        class="w-full h-full rounded-xl border border-slate-800 bg-white shadow-2xl"
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  src: {
    type: String,
    default: '',
  },
  url: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: '',
  },
  mimeType: {
    type: String,
    default: 'image/jpeg',
  },
});

defineEmits(['close']);

const resolvedSrc = computed(() => props.src || props.url || '');

const zoomLevel = ref(1);
const rotation = ref(0);
const panX = ref(0);
const panY = ref(0);
const isPanning = ref(false);
const startX = ref(0);
const startY = ref(0);

const isImage = computed(() => {
  if (props.mimeType && props.mimeType.includes('image')) return true;
  if (props.mimeType && props.mimeType.includes('pdf')) return false;
  if (!resolvedSrc.value) return true;
  return !resolvedSrc.value.toLowerCase().includes('.pdf');
});

const downloadUrl = computed(() => {
  if (!resolvedSrc.value) return '#';
  return resolvedSrc.value + (resolvedSrc.value.includes('?') ? '&' : '?') + 'download=1';
});

function zoomIn() {
  if (zoomLevel.value < 4) zoomLevel.value = Math.min(4, zoomLevel.value + 0.25);
}

function zoomOut() {
  if (zoomLevel.value > 0.5) zoomLevel.value = Math.max(0.5, zoomLevel.value - 0.25);
}

function rotateCW() {
  rotation.value = (rotation.value + 90) % 360;
}

function resetTransform() {
  zoomLevel.value = 1;
  rotation.value = 0;
  panX.value = 0;
  panY.value = 0;
}

function handleWheel(e) {
  if (!isImage.value) return;
  if (e.deltaY < 0) {
    zoomIn();
  } else {
    zoomOut();
  }
}

function startPan(e) {
  if (!isImage.value) return;
  isPanning.value = true;
  startX.value = e.clientX - panX.value;
  startY.value = e.clientY - panY.value;
}

function pan(e) {
  if (!isPanning.value) return;
  panX.value = e.clientX - startX.value;
  panY.value = e.clientY - startY.value;
}

function stopPan() {
  isPanning.value = false;
}

// Esc key close listener
function handleKeyDown(e) {
  if (e.key === 'Escape') {
    // Handled by emit
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>
