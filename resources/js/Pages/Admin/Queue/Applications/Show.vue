<template>
  <Head :title="`Verifikasi ${application.reg_number} - Admin Disdukcapil`" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Breadcrumb & Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link
            :href="route('admin.queue.applications.index')"
            class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-mono">
                {{ application.reg_number }}
              </h1>
              <StatusBadge :status="application.status" />
              <span
                v-if="application.queue_number"
                class="px-2.5 py-1 rounded-xl bg-emerald-600 text-white font-mono font-black text-xs shadow-md shadow-emerald-600/20"
              >
                No. Antrian: {{ application.queue_number }}
              </span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">
              Didaftarkan pada: {{ formatDate(application.submitted_at || application.created_at) }} · Jenis Layanan: <strong class="text-slate-700 dark:text-slate-200">{{ application.service_type?.name }}</strong>
            </p>
          </div>
        </div>

        <!-- Quick Status Control -->
        <div class="flex items-center gap-2">
          <!-- QR Code Badge -->
          <div
            v-if="application.qr_code_svg"
            class="hidden sm:flex flex-col items-center bg-white p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm shrink-0"
            title="QR Code Registrasi & Pengambilan"
          >
            <div
              v-html="application.qr_code_svg"
              class="w-12 h-12 flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:block"
            ></div>
          </div>

          <!-- Check-In Button for physical arrival -->
          <button
            v-if="['APPROVED', 'SCHEDULED', 'QUEUE_BOOKED'].includes(application.status)"
            @click="openCheckInModal"
            class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-emerald-600/20 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Check-In Kedatangan Loket
          </button>

          <!-- Verifikasi Dokumen Fisik Asli button if arrived -->
          <button
            v-if="['ARRIVED', 'CHECKED_IN', 'PHYSICAL_VERIFICATION'].includes(application.status)"
            @click="openPhysicalVerifyModal"
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-blue-600/20 cursor-pointer"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Verifikasi Fisik Asli Lolos
          </button>

          <!-- Complete Service Button -->
          <button
            v-if="['CHECKED_IN', 'IN_SERVICE', 'APPROVED_FOR_SERVICE'].includes(application.status)"
            @click="openCompleteModal"
            class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-indigo-600/20 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Selesai Dilayani
          </button>
        </div>
      </div>

      <!-- Main Split Grid: Left Details & Right Verification Console -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT 1 COLUMN: Applicant Identity & Bio -->
        <div class="space-y-6">
          <!-- Applicant Bio Card -->
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-5">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">
              Profil Pemohon KTP-el
            </h3>

            <!-- Selfie Preview -->
            <div class="flex flex-col items-center p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
              <div
                v-if="selfieDocument"
                @click="previewSelfie"
                class="w-32 h-32 rounded-2xl overflow-hidden border-2 border-blue-500 shadow-md cursor-pointer group relative bg-slate-100 dark:bg-slate-900"
              >
                <img
                  :src="selfieDocument.admin_serve_url || route('admin.queue.documents.serve', { documentId: selfieDocument.id })"
                  alt="Foto Wajah Pemohon"
                  class="w-full h-full object-cover group-hover:scale-105 transition"
                />
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px] font-bold">
                  Klik Perbesar
                </div>
              </div>
              <div
                v-else
                class="w-32 h-32 rounded-2xl bg-slate-200 dark:bg-slate-700 flex flex-col items-center justify-center text-slate-400 text-xs"
              >
                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Tanpa Foto Wajah
              </div>
              <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300 mt-2">
                Foto Wajah Langsung (Selfie)
              </p>
            </div>

            <!-- Detail Fields -->
            <div class="space-y-3 text-xs">
              <div>
                <span class="text-slate-400 text-[11px] block">Nama Lengkap</span>
                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ application.applicant_name }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[11px] block">Nomor Induk Kependudukan (NIK)</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">{{ application.applicant_nik }}</span>
              </div>
              <div v-if="application.applicant_birth_place || application.applicant_birth_date">
                <span class="text-slate-400 text-[11px] block">Tempat, Tanggal Lahir</span>
                <span class="font-bold text-slate-900 dark:text-white">
                  {{ application.applicant_birth_place || '-' }}, {{ formatBirthDate(application.applicant_birth_date) }}
                </span>
              </div>
              <div>
                <span class="text-slate-400 text-[11px] block">No. WhatsApp / Handphone</span>
                <a
                  :href="`https://wa.me/${cleanPhone(application.applicant_phone)}`"
                  target="_blank"
                  class="font-mono font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1"
                >
                  {{ formatPhoneChunked(application.applicant_phone) }}
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
              </div>
              <div v-if="application.applicant_email">
                <span class="text-slate-400 text-[11px] block">Email</span>
                <span class="text-slate-700 dark:text-slate-200">{{ application.applicant_email }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[11px] block">Alamat Domisili</span>
                <span class="text-slate-700 dark:text-slate-200 leading-relaxed">{{ application.applicant_address }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[11px] block">Status Kehadiran Pelayanan</span>
                <span
                  :class="[
                    'inline-block mt-0.5 px-2.5 py-1 rounded-lg text-xs font-bold',
                    application.attendance_type === 'self'
                      ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300'
                      : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                  ]"
                >
                  {{ application.attendance_type === 'self' ? '👤 Datang Sendiri (Pemilik Dokumen)' : '👥 Diwakilkan / Surat Kuasa' }}
                </span>
              </div>

              <!-- Representative Box if not self -->
              <div
                v-if="application.attendance_type !== 'self'"
                class="p-3.5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 space-y-2 mt-2"
              >
                <div class="text-[10px] font-black uppercase text-amber-800 dark:text-amber-300 tracking-wider">
                  Identitas Penerima Kuasa / Perwakilan
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] block">Nama Perwakilan</span>
                  <span class="font-bold text-slate-900 dark:text-white">{{ application.representative_name }}</span>
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] block">NIK Perwakilan</span>
                  <span class="font-mono font-bold text-slate-900 dark:text-white">{{ application.representative_nik }}</span>
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] block">Hubungan & Status KK</span>
                  <span class="font-semibold text-slate-800 dark:text-slate-200">{{ application.representative_relationship }} ({{ application.representative_family_in_kk ? 'Satu KK' : 'Beda KK / Pihak Luar' }})</span>
                </div>
                <div v-if="application.representative_phone">
                  <span class="text-slate-400 text-[10px] block">No. HP Perwakilan</span>
                  <span class="font-mono text-slate-800 dark:text-slate-200">{{ formatPhoneChunked(application.representative_phone) }}</span>
                </div>
                <div v-if="application.representative_reason">
                  <span class="text-slate-400 text-[10px] block">Alasan Pemilik Dokumen Tidak Hadir</span>
                  <span class="text-slate-700 dark:text-slate-300 italic text-[11px]">"{{ application.representative_reason }}"</span>
                </div>
              </div>

              <div v-if="application.notes">
                <span class="text-slate-400 text-[11px] block">Catatan Pemohon</span>
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 italic text-[11px]">
                  "{{ application.notes }}"
                </div>
              </div>
            </div>
          </div>

          <!-- Schedule & Slot Info -->
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">
              Jadwal & Loket Pelayanan
            </h3>
            <div v-if="application.slot?.schedule" class="space-y-2 text-xs">
              <div class="p-3 rounded-2xl bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800">
                <p class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase">Tanggal Kedatangan</p>
                <p class="font-bold text-slate-900 dark:text-white text-sm mt-0.5">
                  {{ formatScheduleDate(application.slot.schedule.schedule_date) }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                  Jam Buka: {{ application.slot.schedule.open_time?.substring(0, 5) }} - {{ application.slot.schedule.close_time?.substring(0, 5) }} WITA
                </p>
              </div>
            </div>
            <div v-else class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs">
              Pemohon belum memiliki nomor antrean. Tetapkan jadwal pada saat menyetujui pengajuan berkas.
            </div>
          </div>
        </div>

        <!-- RIGHT 2 COLUMNS: Document Inspection & Verification Actions -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Documents Inspection Section -->
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-4">
              <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">
                  Pemeriksaan Berkas Persyaratan
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                  Periksa kejelasan foto, kelengkapan berkas fisik, dan kesesuaian data pemohon.
                </p>
              </div>
              <div class="flex items-center gap-2.5">
                <span class="text-xs font-mono font-bold text-slate-500">
                  {{ uploadedCount }} / {{ totalDocsRequired }} Berkas Diunggah
                </span>
                <button
                  v-if="hasPendingDocuments"
                  type="button"
                  @click="approveAllDocs"
                  :disabled="approvingAll"
                  class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                  title="Sahkan seluruh berkas persyaratan pemohon sekaligus"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  <span>{{ approvingAll ? 'Menyinkronkan...' : 'Sahkan Semua Berkas' }}</span>
                </button>
              </div>
            </div>

            <!-- Documents Grid -->
            <div class="space-y-4">
              <div
                v-for="docConfig in requirementsList"
                :key="docConfig.id"
                class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 transition"
              >
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <!-- Doc Info -->
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-slate-900 dark:text-white text-xs">
                        {{ docConfig.label }}
                      </span>
                      <span
                        v-if="docConfig.is_required"
                        class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400"
                      >
                        Wajib
                      </span>
                      <span
                        v-else
                        class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-400"
                      >
                        Opsional
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                      {{ docConfig.description || 'Format: ' + docConfig.accepted_formats }}
                    </p>
                  </div>

                  <!-- Uploaded File State & Actions -->
                  <div class="flex items-center gap-2">
                    <template v-if="getDocument(docConfig.id)">
                      <div class="text-right mr-2">
                        <span
                          :class="[
                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase',
                            ['APPROVED', 'VERIFIED', 'EXAMINED'].includes(getDocument(docConfig.id).status)
                              ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-700'
                              : ['REJECTED', 'REVISION_REQUIRED'].includes(getDocument(docConfig.id).status)
                              ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400 border border-rose-300 dark:border-rose-700'
                              : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 border border-amber-300 dark:border-amber-700'
                          ]"
                        >
                          {{ ['APPROVED', 'VERIFIED', 'EXAMINED'].includes(getDocument(docConfig.id).status) ? 'Sudah Diperiksa' : getDocument(docConfig.id).status === 'REVISION_REQUIRED' ? 'Perlu Perbaikan' : getDocument(docConfig.id).status === 'REJECTED' ? 'Ditolak' : 'Belum Diperiksa' }}
                        </span>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-mono">
                          {{ getDocument(docConfig.id).formatted_size || (Math.round((getDocument(docConfig.id).file_size_bytes || 0) / 1024) + ' KB') }}
                        </p>
                      </div>

                      <!-- Quick Approve button if pending -->
                      <button
                        v-if="getDocument(docConfig.id).status === 'PENDING'"
                        type="button"
                        @click="approveSingleDoc(getDocument(docConfig.id))"
                        class="px-3 py-2 rounded-xl bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-950 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 text-xs font-bold transition flex items-center gap-1 shadow-sm cursor-pointer"
                        title="Tandai berkas ini sah / sudah diperiksa"
                      >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Sahkan</span>
                      </button>

                      <!-- Preview / Inspect Button -->
                      <button
                        type="button"
                        @click="openViewer(getDocument(docConfig.id), docConfig.label)"
                        class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                      >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Lihat & Periksa</span>
                      </button>
                    </template>

                    <div v-else class="text-xs text-slate-400 italic">
                      Berkas belum diunggah
                    </div>
                  </div>
                </div>

                <!-- Reviewer Notes on specific document if any -->
                <div
                  v-if="getDocument(docConfig.id)?.reviewer_notes"
                  class="mt-2.5 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-[11px]"
                >
                  <strong>Catatan Petugas:</strong> {{ getDocument(docConfig.id).reviewer_notes }}
                </div>
              </div>
            </div>
          </div>

          <!-- Pelayanan Loket Fisik & Status Ketersediaan Cetak KTP-el (Serah Terima) -->
          <div
            class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4"
          >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-700/60 pb-3">
              <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  Pelayanan Loket & Status Cetak KTP-el (Serah Terima)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                  Informasikan status pemeriksaan fisik dan kesiapan cetak kartu kepada Bagian Penerimaan (Petugas Penyerahan KTP-el).
                </p>
              </div>

              <Link
                v-if="application.handover"
                :href="route('admin.handover.show', application.handover.id)"
                class="px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white text-xs font-bold transition flex items-center gap-1.5 self-start sm:self-auto shrink-0"
              >
                <span>Buka Modul Serah Terima</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
              </Link>
            </div>

            <!-- Current Physical Status Indicators -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
              <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                <span class="text-slate-400 text-[10px] uppercase font-bold block">Status Fisik di Penerimaan</span>
                <div class="mt-1">
                  <span
                    v-if="application.status === 'READY_FOR_PICKUP' || application.handover?.status === 'SIAP_DIAMBIL'"
                    class="px-2.5 py-1 rounded-xl text-xs font-black bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 inline-flex items-center gap-1"
                  >
                    <span>✓</span> Sudah Dicetak (Siap Diambil)
                  </span>
                  <span
                    v-else-if="application.status === 'COMPLETED' || application.handover?.status === 'SUDAH_DISERAHKAN'"
                    class="px-2.5 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 inline-flex items-center gap-1"
                  >
                    <span>✓</span> Selesai Diserahkan
                  </span>
                  <span
                    v-else-if="application.handover?.status === 'KENDALA_PENGAMBILAN'"
                    class="px-2.5 py-1 rounded-xl text-xs font-black bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 inline-flex items-center gap-1"
                  >
                    <span>⚠️</span> Ada Kendala Pengambilan
                  </span>
                  <span
                    v-else
                    class="px-2.5 py-1 rounded-xl text-xs font-black bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 inline-flex items-center gap-1"
                  >
                    <span>⏳</span> Belum Dicetak / Menunggu Cetak
                  </span>
                </div>
              </div>

              <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                <span class="text-slate-400 text-[10px] uppercase font-bold block">Pemeriksaan Ketersediaan</span>
                <p class="font-bold text-slate-900 dark:text-white mt-1">
                  {{ application.handover?.ready_marked_at ? application.handover.ready_marked_at_formatted : 'Belum Ditandai Siap' }}
                </p>
                <p v-if="application.handover?.ready_marked_user" class="text-[10px] text-slate-400 mt-0.5">
                  Oleh: {{ application.handover.ready_marked_user.name }}
                </p>
              </div>

              <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                <span class="text-slate-400 text-[10px] uppercase font-bold block">Penyerahan ke Pemohon</span>
                <p class="font-bold text-slate-900 dark:text-white mt-1">
                  {{ application.handover?.handover_at ? application.handover.handover_at_formatted : 'Belum Diserahkan' }}
                </p>
                <p v-if="application.handover?.handover_user" class="text-[10px] text-slate-400 mt-0.5">
                  Oleh: {{ application.handover.handover_user.name }}
                </p>
              </div>
            </div>

            <!-- ACTION BUTTONS FOR OPERATOR VERIFIKASI / CETAK -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">
                  Tindakan Operator Loket & Petugas Cetak:
                </span>
                <span class="text-[11px] text-slate-400">
                  Sinkron langsung dengan Modul Bagian Penerimaan
                </span>
              </div>

              <!-- Banner Status for Loket & Cetak Actions -->
              <div
                v-if="application.status === 'REJECTED'"
                class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200 flex items-center gap-2.5"
              >
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                  <span class="font-bold">Permohonan Telah Ditolak:</span> Seluruh tindakan pelayanan loket dan status cetak KTP-el dinonaktifkan.
                </div>
              </div>

              <div
                v-else-if="!isOnlineApproved"
                class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-200 flex items-center gap-2.5"
              >
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                  <span class="font-bold">Menunggu Persetujuan Verifikasi Berkas Online:</span> Tombol pelayanan loket dan pembaruan status cetak kartu akan aktif setelah berkas online disetujui (Lolos Pra-Verifikasi / Ditetapkan Jadwal).
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Action Button 1: Informasikan Belum Dicetak / Sedang Diproses -->
                <button
                  type="button"
                  @click="openProcessingModal"
                  :disabled="!isOnlineApproved || isKtpReadyOrCompleted || application.status === 'PROCESSING'"
                  :class="[
                    'p-3.5 rounded-2xl font-bold text-xs shadow-sm transition flex items-center justify-center gap-2 text-center border',
                    application.status === 'PROCESSING'
                      ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300 dark:border-amber-700 cursor-default'
                      : isKtpReadyOrCompleted
                        ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-700 cursor-not-allowed opacity-50'
                        : 'bg-white dark:bg-slate-900 hover:bg-amber-50 dark:hover:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer'
                  ]"
                  title="Informasikan ke Bagian Penerimaan bahwa KTP-el sedang dalam proses cetak/rekam"
                >
                  <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span>{{ application.status === 'PROCESSING' ? 'Sedang Diproses Cetak' : '⏳ KTP Belum Dicetak (Sedang Proses)' }}</span>
                </button>

                <!-- Action Button 2: Informasikan KTP Sudah Selesai Dicetak (Siap Diambil) -->
                <button
                  type="button"
                  @click="openReadyPickupModal"
                  :disabled="!isOnlineApproved || ['COMPLETED', 'REJECTED', 'CANCELLED', 'EXPIRED'].includes(application.status) || application.status === 'READY_FOR_PICKUP' || application.handover?.status === 'SIAP_DIAMBIL' || application.handover?.status === 'SUDAH_DISERAHKAN'"
                  :class="[
                    'p-3.5 rounded-2xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2 text-center border',
                    application.status === 'READY_FOR_PICKUP' || application.handover?.status === 'SIAP_DIAMBIL'
                      ? 'bg-purple-600 text-white border-purple-600 cursor-default shadow-purple-600/30'
                      : ['COMPLETED', 'REJECTED', 'CANCELLED', 'EXPIRED'].includes(application.status) || application.handover?.status === 'SUDAH_DISERAHKAN'
                        ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-700 cursor-not-allowed opacity-50'
                        : 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-600 shadow-emerald-600/30 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer'
                  ]"
                  title="Informasikan ke Bagian Penerimaan bahwa KTP-el sudah selesai dicetak dan siap diambil pemohon"
                >
                  <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  <span>{{ (application.status === 'READY_FOR_PICKUP' || application.handover?.status === 'SIAP_DIAMBIL') ? '✓ Sudah Siap di Loket Penerimaan' : '✓ KTP Sudah Dicetak (Siap Diambil)' }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Main Verification Action Console -->
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">
                  Keputusan Verifikasi Berkas Online
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                  Tindakan verifikasi awal berkas digital yang diunggah pemohon dari HP/portal.
                </p>
              </div>
              <span
                v-if="isPastOnlineVerification"
                class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-[10px] uppercase tracking-wider"
              >
                Tahap Selesai
              </span>
            </div>

            <!-- Banner info if online verification has already finished -->
            <div
              v-if="isPastOnlineVerification"
              class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/80 flex items-center justify-between text-xs"
            >
              <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0">✓</span>
                <span class="text-slate-700 dark:text-slate-200 font-medium">
                  Tahap Verifikasi Berkas Online telah selesai. Status permohonan saat ini: <strong class="text-blue-600 dark:text-blue-400">{{ formatStatusName(application.status) }}</strong>
                  <span v-if="canReject" class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    * Tombol <strong>Tolak Pengajuan</strong> tetap aktif untuk keperluan pemeriksaan fisik dokumen asli di loket offline.
                  </span>
                  <span v-else-if="application.status === 'READY_FOR_PICKUP' || application.handover?.status === 'SIAP_DIAMBIL'" class="block text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">
                    * Fisik KTP-el telah selesai dicetak & siap di loket penerimaan. Tahap verifikasi dan penolakan telah ditutup.
                  </span>
                </span>
              </div>
              <span v-if="canReject" class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider shrink-0">
                Tolak Aktif (Fisik Loket)
              </span>
              <span v-else class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">
                Tahap Ditutup
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
              <!-- PRE-VERIFY BUTTON (Allows applicant to book slot) -->
              <button
                @click="openPreVerifyModal"
                :disabled="!isOnlineReviewPhase"
                class="px-3.5 py-3.5 bg-cyan-600 hover:bg-cyan-500 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-2xl font-bold text-xs shadow-md shadow-cyan-600/20 transition flex items-center justify-center gap-1.5 cursor-pointer text-center"
                title="Loloskan pra-verifikasi dan izinkan pemohon memilih jadwal antrean sendiri"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Setujui Pra-Verifikasi</span>
              </button>

              <!-- DIRECT APPROVE & SCHEDULE BUTTON (Admin quick assign) -->
              <button
                @click="openApproveModal"
                :disabled="!['SUBMITTED', 'RESUBMITTED', 'UNDER_REVIEW', 'PRE_VERIFIED'].includes(application.status) || !!application.queue_number"
                class="px-3.5 py-3.5 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-2xl font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-1.5 cursor-pointer text-center"
                title="Tetapkan tanggal & nomor antrean langsung oleh admin"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Tetapkan Jadwal</span>
              </button>

              <!-- REVISION BUTTON -->
              <button
                @click="openRevisionModal"
                :disabled="!isOnlineReviewPhase"
                class="px-3.5 py-3.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-2xl font-bold text-xs shadow-md shadow-amber-500/20 transition flex items-center justify-center gap-1.5 cursor-pointer text-center"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Minta Perbaikan</span>
              </button>

              <!-- REJECT BUTTON -->
              <button
                @click="openRejectModal"
                :disabled="!canReject"
                :class="[
                  'px-3.5 py-3.5 rounded-2xl font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5 text-center',
                  canReject
                    ? 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/20 text-white cursor-pointer'
                    : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700 cursor-not-allowed opacity-50 shadow-none'
                ]"
                title="Tolak pengajuan jika berkas online tidak valid atau dokumen fisik di loket tidak sesuai"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Tolak Pengajuan</span>
              </button>
            </div>
          </div>

          <!-- Audit Trail & Verification History -->
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">
              Riwayat Aktivitas & Audit Trail Verifikasi
            </h3>

            <div class="space-y-3">
              <div
                v-for="log in application.verification_history"
                :key="log.id"
                class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 flex items-start gap-3 text-xs"
              >
                <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 dark:text-white">
                      {{ log.action_title || formatStatusName(log.to_status) }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-mono">
                      {{ log.created_at_formatted || formatDate(log.created_at) }}
                    </span>
                  </div>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    Oleh: <strong class="text-slate-700 dark:text-slate-300">{{ log.user?.name || 'Sistem' }}</strong> · Status: 
                    <span v-if="log.from_status && log.from_status !== log.to_status" class="font-medium text-slate-700 dark:text-slate-300">
                      {{ log.from_status_label || formatStatusName(log.from_status) }} &rarr; <span class="font-bold text-blue-600 dark:text-blue-400">{{ log.to_status_label || formatStatusName(log.to_status) }}</span>
                    </span>
                    <span v-else class="font-bold text-blue-600 dark:text-blue-400">
                      {{ log.to_status_label || formatStatusName(log.to_status) }}
                    </span>
                  </p>
                  <p v-if="log.notes" class="mt-1 text-slate-600 dark:text-slate-300 italic text-[11px]">
                    "{{ log.notes }}"
                  </p>
                </div>
              </div>

              <div v-if="!application.verification_history || application.verification_history.length === 0" class="text-xs text-slate-400 italic">
                Belum ada catatan verifikasi.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DOCUMENT VIEWER MODAL (Zoom + 90 deg rotation) -->
    <DocumentViewerModal
      v-if="viewerState.open"
      :src="viewerState.url"
      :url="viewerState.url"
      :title="viewerState.title"
      :mime-type="viewerState.mimeType"
      @close="viewerState.open = false"
    />

    <!-- APPROVE MODAL -->
    <div v-if="approveModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white">
              Tetapkan Jadwal & Terbitkan Nomor Antrean
            </h3>
            <p class="text-xs text-slate-500 mt-1">
              Pilih tanggal kedatangan pelayanan yang masih memiliki sisa kuota. Sistem akan menerbitkan nomor antrean (misal: <strong>A-001</strong>) secara otomatis.
            </p>
          </div>
          <button
            @click="approveModalOpen = false"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1"
          >
            ✕
          </button>
        </div>

        <div v-if="availableSlots.length === 0" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300">
          <strong>Belum ada jadwal buka:</strong> Tidak ditemukan jadwal pelayanan yang aktif dengan kuota tersedia untuk jenis layanan ini dalam 14 hari ke depan. Harap buka kuota pelayanan di menu <em>Jadwal & Kuota</em> terlebih dahulu.
        </div>

        <div v-else class="space-y-3">
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            Pilih Hari & Sesi Jadwal Pelayanan *
          </label>
          <select
            v-model="approveForm.queue_slot_id"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium"
          >
            <option value="" disabled>-- Pilih Hari, Jam & Kuota Pelayanan --</option>
            <option
              v-for="slot in availableSlots"
              :key="slot.id"
              :value="slot.id"
              :disabled="slot.remaining_quota <= 0"
            >
              {{ formatSlotOptionLabel(slot) }}
            </option>
          </select>

          <!-- Selected Slot Info Card -->
          <div
            v-if="selectedSlotDetails"
            class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-950 dark:text-blue-200 space-y-1"
          >
            <span class="text-[10px] uppercase font-bold text-blue-600 dark:text-blue-400 block tracking-wider">Ringkasan Jadwal Terpilih</span>
            <p class="font-bold text-sm text-slate-900 dark:text-white">
              {{ formatSlotDateLabel(selectedSlotDetails) }}
            </p>
            <p class="text-xs text-slate-600 dark:text-slate-300">
              Jam Operasional: <strong>{{ selectedSlotDetails.open_time || '08:00' }} - {{ selectedSlotDetails.close_time || '15:30' }} WITA</strong>
            </p>
            <p class="text-[11px] text-slate-500">
              Sisa Kuota: <strong>{{ selectedSlotDetails.remaining_quota }} pemohon</strong> (Terisi {{ selectedSlotDetails.booked_count }}/{{ selectedSlotDetails.quota }})
            </p>
          </div>

          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 pt-1">
            Catatan Tambahan untuk Pemohon (Opsional)
          </label>
          <textarea
            v-model="approveForm.notes"
            rows="2"
            placeholder="Contoh: Harap membawa berkas fisik asli pada saat pengambilan KTP-el di loket."
            class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
          <button
            @click="approveModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitApprove"
            :disabled="!approveForm.queue_slot_id || approveSubmitting"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span v-if="approveSubmitting">Menerbitkan Antrean...</span>
            <span v-else>Konfirmasi & Terbitkan Antrean</span>
          </button>
        </div>
      </div>
    </div>

    <!-- REVISION MODAL -->
    <div v-if="revisionModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <h3 class="text-lg font-black text-slate-900 dark:text-white">
          Minta Perbaikan Dokumen
        </h3>
        <p class="text-xs text-slate-500">
          Pemohon akan menerima notifikasi dan instruksi untuk memperbaiki dokumen yang belum sesuai tanpa perlu membuat pengajuan baru.
        </p>

        <div class="space-y-3">
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            Instruksi / Catatan Perbaikan *
          </label>
          <textarea
            v-model="revisionForm.notes"
            rows="3"
            placeholder="Tuliskan secara jelas dokumen mana yang buram, tidak lengkap, atau perlu difoto ulang..."
            class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500"
          ></textarea>

          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 pt-2">
            Tandai Dokumen yang Perlu Diperbaiki
          </label>
          <div class="space-y-2">
            <label
              v-for="doc in application.documents"
              :key="doc.id"
              class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs cursor-pointer"
            >
              <input
                type="checkbox"
                v-model="revisionForm.rejectedDocIds"
                :value="doc.id"
                class="rounded text-amber-600 focus:ring-amber-500"
              />
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ doc.service_document?.label }}</span>
            </label>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            @click="revisionModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 transition"
          >
            Batal
          </button>
          <button
            @click="submitRevision"
            :disabled="!revisionForm.notes || revisionSubmitting"
            class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition flex items-center gap-1.5"
          >
            <span v-if="revisionSubmitting">Mengirim...</span>
            <span v-else>Kirim Permintaan Perbaikan</span>
          </button>
        </div>
      </div>
    </div>

    <!-- REJECT MODAL -->
    <div v-if="rejectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <h3 class="text-lg font-black text-rose-600 dark:text-rose-400">
          Tolak Pengajuan Antrean
        </h3>
        <p class="text-xs text-slate-500">
          Penolakan bersifat permanen. Tuliskan alasan penolakan (misal: berkas online tidak valid atau dokumen fisik asli di loket tidak sesuai/palsu) untuk catatan audit.
        </p>

        <div class="space-y-3">
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            Alasan Penolakan Pengajuan *
          </label>
          <textarea
            v-model="rejectForm.reason"
            rows="3"
            placeholder="Contoh: Dokumen fisik asli yang diserahkan di loket tidak cocok dengan data pendaftaran..."
            class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            @click="rejectModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 transition"
          >
            Batal
          </button>
          <button
            @click="submitReject"
            :disabled="!rejectForm.reason || rejectSubmitting"
            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition flex items-center gap-1.5"
          >
            <span v-if="rejectSubmitting">Memproses...</span>
            <span v-else>Konfirmasi Penolakan</span>
          </button>
        </div>
      </div>
    </div>

    <!-- START PROCESSING MODAL (Informasikan Belum Dicetak / Sedang Diproses) -->
    <div v-if="processingModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
              <span class="text-amber-500">⏳</span> Informasikan KTP Belum Dicetak (Sedang Proses)
            </h3>
            <p class="text-xs text-slate-500 mt-1">
              Data akan diinformasikan ke Bagian Penerimaan (Modul Serah Terima) dengan status <strong>Menunggu Ketersediaan KTP</strong>.
            </p>
          </div>
          <button @click="processingModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">✕</button>
        </div>

        <div class="space-y-3">
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            Catatan Proses Pencetakan (Opsional)
          </label>
          <textarea
            v-model="processingForm.notes"
            rows="3"
            placeholder="Contoh: Fisik KTP-el dalam antrean cetak mesin / perekaman biometrik..."
            class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
          <button
            @click="processingModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitProcessing"
            :disabled="processingSubmitting"
            class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span v-if="processingSubmitting">Menyimpan...</span>
            <span v-else>Informasikan ke Penerimaan</span>
          </button>
        </div>
      </div>
    </div>

    <!-- READY FOR PICKUP MODAL (Informasikan KTP Sudah Selesai Dicetak) -->
    <div v-if="readyPickupModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
              <span class="text-emerald-500">✓</span> Informasikan KTP Sudah Dicetak (Siap Diambil)
            </h3>
            <p class="text-xs text-slate-500 mt-1">
              Kartu fisik KTP-el dinyatakan telah selesai dicetak. Bagian Penerimaan (Serah Terima) akan menerima notifikasi bahwa kartu <strong>Siap Diambil</strong> dan pemohon otomatis diberitahukan.
            </p>
          </div>
          <button @click="readyPickupModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">✕</button>
        </div>

        <div class="space-y-3">
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            Catatan Lokasi / Kesiapan Fisik (Opsional)
          </label>
          <textarea
            v-model="readyPickupForm.notes"
            rows="3"
            placeholder="Contoh: Fisik KTP-el telah selesai dicetak dengan kualitas baik dan diletakkan di loket penyerahan..."
            class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
          <button
            @click="readyPickupModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitReadyPickup"
            :disabled="readyPickupSubmitting"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span v-if="readyPickupSubmitting">Menyimpan...</span>
            <span v-else>Konfirmasi KTP Siap Diambil</span>
          </button>
        </div>
      </div>
    </div>

    <!-- PRE-VERIFY MODAL (Setujui Pra-Verifikasi Dokumen Digital) -->
    <div v-if="preVerifyModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-cyan-100 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-black text-lg shrink-0">
              ✓
            </div>
            <div>
              <h3 class="text-base font-black text-slate-900 dark:text-white">
                Setujui Pra-Verifikasi Dokumen Digital
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Validasi kelengkapan berkas awal secara digital
              </p>
            </div>
          </div>
          <button @click="preVerifyModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer">✕</button>
        </div>

        <!-- Info Card -->
        <div class="p-4 rounded-2xl bg-cyan-50/70 dark:bg-cyan-950/30 border border-cyan-200/80 dark:border-cyan-800/60 text-xs text-slate-700 dark:text-slate-200 space-y-2">
          <div class="flex justify-between items-center text-[11px]">
            <span class="text-cyan-700 dark:text-cyan-300 font-bold uppercase tracking-wider">Pemohon Pelayanan</span>
            <span class="font-mono bg-white dark:bg-slate-900 px-2 py-0.5 rounded-md font-bold text-slate-800 dark:text-slate-100">NIK: {{ application.nik }}</span>
          </div>
          <p class="font-extrabold text-sm text-slate-900 dark:text-white">
            {{ application.applicant_name }}
          </p>
          <div class="text-[11px] text-slate-600 dark:text-slate-400 pt-2 border-t border-cyan-200/60 dark:border-cyan-900/50 flex items-start gap-1.5 leading-relaxed">
            <span class="text-cyan-600 dark:text-cyan-400 font-bold">ℹ</span>
            <span>Dengan menyetujui, pemohon akan menerima email notifikasi bahwa berkas telah lengkap & sah, serta diberikan akses memilih jadwal antrean sendiri di portal.</span>
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            Catatan Persetujuan untuk Pemohon (Opsional)
          </label>
          <textarea
            v-model="preVerifyForm.notes"
            rows="3"
            placeholder="Dokumen administrasi awal dinyatakan lengkap dan sah..."
            class="w-full p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 leading-relaxed font-medium"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
          <button
            @click="preVerifyModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitPreVerify"
            :disabled="preVerifySubmitting"
            class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-cyan-600/20 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span v-if="preVerifySubmitting">Menyetujui...</span>
            <span v-else>Konfirmasi & Buka Akses Jadwal</span>
          </button>
        </div>
      </div>
    </div>

    <!-- CHECK-IN MODAL (Konfirmasi Check-In Loket Pelayanan) -->
    <div v-if="checkInModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-lg shrink-0">
              🎫
            </div>
            <div>
              <h3 class="text-base font-black text-slate-900 dark:text-white">
                Konfirmasi Check-In Kedatangan
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Tandai kehadiran fisik pemohon di loket pelayanan Disdukcapil
              </p>
            </div>
          </div>
          <button @click="checkInModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer">✕</button>
        </div>

        <!-- Details Card -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/80 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs text-slate-500">Nomor Antrean Pelayanan:</span>
            <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono font-black text-sm rounded-xl">
              {{ application.queue_number || 'Belum Terbit' }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200 dark:border-slate-800">
            <div>
              <span class="text-[10px] text-slate-400 uppercase font-bold block">Nama Pemohon</span>
              <span class="font-bold text-slate-900 dark:text-white">{{ application.applicant_name }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 uppercase font-bold block">Nomor Induk Kependudukan</span>
              <span class="font-mono text-slate-800 dark:text-slate-200">{{ application.nik }}</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200 dark:border-slate-800">
            <div>
              <span class="text-[10px] text-slate-400 uppercase font-bold block">Jadwal Pelayanan</span>
              <span class="font-medium text-slate-700 dark:text-slate-300">
                {{ formatScheduleDate(application.slot?.schedule?.schedule_date) }}
              </span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 uppercase font-bold block">Status Kehadiran</span>
              <span class="font-medium text-slate-700 dark:text-slate-300">
                {{ application.attendance_type === 'PROXY' ? 'Dikuasakan (Surat Kuasa)' : 'Datang Sendiri' }}
              </span>
            </div>
          </div>
        </div>

        <p class="text-xs text-slate-500 leading-relaxed">
          Status permohonan akan diperbarui menjadi <strong>CHECKED_IN / ARRIVED</strong> dan berkas siap diverifikasi secara fisik di loket.
        </p>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
          <button
            @click="checkInModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitCheckIn"
            :disabled="checkInSubmitting"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span v-if="checkInSubmitting">Memproses...</span>
            <span v-else>Konfirmasi Pemohon Hadir</span>
          </button>
        </div>
      </div>
    </div>

    <!-- PHYSICAL VERIFICATION MODAL (Pemeriksaan Dokumen Fisik Asli di Loket) -->
    <div v-if="physicalVerifyModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-lg shrink-0">
              🔍
            </div>
            <div>
              <h3 class="text-base font-black text-slate-900 dark:text-white">
                Pemeriksaan Fisik Dokumen Asli
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Verifikasi keaslian dan kecocokan fisik berkas asli di loket
              </p>
            </div>
          </div>
          <button @click="physicalVerifyModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer">✕</button>
        </div>

        <!-- Checklist -->
        <div class="space-y-2 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/80">
          <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1">Checklist Pemeriksaan Loket</span>

          <label class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer transition">
            <input
              type="checkbox"
              v-model="physicalVerifyForm.match_identity"
              class="rounded text-blue-600 focus:ring-blue-500 mt-0.5"
            />
            <div class="text-xs">
              <span class="font-bold text-slate-800 dark:text-slate-200">Kecocokan Identitas & Wajah Pemohon</span>
              <p class="text-[11px] text-slate-500">Wajah pemohon / penerima kuasa sesuai dengan foto KTP dan sistem biometrik.</p>
            </div>
          </label>

          <label class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer transition">
            <input
              type="checkbox"
              v-model="physicalVerifyForm.match_documents"
              class="rounded text-blue-600 focus:ring-blue-500 mt-0.5"
            />
            <div class="text-xs">
              <span class="font-bold text-slate-800 dark:text-slate-200">Keaslian Dokumen Fisik Asli</span>
              <p class="text-[11px] text-slate-500">Berkas fisik asli (KK, KTP lama / Surat Kehilangan, Formulir) lengkap & valid.</p>
            </div>
          </label>

          <label v-if="application.attendance_type === 'PROXY'" class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer transition bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/60">
            <input
              type="checkbox"
              v-model="physicalVerifyForm.match_authorization"
              class="rounded text-blue-600 focus:ring-blue-500 mt-0.5"
            />
            <div class="text-xs">
              <span class="font-bold text-slate-800 dark:text-slate-200">Surat Kuasa Sah & Bermeterai</span>
              <p class="text-[11px] text-slate-500">Surat Kuasa F-1.07 asli bermeterai Rp 10.000 dan KTP asli penerima kuasa telah diverifikasi.</p>
            </div>
          </label>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            Catatan Pemeriksaan Fisik (Opsional)
          </label>
          <textarea
            v-model="physicalVerifyForm.notes"
            rows="2"
            placeholder="Pemeriksaan dokumen fisik asli telah sesuai..."
            class="w-full p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium leading-relaxed"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
          <button
            @click="physicalVerifyModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitPhysicalVerify"
            :disabled="physicalVerifySubmitting || !physicalVerifyForm.match_identity || !physicalVerifyForm.match_documents || (application.attendance_type === 'PROXY' && !physicalVerifyForm.match_authorization)"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span v-if="physicalVerifySubmitting">Menyimpan...</span>
            <span v-else>Konfirmasi Lolos & Siap Dilayani</span>
          </button>
        </div>
      </div>
    </div>

    <!-- COMPLETE MODAL (Konfirmasi Pelayanan Selesai & Penyerahan KTP-el) -->
    <div v-if="completeModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-lg shrink-0">
              🎉
            </div>
            <div>
              <h3 class="text-base font-black text-slate-900 dark:text-white">
                Selesaikan Pelayanan & Penyerahan
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Konfirmasi penuntasan proses antrean dan penyerahan dokumen
              </p>
            </div>
          </div>
          <button @click="completeModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer">✕</button>
        </div>

        <!-- Info Card -->
        <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/60 space-y-2 text-xs">
          <div class="flex justify-between items-center">
            <span class="text-indigo-700 dark:text-indigo-300 font-bold uppercase tracking-wider text-[11px]">Rincian Penerima</span>
            <span class="font-mono bg-white dark:bg-slate-900 px-2 py-0.5 rounded-md font-bold text-slate-800 dark:text-slate-100">Antrean: {{ application.queue_number || '-' }}</span>
          </div>
          <p class="font-extrabold text-sm text-slate-900 dark:text-white">
            {{ application.applicant_name }} (NIK: {{ application.nik }})
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Penyerahan kepada: <strong>{{ application.handover?.received_by || application.applicant_name }}</strong> {{ application.attendance_type === 'PROXY' ? '(Penerima Kuasa)' : '(Pemilik Dokumen)' }}
          </p>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
            Catatan Penyelesaian Pelayanan (Opsional)
          </label>
          <textarea
            v-model="completeForm.notes"
            rows="3"
            placeholder="KTP-el fisik telah diserahkan kepada pemohon. Pelayanan selesai."
            class="w-full p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium leading-relaxed"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
          <button
            @click="completeModalOpen = false"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitComplete"
            :disabled="completeSubmitting"
            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span v-if="completeSubmitting">Menyimpan...</span>
            <span v-else>Selesaikan Pelayanan</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/Queue/StatusBadge.vue'
import DocumentViewerModal from '@/Components/Queue/DocumentViewerModal.vue'

const props = defineProps({
  application: Object,
  availableSlots: Array,
})

const isOnlineReviewPhase = computed(() => ['SUBMITTED', 'RESUBMITTED', 'UNDER_REVIEW'].includes(props.application.status))
const isPastOnlineVerification = computed(() => !['SUBMITTED', 'RESUBMITTED', 'UNDER_REVIEW', 'DRAFT'].includes(props.application.status))
const isOnlineApproved = computed(() => [
  'PRE_VERIFIED',
  'APPROVED',
  'QUEUE_BOOKED',
  'SCHEDULED',
  'ARRIVED',
  'CHECKED_IN',
  'PHYSICAL_VERIFICATION',
  'APPROVED_FOR_SERVICE',
  'PROCESSING',
  'IN_SERVICE',
  'READY_FOR_PICKUP',
].includes(props.application.status))

const isKtpReadyOrCompleted = computed(() => {
  return [
    'READY_FOR_PICKUP',
    'COMPLETED',
    'REJECTED',
    'CANCELLED',
    'EXPIRED',
  ].includes(props.application.status) || [
    'SIAP_DIAMBIL',
    'SUDAH_DISERAHKAN',
  ].includes(props.application.handover?.status)
})

const canReject = computed(() => {
  if (['COMPLETED', 'REJECTED', 'CANCELLED', 'EXPIRED', 'READY_FOR_PICKUP'].includes(props.application.status)) {
    return false
  }
  if (['SIAP_DIAMBIL', 'SUDAH_DISERAHKAN'].includes(props.application.handover?.status)) {
    return false
  }
  return true
})

// Viewer modal state
const viewerState = ref({
  open: false,
  title: '',
  url: '',
  mimeType: 'image/jpeg',
})

const selfieDocument = computed(() => {
  if (props.application.selfie_document) {
    return props.application.selfie_document
  }
  return props.application.documents?.find(d => {
    const sDoc = d.service_document
    if (!sDoc) return false
    return sDoc.capture_mode === 'user' ||
      sDoc.label?.toLowerCase().includes('selfie') ||
      sDoc.label?.toLowerCase().includes('foto wajah')
  }) || null
})

const formatBirthDate = (val) => {
  if (props.application.applicant_birth_date_formatted) {
    return props.application.applicant_birth_date_formatted
  }
  if (!val) return '-'
  const raw = String(val).split('T')[0]
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
    const [year, month, day] = raw.split('-')
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
    const mIdx = parseInt(month, 10) - 1
    if (mIdx >= 0 && mIdx < 12) {
      return `${parseInt(day, 10)} ${months[mIdx]} ${year}`
    }
  }
  return val
}

const statusNames = {
  DRAFT: 'Draf',
  SUBMITTED: 'Menunggu Verifikasi',
  UNDER_REVIEW: 'Sedang Diverifikasi',
  REVISION_REQUIRED: 'Perlu Perbaikan Dokumen',
  RESUBMITTED: 'Perbaikan Dikirim Ulang',
  PRE_VERIFIED: 'Lolos Pra-Verifikasi',
  APPROVED: 'Lolos Pra-Verifikasi',
  QUEUE_BOOKED: 'Nomor Antrean Terbit',
  SCHEDULED: 'Jadwal Ditetapkan',
  ARRIVED: 'Hadir di Loket',
  CHECKED_IN: 'Hadir di Loket',
  PHYSICAL_VERIFICATION: 'Pemeriksaan Fisik Dokumen',
  APPROVED_FOR_SERVICE: 'Disetujui untuk Dilayani',
  PROCESSING: 'Sedang Diproses (Rekam/Cetak)',
  IN_SERVICE: 'Sedang Dilayani',
  READY_FOR_PICKUP: 'KTP Siap Diambil',
  COMPLETED: 'Pelayanan Selesai',
  CANCELLED: 'Dibatalkan',
  REJECTED: 'Pengajuan Ditolak',
  EXPIRED: 'Kedaluwarsa',
}

const formatStatusName = (code) => {
  if (!code) return '-'
  return statusNames[code] || code
}

const previewSelfie = () => {
  if (!selfieDocument.value) return
  if (selfieDocument.value.status === 'PENDING') {
    selfieDocument.value.status = 'APPROVED'
    router.post(
      route('admin.queue.documents.approve', { applicationId: props.application.id, docId: selfieDocument.value.id }),
      {},
      { preserveScroll: true, preserveState: true }
    )
  }
  const url = selfieDocument.value.admin_serve_url || (selfieDocument.value.id ? `/admin/queue/documents/${selfieDocument.value.id}/serve` : '')
  viewerState.value = {
    open: true,
    title: 'Foto Wajah Pemohon (Selfie) - ' + props.application.applicant_name,
    url: url,
    mimeType: selfieDocument.value.mime_type || 'image/jpeg',
  }
}

const openViewer = (doc, label) => {
  if (!doc) return
  if (doc.status === 'PENDING') {
    doc.status = 'APPROVED'
    router.post(
      route('admin.queue.documents.approve', { applicationId: props.application.id, docId: doc.id }),
      {},
      { preserveScroll: true, preserveState: true }
    )
  }
  const url = doc.admin_serve_url || (doc.id ? `/admin/queue/documents/${doc.id}/serve` : '')
  viewerState.value = {
    open: true,
    title: label + ' - ' + props.application.applicant_name,
    url: url,
    mimeType: doc.mime_type || 'image/jpeg',
  }
}

const approveSingleDoc = (doc) => {
  if (!doc) return
  doc.status = 'APPROVED'
  router.post(
    route('admin.queue.documents.approve', { applicationId: props.application.id, docId: doc.id }),
    {},
    { preserveScroll: true }
  )
}

const approvingAll = ref(false)
const hasPendingDocuments = computed(() => {
  return props.application.documents?.some(d => d.status === 'PENDING')
})

const approveAllDocs = () => {
  approvingAll.value = true
  router.post(
    route('admin.queue.documents.approve-all', props.application.id),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        approvingAll.value = false
      },
    }
  )
}

// Helpers
const getDocument = (serviceDocId) => {
  if (!serviceDocId) return null
  return props.application.documents?.find(d => d.service_document_id === serviceDocId)
    || props.application.documents?.find(d => !d.service_document_id && d.id === serviceDocId)
    || null
}

const requirementsList = computed(() => {
  if (props.application.service_type?.documents && props.application.service_type.documents.length > 0) {
    return props.application.service_type.documents
  }
  if (props.application.documents && props.application.documents.length > 0) {
    return props.application.documents.map(d => ({
      id: d.service_document_id || d.id,
      label: d.service_document?.label || d.original_filename || 'Dokumen Persyaratan',
      is_required: d.service_document?.is_required ?? true,
      description: d.service_document?.description || '',
      accepted_formats: d.service_document?.accepted_formats || 'jpg,jpeg,png,pdf',
    }))
  }
  return []
})

const uploadedCount = computed(() => {
  return props.application.documents?.length || 0
})

const totalDocsRequired = computed(() => {
  if (props.application.service_type?.documents && props.application.service_type.documents.length > 0) {
    return props.application.service_type.documents.filter(d => d.is_required)?.length || 0
  }
  return props.application.documents?.length || 0
})

// Approve Modal State
const approveModalOpen = ref(false)
const approveSubmitting = ref(false)
const approveForm = ref({
  queue_slot_id: '',
  notes: '',
})

const selectedSlotDetails = computed(() => {
  if (!approveForm.value.queue_slot_id) return null
  return props.availableSlots?.find(s => s.id === approveForm.value.queue_slot_id) || null
})

const openApproveModal = () => {
  approveForm.value.queue_slot_id = props.availableSlots?.[0]?.id || ''
  approveForm.value.notes = ''
  approveModalOpen.value = true
}

const submitApprove = () => {
  approveSubmitting.value = true
  router.post(
    route('admin.queue.applications.approve', props.application.id),
    approveForm.value,
    {
      onFinish: () => {
        approveSubmitting.value = false
        approveModalOpen.value = false
      },
    }
  )
}

// Revision Modal State
const revisionModalOpen = ref(false)
const revisionSubmitting = ref(false)
const revisionForm = ref({
  notes: '',
  rejectedDocIds: [],
})

const openRevisionModal = () => {
  revisionForm.value.notes = ''
  revisionForm.value.rejectedDocIds = []
  revisionModalOpen.value = true
}

const submitRevision = () => {
  revisionSubmitting.value = true
  const document_feedback = {}
  revisionForm.value.rejectedDocIds.forEach(id => {
    document_feedback[id] = {
      status: 'REJECTED',
      note: revisionForm.value.notes,
    }
  })

  router.post(
    route('admin.queue.applications.revision', props.application.id),
    {
      notes: revisionForm.value.notes,
      document_feedback,
    },
    {
      onFinish: () => {
        revisionSubmitting.value = false
        revisionModalOpen.value = false
      },
    }
  )
}

// Reject Modal State
const rejectModalOpen = ref(false)
const rejectSubmitting = ref(false)
const rejectForm = ref({
  reason: '',
})

const openRejectModal = () => {
  rejectForm.value.reason = ''
  rejectModalOpen.value = true
}

const submitReject = () => {
  rejectSubmitting.value = true
  router.post(
    route('admin.queue.applications.reject', props.application.id),
    rejectForm.value,
    {
      onFinish: () => {
        rejectSubmitting.value = false
        rejectModalOpen.value = false
      },
    }
  )
}

// Pre-Verify Modal State
const preVerifyModalOpen = ref(false)
const preVerifySubmitting = ref(false)
const preVerifyForm = ref({ notes: '' })

const openPreVerifyModal = () => {
  preVerifyForm.value.notes = 'Dokumen administrasi awal dinyatakan lengkap dan sah. Silakan memilih jadwal antrean kedatangan di portal.'
  preVerifyModalOpen.value = true
}
const handlePreVerify = openPreVerifyModal

const submitPreVerify = () => {
  preVerifySubmitting.value = true
  router.post(
    route('admin.queue.applications.pre-verify', props.application.id),
    { notes: preVerifyForm.value.notes },
    {
      onFinish: () => {
        preVerifySubmitting.value = false
        preVerifyModalOpen.value = false
      },
    }
  )
}

// Check-In Modal State
const checkInModalOpen = ref(false)
const checkInSubmitting = ref(false)

const openCheckInModal = () => {
  checkInModalOpen.value = true
}
const handleCheckIn = openCheckInModal

const submitCheckIn = () => {
  checkInSubmitting.value = true
  router.post(
    route('admin.queue.applications.checkin', props.application.id),
    {},
    {
      onFinish: () => {
        checkInSubmitting.value = false
        checkInModalOpen.value = false
      },
    }
  )
}

// Physical Verification Modal State
const physicalVerifyModalOpen = ref(false)
const physicalVerifySubmitting = ref(false)
const physicalVerifyForm = ref({
  notes: '',
  match_identity: true,
  match_documents: true,
  match_authorization: true,
})

const openPhysicalVerifyModal = () => {
  physicalVerifyForm.value = {
    notes: 'Pemeriksaan dokumen fisik asli (KK, KTP lama / Surat Kehilangan, Surat Kuasa) telah sesuai dan lengkap.',
    match_identity: true,
    match_documents: true,
    match_authorization: true,
  }
  physicalVerifyModalOpen.value = true
}
const handlePhysicalVerifySuccess = openPhysicalVerifyModal

const submitPhysicalVerify = () => {
  physicalVerifySubmitting.value = true
  router.post(
    route('admin.queue.applications.physical-verification', props.application.id),
    {
      decision: 'APPROVED_FOR_SERVICE',
      notes: physicalVerifyForm.value.notes,
      match_identity: physicalVerifyForm.value.match_identity,
      match_documents: physicalVerifyForm.value.match_documents,
      match_authorization: physicalVerifyForm.value.match_authorization,
    },
    {
      onFinish: () => {
        physicalVerifySubmitting.value = false
        physicalVerifyModalOpen.value = false
      },
    }
  )
}

// Processing Modal State (Informasikan Belum Dicetak / Sedang Diproses)
const processingModalOpen = ref(false)
const processingSubmitting = ref(false)
const processingForm = ref({ notes: '' })

const openProcessingModal = () => {
  processingForm.value.notes = 'Fisik KTP-el dalam proses cetak/rekam biometrik.'
  processingModalOpen.value = true
}

const submitProcessing = () => {
  processingSubmitting.value = true
  router.post(
    route('admin.queue.applications.start-processing', props.application.id),
    { notes: processingForm.value.notes },
    {
      onFinish: () => {
        processingSubmitting.value = false
        processingModalOpen.value = false
      },
    }
  )
}

// Ready for Pickup Modal State (Informasikan KTP Sudah Selesai Dicetak)
const readyPickupModalOpen = ref(false)
const readyPickupSubmitting = ref(false)
const readyPickupForm = ref({ notes: '' })

const openReadyPickupModal = () => {
  readyPickupForm.value.notes = 'Fisik KTP-el telah selesai dicetak dengan kualitas baik dan siap diserahkan kepada pemohon.'
  readyPickupModalOpen.value = true
}

const submitReadyPickup = () => {
  readyPickupSubmitting.value = true
  router.post(
    route('admin.queue.applications.ready-pickup', props.application.id),
    { notes: readyPickupForm.value.notes },
    {
      onFinish: () => {
        readyPickupSubmitting.value = false
        readyPickupModalOpen.value = false
      },
    }
  )
}

// Complete Modal State
const completeModalOpen = ref(false)
const completeSubmitting = ref(false)
const completeForm = ref({ notes: '' })

const openCompleteModal = () => {
  completeForm.value.notes = 'KTP-el fisik telah diserahkan kepada pemohon. Pelayanan selesai.'
  completeModalOpen.value = true
}

const submitComplete = () => {
  completeSubmitting.value = true
  router.post(
    route('admin.queue.applications.complete', props.application.id),
    { notes: completeForm.value.notes },
    {
      onFinish: () => {
        completeSubmitting.value = false
        completeModalOpen.value = false
      },
    }
  )
}

const formatPhoneChunked = (phone) => {
  if (!phone) return '-'
  let digits = String(phone).replace(/\D/g, '')
  if (digits.startsWith('62')) {
    digits = '0' + digits.slice(2)
  }
  digits = digits.slice(0, 13)
  if (!digits) return String(phone)
  const chunks = digits.match(/.{1,4}/g)
  return chunks ? chunks.join('-') : digits
}

const cleanPhone = (phone) => {
  if (!phone) return ''
  let cleaned = phone.replace(/[^0-9]/g, '')
  if (cleaned.startsWith('0')) {
    cleaned = '62' + cleaned.substring(1)
  }
  return cleaned
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    const d = new Date(dateString)
    if (isNaN(d.getTime())) return String(dateString)
    return d.toLocaleString('id-ID', {
      timeZone: 'Asia/Makassar',
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }) + ' WITA'
  } catch (e) {
    return String(dateString)
  }
}

const MONTH_NAMES_ID = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]
const DAY_NAMES_ID = [
  'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
]

function translateDateToIndonesian(str) {
  if (!str) return ''
  const translations = {
    'Sunday': 'Minggu',
    'Monday': 'Senin',
    'Tuesday': 'Selasa',
    'Wednesday': 'Rabu',
    'Thursday': 'Kamis',
    'Friday': 'Jumat',
    'Saturday': 'Sabtu',
    'January': 'Januari',
    'February': 'Februari',
    'March': 'Maret',
    'April': 'April',
    'May': 'Mei',
    'June': 'Juni',
    'July': 'Juli',
    'August': 'Agustus',
    'September': 'September',
    'October': 'Oktober',
    'November': 'November',
    'December': 'Desember',
  }
  let res = String(str)
  for (const [en, id] of Object.entries(translations)) {
    res = res.replace(new RegExp(`\\b${en}\\b`, 'gi'), id)
  }
  return res
}

const formatScheduleDate = (dateString) => {
  if (!dateString) return '-'
  try {
    const cleanDate = typeof dateString === 'string' ? dateString.split('T')[0].trim() : String(dateString)
    if (/^\d{4}-\d{2}-\d{2}$/.test(cleanDate)) {
      const [y, m, day] = cleanDate.split('-').map(Number)
      const d = new Date(y, m - 1, day)
      const dayName = DAY_NAMES_ID[d.getDay()]
      const dayNum = String(d.getDate()).padStart(2, '0')
      const monthName = MONTH_NAMES_ID[d.getMonth()]
      const year = d.getFullYear()
      return `${dayName}, ${dayNum} ${monthName} ${year}`
    }
    const d = new Date(dateString)
    if (!isNaN(d.getTime())) {
      const dayName = DAY_NAMES_ID[d.getDay()]
      const dayNum = String(d.getDate()).padStart(2, '0')
      const monthName = MONTH_NAMES_ID[d.getMonth()]
      const year = d.getFullYear()
      return `${dayName}, ${dayNum} ${monthName} ${year}`
    }
    return translateDateToIndonesian(String(dateString))
  } catch (e) {
    return translateDateToIndonesian(String(dateString))
  }
}

function formatSlotDateLabel(slot) {
  if (!slot) return '-'
  const dateStr = slot.date || slot.schedule?.schedule_date || slot.schedule_date
  if (dateStr) {
    return formatScheduleDate(dateStr)
  }
  if (slot.date_label) {
    return translateDateToIndonesian(slot.date_label)
  }
  return '-'
}

function formatSlotOptionLabel(slot) {
  if (!slot) return ''
  const dateLabel = formatSlotDateLabel(slot)
  const openTime = (slot.open_time || slot.schedule?.open_time || '08:00').substring(0, 5)
  const closeTime = (slot.close_time || slot.schedule?.close_time || '15:30').substring(0, 5)
  const remaining = slot.remaining_quota ?? Math.max(0, (slot.quota ?? 0) - (slot.booked_count ?? 0))
  const quota = slot.quota ?? 0
  const booked = slot.booked_count ?? 0
  return `${dateLabel} (${openTime} - ${closeTime} WITA) — Sisa Kuota: ${remaining} dari ${quota} (Terisi: ${booked})`
}
</script>
