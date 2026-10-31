<template>
  <Head title="Meja Loket & Verifikasi Fisik - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Page Header & Global Search Bar -->
      <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-indigo-900/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30 mb-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              Front-Desk / Meja Loket Fisik Pelayanan
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Pemeriksaan Dokumen Fisik & Serah Terima KTP-el
            </h1>
            <p class="text-xs sm:text-sm text-indigo-200 mt-1 max-w-xl">
              Verifikasi online hanyalah pemeriksaan administrasi awal. Cocokkan identitas fisik asli dan berkas fisik pemohon atau kuasanya di meja loket sebelum proses cetak/rekam.
            </p>
          </div>

          <!-- Quick Search Box -->
          <form @submit.prevent="handleSearch" class="w-full md:w-96">
            <label class="block text-xs font-bold text-indigo-200 mb-1.5">
              Cari Pemohon / Nomor Antrean:
            </label>
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="No Antrean (A-001) / NIK / No Reg..."
                class="w-full px-4 py-3 pl-10 pr-24 rounded-2xl bg-white/10 backdrop-blur border border-white/20 text-white placeholder-indigo-300/60 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white/20 transition"
              />
              <svg class="w-4 h-4 text-indigo-300 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <button
                type="submit"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow"
              >
                Cari
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Main Split Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- LEFT 4 COLS: Queue Roster (Today's queues) -->
        <div class="lg:col-span-4 space-y-4">
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700/60 mb-3">
              <div>
                <h2 class="text-sm font-black text-slate-900 dark:text-white">
                  Antrean Hari Ini
                </h2>
                <p class="text-[11px] text-slate-400">Total: {{ todayApplications.length }} pendaftar</p>
              </div>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400">
                Live Roster
              </span>
            </div>

            <!-- Scrollable list of today's queue -->
            <div class="space-y-2 max-h-[620px] overflow-y-auto pr-1">
              <button
                v-for="app in todayApplications"
                :key="app.id"
                type="button"
                @click="selectApplication(app)"
                :class="[
                  'w-full p-3.5 rounded-2xl text-left border transition cursor-pointer flex items-center justify-between gap-3',
                  selectedApplication?.id === app.id
                    ? 'border-blue-600 bg-blue-50/70 dark:bg-blue-950/40 ring-2 ring-blue-500/20'
                    : 'border-slate-200 dark:border-slate-700/70 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/40'
                ]"
              >
                <div class="space-y-1 min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-mono font-black text-xs text-blue-600 dark:text-blue-400">
                      {{ app.queue_number || app.reg_number }}
                    </span>
                    <StatusBadge :status="app.status" size="sm" />
                  </div>
                  <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
                    {{ app.applicant_name }}
                  </p>
                  <p class="text-[10px] text-slate-400 truncate">
                    {{ app.service_type?.name }} · <span class="font-semibold text-slate-600 dark:text-slate-300">{{ app.attendance_type === 'self' ? 'Sendiri' : 'Diwakilkan' }}</span>
                  </p>
                </div>

                <div class="text-right shrink-0">
                  <span class="text-[10px] font-mono text-slate-400 block">
                    {{ app.slot?.start_time?.substring(0, 5) || '-' }}
                  </span>
                  <svg class="w-4 h-4 text-slate-400 ml-auto mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
              </button>

              <div v-if="todayApplications.length === 0" class="text-center py-8 text-xs text-slate-400">
                Tidak ada antrean terjadwal hari ini.
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT 8 COLS: Active Processing Workspace -->
        <div class="lg:col-span-8 space-y-6">
          <template v-if="selectedApplication">
            <!-- Selected Application Header Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700/60">
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-mono font-bold text-slate-400">
                      {{ selectedApplication.reg_number }}
                    </span>
                    <StatusBadge :status="selectedApplication.status" size="md" />
                    <span
                      v-if="selectedApplication.service_type?.is_biometric_first_recording"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-300"
                    >
                      Perekaman Wajib Hadir Fisik
                    </span>
                  </div>
                  <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">
                    {{ selectedApplication.service_type?.name }}
                  </h2>
                </div>

                <!-- Queue Pill -->
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-2xl px-5 py-3 text-center shadow-md">
                  <span class="text-[9px] uppercase font-bold tracking-widest text-blue-200 block">Nomor Antrean</span>
                  <span class="text-3xl font-black font-mono tracking-tight">{{ selectedApplication.queue_number || 'Belum Terbit' }}</span>
                </div>
              </div>

              <!-- Identity & Representation Details Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Pemilik Dokumen Card -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 space-y-2">
                  <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">
                    1. Pemilik Dokumen (Pemohon)
                  </span>
                  <div>
                    <span class="text-slate-400 text-[10px] block">Nama Lengkap Sesuai KK:</span>
                    <p class="font-bold text-slate-900 dark:text-white text-sm">{{ selectedApplication.applicant_name }}</p>
                  </div>
                  <div>
                    <span class="text-slate-400 text-[10px] block">NIK:</span>
                    <p class="font-mono font-bold text-slate-900 dark:text-white">{{ selectedApplication.applicant_nik }}</p>
                  </div>
                  <div v-if="selectedApplication.applicant_birth_place || selectedApplication.applicant_birth_date">
                    <span class="text-slate-400 text-[10px] block">Tempat, Tgl Lahir:</span>
                    <p class="text-slate-700 dark:text-slate-300">{{ selectedApplication.applicant_birth_place || '-' }}, {{ selectedApplication.applicant_birth_date_formatted || formatBirthDate(selectedApplication.applicant_birth_date) }}</p>
                  </div>
                  <div>
                    <span class="text-slate-400 text-[10px] block">Alamat / Kecamatan:</span>
                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ selectedApplication.applicant_address }}</p>
                  </div>
                  <div>
                    <span class="text-slate-400 text-[10px] block">No. Handphone:</span>
                    <a :href="`https://wa.me/${cleanPhone(selectedApplication.applicant_phone)}`" target="_blank" class="font-mono text-blue-600 font-bold hover:underline">
                      {{ selectedApplication.applicant_phone }}
                    </a>
                  </div>
                </div>

                <!-- Kehadiran & Perwakilan Card -->
                <div
                  :class="[
                    'p-4 rounded-2xl border space-y-2',
                    selectedApplication.attendance_type === 'self'
                      ? 'bg-blue-50/50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800'
                      : 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800'
                  ]"
                >
                  <span class="text-[10px] font-black uppercase tracking-wider block" :class="selectedApplication.attendance_type === 'self' ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400'">
                    2. Status Kehadiran Pelayanan: {{ selectedApplication.attendance_type === 'self' ? 'Datang Sendiri' : 'Diwakilkan' }}
                  </span>

                  <template v-if="selectedApplication.attendance_type !== 'self'">
                    <div>
                      <span class="text-slate-400 text-[10px] block">Nama Yang Mewakili (Penerima Kuasa):</span>
                      <p class="font-bold text-slate-900 dark:text-white text-sm">{{ selectedApplication.representative_name }}</p>
                    </div>
                    <div>
                      <span class="text-slate-400 text-[10px] block">NIK Perwakilan:</span>
                      <p class="font-mono font-bold text-slate-900 dark:text-white">{{ selectedApplication.representative_nik }}</p>
                    </div>
                    <div>
                      <span class="text-slate-400 text-[10px] block">Hubungan Keluarga:</span>
                      <p class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ selectedApplication.representative_relationship }} ({{ selectedApplication.representative_family_in_kk ? 'Satu KK' : 'Di Luar KK / Pihak Luar' }})
                      </p>
                    </div>
                    <div v-if="selectedApplication.representative_phone">
                      <span class="text-slate-400 text-[10px] block">No. HP Perwakilan:</span>
                      <p class="font-mono text-slate-800 dark:text-slate-200">{{ selectedApplication.representative_phone }}</p>
                    </div>
                    <div v-if="selectedApplication.representative_reason">
                      <span class="text-slate-400 text-[10px] block">Alasan Pemilik Dokumen Berhalangan:</span>
                      <p class="text-slate-600 dark:text-slate-400 italic">"{{ selectedApplication.representative_reason }}"</p>
                    </div>
                  </template>

                  <template v-else>
                    <div class="py-4 text-center">
                      <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 mx-auto flex items-center justify-center font-bold mb-2">
                        👤
                      </div>
                      <p class="font-bold text-slate-800 dark:text-slate-200">Pemilik Dokumen Datang Sendiri</p>
                      <p class="text-[11px] text-slate-500 mt-1">
                        Pencocokan biometrik dan wajah dilakukan langsung terhadap pemilik KTP-el.
                      </p>
                    </div>
                  </template>
                </div>
              </div>

              <!-- Uploaded Digital Document Thumbnails for Fast In-Hand Matching -->
              <div class="pt-2">
                <span class="text-[11px] font-black uppercase text-slate-400 tracking-wider block mb-3">
                  Dokumen Digital Pemohon (Untuk Dicocokkan dengan Fisik Asli)
                </span>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  <div
                    v-for="doc in selectedApplication.documents"
                    :key="doc.id"
                    class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex flex-col justify-between"
                  >
                    <div>
                      <p class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate" :title="doc.service_document?.label">
                        {{ doc.service_document?.label || doc.original_filename }}
                      </p>
                      <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                        {{ Math.round((doc.file_size || 0) / 1024) }} KB
                      </p>
                    </div>

                    <button
                      type="button"
                      @click="openViewer(doc)"
                      class="mt-2 w-full py-1.5 px-2 rounded-lg bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-[10px] font-bold transition flex items-center justify-center gap-1"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      Lihat Berkas
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- WORKFLOW MODULE 1: CHECK-IN KEDATANGAN LOKET -->
            <div
              v-if="['QUEUE_BOOKED', 'SCHEDULED', 'PRE_VERIFIED'].includes(selectedApplication.status)"
              class="bg-white dark:bg-slate-800 rounded-3xl p-6 border-2 border-blue-500 shadow-md space-y-4"
            >
              <div class="flex items-center justify-between">
                <div>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 uppercase">
                    Tahap 1: Kedatangan
                  </span>
                  <h3 class="text-base font-black text-slate-900 dark:text-white mt-1">
                    Check-In Kedatangan Pemohon di Loket
                  </h3>
                  <p class="text-xs text-slate-500">
                    Konfirmasi kedatangan fisik sebelum berkas fisik asli diperiksa.
                  </p>
                </div>
              </div>

              <form @submit.prevent="submitCheckIn" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Yang Hadir di Loket *</label>
                    <select
                      v-model="checkInForm.attended_person_type"
                      class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                      @change="handleCheckInPersonTypeChange"
                    >
                      <option value="self">Pemohon Sendiri</option>
                      <option value="representative">Perwakilan / Kuasa</option>
                    </select>
                  </div>
                  <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Orang Yang Hadir *</label>
                    <input
                      v-model="checkInForm.attended_person_name"
                      type="text"
                      class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                      required
                    />
                  </div>
                  <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">NIK Orang Yang Hadir *</label>
                    <input
                      v-model="checkInForm.attended_person_nik"
                      type="text"
                      maxlength="16"
                      class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-mono"
                      required
                    />
                  </div>
                </div>

                <div class="flex justify-end pt-2">
                  <button
                    type="submit"
                    :disabled="checkInSubmitting"
                    class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ checkInSubmitting ? 'Mencatat...' : 'Konfirmasi Check-In Hadir di Loket' }}</span>
                  </button>
                </div>
              </form>
            </div>

            <!-- WORKFLOW MODULE 2: VERIFIKASI DOKUMEN FISIK ASLI & KEPUTUSAN -->
            <div
              v-if="['ARRIVED', 'CHECKED_IN', 'PHYSICAL_VERIFICATION'].includes(selectedApplication.status)"
              class="bg-white dark:bg-slate-800 rounded-3xl p-6 border-2 border-amber-500 shadow-md space-y-4"
            >
              <div class="flex items-center justify-between">
                <div>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 uppercase">
                    Tahap 2: Verifikasi Dokumen Fisik Asli
                  </span>
                  <h3 class="text-base font-black text-slate-900 dark:text-white mt-1">
                    Pemeriksaan Fisik Asli & Kesesuaian Identitas
                  </h3>
                  <p class="text-xs text-slate-500">
                    Cocokkan berkas fisik asli yang dibawa pemohon/kuasa di loket dengan data sistem.
                  </p>
                </div>
              </div>

              <!-- Checklist physically carried -->
              <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2.5 text-xs">
                <label class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                  <input v-model="physicalForm.match_identity" type="checkbox" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500" />
                  <span>Kesesuaian Identitas: Wajah orang yang hadir cocok dengan identitas KTP / Foto Pemohon / Kuasa</span>
                </label>
                <label class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                  <input v-model="physicalForm.match_documents" type="checkbox" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500" />
                  <span>Kelengkapan Berkas Fisik: Seluruh dokumen persyaratan asli dibawa dan sah (KK Asli, Surat Hilang Asli, dll)</span>
                </label>
                <label v-if="selectedApplication.attendance_type !== 'self'" class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                  <input v-model="physicalForm.match_authorization" type="checkbox" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500" />
                  <span>Keabsahan Kuasa: Surat Kuasa asli ditandatangani dan KTP-el asli penerima kuasa ditunjukkan</span>
                </label>
              </div>

              <!-- Decision Radios -->
              <div class="space-y-2 text-xs">
                <label class="block font-bold text-slate-700 dark:text-slate-300">Keputusan Petugas Loket: *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                  <label
                    :class="[
                      'p-3 rounded-xl border text-left cursor-pointer flex items-center gap-2.5 transition',
                      physicalForm.decision === 'APPROVED_FOR_SERVICE'
                        ? 'border-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-bold'
                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300'
                    ]"
                  >
                    <input v-model="physicalForm.decision" type="radio" value="APPROVED_FOR_SERVICE" class="text-emerald-600" />
                    <span>Dokumen Fisik Sah & Lengkap</span>
                  </label>

                  <label
                    :class="[
                      'p-3 rounded-xl border text-left cursor-pointer flex items-center gap-2.5 transition',
                      physicalForm.decision === 'REVISION_REQUIRED'
                        ? 'border-amber-600 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 font-bold'
                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300'
                    ]"
                  >
                    <input v-model="physicalForm.decision" type="radio" value="REVISION_REQUIRED" class="text-amber-600" />
                    <span>Berkas Kurang / Ragu</span>
                  </label>

                  <label
                    :class="[
                      'p-3 rounded-xl border text-left cursor-pointer flex items-center gap-2.5 transition',
                      physicalForm.decision === 'REJECTED'
                        ? 'border-rose-600 bg-rose-50 dark:bg-rose-950/40 text-rose-900 dark:text-rose-200 font-bold'
                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300'
                    ]"
                  >
                    <input v-model="physicalForm.decision" type="radio" value="REJECTED" class="text-rose-600" />
                    <span>Dokumen Tidak Sah / Palsu</span>
                  </label>

                  <label
                    :class="[
                      'p-3 rounded-xl border text-left cursor-pointer flex items-center gap-2.5 transition',
                      physicalForm.decision === 'RESCHEDULE'
                        ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-bold'
                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300'
                    ]"
                  >
                    <input v-model="physicalForm.decision" type="radio" value="RESCHEDULE" class="text-indigo-600" />
                    <span>Jadwal Ulang</span>
                  </label>
                </div>

                <div class="pt-2">
                  <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Verifikasi Fisik:</label>
                  <textarea
                    v-model="physicalForm.notes"
                    rows="2"
                    placeholder="Tuliskan catatan pemeriksaan dokumen fisik..."
                    class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                  ></textarea>
                </div>

                <div class="flex justify-end pt-2">
                  <button
                    type="button"
                    @click="submitPhysicalVerification"
                    :disabled="physicalSubmitting"
                    class="px-6 py-3 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition flex items-center gap-2"
                  >
                    <span>{{ physicalSubmitting ? 'Menyimpan...' : 'Simpan Keputusan Verifikasi Fisik' }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- WORKFLOW MODULE 3: PROSES LAYANAN (REKAM / CETAK) -->
            <div
              v-if="['APPROVED_FOR_SERVICE', 'PROCESSING', 'IN_SERVICE'].includes(selectedApplication.status)"
              class="bg-white dark:bg-slate-800 rounded-3xl p-6 border-2 border-purple-500 shadow-md space-y-4"
            >
              <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 uppercase">
                  Tahap 3: Perekaman Biometrik / Pencetakan KTP-el
                </span>
                <h3 class="text-base font-black text-slate-900 dark:text-white mt-1">
                  Pelaksanaan Pelayanan Teknis
                </h3>
                <p class="text-xs text-slate-500">
                  Perbarui status saat operator mulai memproses di sistem SIAK atau saat mesin cetak selesai.
                </p>
              </div>

              <div class="flex flex-wrap items-center gap-3 pt-2">
                <button
                  v-if="selectedApplication.status === 'APPROVED_FOR_SERVICE'"
                  type="button"
                  @click="handleStartProcessing"
                  class="px-6 py-3 rounded-2xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-md shadow-purple-600/20 transition flex items-center gap-2"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span>Mulai Perekaman / Pencetakan</span>
                </button>

                <button
                  v-if="['PROCESSING', 'IN_SERVICE'].includes(selectedApplication.status)"
                  type="button"
                  @click="handleReadyForPickup"
                  class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  <span>Tandai KTP Selesai Dicetak & Siap Diambil</span>
                </button>
              </div>
            </div>

            <!-- WORKFLOW MODULE 4: SERAH TERIMA FISIK KTP-EL (HANDOVER) -->
            <div
              v-if="['READY_FOR_PICKUP'].includes(selectedApplication.status)"
              class="bg-white dark:bg-slate-800 rounded-3xl p-6 border-2 border-emerald-500 shadow-md space-y-4"
            >
              <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase">
                  Tahap 4: Serah Terima Fisik KTP-el
                </span>
                <h3 class="text-base font-black text-slate-900 dark:text-white mt-1">
                  Penyerahan Fisik Dokumen Kependudukan
                </h3>
                <p class="text-xs text-slate-500">
                  Catat nama dan NIK pihak yang menerima fisik KTP-el secara sah.
                </p>
              </div>

              <form @submit.prevent="submitHandover" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Diterima Oleh: *</label>
                    <select
                      v-model="handoverForm.received_by_type"
                      class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                      @change="handleHandoverTypeChange"
                    >
                      <option value="self">Pemilik Dokumen Sendiri</option>
                      <option value="representative">Perwakilan / Kuasa yang Sah</option>
                    </select>
                  </div>
                  <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Penerima: *</label>
                    <input
                      v-model="handoverForm.received_by_name"
                      type="text"
                      class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                      required
                    />
                  </div>
                  <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">NIK Penerima: *</label>
                    <input
                      v-model="handoverForm.received_by_nik"
                      type="text"
                      maxlength="16"
                      class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-mono"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Serah Terima (Opsional):</label>
                  <input
                    v-model="handoverForm.notes"
                    type="text"
                    placeholder="Contoh: KTP lama yang rusak telah diserahkan dan ditarik dinas."
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                  />
                </div>

                <div class="flex justify-end pt-2">
                  <button
                    type="submit"
                    :disabled="handoverSubmitting"
                    class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ handoverSubmitting ? 'Menyelesaikan...' : 'Konfirmasi Serah Terima Fisik (Layanan Selesai)' }}</span>
                  </button>
                </div>
              </form>
            </div>

            <!-- COMPLETED STATE DISPLAY -->
            <div
              v-if="selectedApplication.status === 'COMPLETED'"
              class="bg-green-50 dark:bg-green-950/40 rounded-3xl p-6 border-2 border-green-500 shadow-sm flex items-start gap-4"
            >
              <div class="w-10 h-10 rounded-2xl bg-green-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              </div>
              <div class="text-xs">
                <h4 class="text-base font-black text-green-950 dark:text-green-200">
                  Layanan Telah Selesai
                </h4>
                <p class="text-green-800 dark:text-green-300 mt-1 leading-relaxed">
                  Fisik KTP-el telah diserahkan secara sah kepada <strong>{{ selectedApplication.handover_recipient_name }}</strong> (NIK: {{ selectedApplication.handover_recipient_nik }}) pada {{ formatDate(selectedApplication.handover_at) }}.
                </p>
              </div>
            </div>
          </template>

          <!-- Empty State: No Application Selected -->
          <div
            v-else
            class="bg-white dark:bg-slate-800 rounded-3xl p-12 text-center border border-slate-200 dark:border-slate-700 shadow-sm"
          >
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-400 mx-auto flex items-center justify-center mb-4">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">
              Pilih Antrean atau Cari Pemohon
            </h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
              Gunakan kolom pencarian di atas atau pilih salah satu antrean dari daftar sebelah kiri untuk memulai pemeriksaan berkas fisik di meja loket.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Viewer Modal -->
    <DocumentViewerModal
      v-if="viewerDoc"
      :src="viewerDoc.url"
      :title="viewerDoc.title"
      :mime-type="viewerDoc.mime_type"
      @close="viewerDoc = null"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/Queue/StatusBadge.vue';
import DocumentViewerModal from '@/Components/Queue/DocumentViewerModal.vue';

const props = defineProps({
  search: {
    type: String,
    default: '',
  },
  selectedApplication: {
    type: Object,
    default: null,
  },
  todayApplications: {
    type: Array,
    default: () => [],
  },
});

const searchQuery = ref(props.search);
const viewerDoc = ref(null);

// Check-in form
const checkInSubmitting = ref(false);
const checkInForm = ref({
  attended_person_type: 'self',
  attended_person_name: '',
  attended_person_nik: '',
});

// Physical verification form
const physicalSubmitting = ref(false);
const physicalForm = ref({
  decision: 'APPROVED_FOR_SERVICE',
  notes: '',
  match_identity: true,
  match_documents: true,
  match_authorization: true,
});

// Handover form
const handoverSubmitting = ref(false);
const handoverForm = ref({
  received_by_type: 'self',
  received_by_name: '',
  received_by_nik: '',
  notes: '',
});

// Populate forms when selectedApplication changes
watch(
  () => props.selectedApplication,
  (app) => {
    if (!app) return;

    // CheckIn form init
    if (app.attendance_type === 'self') {
      checkInForm.value.attended_person_type = 'self';
      checkInForm.value.attended_person_name = app.applicant_name;
      checkInForm.value.attended_person_nik = app.applicant_nik;
    } else {
      checkInForm.value.attended_person_type = 'representative';
      checkInForm.value.attended_person_name = app.representative_name || '';
      checkInForm.value.attended_person_nik = app.representative_nik || '';
    }

    // Handover form init
    if (app.attendance_type === 'self') {
      handoverForm.value.received_by_type = 'self';
      handoverForm.value.received_by_name = app.applicant_name;
      handoverForm.value.received_by_nik = app.applicant_nik;
    } else {
      handoverForm.value.received_by_type = 'representative';
      handoverForm.value.received_by_name = app.representative_name || '';
      handoverForm.value.received_by_nik = app.representative_nik || '';
    }
  },
  { immediate: true }
);

function handleSearch() {
  if (!searchQuery.value) return;
  router.get(route('admin.queue.counter.index'), { search: searchQuery.value }, { preserveState: true });
}

function selectApplication(app) {
  searchQuery.value = app.queue_number || app.reg_number;
  router.get(route('admin.queue.counter.index'), { search: searchQuery.value }, { preserveState: true });
}

function handleCheckInPersonTypeChange() {
  const app = props.selectedApplication;
  if (!app) return;
  if (checkInForm.value.attended_person_type === 'self') {
    checkInForm.value.attended_person_name = app.applicant_name;
    checkInForm.value.attended_person_nik = app.applicant_nik;
  } else {
    checkInForm.value.attended_person_name = app.representative_name || '';
    checkInForm.value.attended_person_nik = app.representative_nik || '';
  }
}

function handleHandoverTypeChange() {
  const app = props.selectedApplication;
  if (!app) return;
  if (handoverForm.value.received_by_type === 'self') {
    handoverForm.value.received_by_name = app.applicant_name;
    handoverForm.value.received_by_nik = app.applicant_nik;
  } else {
    handoverForm.value.received_by_name = app.representative_name || '';
    handoverForm.value.received_by_nik = app.representative_nik || '';
  }
}

function submitCheckIn() {
  if (!props.selectedApplication) return;
  checkInSubmitting.value = true;
  router.post(
    route('admin.queue.counter.checkin', props.selectedApplication.id),
    checkInForm.value,
    {
      onFinish: () => {
        checkInSubmitting.value = false;
      },
    }
  );
}

function submitPhysicalVerification() {
  if (!props.selectedApplication) return;
  physicalSubmitting.value = true;
  router.post(
    route('admin.queue.counter.physical-verification', props.selectedApplication.id),
    physicalForm.value,
    {
      onFinish: () => {
        physicalSubmitting.value = false;
      },
    }
  );
}

function handleStartProcessing() {
  if (!props.selectedApplication) return;
  if (confirm(`Mulai proses perekaman/pencetakan untuk pemohon ${props.selectedApplication.applicant_name}?`)) {
    router.post(route('admin.queue.counter.start-processing', props.selectedApplication.id));
  }
}

function handleReadyForPickup() {
  if (!props.selectedApplication) return;
  if (confirm(`Tandai KTP-el selesai dicetak dan siap diambil di loket?`)) {
    router.post(route('admin.queue.counter.ready-pickup', props.selectedApplication.id));
  }
}

function submitHandover() {
  if (!props.selectedApplication) return;
  handoverSubmitting.value = true;
  router.post(
    route('admin.queue.counter.complete', props.selectedApplication.id),
    handoverForm.value,
    {
      onFinish: () => {
        handoverSubmitting.value = false;
      },
    }
  );
}

function openViewer(doc) {
  viewerDoc.value = {
    url: doc.admin_serve_url || route('admin.queue.documents.serve', { documentId: doc.id }),
    title: doc.service_document?.label || doc.original_filename,
    mime_type: doc.mime_type,
  };
}

function cleanPhone(phone) {
  if (!phone) return '';
  let cleaned = phone.replace(/[^0-9]/g, '');
  if (cleaned.startsWith('0')) {
    cleaned = '62' + cleaned.substring(1);
  }
  return cleaned;
}

function formatBirthDate(val) {
  if (!val) return '-';
  const raw = String(val).split('T')[0];
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
    const [year, month, day] = raw.split('-');
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const mIdx = parseInt(month, 10) - 1;
    if (mIdx >= 0 && mIdx < 12) {
      return `${parseInt(day, 10)} ${months[mIdx]} ${year}`;
    }
  }
  return val;
}

function formatDate(dateString) {
  if (!dateString) return '-';
  const d = new Date(dateString);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>
