<template>
  <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/90 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col max-h-[92vh]">
      <!-- Header -->
      <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div>
          <h3 class="font-bold text-slate-900 dark:text-white text-base">
            {{ isSelfie ? 'Foto Wajah Pemohon' : (isKtp ? 'Foto KTP-el' : 'Foto Dokumen Persyaratan') }}
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
            {{ documentLabel }}
          </p>
        </div>
        <button
          @click="closeModal"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Main Body -->
      <div class="p-4 sm:p-5 flex-1 overflow-y-auto flex flex-col items-center">
        <!-- Capture Viewfinder / Preview Container -->
        <div class="w-full relative rounded-2xl overflow-hidden bg-black h-[56vh] max-h-[500px] min-h-[380px] sm:h-[460px] flex items-center justify-center shadow-inner">
          <!-- 1. LIVE CAMERA STREAM -->
          <video
            v-show="isCameraActive && !capturedBlob"
            ref="videoRef"
            autoplay
            playsinline
            muted
            :class="['w-full h-full object-cover', isSelfie && currentFacingMode === 'user' ? '-scale-x-100' : '']"
          ></video>

          <!-- Guideline Overlay on Live Camera -->
          <div
            v-if="isCameraActive && !capturedBlob"
            class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-3.5 sm:p-4 overflow-hidden"
          >
            <!-- Top Guide Badge -->
            <div class="z-10 bg-slate-950/80 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/20 shadow-lg text-center max-w-[92%]">
              <p class="text-xs font-semibold text-white flex items-center justify-center gap-1.5">
                <span v-if="isKtp" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span v-else-if="isSelfie" class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                <span v-else class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>{{ guideTitle }}</span>
              </p>
            </div>

            <!-- Center Cutout Frame -->
            <div class="w-full flex-1 flex items-center justify-center relative my-1">
              <!-- 1. KTP GUIDELINE (Green card outline + Pasfoto box on right) -->
              <div
                v-if="isKtp"
                class="relative w-[94%] sm:w-[86%] max-w-[460px] aspect-[1.58/1] rounded-2xl border-2 sm:border-[3px] border-emerald-400 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)] flex items-center"
              >
                <!-- Left: Area NIK & Identitas -->
                <div class="absolute left-2.5 sm:left-3.5 top-2.5 sm:top-3.5 bottom-2.5 sm:bottom-3.5 right-[34%] rounded-xl border border-dashed border-emerald-400/50 p-2 sm:p-2.5 flex flex-col justify-between text-emerald-300">
                  <div class="flex items-center gap-1.5 font-mono font-bold tracking-wider text-[10px] sm:text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-ping"></span>
                    AREA NIK & IDENTITAS
                  </div>
                  <div class="text-[9px] sm:text-[10px] text-emerald-100/80 leading-tight">
                    Posisikan teks KTP agar terlihat tajam dan tidak silau
                  </div>
                </div>

                <!-- Right: Pasfoto Box (Dedicated matching user's image) -->
                <div class="absolute right-2.5 sm:right-3.5 top-2.5 sm:top-3.5 bottom-2.5 sm:bottom-3.5 w-[30%] rounded-xl border-2 sm:border-[3px] border-emerald-400 bg-emerald-400/10 flex flex-col items-center justify-center p-1 text-center shadow-sm">
                  <svg class="w-6 h-6 sm:w-8 sm:h-8 text-emerald-300 drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <span class="text-[9px] sm:text-[10px] font-bold text-emerald-200 mt-1 leading-tight tracking-wide">
                    PASFOTO KTP
                  </span>
                  <span class="text-[8px] text-emerald-300/80 mt-0.5 leading-tight">
                    Paskan foto di sini
                  </span>
                </div>
              </div>

              <!-- 2. SELFIE GUIDELINE (White dashed oval for face) -->
              <div
                v-else-if="isSelfie"
                class="relative w-48 h-64 sm:w-56 sm:h-72 border-2 sm:border-[3px] border-dashed border-white rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] flex flex-col items-center justify-center"
              >
                <!-- Eye-level line -->
                <div class="w-2/3 border-b border-white/40 mb-6 relative">
                  <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-[9px] text-white/80 font-semibold tracking-wider whitespace-nowrap">
                    POSISI MATA
                  </span>
                </div>
              </div>

              <!-- 3. GENERAL DOCUMENT GUIDELINE (KK, Surat, Akta, dll) -->
              <div
                v-else
                class="relative w-[92%] sm:w-[88%] h-[88%] sm:h-[84%] border-2 sm:border-[3px] border-emerald-400 rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.5)] flex items-center justify-center"
              >
                <!-- 4 Corner Bracket Accents -->
                <div class="absolute top-2 left-2 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl"></div>
                <div class="absolute top-2 right-2 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr"></div>
                <div class="absolute bottom-2 left-2 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl"></div>
                <div class="absolute bottom-2 right-2 w-6 h-6 border-b-4 border-r-4 border-white rounded-br"></div>

                <!-- Center guidance hint -->
                <div class="text-center p-3 text-white/85 max-w-xs drop-shadow">
                  <p class="text-[11px] font-semibold leading-relaxed">
                    Pastikan 4 sudut lembar dokumen berada di dalam kotak hijau
                  </p>
                </div>
              </div>
            </div>

            <!-- Bottom Hint Badge -->
            <div class="z-10 bg-slate-950/70 backdrop-blur-sm px-3 py-1 rounded-full text-[11px] text-white/90 text-center max-w-[92%]">
              {{ guideHint }}
            </div>
          </div>

          <!-- 2. CAPTURED PREVIEW -->
          <img
            v-if="capturedBlob"
            :src="previewUrl"
            alt="Preview Dokumen"
            class="w-full h-full object-contain bg-slate-950"
          />

          <!-- 3. CAMERA STREAM RESTRICTED / FALLBACK -->
          <div
            v-if="!isCameraActive && !capturedBlob && cameraError"
            class="text-center p-6 text-slate-300 space-y-3.5 max-w-md"
          >
            <div class="w-14 h-14 rounded-2xl bg-amber-500/20 text-amber-400 mx-auto flex items-center justify-center">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>

            <div>
              <p class="text-sm font-bold text-white">
                {{ isContextInsecure && !isMobileDevice ? 'Webcam PC Memerlukan Koneksi Aman' : 'Kamera Belum Aktif' }}
              </p>
              <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                <span v-if="isContextInsecure && !isMobileDevice">
                  Browser Chrome di PC/Komputer membatasi akses video webcam pada domain HTTP biasa (<code>.test</code>). Di Windows PC, tombol HTML hanya membuka File Explorer.
                </span>
                <span v-else>
                  {{ cameraError }}
                </span>
              </p>
            </div>

            <!-- If on Desktop & Insecure: Offer Localhost Button or Guide -->
            <div v-if="isContextInsecure && !isMobileDevice" class="space-y-2 pt-1">
              <a
                :href="localhostRedirectUrl"
                class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Buka via Localhost:8000 (Webcam Otomatis Aktif)</span>
              </a>
              <p class="text-[11px] text-slate-400 leading-normal">
                Catatan: Di HP/Smartphone (Android & iPhone) atau server production ber-HTTPS, kamera akan menyala secara native tanpa kendala.
              </p>
            </div>

            <!-- If on Mobile: Native Camera Button -->
            <div v-else class="pt-1">
              <button
                type="button"
                @click="triggerNativeCameraInput"
                class="w-full px-5 py-3.5 bg-blue-600 hover:bg-blue-500 active:scale-[0.99] text-white rounded-2xl text-xs font-bold shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Buka Kamera HP (Kamera Bawaan)</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Guidance Banner -->
        <div class="w-full mt-3 px-3 py-2 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-100 dark:border-blue-900/40 flex items-start gap-2.5">
          <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
            <span class="font-semibold">Petunjuk:</span> {{ captureHint || 'Pastikan dokumen berada di tempat terang, tidak buram, dan teks dapat terbaca jelas.' }}
          </p>
        </div>

        <!-- Compression Information Badge (when captured) -->
        <div
          v-if="compressionStats"
          class="w-full mt-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center justify-between text-xs text-emerald-800 dark:text-emerald-300"
        >
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium">Kompresi Otomatis Berhasil</span>
          </div>
          <div class="text-[11px] font-mono">
            <span class="line-through text-emerald-600/70">{{ formatSize(compressionStats.originalSize) }}</span>
            &rarr; <span class="font-bold">{{ formatSize(compressionStats.compressedSize) }}</span>
            <span v-if="compressionStats.reductionPercent > 0" class="text-emerald-700 dark:text-emerald-400 font-bold ml-1">(-{{ compressionStats.reductionPercent }}%)</span>
          </div>
        </div>

        <!-- Hidden Canvas for Snapshot -->
        <canvas ref="canvasRef" class="hidden"></canvas>

        <!-- Hidden Native Camera Capture Input (HTML5 Camera Capture) -->
        <input
          ref="nativeCameraInputRef"
          type="file"
          accept="image/*"
          :capture="isSelfie ? 'user' : 'environment'"
          class="hidden"
          @change="handleNativeFilePicked"
        />
      </div>

      <!-- Action Footer -->
      <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between gap-3">
        <!-- Left: Switch Camera (Front / Back) -->
        <div>
          <button
            v-if="isCameraActive && !capturedBlob && hasMultipleCameras"
            type="button"
            @click="toggleCameraFacing"
            class="px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm flex items-center gap-1.5 cursor-pointer"
            title="Ganti Kamera Depan/Belakang"
          >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span class="hidden sm:inline">Ganti</span> Kamera
          </button>
        </div>

        <!-- Right: Actions (Buka Kamera / Retake / Snapshot / Confirm) -->
        <div class="flex items-center gap-2 ml-auto">
          <!-- Fallback Trigger if camera stream was blocked -->
          <button
            v-if="!isCameraActive && !capturedBlob && isMobileDevice"
            type="button"
            @click="triggerNativeCameraInput"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-lg shadow-blue-600/30 flex items-center gap-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Buka Kamera HP
          </button>

          <a
            v-if="!isCameraActive && !capturedBlob && !isMobileDevice && isContextInsecure"
            :href="localhostRedirectUrl"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Buka di Localhost (Webcam)
          </a>

          <!-- Retake Button -->
          <button
            v-if="capturedBlob"
            type="button"
            @click="retakePhoto"
            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 transition shadow-sm cursor-pointer"
          >
            Ambil Ulang
          </button>

          <!-- Capture Snapshot Button (Live Stream Active) -->
          <button
            v-if="isCameraActive && !capturedBlob"
            type="button"
            @click="takeSnapshot"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-lg shadow-blue-600/30 flex items-center gap-2 cursor-pointer"
          >
            <div class="w-3 h-3 rounded-full bg-white animate-pulse"></div>
            Ambil Foto
          </button>

          <!-- Confirm & Use Photo Button -->
          <button
            v-if="capturedBlob"
            type="button"
            @click="confirmPhoto"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-1.5 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Gunakan Foto Ini
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { compressImage, formatFileSize } from './ImageCompressor';

const props = defineProps({
  documentLabel: {
    type: String,
    required: true,
  },
  captureMode: {
    type: String,
    default: 'environment', // 'environment' = back camera, 'user' = front camera
  },
  captureHint: {
    type: String,
    default: null,
  },
  acceptedFormats: {
    type: String,
    default: 'jpg,jpeg,png',
  },
});

const emit = defineEmits(['close', 'captured']);

const videoRef = ref(null);
const canvasRef = ref(null);
const nativeCameraInputRef = ref(null);

const isCameraActive = ref(false);
const currentFacingMode = ref(props.captureMode === 'user' ? 'user' : 'environment');
const mediaStream = ref(null);
const cameraError = ref(null);
const hasMultipleCameras = ref(true);

const capturedBlob = ref(null);
const previewUrl = ref(null);
const compressionStats = ref(null);

const isSelfie = computed(() => {
  if (props.captureMode === 'user') return true;
  const label = (props.documentLabel || '').toLowerCase();
  return label.includes('selfie') || label.includes('wajah') || label.includes('face');
});

const isKtp = computed(() => {
  if (isSelfie.value) return false;
  const label = (props.documentLabel || '').toLowerCase();
  return label.includes('ktp') || label.includes('e-ktp') || label.includes('identitas');
});

const guideTitle = computed(() => {
  if (isSelfie.value) return 'Posisikan Wajah Tepat di Dalam Garis Oval';
  if (isKtp.value) return 'Posisikan KTP & Pasfoto Pas Pada Kotak Hijau';
  return 'Posisikan Seluruh Dokumen Dalam Kotak Hijau';
});

const guideHint = computed(() => {
  if (props.captureHint) return props.captureHint;
  if (isSelfie.value) return 'Hadapkan wajah lurus ke kamera dengan pencahayaan yang cukup';
  if (isKtp.value) return 'Sejajarkan fisik KTP ke bingkai hijau dan paskan foto wajah di kotak kanan';
  return 'Pastikan dokumen tidak miring, tidak terpotong, dan tulisan terbaca jelas';
});

const isMobileDevice = computed(() => {
  if (typeof navigator === 'undefined') return false;
  return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
});

const isContextInsecure = computed(() => {
  if (typeof window === 'undefined') return false;
  return window.isSecureContext === false;
});

const localhostRedirectUrl = computed(() => {
  if (typeof window === 'undefined') return '#';
  return 'http://127.0.0.1:8000' + window.location.pathname + window.location.search;
});

const formatSize = (bytes) => formatFileSize(bytes);

onMounted(async () => {
  await startCamera();
});

onBeforeUnmount(() => {
  stopCamera();
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
  }
});

async function startCamera() {
  stopCamera();
  cameraError.value = null;

  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    if (isContextInsecure.value && !isMobileDevice.value) {
      cameraError.value = 'Browser Chrome di PC/Komputer membatasi akses webcam pada koneksi HTTP (tidak ber-SSL).';
    } else {
      cameraError.value = 'Akses live camera tidak didukung oleh browser pada koneksi ini.';
    }
    isCameraActive.value = false;
    return;
  }

  try {
    const constraints = {
      video: {
        facingMode: { ideal: currentFacingMode.value },
        width: { ideal: 1920 },
        height: { ideal: 1080 },
      },
      audio: false,
    };

    const stream = await navigator.mediaDevices.getUserMedia(constraints);
    mediaStream.value = stream;

    if (videoRef.value) {
      videoRef.value.srcObject = stream;
      await videoRef.value.play();
      isCameraActive.value = true;
    }
  } catch (err) {
    console.warn('Camera access denied or unavailable:', err);
    isCameraActive.value = false;
    cameraError.value = 'Izin kamera langsung belum aktif. Silakan klik tombol di bawah untuk memotret dengan kamera perangkat Anda.';
  }
}

function stopCamera() {
  if (mediaStream.value) {
    mediaStream.value.getTracks().forEach((track) => track.stop());
    mediaStream.value = null;
  }
  isCameraActive.value = false;
}

function toggleCameraFacing() {
  currentFacingMode.value = currentFacingMode.value === 'user' ? 'environment' : 'user';
  startCamera();
}

async function takeSnapshot() {
  if (!videoRef.value || !canvasRef.value) return;

  const video = videoRef.value;
  const canvas = canvasRef.value;

  canvas.width = video.videoWidth || 1280;
  canvas.height = video.videoHeight || 720;

  const ctx = canvas.getContext('2d');

  if (isSelfie.value && currentFacingMode.value === 'user') {
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
  }

  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

  stopCamera();

  canvas.toBlob((blob) => {
    if (!blob) return;

    const rawFile = new File([blob], `capture-${Date.now()}.jpg`, { type: 'image/jpeg' });

    // Store in memory directly without waiting for compression
    capturedBlob.value = rawFile;
    previewUrl.value = URL.createObjectURL(rawFile);
  }, 'image/jpeg', 0.95);
}

function retakePhoto() {
  capturedBlob.value = null;
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
  }
  compressionStats.value = null;
  startCamera();
}

function triggerNativeCameraInput() {
  if (nativeCameraInputRef.value) {
    nativeCameraInputRef.value.click();
  }
}

function handleNativeFilePicked(e) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;

  stopCamera();

  // Store in memory directly without waiting for compression
  capturedBlob.value = file;
  previewUrl.value = URL.createObjectURL(file);
}

function confirmPhoto() {
  if (!capturedBlob.value) return;

  emit('captured', capturedBlob.value);
  closeModal();
}

function closeModal() {
  stopCamera();
  emit('close');
}
</script>
