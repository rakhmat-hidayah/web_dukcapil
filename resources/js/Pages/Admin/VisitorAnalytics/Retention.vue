<template>
  <Head title="Retensi & Pembersihan Data Log Pengunjung - Dukcapil Dompu" />

  <AdminLayout>
    <div class="space-y-6 text-left pb-12">
      <!-- Header Banner -->
      <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-slate-900 via-rose-950 to-red-900 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <span class="px-2.5 py-0.5 rounded-full bg-red-500/20 text-red-300 border border-red-400/30 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                <DatabaseZap class="w-3 h-3" />
                Manajemen Retensi Data
              </span>
              <span class="text-xs text-red-200/60 font-mono">| Mesin Pembersihan Otomatis v2</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Retensi &amp; Pembersihan Data Log Pengunjung
            </h1>
            <p class="text-xs text-red-100/70 mt-1 max-w-2xl leading-relaxed">
              Kelola kebijakan retensi data log pengunjung secara otomatis. Proses penghapusan berjalan bertahap (batch/chunk) di background tanpa mengganggu website.
            </p>
          </div>

          <div class="flex items-center gap-3 shrink-0">
            <Link :href="route('admin.visitor-analytics.index')" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white text-xs font-bold rounded-2xl transition flex items-center gap-2">
              <BarChart2 class="w-4 h-4" />
              Analisis Pengunjung
            </Link>
          </div>
        </div>
      </div>

      <!-- Storage Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Log Visitor</span>
            <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-500 rounded-xl">
              <Activity class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-black text-gray-800 dark:text-zinc-100">{{ storageInfo.total_logs }}</p>
          <p class="text-[10px] text-gray-400 font-medium">Total record di tabel visitor_logs</p>
        </div>

        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Ukuran Database</span>
            <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-500 rounded-xl">
              <HardDrive class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-black text-gray-800 dark:text-zinc-100">{{ storageInfo.table_size_mb }} <span class="text-sm font-semibold text-gray-400">MB</span></p>
          <p class="text-[10px] text-gray-400 font-medium">DATA + INDEX visitor_logs (MySQL)</p>
        </div>

        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Eligible Cleanup</span>
            <div class="p-2 rounded-xl" :class="parseInt(storageInfo.eligible_for_cleanup?.replace(/,/g,'')) > 0 ? 'bg-red-50 dark:bg-red-950/40 text-red-500' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500'">
              <Trash2 class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-black" :class="parseInt(storageInfo.eligible_for_cleanup?.replace(/,/g,'')) > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'">
            {{ storageInfo.eligible_for_cleanup }}
          </p>
          <p class="text-[10px] text-gray-400 font-medium">Log melewati masa retensi {{ storageInfo.retention_days }} hari</p>
        </div>

        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Next Cleanup</span>
            <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 rounded-xl">
              <Clock class="w-4 h-4" />
            </div>
          </div>
          <p class="text-sm font-black text-gray-800 dark:text-zinc-100 leading-tight">{{ storageInfo.next_cleanup }}</p>
          <p class="text-[10px] text-gray-400 font-medium">Jadwal cleanup otomatis berikutnya</p>
        </div>
      </div>

      <!-- Last Cleanup Info Bar -->
      <div v-if="storageInfo.last_cleanup_at" class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 rounded-3xl flex items-center gap-3 text-emerald-800 dark:text-emerald-300 text-xs">
        <CheckCircle class="w-5 h-5 shrink-0 text-emerald-500" />
        <div>
          <span class="font-bold">Cleanup Terakhir:</span>
          {{ storageInfo.last_cleanup_at }} —
          <span class="font-black">{{ storageInfo.last_cleanup_deleted }} record</span> berhasil dihapus.
          | Arsip: <span class="font-bold">{{ storageInfo.archive_status }}</span>
        </div>
      </div>

      <!-- Cleanup Running Banner -->
      <div v-if="cleanupRunning || isPolling" class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 rounded-3xl flex items-center gap-3 text-amber-800 dark:text-amber-300 text-xs">
        <RotateCw class="w-5 h-5 shrink-0 animate-spin text-amber-600" />
        <div>
          <span class="font-bold">Proses Cleanup Sedang Berjalan</span> — Penghapusan data dilakukan secara bertahap (batch) di background. Halaman ini akan diperbarui secara otomatis setelah selesai.
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT: Settings Form -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
          <div class="p-5 border-b border-gray-100 dark:border-zinc-800 flex items-center gap-2.5">
            <div class="p-2 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-xl">
              <Sliders class="w-5 h-5" />
            </div>
            <div>
              <h2 class="font-black text-sm text-gray-800 dark:text-zinc-100">Konfigurasi Analisis Pengunjung</h2>
              <p class="text-[10px] text-gray-400">Pengaturan pencatatan, retensi, dan jadwal pembersihan otomatis</p>
            </div>
          </div>

          <form @submit.prevent="saveSettings" class="p-6 space-y-6">
            <!-- Enable Visitor Logging -->
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/60 rounded-2xl border border-gray-100 dark:border-zinc-800">
              <div>
                <p class="text-sm font-black text-gray-800 dark:text-zinc-100">Pencatatan Log Pengunjung</p>
                <p class="text-[11px] text-gray-500 mt-0.5">Aktif/nonaktifkan pencatatan seluruh kunjungan pengunjung website.</p>
              </div>
              <button type="button" @click="form.visitor_logging_enabled = form.visitor_logging_enabled === '1' ? '0' : '1'" class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200" :class="form.visitor_logging_enabled === '1' ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-zinc-600'">
                <span class="inline-block h-6 w-6 rounded-full bg-white shadow-lg transform transition-transform duration-200" :class="form.visitor_logging_enabled === '1' ? 'translate-x-5' : 'translate-x-0'"></span>
              </button>
            </div>

            <!-- Enable Auto Cleanup -->
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/60 rounded-2xl border border-gray-100 dark:border-zinc-800">
              <div>
                <p class="text-sm font-black text-gray-800 dark:text-zinc-100">Pembersihan Otomatis</p>
                <p class="text-[11px] text-gray-500 mt-0.5">Aktifkan penghapusan otomatis log yang melewati masa retensi via Laravel Scheduler.</p>
              </div>
              <button type="button" @click="form.visitor_auto_cleanup_enabled = form.visitor_auto_cleanup_enabled === '1' ? '0' : '1'" class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200" :class="form.visitor_auto_cleanup_enabled === '1' ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-zinc-600'">
                <span class="inline-block h-6 w-6 rounded-full bg-white shadow-lg transform transition-transform duration-200" :class="form.visitor_auto_cleanup_enabled === '1' ? 'translate-x-5' : 'translate-x-0'"></span>
              </button>
            </div>

            <!-- Retention Period -->
            <div class="space-y-2">
              <label class="text-xs font-black text-gray-700 dark:text-zinc-200 uppercase tracking-wider">Masa Simpan Log (Retensi)</label>
              <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                <button v-for="day in [30, 60, 90, 180, 365]" :key="day" type="button" @click="form.visitor_retention_days = String(day)" class="py-2.5 text-xs font-black rounded-2xl border-2 transition" :class="form.visitor_retention_days === String(day) ? 'bg-primary-600 border-primary-600 text-white shadow-md' : 'bg-white dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:border-primary-400'">
                  {{ day }} Hari
                </button>
              </div>
              <p class="text-[10px] text-gray-400">Log yang lebih lama dari <span class="font-bold text-primary-600">{{ form.visitor_retention_days }} hari</span> akan dihapus otomatis.</p>
            </div>

            <!-- Cleanup Schedule -->
            <div class="space-y-2">
              <label class="text-xs font-black text-gray-700 dark:text-zinc-200 uppercase tracking-wider">Cleanup Schedule (Frekuensi Jadwal)</label>
              <div class="grid grid-cols-3 gap-2">
                <button v-for="s in [{ val: 'daily', label: 'Daily' }, { val: 'weekly', label: 'Weekly' }, { val: 'monthly', label: 'Monthly' }]" :key="s.val" type="button" @click="form.visitor_cleanup_schedule = s.val" class="py-2.5 text-xs font-black rounded-2xl border-2 transition" :class="form.visitor_cleanup_schedule === s.val ? 'bg-indigo-600 border-indigo-600 text-white shadow-md' : 'bg-white dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:border-indigo-400'">
                  {{ s.label }}
                </button>
              </div>
            </div>

            <!-- Cleanup Time -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-700 dark:text-zinc-200 uppercase tracking-wider">Cleanup Time (WIB)</label>
                <select v-model="form.visitor_cleanup_time" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none">
                  <option value="00:00">00:00 WIB (Tengah Malam)</option>
                  <option value="01:00">01:00 WIB</option>
                  <option value="02:00">02:00 WIB (Default)</option>
                  <option value="03:00">03:00 WIB</option>
                  <option value="04:00">04:00 WIB</option>
                  <option value="05:00">05:00 WIB (Subuh)</option>
                </select>
              </div>

              <div class="space-y-2">
                <label class="text-xs font-black text-gray-700 dark:text-zinc-200 uppercase tracking-wider">Chunk Size (Record per Batch)</label>
                <select v-model="form.visitor_cleanup_chunk_size" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none">
                  <option value="500">500 record / batch</option>
                  <option value="1000">1.000 record / batch (Default)</option>
                  <option value="2000">2.000 record / batch</option>
                  <option value="5000">5.000 record / batch (Powerful Server)</option>
                </select>
              </div>
            </div>

            <!-- Archive Before Delete -->
            <div class="flex items-center justify-between p-4 bg-amber-50/60 dark:bg-amber-950/20 rounded-2xl border border-amber-200 dark:border-amber-900/30">
              <div>
                <p class="text-sm font-black text-gray-800 dark:text-zinc-100 flex items-center gap-1.5">
                  <Archive class="w-4 h-4 text-amber-600" /> Archive Before Delete
                </p>
                <p class="text-[11px] text-gray-500 mt-0.5">Jika ON, log dipindahkan ke tabel <code class="bg-amber-100 dark:bg-amber-900/50 px-1 rounded text-amber-700 dark:text-amber-300">visitor_logs_archive</code> sebelum dihapus. Membutuhkan storage ekstra.</p>
              </div>
              <button type="button" @click="form.visitor_archive_before_delete = form.visitor_archive_before_delete === '1' ? '0' : '1'" class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ml-4" :class="form.visitor_archive_before_delete === '1' ? 'bg-amber-500' : 'bg-gray-300 dark:bg-zinc-600'">
                <span class="inline-block h-6 w-6 rounded-full bg-white shadow-lg transform transition-transform duration-200" :class="form.visitor_archive_before_delete === '1' ? 'translate-x-5' : 'translate-x-0'"></span>
              </button>
            </div>

            <!-- Save Button -->
            <div class="pt-2">
              <button type="submit" :disabled="saving" class="w-full py-3 bg-primary-600 hover:bg-primary-500 disabled:opacity-60 text-white text-sm font-black rounded-2xl shadow-lg shadow-primary-600/30 transition flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer">
                <RotateCw v-if="saving" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4" />
                {{ saving ? 'Menyimpan...' : 'Simpan Pengaturan Retention' }}
              </button>
            </div>
          </form>
        </div>

        <!-- RIGHT: Manual Cleanup + Safety Info -->
        <div class="space-y-4">
          <!-- Manual Cleanup Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-zinc-800 flex items-center gap-2.5">
              <div class="p-2 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-xl">
                <Zap class="w-5 h-5" />
              </div>
              <div>
                <h2 class="font-black text-sm text-gray-800 dark:text-zinc-100">Manual Cleanup Now</h2>
                <p class="text-[10px] text-gray-400">Eksekusi cleanup langsung sekarang</p>
              </div>
            </div>

            <div class="p-5 space-y-4">
              <!-- Preview Stats -->
              <div class="p-4 bg-red-50/60 dark:bg-red-950/20 rounded-2xl border border-red-200 dark:border-red-900/30 text-xs space-y-1.5">
                <div class="flex justify-between">
                  <span class="text-gray-500 font-bold">Records yang akan dihapus:</span>
                  <span class="font-black text-red-600 dark:text-red-400">{{ storageInfo.eligible_for_cleanup }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500 font-bold">Masa retensi aktif:</span>
                  <span class="font-black text-gray-800 dark:text-zinc-100">{{ storageInfo.retention_days }} hari</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500 font-bold">Chunk size:</span>
                  <span class="font-black text-gray-800 dark:text-zinc-100">{{ form.visitor_cleanup_chunk_size }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500 font-bold">Archive terlebih dahulu:</span>
                  <span class="font-black" :class="form.visitor_archive_before_delete === '1' ? 'text-amber-600' : 'text-gray-400'">
                    {{ form.visitor_archive_before_delete === '1' ? 'Ya (AKTIF)' : 'Tidak' }}
                  </span>
                </div>
              </div>

              <p class="text-[10px] text-gray-500 leading-relaxed bg-gray-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-gray-100 dark:border-zinc-800">
                ⚠️ Data yang sudah dihapus <strong>tidak dapat dikembalikan</strong> (kecuali Archive aktif). Proses berjalan secara batch di background dan tidak mengganggu website.
              </p>

              <button
                @click="openConfirmModal"
                :disabled="cleanupRunning || isPolling"
                class="w-full py-3 bg-red-600 hover:bg-red-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-black rounded-2xl shadow-lg shadow-red-600/30 transition flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer"
              >
                <Trash2 class="w-4 h-4" />
                {{ cleanupRunning || isPolling ? 'Cleanup Sedang Berjalan...' : 'Cleanup Now' }}
              </button>
            </div>
          </div>

          <!-- System Safety Info -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm p-5 space-y-3">
            <h3 class="font-black text-xs uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
              <ShieldCheck class="w-4 h-4 text-emerald-500" /> Arsitektur Keamanan Sistem
            </h3>
            <ul class="space-y-2 text-[11px] text-gray-600 dark:text-zinc-300">
              <li class="flex items-start gap-2">
                <CheckCircle class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
                <span>Chunk processing <strong>{{ form.visitor_cleanup_chunk_size }} record/batch</strong> — tidak ada DELETE massal</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckCircle class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
                <span>Statistik harian dipreservasi ke <code class="bg-gray-100 dark:bg-zinc-700 px-1 rounded">visitor_daily_summary</code> sebelum data dihapus</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckCircle class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
                <span>Laravel Scheduler dengan <code class="bg-gray-100 dark:bg-zinc-700 px-1 rounded">withoutOverlapping(30)</code> — tidak pernah concurrent</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckCircle class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
                <span>Berjalan di background — pengunjung website tidak terpengaruh</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckCircle class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
                <span>Setiap proses tercatat di <code class="bg-gray-100 dark:bg-zinc-700 px-1 rounded">visitor_cleanup_logs</code> (audit trail)</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Cleanup History Table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-zinc-800 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400 rounded-xl">
              <ClipboardList class="w-5 h-5" />
            </div>
            <div>
              <h2 class="font-black text-sm text-gray-800 dark:text-zinc-100">Riwayat Cleanup</h2>
              <p class="text-[10px] text-gray-400">Audit trail seluruh proses visitor log cleanup</p>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50/80 dark:bg-zinc-800/80 border-b border-gray-100 dark:border-zinc-800 text-[10px] font-black uppercase tracking-wider text-gray-400">
                <th class="py-3.5 px-4">ID</th>
                <th class="py-3.5 px-4">Dimulai</th>
                <th class="py-3.5 px-4">Selesai</th>
                <th class="py-3.5 px-4 text-right">Deleted</th>
                <th class="py-3.5 px-4 text-right">Archived</th>
                <th class="py-3.5 px-4 text-right">Durasi</th>
                <th class="py-3.5 px-4">Retensi</th>
                <th class="py-3.5 px-4">Trigger</th>
                <th class="py-3.5 px-4">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="cleanupHistory.length === 0">
                <td colspan="9" class="py-12 text-center text-gray-400">
                  <div class="flex flex-col items-center gap-2">
                    <ClipboardList class="w-8 h-8 text-gray-300" />
                    <p class="font-bold text-xs">Belum ada riwayat cleanup.</p>
                  </div>
                </td>
              </tr>

              <tr v-for="log in cleanupHistory" :key="log.id" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/50 transition">
                <td class="py-3.5 px-4 font-mono text-[11px] text-gray-400">#{{ log.id }}</td>
                <td class="py-3.5 px-4 text-xs font-bold">{{ log.started_at ?? '-' }}</td>
                <td class="py-3.5 px-4 text-xs">{{ log.finished_at ?? '-' }}</td>
                <td class="py-3.5 px-4 text-right">
                  <span class="font-black text-red-600 dark:text-red-400">{{ log.deleted_records }}</span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <span class="font-bold text-amber-600 dark:text-amber-400">{{ log.archived_records }}</span>
                </td>
                <td class="py-3.5 px-4 text-right font-mono text-xs text-gray-500">
                  {{ log.duration_seconds != null ? log.duration_seconds + 's' : '-' }}
                </td>
                <td class="py-3.5 px-4 font-bold text-gray-600 dark:text-zinc-300">{{ log.retention_days ? log.retention_days + ' hari' : '-' }}</td>
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded-lg text-[10px] font-black capitalize"
                    :class="log.triggered_by === 'manual' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-400'">
                    {{ log.triggered_by }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-1 rounded-xl text-[10px] font-black capitalize inline-flex items-center gap-1"
                    :class="{
                      'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300': log.status === 'completed',
                      'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300': log.status === 'running',
                      'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300': log.status === 'failed',
                      'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-400': log.status === 'cancelled',
                    }"
                  >
                    <CheckCircle v-if="log.status === 'completed'" class="w-3 h-3" />
                    <RotateCw v-else-if="log.status === 'running'" class="w-3 h-3 animate-spin" />
                    <AlertTriangle v-else-if="log.status === 'failed'" class="w-3 h-3" />
                    {{ log.status }}
                  </span>
                  <p v-if="log.error_message" class="text-[9px] text-red-500 mt-1 truncate max-w-[200px]" :title="log.error_message">
                    {{ log.error_message }}
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- CONFIRM CLEANUP MODAL -->
    <transition name="fade">
      <div v-if="confirmModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
          <div class="p-5 bg-gradient-to-r from-red-700 to-rose-800 text-white flex items-center gap-2.5">
            <div class="p-2 bg-white/15 text-white rounded-xl">
              <AlertTriangle class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm font-black">Konfirmasi Manual Cleanup</h3>
              <p class="text-[10px] text-red-200/80">Tindakan ini tidak dapat dibatalkan</p>
            </div>
          </div>

          <div class="p-6 space-y-4">
            <p class="text-sm text-gray-700 dark:text-zinc-200 font-bold leading-relaxed">
              Apakah Anda yakin ingin menghapus seluruh log yang lebih lama dari
              <span class="text-red-600 font-black">{{ storageInfo.retention_days }} hari</span>?
            </p>

            <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 rounded-2xl text-xs space-y-1 text-red-800 dark:text-red-300">
              <p class="font-black">⚠️ Peringatan:</p>
              <p>• Estimasi record yang dihapus: <strong>{{ storageInfo.eligible_for_cleanup }}</strong></p>
              <p>• Proses berjalan batch <strong>{{ form.visitor_cleanup_chunk_size }} record</strong> per iterasi</p>
              <p v-if="form.visitor_archive_before_delete === '0'">• Archive TIDAK aktif — data yang dihapus <strong>permanen dan tidak dapat dikembalikan</strong></p>
              <p v-else>• Data akan diarsipkan ke <code>visitor_logs_archive</code> sebelum dihapus</p>
            </div>

            <div class="flex gap-3 pt-2">
              <button @click="confirmModalOpen = false" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-800 dark:text-zinc-100 text-xs font-black rounded-2xl transition cursor-pointer">
                Batal
              </button>
              <button @click="executeCleanup" :disabled="startingCleanup" class="flex-1 py-2.5 bg-red-600 hover:bg-red-500 disabled:opacity-60 text-white text-xs font-black rounded-2xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                <RotateCw v-if="startingCleanup" class="w-3.5 h-3.5 animate-spin" />
                <Trash2 v-else class="w-3.5 h-3.5" />
                {{ startingCleanup ? 'Memulai...' : 'Ya, Hapus Sekarang' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import {
  DatabaseZap, Activity, HardDrive, Trash2, Clock, CheckCircle, RotateCw,
  Sliders, Save, Zap, ShieldCheck, ClipboardList, AlertTriangle, Archive, BarChart2
} from '@lucide/vue';

const props = defineProps({
  settings: Object,
  storageInfo: Object,
  cleanupHistory: Array,
  cleanupRunning: Boolean,
});

const confirmModalOpen = ref(false);
const saving = ref(false);
const startingCleanup = ref(false);
const isPolling = ref(props.cleanupRunning);
let pollTimer = null;

const form = ref({
  visitor_logging_enabled:      props.settings?.visitor_logging_enabled ?? '1',
  visitor_auto_cleanup_enabled: props.settings?.visitor_auto_cleanup_enabled ?? '1',
  visitor_retention_days:       props.settings?.visitor_retention_days ?? '90',
  visitor_cleanup_schedule:     props.settings?.visitor_cleanup_schedule ?? 'daily',
  visitor_cleanup_time:         props.settings?.visitor_cleanup_time ?? '02:00',
  visitor_archive_before_delete: props.settings?.visitor_archive_before_delete ?? '0',
  visitor_cleanup_chunk_size:   props.settings?.visitor_cleanup_chunk_size ?? '1000',
});

const saveSettings = () => {
  saving.value = true;
  router.post(route('admin.visitor-analytics-retention.settings'), form.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    }
  });
};

const openConfirmModal = () => {
  confirmModalOpen.value = true;
};

const executeCleanup = async () => {
  startingCleanup.value = true;
  try {
    const res = await axios.post(route('admin.visitor-analytics-retention.cleanup-now'));
    if (res.data.success) {
      confirmModalOpen.value = false;
      isPolling.value = true;
      startPolling();
    } else {
      alert(res.data.message);
    }
  } catch (err) {
    alert(err.response?.data?.message ?? 'Gagal memulai cleanup.');
  } finally {
    startingCleanup.value = false;
  }
};

const startPolling = () => {
  pollTimer = setInterval(async () => {
    try {
      const res = await axios.get(route('admin.visitor-analytics-retention.cleanup-status'));
      if (!res.data.running) {
        isPolling.value = false;
        clearInterval(pollTimer);
        // Reload to refresh stats and history
        router.reload({ preserveScroll: true });
      }
    } catch {
      clearInterval(pollTimer);
      isPolling.value = false;
    }
  }, 3000);
};

onMounted(() => {
  if (props.cleanupRunning) {
    startPolling();
  }
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
