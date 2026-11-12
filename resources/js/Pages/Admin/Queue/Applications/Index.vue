<template>
  <Head title="Daftar Pengajuan Antrian & Berkas - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Pengajuan Antrian & Verifikasi Berkas
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Daftar seluruh pendaftaran KTP-el online, pemeriksaan persyaratan dokumen, dan status pemanggilan.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('admin.queue.dashboard')"
            class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 0118 0z"/></svg>
            Kembali ke Dashboard
          </Link>
        </div>
      </div>

      <!-- Operator Special Reminders Banner -->
      <div
        v-if="(urgencyCounts?.overdue || 0) > 0 || (urgencyCounts?.new || 0) > 0"
        class="grid grid-cols-1 md:grid-cols-2 gap-4"
      >
        <!-- Overdue > 24 Hours Alert -->
        <div
          v-if="(urgencyCounts?.overdue || 0) > 0"
          class="p-4 rounded-2xl bg-gradient-to-r from-rose-50 to-rose-100/80 dark:from-rose-950/40 dark:to-rose-900/20 border-2 border-rose-300 dark:border-rose-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition"
        >
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/30">
              <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h4 class="text-xs sm:text-sm font-black text-rose-900 dark:text-rose-200">
                  {{ urgencyCounts.overdue }} Permohonan Belum Diverifikasi >1x24 Jam!
                </h4>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white animate-pulse">
                  Prioritas Utama
                </span>
              </div>
              <p class="text-[11px] text-rose-700 dark:text-rose-300/90 mt-0.5">
                Melewati batas waktu standar pelayanan (SLA 24 jam). Harap segera selesaikan verifikasi berkas.
              </p>
            </div>
          </div>
          <button
            @click="selectUrgency('overdue')"
            class="px-3.5 py-2 rounded-xl text-xs font-black bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20 transition whitespace-nowrap self-start sm:self-auto cursor-pointer flex items-center gap-1.5"
          >
            <span>Verifikasi Sekarang ({{ urgencyCounts.overdue }})</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>

        <!-- New Incoming Unverified Alert -->
        <div
          v-if="(urgencyCounts?.new || 0) > 0"
          class="p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-amber-100/80 dark:from-amber-950/40 dark:to-amber-900/20 border-2 border-amber-300 dark:border-amber-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition"
        >
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/30">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h4 class="text-xs sm:text-sm font-black text-amber-900 dark:text-amber-200">
                  {{ urgencyCounts.new }} Permohonan Baru Masuk
                </h4>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white">
                  Segera Diverifikasi
                </span>
              </div>
              <p class="text-[11px] text-amber-800 dark:text-amber-300/90 mt-0.5">
                Dokumen digital baru diunggah oleh pemohon dan menunggu pra-verifikasi operator.
              </p>
            </div>
          </div>
          <button
            @click="selectUrgency('new')"
            class="px-3.5 py-2 rounded-xl text-xs font-black bg-amber-500 hover:bg-amber-400 text-white shadow-md shadow-amber-500/20 transition whitespace-nowrap self-start sm:self-auto cursor-pointer flex items-center gap-1.5"
          >
            <span>Tinjau Berkas ({{ urgencyCounts.new }})</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>

      <!-- Filter Tabs by Status & Urgency -->
      <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
        <!-- All Status Tab -->
        <button
          @click="selectStatus('')"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            activeStatus === '' && !activeUrgency
              ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
              : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700'
          ]"
        >
          <span>Semua Status</span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono',
              activeStatus === '' && !activeUrgency ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
            ]"
          >
            {{ statusCounts?.all || 0 }}
          </span>
        </button>

        <!-- Overdue >24h Tab -->
        <button
          @click="selectUrgency('overdue')"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            activeUrgency === 'overdue'
              ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20 ring-2 ring-rose-300 dark:ring-rose-800'
              : 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50'
          ]"
        >
          <span class="flex items-center gap-1.5">
            <span v-if="(urgencyCounts?.overdue || 0) > 0" class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            <span>⚠️ Belum Diverifikasi >24 Jam</span>
          </span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold',
              activeUrgency === 'overdue' ? 'bg-rose-500 text-white' : 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300'
            ]"
          >
            {{ urgencyCounts?.overdue || 0 }}
          </span>
        </button>

        <!-- New Incoming Tab -->
        <button
          @click="selectUrgency('new')"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            activeUrgency === 'new'
              ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20 ring-2 ring-amber-300 dark:ring-amber-800'
              : 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50'
          ]"
        >
          <span class="flex items-center gap-1.5">
            <span v-if="(urgencyCounts?.new || 0) > 0" class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span>⚡ Baru Masuk (&lt;24 Jam)</span>
          </span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold',
              activeUrgency === 'new' ? 'bg-amber-400 text-white' : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300'
            ]"
          >
            {{ urgencyCounts?.new || 0 }}
          </span>
        </button>

        <!-- Standard Status Tabs -->
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="selectStatus(tab.value)"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            isTabActive(tab.value)
              ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
              : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700'
          ]"
        >
          <span>{{ tab.label }}</span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono',
              isTabActive(tab.value) ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
            ]"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
        <div class="flex flex-1 flex-col sm:flex-row gap-3">
          <!-- Search Input -->
          <div class="relative flex-1">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input
              v-model="searchQuery"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Cari No. Registrasi, NIK, Nama, No. HP, No. Antrian..."
              class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Service Filter -->
          <select
            v-model="selectedService"
            @change="applyFilters"
            class="px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Jenis Layanan</option>
            <option v-for="srv in services" :key="srv.id" :value="srv.id">
              {{ srv.name }}
            </option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="applyFilters"
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition"
          >
            Terapkan Filter
          </button>
          <button
            v-if="searchQuery || selectedService || activeStatus || activeUrgency"
            @click="resetFilters"
            class="px-3 py-2.5 text-xs text-slate-500 hover:text-rose-600 font-bold transition"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Application Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm !mt-3.5">
        <div class="overflow-x-auto scrollbar-thin">
          <table class="w-full text-left text-xs min-w-[1020px]">
            <thead class="bg-slate-50 dark:bg-slate-900/90 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700 select-none">
              <tr>
                <th class="px-3 py-3 whitespace-nowrap min-w-[140px]">No. Registrasi / Antrian</th>
                <th class="px-3 py-3 whitespace-nowrap min-w-[165px]">Foto & Data Pemohon</th>
                <th class="px-3 py-3 whitespace-nowrap min-w-[140px]">Pemilik Dokumen</th>
                <th class="px-3 py-3 text-center whitespace-nowrap min-w-[115px]">Hubungan (SHDK)</th>
                <th class="px-3 py-3 whitespace-nowrap min-w-[140px]">Jenis Layanan</th>
                <th class="px-3 py-3 whitespace-nowrap min-w-[115px]">Jadwal Pelayanan</th>
                <th class="px-3 py-3 text-center whitespace-nowrap min-w-[130px]">Status</th>
                <th class="sticky right-0 z-20 bg-slate-50 dark:bg-slate-900 px-3 py-3 text-center whitespace-nowrap border-l border-slate-200 dark:border-slate-700 shadow-[-6px_0_10px_-4px_rgba(0,0,0,0.08)] min-w-[175px]">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium text-slate-700 dark:text-slate-200">
              <tr
                v-for="app in applications.data"
                :key="app.id"
                :class="[
                  'transition group border-b border-slate-100 dark:border-slate-700/60',
                  app.verification_urgency === 'overdue'
                    ? 'bg-rose-50/70 dark:bg-rose-950/25 border-l-4 border-l-rose-500 hover:bg-rose-100/60 dark:hover:bg-rose-900/40'
                    : (app.verification_urgency === 'new'
                      ? 'bg-amber-50/50 dark:bg-amber-950/15 border-l-4 border-l-amber-500 hover:bg-amber-100/50 dark:hover:bg-amber-900/30'
                      : 'hover:bg-slate-50/80 dark:hover:bg-slate-700/30')
                ]"
              >
                <!-- Reg & Queue Number -->
                <td class="px-3 py-2.5 whitespace-nowrap min-w-[140px]">
                  <div class="font-mono font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                    <span>{{ app.reg_number }}</span>
                    <span
                      v-if="app.queue_number"
                      class="px-1.5 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 text-[10.5px] font-black font-mono border border-emerald-300 dark:border-emerald-800"
                    >
                      {{ app.queue_number }}
                    </span>
                  </div>
                  <p class="text-[10px] text-slate-400 mt-0.5">
                    Didaftarkan: {{ formatDate(app.submitted_at || app.created_at) }}
                  </p>

                  <!-- Urgent Reminder Badges -->
                  <div v-if="app.verification_urgency === 'overdue'" class="mt-1 flex items-center gap-1">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-rose-600 text-white shadow-xs animate-pulse">
                      <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                      Belum Diverifikasi >24 Jam
                    </span>
                    <span class="text-[9px] font-bold text-rose-600 dark:text-rose-400">
                      ({{ app.waiting_time_human }})
                    </span>
                  </div>
                  <div v-else-if="app.verification_urgency === 'new'" class="mt-1 flex items-center gap-1">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-xs">
                      <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                      Baru Masuk
                    </span>
                    <span class="text-[9px] font-bold text-amber-700 dark:text-amber-400">
                      ({{ app.waiting_time_human }})
                    </span>
                  </div>
                </td>

                <!-- Applicant & NIK with Photo Preview -->
                <td class="px-3 py-2.5 min-w-[165px]">
                  <div class="flex items-center gap-2.5">
                    <!-- Case 1: Photo Available (Unverified, File exists) - BLURRED FOR PRIVACY -->
                    <div
                      v-if="app.selfie_thumbnail_url || app.selfie_url"
                      class="group/photo relative w-9 h-9 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-xs shrink-0 bg-slate-100 dark:bg-slate-800 cursor-pointer"
                      title="Foto selfie disamarkan demi privasi pemohon (Arahkan kursor untuk melihat jelas)"
                    >
                      <img
                        :src="app.selfie_thumbnail_url || app.selfie_url"
                        :alt="getApplicantName(app)"
                        class="w-full h-full object-cover select-none pointer-events-none filter blur-[4px] group-hover/photo:blur-none transition-all duration-300 scale-110 group-hover/photo:scale-100"
                        loading="lazy"
                      />
                      <!-- Privacy icon badge overlay (dissolves on hover) -->
                      <div class="absolute inset-0 flex items-center justify-center bg-black/15 group-hover/photo:opacity-0 transition-opacity pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-white drop-shadow-sm opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                      </div>
                    </div>

                    <!-- Case 2: Photo Examined & Verified (Biometric Verified, File Safely Destroyed) - NEAT & POLISHED -->
                    <div
                      v-else-if="app.selfie_verification || app.is_selfie_verified"
                      class="relative w-9 h-9 rounded-xl border flex flex-col items-center justify-center shrink-0 shadow-xs select-none transition-all"
                      :class="app.selfie_verification?.decision === 'MATCH'
                        ? 'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-300 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400'
                        : 'bg-rose-50 dark:bg-rose-950/60 border-rose-300 dark:border-rose-800 text-rose-600 dark:text-rose-400'"
                      :title="app.selfie_verification?.decision === 'MATCH'
                        ? 'Foto Telah Terverifikasi: SESUAI PEMOHON (MATCH). Berkas fisik telah dimusnahkan secara aman.'
                        : 'Foto Telah Terverifikasi: BUKAN PEMOHON (MISMATCH). Berkas fisik telah dimusnahkan secara aman.'"
                    >
                      <!-- Styled Pill Icon -->
                      <div
                        class="w-3.5 h-3.5 rounded-full flex items-center justify-center shrink-0"
                        :class="app.selfie_verification?.decision === 'MATCH'
                          ? 'bg-emerald-500 text-white'
                          : 'bg-rose-500 text-white'"
                      >
                        <svg v-if="app.selfie_verification?.decision === 'MATCH'" class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </div>
                      <span
                        class="text-[7px] font-black uppercase tracking-tight leading-none mt-0.5 text-center"
                        :class="app.selfie_verification?.decision === 'MATCH' ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'"
                      >
                        {{ app.selfie_verification?.decision === 'MATCH' ? 'Sesuai' : 'Bukan' }}
                      </span>
                    </div>

                    <!-- Case 3: No Photo Uploaded / Empty -->
                    <div
                      v-else
                      class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-slate-400 shrink-0 select-none"
                      title="Belum ada foto selfie pemohon"
                    >
                      <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      <span class="text-[7px] font-bold text-slate-400 mt-0.5 leading-none">Kosong</span>
                    </div>

                    <!-- Applicant Text Info -->
                    <div class="min-w-0">
                      <div class="flex items-center gap-1 flex-wrap">
                        <span class="font-bold text-slate-900 dark:text-white truncate max-w-[115px]" :title="getApplicantName(app)">
                          {{ getApplicantName(app) }}
                        </span>
                        <span
                          v-if="isRepresented(app)"
                          class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shrink-0"
                          title="Mengajukan permohonan mewakili anggota keluarga"
                        >
                          Mewakili
                        </span>
                        <span
                          v-else
                          class="px-1.5 py-0.2 rounded text-[8.5px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 shrink-0"
                        >
                          Diri Sendiri
                        </span>
                      </div>
                      <div class="font-mono text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                        <span>NIK: {{ maskNik(getApplicantNik(app)) }}</span>
                      </div>
                      <!-- Biometric Verification Pill if verified and photo preview is active -->
                      <div v-if="(app.selfie_thumbnail_url || app.selfie_url) && (app.selfie_verification || app.is_selfie_verified)" class="mt-1">
                        <span
                          class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[8px] font-black uppercase tracking-tight border shadow-2xs"
                          :class="app.selfie_verification?.decision === 'MATCH'
                            ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800'
                            : 'bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-800'"
                          :title="app.selfie_verification?.decision === 'MATCH' ? 'Wajah Sesuai (MATCH)' : 'Bukan Pemohon (MISMATCH)'"
                        >
                          <svg v-if="app.selfie_verification?.decision === 'MATCH'" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                          <svg v-else class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                          <span>{{ app.selfie_verification?.decision === 'MATCH' ? 'Wajah Sesuai' : 'Bukan Pemohon' }}</span>
                        </span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Pemilik Dokumen (Yang Bersangkutan / YBS KTP-el) -->
                <td class="px-3 py-2.5 min-w-[140px]">
                  <div class="min-w-0">
                    <div v-if="isRepresented(app)">
                      <div class="flex items-center gap-1 flex-wrap">
                        <span class="font-bold text-slate-900 dark:text-white truncate max-w-[115px]" :title="getDocumentOwnerName(app)">
                          {{ getDocumentOwnerName(app) }}
                        </span>
                        <span
                          class="px-1.5 py-0.2 rounded text-[8.5px] font-bold bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shrink-0"
                          title="Pemilik Dokumen KTP-el"
                        >
                          Pemilik KTP
                        </span>
                      </div>
                      <div class="font-mono text-[10px] text-slate-400 mt-0.5">
                        NIK: {{ maskNik(getDocumentOwnerNik(app)) }}
                      </div>
                    </div>
                    <div v-else>
                      <div class="flex items-center gap-1 flex-wrap">
                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[115px]" :title="getDocumentOwnerName(app)">
                          {{ getDocumentOwnerName(app) }}
                        </span>
                        <span class="px-1.5 py-0.2 rounded text-[8.5px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shrink-0">
                          Sama dgn Pemohon
                        </span>
                      </div>
                      <div class="font-mono text-[10px] text-slate-400 mt-0.5">
                        NIK: {{ maskNik(getDocumentOwnerNik(app)) }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Status Hubungan Dalam Keluarga (SHDK) -->
                <td class="px-3 py-2.5 text-center whitespace-nowrap min-w-[115px]">
                  <div v-if="isRepresented(app)" class="inline-flex flex-col items-center">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-2xs">
                      {{ getFamilyShdk(app) }}
                    </span>
                    <span class="text-[9px] text-slate-400 mt-0.5 font-medium">
                      {{ app.representative_kk_status === 'same_kk' || !app.representative_kk_status ? 'Dalam 1 KK' : 'Luar KK' }}
                    </span>
                  </div>
                  <div v-else class="text-slate-400 text-center">
                    <span class="font-mono text-xs text-slate-400 font-bold">-</span>
                  </div>
                </td>

                <!-- Service Type -->
                <td class="px-3 py-2.5 whitespace-nowrap min-w-[140px]">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-[11px] font-semibold">
                    {{ app.service_type?.name || 'Pelayanan KTP-el' }}
                  </span>
                </td>

                <!-- Schedule Date -->
                <td class="px-3 py-2.5 whitespace-nowrap min-w-[115px]">
                  <div v-if="app.slot?.schedule?.schedule_date">
                    <p class="font-bold text-slate-900 dark:text-white text-xs">
                      {{ formatScheduleDate(app.slot.schedule.schedule_date) }}
                    </p>
                    <p class="text-[10px] text-slate-400">
                      {{ app.slot.schedule.open_time?.substring(0, 5) }} - {{ app.slot.schedule.close_time?.substring(0, 5) }} WITA
                    </p>
                  </div>
                  <span v-else class="text-slate-400 italic text-[11px]">
                    Belum dijadwalkan
                  </span>
                </td>

                <!-- Status Badge -->
                <td class="px-3 py-2.5 text-center whitespace-nowrap min-w-[130px]">
                  <div v-if="app.status === 'DRAFT'" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping shrink-0"></span>
                    <span>Draf (Sisa {{ app.draft_remaining_minutes !== undefined ? app.draft_remaining_minutes : (draftTimeoutMinutes || 15) }} mnt)</span>
                  </div>
                  <StatusBadge v-else :status="app.status" />
                  <div v-if="app.verification_urgency === 'overdue'" class="mt-1">
                    <span class="inline-block text-[8.5px] font-extrabold text-rose-600 dark:text-rose-400 bg-rose-100 dark:bg-rose-950/80 px-1.5 py-0.5 rounded-full">
                      ⚠️ Terlambat SLA 1x24 Jam
                    </span>
                  </div>
                  <div v-else-if="app.verification_urgency === 'new'" class="mt-1">
                    <span class="inline-block text-[8.5px] font-extrabold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/80 px-1.5 py-0.5 rounded-full">
                      ⚡ Butuh Verifikasi Segera
                    </span>
                  </div>
                </td>

                <!-- Actions -->
                <td
                  :class="[
                    'sticky right-0 z-10 px-2.5 py-2.5 text-center whitespace-nowrap border-l border-slate-100 dark:border-slate-700/60 shadow-[-6px_0_10px_-4px_rgba(0,0,0,0.08)] transition min-w-[175px]',
                    app.verification_urgency === 'overdue'
                      ? 'bg-rose-50/95 dark:bg-rose-950/95 group-hover:bg-rose-100/95 dark:group-hover:bg-rose-900/95'
                      : (app.verification_urgency === 'new'
                        ? 'bg-amber-50/95 dark:bg-amber-950/95 group-hover:bg-amber-100/95 dark:group-hover:bg-amber-900/95'
                        : 'bg-white dark:bg-slate-800 group-hover:bg-slate-50 dark:group-hover:bg-slate-700/80')
                  ]"
                >
                  <div class="flex items-center justify-center gap-1">
                    <!-- 1. Primary Action: Tinjau Berkas -->
                    <Link
                      v-if="app.verification_urgency === 'overdue'"
                      :href="route('admin.queue.applications.show', app.id)"
                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-sm shadow-rose-600/30 transition shrink-0 animate-pulse"
                      title="Verifikasi Permohonan Segera (Lewat SLA)"
                    >
                      <span>Verifikasi</span>
                      <span class="hidden 2xl:inline">Sekarang</span>
                      <span>⚠️</span>
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <Link
                      v-else-if="app.verification_urgency === 'new'"
                      :href="route('admin.queue.applications.show', app.id)"
                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-white font-bold text-xs shadow-sm shadow-amber-500/30 transition shrink-0"
                      title="Tinjau Berkas Pemohon"
                    >
                      <span>Tinjau Berkas</span>
                      <span>⚡</span>
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                    <Link
                      v-else
                      :href="route('admin.queue.applications.show', app.id)"
                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition shrink-0"
                      title="Tinjau Berkas Pemohon"
                    >
                      <span>Tinjau Berkas</span>
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>

                    <!-- 2. Quick Status Escalation / Sync Button -->
                    <button
                      type="button"
                      @click="openEscalateModal(app)"
                      class="inline-flex items-center gap-1 px-2 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white text-indigo-700 dark:text-indigo-300 font-bold text-xs shadow-2xs transition cursor-pointer shrink-0"
                      title="Eskalasi / Update Status Permohonan"
                    >
                      <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                      <span>Status</span>
                    </button>

                    <!-- 3. Super Admin Delete Button -->
                    <button
                      v-if="isSuperAdmin"
                      type="button"
                      @click="confirmDelete(app)"
                      class="inline-flex items-center justify-center p-1.5 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white text-rose-600 dark:text-rose-400 font-bold text-xs shadow-xs transition cursor-pointer shrink-0"
                      title="Hapus Permohonan (Khusus Super Admin)"
                    >
                      <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="!applications.data || applications.data.length === 0">
                <td colspan="8" class="px-6 py-16 text-center text-slate-400">
                  <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </div>
                  <p class="font-bold text-slate-600 dark:text-slate-300">Tidak ada pengajuan ditemukan</p>
                  <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination with Per-Page Selector & Clean Page Numbers -->
        <div
          v-if="applications.total > 0"
          class="px-4 py-3.5 border-t border-slate-100 dark:border-slate-700/60 flex flex-col sm:flex-row items-center justify-between gap-3 select-none"
        >
          <!-- Info & Per-Page Selector -->
          <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
            <span>
              Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ applications.from || 0 }}</span> -
              <span class="font-bold text-slate-700 dark:text-slate-200">{{ applications.to || 0 }}</span> dari total
              <span class="font-bold text-slate-700 dark:text-slate-200">{{ applications.total }}</span> pendaftar
            </span>
            <div class="hidden sm:flex items-center gap-1.5 pl-3 border-l border-slate-200 dark:border-slate-700">
              <span class="text-[11px] text-slate-400">Tampilkan:</span>
              <select
                v-model="perPage"
                @change="changePerPage"
                class="px-2 py-1 text-xs font-bold bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer"
              >
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>
          </div>

          <!-- Page Buttons -->
          <div
            v-if="applications.links && applications.links.length > 3"
            class="flex items-center gap-1 overflow-x-auto max-w-full pb-1 sm:pb-0 scrollbar-thin"
          >
            <!-- Tombol Sebelumnya -->
            <Link
              v-if="applications.prev_page_url"
              :href="applications.prev_page_url"
              preserve-scroll
              preserve-state
              class="h-8 px-2.5 rounded-lg text-xs font-bold transition flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-2xs cursor-pointer active:scale-95"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              <span>Prev</span>
            </Link>
            <span
              v-else
              class="h-8 px-2.5 rounded-lg text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 cursor-not-allowed flex items-center gap-1 opacity-60"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              <span>Prev</span>
            </span>

            <!-- Nomor Halaman -->
            <template v-for="(link, i) in numericLinks" :key="i">
              <span
                v-if="link.label === '...'"
                class="w-8 h-8 flex items-center justify-center text-xs text-slate-400 font-bold"
              >
                ...
              </span>
              <Link
                v-else-if="link.url"
                :href="link.url"
                preserve-scroll
                preserve-state
                :class="[
                  'w-8 h-8 rounded-lg text-xs font-bold transition flex items-center justify-center cursor-pointer active:scale-95',
                  link.active
                    ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-500'
                    : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-2xs'
                ]"
              >
                {{ link.label }}
              </Link>
              <span
                v-else
                class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center text-slate-400 bg-slate-100 dark:bg-slate-800 cursor-not-allowed"
              >
                {{ link.label }}
              </span>
            </template>

            <!-- Tombol Berikutnya -->
            <Link
              v-if="applications.next_page_url"
              :href="applications.next_page_url"
              preserve-scroll
              preserve-state
              class="h-8 px-2.5 rounded-lg text-xs font-bold transition flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-2xs cursor-pointer active:scale-95"
            >
              <span>Next</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
            <span
              v-else
              class="h-8 px-2.5 rounded-lg text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 cursor-not-allowed flex items-center gap-1 opacity-60"
            >
              <span>Next</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal (Super Admin) -->
      <transition name="fade">
        <div
          v-if="deleteModalOpen"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        >
          <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl w-full max-w-md shadow-2xl p-6 relative text-center">
            <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-100 dark:border-rose-900/40 shadow-inner">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>

            <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">
              Hapus Permohonan Antrian?
            </h3>

            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5 leading-relaxed">
              Anda akan menghapus permanen data permohonan nomor
              <strong class="text-slate-800 dark:text-slate-200 font-mono font-bold block text-sm mt-1 mb-1">
                {{ appToDelete?.reg_number }} ({{ appToDelete?.applicant_name }})
              </strong>
              Layanan: <span class="font-medium text-slate-700 dark:text-slate-300">{{ appToDelete?.service_type?.name }}</span>.<br/>
              Seluruh berkas dokumen foto fisik yang telah diunggah dan nomor antrian yang dibooking akan dibatalkan & dibersihkan dari server.
            </p>

            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 rounded-2xl text-[11px] text-amber-700 dark:text-amber-300 font-medium mb-6 text-left flex items-start gap-2">
              <span class="text-base leading-none shrink-0">⚠️</span>
              <span><strong>Aksi Super Admin:</strong> Tindakan ini bersifat permanen dan tidak dapat dibatalkan.</span>
            </div>

            <div class="flex items-center justify-center gap-3">
              <button
                type="button"
                @click="deleteModalOpen = false; appToDelete = null"
                :disabled="isDeleting"
                class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer"
              >
                Batal
              </button>
              <button
                type="button"
                @click="executeDelete"
                :disabled="isDeleting"
                class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/30 active:scale-95 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span v-if="isDeleting">Menghapus...</span>
                <span v-else>Ya, Hapus Permanen</span>
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Modal Eskalasi & Sinkronisasi Status -->
      <transition name="fade">
        <div
          v-if="escalateModalOpen && escalateTargetApp"
          class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto"
          @click.self="escalateModalOpen = false"
        >
          <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-gradient-to-r from-indigo-50/80 to-white dark:from-slate-800 dark:to-slate-800">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/30">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                  <h3 class="font-black text-slate-900 dark:text-white text-base">
                    Eskalasi & Update Status
                  </h3>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Sinkronkan status permohonan ke tahapan pelayanan yang sesuai
                  </p>
                </div>
              </div>
              <button
                type="button"
                @click="escalateModalOpen = false"
                class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form @submit.prevent="submitEscalate" class="p-5 space-y-4">
              <!-- Info Pemohon -->
              <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                  <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ escalateTargetApp.reg_number }}</span>
                  <StatusBadge :status="escalateTargetApp.status" size="sm" />
                </div>
                <div class="font-black text-slate-900 dark:text-white text-sm">
                  {{ escalateTargetApp.applicant_name }}
                  <span class="font-mono text-xs font-normal text-slate-500">({{ maskNik(escalateTargetApp.applicant_nik) }})</span>
                </div>
                <div class="text-slate-500 dark:text-slate-400">
                  Layanan: <strong class="text-slate-700 dark:text-slate-200">{{ escalateTargetApp.service_type?.name }}</strong>
                </div>
              </div>

              <!-- Warning Banner if in Draft -->
              <div
                v-if="escalateTargetApp.status === 'DRAFT'"
                class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-2.5"
              >
                <span class="text-base shrink-0">⚠️</span>
                <p class="leading-relaxed">
                  Permohonan ini saat ini berstatus <strong>Draf</strong>. Jika warga terkendala jaringan saat kirim berkas, pilih <strong>Menunggu Verifikasi</strong> atau <strong>Jadwal Antrean Terbit</strong> untuk meloloskan permohonan ke proses pelayanan aktif.
                </p>
              </div>

              <!-- Pilihan Status Target -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                  Target Status Eskalasi <span class="text-rose-500">*</span>
                </label>
                <select
                  v-model="escalateForm.status"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  required
                >
                  <optgroup label="Tahap Pengajuan & Verifikasi Online">
                    <option value="SUBMITTED">Menunggu Verifikasi (SUBMITTED)</option>
                    <option value="UNDER_REVIEW">Sedang Diverifikasi Petugas (UNDER_REVIEW)</option>
                    <option value="PRE_VERIFIED">Lolos Pra-Verifikasi (PRE_VERIFIED)</option>
                    <option value="REVISION_REQUIRED">Perlu Perbaikan Berkas (REVISION_REQUIRED)</option>
                    <option value="REJECTED">Pengajuan Ditolak (REJECTED)</option>
                  </optgroup>
                  <optgroup label="Tahap Loket & Pelayanan Fisik">
                    <option value="QUEUE_BOOKED">Jadwal & Nomor Antrean Terbit (QUEUE_BOOKED)</option>
                    <option value="ARRIVED">Hadir di Loket Offline (ARRIVED)</option>
                    <option value="PROCESSING">Perekaman / Pencetakan Diproses (PROCESSING)</option>
                    <option value="READY_FOR_PICKUP">KTP Siap Diambil di Loket (READY_FOR_PICKUP)</option>
                    <option value="COMPLETED">Pelayanan Selesai Diserahkan (COMPLETED)</option>
                  </optgroup>
                  <optgroup label="Status Lainnya">
                    <option value="DRAFT">Draf (Belum Dikirim)</option>
                    <option value="CANCELLED">Dibatalkan (CANCELLED)</option>
                  </optgroup>
                </select>
              </div>

              <!-- Auto issue queue checkbox -->
              <div v-if="!escalateTargetApp.queue_number && ['SUBMITTED', 'UNDER_REVIEW', 'PRE_VERIFIED', 'QUEUE_BOOKED', 'ARRIVED', 'PROCESSING', 'READY_FOR_PICKUP', 'COMPLETED'].includes(escalateForm.status)">
                <label class="flex items-start gap-2.5 p-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-900/60 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="escalateForm.auto_issue_queue"
                    class="rounded text-indigo-600 focus:ring-indigo-500 mt-0.5"
                  />
                  <div class="text-xs">
                    <span class="font-bold text-indigo-900 dark:text-indigo-200 block">
                      Otomatis Terbitkan Nomor Antrean & Jadwal Terdekat
                    </span>
                    <span class="text-indigo-700 dark:text-indigo-400 text-[11px]">
                      Jika dicentang dan belum memiliki nomor antrean, sistem akan langsung mengalokasikan nomor antrean dan jadwal loket terdekat.
                    </span>
                  </div>
                </label>
              </div>

              <!-- Catatan / Alasan Eskalasi -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Catatan / Alasan Eskalasi <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea
                  v-model="escalateForm.reason"
                  rows="3"
                  placeholder="Contoh: Pemohon melapor status macet di Draf, berkas dicek lengkap / eskalasi status ke loket."
                  class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                ></textarea>
              </div>

              <!-- Buttons -->
              <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button
                  type="button"
                  @click="escalateModalOpen = false"
                  class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="escalateForm.processing"
                  class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                >
                  <span v-if="escalateForm.processing">Menyimpan...</span>
                  <span v-else>Simpan & Eskalasi Status</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/Queue/StatusBadge.vue'

const props = defineProps({
  applications: Object,
  statusCounts: Object,
  urgencyCounts: Object,
  services: Array,
  filters: Object,
  can_delete: Boolean,
  draftTimeoutMinutes: {
    type: Number,
    default: 15,
  },
})

const page = usePage()
const isSuperAdmin = computed(() => {
  return props.can_delete 
    || page.props.auth?.is_super_admin 
    || page.props.auth?.roles?.includes('Administrator Utama') 
    || page.props.auth?.roles?.includes('Super Administrator') 
    || page.props.auth?.user?.username === 'superadmin'
})

const deleteModalOpen = ref(false)
const appToDelete = ref(null)
const isDeleting = ref(false)

const confirmDelete = (app) => {
  appToDelete.value = app
  deleteModalOpen.value = true
}

const executeDelete = () => {
  if (!appToDelete.value) return
  isDeleting.value = true
  router.delete(route('admin.queue.applications.destroy', appToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      deleteModalOpen.value = false
      appToDelete.value = null
      isDeleting.value = false
    },
    onError: () => {
      isDeleting.value = false
    },
    onFinish: () => {
      isDeleting.value = false
    }
  })
}

// Status Escalation & Manual Sync
const escalateModalOpen = ref(false)
const escalateTargetApp = ref(null)
const escalateForm = useForm({
  status: '',
  reason: '',
  auto_issue_queue: true,
})

const openEscalateModal = (app) => {
  escalateTargetApp.value = app
  escalateForm.status = app.status === 'DRAFT' ? 'SUBMITTED' : app.status
  escalateForm.reason = ''
  escalateForm.auto_issue_queue = true
  escalateModalOpen.value = true
}

const submitEscalate = () => {
  if (!escalateTargetApp.value) return
  escalateForm.post(route('admin.queue.applications.update-status', escalateTargetApp.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      escalateModalOpen.value = false
      escalateTargetApp.value = null
    },
  })
}

const activeStatus = ref(props.filters?.status || '')
const activeUrgency = ref(props.filters?.urgency || '')
const searchQuery = ref(props.filters?.search || '')
const selectedService = ref(props.filters?.service_id || '')
const perPage = ref(String(props.filters?.per_page || 15))

const numericLinks = computed(() => {
  if (!props.applications?.links || props.applications.links.length <= 2) return []
  return props.applications.links.slice(1, -1)
})

const changePerPage = () => {
  applyFilters()
}

const statusTabs = computed(() => [
  { label: 'Menunggu Verifikasi', value: 'SUBMITTED', count: props.statusCounts?.SUBMITTED || 0 },
  { label: 'Sedang Diverifikasi', value: 'UNDER_REVIEW', count: props.statusCounts?.UNDER_REVIEW || 0 },
  { label: 'Perlu Perbaikan', value: 'REVISION_REQUIRED', count: props.statusCounts?.REVISION_REQUIRED || 0 },
  { label: 'Lolos Pra-Verifikasi', value: 'PRE_VERIFIED', count: props.statusCounts?.PRE_VERIFIED || 0 },
  { label: 'Jadwal Antrean Terbit', value: 'QUEUE_BOOKED', count: props.statusCounts?.QUEUE_BOOKED || 0 },
  { label: 'Hadir di Loket', value: 'ARRIVED', count: props.statusCounts?.ARRIVED || 0 },
  { label: 'Pemeriksaan Fisik', value: 'PHYSICAL_VERIFICATION', count: props.statusCounts?.PHYSICAL_VERIFICATION || 0 },
  { label: 'Sedang Rekam / Cetak', value: 'PROCESSING', count: props.statusCounts?.PROCESSING || 0 },
  { label: 'KTP Siap Diambil', value: 'READY_FOR_PICKUP', count: props.statusCounts?.READY_FOR_PICKUP || 0 },
  { label: 'Pelayanan Selesai', value: 'COMPLETED', count: props.statusCounts?.COMPLETED || 0 },
  { label: 'Pengajuan Ditolak', value: 'REJECTED', count: props.statusCounts?.REJECTED || 0 },
  { label: `Draf (Masa Tunggu ≤${props.draftTimeoutMinutes || 15} mnt)`, value: 'DRAFT', count: props.statusCounts?.DRAFT || 0 },
  { label: 'Batal / Kedaluwarsa', value: 'EXPIRED', count: props.statusCounts?.EXPIRED || 0 },
])

const isTabActive = (tabVal) => {
  if (activeUrgency.value) return false
  if (activeStatus.value === tabVal) return true
  if (tabVal === 'PRE_VERIFIED' && activeStatus.value === 'APPROVED') return true
  if (tabVal === 'QUEUE_BOOKED' && activeStatus.value === 'SCHEDULED') return true
  if (tabVal === 'ARRIVED' && activeStatus.value === 'CHECKED_IN') return true
  if (tabVal === 'PROCESSING' && activeStatus.value === 'IN_SERVICE') return true
  return false
}

const selectStatus = (status) => {
  activeStatus.value = status
  activeUrgency.value = ''
  applyFilters()
}

const selectUrgency = (urgency) => {
  activeUrgency.value = urgency
  activeStatus.value = ''
  applyFilters()
}

const applyFilters = () => {
  router.get(
    route('admin.queue.applications.index'),
    {
      status: activeStatus.value || undefined,
      urgency: activeUrgency.value || undefined,
      search: searchQuery.value || undefined,
      service_id: selectedService.value || undefined,
      per_page: perPage.value !== '15' ? perPage.value : undefined,
    },
    { preserveState: true, replace: true }
  )
}

const resetFilters = () => {
  activeStatus.value = ''
  activeUrgency.value = ''
  searchQuery.value = ''
  selectedService.value = ''
  perPage.value = '15'
  router.get(route('admin.queue.applications.index'), {}, { preserveState: true, replace: true })
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

const maskNik = (nik) => {
  if (!nik || nik.length < 8) return nik
  return nik.substring(0, 6) + '******' + nik.substring(12)
}

const isRepresented = (app) => {
  if (!app) return false
  return Boolean(
    app.is_represented ||
    app.attendance_type === 'family_in_kk' ||
    app.attendance_type === 'family_out_kk' ||
    app.attendance_type === 'other' ||
    app.attendance_type === 'representative' ||
    app.attendance_type === 'PROXY' ||
    app.family_shdk ||
    (app.representative_name && app.representative_name.trim() !== '' && app.representative_name !== app.applicant_name)
  )
}

const getApplicantName = (app) => {
  if (!app) return '-'
  if (isRepresented(app)) {
    return app.representative_name || app.applicant_account?.name || app.applicant_name
  }
  return app.applicant_name
}

const getApplicantNik = (app) => {
  if (!app) return ''
  if (isRepresented(app)) {
    return app.representative_nik || app.applicant_account?.nik || app.applicant_nik
  }
  return app.applicant_nik
}

const getDocumentOwnerName = (app) => {
  if (!app) return '-'
  return app.applicant_name
}

const getDocumentOwnerNik = (app) => {
  if (!app) return ''
  return app.applicant_nik
}

const getFamilyShdk = (app) => {
  if (!app || !isRepresented(app)) return '-'
  return app.family_shdk || app.representative_relationship || 'Keluarga (1 KK)'
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

const MONTH_NAMES_SHORT_ID = [
  'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
  'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
]
const DAY_NAMES_SHORT_ID = [
  'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'
]

const formatScheduleDate = (dateString) => {
  if (!dateString) return '-'
  try {
    const clean = typeof dateString === 'string' ? dateString.split('T')[0].trim() : String(dateString)
    if (/^\d{4}-\d{2}-\d{2}$/.test(clean)) {
      const [y, m, day] = clean.split('-').map(Number)
      const d = new Date(y, m - 1, day)
      const dayName = DAY_NAMES_SHORT_ID[d.getDay()]
      const dayNum = d.getDate()
      const monthName = MONTH_NAMES_SHORT_ID[d.getMonth()]
      const year = d.getFullYear()
      return `${dayName}, ${dayNum} ${monthName} ${year}`
    }
    const fallback = new Date(dateString)
    if (!isNaN(fallback.getTime())) {
      const dayName = DAY_NAMES_SHORT_ID[fallback.getDay()]
      const dayNum = fallback.getDate()
      const monthName = MONTH_NAMES_SHORT_ID[fallback.getMonth()]
      const year = fallback.getFullYear()
      return `${dayName}, ${dayNum} ${monthName} ${year}`
    }
    return String(dateString)
  } catch (e) {
    return String(dateString)
  }
}
</script>
