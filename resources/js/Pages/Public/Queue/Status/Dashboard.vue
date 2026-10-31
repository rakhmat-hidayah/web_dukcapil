<template>
  <Head :title="`Status Pengajuan [${application.reg_number}] - Antrian Online KTP-el`" />

  <PublicLayout>
    <div class="queue-dashboard-container py-8 sm:py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 print:p-0 print:m-0 print:max-w-none print:space-y-0">
      <!-- Status Top Card -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700 shadow-xl relative overflow-hidden print:hidden">
        <!-- Colored top border accent -->
        <div
          class="absolute top-0 left-0 right-0 h-2"
          :class="accentBarColor"
        ></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="space-y-1">
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

          <!-- Queue Ticket Number Pill if Booked/Beyond -->
          <div
            v-if="application.queue_number"
            class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-3xl p-5 text-center shadow-lg shadow-blue-600/30 min-w-[200px]"
          >
            <p class="text-[10px] uppercase font-bold tracking-widest text-blue-200">
              Nomor Antrean
            </p>
            <p class="text-4xl font-black font-mono tracking-tight mt-1">
              {{ application.queue_number }}
            </p>
            <p class="text-[11px] text-blue-100 mt-1 font-medium">
              {{ scheduleDateFormatted }}
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
            <div>
              <p class="font-bold text-sm text-blue-900 dark:text-blue-200">Berkas Sedang Diverifikasi Petugas (Pra-Verifikasi Online)</p>
              <p class="mt-1 leading-relaxed">
                Dokumen persyaratan digital Anda sedang diperiksa kelengkapannya oleh verifikator Disdukcapil Kabupaten Dompu. Begitu lolos pra-verifikasi, tombol pemilihan jadwal antrean akan muncul di halaman ini.
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
            class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-xs text-rose-900 dark:text-rose-200 flex items-start gap-3"
          >
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="font-bold text-sm text-rose-900 dark:text-rose-200">Pengajuan Ditolak</p>
              <p class="mt-1 leading-relaxed">
                Alasan: <em>{{ application.rejection_reason || 'Dokumen tidak memenuhi ketentuan hukum atau persyaratan yang berlaku.' }}</em>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- PRINTABLE TICKET CARD (If Booked or beyond) -->
      <div
        v-if="['QUEUE_BOOKED', 'SCHEDULED', 'ARRIVED', 'CHECKED_IN', 'PHYSICAL_VERIFICATION', 'APPROVED_FOR_SERVICE', 'PROCESSING', 'IN_SERVICE', 'READY_FOR_PICKUP', 'COMPLETED'].includes(application.status)"
        id="tiket-antrian"
        class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border-2 border-dashed border-blue-300 dark:border-blue-800 shadow-xl relative"
      >
        <!-- Print Trigger -->
        <button
          @click="windowPrint"
          class="absolute top-6 right-6 p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition print:hidden"
          title="Cetak Tiket Antrian"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        </button>

        <div class="text-center pb-6 border-b border-slate-100 dark:border-slate-700">
          <p class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">
            DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL KABUPATEN DOMPU
          </p>
          <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">
            TIKET ANTREAN PELAYANAN KTP-EL
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">
            Nomor Registrasi: <span class="font-mono font-bold">{{ application.reg_number }}</span>
          </p>
        </div>

        <div class="py-8 text-center bg-slate-50 dark:bg-slate-900/60 rounded-2xl my-6 border border-slate-200/50 dark:border-slate-800">
          <p class="text-xs uppercase font-bold tracking-widest text-slate-400">Nomor Antrean Anda</p>
          <p class="text-5xl sm:text-6xl font-black font-mono tracking-tight text-blue-600 dark:text-blue-400 mt-2">
            {{ application.queue_number || '-' }}
          </p>
          <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-2">
            {{ application.service_type?.name }}
          </p>
          <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
            Sesi: {{ application.slot?.name || 'Sesi Pelayanan' }} ({{ scheduleTimeFormatted }})
          </div>
        </div>

        <!-- Schedule & Location Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700">
            <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[10px]">Tanggal & Waktu Kedatangan</span>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ scheduleDateFormatted }}</p>
            <p class="text-slate-500 mt-0.5">{{ scheduleTimeFormatted }}</p>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700">
            <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[10px]">Lokasi Pelayanan</span>
            <p class="text-xs font-bold text-slate-900 dark:text-white mt-1">Kantor Dinas Dukcapil Kab. Dompu</p>
            <p class="text-slate-500 mt-0.5 leading-relaxed">{{ officeAddress }}</p>
          </div>
        </div>

        <!-- Attendance & Representative Details on Ticket -->
        <div class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
          Riwayat Progres & Audit Trail
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
  const d = new Date(slot.schedule.schedule_date);
  return d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
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

  /* Force hide all siblings except the ticket card */
  .queue-dashboard-container > *:not(#tiket-antrian) {
    display: none !important;
  }

  /* Ticket Card Print Layout */
  #tiket-antrian {
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
  #tiket-antrian button {
    display: none !important;
  }

  /* Subcontainers inside ticket: preserve borders & light backgrounds */
  #tiket-antrian .bg-slate-50,
  #tiket-antrian .dark\:bg-slate-900\/60,
  #tiket-antrian .dark\:bg-slate-900\/40 {
    background-color: #f8fafc !important;
    border-color: #cbd5e1 !important;
  }

  #tiket-antrian .bg-amber-50\/70,
  #tiket-antrian .dark\:bg-amber-950\/30 {
    background-color: #fefce8 !important;
    border-color: #fde047 !important;
  }

  #tiket-antrian .text-blue-600,
  #tiket-antrian .dark\:text-blue-400 {
    color: #1d4ed8 !important;
  }

  #tiket-antrian .bg-blue-100,
  #tiket-antrian .dark\:bg-blue-900\/40 {
    background-color: #dbeafe !important;
    color: #1e40af !important;
  }
}
</style>
