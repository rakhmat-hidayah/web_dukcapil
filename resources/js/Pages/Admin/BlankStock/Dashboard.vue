<template>
  <Head title="Dashboard Persediaan Blangko KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-sky-900/50">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold mb-3 border shadow-xs"
                 :class="statusBadgeClasses">
              <span class="w-2 h-2 rounded-full animate-ping" :class="statusDotClasses"></span>
              <span>Status Stok: {{ currentStockStatus }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
              <Layers class="w-8 h-8 text-sky-400 shrink-0" />
              <span>Persediaan Blangko KTP-el</span>
            </h1>
            <p class="text-xs sm:text-sm text-sky-200 mt-1 max-w-2xl leading-relaxed">
              Sistem buku persediaan resmi, pencatatan multi-operator cetak terverifikasi, kontrol mutasi transaksi atomic, dan publikasi ketersediaan blangko Disdukcapil Kabupaten Dompu.
            </p>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex flex-wrap items-center gap-2.5">
            <Link
              v-if="$page.props.auth?.permissions?.includes('blank_stocks.receipts.create') || isSuperOrAdmin"
              :href="route('admin.blank-stocks.receipts.index')"
              class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer"
            >
              <Inbox class="w-4 h-4" />
              <span>Penerimaan Baru</span>
            </Link>

            <Link
              :href="route('admin.blank-stocks.usage.index')"
              class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 transition flex items-center gap-2 cursor-pointer"
            >
              <ClipboardList class="w-4 h-4" />
              <span>Laporan Penggunaan</span>
            </Link>

            <Link
              v-if="$page.props.auth?.permissions?.includes('blank_stocks.reports.export') || isSuperOrAdmin"
              :href="route('admin.blank-stocks.reports.index')"
              class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 transition flex items-center gap-2 cursor-pointer"
            >
              <FileSpreadsheet class="w-4 h-4" />
              <span>Rekapitulasi</span>
            </Link>

            <a
              :href="route('public.blangko.index')"
              target="_blank"
              class="px-3.5 py-2.5 rounded-xl bg-sky-500/20 hover:bg-sky-500/30 text-sky-200 font-bold text-xs border border-sky-400/30 transition flex items-center gap-1.5"
              title="Lihat Tampilan Publik"
            >
              <Globe class="w-4 h-4" />
              <span class="hidden sm:inline">Info Publik</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Active Alerts -->
      <div v-if="summary.alerts && summary.alerts.length > 0" class="space-y-3">
        <div
          v-for="(alert, idx) in summary.alerts"
          :key="idx"
          class="p-4 rounded-2xl flex items-start gap-3 border shadow-sm animate-fade-in"
          :class="{
            'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-200': alert.type === 'danger',
            'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900/60 text-amber-800 dark:text-amber-200': alert.type === 'warning',
            'bg-sky-50 dark:bg-sky-950/40 border-sky-200 dark:border-sky-900/60 text-sky-800 dark:text-sky-200': alert.type === 'info',
          }"
        >
          <AlertTriangle class="w-5 h-5 shrink-0 mt-0.5" />
          <div class="flex-1 text-xs">
            <h4 class="font-extrabold text-sm">{{ alert.title }}</h4>
            <p class="mt-0.5 leading-relaxed">{{ alert.message }}</p>
          </div>
        </div>
      </div>

      <!-- Core Stock Metric Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Saldo Persediaan Resmi -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-500 dark:text-zinc-400">Saldo Persediaan Gudang</span>
            <div class="w-10 h-10 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center border border-sky-100 dark:border-sky-900">
              <Layers class="w-5 h-5" />
            </div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-zinc-100 tracking-tight font-mono">
              {{ formatNumber(summary?.stock?.current_stock) }}
              <span class="text-xs font-bold text-gray-400 dark:text-zinc-500">keping</span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1 flex items-center gap-1">
              <span>Batas Kritis: <strong>{{ summary?.stock?.min_stock_alert || 0 }}</strong> keping</span>
            </p>
          </div>
        </div>

        <!-- Card 2: Penggunaan Hari Ini -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-500 dark:text-zinc-400">Penggunaan Hari Ini</span>
            <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-100 dark:border-blue-900">
              <CheckCircle2 class="w-5 h-5" />
            </div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-zinc-100 tracking-tight font-mono">
              {{ formatNumber(todayUsed) }}
              <span class="text-xs font-bold text-gray-400 dark:text-zinc-500">keping</span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1">
              Rusak hari ini: <span class="font-bold text-rose-600 dark:text-rose-400">{{ todayDamaged }} keping</span>
            </p>
          </div>
        </div>

        <!-- Card 3: Penerimaan Bulan Ini -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-500 dark:text-zinc-400">Penerimaan Bulan Ini</span>
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-900">
              <Inbox class="w-5 h-5" />
            </div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight font-mono">
              +{{ formatNumber(monthReceived) }}
              <span class="text-xs font-bold text-gray-400 dark:text-zinc-500">keping</span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1">
              Total riwayat masuk: {{ formatNumber(summary?.stock?.total_received) }}
            </p>
          </div>
        </div>

        <!-- Card 4: Penggunaan Bulan Ini -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-500 dark:text-zinc-400">Total Keluar Bulan Ini</span>
            <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-900">
              <ClipboardList class="w-5 h-5" />
            </div>
          </div>
          <div>
            <div class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-zinc-100 tracking-tight font-mono">
              {{ formatNumber(monthUsed + monthDamaged) }}
              <span class="text-xs font-bold text-gray-400 dark:text-zinc-500">keping</span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1">
              Cetakan: {{ formatNumber(monthUsed) }} | Rusak: {{ formatNumber(monthDamaged) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Secondary Metrics: Status Operator & Stok Opname -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Status Pelaporan Operator Hari Ini -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Users class="w-4 h-4 text-sky-600 dark:text-sky-400" />
              <h3 class="text-xs font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider">
                Laporan Operator Hari Ini
              </h3>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
              {{ operatorsReported }} / {{ operatorsReported + operatorsPending }} Operator
            </span>
          </div>

          <div class="space-y-2 pt-1 text-xs">
            <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-zinc-800">
              <span class="text-gray-600 dark:text-zinc-400">Sudah Lapor Hari Ini</span>
              <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ operatorsReported }} Operator</span>
            </div>
            <div class="flex justify-between items-center py-1">
              <span class="text-gray-600 dark:text-zinc-400">Belum Lapor Hari Ini</span>
              <span class="font-extrabold" :class="operatorsPending > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500'">
                {{ operatorsPending }} Operator
              </span>
            </div>
          </div>
        </div>

        <!-- Stok Opname & Penyesuaian Pending -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <PackageCheck class="w-4 h-4 text-purple-600 dark:text-purple-400" />
              <h3 class="text-xs font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider">
                Rekonsiliasi Fisik
              </h3>
            </div>
            <span v-if="(summary?.pending_adjustments_count || 0) > 0" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-700 animate-pulse">
              {{ summary.pending_adjustments_count }} Usulan Pending
            </span>
          </div>

          <div class="space-y-2 pt-1 text-xs">
            <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-zinc-800">
              <span class="text-gray-600 dark:text-zinc-400">Opname Fisik Terakhir</span>
              <span class="font-bold text-gray-800 dark:text-zinc-200">
                {{ summary?.last_opname ? summary.last_opname.date : 'Belum pernah' }}
              </span>
            </div>
            <div class="flex justify-between items-center py-1">
              <span class="text-gray-600 dark:text-zinc-400">Selisih Fisik Terakhir</span>
              <span class="font-bold" :class="summary?.last_opname && summary.last_opname.difference !== 0 ? 'text-rose-500' : 'text-emerald-500'">
                {{ summary?.last_opname ? (summary.last_opname.difference > 0 ? '+' : '') + summary.last_opname.difference + ' keping' : '-' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Informasi Publik Status -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Globe class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
              <h3 class="text-xs font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider">
                Publikasi Informasi
              </h3>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
              Aktif
            </span>
          </div>

          <div class="space-y-2 pt-1 text-xs">
            <div class="flex justify-between items-center py-1 border-b border-gray-100 dark:border-zinc-800">
              <span class="text-gray-600 dark:text-zinc-400">Keping Ditampilkan Publik</span>
              <span class="font-mono font-black text-gray-900 dark:text-zinc-100">{{ formatNumber(summary?.stock?.current_stock) }}</span>
            </div>
            <div class="flex justify-between items-center py-1">
              <span class="text-gray-600 dark:text-zinc-400">Pembaruan Valid Terakhir</span>
              <span class="font-bold text-gray-600 dark:text-zinc-400">{{ summary?.stock?.last_updated || summary?.stock?.last_transaction_at || '-' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Chart 14 Hari & Breakdown Operator -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart Penggunaan 14 Hari Terakhir -->
        <div class="lg:col-span-7 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-4.5 sm:p-6 shadow-sm space-y-4">
          <!-- Header Card Chart -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-zinc-100 tracking-tight">
                  Tren Penggunaan Blangko
                </h3>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                  {{ chartPeriod === '7' ? '7 Hari Terakhir' : '14 Hari Terakhir' }}
                </span>
              </div>
              <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-0.5">
                Perbandingan pencetakan berhasil vs blangko rusak per hari
              </p>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-3 pt-1 sm:pt-0 border-t sm:border-t-0 border-gray-100 dark:border-zinc-800">
              <!-- Period Toggle -->
              <div class="inline-flex p-0.5 rounded-xl bg-gray-100 dark:bg-zinc-800 text-[11px] font-bold border border-gray-200/60 dark:border-zinc-700/60">
                <button
                  type="button"
                  @click="setChartPeriod('7')"
                  class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                  :class="chartPeriod === '7' ? 'bg-white dark:bg-zinc-700 text-blue-600 dark:text-blue-300 shadow-xs' : 'text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-200'"
                >
                  7 Hari
                </button>
                <button
                  type="button"
                  @click="setChartPeriod('14')"
                  class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                  :class="chartPeriod === '14' ? 'bg-white dark:bg-zinc-700 text-blue-600 dark:text-blue-300 shadow-xs' : 'text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-200'"
                >
                  14 Hari
                </button>
              </div>

              <!-- Legend -->
              <div class="flex items-center gap-2.5 text-[11px] font-bold">
                <div class="flex items-center gap-1.5">
                  <span class="w-2.5 h-2.5 rounded-sm bg-blue-500"></span>
                  <span class="text-gray-600 dark:text-zinc-400 text-[10px] sm:text-xs">Digunakan</span>
                </div>
                <div class="flex items-center gap-1.5">
                  <span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span>
                  <span class="text-gray-600 dark:text-zinc-400 text-[10px] sm:text-xs">Rusak</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Chart Container with Mobile Horizontal Scroll and Adaptive Width -->
          <div ref="chartContainerRef" class="overflow-x-auto scrollbar-thin pb-2 pt-1">
            <div
              :class="chartPeriod === '7' ? 'w-full' : 'min-w-[480px] sm:min-w-full'"
              class="h-52 flex items-end justify-between gap-1.5 sm:gap-2 border-b border-gray-100 dark:border-zinc-800 pb-2.5 pt-4 transition-all"
            >
              <div
                v-for="(day, idx) in displayedChart"
                :key="day.date || idx"
                @click="selectDay(day)"
                class="flex-1 flex flex-col items-center gap-1 h-full justify-end group relative cursor-pointer"
              >
                <!-- Tooltip on hover (desktop) -->
                <div class="absolute -top-10 hidden group-hover:flex flex-col items-center bg-gray-900 text-white text-[10px] py-1 px-2 rounded-lg whitespace-nowrap z-20 shadow-lg pointer-events-none">
                  <span>{{ day.label }}: {{ day.used }} digunakan, {{ day.damaged }} rusak</span>
                </div>

                <!-- Number badge above bar -->
                <span
                  v-if="(day.used + day.damaged) > 0"
                  class="text-[9px] font-black font-mono text-blue-600 dark:text-blue-400 leading-none mb-1"
                >
                  {{ day.used + day.damaged }}
                </span>
                <span
                  v-else
                  class="text-[8px] text-gray-300 dark:text-zinc-700 leading-none mb-1 select-none"
                >
                  0
                </span>

                <!-- Bar stacks -->
                <div
                  class="w-full max-w-[24px] sm:max-w-[30px] flex flex-col-reverse rounded-t overflow-hidden bg-gray-100 dark:bg-zinc-800 h-full max-h-[135px] justify-start transition-all duration-200"
                  :class="activeDay?.date === day.date ? 'ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-zinc-900 shadow-sm' : 'hover:opacity-85'"
                >
                  <div
                    class="bg-blue-500 transition-all duration-300"
                    :style="{ height: getBarHeight(day.used) + '%' }"
                  ></div>
                  <div
                    v-if="day.damaged > 0"
                    class="bg-rose-500 transition-all duration-300"
                    :style="{ height: getBarHeight(day.damaged) + '%' }"
                  ></div>
                </div>

                <!-- Stacked Clean Date Labels (No Truncation) -->
                <div class="flex flex-col items-center leading-none text-center select-none pt-1">
                  <span
                    class="text-[10px] font-black transition"
                    :class="activeDay?.date === day.date ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-zinc-300'"
                  >
                    {{ formatDayNum(day.date) }}
                  </span>
                  <span class="text-[8px] font-bold text-gray-400 dark:text-zinc-500 uppercase mt-0.5">
                    {{ formatMonthShort(day.date) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Mobile Swipe Hint (When 14-day view is active on narrow screens) -->
          <div
            v-if="chartPeriod === '14'"
            class="sm:hidden flex items-center justify-center gap-1.5 text-[10px] text-gray-400 dark:text-zinc-500 pt-0.5"
          >
            <ArrowLeftRight class="w-3 h-3 text-gray-400" />
            <span>Geser grafik ke kiri / kanan untuk melihat semua hari</span>
          </div>

          <!-- Active Day Interactive Detail Pill -->
          <div
            v-if="activeDay"
            class="p-3 rounded-2xl bg-gradient-to-r from-blue-50/70 to-sky-50/60 dark:from-blue-950/40 dark:to-sky-950/30 border border-blue-200/80 dark:border-blue-900/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs transition"
          >
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
              <span class="font-extrabold text-gray-900 dark:text-zinc-100">
                {{ formatFullDate(activeDay.date) }}
              </span>
              <span
                v-if="activeDay.date === todayDateStr"
                class="text-[10px] font-black px-1.5 py-0.2 rounded-md bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800"
              >
                Hari Ini
              </span>
            </div>

            <div class="flex items-center gap-3 font-semibold text-[11px] sm:text-xs">
              <div class="flex items-center gap-1 text-blue-700 dark:text-blue-300">
                <span>Digunakan:</span>
                <strong class="font-mono font-black">{{ formatNumber(activeDay.used) }}</strong>
              </div>
              <span class="text-gray-300 dark:text-zinc-700">|</span>
              <div class="flex items-center gap-1 text-rose-700 dark:text-rose-300">
                <span>Rusak:</span>
                <strong class="font-mono font-black">{{ formatNumber(activeDay.damaged) }}</strong>
              </div>
              <span class="text-gray-300 dark:text-zinc-700">|</span>
              <div class="font-mono font-black text-gray-900 dark:text-zinc-100">
                Total: {{ formatNumber(activeDay.used + activeDay.damaged) }} keping
              </div>
            </div>
          </div>
        </div>

        <!-- Rekap Operator Bulan Ini -->
        <div class="lg:col-span-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 tracking-tight">
              Pencatatan Operator Bulan Ini
            </h3>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Multi-Operator</span>
          </div>

          <div v-if="!summary?.operator_breakdown || summary.operator_breakdown.length === 0" class="text-center py-8 text-xs text-gray-400">
            Belum ada data pemakaian operator pada bulan ini.
          </div>

          <div v-else class="space-y-3 max-h-56 overflow-y-auto pr-1">
            <div
              v-for="op in (summary?.operator_breakdown || [])"
              :key="op.operator_id"
              class="p-3 rounded-2xl bg-gray-50 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs"
            >
              <div class="min-w-0">
                <h4 class="font-extrabold text-gray-900 dark:text-zinc-100 truncate">{{ op.operator_name }}</h4>
                <p class="text-[10px] text-gray-500 dark:text-zinc-400 mt-0.5">
                  {{ op.total_reports }} sesi laporan
                </p>
              </div>

              <div class="text-right shrink-0">
                <span class="font-mono font-black text-sm text-gray-900 dark:text-zinc-100">
                  {{ formatNumber(op.total) }}
                </span>
                <span class="text-[10px] text-gray-400 block">
                  Cetak: {{ op.total_used }} | Rusak: {{ op.total_damaged }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 10 Transaksi Mutasi Terakhir -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 tracking-tight flex items-center gap-2">
              <History class="w-4 h-4 text-sky-600 dark:text-sky-400" />
              <span>Mutasi Persediaan Terakhir (Kartu Persediaan)</span>
            </h3>
            <p class="text-[11px] text-gray-500 dark:text-zinc-400">
              Audit log mutasi penambahan, pemakaian cetak, kerusakan, dan penyesuaian saldo resmi
            </p>
          </div>

          <Link
            :href="route('admin.blank-stocks.ledger.index')"
            class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1"
          >
            <span>Buka Buku Kartu Persediaan Lengkap</span>
            <span>&rarr;</span>
          </Link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:zinc-800 text-gray-600 dark:text-zinc-300 font-extrabold text-[11px] uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4 rounded-l-xl">No. Transaksi</th>
                <th class="py-3 px-3">Tanggal</th>
                <th class="py-3 px-3">Jenis Mutasi</th>
                <th class="py-3 px-3 text-right">Masuk</th>
                <th class="py-3 px-3 text-right">Keluar</th>
                <th class="py-3 px-3 text-right">Saldo Akhir</th>
                <th class="py-3 px-3">Aktor / Operator</th>
                <th class="py-3 px-4 rounded-r-xl">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="!summary?.recent_transactions || summary.recent_transactions.length === 0">
                <td colspan="8" class="text-center py-8 text-gray-400">
                  Belum ada riwayat transaksi kartu persediaan.
                </td>
              </tr>
              <tr
                v-for="trx in (summary?.recent_transactions || [])"
                :key="trx.id"
                class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 transition"
              >
                <td class="py-3 px-4 font-mono font-bold text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ trx.transaction_number }}
                </td>
                <td class="py-3 px-3 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                  {{ trx.transaction_date }}
                </td>
                <td class="py-3 px-3 whitespace-nowrap">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border"
                    :class="getTypeBadgeClass(trx.transaction_type)"
                  >
                    {{ trx.type_label }}
                  </span>
                </td>
                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                  {{ trx.qty_in > 0 ? '+' + formatNumber(trx.qty_in) : '-' }}
                </td>
                <td class="py-3 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                  {{ (trx.qty_out_used + trx.qty_out_damaged) > 0 ? '-' + formatNumber(trx.qty_out_used + trx.qty_out_damaged) : '-' }}
                </td>
                <td class="py-3 px-3 text-right font-mono font-black text-gray-900 dark:text-zinc-100">
                  {{ formatNumber(trx.balance_after) }}
                </td>
                <td class="py-3 px-3 text-gray-700 dark:text-zinc-300 font-semibold truncate max-w-[130px]">
                  {{ trx.actor_name }}
                  <span v-if="trx.operator_name" class="block text-[10px] text-gray-400 font-normal truncate">
                    Op: {{ trx.operator_name }}
                  </span>
                </td>
                <td class="py-3 px-4 text-gray-500 dark:text-zinc-400 truncate max-w-[180px]">
                  {{ trx.notes || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Layers, Inbox, ClipboardList, FileSpreadsheet, Globe, AlertTriangle, 
  CheckCircle2, Users, PackageCheck, History, ArrowLeftRight 
} from '@lucide/vue';

const props = defineProps({
  summary: {
    type: Object,
    required: true,
  },
});

const page = usePage();
const userRoles = computed(() => page.props.auth?.roles || []);
const isSuperAdmin = computed(() => page.props.auth?.is_super_admin || false);
const isSuperOrAdmin = computed(() => 
  isSuperAdmin.value ||
  userRoles.value.includes('Super Administrator') || 
  userRoles.value.includes('Administrator Utama') || 
  userRoles.value.includes('Bendahara Barang') ||
  userRoles.value.includes('Supervisor Layanan KTP-el') ||
  userRoles.value.includes('Supervisor Persediaan Blangko')
);

// Chart mobile responsiveness & interactive states
const chartPeriod = ref('14');
const chartContainerRef = ref(null);
const activeDay = ref(null);

const todayDateStr = computed(() => {
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
});

const displayedChart = computed(() => {
  const chart = props.summary?.daily_chart || [];
  if (chartPeriod.value === '7') {
    return chart.slice(-7);
  }
  return chart;
});

const scrollToRight = () => {
  nextTick(() => {
    if (chartContainerRef.value) {
      chartContainerRef.value.scrollLeft = chartContainerRef.value.scrollWidth;
    }
  });
};

const setChartPeriod = (period) => {
  chartPeriod.value = period;
  if (period === '14') {
    scrollToRight();
  }
};

const selectDay = (day) => {
  activeDay.value = day;
};

const formatDayNum = (dateStr) => {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  return parts.length === 3 ? parts[2] : dateStr;
};

const formatMonthShort = (dateStr) => {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { month: 'short' });
  } catch {
    return '';
  }
};

const formatFullDate = (dateStr) => {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  } catch {
    return dateStr;
  }
};

watch(
  () => props.summary?.daily_chart,
  (chart) => {
    if (chart && chart.length > 0) {
      const active = [...chart].reverse().find(d => (d.used + d.damaged) > 0) || chart[chart.length - 1];
      activeDay.value = active;
      scrollToRight();
    }
  },
  { immediate: true }
);

onMounted(() => {
  scrollToRight();
});

const currentStockStatus = computed(() => {
  return props.summary?.stock?.status || props.summary?.stock?.availability_status || 'STOK KOSONG';
});

const todayUsed = computed(() => {
  return props.summary?.metrics?.today_used ?? props.summary?.today?.used ?? 0;
});

const todayDamaged = computed(() => {
  return props.summary?.metrics?.today_damaged ?? props.summary?.today?.damaged ?? 0;
});

const monthReceived = computed(() => {
  return props.summary?.metrics?.month_received ?? props.summary?.this_month?.received ?? 0;
});

const monthUsed = computed(() => {
  return props.summary?.metrics?.month_used ?? props.summary?.this_month?.used ?? 0;
});

const monthDamaged = computed(() => {
  return props.summary?.metrics?.month_damaged ?? props.summary?.this_month?.damaged ?? 0;
});

const operatorsReported = computed(() => {
  return props.summary?.operators_status?.reported_today ?? props.summary?.today?.operators_reported ?? 0;
});

const operatorsPending = computed(() => {
  return props.summary?.operators_status?.pending_today ?? props.summary?.today?.operators_pending ?? 0;
});

const statusBadgeClasses = computed(() => {
  switch (currentStockStatus.value) {
    case 'TERSEDIA':
      return 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30';
    case 'PERSEDIAAN TERBATAS':
      return 'bg-amber-500/20 text-amber-300 border-amber-400/30';
    default:
      return 'bg-rose-500/20 text-rose-300 border-rose-400/30';
  }
});

const statusDotClasses = computed(() => {
  switch (currentStockStatus.value) {
    case 'TERSEDIA':
      return 'bg-emerald-400';
    case 'PERSEDIAAN TERBATAS':
      return 'bg-amber-400';
    default:
      return 'bg-rose-400';
  }
});

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
};

const getBarHeight = (val) => {
  if (!val || val <= 0) return 4;
  const list = displayedChart.value || [];
  const max = Math.max(...list.map(d => (d?.used || 0) + (d?.damaged || 0)), 50);
  return Math.min(100, Math.max(6, Math.round((val / max) * 100)));
};

const getTypeBadgeClass = (type) => {
  switch (type) {
    case 'RECEIPT':
      return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
    case 'USAGE':
      return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800';
    case 'DAMAGE':
      return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800';
    case 'ADJUSTMENT_ADD':
    case 'ADJUSTMENT_SUB':
      return 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800';
    case 'REVERSAL':
      return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
    default:
      return 'bg-gray-100 text-gray-700';
  }
};
</script>
