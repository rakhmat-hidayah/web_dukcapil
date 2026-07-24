<template>
  <Head title="Statistik Kependudukan Kabupaten Dompu" />

  <PublicLayout>
    <div class="space-y-8 text-left">
      <!-- Title -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Statistik Kependudukan</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Data kependudukan resmi Kabupaten Dompu yang dipublikasikan oleh Dinas Kependudukan dan Pencatatan Sipil.
        </p>
      </div>

      <!-- Year, Semester & Region filter bar -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-wrap gap-4 items-center">
        <div class="flex items-center gap-3 flex-1 flex-wrap">
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tahun Data</label>
            <select v-model="selectedYear" @change="applyFilters" class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none">
              <option v-for="yr in availableYears" :key="yr" :value="yr">{{ yr }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Semester</label>
            <select v-model="selectedSem" @change="applyFilters" class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none">
              <option v-for="s in availableSemesters" :key="s" :value="s">Semester {{ s }} ({{ s == 1 ? 's.d. Juni' : 's.d. Desember' }})</option>
            </select>
          </div>
          <!-- Region Level -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Level Wilayah</label>
            <select v-model="selectedLevel" @change="onLevelChange" class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none">
              <option value="regency">Kabupaten Dompu</option>
              <option value="district">Kecamatan</option>
            </select>
          </div>
          <!-- Region Code (district) -->
          <div v-if="selectedLevel === 'district'">
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Kecamatan</label>
            <select v-model="selectedCode" @change="applyFilters" class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none">
              <option v-for="kec in kecamatans" :key="kec.code" :value="kec.code">{{ kec.name }}</option>
            </select>
          </div>
        </div>
        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider font-mono">
          Data Tahun {{ selectedYear }} — Sem {{ selectedSem }}<span v-if="selectedLevel === 'district'"> — {{ kecamatans.find(k => k.code === selectedCode)?.name ?? selectedCode }}</span>
        </div>
      </div>

      <!-- Data Belum Tersedia -->
      <div v-if="!dataAvailable || !summary.total_population" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-12 shadow-sm text-center">
        <div class="text-5xl mb-4">📭</div>
        <h3 class="text-lg font-black text-gray-700 dark:text-zinc-200 mb-2">Data Belum Tersedia</h3>
        <p class="text-sm text-gray-400 max-w-md mx-auto">
          Belum ada dataset yang dipublikasikan untuk wilayah dan periode yang dipilih.
          Data akan ditampilkan setelah operator Dukcapil mengunggah dataset resmi.
        </p>
      </div>

      <template v-else>
      <!-- Summary KPI tiles (Premium Gradient & Glassmorphism Cards) -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Total Penduduk -->
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white border border-indigo-500/20 rounded-3xl p-5 shadow-xl shadow-indigo-950/20 flex flex-col justify-between group hover:border-indigo-500/40 transition">
          <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl group-hover:bg-indigo-500/20 transition"></div>
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[9px] font-extrabold uppercase tracking-widest text-indigo-300 font-mono">Total Penduduk</span>
              <span class="px-2 py-0.5 bg-indigo-500/20 text-indigo-300 rounded-full text-[9px] font-bold border border-indigo-500/30">👥 Agregat</span>
            </div>
            <p class="text-3xl font-black tracking-tight mt-3 text-white tabular-nums">{{ formatNumber(summary.total_population) }}</p>
          </div>
          <div class="mt-4 pt-3 border-t border-indigo-500/20 flex items-center justify-between text-[10px] text-indigo-200/80">
            <span>Terdaftar di Dukcapil</span>
            <span class="font-bold text-indigo-300 font-mono">
              Sex Ratio: {{ (summary.total_male && summary.total_female) ? ((summary.total_male / summary.total_female) * 100).toFixed(1) : '-' }}
            </span>
          </div>
        </div>

        <!-- Laki-laki -->
        <div class="relative overflow-hidden bg-white dark:bg-zinc-900 border border-sky-100 dark:border-sky-900/30 rounded-3xl p-5 shadow-lg shadow-sky-500/5 flex flex-col justify-between group hover:border-sky-300 dark:hover:border-sky-700 transition">
          <div class="absolute top-0 right-0 w-24 h-24 bg-sky-500/5 rounded-bl-full group-hover:bg-sky-500/10 transition"></div>
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[9px] font-extrabold uppercase tracking-wider text-sky-600 dark:text-sky-400 font-mono">Laki-laki</span>
              <span class="p-1.5 bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 rounded-xl text-xs">👨</span>
            </div>
            <p class="text-2xl font-black text-sky-700 dark:text-sky-400 tracking-tight mt-3 tabular-nums">{{ formatNumber(summary.total_male) }}</p>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between text-[10px] text-gray-500 dark:text-zinc-400">
            <span>Porsi Populasi</span>
            <span class="font-bold text-sky-600 dark:text-sky-400 font-mono">
              {{ summary.total_population ? ((summary.total_male / summary.total_population) * 100).toFixed(1) + '%' : '0%' }}
            </span>
          </div>
        </div>

        <!-- Perempuan -->
        <div class="relative overflow-hidden bg-white dark:bg-zinc-900 border border-pink-100 dark:border-pink-900/30 rounded-3xl p-5 shadow-lg shadow-pink-500/5 flex flex-col justify-between group hover:border-pink-300 dark:hover:border-pink-700 transition">
          <div class="absolute top-0 right-0 w-24 h-24 bg-pink-500/5 rounded-bl-full group-hover:bg-pink-500/10 transition"></div>
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[9px] font-extrabold uppercase tracking-wider text-pink-600 dark:text-pink-400 font-mono">Perempuan</span>
              <span class="p-1.5 bg-pink-50 dark:bg-pink-950 text-pink-600 dark:text-pink-400 rounded-xl text-xs">👩</span>
            </div>
            <p class="text-2xl font-black text-pink-600 dark:text-pink-400 tracking-tight mt-3 tabular-nums">{{ formatNumber(summary.total_female) }}</p>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between text-[10px] text-gray-500 dark:text-zinc-400">
            <span>Porsi Populasi</span>
            <span class="font-bold text-pink-600 dark:text-pink-400 font-mono">
              {{ summary.total_population ? ((summary.total_female / summary.total_population) * 100).toFixed(1) + '%' : '0%' }}
            </span>
          </div>
        </div>

        <!-- Kecamatan -->
        <div class="relative overflow-hidden bg-white dark:bg-zinc-900 border border-indigo-100 dark:border-indigo-900/30 rounded-3xl p-5 shadow-lg shadow-indigo-500/5 flex flex-col justify-between group hover:border-indigo-300 dark:hover:border-indigo-700 transition">
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[9px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 font-mono">Kecamatan</span>
              <span class="p-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs">🏛️</span>
            </div>
            <p class="text-2xl font-black text-indigo-700 dark:text-indigo-400 tracking-tight mt-3 tabular-nums">{{ summary.total_kecamatan || 8 }}</p>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between text-[10px] text-gray-500 dark:text-zinc-400">
            <span>Cakupan Administrasi</span>
            <span class="font-bold text-indigo-600 dark:text-indigo-400 font-mono">100% Kab</span>
          </div>
        </div>

        <!-- Desa/Kelurahan -->
        <div class="relative overflow-hidden bg-white dark:bg-zinc-900 border border-emerald-100 dark:border-emerald-900/30 rounded-3xl p-5 shadow-lg shadow-emerald-500/5 flex flex-col justify-between group hover:border-emerald-300 dark:hover:border-emerald-700 transition col-span-2 sm:col-span-1">
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[9px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 font-mono">Desa / Kelurahan</span>
              <span class="p-1.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs">🏡</span>
            </div>
            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400 tracking-tight mt-3 tabular-nums">{{ summary.total_desa || 81 }}</p>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between text-[10px] text-gray-500 dark:text-zinc-400">
            <span>Desa & Kelurahan</span>
            <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">Dompu</span>
          </div>
        </div>
      </div>

      <!-- Section A: Capaian Dokumen & Layanan Kependudukan -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xs font-black text-gray-500 dark:text-zinc-400 uppercase tracking-widest font-mono flex items-center gap-2">
            <span>📋</span> Capaian Dokumen & Layanan Kependudukan
          </h3>
          <span class="text-[10px] text-gray-400 font-medium">Target Capaian Nasional & Daerah</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <!-- Akta Lahir 0-17 Thn -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4 hover:shadow-md transition">
            <div class="flex justify-between items-start">
              <div>
                <div class="flex items-center gap-1.5 mb-1">
                  <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-full text-[8px] font-bold">
                    {{ charts.akta_lahir?.percentage !== undefined ? (charts.akta_lahir.percentage >= 90 ? '🟢 Sesuai Target' : '🟡 Perlu Akselerasi') : '⚪ Belum Ada Data' }}
                  </span>
                </div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Akta Lahir (0-17 Thn)</p>
                <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight mt-1">
                  {{ charts.akta_lahir?.percentage !== undefined ? charts.akta_lahir.percentage + '%' : '-' }}
                </p>
              </div>
              <span class="p-3 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 rounded-2xl text-xl shadow-inner">📜</span>
            </div>
            <div class="space-y-1.5">
              <div class="h-2.5 w-full bg-gray-100 dark:bg-zinc-800 rounded-full overflow-hidden p-0.5">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500 shadow-sm" :style="{ width: Math.min(100, (charts.akta_lahir?.percentage || 0)) + '%' }"></div>
              </div>
              <div class="flex justify-between text-[10px] text-gray-500 dark:text-zinc-400 font-medium">
                <span>Terbit: <strong class="text-gray-700 dark:text-zinc-200 font-mono">{{ formatNumber(charts.akta_lahir?.owned || 0) }}</strong></span>
                <span>Target: <strong class="text-gray-400 font-mono">{{ formatNumber(charts.akta_lahir?.target || 0) }}</strong></span>
              </div>
            </div>
          </div>

          <!-- Cakupan KIA -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4 hover:shadow-md transition">
            <div class="flex justify-between items-start">
              <div>
                <div class="flex items-center gap-1.5 mb-1">
                  <span class="px-2 py-0.5 bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 rounded-full text-[8px] font-bold">
                    {{ charts.kia?.percentage !== undefined ? (charts.kia.percentage >= 80 ? '🔵 Sangat Baik' : '🟡 Cukup') : '⚪ Belum Ada Data' }}
                  </span>
                </div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Kartu Identitas Anak (KIA)</p>
                <p class="text-3xl font-black text-sky-600 dark:text-sky-400 font-mono tracking-tight mt-1">
                  {{ charts.kia?.percentage !== undefined ? charts.kia.percentage + '%' : '-' }}
                </p>
              </div>
              <span class="p-3 bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-300 rounded-2xl text-xl shadow-inner">🪪</span>
            </div>
            <div class="space-y-1.5">
              <div class="h-2.5 w-full bg-gray-100 dark:bg-zinc-800 rounded-full overflow-hidden p-0.5">
                <div class="h-full bg-gradient-to-r from-sky-500 to-blue-500 rounded-full transition-all duration-500 shadow-sm" :style="{ width: Math.min(100, (charts.kia?.percentage || 0)) + '%' }"></div>
              </div>
              <div class="flex justify-between text-[10px] text-gray-500 dark:text-zinc-400 font-medium">
                <span>Terbit: <strong class="text-gray-700 dark:text-zinc-200 font-mono">{{ formatNumber(charts.kia?.owned || 0) }}</strong></span>
                <span>Target: <strong class="text-gray-400 font-mono">{{ formatNumber(charts.kia?.target || 0) }}</strong></span>
              </div>
            </div>
          </div>

          <!-- Cakupan IKD -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4 hover:shadow-md transition">
            <div class="flex justify-between items-start">
              <div>
                <div class="flex items-center gap-1.5 mb-1">
                  <span class="px-2 py-0.5 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-[8px] font-bold">
                    📱 Digital ID
                  </span>
                </div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Identitas Digital (IKD)</p>
                <p class="text-3xl font-black text-purple-600 dark:text-purple-400 font-mono tracking-tight mt-1">
                  {{ charts.ikd?.percentage !== undefined ? charts.ikd.percentage + '%' : '-' }}
                </p>
              </div>
              <span class="p-3 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 rounded-2xl text-xl shadow-inner">📱</span>
            </div>
            <div class="space-y-1.5">
              <div class="h-2.5 w-full bg-gray-100 dark:bg-zinc-800 rounded-full overflow-hidden p-0.5">
                <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full transition-all duration-500 shadow-sm" :style="{ width: Math.min(100, (charts.ikd?.percentage || 0)) + '%' }"></div>
              </div>
              <div class="flex justify-between text-[10px] text-gray-500 dark:text-zinc-400 font-medium">
                <span>Aktivasi: <strong class="text-gray-700 dark:text-zinc-200 font-mono">{{ formatNumber(charts.ikd?.owned || 0) }}</strong></span>
                <span>Wajib KTP: <strong class="text-gray-400 font-mono">{{ formatNumber(charts.ikd?.target || 0) }}</strong></span>
              </div>
            </div>
          </div>

          <!-- Perekaman Wajib KTP -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4 hover:shadow-md transition">
            <div class="flex justify-between items-start">
              <div>
                <div class="flex items-center gap-1.5 mb-1">
                  <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-[8px] font-bold">
                    {{ charts.wajib_ktp?.recorded_percentage !== undefined ? (charts.wajib_ktp.recorded_percentage >= 95 ? '🟢 Hampir Tuntas' : '🟡 Proses') : '⚪ Belum Ada Data' }}
                  </span>
                </div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Perekaman KTP-el</p>
                <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400 font-mono tracking-tight mt-1">
                  {{ charts.wajib_ktp?.recorded_percentage !== undefined ? charts.wajib_ktp.recorded_percentage + '%' : '-' }}
                </p>
              </div>
              <span class="p-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 rounded-2xl text-xl shadow-inner">💳</span>
            </div>
            <div class="space-y-1.5">
              <div class="h-2.5 w-full bg-gray-100 dark:bg-zinc-800 rounded-full overflow-hidden p-0.5">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full transition-all duration-500 shadow-sm" :style="{ width: Math.min(100, (charts.wajib_ktp?.recorded_percentage || 0)) + '%' }"></div>
              </div>
              <div class="flex justify-between text-[10px] text-gray-500 dark:text-zinc-400 font-medium">
                <span>Terekam: <strong class="text-gray-700 dark:text-zinc-200 font-mono">{{ formatNumber(charts.wajib_ktp?.recorded || 0) }}</strong></span>
                <span>Wajib KTP: <strong class="text-gray-400 font-mono">{{ formatNumber(charts.wajib_ktp?.total || 0) }}</strong></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section B: Indikator Demografi & Rasio Ketergantungan Grid -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xs font-black text-gray-500 dark:text-zinc-400 uppercase tracking-widest font-mono flex items-center gap-2">
            <span>📊</span> Struktur Demografi & Rasio Ketergantungan
          </h3>
          <span class="text-[10px] text-gray-400 font-medium">Indikator Komposisi Penduduk</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <!-- Kepala Keluarga -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-gray-400 font-mono">Kepala Keluarga</span>
                <span class="text-sm">🏠</span>
              </div>
              <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight mt-1">
                {{ formatNumber(charts.households?.total || 0) }}
              </p>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-zinc-800 text-[10px] text-gray-500 dark:text-zinc-400">
              <p class="font-semibold text-emerald-700 dark:text-emerald-300">
                {{ summary.total_population && charts.households?.total ? 'Rata-rata ' + (summary.total_population / charts.households.total).toFixed(2) + ' jiwa/KK' : 'Data KK belum diunggah' }}
              </p>
            </div>
          </div>

          <!-- Penduduk Produktif -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-gray-400 font-mono">Usia Produktif</span>
                <span class="text-xs font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded">15-59 Thn</span>
              </div>
              <p class="text-2xl font-black text-blue-600 dark:text-blue-400 font-mono tracking-tight mt-1">
                {{ formatNumber(charts.productive_age?.total || 0) }}
              </p>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-zinc-800 text-[10px]">
              <p class="font-bold text-blue-600 dark:text-blue-400">
                ⚡ {{ charts.productive_age?.percentage !== undefined ? charts.productive_age.percentage + '% Bonus Demografi' : (summary.total_population && charts.productive_age?.total ? ((charts.productive_age.total / summary.total_population) * 100).toFixed(1) + '% Bonus Demografi' : 'Data produktif belum ada') }}
              </p>
            </div>
          </div>

          <!-- Jumlah Lansia -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-gray-400 font-mono">Penduduk Lansia</span>
                <span class="text-xs font-bold text-amber-600 bg-amber-50 dark:bg-amber-900/30 px-1.5 py-0.5 rounded">60+ Thn</span>
              </div>
              <p class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono tracking-tight mt-1">
                {{ formatNumber(charts.lansia?.total || 0) }}
              </p>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-zinc-800 text-[10px]">
              <p class="font-bold text-amber-600 dark:text-amber-400">
                👴 {{ charts.lansia?.percentage !== undefined ? charts.lansia.percentage + '% Dari Total Penduduk' : (summary.total_population && charts.lansia?.total ? ((charts.lansia.total / summary.total_population) * 100).toFixed(1) + '% Dari Total Penduduk' : 'Data lansia belum ada') }}
              </p>
            </div>
          </div>

          <!-- Rasio Ketergantungan -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-gray-400 font-mono">Dependency Ratio</span>
                <span class="text-sm">📉</span>
              </div>
              <p class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono tracking-tight mt-1">
                {{ charts.dependency_ratio?.ratio !== undefined ? charts.dependency_ratio.ratio + '%' : '-' }}
              </p>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-zinc-800 text-[10px] text-gray-500 dark:text-zinc-400">
              <p class="font-semibold text-rose-600 dark:text-rose-400">
                {{ charts.dependency_ratio?.ratio !== undefined ? charts.dependency_ratio.ratio + ' jiwa non-produktif per 100 produktif' : 'Rasio belum diunggah' }}
              </p>
            </div>
          </div>

          <!-- Jumlah Disabilitas -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition col-span-2 sm:col-span-1">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-gray-400 font-mono">Disabilitas</span>
                <span class="text-sm">♿</span>
              </div>
              <p class="text-2xl font-black text-teal-600 dark:text-teal-400 font-mono tracking-tight mt-1">
                {{ formatNumber(charts.disability?.total || 0) }}
              </p>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-zinc-800 text-[10px] text-gray-500 dark:text-zinc-400">
              <p class="font-semibold text-teal-600 dark:text-teal-400">
                {{ charts.disability?.total ? 'Terbagi dalam ragam disabilitas' : 'Data disabilitas belum ada' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Per Kecamatan Population -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
          <h4 class="text-xs font-extrabold text-gray-700 dark:text-zinc-100 mb-4">📍 Penduduk per Kecamatan</h4>
          <div ref="kecamatanChartEl" class="h-64"></div>
        </div>

        <!-- Population Pyramid -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
          <h4 class="text-xs font-extrabold text-gray-700 dark:text-zinc-100 mb-4">🔻 Piramida Penduduk (Kelompok Usia)</h4>
          <div ref="pyramidChartEl" class="h-64"></div>
        </div>

        <!-- Religion -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
          <h4 class="text-xs font-extrabold text-gray-700 dark:text-zinc-100 mb-4">☪️ Distribusi Agama</h4>
          <div ref="religionChartEl" class="h-64"></div>
        </div>

        <!-- Education -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
          <h4 class="text-xs font-extrabold text-gray-700 dark:text-zinc-100 mb-4">🎓 Tingkat Pendidikan</h4>
          <div ref="educationChartEl" class="h-64"></div>
        </div>

        <!-- Marital Status -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
          <h4 class="text-xs font-extrabold text-gray-700 dark:text-zinc-100 mb-4">💍 Status Perkawinan</h4>
          <div ref="maritalChartEl" class="h-64"></div>
        </div>

        <!-- Blood Types -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
          <h4 class="text-xs font-extrabold text-gray-700 dark:text-zinc-100 mb-4">🩸 Golongan Darah</h4>
          <div ref="bloodChartEl" class="h-64"></div>
        </div>

        <!-- Disability Chart -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm md:col-span-2">
          <h4 class="text-xs font-extrabold text-gray-700 dark:text-zinc-100 mb-4">♿ Ragam Penyandang Disabilitas</h4>
          <div ref="disabilityChartEl" class="h-64"></div>
        </div>
      </div>

      <!-- Kecamatan table breakdown -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-zinc-800">
          <h4 class="text-xs font-extrabold text-gray-800 dark:text-zinc-100">📊 Data Penduduk per Kecamatan</h4>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800/60">
              <tr>
                <th class="text-left px-3 py-2 sm:px-6 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Kode Wilayah</th>
                <th class="text-left px-3 py-2 sm:px-6 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Kecamatan</th>
                <th class="text-right px-3 py-2 sm:px-6 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Total</th>
                <th class="text-right px-3 py-2 sm:px-6 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Laki-laki</th>
                <th class="text-right px-3 py-2 sm:px-6 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Perempuan</th>
                <th class="text-right px-3 py-2 sm:px-6 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Luas km²</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
              <tr v-if="!sortedHeatmapData || sortedHeatmapData.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic text-xs">Belum ada data per kecamatan untuk periode ini.</td>
              </tr>
              <tr v-for="kec in sortedHeatmapData" :key="kec.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                <td class="px-3 py-2 sm:px-6 sm:py-3 font-mono font-extrabold text-blue-600 dark:text-blue-400 text-[10px] sm:text-[11px] whitespace-nowrap">{{ kec.code }}</td>
                <td class="px-3 py-2 sm:px-6 sm:py-3 font-bold text-gray-700 dark:text-zinc-200 text-xs whitespace-nowrap">{{ kec.name }}</td>
                <td class="px-3 py-2 sm:px-6 sm:py-3 text-right font-mono font-semibold text-gray-600 dark:text-zinc-300 text-xs whitespace-nowrap">
                  <span v-if="kec.hasData">{{ formatNumber(kec.population) }}</span>
                  <span v-else class="text-gray-300 dark:text-zinc-600 italic text-[10px]">Belum ada data</span>
                </td>
                <td class="px-3 py-2 sm:px-6 sm:py-3 text-right font-mono text-sky-600 text-xs whitespace-nowrap">
                  <span v-if="kec.hasData">{{ formatNumber(kec.male) }}</span>
                  <span v-else class="text-gray-300 dark:text-zinc-600">—</span>
                </td>
                <td class="px-3 py-2 sm:px-6 sm:py-3 text-right font-mono text-pink-600 text-xs whitespace-nowrap">
                  <span v-if="kec.hasData">{{ formatNumber(kec.female) }}</span>
                  <span v-else class="text-gray-300 dark:text-zinc-600">—</span>
                </td>
                <td class="px-3 py-2 sm:px-6 sm:py-3 text-right font-mono text-gray-400 text-xs whitespace-nowrap">{{ kec.area_km2 ? Number(kec.area_km2).toFixed(2) : '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import * as echarts from 'echarts';

const props = defineProps({
  kecamatans:           Array,
  desas:                Array,
  dataAvailable:        Boolean,
  availableYears:       Array,
  availableSemesters:   Array,
  selectedYear:         Number,
  selectedSemester:     Number,
  selectedRegionLevel:  String,
  selectedRegionCode:   String,
  summary:              Object,
  charts:               Object,
  heatmapData:          Array,
  typeLabels:           Object,
  semesterLabels:       Object,
  regionLevels:         Object,
  regencyCode:          String,
});

const selectedYear  = ref(props.selectedYear ?? (props.availableYears?.[0] || new Date().getFullYear()));
const selectedSem   = ref(props.selectedSemester ?? 1);
const selectedLevel = ref(props.selectedRegionLevel ?? 'regency');
const selectedCode  = ref(props.selectedRegionCode  ?? props.regencyCode);

watch(() => props.selectedYear, (val) => { if (val) selectedYear.value = val; });
watch(() => props.selectedSemester, (val) => { if (val) selectedSem.value = val; });
watch(() => props.selectedRegionLevel, (val) => { if (val) selectedLevel.value = val; });
watch(() => props.selectedRegionCode, (val) => { if (val) selectedCode.value = val; });

watch(() => props.charts, () => {
  if (props.dataAvailable) renderAllCharts();
}, { deep: true });

watch(() => props.dataAvailable, (isAvailable) => {
  if (isAvailable) renderAllCharts();
});

const onLevelChange = () => {
  if (selectedLevel.value === 'regency') {
    selectedCode.value = props.regencyCode;
  } else {
    selectedCode.value = props.kecamatans?.[0]?.code ?? '';
  }
  applyFilters();
};

const applyFilters = () => {
  router.get(route('public.statistics'), {
    year:         selectedYear.value,
    semester:     selectedSem.value,
    region_level: selectedLevel.value,
    region_code:  selectedCode.value,
  }, { preserveState: true });
};

const sortedHeatmapData = computed(() => {
  const list = [...(props.heatmapData || props.kecamatans || [])];
  return list.sort((a, b) => String(a.code || '').localeCompare(String(b.code || '')));
});

const kecamatanChartEl = ref(null);
const pyramidChartEl   = ref(null);
const religionChartEl  = ref(null);
const educationChartEl = ref(null);
const maritalChartEl    = ref(null);
const bloodChartEl      = ref(null);
const disabilityChartEl = ref(null);

const COLORS = ['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'];

const formatNumber = (n) => (n || 0).toLocaleString('id-ID');

const noDataOption = (msg) => ({
  title: { text: msg, left: 'center', top: 'middle', textStyle: { color: '#9ca3af', fontSize: 11 } },
});

const chartInstances = [];

const handleResize = () => {
  chartInstances.forEach(c => c && c.resize && c.resize());
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
  if (props.dataAvailable) renderAllCharts();
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});

const renderAllCharts = async () => {
  await nextTick();
  setTimeout(() => {
  // Per-Kecamatan bar
  if (kecamatanChartEl.value) {
    const c = echarts.init(kecamatanChartEl.value);
    chartInstances.push(c);
    c.clear();

    const data = props.heatmapData || [];
    const hasAnyData = data.some(k => k.hasData);

    if (!hasAnyData) {
      c.setOption(noDataOption('Belum ada dataset per kecamatan'));
    } else {
      c.setOption({
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'shadow' },
          formatter: (params) => {
            const item = data[params[0].dataIndex];
            if (!item || !item.hasData) return `${params[0].name}: Belum ada data`;
            return `${item.name}<br/>Total: ${(item.population || 0).toLocaleString('id-ID')} jiwa<br/>L: ${(item.male || 0).toLocaleString('id-ID')} | P: ${(item.female || 0).toLocaleString('id-ID')}`;
          }
        },
        grid: { left: 5, right: 25, top: 10, bottom: 10, containLabel: true },
        xAxis: { type: 'value', axisLabel: { fontSize: 8, formatter: v => (v >= 1000 ? (v/1000).toFixed(0)+'K' : v) } },
        yAxis: { type: 'category', data: data.map(k => k.name), axisLabel: { fontSize: 9 } },
        series: [{
          type: 'bar',
          data: data.map(k => {
            const isSelected = selectedLevel.value === 'district' && k.code === selectedCode.value;
            return {
              value: k.population || 0,
              itemStyle: isSelected ? {
                color: { type: 'linear', x: 0, y: 0, x2: 1, y2: 0, colorStops: [{ offset: 0, color: '#f59e0b' }, { offset: 1, color: '#d97706' }] }
              } : {
                color: { type: 'linear', x: 0, y: 0, x2: 1, y2: 0, colorStops: [{ offset: 0, color: '#6366f1' }, { offset: 1, color: '#06b6d4' }] }
              }
            };
          }),
          barMaxWidth: 18
        }],
      });
    }
  }

  // Population Pyramid
  if (pyramidChartEl.value) {
    const c    = echarts.init(pyramidChartEl.value);
    chartInstances.push(c);
    c.clear();
    const data = props.charts?.population;
    if (!data) { c.setOption(noDataOption('Tidak ada data piramida penduduk')); }
    else {
      const cats = data.categories || [];
      c.setOption({
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: { data: ['Laki-laki', 'Perempuan'], bottom: 0, textStyle: { fontSize: 8 } },
        grid: { left: 5, right: 15, top: 10, bottom: 28, containLabel: true },
        xAxis: { type: 'value', axisLabel: { fontSize: 8, formatter: v => (Math.abs(v) >= 1000 ? Math.abs(v/1000).toFixed(0)+'K' : Math.abs(v)) } },
        yAxis: { type: 'category', data: cats, axisLabel: { fontSize: 8 } },
        series: [
          { name: 'Laki-laki', type: 'bar', stack: 'total', data: (data.male||[]).map(v => -Math.abs(v)), itemStyle: { color: '#06b6d4' }, barMaxWidth: 14 },
          { name: 'Perempuan', type: 'bar', stack: 'total', data: data.female||[], itemStyle: { color: '#ec4899' }, barMaxWidth: 14 },
        ],
      });
    }
  }

  // Religion
  if (religionChartEl.value) {
    const c = echarts.init(religionChartEl.value);
    chartInstances.push(c);
    c.clear();
    const d = props.charts?.religion;
    if (!d?.items) { c.setOption(noDataOption('Tidak ada data agama')); }
    else {
      c.setOption({ tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' }, legend: { bottom: 0, textStyle: { fontSize: 8 } },
        series: [{ type: 'pie', radius: ['35%','65%'], center: ['50%','42%'], data: d.items.map((it,i) => ({ name: it.name, value: it.value, itemStyle: { color: COLORS[i%COLORS.length] } })), label: { fontSize: 8 } }] });
    }
  }

  // Education
  if (educationChartEl.value) {
    const c = echarts.init(educationChartEl.value);
    chartInstances.push(c);
    c.clear();
    const d = props.charts?.education;
    if (!d?.categories) { c.setOption(noDataOption('Tidak ada data pendidikan')); }
    else {
      c.setOption({ tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } }, grid: { left: 5, right: 20, top: 10, bottom: 10, containLabel: true },
        xAxis: { type: 'value', axisLabel: { fontSize: 8, formatter: v => (v >= 1000 ? (v/1000).toFixed(0)+'K' : v) } }, yAxis: { type: 'category', data: d.categories, axisLabel: { fontSize: 8 } },
        series: [{ type: 'bar', data: d.values, itemStyle: { color: '#10b981' }, barMaxWidth: 16 }] });
    }
  }

  // Marital
  if (maritalChartEl.value) {
    const c = echarts.init(maritalChartEl.value);
    chartInstances.push(c);
    c.clear();
    const d = props.charts?.marital;
    if (!d?.items) { c.setOption(noDataOption('Tidak ada data status perkawinan')); }
    else {
      c.setOption({ tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' }, legend: { bottom: 0, textStyle: { fontSize: 8 } },
        series: [{ type: 'pie', radius: '60%', center: ['50%','42%'], data: d.items.map((it,i) => ({ name: it.name, value: it.value, itemStyle: { color: COLORS[i%COLORS.length] } })), label: { fontSize: 8 } }] });
    }
  }

  // Blood type
  if (bloodChartEl.value) {
    const c = echarts.init(bloodChartEl.value);
    chartInstances.push(c);
    c.clear();
    const d = props.charts?.blood_type;
    if (!d?.categories) { c.setOption(noDataOption('Tidak ada data golongan darah')); }
    else {
      c.setOption({ tooltip: { trigger: 'axis' }, grid: { left: 5, right: 15, top: 20, bottom: 10, containLabel: true },
        xAxis: { type: 'category', data: d.categories, axisLabel: { fontSize: 10, fontWeight: 'bold' } }, yAxis: { type: 'value', axisLabel: { fontSize: 8, formatter: v => (v >= 1000 ? (v/1000).toFixed(0)+'K' : v) } },
        series: [{ type: 'bar', data: d.values, itemStyle: { color: params => COLORS[params.dataIndex%COLORS.length] }, barMaxWidth: 32, label: { show: true, position: 'top', fontSize: 8, formatter: (params) => (params.value || 0).toLocaleString('id-ID') } }] });
    }
  }

  // Disability chart
  if (disabilityChartEl.value) {
    const c = echarts.init(disabilityChartEl.value);
    chartInstances.push(c);
    c.clear();
    const d = props.charts?.disability;
    if (!d?.items) { c.setOption(noDataOption('Tidak ada data ragam disabilitas')); }
    else {
      c.setOption({
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { left: 5, right: 35, top: 10, bottom: 10, containLabel: true },
        xAxis: { type: 'value', axisLabel: { fontSize: 8 } },
        yAxis: { type: 'category', data: d.items.map(i => i.name), axisLabel: { fontSize: 8 } },
        series: [{
          type: 'bar', data: d.items.map(i => i.value),
          itemStyle: { color: '#0d9488' },
          barMaxWidth: 18,
          label: { show: true, position: 'right', fontSize: 8, formatter: (params) => (params.value || 0).toLocaleString('id-ID') }
        }],
      });
    }
  }
    handleResize();
  }, 150);
};
</script>
