<template>
  <Head :title="`Status Pengajuan [${application.reg_number}] - Antrian Online KTP-el`" />

  <PublicLayout>
    <div class="queue-dashboard-container py-8 sm:py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 print:p-0 print:m-0 print:max-w-none print:space-y-0">
      <!-- Top Quick Nav & Persistent Registration Identity Bar -->
      <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-4 sm:p-5 shadow-lg border border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
        <!-- Left: Persistent Registration Number with One-Click Copy -->
        <div class="flex items-center gap-3 flex-wrap">
          <div class="w-10 h-10 rounded-2xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-400 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <div>
            <span class="text-[10px] uppercase font-bold tracking-widest text-blue-300 block">
              NOMOR REGISTRASI PERMOHONAN
            </span>
            <div class="flex items-center gap-2 mt-0.5">
              <span class="text-xl sm:text-2xl font-black font-mono tracking-wide text-white">
                {{ application.reg_number }}
              </span>
              <button
                type="button"
                @click="copyText(application.reg_number, 'Nomor Registrasi')"
                class="px-2.5 py-1 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-[11px] font-bold transition flex items-center gap-1 active:scale-95"
                title="Salin Nomor Registrasi ke Clipboard"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Salin</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Right: Print / Save PDF & Riwayat Links -->
        <div class="flex items-center gap-2.5 self-start md:self-center shrink-0 flex-wrap">
          <button
            type="button"
            @click="showQrModal = true"
            class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm active:scale-95"
            title="Tampilkan QR Code untuk di-scan petugas loket"
          >
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            <span>Scan QR Petugas</span>
          </button>

          <button
            type="button"
            @click="windowPrint"
            class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm active:scale-95"
            title="Cetak Tanda Terima atau Simpan PDF"
          >
            <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak / Simpan PDF</span>
          </button>

          <Link
            :href="route('queue.history')"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-blue-600/30"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Riwayat Permohonan Saya</span>
          </Link>
        </div>
      </div>

      <!-- High-Priority Action Banner: Schedule Selection Required -->
      <div
        v-if="['PRE_VERIFIED', 'APPROVED'].includes(application.status) && !application.queue_number"
        class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white rounded-3xl p-5 sm:p-6 shadow-xl border-2 border-emerald-400 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 print:hidden"
      >
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0 text-2xl font-black">
            📅
          </div>
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-white/20 text-white mb-1.5">
              Langkah Wajib Selanjutnya
            </div>
            <h2 class="text-lg sm:text-xl font-black">
              Berkas Disetujui! Segera Pilih Jadwal Kedatangan Anda
            </h2>
            <p class="text-xs text-emerald-100 max-w-xl leading-relaxed mt-1">
              Dokumen persyaratan digital Anda telah sah & lengkap. Harap segera memilih tanggal kedatangan untuk menerbitkan <strong>Nomor Antrean Resmi</strong> ke kantor Disdukcapil.
            </p>
          </div>
        </div>

        <Link
          :href="bookingUrl"
          class="w-full md:w-auto px-6 py-3.5 rounded-2xl bg-white text-emerald-800 hover:bg-emerald-50 text-xs font-black transition shadow-lg hover:scale-105 text-center shrink-0 flex items-center justify-center gap-2 cursor-pointer"
        >
          <span>Pilih Jadwal Kedatangan &rarr;</span>
        </Link>
      </div>

      <!-- Status Top Card -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-xl relative overflow-hidden print:hidden">
        <!-- Colored top border accent -->
        <div
          class="absolute top-0 left-0 right-0 h-2"
          :class="accentBarColor"
        ></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="flex items-start gap-4 flex-1">
            <!-- Applicant Photo Thumbnail in Top Card (Visible on Mobile & Desktop) -->
            <div class="shrink-0">
              <div
                v-if="application.selfie_url"
                @click="showSelfieModal = true"
                class="w-14 h-14 sm:w-20 sm:h-20 rounded-2xl overflow-hidden border-2 border-blue-500/40 dark:border-blue-500/40 shadow-sm bg-white dark:bg-slate-800 p-0.5 cursor-pointer group relative"
                title="Klik untuk memperbesar Foto Pemohon"
              >
                <img
                  :src="application.selfie_url"
                  :alt="application.applicant_name"
                  class="w-full h-full object-cover rounded-xl group-hover:scale-105 transition"
                  @error="selfieImgFailed = true"
                />
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition rounded-xl flex items-center justify-center text-white text-[9px] font-bold">
                  Lihat
                </div>
              </div>
              <div
                v-else
                class="w-14 h-14 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex flex-col items-center justify-center shadow-md font-black text-lg sm:text-2xl"
              >
                {{ (application.applicant_name || 'A').charAt(0).toUpperCase() }}
              </div>
            </div>
            <div class="space-y-1 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400">
                  NO. REGISTRASI: {{ application.reg_number }}
                </span>
                <StatusBadge :status="application.status" size="md" />
              </div>

              <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white pt-1">
                {{ application.service_type?.name }}
              </h1>

              <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                <span>Pemohon: <strong class="text-slate-700 dark:text-slate-200">{{ application.applicant_name }}</strong></span>
                <span>·</span>
                <span>NIK: <span class="font-mono font-bold">{{ application.masked_nik }}</span></span>
                <span>·</span>
                <span class="inline-flex items-center gap-1 font-medium text-blue-600 dark:text-blue-400">
                  Kehadiran: {{ attendanceLabel }}
                </span>
              </div>

              <!-- Representative Details if represented -->
              <div
                v-if="application.attendance_type !== 'self' && application.representative_name"
                class="mt-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 flex flex-wrap items-center gap-3"
              >
                <span class="font-bold text-slate-800 dark:text-slate-200">Perwakilan / Kuasa:</span>
                <span>{{ application.representative_name }}</span>
                <span>(NIK: <span class="font-mono">{{ application.representative_nik }}</span>)</span>
                <span>· Hubungan: <strong>{{ application.representative_relationship }}</strong></span>
                <span v-if="application.representative_reason" class="text-slate-500 italic">· Alasan: "{{ application.representative_reason }}"</span>
              </div>
            </div>
          </div>

          <!-- Separated Queue Number Box -->
          <!-- A. PUBLISHED QUEUE NUMBER -->
          <div
            v-if="application.queue_number"
            class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-3xl p-5 text-center shadow-lg shadow-blue-600/30 min-w-[210px] shrink-0"
          >
            <p class="text-[10px] uppercase font-bold tracking-widest text-blue-200">
              NOMOR ANTREAN RESMI
            </p>
            <p class="text-4xl sm:text-5xl font-black font-mono tracking-tight mt-1">
              {{ application.queue_number }}
            </p>
            <p class="text-[11px] text-blue-100 mt-1 font-medium">
              {{ scheduleDateFormatted }}
            </p>
            <button
              type="button"
              @click="copyText(application.queue_number, 'Nomor Antrean')"
              class="mt-3 px-3 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition inline-flex items-center gap-1 w-full justify-center active:scale-95"
              title="Salin Nomor Antrean"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              <span>Salin No. Antrean</span>
            </button>
          </div>

          <!-- B. NOT YET PUBLISHED QUEUE NUMBER -->
          <div
            v-else
            class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-3xl p-5 text-center min-w-[210px] shrink-0"
          >
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
              Nomor Antrean
            </span>
            <p class="text-sm font-bold text-slate-600 dark:text-slate-300 mt-2">
              Belum Diterbitkan
            </p>
            <p class="text-[11px] text-slate-400 mt-1 max-w-[180px] mx-auto leading-tight">
              Nomor antrean dan jadwal akan terbit setelah verifikasi berkas disetujui.
            </p>
          </div>
        </div>

        <!-- Notification Banner / Instructions based on Status -->
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700/60">
          <!-- 1. DRAFT -->
          <div
            v-if="application.status === 'DRAFT'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 flex items-center justify-between gap-4"
          >
            <p>Pendaftaran Anda masih berstatus Draf. Harap lengkapi upload dokumen persyaratan.</p>
            <Link
              :href="route('queue.apply.documents', { regNumber: application.reg_number })"
              class="px-4 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-500 transition shrink-0"
            >
              Lanjutkan Unggah &rarr;
            </Link>
          </div>

          <!-- 2. SUBMITTED / UNDER REVIEW -->
          <div
            v-else-if="['SUBMITTED', 'UNDER_REVIEW'].includes(application.status)"
            class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 text-xs text-blue-800 dark:text-blue-300 flex items-start gap-3"
          >
            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <div class="flex-1">
              <p class="font-bold text-sm text-blue-900 dark:text-blue-200">
                {{ application.queue_number ? 'Nomor Antrean Resmi Telah Terbit · Berkas Sedang Diverifikasi' : 'Berkas Sedang Diverifikasi Petugas (Pra-Verifikasi Online)' }}
              </p>
              <p class="mt-1 leading-relaxed">
                <template v-if="application.queue_number">
                  Nomor antrean resmi Anda telah diterbitkan untuk jadwal <strong>{{ scheduleDateFormatted }}</strong>. Petugas verifikator sedang memeriksa berkas digital Anda. Silakan simpan atau cetak bukti tiket antrean di bawah ini dan bawa dokumen asli saat hadir ke kantor.
                </template>
                <template v-else>
                  Dokumen persyaratan digital Anda sedang diperiksa kelengkapannya oleh verifikator Disdukcapil Kabupaten Dompu.
                </template>
              </p>
            </div>
          </div>

          <!-- 3. PRE_VERIFIED / APPROVED (CALL TO ACTION TO SELECT QUEUE SCHEDULE) -->
          <div
            v-else-if="['PRE_VERIFIED', 'APPROVED'].includes(application.status) && !application.queue_number"
            class="p-5 sm:p-6 rounded-3xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
          >
            <div>
              <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Pra-Verifikasi Online Lolos
              </div>
              <h3 class="text-lg font-black">
                Silakan Pilih Jadwal Kedatangan & Ambil Antrean
              </h3>
              <p class="text-xs text-emerald-100 mt-1 max-w-xl leading-relaxed">
                Dokumen Anda dinyatakan memenuhi syarat administrasi awal. Langkah selanjutnya adalah memilih sesi kedatangan ke kantor untuk verifikasi fisik asli dan proses cetak/rekam.
              </p>
            </div>

            <Link
              :href="bookingUrl"
              class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white text-emerald-800 hover:bg-emerald-50 text-xs font-black transition shadow-md hover:scale-105 text-center shrink-0"
            >
              Pilih Jadwal & Ambil Antrean &rarr;
            </Link>
          </div>

          <!-- 4. QUEUE_BOOKED / SCHEDULED -->
          <div
            v-else-if="['QUEUE_BOOKED', 'SCHEDULED'].includes(application.status)"
            class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-900 dark:text-blue-200 flex items-start gap-3"
          >
            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <div>
              <p class="font-bold text-sm text-blue-900 dark:text-blue-200">Nomor Antrean Telah Diterbitkan</p>
              <p class="mt-1 leading-relaxed">
                Silakan simpan atau cetak tiket antrean di bawah. Hadirlah di Kantor Disdukcapil Kabupaten Dompu pada tanggal <strong>{{ scheduleDateFormatted }}</strong> dan bawa seluruh dokumen fisik asli.
              </p>
              <div v-if="canReschedule" class="mt-2.5">
                <Link
                  :href="route('queue.booking', { token: application.access_token, reschedule: 1 })"
                  class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 dark:text-blue-300 hover:underline"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span>Ingin ubah tanggal / jadwal kedatangan? Klik di sini</span>
                </Link>
              </div>
            </div>
          </div>

          <!-- 5. ARRIVED / CHECKED_IN -->
          <div
            v-else-if="['ARRIVED', 'CHECKED_IN'].includes(application.status)"
            class="p-4 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 text-xs text-teal-900 dark:text-teal-200 flex items-start gap-3"
          >
            <svg class="w-5 h-5 text-teal-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <div>
              <p class="font-bold text-sm text-teal-900 dark:text-teal-200">Telah Hadir di Loket (Check-In Selesai)</p>
              <p class="mt-1 leading-relaxed">
                Kehadiran Anda telah dikonfirmasi petugas loket. Harap menunggu panggilan di ruang tunggu untuk pemeriksaan fisik dokumen asli dan identitas pemohon/kuasa.
              </p>
            </div>
          </div>

          <!-- 6. PHYSICAL_VERIFICATION / APPROVED_FOR_SERVICE -->
          <div
            v-else-if="['PHYSICAL_VERIFICATION', 'APPROVED_FOR_SERVICE'].includes(application.status)"
            class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-3"
          >
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="font-bold text-sm text-emerald-900 dark:text-emerald-200">Verifikasi Fisik Asli Lolos — Siap Dilayani</p>
              <p class="mt-1 leading-relaxed">
                Petugas telah mencocokkan dokumen fisik asli dan menyatakan dokumen sah. Permohonan Anda dilanjutkan ke tahap perekaman/pencetakan fisik KTP-el.
              </p>
            </div>
          </div>

          <!-- 7. PROCESSING / IN_SERVICE -->
          <div
            v-else-if="['PROCESSING', 'IN_SERVICE'].includes(application.status)"
            class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 text-xs text-purple-900 dark:text-purple-200 flex items-start gap-3"
          >
            <svg class="w-5 h-5 text-purple-600 shrink-0 mt-0.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <div>
              <p class="font-bold text-sm text-purple-900 dark:text-purple-200">KTP-el Sedang Diproses / Dicetak</p>
              <p class="mt-1 leading-relaxed">
                Permohonan sedang diproses di operator SIAK / mesin pencetak KTP-el. Silakan menunggu notifikasi siap ambil.
              </p>
            </div>
          </div>

          <!-- 8. READY_FOR_PICKUP -->
          <div
            v-else-if="application.status === 'READY_FOR_PICKUP'"
            class="p-5 rounded-3xl bg-gradient-to-r from-emerald-600 to-green-700 text-white shadow-lg flex items-start gap-4"
          >
            <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
              <h3 class="text-base font-black">KTP-el Anda Siap Diambil di Loket!</h3>
              <p class="text-xs text-emerald-100 mt-1 leading-relaxed">
                Pencetakan fisik KTP-el telah rampung. Silakan menuju meja loket pengambilan / serah terima dokumen dengan menunjukkan tiket antrean ini.
              </p>
            </div>
          </div>

          <!-- 9. COMPLETED -->
          <div
            v-else-if="application.status === 'COMPLETED'"
            class="p-4 rounded-2xl bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-xs text-green-900 dark:text-green-200 flex items-start gap-3"
          >
            <svg class="w-5 h-5 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="font-bold text-sm text-green-900 dark:text-green-200">Pelayanan Selesai & Dokumen Diserahkan</p>
              <p class="mt-1 leading-relaxed">
                Fisik KTP-el telah diserahkan secara sah kepada <strong class="text-green-950 dark:text-white">{{ application.handover_recipient_name || application.applicant_name }}</strong>. Terima kasih atas partisipasi Anda dalam tertib administrasi kependudukan di Disdukcapil Kabupaten Dompu.
              </p>
            </div>
          </div>

          <!-- 10. REVISION REQUIRED -->
          <div
            v-else-if="application.status === 'REVISION_REQUIRED'"
            class="p-4 sm:p-5 rounded-2xl bg-orange-50 dark:bg-orange-950/50 border border-orange-200 dark:border-orange-800 text-orange-900 dark:text-orange-200"
          >
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-orange-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <div class="flex-1">
                <p class="font-bold text-sm">Dokumen Memerlukan Perbaikan</p>
                <p class="text-xs mt-1 leading-relaxed">
                  {{ application.revision_notes || 'Terdapat dokumen yang perlu diperbaiki atau diunggah ulang agar memenuhi standar pelayanan.' }}
                </p>
                <a
                  href="#unggah-revisi"
                  class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition shadow-sm"
                >
                  Unggah Ulang Dokumen yang Diminta &darr;
                </a>
              </div>
            </div>
          </div>

          <!-- 11. REJECTED -->
          <div
            v-else-if="application.status === 'REJECTED'"
            class="p-5 rounded-2xl bg-rose-50/80 dark:bg-rose-950/50 border-2 border-rose-300 dark:border-rose-800 text-xs space-y-3"
          >
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
              </div>
              <div class="flex-1">
                <p class="font-black text-sm text-rose-900 dark:text-rose-100">Status Permohonan: Pengajuan Ditolak</p>
                <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">
                  Permohonan Anda tidak dapat diproses lebih lanjut oleh petugas verifikator Dukcapil.
                </p>
              </div>
            </div>
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900 shadow-xs">
              <div class="flex items-center gap-1.5 text-xs font-bold text-rose-700 dark:text-rose-400 mb-1">
                <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Alasan Penolakan Petugas:</span>
              </div>
              <p class="text-xs sm:text-sm font-black text-rose-600 dark:text-rose-400 font-sans leading-relaxed tracking-wide">
                "{{ application.rejection_reason || 'Dokumen persyaratan tidak memenuhi ketentuan hukum atau persyaratan yang berlaku.' }}"
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- UNIVERSAL PRINTABLE TICKET / REGISTRATION RECEIPT (Formatted for Paper Print / Save as PDF) -->
      <div
        id="bukti-permohonan"
        class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border-2 border-dashed border-blue-300 dark:border-blue-800 shadow-xl relative"
        :class="{ 'print:block': true, 'hidden sm:block': !application.queue_number }"
      >
        <!-- Print Trigger -->
        <button
          type="button"
          @click="windowPrint"
          class="absolute top-6 right-6 p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition print:hidden"
          title="Cetak Bukti atau Simpan PDF"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        </button>

        <div class="text-center pb-6 border-b border-slate-100 dark:border-slate-700">
          <p class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">
            PEMERINTAH KABUPATEN DOMPU — DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL
          </p>
          <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">
            {{ application.queue_number ? 'TIKET ANTREAN & BUKTI PELAYANAN KTP-EL' : 'BUKTI TANDA TERIMA PENDAFTARAN KTP-EL' }}
          </h2>
          <div class="mt-1 flex items-center justify-center gap-3 text-xs text-slate-500">
            <span>Nomor Registrasi: <strong class="font-mono text-slate-800 dark:text-slate-200">{{ application.reg_number }}</strong></span>
            <span>·</span>
            <span>Status: <strong class="text-blue-600 dark:text-blue-400">{{ application.status_label }}</strong></span>
          </div>
        </div>

        <!-- Main Ticket Card: Number Info + Official QR Code -->
        <div class="p-6 sm:p-8 bg-slate-50 dark:bg-slate-900/60 rounded-2xl my-6 border border-slate-200/70 dark:border-slate-800">
          <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            
            <!-- Left Side: Registration / Queue Number Details -->
            <div class="flex-1 text-center md:text-left space-y-2">
              <template v-if="application.queue_number">
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                  Nomor Antrean Resmi Anda
                </span>
                <p class="text-5xl sm:text-6xl font-black font-mono tracking-tight text-blue-600 dark:text-blue-400">
                  {{ application.queue_number }}
                </p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                  {{ application.service_type?.name }}
                </p>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 pt-1">
                  <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">
                    Sesi: {{ application.slot?.name || 'Sesi Pelayanan' }} ({{ scheduleTimeFormatted }})
                  </span>
                  <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-mono font-bold bg-slate-200/70 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    Reg: {{ application.reg_number }}
                  </span>
                </div>
              </template>
              <template v-else>
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                  Nomor Registrasi Pendaftaran
                </span>
                <p class="text-3xl sm:text-4xl lg:text-5xl font-black font-mono tracking-tight text-slate-900 dark:text-slate-100">
                  {{ application.reg_number }}
                </p>
                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                  {{ application.service_type?.name }}
                </p>
                <div class="pt-1">
                  <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300">
                    Tahap: Pra-Verifikasi Dokumen Online (Nomor Antrean Diterbitkan Setelah Lolos Verifikasi)
                  </div>
                </div>
              </template>
            </div>

            <!-- Center & Right: Applicant Photo & Official QR Code Group -->
            <div class="flex flex-row items-center justify-center gap-4 sm:gap-6 flex-wrap sm:flex-nowrap shrink-0">
              <!-- Applicant Photo Box on Printable Ticket -->
              <div class="shrink-0 flex flex-col items-center text-center">
                <div
                  v-if="application.selfie_url && !selfieImgFailed"
                  @click="showSelfieModal = true"
                  class="w-28 h-32 sm:w-32 sm:h-36 p-1 bg-white dark:bg-slate-800 rounded-2xl border-2 border-blue-400 dark:border-blue-600 shadow-md overflow-hidden cursor-pointer group relative hover:scale-105 transition"
                  title="Klik untuk memperbesar Foto Pemohon"
                >
                  <img
                    :src="application.selfie_url"
                    :alt="application.applicant_name"
                    class="w-full h-full object-cover rounded-xl"
                    @error="selfieImgFailed = true"
                  />
                  <div class="absolute inset-0 bg-blue-900/20 opacity-0 group-hover:opacity-100 transition rounded-xl flex items-center justify-center text-white text-[10px] font-bold print:hidden">
                    Perbesar
                  </div>
                </div>
                <div
                  v-else
                  class="w-28 h-32 sm:w-32 sm:h-36 p-2 bg-slate-100 dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 flex flex-col items-center justify-center text-center text-slate-400"
                >
                  <svg class="w-10 h-10 mb-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  <span class="text-[10px] font-bold text-slate-500 uppercase">Pasfoto</span>
                  <span class="text-[9px] text-slate-400">Verifikasi Fisik</span>
                </div>
                <p class="text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-wider mt-1.5 flex items-center gap-1">
                  <span>Foto Pemohon</span>
                  <span v-if="application.selfie_url && !selfieImgFailed" class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                </p>
              </div>

              <!-- Right Side: Official QR Code Box for Scanner Device & Phone -->
              <div class="shrink-0 flex flex-col items-center text-center">
                <div
                  @click="showQrModal = true"
                  class="w-28 h-32 sm:w-36 sm:h-36 p-2 bg-white rounded-2xl border-2 border-slate-300 dark:border-slate-600 shadow-md flex items-center justify-center cursor-pointer hover:scale-105 transition group relative"
                  title="Klik untuk memperbesar QR Code"
                >
                  <!-- SVG QR Code rendered crisp and clean -->
                  <div
                    v-if="application.qr_code_svg"
                    v-html="application.qr_code_svg"
                    class="w-full h-full flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:max-w-full [&_svg]:max-h-full"
                  ></div>
                  <div v-else class="text-slate-400 text-xs">Memuat QR Code...</div>

                  <!-- Hover hint for desktop -->
                  <div class="absolute inset-0 bg-blue-900/10 rounded-2xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center print:hidden">
                    <span class="px-2 py-1 rounded-lg bg-black/75 text-white text-[10px] font-bold">Perbesar</span>
                  </div>
                </div>

                <!-- QR Code Subtext & Scanner Guidance -->
                <div class="mt-1.5 space-y-0.5">
                  <p class="text-[11px] font-mono font-bold text-slate-800 dark:text-slate-200 tracking-wider">
                    {{ application.reg_number }}
                  </p>
                  <p class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Scan QR Loket
                  </p>
                  <button
                    type="button"
                    @click="showQrModal = true"
                    class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:underline print:hidden cursor-pointer"
                  >
                    Perbesar QR
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Schedule & Location Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700">
            <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[10px]">Tanggal & Waktu Kedatangan</span>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ scheduleDateFormatted }}</p>
            <p class="text-slate-500 mt-0.5">{{ application.queue_number ? scheduleTimeFormatted : 'Menunggu pemilihan jadwal setelah verifikasi' }}</p>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700">
            <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[10px]">Lokasi Pelayanan</span>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1">Kantor Dinas Dukcapil Kab. Dompu</p>
            <p class="text-slate-500 mt-0.5 leading-relaxed">{{ officeAddress }}</p>
          </div>
        </div>

        <!-- Attendance & Representative Details on Ticket -->
        <div class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 text-xs">
          <div class="flex items-start gap-3.5">
            <div v-if="application.selfie_url" class="shrink-0">
              <img
                :src="application.selfie_url"
                :alt="application.applicant_name"
                class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-slate-600 shadow-xs cursor-pointer hover:opacity-80 transition"
                title="Klik untuk memperbesar foto pemohon"
                @click="showSelfieModal = true"
                @error="selfieImgFailed = true"
              />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 flex-1">
              <div>
                <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[10px]">Pemilik Dokumen</span>
                <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ application.applicant_name }}</p>
                <p class="text-slate-500 font-mono">NIK: {{ application.masked_nik }}</p>
              </div>
              <div>
                <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[10px]">Yang Hadir ke Kantor</span>
                <p class="font-bold text-slate-900 dark:text-white mt-0.5">
                  {{ application.attendance_type === 'self' ? application.applicant_name + ' (Pemohon Sendiri)' : application.representative_name + ' (Perwakilan / Kuasa)' }}
                </p>
                <p v-if="application.attendance_type !== 'self'" class="text-slate-500 font-mono">
                  NIK Perwakilan: {{ application.representative_nik }} ({{ application.representative_relationship }})
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Checklist of Original Documents to Bring -->
        <div class="mt-6 p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-xs">
          <p class="font-bold text-amber-900 dark:text-amber-300 mb-2 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Petunjuk Penting Kedatangan ke Loket:
          </p>
          <ul class="list-disc list-inside space-y-1 text-amber-800 dark:text-amber-200 leading-relaxed">
            <li>Harap hadir paling lambat 15 menit sebelum waktu sesi pelayanan dimulai.</li>
            <li>Tunjukkan tiket atau nomor antrean ini kepada petugas meja loket saat tiba di kantor.</li>
            <li><strong>Wajib membawa dokumen FISIK ASLI</strong> yang telah diunggah untuk dicocokkan keabsahannya oleh petugas sebelum proses perekaman atau pencetakan KTP-el.</li>
            <li v-if="application.attendance_type !== 'self'"><strong>Perwakilan wajib membawa KTP-el asli miliknya, KK asli, dan Surat Kuasa asli</strong> yang telah ditandatangani.</li>
          </ul>
        </div>

        <!-- Print Timestamp Footer (Visible only in print) -->
        <div class="mt-5 pt-3 border-t border-slate-200 dark:border-slate-700 text-center text-[10px] text-slate-400 hidden print:block">
          Dicetak pada: {{ printTimestamp }} — Pelayanan KTP-el Disdukcapil Kabupaten Dompu
        </div>
      </div>

      <!-- Uploaded Documents Inspection Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-md print:hidden">
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h3 class="text-xl font-black text-slate-900 dark:text-white">
              Status Berkas Persyaratan Digital
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
              Rincian hasil verifikasi terhadap setiap dokumen yang Anda unggah.
            </p>
          </div>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
          <div
            v-for="doc in (application.documents || [])"
            :key="doc.id"
            class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-slate-900 dark:text-white">
                  {{ doc.service_document?.label || doc.original_filename }}
                </span>
                <StatusBadge :status="doc.status" :custom-label="docStatusLabel(doc.status)" size="sm" />
              </div>

              <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                {{ doc.original_filename }} · {{ doc.formatted_size }}
              </p>

              <!-- Per-document Reviewer Comment -->
              <p
                v-if="doc.reviewer_note"
                class="text-xs text-orange-600 dark:text-orange-400 italic bg-orange-50 dark:bg-orange-950/40 px-3 py-1.5 rounded-xl border border-orange-200 dark:border-orange-800 mt-1"
              >
                Catatan Petugas: "{{ doc.reviewer_note }}"
              </p>
            </div>

            <!-- Preview Button -->
            <button
              type="button"
              @click="openPreviewDoc(doc)"
              class="self-start sm:self-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5"
            >
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              Pratinjau
            </button>
          </div>
        </div>
      </div>

      <!-- IN-PLACE REVISION UPLOAD FORM (Only when REVISION_REQUIRED) -->
      <div
        v-if="application.status === 'REVISION_REQUIRED'"
        id="unggah-revisi"
        class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border-2 border-orange-300 dark:border-orange-800 shadow-xl space-y-6 print:hidden"
      >
        <div>
          <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-100 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 mb-2 inline-block">
            Formulir Perbaikan Berkas
          </span>
          <h3 class="text-xl font-black text-slate-900 dark:text-white">
            Unggah Ulang Dokumen yang Perlu Diperbaiki
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Pilih dokumen yang diminta perbaikan dan unggah foto baru yang lebih jelas.
          </p>
        </div>

        <form @submit.prevent="submitRevision" class="space-y-4">
          <div
            v-for="doc in revisionDocuments"
            :key="doc.id"
            class="p-4 rounded-2xl border border-orange-200 dark:border-orange-800/80 bg-orange-50/30 dark:bg-orange-950/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
          >
            <div>
              <p class="font-bold text-sm text-slate-900 dark:text-white">
                {{ doc.service_document?.label }}
              </p>
              <p class="text-xs text-orange-700 dark:text-orange-400 mt-0.5">
                Alasan: "{{ doc.reviewer_note || 'Perlu diperbaiki' }}"
              </p>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
              <span
                v-if="revisionFiles[doc.service_document_id]"
                class="px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-semibold flex items-center gap-1.5"
              >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Foto Baru Siap
              </span>

              <button
                type="button"
                @click="openRevisionCamera(doc)"
                class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>{{ revisionFiles[doc.service_document_id] ? 'Foto Ulang' : 'Buka Kamera Dokumen' }}</span>
              </button>
            </div>
          </div>

          <div class="pt-4 flex justify-end">
            <button
              type="submit"
              :disabled="revisionForm.processing || Object.keys(revisionFiles).length === 0"
              class="px-6 py-3 rounded-2xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-lg shadow-orange-600/30 transition disabled:opacity-50"
            >
              {{ revisionForm.processing ? 'Mengirim Revisi...' : 'Kirim Ulang Berkas Perbaikan' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Verification History Timeline -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-md print:hidden">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6">
          Riwayat Progres
        </h3>

        <div class="space-y-6 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
          <div
            v-for="(hist, idx) in (application.verification_history || [])"
            :key="idx"
            class="flex items-start gap-4 relative"
          >
            <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 text-xs font-bold ring-4 ring-white dark:ring-slate-800 shadow">
              ✓
            </div>
            <div class="flex-1 text-xs">
              <div class="flex items-center justify-between gap-2">
                <StatusBadge :status="hist.to_status" size="sm" />
                <span class="text-[11px] text-slate-400 font-mono">{{ hist.created_at_formatted || formatDate(hist.created_at) }}</span>
              </div>
              <p class="text-slate-700 dark:text-slate-300 mt-1 leading-relaxed font-medium">
                {{ hist.notes || 'Status diperbarui.' }}
              </p>

              <!-- Direct CTA Action Button in Timeline (Pre-Verified Phase, only if active) -->
              <div
                v-if="['PRE_VERIFIED', 'APPROVED'].includes(hist.to_status) && !application.queue_number && !['COMPLETED', 'READY_FOR_PICKUP', 'PROCESSING', 'IN_SERVICE', 'APPROVED_FOR_SERVICE', 'PHYSICAL_VERIFIED', 'PHYSICAL_VERIFICATION', 'ARRIVED', 'CHECKED_IN', 'REJECTED', 'CANCELLED'].includes(application.status)"
                class="mt-3 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs"
              >
                <div class="text-xs text-emerald-900 dark:text-emerald-200 space-y-0.5">
                  <span class="font-extrabold flex items-center gap-1.5 text-emerald-800 dark:text-emerald-300">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Aksi Lanjutan: Pilih Jadwal Pelayanan
                  </span>
                  <p class="text-[11px] text-emerald-700 dark:text-emerald-300 leading-snug">
                    Klik tombol di samping untuk langsung memilih tanggal kedatangan dan menerbitkan nomor antrean resmi Anda.
                  </p>
                </div>
                <Link
                  :href="bookingUrl"
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 shrink-0 hover:scale-[1.02] active:scale-95 cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span>Pilih Jadwal Sekarang &rarr;</span>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cancellation Option (If permitted) -->
      <div
        v-if="application.can_be_cancelled"
        class="text-center pt-4 print:hidden"
      >
        <button
          type="button"
          @click="confirmCancelApplication"
          class="text-xs text-rose-500 hover:text-rose-600 dark:hover:text-rose-400 font-medium underline"
        >
          Batalkan Pengajuan Ini
        </button>
      </div>
    </div>

    <!-- Document Viewer Modal -->
    <DocumentViewerModal
      v-if="activePreviewDoc"
      :src="activePreviewDoc.preview_url"
      :title="activePreviewDoc.title"
      :mime-type="activePreviewDoc.mime_type"
      @close="activePreviewDoc = null"
    />

    <!-- Camera Capture Modal for Revision -->
    <CameraCapture
      v-if="activeRevisionCameraDoc"
      :document-label="activeRevisionCameraDoc.service_document?.label || 'Dokumen Revisi'"
      :capture-mode="activeRevisionCameraDoc.service_document?.capture_mode || 'environment'"
      :capture-hint="activeRevisionCameraDoc.service_document?.capture_hint"
      @close="activeRevisionCameraDoc = null"
      @captured="handleRevisionPhotoCaptured"
    />

    <!-- Fullscreen / High-Res QR Code Modal (Optimized for Loket Scanner Gun & Phone Display) -->
    <div
      v-if="showQrModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm print:hidden animate-in fade-in duration-150"
      @click.self="showQrModal = false"
    >
      <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center shadow-2xl relative space-y-4">
        <!-- Close Button -->
        <button
          type="button"
          @click="showQrModal = false"
          class="absolute top-4 right-4 p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <!-- Dukcapil Header in Modal -->
        <div>
          <p class="text-[10px] font-black uppercase tracking-widest text-blue-600">
            DISDUKCAPIL KABUPATEN DOMPU
          </p>
          <h3 class="text-base font-black text-slate-900 mt-0.5">
            QR Code Pelacakan & Pengambilan
          </h3>
          <p class="text-xs text-slate-500 mt-0.5">
            {{ application.service_type?.name }}
          </p>
        </div>

        <!-- High Contrast QR Box -->
        <div class="p-4 sm:p-6 bg-white rounded-2xl border-2 border-slate-200 shadow-inner flex items-center justify-center aspect-square w-64 h-64 sm:w-72 sm:h-72 mx-auto">
          <div
            v-if="application.qr_code_svg"
            v-html="application.qr_code_svg"
            class="w-full h-full flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:max-w-full [&_svg]:max-h-full"
          ></div>
        </div>

        <!-- Details below QR -->
        <div class="space-y-1">
          <p class="text-lg font-black font-mono tracking-tight text-slate-900">
            {{ application.reg_number }}
          </p>
          <p class="text-xs font-semibold text-slate-700">
            {{ application.applicant_name }}
          </p>
          <div class="pt-2 text-[11px] text-slate-500 leading-relaxed bg-blue-50/70 p-3 rounded-xl border border-blue-100 flex items-start gap-2 text-left">
            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Tunjukkan layar ini kepada petugas di loket pelayanan atau pengambilan KTP-el untuk di-scan menggunakan scanner QR / barcode.</span>
          </div>
        </div>

        <!-- Action Button -->
        <div class="pt-2">
          <button
            type="button"
            @click="showQrModal = false"
            class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition cursor-pointer"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Fullscreen / High-Res Applicant Photo Lightbox Modal -->
    <div
      v-if="showSelfieModal && application.selfie_url"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm print:hidden animate-in fade-in duration-150"
      @click.self="showSelfieModal = false"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 max-w-sm w-full text-center shadow-2xl relative space-y-4 border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
          <div>
            <h3 class="font-black text-sm text-slate-900 dark:text-white">Foto Wajah Pemohon</h3>
            <p class="text-[11px] text-slate-500">{{ application.applicant_name }} ({{ application.reg_number }})</p>
          </div>
          <button
            type="button"
            @click="showSelfieModal = false"
            class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center cursor-pointer text-sm font-bold"
          >
            ✕
          </button>
        </div>
        <div class="w-full aspect-3/4 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 border-2 border-blue-500/30 shadow-inner">
          <img
            :src="application.selfie_url"
            :alt="application.applicant_name"
            class="w-full h-full object-cover"
          />
        </div>
        <p class="text-[10px] text-slate-400">
          Foto biometrik resmi untuk verifikasi fisik di loket pelayanan Disdukcapil Dompu
        </p>
        <button
          type="button"
          @click="showSelfieModal = false"
          class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition cursor-pointer"
        >
          Tutup Pratinjau
        </button>
      </div>
    </div>

    <!-- Copy Toast Notification -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform translate-y-2 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-2 opacity-0"
    >
      <div
        v-if="toastMessage"
        class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white text-xs font-bold px-4 py-3 rounded-2xl shadow-2xl border border-slate-700 flex items-center gap-2 print:hidden"
      >
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        <span>{{ toastMessage }}</span>
      </div>
    </transition>
  </PublicLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StatusBadge from '@/Components/Queue/StatusBadge.vue';
import DocumentViewerModal from '@/Components/Queue/DocumentViewerModal.vue';
import CameraCapture from '@/Components/Queue/CameraCapture.vue';

const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
  officeAddress: {
    type: String,
    default: '',
  },
  helpdeskPhone: {
    type: String,
    default: '',
  },
});

const activePreviewDoc = ref(null);
const activeRevisionCameraDoc = ref(null);
const revisionFiles = ref({});
const revisionForm = useForm({});
const toastMessage = ref('');
const showQrModal = ref(false);
const showSelfieModal = ref(false);
const selfieImgFailed = ref(false);
let toastTimer = null;

function copyText(text, label) {
  if (!text) return;
  navigator.clipboard.writeText(text).then(() => {
    toastMessage.value = `${label} (${text}) berhasil disalin ke clipboard!`;
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toastMessage.value = '';
    }, 3000);
  }).catch(() => {
    toastMessage.value = `${label}: ${text}`;
  });
}

const bookingUrl = computed(() => {
  try {
    if (typeof route === 'function') {
      if (route().has && route().has('queue.booking')) {
        return route('queue.booking', { token: props.application?.access_token });
      }
      if (route().has && route().has('queue.booking.show')) {
        return route('queue.booking.show', { token: props.application?.access_token });
      }
    }
  } catch (e) {
    // ignore
  }
  return `/layanan/antrian/status/${props.application?.access_token}/booking`;
});

const canReschedule = computed(() => {
  if (!props.application?.queue_number) return false;

  // Status dokumen telah selesai, siap diambil, sedang diproses mesin/loket, verifikasi fisik, atau telah hadir di loket - LINK MUTLAK TIDAK MUNCUL
  const lockedStatuses = [
    'COMPLETED',
    'READY_FOR_PICKUP',
    'PROCESSING',
    'IN_SERVICE',
    'APPROVED_FOR_SERVICE',
    'PHYSICAL_VERIFIED',
    'PHYSICAL_VERIFICATION',
    'PHYSICAL_VERIFICATION_REJECTED',
    'CHECKED_IN',
    'ARRIVED',
    'REJECTED',
    'CANCELLED',
    'DRAFT',
    'SUBMITTED',
    'UNDER_REVIEW',
  ];

  if (lockedStatuses.includes(props.application?.status)) {
    return false;
  }

  // Jika modul serah terima sudah menandai siap diambil atau sudah diserahkan
  if (['SUDAH_DISERAHKAN', 'SIAP_DIAMBIL'].includes(props.application?.handover?.status)) {
    return false;
  }

  // Hanya permohonan dengan jadwal resmi aktif yang belum diproses loket yang boleh ubah tanggal
  return ['QUEUE_BOOKED', 'SCHEDULED'].includes(props.application?.status);
});

const attendanceLabel = computed(() => {
  const map = {
    self: 'Saya Sendiri (Pemilik Dokumen)',
    family_in_kk: 'Keluarga (Satu KK)',
    family_out_kk: 'Keluarga (Beda KK)',
    other: 'Orang Lain (Penerima Kuasa)',
  };
  return map[props.application.attendance_type] || props.application.attendance_type || 'Saya Sendiri';
});

const scheduleDateFormatted = computed(() => {
  const slot = props.application.slot;
  if (!slot || !slot.schedule) return '-';
  if (slot.schedule.schedule_date_formatted) {
    return slot.schedule.schedule_date_formatted;
  }
  const dateStr = slot.schedule.schedule_date;
  if (typeof dateStr === 'string' && dateStr.includes('T')) {
    const clean = dateStr.split('T')[0];
    const [y, m, day] = clean.split('-').map(Number);
    return new Date(y, m - 1, day).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  }
  const d = new Date(dateStr);
  return isNaN(d.getTime()) ? String(dateStr) : d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

const scheduleTimeFormatted = computed(() => {
  const slot = props.application.slot;
  if (!slot || !slot.schedule) return '08:00 - 15:30 WITA';
  const open = (slot.schedule.open_time || '08:00').toString().slice(0, 5);
  const close = (slot.schedule.close_time || '15:30').toString().slice(0, 5);
  return `${open} - ${close} WITA`;
});

const accentBarColor = computed(() => {
  switch (props.application.status) {
    case 'APPROVED':
    case 'SCHEDULED':
    case 'COMPLETED':
      return 'bg-emerald-500';
    case 'REVISION_REQUIRED':
      return 'bg-orange-500';
    case 'REJECTED':
      return 'bg-rose-500';
    default:
      return 'bg-blue-600';
  }
});

const revisionDocuments = computed(() => {
  return props.application.documents?.filter(
    (d) => d.status === 'REVISION_REQUIRED' || d.status === 'REJECTED'
  ) || [];
});

function docStatusLabel(status) {
  switch (status) {
    case 'APPROVED':
    case 'VERIFIED':
    case 'EXAMINED':
      return 'Sudah Diperiksa';
    case 'REVISION_REQUIRED':
      return 'Perlu Perbaikan';
    case 'REJECTED':
      return 'Dokumen Ditolak';
    case 'PENDING':
    default:
      return 'Belum Diperiksa';
  }
}

function openPreviewDoc(doc) {
  activePreviewDoc.value = {
    preview_url: route('queue.serve-doc', {
      token: props.application.access_token,
      documentId: doc.id,
    }),
    title: doc.original_filename,
    mime_type: doc.mime_type,
  };
}

function openRevisionCamera(doc) {
  activeRevisionCameraDoc.value = doc;
}

function handleRevisionPhotoCaptured(file) {
  if (!activeRevisionCameraDoc.value) return;
  revisionFiles.value[activeRevisionCameraDoc.value.service_document_id] = file;
  activeRevisionCameraDoc.value = null;
}

function submitRevision() {
  const formData = new FormData();
  Object.keys(revisionFiles.value).forEach((secDocId) => {
    formData.append(`documents[${secDocId}]`, revisionFiles.value[secDocId]);
  });

  router.post(
    route('queue.resubmit', { token: props.application.access_token }),
    formData,
    {
      onSuccess: () => {
        revisionFiles.value = {};
      },
    }
  );
}

function confirmCancelApplication() {
  if (confirm('Apakah Anda yakin ingin membatalkan pengajuan antrian ini? Tindakan ini tidak dapat dibatalkan.')) {
    router.post(route('queue.cancel', { token: props.application.access_token }));
  }
}

function windowPrint() {
  window.print();
}

function formatDate(isoStr) {
  if (!isoStr) return '-';
  try {
    const d = new Date(isoStr);
    if (isNaN(d.getTime())) return String(isoStr);
    return d.toLocaleString('id-ID', {
      timeZone: 'Asia/Makassar',
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }) + ' WITA';
  } catch (e) {
    return String(isoStr);
  }
}

const printTimestamp = computed(() => {
  return new Date().toLocaleString('id-ID', {
    timeZone: 'Asia/Makassar',
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }) + ' WITA';
});
</script>

<style>
@media print {
  @page {
    size: portrait;
    margin: 8mm 10mm;
  }

  /* Reset body and canvas */
  html, body {
    background: #ffffff !important;
    background-color: #ffffff !important;
    color: #0f172a !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Hide layout elements: headers, footers, navs, floating bars */
  header,
  footer,
  nav,
  aside,
  .sticky,
  .print\:hidden {
    display: none !important;
  }

  main {
    margin: 0 !important;
    padding: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
  }

  /* Force hide all siblings except the receipt/ticket card */
  .queue-dashboard-container > *:not(#bukti-permohonan) {
    display: none !important;
  }

  /* Receipt / Ticket Card Print Layout */
  #bukti-permohonan {
    display: block !important;
    position: relative !important;
    width: 100% !important;
    max-width: 600px !important;
    margin: 0 auto !important;
    padding: 20px 24px !important;
    border: 2px dashed #1e293b !important;
    border-radius: 14px !important;
    box-shadow: none !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    color: #0f172a !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  /* Hide print trigger button inside ticket */
  #bukti-permohonan button {
    display: none !important;
  }

  /* Subcontainers inside receipt: preserve borders & light backgrounds */
  #bukti-permohonan .bg-slate-50,
  #bukti-permohonan .dark\:bg-slate-900\/60,
  #bukti-permohonan .dark\:bg-slate-900\/40 {
    background-color: #f8fafc !important;
    border-color: #cbd5e1 !important;
  }

  #bukti-permohonan .bg-amber-50\/70,
  #bukti-permohonan .dark\:bg-amber-950\/30 {
    background-color: #fefce8 !important;
    border-color: #fde047 !important;
  }

  #bukti-permohonan .text-blue-600,
  #bukti-permohonan .dark\:text-blue-400 {
    color: #1d4ed8 !important;
  }

  #bukti-permohonan .bg-blue-100,
  #bukti-permohonan .dark\:bg-blue-900\/40 {
    background-color: #dbeafe !important;
    color: #1e40af !important;
  }
}
</style>
