<template>
  <Head :title="`Detail Serah Terima - ${handover.application?.reg_number} - Disdukcapil Dompu`" />

  <AdminLayout>
    <div class="space-y-6 max-w-6xl mx-auto">
      <!-- Breadcrumb & Nav -->
      <div class="flex items-center justify-between">
        <Link
          :href="route('admin.handover.dashboard')"
          class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali ke Dashboard Serah Terima
        </Link>
        <span class="text-xs font-mono text-slate-400">
          ID: #{{ handover.id }} · Ref: {{ handover.application?.reg_number }}
        </span>
      </div>

      <!-- Main Status Banner -->
      <div
        :class="[
          'rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border',
          handover.status === 'SUDAH_DISERAHKAN'
            ? 'bg-gradient-to-r from-emerald-900 via-teal-950 to-slate-900 border-emerald-600/40'
            : handover.status === 'SIAP_DIAMBIL'
            ? 'bg-gradient-to-r from-blue-900 via-indigo-950 to-slate-900 border-blue-600/40'
            : handover.status === 'KENDALA_PENGAMBILAN'
            ? 'bg-gradient-to-r from-rose-900 via-rose-950 to-slate-900 border-rose-600/40'
            : 'bg-gradient-to-r from-amber-900 via-amber-950 to-slate-900 border-amber-600/40'
        ]"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur border border-white/20 mb-3">
              <span class="w-2 h-2 rounded-full" :class="statusDotColor"></span>
              Status: {{ handover.status_label }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              {{ handover.application?.applicant_name }}
            </h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs opacity-90">
              <span>No. Referensi: <strong class="font-mono">{{ handover.application?.reg_number }}</strong></span>
              <span>·</span>
              <span>Layanan: <strong>{{ handover.application?.service_type?.name }}</strong></span>
              <span>·</span>
              <span>NIK: <strong class="font-mono">{{ handover.application?.masked_nik }}</strong></span>
            </div>
          </div>

          <!-- QR Code Badge on Banner -->
          <div
            v-if="handover.application?.qr_code_svg"
            class="hidden md:flex flex-col items-center bg-white p-2 rounded-2xl shadow-md border border-white/30 text-center shrink-0"
            title="QR Code Referensi Dokumen"
          >
            <div
              v-html="handover.application.qr_code_svg"
              class="w-16 h-16 flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:block"
            ></div>
            <span class="text-[9px] font-mono font-bold text-slate-800 mt-1">
              {{ handover.application.reg_number }}
            </span>
          </div>

          <!-- Status Specific Quick Action on Banner -->
          <div v-if="handover.status === 'MENUNGGU_KETERSEDIAAN'">
            <button
              type="button"
              @click="markReadyDirect"
              :disabled="isSubmitting"
              class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xl shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              <span>Fisik Tersedia (Tandai Siap Diambil)</span>
            </button>
          </div>
          <div v-else-if="handover.status === 'KENDALA_PENGAMBILAN'">
            <button
              type="button"
              @click="openResolveModal"
              :disabled="isSubmitting"
              class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xl transition flex items-center gap-2 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              <span>Selesaikan Kendala</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Comparison Card: Pemilik KTP-el vs Penerima Aktual -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 1. Data Pemilik KTP-el -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              Data Pemilik KTP-el (Pemohon)
            </h3>
            <span class="text-[10px] uppercase font-bold text-slate-400">Data Master</span>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <span class="text-slate-400 block text-[11px]">Nama Lengkap:</span>
              <p class="font-bold text-slate-900 dark:text-white text-sm">{{ handover.application?.applicant_name }}</p>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <span class="text-slate-400 block text-[11px]">NIK:</span>
                <p class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ handover.application?.applicant_nik }}</p>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">No. Telepon / WA:</span>
                <p class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ handover.application?.applicant_phone || '-' }}</p>
              </div>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Alamat Kependudukan:</span>
              <p class="text-slate-700 dark:text-slate-300">{{ handover.application?.applicant_address || '-' }}</p>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
              <span class="text-slate-400 block text-[11px]">Rencana Kehadiran Awal:</span>
              <p class="font-medium text-slate-700 dark:text-slate-300">
                {{ handover.application?.attendance_label }}
                <span v-if="handover.application?.representative_name" class="block text-amber-600 dark:text-amber-400">
                  Dikuasakan ke: {{ handover.application?.representative_name }} (NIK: {{ handover.application?.representative_nik }})
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- 2. Status & Pemeriksaan Fisik Kartu -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              Status Kesiapan Fisik & Pemeriksaan
            </h3>
            <span class="text-[10px] uppercase font-bold text-slate-400">Verifikasi Fisik</span>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <span class="text-slate-400 block text-[11px]">Status Kesiapan Kartu:</span>
              <span
                :class="[
                  'inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold text-xs mt-1',
                  handover.status === 'SUDAH_DISERAHKAN'
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300'
                    : handover.status === 'SIAP_DIAMBIL'
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/80 dark:text-blue-300'
                    : handover.status === 'KENDALA_PENGAMBILAN'
                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300'
                    : 'bg-amber-100 text-amber-700 dark:bg-amber-950/80 dark:text-amber-300'
                ]"
              >
                {{ handover.status_label }}
              </span>
            </div>

            <div v-if="handover.ready_marked_at">
              <span class="text-slate-400 block text-[11px]">Waktu Dinyatakan Siap:</span>
              <p class="font-mono font-semibold text-slate-800 dark:text-slate-200">
                {{ handover.ready_marked_at_formatted }} oleh {{ handover.ready_marked_user?.name || 'Petugas' }}
              </p>
              <p v-if="handover.ready_notes" class="text-slate-500 italic mt-0.5">"{{ handover.ready_notes }}"</p>
            </div>

            <div v-if="handover.has_exception" class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-200">
              <span class="font-bold block mb-1">Catatan Kendala:</span>
              <p>{{ handover.exception_reason }}</p>
              <span class="block text-[10px] text-rose-600 dark:text-rose-400 mt-1">
                Dilaporkan oleh {{ handover.exception_reported_user?.name }} pada {{ handover.exception_reported_at_formatted }}
              </span>
            </div>

            <div v-if="handover.status === 'SUDAH_DISERAHKAN'" class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-200">
              <span class="font-bold block mb-1">KTP-el Berhasil Diserahkan:</span>
              <p>Penerima: <strong>{{ handover.actual_receiver_name }}</strong> ({{ handover.receiver_relationship }})</p>
              <span class="block text-[10px] text-emerald-600 dark:text-emerald-400 mt-1">
                Diserahkan pada {{ handover.handover_at_formatted }} oleh {{ handover.handover_user?.name }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- WORKSPACE 1: Jika SIAP_DIAMBIL -> Form Penyerahan & Live Mobile Camera -->
      <div
        v-if="handover.status === 'SIAP_DIAMBIL'"
        class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border-2 border-blue-600 dark:border-blue-500 shadow-xl space-y-6"
      >
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 mb-1">
              Tahap D & E: Proses Serah Terima Fisik
            </div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white">
              Pencatatan Penyerahan & Dokumentasi Foto Kamera
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
              Cocokkan identitas penerima yang hadir, ambil foto bukti serah terima dengan kamera HP, lalu konfirmasi penyerahan.
            </p>
          </div>

          <button
            type="button"
            @click="openExceptionModal"
            class="px-4 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs transition cursor-pointer"
          >
            Laporkan Kendala
          </button>
        </div>

        <form @submit.prevent="submitHandoverCompletion" class="space-y-6">
          <!-- 1. Data Penerima Aktual -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Tipe Penerima <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="handoverForm.actual_receiver_type"
                @change="handleReceiverTypeChange"
                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              >
                <option value="self">Pemohon Sendiri</option>
                <option value="representative">Perwakilan / Penerima Kuasa</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Nama Penerima Aktual <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="handoverForm.actual_receiver_name"
                type="text"
                placeholder="Nama lengkap penerima..."
                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                NIK Penerima Aktual (16 Digit) <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="handoverForm.actual_receiver_nik"
                type="text"
                maxlength="16"
                placeholder="16 digit NIK penerima..."
                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Hubungan dengan Pemilik <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="handoverForm.receiver_relationship"
                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              >
                <option value="Sendiri">Sendiri (Pemilik KTP-el)</option>
                <option value="Suami/Istri">Suami / Istri</option>
                <option value="Anak">Anak</option>
                <option value="Orang Tua">Orang Tua</option>
                <option value="Keluarga dalam 1 KK">Keluarga dalam 1 KK</option>
                <option value="Kuasa Resmi (Surat Kuasa F-1.07)">Kuasa Resmi (Surat Kuasa F-1.07)</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
          </div>

          <!-- 2. Kamera Langsung / Pengambilan Foto Bukti Penyerahan -->
          <div class="bg-slate-50 dark:bg-slate-900/60 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <h4 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  Foto Bukti Penyerahan Fisik KTP-el (Kamera HP) <span class="text-rose-500">*</span>
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  Foto diambil langsung pada saat penyerahan kartu kepada penerima. Pastikan wajah penerima dan fisik KTP-el terlihat jelas.
                </p>
              </div>

              <!-- Camera controls -->
              <div class="flex items-center gap-2">
                <button
                  v-if="!cameraActive && !capturedPhotoUrl"
                  type="button"
                  @click="startCamera"
                  class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                  Buka Kamera Langsung
                </button>
                <button
                  v-if="cameraActive"
                  type="button"
                  @click="switchCamera"
                  class="px-3 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                  Putar Kamera
                </button>
                <button
                  v-if="cameraActive"
                  type="button"
                  @click="stopCamera"
                  class="px-3 py-1.5 rounded-xl bg-rose-100 text-rose-700 text-xs font-bold transition cursor-pointer"
                >
                  Tutup
                </button>
              </div>
            </div>

            <!-- Viewfinder Area -->
            <div class="relative bg-slate-900 rounded-3xl overflow-hidden min-h-[300px] flex items-center justify-center border border-slate-300 dark:border-slate-700">
              <!-- Live Video Stream -->
              <video
                v-show="cameraActive && !capturedPhotoUrl"
                ref="videoRef"
                autoplay
                playsinline
                muted
                class="w-full max-h-[460px] object-cover"
              ></video>

              <!-- Viewfinder Frame Overlay -->
              <div
                v-if="cameraActive && !capturedPhotoUrl"
                class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center p-6"
              >
                <div class="w-full max-w-sm h-56 border-2 border-dashed border-emerald-400 rounded-2xl relative shadow-lg">
                  <span class="absolute top-2 left-3 text-[10px] font-bold text-emerald-400 bg-black/60 px-2 py-0.5 rounded">
                    Posisikan Penerima & KTP-el di Dalam Bingkai
                  </span>
                </div>
              </div>

              <!-- Captured Snapshot Preview -->
              <div v-if="capturedPhotoUrl" class="relative w-full flex flex-col items-center p-4">
                <img
                  :src="capturedPhotoUrl"
                  alt="Hasil Foto Bukti Penyerahan"
                  class="rounded-2xl max-h-[440px] w-auto object-contain shadow-2xl border-2 border-emerald-500"
                />
                <div class="mt-3 flex items-center gap-3">
                  <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Foto Siap Disimpan
                  </span>
                  <button
                    type="button"
                    @click="retakePhoto"
                    class="px-4 py-1.5 rounded-xl bg-slate-700 text-white hover:bg-slate-600 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Ambil Ulang Foto
                  </button>
                </div>
              </div>

              <!-- Initial Standby Prompt when Camera not yet started -->
              <div v-if="!cameraActive && !capturedPhotoUrl" class="text-center p-8 space-y-3">
                <div class="w-16 h-16 rounded-full bg-slate-800 text-slate-400 mx-auto flex items-center justify-center">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="text-slate-300 text-xs font-semibold">Kamera belum diaktifkan.</p>
                <button
                  type="button"
                  @click="startCamera"
                  class="px-6 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg transition cursor-pointer"
                >
                  Buka Kamera Langsung
                </button>
              </div>

              <!-- Shutter Capture Button when Live -->
              <div
                v-if="cameraActive && !capturedPhotoUrl"
                class="absolute bottom-4 inset-x-0 flex items-center justify-center"
              >
                <button
                  type="button"
                  @click="captureSnapshot"
                  class="w-16 h-16 rounded-full bg-white text-slate-900 border-4 border-slate-300 shadow-2xl flex items-center justify-center hover:scale-105 active:scale-95 transition cursor-pointer"
                >
                  <div class="w-11 h-11 rounded-full bg-blue-600"></div>
                </button>
              </div>

              <canvas ref="canvasRef" class="hidden"></canvas>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
            <span class="text-xs text-slate-500 dark:text-slate-400">
              * Seluruh data penerima dan foto dokumentasi disimpan aman di server dengan audit trail resmi.
            </span>

            <button
              type="submit"
              :disabled="isSubmitting || !photoBlob"
              class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-xl shadow-emerald-600/30 transition transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              <span v-if="isSubmitting">Menyimpan & Menyelesaikan Penyerahan...</span>
              <span v-else>Konfirmasi Penyerahan KTP-el &rarr;</span>
            </button>
          </div>
        </form>
      </div>

      <!-- WORKSPACE 2: Jika SUDAH_DISERAHKAN -> Preview Bukti Resmi Selesai -->
      <div
        v-if="handover.status === 'SUDAH_DISERAHKAN'"
        class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6"
      >
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
              <h3 class="text-base font-black text-slate-900 dark:text-white">
                Bukti Resmi Penyerahan KTP-el Telah Terbit
              </h3>
              <p class="text-xs text-slate-400">Transaksi selesai dan tercatat resmi pada sistem pelayanan Disdukcapil.</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
          <div class="space-y-3 text-xs">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 space-y-2">
              <div class="flex justify-between">
                <span class="text-slate-400">Penerima Aktual:</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ handover.actual_receiver_name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">NIK Penerima:</span>
                <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ handover.actual_receiver_nik || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Hubungan:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ handover.receiver_relationship }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Waktu Penyerahan (WITA):</span>
                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ handover.handover_at_formatted }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Petugas Penyerah:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ handover.handover_user?.name }}</span>
              </div>
              <div v-if="handover.handover_notes" class="pt-2 border-t border-slate-200 dark:border-slate-700">
                <span class="text-slate-400 block mb-0.5">Catatan Penyerahan:</span>
                <p class="text-slate-600 dark:text-slate-300 italic">"{{ handover.handover_notes }}"</p>
              </div>
            </div>
          </div>

          <!-- Photo Proof Preview -->
          <div class="flex flex-col items-center">
            <div class="relative bg-slate-900 rounded-2xl overflow-hidden max-h-[380px] border border-slate-200 dark:border-slate-700 shadow-md">
              <img
                :src="route('admin.handover.photo', handover.id)"
                alt="Foto Bukti Penyerahan KTP-el"
                class="max-h-[380px] w-auto object-contain"
              />
            </div>
            <span class="text-[11px] font-mono text-slate-400 mt-2">
              Foto Bukti Penyerahan (Private Storage)
            </span>
          </div>
        </div>
      </div>

      <!-- Audit Trail & Riwayat Aktivitas -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Riwayat Aktivitas & Audit Trail Transaksi
        </h3>

        <div v-if="handover.logs?.length === 0" class="py-6 text-center text-slate-400 text-xs">
          Belum ada catatan log aktivitas.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="log in handover.logs"
            :key="log.id"
            class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700/60 text-xs"
          >
            <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <span class="font-bold text-slate-900 dark:text-white">{{ log.action_label }}</span>
                <span class="text-[11px] font-mono text-slate-400">{{ log.created_at_formatted }}</span>
              </div>
              <p class="text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">{{ log.notes }}</p>
              <span class="text-[10px] text-slate-400 font-medium block mt-1">
                Petugas: {{ log.user?.name || 'Sistem' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Catat Kendala -->
    <div
      v-if="showExceptionModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    >
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-700 space-y-5">
        <h3 class="text-lg font-black text-slate-900 dark:text-white">Laporkan Kendala Pengambilan</h3>
        <textarea
          v-model="exceptionReasonText"
          rows="3"
          placeholder="Tuliskan alasan kendala..."
          class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-rose-500"
        ></textarea>
        <div class="flex items-center justify-end gap-3">
          <button type="button" @click="showExceptionModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Batal</button>
          <button type="button" @click="submitExceptionAction" :disabled="!exceptionReasonText.trim()" class="px-6 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-bold">Simpan Kendala</button>
        </div>
      </div>
    </div>

    <!-- Modal Selesaikan Kendala -->
    <div
      v-if="showResolveModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    >
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-700 space-y-5">
        <h3 class="text-lg font-black text-slate-900 dark:text-white">Selesaikan Kendala Pengambilan</h3>
        <textarea
          v-model="resolveNotesText"
          rows="3"
          placeholder="Tuliskan catatan penyelesaian..."
          class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500"
        ></textarea>
        <div class="flex items-center justify-end gap-3">
          <button type="button" @click="showResolveModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Batal</button>
          <button type="button" @click="submitResolveAction" :disabled="!resolveNotesText.trim()" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold">Selesaikan & Siap Diambil</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  handover: {
    type: Object,
    required: true,
  },
});

const isSubmitting = ref(false);
const showExceptionModal = ref(false);
const exceptionReasonText = ref('');
const showResolveModal = ref(false);
const resolveNotesText = ref('');

// Camera & Capture state
const videoRef = ref(null);
const canvasRef = ref(null);
const cameraActive = ref(false);
const currentFacingMode = ref('environment'); // Prioritize back camera on phones
const mediaStream = ref(null);
const capturedPhotoUrl = ref(null);
const photoBlob = ref(null);

// Form state
const handoverForm = reactive({
  actual_receiver_type: props.handover.actual_receiver_type || (props.handover.application?.attendance_type === 'representative' ? 'representative' : 'self'),
  actual_receiver_name: props.handover.actual_receiver_name || (props.handover.application?.attendance_type === 'representative' ? (props.handover.application?.representative_name || '') : props.handover.application?.applicant_name),
  actual_receiver_nik: props.handover.actual_receiver_nik || (props.handover.application?.attendance_type === 'representative' ? (props.handover.application?.representative_nik || '') : props.handover.application?.applicant_nik),
  actual_receiver_phone: props.handover.actual_receiver_phone || props.handover.application?.applicant_phone,
  receiver_relationship: props.handover.receiver_relationship || (props.handover.application?.attendance_type === 'representative' ? 'Kuasa Resmi (Surat Kuasa F-1.07)' : 'Sendiri'),
  handover_notes: '',
});

function handleReceiverTypeChange() {
  if (handoverForm.actual_receiver_type === 'self') {
    handoverForm.actual_receiver_name = props.handover.application?.applicant_name || '';
    handoverForm.actual_receiver_nik = props.handover.application?.applicant_nik || '';
    handoverForm.receiver_relationship = 'Sendiri';
  } else {
    handoverForm.actual_receiver_name = props.handover.application?.representative_name || '';
    handoverForm.actual_receiver_nik = props.handover.application?.representative_nik || '';
    handoverForm.receiver_relationship = 'Kuasa Resmi (Surat Kuasa F-1.07)';
  }
}

// Camera functions
async function startCamera() {
  capturedPhotoUrl.value = null;
  photoBlob.value = null;

  try {
    if (mediaStream.value) {
      stopCamera();
    }

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
      cameraActive.value = true;
    }
  } catch (err) {
    alert('Gagal membuka kamera: Pastikan izin kamera browser aktif. (' + err.message + ')');
  }
}

function stopCamera() {
  if (mediaStream.value) {
    mediaStream.value.getTracks().forEach((track) => track.stop());
    mediaStream.value = null;
  }
  cameraActive.value = false;
}

function switchCamera() {
  currentFacingMode.value = currentFacingMode.value === 'environment' ? 'user' : 'environment';
  startCamera();
}

function captureSnapshot() {
  if (!videoRef.value || !canvasRef.value) return;

  const video = videoRef.value;
  const canvas = canvasRef.value;

  canvas.width = video.videoWidth || 1280;
  canvas.height = video.videoHeight || 720;

  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

  canvas.toBlob((blob) => {
    if (blob) {
      photoBlob.value = blob;
      capturedPhotoUrl.value = URL.createObjectURL(blob);
      stopCamera();
    }
  }, 'image/jpeg', 0.9);
}

function retakePhoto() {
  capturedPhotoUrl.value = null;
  photoBlob.value = null;
  startCamera();
}

onBeforeUnmount(() => {
  stopCamera();
});

// Submit actions
function submitHandoverCompletion() {
  if (!photoBlob.value) {
    alert('Wajib mengambil foto bukti fisik penyerahan dengan kamera.');
    return;
  }

  isSubmitting.value = true;
  const formData = new FormData();
  formData.append('actual_receiver_type', handoverForm.actual_receiver_type);
  formData.append('actual_receiver_name', handoverForm.actual_receiver_name);
  formData.append('actual_receiver_nik', handoverForm.actual_receiver_nik);
  formData.append('actual_receiver_phone', handoverForm.actual_receiver_phone || '');
  formData.append('receiver_relationship', handoverForm.receiver_relationship);
  formData.append('handover_notes', handoverForm.handover_notes || '');
  formData.append('photo', photoBlob.value, 'handover_proof.jpg');

  router.post(route('admin.handover.complete', props.handover.id), formData, {
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
}

function markReadyDirect() {
  isSubmitting.value = true;
  router.post(route('admin.handover.mark-ready', props.handover.id), {}, {
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
}

function openExceptionModal() {
  showExceptionModal.value = true;
  exceptionReasonText.value = '';
}

function submitExceptionAction() {
  if (!exceptionReasonText.value.trim()) return;
  router.post(route('admin.handover.report-exception', props.handover.id), { reason: exceptionReasonText.value }, {
    onFinish: () => {
      showExceptionModal.value = false;
    },
  });
}

function openResolveModal() {
  showResolveModal.value = true;
  resolveNotesText.value = '';
}

function submitResolveAction() {
  if (!resolveNotesText.value.trim()) return;
  router.post(route('admin.handover.resolve-exception', props.handover.id), { notes: resolveNotesText.value }, {
    onFinish: () => {
      showResolveModal.value = false;
    },
  });
}

const statusDotColor = computed(() => {
  switch (props.handover.status) {
    case 'SUDAH_DISERAHKAN': return 'bg-emerald-400';
    case 'SIAP_DIAMBIL': return 'bg-blue-400';
    case 'KENDALA_PENGAMBILAN': return 'bg-rose-400';
    default: return 'bg-amber-400 animate-pulse';
  }
});
</script>
