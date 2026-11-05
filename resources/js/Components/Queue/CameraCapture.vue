<template>
  <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/90 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col max-h-[95vh]">
      <!-- Header -->
      <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h3 class="font-bold text-slate-900 dark:text-white text-base">
              {{ headerTitle }}
            </h3>
            <span v-if="isSelfie" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
              Pemohon Asli
            </span>
          </div>
          <p v-if="isSelfie" class="text-xs font-semibold text-amber-700 dark:text-amber-300 mt-0.5">
            ⚠️ Wajib Foto Wajah Pemohon Asli (Bukan foto pihak yang mewakili/kuasa).
          </p>
          <p v-else class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
            {{ documentLabel }}
          </p>
        </div>
        <button
          @click="closeModal"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Main Body -->
      <div class="p-3 sm:p-4 flex-1 overflow-y-auto flex flex-col items-center">
        <!-- Capture Viewfinder / Preview Container -->
        <div class="w-full relative rounded-2xl overflow-hidden bg-black h-[63vh] max-h-[580px] min-h-[420px] sm:h-[510px] flex items-center justify-center shadow-inner">
          <!-- 1. LIVE CAMERA STREAM (Always kept in DOM for smooth hardware stream) -->
          <video
            ref="videoRef"
            autoplay
            playsinline
            webkit-playsinline
            muted
            :class="[
              'w-full h-full object-cover transition-all duration-300',
              isCameraActive && !capturedBlob ? 'opacity-100 block' : 'opacity-0 hidden',
              (frameMode === 'selfie' || isSelfie) && currentFacingMode === 'user' ? '-scale-x-100' : ''
            ]"
            :style="!supportedZoom && currentZoom > 1 ? { transform: `scale(${currentZoom})` } : {}"
          ></video>

          <!-- Loading Indicator when starting camera -->
          <div
            v-if="isStartingCamera && !capturedBlob"
            class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center text-white z-20 space-y-3 p-4"
          >
            <div class="w-10 h-10 border-3 border-emerald-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs font-semibold tracking-wide text-slate-200">Menghubungkan Kamera Langsung...</p>
            <p class="text-[11px] text-slate-400 text-center max-w-xs">Mohon izinkan akses kamera jika browser menampilkan permintaan izin.</p>
          </div>

          <!-- Guideline Overlay on Live Camera -->
          <div
            v-if="isCameraActive && !capturedBlob"
            class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-2 sm:p-2.5 overflow-hidden z-10"
          >
            <!-- Top Controls: Document Mode Pills & Zoom Toggles -->
            <div class="w-full flex flex-col items-center gap-1.5 z-20 pointer-events-none">
              <!-- Interactive Document Frame Selector Pills -->
              <div class="flex items-center justify-center gap-1 bg-slate-950/85 backdrop-blur-md p-1 rounded-2xl border border-white/20 shadow-xl max-w-[98%] overflow-x-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden pointer-events-auto">
                <!-- 1. Kertas F4 / A4 (Tegak) -->
                <button
                  type="button"
                  @click="frameMode = 'f4_a4'"
                  :class="[
                    'px-2.5 py-1 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 cursor-pointer whitespace-nowrap',
                    frameMode === 'f4_a4'
                      ? 'bg-emerald-500 text-slate-950 shadow-md font-black scale-[1.02]'
                      : 'text-slate-300 hover:text-white hover:bg-white/10'
                  ]"
                  title="Sesuaikan garis hijau ke ukuran kertas F4 / A4 (Tegak)"
                >
                  <span>📄</span> Kertas F4 / A4
                </button>

                <!-- 2. Ukuran KTP / KIA (Kartu) -->
                <button
                  type="button"
                  @click="frameMode = 'ktp'"
                  :class="[
                    'px-2.5 py-1 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 cursor-pointer whitespace-nowrap',
                    frameMode === 'ktp'
                      ? 'bg-emerald-500 text-slate-950 shadow-md font-black scale-[1.02]'
                      : 'text-slate-300 hover:text-white hover:bg-white/10'
                  ]"
                  title="Sesuaikan garis hijau ke ukuran kartu fisik KTP-el / KIA"
                >
                  <span>🪪</span> Ukuran KTP
                </button>

                <!-- 3. Kartu Keluarga / Dokumen Landscape -->
                <button
                  type="button"
                  @click="frameMode = 'kk_landscape'"
                  :class="[
                    'px-2.5 py-1 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 cursor-pointer whitespace-nowrap',
                    frameMode === 'kk_landscape'
                      ? 'bg-emerald-500 text-slate-950 shadow-md font-black scale-[1.02]'
                      : 'text-slate-300 hover:text-white hover:bg-white/10'
                  ]"
                  title="Sesuaikan garis hijau ke ukuran Kartu Keluarga (Landscape)"
                >
                  <span>📋</span> Lembar KK
                </button>

                <!-- 4. Bebas / Penuh -->
                <button
                  type="button"
                  @click="frameMode = 'full'"
                  :class="[
                    'px-2.5 py-1 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 cursor-pointer whitespace-nowrap',
                    frameMode === 'full'
                      ? 'bg-emerald-500 text-slate-950 shadow-md font-black scale-[1.02]'
                      : 'text-slate-300 hover:text-white hover:bg-white/10'
                  ]"
                  title="Garis hijau layar penuh / bebas"
                >
                  <span>📐</span> Bebas
                </button>

                <!-- 5. Foto Wajah (jika relevan) -->
                <button
                  v-if="isSelfieModeAvailable"
                  type="button"
                  @click="frameMode = 'selfie'"
                  :class="[
                    'px-2.5 py-1 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 cursor-pointer whitespace-nowrap',
                    frameMode === 'selfie'
                      ? 'bg-emerald-500 text-slate-950 shadow-md font-black scale-[1.02]'
                      : 'text-slate-300 hover:text-white hover:bg-white/10'
                  ]"
                  title="Garis oval foto wajah"
                >
                  <span>👤</span> Wajah
                </button>
              </div>

              <!-- Top Auxiliary Bar: Guide Badge & Zoom Buttons -->
              <div class="flex items-center justify-between w-full px-2 max-w-[96%] pointer-events-auto">
                <!-- Status Guide Badge -->
                <div class="bg-slate-950/80 backdrop-blur-md px-3 py-1 rounded-full border border-white/20 shadow text-center">
                  <p class="text-[11px] font-semibold text-white flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>{{ guideTitle }}</span>
                  </p>
                </div>

                <!-- Zoom Level Toggle (1.0x, 1.4x, 2.0x) -->
                <div class="flex items-center gap-1 bg-slate-950/85 backdrop-blur-md px-2 py-0.5 rounded-full border border-white/20 shadow">
                  <span class="text-[10px] text-slate-400 font-mono font-bold mr-0.5">Zoom</span>
                  <button
                    v-for="z in [1.0, 1.4, 2.0]"
                    :key="z"
                    type="button"
                    @click="setZoom(z)"
                    :class="[
                      'px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold transition-all cursor-pointer',
                      currentZoom === z
                        ? 'bg-emerald-500 text-slate-950 shadow-sm'
                        : 'text-slate-300 hover:text-white hover:bg-white/10'
                    ]"
                  >
                    {{ z }}x
                  </button>
                </div>
              </div>
            </div>

            <!-- Center Cutout Frame (Garis Hijau Menyesuaikan Ukuran Dokumen) -->
            <div class="w-full flex-1 flex items-center justify-center relative my-1 overflow-hidden">
              <!-- A. KERTAS F4 / A4 TEGAK (Besar, Proporsional & Siap Auto-Crop) -->
              <div
                v-if="frameMode === 'f4_a4'"
                ref="guideFrameRef"
                class="relative h-[92%] sm:h-[94%] max-w-[92%] border-2 sm:border-[3px] border-emerald-400 rounded-xl sm:rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] flex flex-col items-center justify-between p-2 sm:p-2.5 transition-all duration-300"
                style="aspect-ratio: 210 / 297;"
              >
                <!-- 4 Corner White Accents -->
                <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl"></div>
                <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr"></div>
                <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl"></div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-white rounded-br"></div>

                <!-- Top Guide: Kop Surat -->
                <div class="w-full border-b border-dashed border-emerald-400/50 pb-1 flex items-center justify-between text-emerald-300/90 text-[9px] font-mono font-bold tracking-wider">
                  <span>KOP / JUDUL SURAT</span>
                  <span class="text-[8px] bg-emerald-950/70 px-1.5 py-0.5 rounded text-emerald-300 border border-emerald-500/40 font-mono">F4 / A4 TEGAK</span>
                </div>

                <!-- Center Guidance Prompt (Minimal watermark, text is clean) -->
                <div class="pointer-events-none text-center p-1 text-white drop-shadow">
                  <p class="text-[10px] sm:text-[11px] font-bold text-white/95 drop-shadow leading-tight bg-slate-950/60 px-3 py-0.5 rounded-full inline-block">
                    Paskan 4 sudut lembar di bingkai hijau
                  </p>
                </div>

                <!-- Bottom Guide: Tanda Tangan -->
                <div class="w-full border-t border-dashed border-emerald-400/50 pt-1 flex items-center justify-between text-emerald-300/90 text-[9px] font-mono">
                  <span>TANDA TANGAN & STEMPEL</span>
                  <span class="text-[8px] text-emerald-300/70">210×297 mm</span>
                </div>
              </div>

              <!-- B. UKURAN FISIK KTP (CR80 Standard: 85.6 x 54 mm, Lebar & Dekat) -->
              <div
                v-else-if="frameMode === 'ktp'"
                ref="guideFrameRef"
                class="relative w-[96%] sm:w-[92%] max-w-[460px] border-2 sm:border-[3px] border-emerald-400 rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] flex items-center justify-between p-2.5 transition-all duration-300"
                style="aspect-ratio: 85.6 / 54;"
              >
                <!-- 4 Corner White Accents -->
                <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl"></div>
                <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr"></div>
                <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl"></div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-white rounded-br"></div>

                <!-- Left: Area NIK & Data KTP -->
                <div class="h-full flex-1 mr-2 rounded-xl border border-dashed border-emerald-400/50 p-2 sm:p-2.5 flex flex-col justify-between text-emerald-300 bg-emerald-950/30">
                  <div class="flex items-center gap-1.5 font-mono font-bold tracking-wider text-[10px] sm:text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-ping"></span>
                    AREA NIK & IDENTITAS
                  </div>
                  <div class="text-[9px] sm:text-[10px] text-emerald-100/90 leading-tight">
                    Dekatkan kamera agar NIK terlihat tajam dan tidak silau
                  </div>
                  <div class="text-[8px] font-mono text-emerald-300/70">
                    UKURAN KTP (85.6 × 54 mm)
                  </div>
                </div>

                <!-- Right: Pasfoto KTP Box -->
                <div class="h-full w-[30%] rounded-xl border-2 sm:border-[3px] border-emerald-400 bg-emerald-400/15 flex flex-col items-center justify-center p-1 text-center shadow-sm">
                  <svg class="w-6 h-6 sm:w-7 sm:h-7 text-emerald-300 drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <span class="text-[9px] sm:text-[10px] font-bold text-emerald-200 mt-1 leading-tight tracking-wide">
                    PASFOTO KTP
                  </span>
                  <span class="text-[8px] text-emerald-300/80 mt-0.5 leading-tight">
                    Paskan di sini
                  </span>
                </div>
              </div>

              <!-- C. KARTU KELUARGA / LANDSCAPE -->
              <div
                v-else-if="frameMode === 'kk_landscape'"
                ref="guideFrameRef"
                class="relative w-[96%] sm:w-[94%] max-w-[480px] border-2 sm:border-[3px] border-emerald-400 rounded-xl sm:rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] flex flex-col items-center justify-between p-2.5 transition-all duration-300"
                style="aspect-ratio: 297 / 210;"
              >
                <!-- 4 Corner White Accents -->
                <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl"></div>
                <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr"></div>
                <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl"></div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-white rounded-br"></div>

                <!-- Header -->
                <div class="w-full border-b border-dashed border-emerald-400/50 pb-1 flex items-center justify-between text-emerald-300/90 text-[9px] font-mono font-bold tracking-wider">
                  <span>KOP KARTU KELUARGA (KK)</span>
                  <span class="text-[8px] bg-emerald-950/70 px-1.5 py-0.5 rounded text-emerald-300 border border-emerald-500/40 font-mono">LANDSCAPE</span>
                </div>

                <!-- Center Guidance -->
                <div class="pointer-events-none text-center p-1 text-white drop-shadow">
                  <p class="text-[10px] sm:text-[11px] font-bold text-white/95 drop-shadow leading-tight bg-slate-950/60 px-3 py-0.5 rounded-full inline-block">
                    Posisikan lembar KK mendatar pas di kotak hijau
                  </p>
                </div>

                <!-- Footer -->
                <div class="w-full border-t border-dashed border-emerald-400/50 pt-1 flex items-center justify-between text-emerald-300/90 text-[9px] font-mono">
                  <span>BARCODE TTE / TTD KEPALA DINAS</span>
                  <span class="text-[8px] text-emerald-300/70">A4 / F4 Mendatar</span>
                </div>
              </div>

              <!-- D. SELFIE / FOTO WAJAH (Oval) -->
              <div
                v-else-if="frameMode === 'selfie'"
                ref="guideFrameRef"
                class="relative w-52 h-68 sm:w-60 sm:h-76 border-2 sm:border-[3px] border-dashed border-white rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] flex flex-col items-center justify-center transition-all duration-300"
              >
                <!-- Eye-level line -->
                <div class="w-2/3 border-b border-white/40 mb-6 relative">
                  <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-[9px] text-white/80 font-semibold tracking-wider whitespace-nowrap">
                    POSISI MATA
                  </span>
                </div>

                <!-- Warning Pill inside Oval -->
                <div class="absolute bottom-6 px-3 py-1 bg-amber-500/95 text-slate-950 text-[10px] font-black rounded-full shadow text-center max-w-[85%] uppercase tracking-tight">
                  Wajib Wajah Pemohon Asli
                </div>
              </div>

              <!-- E. BEBAS / PENUH (Full Viewfinder Area) -->
              <div
                v-else
                ref="guideFrameRef"
                class="relative w-[95%] sm:w-[92%] h-[92%] sm:h-[90%] border-2 sm:border-[3px] border-emerald-400 rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] flex items-center justify-center p-3 transition-all duration-300"
              >
                <!-- 4 Corner White Accents -->
                <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl"></div>
                <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr"></div>
                <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl"></div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-white rounded-br"></div>

                <div class="text-center p-2 text-white drop-shadow">
                  <p class="text-[11px] font-bold leading-relaxed bg-slate-950/60 px-3 py-1 rounded-full inline-block">
                    Posisikan seluruh lembar dokumen berada di dalam kotak hijau
                  </p>
                </div>
              </div>
            </div>

            <!-- Bottom Auto-Crop Indicator Pill -->
            <div class="z-10 bg-slate-950/85 backdrop-blur-md px-3.5 py-1 rounded-full text-[10px] sm:text-[11px] text-emerald-300 border border-emerald-500/30 text-center max-w-[94%] shadow flex items-center justify-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <span>Foto otomatis dipotong pas di dalam bingkai hijau agar hasil dokumen tajam & dekat</span>
            </div>
          </div>

          <!-- 2. CAPTURED PREVIEW -->
          <img
            v-if="capturedBlob"
            :src="previewUrl"
            alt="Preview Dokumen"
            class="w-full h-full object-contain bg-slate-950 z-20"
          />

          <!-- 3. CAMERA STREAM RESTRICTED / PERMISSION NOTICE -->
          <div
            v-if="!isCameraActive && !isStartingCamera && !capturedBlob && cameraError"
            class="text-center p-5 sm:p-6 text-slate-300 space-y-3.5 max-w-md z-30"
          >
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 mx-auto flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>

            <div>
              <p class="text-sm font-bold text-white">
                Izin Kamera Browser Belum Aktif
              </p>
              <div class="text-[11px] text-slate-300 mt-2 bg-slate-900/90 p-3.5 rounded-2xl border border-white/10 text-left space-y-2">
                <p class="font-semibold text-emerald-400">Cara Memunculkan / Mengaktifkan Izin:</p>
                <ol class="list-decimal list-inside space-y-1 text-slate-300/90 leading-relaxed">
                  <li>Klik tombol <span class="text-emerald-400 font-bold">"Minta Izin & Aktifkan Kamera"</span> di bawah.</li>
                  <li>Jika Chrome belum memunculkan konfirmasi, ketuk <strong>ikon setelan / gembok 🔒</strong> di sebelah kiri alamat web <code class="text-emerald-300">dukcapil.dompukab.go.id</code>.</li>
                  <li>Pilih menu <strong>Izin</strong> &rarr; pilih <strong>Kamera</strong> &rarr; pilih <strong>Izinkan</strong>.</li>
                </ol>
              </div>
            </div>

            <div class="space-y-2 pt-1">
              <button
                type="button"
                @click="startCamera"
                class="w-full px-5 py-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Minta Izin & Aktifkan Kamera Sekarang</span>
              </button>

              <button
                type="button"
                @click="triggerNativeCameraInput"
                class="w-full px-4 py-2.5 bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl text-[11px] font-medium transition flex items-center justify-center gap-2 cursor-pointer border border-slate-700"
              >
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Buka Lewat Kamera Bawaan HP (Alternatif)</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Guidance Banner -->
        <div class="w-full mt-2.5 px-3 py-2 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-100 dark:border-blue-900/40 flex items-start gap-2.5">
          <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
            <span class="font-semibold">Petunjuk:</span> {{ captureHint || 'Dekatkan kamera ke dokumen agar tulisan terbaca jelas. Sistem akan memotong foto otomatis mengikuti bingkai hijau.' }}
          </p>
        </div>

        <!-- Hidden Canvas for Snapshot -->
        <canvas ref="canvasRef" class="hidden"></canvas>

        <!-- Hidden Native Camera Capture Input (Fallback Only) -->
        <input
          ref="nativeCameraInputRef"
          type="file"
          accept="image/*"
          :capture="frameMode === 'selfie' || isSelfie ? 'user' : 'environment'"
          class="hidden"
          @change="handleNativeFilePicked"
        />
      </div>

      <!-- Action Footer -->
      <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between gap-3">
        <!-- Left: Switch Camera (Front / Back) -->
        <div>
          <button
            v-if="isCameraActive && !capturedBlob"
            type="button"
            @click="toggleCameraFacing"
            class="px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm flex items-center gap-1.5 cursor-pointer"
            title="Ganti Kamera Depan/Belakang"
          >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span class="hidden sm:inline">Ganti</span> Kamera
          </button>
        </div>

        <!-- Right: Actions (Ambil Foto / Retake / Confirm) -->
        <div class="flex items-center gap-2 ml-auto">
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
            class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white text-xs font-bold transition shadow-lg shadow-blue-600/30 flex items-center gap-2 cursor-pointer"
          >
            <div class="w-3 h-3 rounded-full bg-white animate-pulse"></div>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Ambil Foto Sekarang
          </button>

          <!-- Confirm & Use Photo Button -->
          <button
            v-if="capturedBlob"
            type="button"
            @click="confirmPhoto"
            class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white text-xs font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-1.5 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Gunakan Foto Ini & Lanjut
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';

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
const guideFrameRef = ref(null);

const isStartingCamera = ref(false);
const isCameraActive = ref(false);
const currentFacingMode = ref(props.captureMode === 'user' ? 'user' : 'environment');
const mediaStream = ref(null);
const cameraError = ref(null);
const capturedBlob = ref(null);
const previewUrl = ref(null);

// Zoom states
const currentZoom = ref(1.0);
const supportedZoom = ref(false);

// Determine default mode based on document label & capture mode
function getDefaultFrameMode() {
  if (props.captureMode === 'user') return 'selfie';
  const label = (props.documentLabel || '').toLowerCase();
  if (label.includes('selfie') || label.includes('wajah') || label.includes('face')) {
    return 'selfie';
  }
  if (label.includes('ktp') || label.includes('e-ktp') || label.includes('kia') || label.includes('identitas') || label.includes('id card')) {
    return 'ktp';
  }
  if (label.includes('keluarga') || label.includes('kk')) {
    return 'kk_landscape';
  }
  // Default for documents (Surat Kehilangan, Formulir F-1.02, Surat Kuasa, dll)
  return 'f4_a4';
}

const frameMode = ref(getDefaultFrameMode());

watch(() => props.documentLabel, () => {
  frameMode.value = getDefaultFrameMode();
});

const isSelfie = computed(() => {
  if (props.captureMode === 'user') return true;
  const label = (props.documentLabel || '').toLowerCase();
  return label.includes('selfie') || label.includes('wajah') || label.includes('face');
});

const isSelfieModeAvailable = computed(() => {
  return isSelfie.value || currentFacingMode.value === 'user';
});

const headerTitle = computed(() => {
  if (frameMode.value === 'selfie') return 'Foto Wajah Pemohon';
  if (frameMode.value === 'ktp') return 'Foto Dokumen Ukuran KTP';
  if (frameMode.value === 'f4_a4') return 'Foto Dokumen Kertas F4 / A4';
  if (frameMode.value === 'kk_landscape') return 'Foto Kartu Keluarga (Landscape)';
  return 'Foto Dokumen Persyaratan';
});

const guideTitle = computed(() => {
  if (frameMode.value === 'selfie') return 'Foto Selfie Pemohon Asli (Bukan Kuasa)';
  if (frameMode.value === 'ktp') return 'Dekatkan KTP Pas di Kotak Hijau';
  if (frameMode.value === 'f4_a4') return 'Paskan Kertas F4 / A4 di Kotak Hijau';
  if (frameMode.value === 'kk_landscape') return 'Paskan Lembar KK Mendatar';
  return 'Posisikan Dokumen Dalam Kotak Hijau';
});

let permissionStatusObj = null;

async function setupPermissionListener() {
  if (navigator.permissions && navigator.permissions.query) {
    try {
      const res = await navigator.permissions.query({ name: 'camera' });
      permissionStatusObj = res;
      res.onchange = () => {
        if (res.state === 'granted' && !isCameraActive.value) {
          startCamera();
        }
      };
    } catch (e) {
      // Permission query not supported for camera in some browsers
    }
  }
}

onMounted(async () => {
  setupPermissionListener();
  await startCamera();
});

onBeforeUnmount(() => {
  stopCamera();
  if (permissionStatusObj) {
    permissionStatusObj.onchange = null;
  }
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
  }
});

async function setZoom(val) {
  currentZoom.value = val;
  if (mediaStream.value) {
    try {
      const [track] = mediaStream.value.getVideoTracks();
      if (track && track.applyConstraints) {
        await track.applyConstraints({
          advanced: [{ zoom: val }],
        });
      }
    } catch (e) {
      // Fallback handled via CSS transform / canvas crop
    }
  }
}

async function startCamera() {
  stopCamera();
  cameraError.value = null;
  isStartingCamera.value = true;

  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    cameraError.value = 'Akses live camera tidak didukung oleh browser pada perangkat ini.';
    isCameraActive.value = false;
    isStartingCamera.value = false;
    return;
  }

  const preferredFacing = currentFacingMode.value || (frameMode.value === 'selfie' || isSelfie.value ? 'user' : 'environment');
  let stream = null;

  // Waterfall 1: Try with ideal facingMode & high resolution
  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: { ideal: preferredFacing },
        width: { ideal: 1920 },
        height: { ideal: 1080 },
      },
      audio: false,
    });
  } catch (err1) {
    console.warn('Attempt 1 (ideal facingMode) failed:', err1);
    // Waterfall 2: Try exact facingMode
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: preferredFacing,
        },
        audio: false,
      });
    } catch (err2) {
      console.warn('Attempt 2 (exact facingMode) failed:', err2);
      // Waterfall 3: Try generic video
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: true,
          audio: false,
        });
      } catch (err3) {
        console.error('All camera attempts failed:', err3);
        isCameraActive.value = false;
        isStartingCamera.value = false;

        if (err3.name === 'NotAllowedError' || err3.name === 'PermissionDeniedError') {
          cameraError.value = 'Izin kamera belum aktif. Silakan klik ikon gembok 🔒 atau setelan di samping alamat web (atas browser), ubah izin Kamera menjadi "Izinkan", lalu klik "Coba Lagi Aktifkan Kamera Langsung".';
        } else {
          cameraError.value = 'Kamera tidak dapat diakses (' + (err3.message || err3.name || 'Error') + '). Pastikan tidak ada aplikasi lain yang sedang menggunakan kamera.';
        }
        return;
      }
    }
  }

  mediaStream.value = stream;

  // Check hardware zoom capability
  try {
    const [track] = stream.getVideoTracks();
    if (track && track.getCapabilities) {
      const caps = track.getCapabilities();
      supportedZoom.value = !!caps.zoom;
      if (caps.zoom && currentZoom.value > 1) {
        track.applyConstraints({ advanced: [{ zoom: currentZoom.value }] }).catch(() => {});
      }
    }
  } catch (e) {}

  await nextTick();

  if (videoRef.value) {
    videoRef.value.srcObject = stream;
    videoRef.value.setAttribute('playsinline', 'true');
    videoRef.value.setAttribute('webkit-playsinline', 'true');
    videoRef.value.muted = true;
    try {
      await videoRef.value.play();
    } catch (playErr) {
      console.warn('Video play warning:', playErr);
    }
  }

  isCameraActive.value = true;
  isStartingCamera.value = false;
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

  const vw = video.videoWidth || 1280;
  const vh = video.videoHeight || 720;

  let sx = 0;
  let sy = 0;
  let sw = vw;
  let sh = vh;

  // Auto-Crop tightly to the green guide frame
  if (guideFrameRef.value) {
    try {
      const videoRect = video.getBoundingClientRect();
      const guideRect = guideFrameRef.value.getBoundingClientRect();

      if (videoRect.width > 0 && videoRect.height > 0) {
        // Calculate the CSS object-cover scale
        const scale = Math.max(videoRect.width / vw, videoRect.height / vh);
        const renderedW = vw * scale;
        const renderedH = vh * scale;

        // Offset of the rendered video within video container
        const offsetX = (videoRect.width - renderedW) / 2;
        const offsetY = (videoRect.height - renderedH) / 2;

        // Guide box coordinates relative to rendered video stream
        const guideXInVideo = guideRect.left - videoRect.left - offsetX;
        const guideYInVideo = guideRect.top - videoRect.top - offsetY;

        // Map to intrinsic video coordinates
        let rawCropX = guideXInVideo / scale;
        let rawCropY = guideYInVideo / scale;
        let rawCropW = guideRect.width / scale;
        let rawCropH = guideRect.height / scale;

        // Add 2% safety margin around the border so document edges are preserved
        const marginX = rawCropW * 0.02;
        const marginY = rawCropH * 0.02;

        sx = Math.max(0, Math.floor(rawCropX - marginX));
        sy = Math.max(0, Math.floor(rawCropY - marginY));
        sw = Math.min(vw - sx, Math.ceil(rawCropW + marginX * 2));
        sh = Math.min(vh - sy, Math.ceil(rawCropH + marginY * 2));
      }
    } catch (cropErr) {
      console.warn('Auto-crop calculation fallback to full sensor:', cropErr);
      sx = 0;
      sy = 0;
      sw = vw;
      sh = vh;
    }
  }

  // Safety fallback if calculated crop is invalid
  if (sw <= 100 || sh <= 100) {
    sx = 0;
    sy = 0;
    sw = vw;
    sh = vh;
  }

  canvas.width = sw;
  canvas.height = sh;

  const ctx = canvas.getContext('2d');

  if ((frameMode.value === 'selfie' || isSelfie.value) && currentFacingMode.value === 'user') {
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);
  } else {
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);
  }

  stopCamera();

  canvas.toBlob((blob) => {
    if (!blob) return;

    const rawFile = new File([blob], `capture-${Date.now()}.jpg`, { type: 'image/jpeg' });

    // Store in memory for instant high-res preview
    capturedBlob.value = rawFile;
    previewUrl.value = URL.createObjectURL(rawFile);
  }, 'image/jpeg', 0.94);
}

function retakePhoto() {
  capturedBlob.value = null;
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
  }
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
