<template>
  <Head title="Laporan & Rekapitulasi Persediaan Blangko - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner (Hidden on print) -->
      <div class="print:hidden bg-gradient-to-r from-slate-900 via-sky-950 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-sky-900/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/20 text-sky-300 border border-sky-400/30 mb-2">
              <FileSpreadsheet class="w-4 h-4" />
              <span>Modul Rekapitulasi & Pelaporan Resmi</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Laporan Persediaan Blangko KTP-el
            </h1>
            <p class="text-xs sm:text-sm text-sky-200 mt-1 max-w-xl">
              Cetak dan ekspor rekapitulasi penggunaan, penerimaan, kerusakan blangko per operator untuk pertanggungjawaban dinas dan Ditjen Dukcapil.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2.5">
            <button
              @click="printReport"
              class="px-4 py-2.5 rounded-xl bg-white hover:bg-gray-100 text-slate-900 font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Berkas (Print)</span>
            </button>

            <a
              :href="exportUrl"
              class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
            >
              <Download class="w-4 h-4" />
              <span>Ekspor CSV (Excel)</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Report Type Navigation Tabs (Hidden on print) -->
      <div class="print:hidden bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-2 shadow-sm flex flex-wrap gap-1.5">
        <button
          v-for="tab in reportTabs"
          :key="tab.id"
          @click="selectTab(tab.id)"
          class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer"
          :class="reportType === tab.id ? 'bg-sky-600 text-white shadow-sm' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800'"
        >
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <!-- Filters (Hidden on print) -->
      <div class="print:hidden bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm">
        <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row items-center gap-3">
          <div class="flex items-center gap-2 flex-1 w-full sm:w-auto">
            <label class="text-xs font-bold text-gray-500 shrink-0">Rentang:</label>
            <input
              v-model="filterForm.start_date"
              type="date"
              class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-sky-500"
            />
            <span class="text-xs text-gray-400">-</span>
            <input
              v-model="filterForm.end_date"
              type="date"
              class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-sky-500"
            />
          </div>

          <div v-if="['daily_usage', 'operator_usage', 'damage'].includes(reportType)" class="w-full sm:w-56">
            <select
              v-model="filterForm.operator_id"
              class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-sky-500"
            >
              <option value="">Semua Operator</option>
              <option v-for="op in operators" :key="op.id" :value="op.id">
                {{ op.name }}
              </option>
            </select>
          </div>

          <div class="flex gap-2 w-full sm:w-auto">
            <button
              type="submit"
              class="flex-1 sm:flex-initial px-5 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white font-bold text-xs transition cursor-pointer"
            >
              Tampilkan
            </button>
          </div>
        </form>
      </div>

      <!-- Printable Report View -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm print:p-0 print:border-none print:shadow-none space-y-6">
        <!-- Official Print Header (Only visible on print or nicely rendered) -->
        <div class="border-b-2 border-gray-900 pb-4 text-center space-y-1">
          <h2 class="text-base sm:text-lg font-black uppercase text-gray-900 dark:text-zinc-100">
            Pemerintah Kabupaten Dompu
          </h2>
          <h3 class="text-sm sm:text-base font-extrabold uppercase text-gray-800 dark:text-zinc-200">
            Dinas Kependudukan dan Pencatatan Sipil
          </h3>
          <p class="text-xs text-gray-600 dark:text-zinc-400">
            Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211 | Email: dukcapilkabdompu@gmail.com
          </p>
          <div class="pt-2">
            <span class="inline-block px-3 py-0.5 rounded-full bg-gray-100 dark:bg-zinc-800 text-[11px] font-black uppercase tracking-wider text-gray-800 dark:text-zinc-200">
              {{ currentTabTitle }}
            </span>
            <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1">
              Periode: <strong>{{ formatDateDisplay(filters.start_date) }}</strong> s/d <strong>{{ formatDateDisplay(filters.end_date) }}</strong> | Saldo Saat Ini: <strong>{{ formatNumber(currentStock) }} keping</strong>
            </p>
          </div>
        </div>

        <!-- Dynamic Report Table -->
        <div class="overflow-x-auto">
          <!-- Type 1: daily_usage -->
          <table v-if="reportType === 'daily_usage'" class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 font-extrabold text-[11px] uppercase">
              <tr>
                <th class="py-2.5 px-3">No. Laporan</th>
                <th class="py-2.5 px-3">Tanggal</th>
                <th class="py-2.5 px-3">Operator</th>
                <th class="py-2.5 px-3">Sesi</th>
                <th class="py-2.5 px-3 text-right">Digunakan</th>
                <th class="py-2.5 px-3 text-right">Rusak</th>
                <th class="py-2.5 px-3 text-right">Total Keping</th>
                <th class="py-2.5 px-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="reportRows.length === 0">
                <td colspan="8" class="text-center py-8 text-gray-400">Tidak ada data untuk periode ini.</td>
              </tr>
              <tr v-for="row in reportRows" :key="row.id" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/40">
                <td class="py-2.5 px-3 font-mono font-bold">{{ row.report_number }}</td>
                <td class="py-2.5 px-3">{{ row.usage_date }}</td>
                <td class="py-2.5 px-3 font-semibold">{{ row.operator_name }}</td>
                <td class="py-2.5 px-3">{{ row.session_name }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-blue-600 dark:text-blue-400">{{ formatNumber(row.quantity_used) }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">{{ formatNumber(row.quantity_damaged) }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-black text-gray-900 dark:text-zinc-100">{{ formatNumber(row.total_quantity) }}</td>
                <td class="py-2.5 px-3 uppercase text-[10px] font-bold">
                  <span class="px-2 py-0.5 rounded-full border text-[9px]" :class="getStatusBadgeClass(row.status)">
                    {{ row.status_label || row.status }}
                  </span>
                </td>
              </tr>
            </tbody>
            <tfoot v-if="reportRows.length > 0" class="bg-gray-50/80 dark:bg-zinc-800/70 font-extrabold border-t-2 border-gray-300 dark:border-zinc-700">
              <tr>
                <td colspan="4" class="py-3 px-3 text-right text-gray-700 dark:text-zinc-200 uppercase tracking-wider text-[11px]">
                  TOTAL PENGGUNAAN PERIODE INI:
                </td>
                <td class="py-3 px-3 text-right font-mono text-blue-600 dark:text-blue-400 font-black">
                  {{ formatNumber(reportSummary.total_used) }}
                </td>
                <td class="py-3 px-3 text-right font-mono text-rose-600 dark:text-rose-400 font-black">
                  {{ formatNumber(reportSummary.total_damaged) }}
                </td>
                <td class="py-3 px-3 text-right font-mono text-gray-900 dark:text-zinc-100 font-black">
                  {{ formatNumber(reportSummary.grand_total) }}
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>

          <!-- Type 2: receipts -->
          <table v-else-if="reportType === 'receipts'" class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 font-extrabold text-[11px] uppercase">
              <tr>
                <th class="py-2.5 px-3">No. Penerimaan</th>
                <th class="py-2.5 px-3">Tanggal Terima</th>
                <th class="py-2.5 px-3 text-right">Jumlah</th>
                <th class="py-2.5 px-3">Sumber</th>
                <th class="py-2.5 px-3">No. BAST</th>
                <th class="py-2.5 px-3">Penerima</th>
                <th class="py-2.5 px-3 text-right">Saldo Sesudah</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="reportRows.length === 0">
                <td colspan="7" class="text-center py-8 text-gray-400">Tidak ada data penerimaan untuk periode ini.</td>
              </tr>
              <tr v-for="row in reportRows" :key="row.id" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/40">
                <td class="py-2.5 px-3 font-mono font-bold">{{ row.receipt_number }}</td>
                <td class="py-2.5 px-3">{{ row.received_date }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-600 dark:text-emerald-400">+{{ formatNumber(row.quantity) }}</td>
                <td class="py-2.5 px-3">{{ row.source }}</td>
                <td class="py-2.5 px-3 font-mono">{{ row.document_number }}</td>
                <td class="py-2.5 px-3 font-semibold">{{ row.receiver_name }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold">{{ formatNumber(row.balance_after) }}</td>
              </tr>
            </tbody>
            <tfoot v-if="reportRows.length > 0" class="bg-gray-50/80 dark:bg-zinc-800/70 font-extrabold border-t-2 border-gray-300 dark:border-zinc-700">
              <tr>
                <td colspan="2" class="py-3 px-3 text-right text-gray-700 dark:text-zinc-200 uppercase tracking-wider text-[11px]">
                  TOTAL PENERIMAAN:
                </td>
                <td class="py-3 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-black">
                  +{{ formatNumber(reportSummary.total_quantity) }}
                </td>
                <td colspan="4"></td>
              </tr>
            </tfoot>
          </table>

          <!-- Type 3: operator_usage -->
          <table v-else-if="reportType === 'operator_usage'" class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 font-extrabold text-[11px] uppercase">
              <tr>
                <th class="py-2.5 px-3">Nama Operator</th>
                <th class="py-2.5 px-3 text-center">Jumlah Laporan</th>
                <th class="py-2.5 px-3 text-right">Total Berhasil Digunakan</th>
                <th class="py-2.5 px-3 text-right">Total Rusak</th>
                <th class="py-2.5 px-3 text-right">Total Keping Keluar</th>
                <th class="py-2.5 px-3 text-right">Rasio Kerusakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="reportRows.length === 0">
                <td colspan="6" class="text-center py-8 text-gray-400">Tidak ada data pemakaian operator.</td>
              </tr>
              <tr v-for="(op, idx) in reportRows" :key="idx" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/40">
                <td class="py-2.5 px-3 font-bold">{{ op.operator_name }}</td>
                <td class="py-2.5 px-3 text-center font-mono">{{ formatNumber(op.report_count) }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-blue-600 dark:text-blue-400">{{ formatNumber(op.total_used) }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">{{ formatNumber(op.total_damaged) }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-black text-gray-900 dark:text-zinc-100">{{ formatNumber(op.total_out) }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold">
                  {{ op.damage_ratio }}%
                </td>
              </tr>
            </tbody>
            <tfoot v-if="reportRows.length > 0" class="bg-gray-50/80 dark:bg-zinc-800/70 font-extrabold border-t-2 border-gray-300 dark:border-zinc-700">
              <tr>
                <td class="py-3 px-3 text-right text-gray-700 dark:text-zinc-200 uppercase tracking-wider text-[11px]">
                  REKAP TOTAL:
                </td>
                <td class="py-3 px-3 text-center font-mono font-bold">{{ formatNumber(reportSummary.total_reports) }}</td>
                <td class="py-3 px-3 text-right font-mono text-blue-600 dark:text-blue-400 font-black">{{ formatNumber(reportSummary.total_used) }}</td>
                <td class="py-3 px-3 text-right font-mono text-rose-600 dark:text-rose-400 font-black">{{ formatNumber(reportSummary.total_damaged) }}</td>
                <td class="py-3 px-3 text-right font-mono text-gray-900 dark:text-zinc-100 font-black">{{ formatNumber(reportSummary.grand_total) }}</td>
                <td class="py-3 px-3 text-right font-mono font-black">{{ reportSummary.overall_ratio }}%</td>
              </tr>
            </tfoot>
          </table>

          <!-- Type 4: damage -->
          <table v-else-if="reportType === 'damage'" class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 font-extrabold text-[11px] uppercase">
              <tr>
                <th class="py-2.5 px-3">No. Laporan</th>
                <th class="py-2.5 px-3">Tanggal</th>
                <th class="py-2.5 px-3">Operator</th>
                <th class="py-2.5 px-3 text-right">Jumlah Rusak</th>
                <th class="py-2.5 px-3">Alasan Kerusakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="reportRows.length === 0">
                <td colspan="5" class="text-center py-8 text-gray-400">Tidak ada rekaman blangko rusak pada periode ini.</td>
              </tr>
              <tr v-for="row in reportRows" :key="row.id" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/40">
                <td class="py-2.5 px-3 font-mono font-bold">{{ row.report_number }}</td>
                <td class="py-2.5 px-3">{{ row.usage_date }}</td>
                <td class="py-2.5 px-3 font-semibold">{{ row.operator_name }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">{{ formatNumber(row.quantity_damaged) }}</td>
                <td class="py-2.5 px-3 text-gray-700 dark:text-zinc-300">
                  {{ row.damage_reason }}
                  <a v-if="row.damage_proof_url" :href="row.damage_proof_url" target="_blank" class="ml-2 text-sky-500 hover:underline text-[11px] print:hidden">
                    [Lihat Foto]
                  </a>
                </td>
              </tr>
            </tbody>
            <tfoot v-if="reportRows.length > 0" class="bg-gray-50/80 dark:bg-zinc-800/70 font-extrabold border-t-2 border-gray-300 dark:border-zinc-700">
              <tr>
                <td colspan="3" class="py-3 px-3 text-right text-gray-700 dark:text-zinc-200 uppercase tracking-wider text-[11px]">
                  TOTAL BLANGKO RUSAK:
                </td>
                <td class="py-3 px-3 text-right font-mono text-rose-600 dark:text-rose-400 font-black">
                  {{ formatNumber(reportSummary.total_damaged) }}
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>

          <!-- Type 5: ledger -->
          <table v-else-if="reportType === 'ledger'" class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 font-extrabold text-[11px] uppercase">
              <tr>
                <th class="py-2.5 px-3">No. Transaksi</th>
                <th class="py-2.5 px-3">Tanggal</th>
                <th class="py-2.5 px-3">Jenis Mutasi</th>
                <th class="py-2.5 px-3 text-right">Masuk (+)</th>
                <th class="py-2.5 px-3 text-right">Keluar (-)</th>
                <th class="py-2.5 px-3 text-right">Saldo Akhir</th>
                <th class="py-2.5 px-3">Petugas</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="reportRows.length === 0">
                <td colspan="7" class="text-center py-8 text-gray-400">Tidak ada riwayat mutasi kartu persediaan.</td>
              </tr>
              <tr v-for="t in reportRows" :key="t.id" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/40">
                <td class="py-2.5 px-3 font-mono font-bold">{{ t.transaction_number }}</td>
                <td class="py-2.5 px-3">{{ t.transaction_date }}</td>
                <td class="py-2.5 px-3">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gray-100 dark:bg-zinc-800 text-gray-800 dark:text-zinc-200">
                    {{ t.type_label || t.transaction_type }}
                  </span>
                </td>
                <td class="py-2.5 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                  {{ t.qty_in > 0 ? '+' + formatNumber(t.qty_in) : '-' }}
                </td>
                <td class="py-2.5 px-3 text-right font-mono text-rose-600 dark:text-rose-400 font-bold">
                  {{ t.total_out > 0 ? '-' + formatNumber(t.total_out) : '-' }}
                </td>
                <td class="py-2.5 px-3 text-right font-mono font-black text-gray-900 dark:text-zinc-100">{{ formatNumber(t.balance_after) }}</td>
                <td class="py-2.5 px-3 font-semibold">{{ t.actor_name }}</td>
              </tr>
            </tbody>
            <tfoot v-if="reportRows.length > 0" class="bg-gray-50/80 dark:bg-zinc-800/70 font-extrabold border-t-2 border-gray-300 dark:border-zinc-700">
              <tr>
                <td colspan="3" class="py-3 px-3 text-right text-gray-700 dark:text-zinc-200 uppercase tracking-wider text-[11px]">
                  TOTAL MUTASI PERIODE INI:
                </td>
                <td class="py-3 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-black">
                  +{{ formatNumber(reportSummary.total_in) }}
                </td>
                <td class="py-3 px-3 text-right font-mono text-rose-600 dark:text-rose-400 font-black">
                  -{{ formatNumber(reportSummary.total_out) }}
                </td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          </table>

          <!-- Type 6: opname -->
          <table v-else-if="reportType === 'opname'" class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 font-extrabold text-[11px] uppercase">
              <tr>
                <th class="py-2.5 px-3">No. Berita Acara</th>
                <th class="py-2.5 px-3">Tanggal</th>
                <th class="py-2.5 px-3 text-right">Stok Sistem</th>
                <th class="py-2.5 px-3 text-right">Fisik Terhitung</th>
                <th class="py-2.5 px-3 text-right">Selisih</th>
                <th class="py-2.5 px-3">Pemeriksa</th>
                <th class="py-2.5 px-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="reportRows.length === 0">
                <td colspan="7" class="text-center py-8 text-gray-400">Tidak ada berita acara opname untuk periode ini.</td>
              </tr>
              <tr v-for="op in reportRows" :key="op.id" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/40">
                <td class="py-2.5 px-3 font-mono font-bold">{{ op.opname_number }}</td>
                <td class="py-2.5 px-3">{{ op.opname_date }}</td>
                <td class="py-2.5 px-3 text-right font-mono">{{ formatNumber(op.system_stock) }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold">{{ formatNumber(op.physical_stock) }}</td>
                <td class="py-2.5 px-3 text-right font-mono font-black" :class="op.difference < 0 ? 'text-rose-600 dark:text-rose-400' : (op.difference > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500')">
                  {{ op.difference > 0 ? '+' : '' }}{{ formatNumber(op.difference) }}
                </td>
                <td class="py-2.5 px-3 font-semibold">{{ op.checker_name }}</td>
                <td class="py-2.5 px-3 uppercase text-[10px] font-bold">
                  <span class="px-2 py-0.5 rounded-full border text-[9px]" :class="op.status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                    {{ op.status_label || op.status }}
                  </span>
                </td>
              </tr>
            </tbody>
            <tfoot v-if="reportRows.length > 0" class="bg-gray-50/80 dark:bg-zinc-800/70 font-extrabold border-t-2 border-gray-300 dark:border-zinc-700">
              <tr>
                <td colspan="2" class="py-3 px-3 text-right text-gray-700 dark:text-zinc-200 uppercase tracking-wider text-[11px]">
                  TOTAL BERITA ACARA: {{ formatNumber(reportSummary.total_opnames) }}
                </td>
                <td colspan="2" class="py-3 px-3 text-right text-gray-700 dark:text-zinc-200 uppercase tracking-wider text-[11px]">
                  TOTAL AKUMULASI SELISIH FISIK:
                </td>
                <td class="py-3 px-3 text-right font-mono font-black" :class="(reportSummary.total_difference || 0) < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'">
                  {{ (reportSummary.total_difference || 0) > 0 ? '+' : '' }}{{ formatNumber(reportSummary.total_difference) }}
                </td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Official Signatures (Visible on Print) -->
        <div class="hidden print:grid grid-cols-2 gap-8 pt-12 text-center text-xs">
          <div>
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala Dinas Dukcapil Kabupaten Dompu</p>
            <div class="h-20"></div>
            <p class="font-extrabold underline">( ........................................................... )</p>
            <p class="text-[10px]">NIP. ....................................................</p>
          </div>
          <div>
            <p>Dompu, {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
            <p class="font-bold">Bendahara Barang / Pengurus Persediaan</p>
            <div class="h-20"></div>
            <p class="font-extrabold underline">( {{ $page.props.auth?.user?.name || '...........................................................' }} )</p>
            <p class="text-[10px]">NIP. ....................................................</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FileSpreadsheet, Printer, Download } from '@lucide/vue';

const props = defineProps({
  reportType: {
    type: String,
    default: 'daily_usage',
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  data: {
    type: [Array, Object],
    default: () => ({ rows: [], summary: {} }),
  },
  operators: {
    type: Array,
    default: () => [],
  },
  currentStock: {
    type: Number,
    default: 0,
  },
});

const reportRows = computed(() => {
  if (Array.isArray(props.data)) return props.data;
  return props.data?.rows || [];
});

const reportSummary = computed(() => {
  if (Array.isArray(props.data)) return {};
  return props.data?.summary || props.data || {};
});

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'draft':
      return 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 border-gray-200 dark:border-zinc-700';
    case 'submitted':
      return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800';
    case 'approved':
      return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
    case 'reversed':
      return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800 line-through';
    default:
      return 'bg-gray-100 text-gray-700 border-gray-200';
  }
};

const reportTabs = [
  { id: 'daily_usage', label: 'Penggunaan Harian' },
  { id: 'receipts', label: 'Penerimaan BAST' },
  { id: 'operator_usage', label: 'Rekap Operator' },
  { id: 'damage', label: 'Kerusakan Blangko' },
  { id: 'ledger', label: 'Buku Kartu Persediaan' },
  { id: 'opname', label: 'Stok Opname Fisik' },
];

const currentTabTitle = computed(() => {
  const match = reportTabs.find(t => t.id === props.reportType);
  return match ? `Laporan Rekapitulasi ${match.label}` : 'Laporan Persediaan Blangko KTP-el';
});

const filterForm = ref({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
  operator_id: props.filters.operator_id || '',
});

const selectTab = (tabId) => {
  router.get(route('admin.blank-stocks.reports.index'), {
    type: tabId,
    start_date: filterForm.value.start_date,
    end_date: filterForm.value.end_date,
    operator_id: filterForm.value.operator_id,
  }, { preserveState: true, replace: true });
};

const applyFilters = () => {
  router.get(route('admin.blank-stocks.reports.index'), {
    type: props.reportType,
    start_date: filterForm.value.start_date,
    end_date: filterForm.value.end_date,
    operator_id: filterForm.value.operator_id,
  }, { preserveState: true, replace: true });
};

const exportUrl = computed(() => {
  const params = new URLSearchParams({
    type: props.reportType,
    start_date: filterForm.value.start_date,
    end_date: filterForm.value.end_date,
    operator_id: filterForm.value.operator_id || '',
  });
  return `${route('admin.blank-stocks.reports.export')}?${params.toString()}`;
});

const printReport = () => {
  window.print();
};

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
};

const formatDateDisplay = (dateStr) => {
  if (!dateStr) return '-';
  try {
    const parts = dateStr.split('-');
    if (parts.length === 3) {
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateStr;
  } catch (e) {
    return dateStr;
  }
};
</script>
