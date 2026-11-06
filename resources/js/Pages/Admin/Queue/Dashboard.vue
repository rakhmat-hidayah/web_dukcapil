<template>
  <Head title="Executive Dashboard Antrian Online KTP-el - Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Top Title & Quick Actions Bar -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">
              Executive Dashboard Antrian KTP-el
            </h1>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
              100% Real-Time Data
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Data operasional pelayanan kependudukan Disdukcapil Kabupaten Dompu per {{ todayDate }}.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <Link
            :href="route('admin.queue.applications.index', { status: 'SUBMITTED' })"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2"
          >
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            Verifikasi Berkas Masuk ({{ stats.waiting_review }})
          </Link>
        </div>
      </div>

      <!-- Filter Controls Bar -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
          <div>
            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Mulai Tanggal</label>
            <input
              v-model="filterForm.start_date"
              type="date"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Sampai Tanggal</label>
            <input
              v-model="filterForm.end_date"
              type="date"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Layanan</label>
            <select
              v-model="filterForm.service_id"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
            >
              <option value="">Semua Layanan</option>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kehadiran</label>
            <select
              v-model="filterForm.attendance_type"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
            >
              <option value="">Semua Kehadiran</option>
              <option value="self">Datang Sendiri</option>
              <option value="represented">Diwakilkan / Kuasa</option>
            </select>
          </div>

          <div class="flex items-end gap-2">
            <button
              type="submit"
              class="flex-1 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition shadow-sm cursor-pointer"
            >
              Terapkan Filter
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold transition cursor-pointer"
              title="Reset Filter"
            >
              ↺
            </button>
          </div>
        </form>
      </div>

      <!-- Top Metric Cards (5 KPI Cards) -->
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Total Pengajuan -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Pengajuan</span>
          <p class="text-3xl font-black text-slate-900 dark:text-white mt-2 font-mono">
            {{ stats.total_applications }}
          </p>
          <p class="text-[11px] text-slate-400 mt-1">
            {{ stats.today_applications_count }} terdaftar hari ini
          </p>
        </div>

        <!-- Menunggu Verifikasi Online -->
        <Link
          :href="route('admin.queue.applications.index', { status: 'SUBMITTED' })"
          class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-blue-200 dark:border-blue-900/60 shadow-sm hover:shadow-md transition"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Pra-Verifikasi Online</span>
            <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-slate-900 dark:text-white mt-2 font-mono">
            {{ stats.waiting_review + stats.under_review }}
          </p>
          <p class="text-[11px] text-slate-400 mt-1">
            {{ stats.waiting_review }} baru · {{ stats.under_review }} sedang dicek
          </p>
        </Link>

        <!-- Kuota & Antrean Hari Ini -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Antrean Hari Ini</span>
            <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-purple-600 dark:text-purple-400 mt-2 font-mono">
            {{ stats.today_booked }}
            <span class="text-xs font-normal text-slate-400">/ {{ stats.today_quota }} Kuota</span>
          </p>
          <p class="text-[11px] text-slate-400 mt-1">
            Sisa {{ stats.today_remaining }} slot hari ini
          </p>
        </div>

        <!-- Tingkat Kehadiran Loket -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider">Check-In Loket</span>
            <div class="w-7 h-7 rounded-lg bg-teal-50 dark:bg-teal-950/40 text-teal-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
          <p class="text-3xl font-black text-teal-600 dark:text-teal-400 mt-2 font-mono">
            {{ stats.attendance_rate }}%
          </p>
          <p class="text-[11px] text-slate-400 mt-1">
            {{ stats.today_checked_in }} dari {{ stats.today_booked }} antrean telah hadir
          </p>
        </div>

        <!-- KPI: Total Pencetakan KTP-el -->
        <Link
          :href="route('admin.queue.applications.index', { status: 'READY_FOR_PICKUP' })"
          class="bg-gradient-to-br from-indigo-50/50 via-white to-emerald-50/40 dark:from-slate-800 dark:to-slate-800 p-5 rounded-3xl border border-indigo-200 dark:border-indigo-800/60 shadow-sm hover:shadow-md transition col-span-2 sm:col-span-1"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
              <span>Total Cetak KTP-el</span>
              <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300">KPI</span>
            </span>
            <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-black text-indigo-900 dark:text-indigo-200 mt-2 font-mono">
            {{ stats.total_printed }}
            <span class="text-xs font-normal text-slate-400">kartu</span>
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
            <span>{{ stats.today_printed }} dicetak hari ini</span>
            <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ stats.print_rate }}%</span>
          </p>
        </Link>
      </div>

      <!-- Representation Breakdown & Lifecycle Pipeline -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Representation Analysis -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
            Analisis Kehadiran Pemohon
          </h3>
          <p class="text-xs text-slate-400">
            Perbandingan pemohon yang datang sendiri vs dikuasakan kepada keluarga/pihak lain.
          </p>

          <div class="space-y-4 pt-2">
            <!-- Datang Sendiri -->
            <div>
              <div class="flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                <span>👤 Datang Sendiri</span>
                <span class="font-mono">{{ stats.self_count }} ({{ getPercent(stats.self_count, stats.total_applications) }}%)</span>
              </div>
              <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="bg-blue-600 h-full rounded-full" :style="{ width: `${getPercent(stats.self_count, stats.total_applications)}%` }"></div>
              </div>
            </div>

            <!-- Diwakilkan -->
            <div>
              <div class="flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                <span>👥 Diwakilkan / Surat Kuasa</span>
                <span class="font-mono">{{ stats.represented_count }} ({{ getPercent(stats.represented_count, stats.total_applications) }}%)</span>
              </div>
              <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="bg-amber-500 h-full rounded-full" :style="{ width: `${getPercent(stats.represented_count, stats.total_applications)}%` }"></div>
              </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
              <strong>Catatan Kepatuhan:</strong> Perekaman KTP-el Baru/Pemula tidak dapat diwakilkan karena memerlukan biometrik langsung.
            </div>
          </div>
        </div>

        <!-- Right 2 cols: Full Lifecycle Pipeline -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
              <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <span>Status Pipeline Pelayanan KTP-el</span>
              </h3>
              <p class="text-xs text-slate-400">
                Alur status berkas pengajuan dari pra-verifikasi hingga pencetakan fisik dan serah terima kartu
              </p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Total Dicetak: <strong class="font-mono">{{ stats.total_printed }}</strong> ({{ stats.print_rate }}%)
              </span>
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Selesai Diserahkan: <strong class="font-mono">{{ stats.completion_rate }}%</strong>
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
            <!-- 1. Pra-Verifikasi Lolos -->
            <Link
              :href="route('admin.queue.applications.index', { status: 'PRE_VERIFIED' })"
              class="p-3.5 rounded-2xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800 hover:shadow-md transition block group"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-cyan-700 dark:text-cyan-300 uppercase block">Pra-Verifikasi Lolos</span>
                <span class="text-[10px] text-cyan-500 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
              </div>
              <p class="text-2xl font-black text-cyan-900 dark:text-cyan-200 font-mono mt-1">{{ stats.pre_verified }}</p>
              <p class="text-[10px] text-cyan-600 dark:text-cyan-400 mt-0.5">Siap ambil jadwal</p>
            </Link>

            <!-- 2. Jadwal Terbit -->
            <Link
              :href="route('admin.queue.applications.index', { status: 'QUEUE_BOOKED' })"
              class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 hover:shadow-md transition block group"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase block">Jadwal Terbit</span>
                <span class="text-[10px] text-blue-500 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
              </div>
              <p class="text-2xl font-black text-blue-900 dark:text-blue-200 font-mono mt-1">{{ stats.queue_booked }}</p>
              <p class="text-[10px] text-blue-600 dark:text-blue-400 mt-0.5">Menunggu hari kedatangan</p>
            </Link>

            <!-- 3. Hadir di Loket -->
            <Link
              :href="route('admin.queue.applications.index', { status: 'ARRIVED' })"
              class="p-3.5 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 hover:shadow-md transition block group"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-teal-700 dark:text-teal-300 uppercase block">Hadir di Loket</span>
                <span class="text-[10px] text-teal-500 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
              </div>
              <p class="text-2xl font-black text-teal-900 dark:text-teal-200 font-mono mt-1">{{ stats.arrived }}</p>
              <p class="text-[10px] text-teal-600 dark:text-teal-400 mt-0.5">Pemeriksaan fisik asli</p>
            </Link>

            <!-- 4. Rekam / Antrean Cetak -->
            <Link
              :href="route('admin.queue.applications.index', { status: 'PROCESSING' })"
              class="p-3.5 rounded-2xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 hover:shadow-md transition block group"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-purple-700 dark:text-purple-300 uppercase block">Rekam / Cetak</span>
                <span class="text-[10px] text-purple-500 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
              </div>
              <p class="text-2xl font-black text-purple-900 dark:text-purple-200 font-mono mt-1">{{ stats.processing }}</p>
              <p class="text-[10px] text-purple-600 dark:text-purple-400 mt-0.5">Disetujui & antre cetak</p>
            </Link>

            <!-- 5. Siap Diambil (KTP Selesai Dicetak) -->
            <Link
              :href="route('admin.queue.applications.index', { status: 'READY_FOR_PICKUP' })"
              class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 hover:shadow-md transition block group ring-1 ring-emerald-500/20"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase block">Siap Diambil</span>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-200 text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200">Tercetak</span>
              </div>
              <p class="text-2xl font-black text-emerald-900 dark:text-emerald-200 font-mono mt-1">{{ stats.ready_pickup }}</p>
              <p class="text-[10px] text-emerald-700 dark:text-emerald-400 mt-0.5">Di meja penyerahan</p>
            </Link>

            <!-- 6. Selesai Diserahkan (Status Akhir) -->
            <Link
              :href="route('admin.queue.applications.index', { status: 'COMPLETED' })"
              class="p-3.5 rounded-2xl bg-green-50 dark:bg-green-950/40 border border-green-300 dark:border-green-700 hover:shadow-md transition block group ring-1 ring-green-500/20"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-green-800 dark:text-green-300 uppercase block">Selesai Diserahkan</span>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-green-200 text-green-900 dark:bg-green-900 dark:text-green-200">Tuntas</span>
              </div>
              <p class="text-2xl font-black text-green-900 dark:text-green-200 font-mono mt-1">{{ stats.completed }}</p>
              <p class="text-[10px] text-green-700 dark:text-green-400 mt-0.5">Layanan tuntas (status akhir)</p>
            </Link>

            <!-- 7. Perlu Perbaikan -->
            <Link
              :href="route('admin.queue.applications.index', { status: 'REVISION_REQUIRED' })"
              class="p-3.5 rounded-2xl bg-orange-50 dark:bg-orange-950/40 border border-orange-200 dark:border-orange-800 hover:shadow-md transition block group"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-orange-700 dark:text-orange-300 uppercase block">Perlu Perbaikan</span>
                <span class="text-[10px] text-orange-500 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
              </div>
              <p class="text-2xl font-black text-orange-900 dark:text-orange-200 font-mono mt-1">{{ stats.revision_required }}</p>
              <p class="text-[10px] text-orange-600 dark:text-orange-400 mt-0.5">Revisi dokumen</p>
            </Link>

            <!-- 8. Ditolak -->
            <Link
              :href="route('admin.queue.applications.index', { status: 'REJECTED' })"
              class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 hover:shadow-md transition block group"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-rose-700 dark:text-rose-300 uppercase block">Ditolak</span>
                <span class="text-[10px] text-rose-500 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
              </div>
              <p class="text-2xl font-black text-rose-900 dark:text-rose-200 font-mono mt-1">{{ stats.rejected }}</p>
              <p class="text-[10px] text-rose-600 dark:text-rose-400 mt-0.5">Tidak memenuhi syarat</p>
            </Link>
          </div>
        </div>
      </div>

      <!-- Services & Today Queue Details -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Today's Live Counter Table (2 Cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white text-base">
                Daftar Antrean Hari Ini
              </h3>
              <p class="text-xs text-slate-400">Pemohon yang memiliki jadwal hadir pada tanggal {{ todayDate }}</p>
            </div>
            <Link
              :href="route('admin.queue.applications.index')"
              class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline"
            >
              Lihat Semua Antrean &rarr;
            </Link>
          </div>

          <div v-if="todayQueue.length === 0" class="py-12 text-center text-slate-400 text-xs">
            Belum ada antrean yang terjadwal untuk hari ini.
          </div>

          <div v-else class="divide-y divide-slate-100 dark:divide-slate-700/60 overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                  <th class="pb-3">No. Antrian</th>
                  <th class="pb-3">Pemohon</th>
                  <th class="pb-3">Kehadiran</th>
                  <th class="pb-3">Layanan</th>
                  <th class="pb-3">Status</th>
                  <th class="pb-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                <tr v-for="app in todayQueue" :key="app.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-750">
                  <td class="py-3 font-mono font-black text-sm text-blue-600 dark:text-blue-400">
                    {{ app.queue_number }}
                  </td>
                  <td class="py-3 font-semibold text-slate-900 dark:text-white">
                    {{ app.applicant_name }}
                    <span v-if="app.applicant_nik" class="block text-[11px] text-slate-400 font-mono">NIK: {{ app.applicant_nik.slice(0, 6) }}******{{ app.applicant_nik.slice(-4) }}</span>
                  </td>
                  <td class="py-3 text-slate-600 dark:text-slate-300">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded text-[10px] font-bold',
                        app.attendance_type === 'self' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'
                      ]"
                    >
                      {{ app.attendance_type === 'self' ? 'Sendiri' : 'Diwakilkan' }}
                    </span>
                  </td>
                  <td class="py-3 text-slate-600 dark:text-slate-300">
                    {{ app.service_type?.name }}
                  </td>
                  <td class="py-3">
                    <StatusBadge :status="app.status" size="sm" />
                  </td>
                  <td class="py-3 text-right">
                    <Link
                      :href="route('admin.queue.applications.show', app.id)"
                      class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold transition"
                    >
                      Detail / Layani
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Right: Service Type Distribution (1 Col) -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base">
              Distribusi Jenis Layanan
            </h3>
            <p class="text-xs text-slate-400">Jumlah pendaftaran berdasarkan jenis permohonan</p>
          </div>

          <div class="space-y-3 pt-2">
            <div
              v-for="s in services"
              :key="s.id"
              class="p-3.5 rounded-2xl border border-slate-100 dark:border-slate-700 flex items-center justify-between"
            >
              <div>
                <p class="font-bold text-xs text-slate-900 dark:text-white">
                  {{ s.name }}
                </p>
                <p v-if="s.is_biometric_first_recording" class="text-[10px] text-amber-600 font-semibold">
                  * Biometrik Fisik Wajib
                </p>
              </div>
              <span class="font-mono font-black text-sm text-slate-900 dark:text-white">
                {{ s.applications_count }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/Queue/StatusBadge.vue';

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
  services: {
    type: Array,
    default: () => [],
  },
  recentPending: {
    type: Array,
    default: () => [],
  },
  todayQueue: {
    type: Array,
    default: () => [],
  },
  todayDate: {
    type: String,
    default: '',
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const filterForm = ref({
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
  service_id: props.filters?.service_id || '',
  attendance_type: props.filters?.attendance_type || '',
});

function applyFilters() {
  router.get(route('admin.queue.dashboard'), filterForm.value, { preserveState: true });
}

function resetFilters() {
  filterForm.value = {
    start_date: '',
    end_date: '',
    service_id: '',
    attendance_type: '',
  };
  router.get(route('admin.queue.dashboard'));
}

function getPercent(val, total) {
  if (!total || total === 0) return 0;
  return Math.round((val / total) * 100);
}

function formatPhoneChunked(phone) {
  if (!phone) return '-';
  let digits = String(phone).replace(/\D/g, '');
  if (digits.startsWith('62')) {
    digits = '0' + digits.slice(2);
  }
  digits = digits.slice(0, 13);
  if (!digits) return String(phone);
  const chunks = digits.match(/.{1,4}/g);
  return chunks ? chunks.join('-') : digits;
}
</script>
