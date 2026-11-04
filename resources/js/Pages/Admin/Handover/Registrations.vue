<template>
  <Head title="Pemohon Sudah Registrasi - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800 mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Modul Serah Terima KTP-el & Antrean
          </div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Pemohon Sudah Registrasi
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Daftar seluruh warga pemohon yang telah melakukan pendaftaran antrean online KTP-el, status verifikasi berkas, serta ketersediaan fisik kartu di bagian serah terima.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            href="/admin/handover/ready"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Siap Diambil ({{ counts.siap_diambil || 0 }})</span>
          </Link>
        </div>
      </div>

      <!-- Quick Stats Overview -->
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <div 
          @click="selectStatus('')"
          :class="[
            'p-4 rounded-2xl border transition cursor-pointer',
            activeStatus === ''
              ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-300 dark:border-blue-700 shadow-sm'
              : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50'
          ]"
        >
          <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Terdaftar</p>
          <p class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ counts.total || 0 }}</p>
        </div>

        <div 
          @click="selectStatus('DRAFT')"
          :class="[
            'p-4 rounded-2xl border transition cursor-pointer',
            activeStatus === 'DRAFT'
              ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700 shadow-sm'
              : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50'
          ]"
        >
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Draf Tunggu</p>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 font-bold font-mono">≤{{ draftTimeoutMinutes }}m</span>
          </div>
          <p class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ counts.draft || 0 }}</p>
        </div>

        <div 
          @click="selectStatus('MENUNGGU_VERIFIKASI')"
          :class="[
            'p-4 rounded-2xl border transition cursor-pointer',
            activeStatus === 'MENUNGGU_VERIFIKASI'
              ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700 shadow-sm'
              : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50'
          ]"
        >
          <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Menunggu Verif</p>
          <p class="text-xl font-black text-amber-600 mt-1">{{ counts.menunggu_verifikasi || 0 }}</p>
        </div>

        <div 
          @click="selectStatus('LOLOS_VERIFIKASI')"
          :class="[
            'p-4 rounded-2xl border transition cursor-pointer',
            activeStatus === 'LOLOS_VERIFIKASI'
              ? 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-300 dark:border-indigo-700 shadow-sm'
              : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50'
          ]"
        >
          <p class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Lolos / Antrean</p>
          <p class="text-xl font-black text-indigo-600 mt-1">{{ counts.lolos_verifikasi || 0 }}</p>
        </div>

        <div 
          @click="selectStatus('MENUNGGU_KETERSEDIAAN')"
          :class="[
            'p-4 rounded-2xl border transition cursor-pointer',
            activeStatus === 'MENUNGGU_KETERSEDIAAN'
              ? 'bg-sky-50 dark:bg-sky-950/40 border-sky-300 dark:border-sky-700 shadow-sm'
              : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50'
          ]"
        >
          <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Menunggu Fisik</p>
          <p class="text-xl font-black text-sky-600 mt-1">{{ counts.menunggu_ketersediaan || 0 }}</p>
        </div>

        <div 
          @click="selectStatus('SIAP_DIAMBIL')"
          :class="[
            'p-4 rounded-2xl border transition cursor-pointer',
            activeStatus === 'SIAP_DIAMBIL'
              ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 shadow-sm'
              : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50'
          ]"
        >
          <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Siap Diambil</p>
          <p class="text-xl font-black text-emerald-600 mt-1">{{ counts.siap_diambil || 0 }}</p>
        </div>

        <div 
          @click="selectStatus('SUDAH_DISERAHKAN')"
          :class="[
            'p-4 rounded-2xl border transition cursor-pointer',
            activeStatus === 'SUDAH_DISERAHKAN'
              ? 'bg-purple-50 dark:bg-purple-950/40 border-purple-300 dark:border-purple-700 shadow-sm'
              : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50'
          ]"
        >
          <p class="text-[11px] font-bold text-purple-600 uppercase tracking-wider">Diserahkan</p>
          <p class="text-xl font-black text-purple-600 mt-1">{{ counts.sudah_diserahkan || 0 }}</p>
        </div>
      </div>

      <!-- Search & Filters Bar -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between gap-3 text-[11px] pb-2 border-b border-slate-100 dark:border-slate-700">
          <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
            <span class="flex h-2.5 w-2.5 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
            </span>
            <span class="font-bold text-slate-800 dark:text-slate-200">Pencarian & Barcode Scanner</span>
            <span class="text-slate-400 hidden sm:inline">· Ketik atau scan kode barcode permohonan pemohon</span>
          </div>
          <span class="text-slate-400 font-mono text-[10px]">Realtime Data</span>
        </div>

        <form @submit.prevent="applyFilters" class="flex flex-col md:flex-row items-center gap-3">
          <!-- Search input -->
          <div class="relative flex-1 w-full">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Scan barcode atau cari No. Registrasi, NIK, Nama, No. HP, No. Antrean..."
              class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>

          <!-- Service Type Filter -->
          <select
            v-model="selectedService"
            @change="applyFilters"
            class="w-full md:w-56 px-3 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Jenis Layanan</option>
            <option v-for="s in services" :key="s.id" :value="s.id">
              {{ s.name }}
            </option>
          </select>

          <!-- Submit & Reset -->
          <div class="flex items-center gap-2 w-full md:w-auto">
            <button
              type="submit"
              class="flex-1 md:flex-none px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-md shadow-blue-600/20 cursor-pointer"
            >
              Cari Data
            </button>
            <button
              v-if="searchQuery || selectedService || activeStatus"
              type="button"
              @click="resetFilters"
              class="px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-600 dark:text-slate-300 font-bold text-xs transition cursor-pointer"
            >
              Reset
            </button>
          </div>
        </form>

        <!-- Status Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 pt-1 text-xs">
          <button
            v-for="tab in filterTabs"
            :key="tab.value"
            @click="selectStatus(tab.value)"
            :class="[
              'px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer flex items-center gap-1.5',
              activeStatus === tab.value
                ? 'bg-blue-600 text-white shadow-sm'
                : 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200'
            ]"
          >
            <span>{{ tab.label }}</span>
            <span 
              :class="[
                'px-1.5 py-0.2 rounded-full text-[10px] font-black',
                activeStatus === tab.value ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>
      </div>

      <!-- Main Applications Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-5 py-4">No. Registrasi / Antrean</th>
                <th class="px-5 py-4">Foto & Data Pemohon</th>
                <th class="px-5 py-4">Jenis Layanan</th>
                <th class="px-5 py-4">Status Berkas</th>
                <th class="px-5 py-4">Status Fisik KTP (Loket)</th>
                <th class="px-5 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium text-slate-700 dark:text-slate-200">
              <tr
                v-for="app in applications.data"
                :key="app.id"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition border-b border-slate-100 dark:border-slate-700/60"
              >
                <!-- No Registrasi & Antrean -->
                <td class="px-5 py-3.5 whitespace-nowrap">
                  <div class="font-mono font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ app.reg_number }}</span>
                    <span
                      v-if="app.queue_number"
                      class="px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 text-[11px] font-black font-mono border border-emerald-300 dark:border-emerald-800"
                    >
                      {{ app.queue_number }}
                    </span>
                  </div>
                  <p class="text-[10px] text-slate-400 mt-0.5">
                    Daftar: {{ formatDate(app.created_at) }}
                  </p>
                </td>

                <!-- Foto & Data Pemohon -->
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <!-- Thumbnail with Lightbox trigger -->
                    <div
                      v-if="app.selfie_thumbnail_url || app.selfie_url"
                      @click.stop="openPhotoPreview(app)"
                      class="relative w-12 h-12 rounded-xl overflow-hidden border border-blue-200 dark:border-blue-900 shadow-sm shrink-0 cursor-pointer group/photo bg-slate-100 dark:bg-slate-800 transition transform hover:scale-105 hover:ring-2 hover:ring-blue-500"
                      title="Klik untuk melihat pratinjau foto pemohon"
                    >
                      <img
                        :src="app.selfie_thumbnail_url || app.selfie_url"
                        :alt="app.applicant_name"
                        class="w-full h-full object-cover transition duration-200"
                        loading="lazy"
                      />
                      <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/photo:opacity-100 transition flex items-center justify-center text-white">
                        <svg class="w-4 h-4 drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                      </div>
                    </div>
                    <div
                      v-else
                      class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700/60 border border-dashed border-slate-300 dark:border-slate-600 flex flex-col items-center justify-center text-slate-400 shrink-0 text-[8px] font-semibold select-none"
                      title="Belum ada foto selfie pemohon"
                    >
                      <svg class="w-5 h-5 text-slate-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      <span class="text-[8px] leading-tight text-slate-400 mt-0.5">No Foto</span>
                    </div>

                    <!-- Applicant Name & NIK -->
                    <div class="min-w-0">
                      <div class="font-bold text-slate-900 dark:text-white truncate max-w-xs">
                        {{ app.applicant_name }}
                      </div>
                      <div class="font-mono text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                        <span>NIK: {{ maskNik(app.applicant_nik) }}</span>
                        <span>·</span>
                        <span>{{ formatPhoneChunked(app.applicant_phone) }}</span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Jenis Layanan -->
                <td class="px-5 py-3.5 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                    {{ app.service_type?.name || 'Pelayanan KTP-el' }}
                  </span>
                </td>

                <!-- Status Berkas Administrasi -->
                <td class="px-5 py-3.5 whitespace-nowrap">
                  <div v-if="app.status === 'DRAFT'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping shrink-0"></span>
                    <span>Draf (Sisa {{ app.draft_remaining_minutes !== undefined ? app.draft_remaining_minutes : draftTimeoutMinutes }} mnt)</span>
                  </div>
                  <StatusBadge v-else :status="app.status" />
                </td>

                <!-- Status Fisik KTP-el di Loket Serah Terima -->
                <td class="px-5 py-3.5 whitespace-nowrap">
                  <div v-if="app.status === 'DRAFT'">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                      Masa Pengisian Berkas
                    </span>
                  </div>
                  <div v-else-if="app.handover">
                    <span
                      v-if="app.handover.status === 'SUDAH_DISERAHKAN'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                      Sudah Diserahkan
                    </span>
                    <span
                      v-else-if="app.handover.status === 'SIAP_DIAMBIL'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      Siap Diambil di Loket
                    </span>
                    <span
                      v-else-if="app.handover.status === 'MENUNGGU_KETERSEDIAAN'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      Cek Fisik Kartu
                    </span>
                    <span
                      v-else-if="app.handover.status === 'KENDALA_PENGAMBILAN'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                      Ada Kendala
                    </span>
                  </div>
                  <div v-else>
                    <span
                      v-if="['READY_FOR_PICKUP'].includes(app.status)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700"
                    >
                      Siap Diambil
                    </span>
                    <span
                      v-else-if="['APPROVED_FOR_SERVICE', 'PROCESSING'].includes(app.status)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-700"
                    >
                      Proses Cetak / Rekam
                    </span>
                    <span
                      v-else-if="['REJECTED', 'EXPIRED', 'CANCELLED'].includes(app.status)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500"
                    >
                      Tidak Aktif
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300"
                    >
                      Tahap Verifikasi
                    </span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <!-- Direct Serah Terima Action if Handover item exists -->
                    <Link
                      v-if="app.handover?.id"
                      :href="'/admin/handover/' + app.handover.id"
                      class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition"
                      title="Buka Halaman Serah Terima KTP-el"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                      <span>Serah Terima</span>
                    </Link>

                    <!-- View Application Details -->
                    <Link
                      :href="'/admin/queue/applications/' + app.id"
                      class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs transition"
                    >
                      <span>Detail Berkas</span>
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="!applications.data || applications.data.length === 0">
                <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                  <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                  </div>
                  <p class="font-bold text-slate-600 dark:text-slate-300">Tidak ada pemohon terdaftar ditemukan</p>
                  <p class="text-xs text-slate-400 mt-1">Sesuaikan filter atau kata kunci pencarian Anda.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <div
          v-if="applications.links && applications.links.length > 3"
          class="px-5 py-4 border-t border-slate-100 dark:border-slate-700/60 flex flex-col sm:flex-row items-center justify-between gap-3"
        >
          <div class="text-xs text-slate-500 text-center sm:text-left">
            Menampilkan {{ applications.from || 0 }} - {{ applications.to || 0 }} dari total {{ applications.total }} pemohon terdaftar
          </div>
          <div class="w-full sm:w-auto flex items-center justify-start sm:justify-end gap-1.5 overflow-x-auto max-w-full pb-1 sm:pb-0 scrollbar-thin select-none touch-pan-x">
            <template v-for="(link, i) in applications.links" :key="i">
              <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                preserve-state
                :class="[
                  'min-w-[36px] h-9 px-2.5 rounded-lg text-xs font-bold transition flex items-center justify-center shrink-0 cursor-pointer active:scale-95 touch-manipulation',
                  link.active
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200'
                ]"
                v-html="link.label"
              />
              <span
                v-else
                class="min-w-[36px] h-9 px-2.5 rounded-lg text-xs font-medium text-slate-400 cursor-not-allowed opacity-50 flex items-center justify-center shrink-0 select-none"
                v-html="link.label"
              />
            </template>
          </div>
        </div>
      </div>

      <!-- Lightbox Photo Preview Modal -->
      <transition name="fade">
        <div
          v-if="previewPhotoApp"
          class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
          @click.self="closePhotoPreview"
        >
          <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden relative animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/90">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shadow-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                  <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">
                    Preview Foto Pemohon
                  </h3>
                  <p class="text-xs text-slate-400 font-mono mt-0.5">
                    {{ previewPhotoApp.reg_number }}
                    <span v-if="previewPhotoApp.queue_number" class="ml-1 px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 font-black">
                      No. Antrean: {{ previewPhotoApp.queue_number }}
                    </span>
                  </p>
                </div>
              </div>

              <button
                type="button"
                @click="closePhotoPreview"
                class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center justify-center transition cursor-pointer"
                title="Tutup (Esc)"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>

            <!-- Image Container -->
            <div class="p-6 bg-slate-950 flex items-center justify-center relative min-h-[260px] max-h-[58vh]">
              <img
                :src="previewPhotoApp.selfie_url || previewPhotoApp.selfie_thumbnail_url"
                :alt="previewPhotoApp.applicant_name"
                class="max-w-full max-h-[52vh] object-contain rounded-2xl shadow-2xl border border-white/10"
              />
            </div>

            <!-- Detail Information -->
            <div class="p-5 bg-white dark:bg-slate-800 space-y-3.5">
              <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                  <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block">Nama Lengkap</span>
                  <span class="font-bold text-slate-900 dark:text-white text-sm block truncate">{{ previewPhotoApp.applicant_name }}</span>
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block">NIK Pemilik</span>
                  <span class="font-mono font-bold text-slate-900 dark:text-white block">{{ previewPhotoApp.applicant_nik }}</span>
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block">Jenis Layanan</span>
                  <span class="font-semibold text-blue-600 dark:text-blue-400 block truncate">{{ previewPhotoApp.service_type?.name }}</span>
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block">Status Berkas</span>
                  <StatusBadge :status="previewPhotoApp.status" class="mt-0.5 inline-block" />
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button
                  type="button"
                  @click="closePhotoPreview"
                  class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold transition cursor-pointer"
                >
                  Tutup
                </button>
                <Link
                  v-if="previewPhotoApp.handover?.id"
                  :href="'/admin/handover/' + previewPhotoApp.handover.id"
                  class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center gap-1.5 transition cursor-pointer"
                >
                  <span>Proses Serah Terima KTP</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </Link>
                <Link
                  v-else
                  :href="'/admin/queue/applications/' + previewPhotoApp.id"
                  class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 flex items-center gap-1.5 transition cursor-pointer"
                >
                  <span>Lihat Detail Berkas</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/Queue/StatusBadge.vue';

const props = defineProps({
  applications: {
    type: Object,
    required: true,
  },
  services: {
    type: Array,
    default: () => [],
  },
  counts: {
    type: Object,
    default: () => ({}),
  },
  draftTimeoutMinutes: {
    type: Number,
    default: 15,
  },
  filters: {
    type: Object,
    default: () => ({ search: '', service_id: '', status: '' }),
  },
});

const searchQuery = ref(props.filters.search || '');
const selectedService = ref(props.filters.service_id || '');
const activeStatus = ref(props.filters.status || '');

const previewPhotoApp = ref(null);

const filterTabs = computed(() => [
  { label: 'Semua Registrasi', value: '', count: props.counts?.total || 0 },
  { label: `Draf (Masa Tunggu ≤${props.draftTimeoutMinutes || 15} mnt)`, value: 'DRAFT', count: props.counts?.draft || 0 },
  { label: 'Menunggu Verifikasi', value: 'MENUNGGU_VERIFIKASI', count: props.counts?.menunggu_verifikasi || 0 },
  { label: 'Lolos Verifikasi / Antrean', value: 'LOLOS_VERIFIKASI', count: props.counts?.lolos_verifikasi || 0 },
  { label: 'Menunggu Fisik KTP', value: 'MENUNGGU_KETERSEDIAAN', count: props.counts?.menunggu_ketersediaan || 0 },
  { label: 'Siap Diambil di Loket', value: 'SIAP_DIAMBIL', count: props.counts?.siap_diambil || 0 },
  { label: 'Sudah Diserahkan', value: 'SUDAH_DISERAHKAN', count: props.counts?.sudah_diserahkan || 0 },
  { label: 'Kendala', value: 'KENDALA', count: props.counts?.kendala || 0 },
]);

const openPhotoPreview = (app) => {
  previewPhotoApp.value = app;
};

const closePhotoPreview = () => {
  previewPhotoApp.value = null;
};

const selectStatus = (statusVal) => {
  activeStatus.value = statusVal;
  applyFilters();
};

const applyFilters = () => {
  router.get(
    '/admin/handover/registrations',
    {
      search: searchQuery.value || undefined,
      service_id: selectedService.value || undefined,
      status: activeStatus.value || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedService.value = '';
  activeStatus.value = '';
  router.get('/admin/handover/registrations', {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const maskNik = (nik) => {
  if (!nik || nik.length < 16) return nik || '-';
  return nik.substring(0, 6) + '******' + nik.substring(12);
};

const formatPhoneChunked = (phone) => {
  if (!phone) return '-';
  const clean = phone.replace(/[^0-9]/g, '');
  if (clean.length === 12) {
    return `${clean.substring(0, 4)}-${clean.substring(4, 8)}-${clean.substring(8)}`;
  } else if (clean.length === 11) {
    return `${clean.substring(0, 4)}-${clean.substring(4, 7)}-${clean.substring(7)}`;
  } else if (clean.length === 13) {
    return `${clean.substring(0, 4)}-${clean.substring(4, 8)}-${clean.substring(8)}`;
  }
  return phone;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }) + ' WITA';
};
</script>
