<template>
  <Head title="Laporan Penggunaan Blangko KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-blue-950 via-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-blue-800/40">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30 mb-2">
              <ClipboardList class="w-4 h-4" />
              <span>Modul Multi-Operator Cetak Terverifikasi</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Laporan Penggunaan Blangko
            </h1>
            <p class="text-xs sm:text-sm text-blue-200 mt-1 max-w-xl">
              Pencatatan real-time hasil cetak KTP-el berhasil dan kartu rusak per operator dengan verifikasi saldo atomic anti-race condition.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <button
              v-if="canCreate"
              @click="showCreateModal = true"
              class="px-5 py-3 rounded-2xl bg-blue-500 hover:bg-blue-400 text-slate-950 font-black text-xs shadow-lg shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer active:scale-95"
            >
              <Plus class="w-4 h-4 stroke-[3]" />
              <span>Input Laporan Penggunaan</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          <div class="relative lg:col-span-2">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" />
            <input
              v-model="filterForm.search"
              type="text"
              placeholder="Cari nomor laporan, operator, alasan rusak..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Filter Operator (Supervisor / Admin only) -->
          <div v-if="canViewAll">
            <select
              v-model="filterForm.operator_id"
              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Semua Operator Cetak</option>
              <option v-for="op in operators" :key="op.id" :value="op.id">
                {{ op.name }}
              </option>
            </select>
          </div>

          <!-- Filter Status -->
          <div>
            <select
              v-model="filterForm.status"
              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Semua Status</option>
              <option value="draft">Draf</option>
              <option value="submitted">Diajukan</option>
              <option value="approved">Disetujui</option>
              <option value="reversed">Dibatalkan</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="submit"
              class="flex-1 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white font-bold text-xs transition cursor-pointer"
            >
              Filter
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-400 font-bold text-xs transition cursor-pointer"
            >
              Reset
            </button>
          </div>
        </form>
      </div>

      <!-- Reports Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 tracking-tight">
            Daftar Laporan Penggunaan
          </h3>
          <span class="text-xs font-bold text-gray-400">
            Total {{ reports.total }} Laporan Tercatat
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 font-extrabold text-[11px] uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4 rounded-l-xl">No. Laporan</th>
                <th class="py-3 px-3">Tanggal / Sesi</th>
                <th class="py-3 px-3">Operator Cetak</th>
                <th class="py-3 px-3 text-right">Digunakan</th>
                <th class="py-3 px-3 text-right">Rusak</th>
                <th class="py-3 px-3 text-right">Total Keping</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-4 rounded-r-xl text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="reports.data.length === 0">
                <td colspan="8" class="text-center py-10 text-gray-400">
                  Belum ada data laporan penggunaan blangko.
                </td>
              </tr>
              <tr
                v-for="rep in reports.data"
                :key="rep.id"
                class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 transition"
              >
                <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ rep.report_number }}
                </td>
                <td class="py-3.5 px-3 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                  {{ rep.usage_date }}
                  <span class="block text-[10px] text-gray-400 font-medium">{{ rep.session_name }}</span>
                </td>
                <td class="py-3.5 px-3 text-gray-900 dark:text-zinc-100 font-bold whitespace-nowrap">
                  {{ rep.operator_name }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-bold text-blue-600 dark:text-blue-400 whitespace-nowrap">
                  {{ formatNumber(rep.quantity_used) }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-bold whitespace-nowrap" :class="rep.quantity_damaged > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400'">
                  {{ formatNumber(rep.quantity_damaged) }}
                  <span v-if="rep.damage_proof_url" class="inline-block ml-1">
                    <a :href="rep.damage_proof_url" target="_blank" title="Lihat Foto Bukti Kerusakan" class="text-sky-500 hover:underline">📷</a>
                  </span>
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ formatNumber(rep.total_quantity) }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border"
                    :class="getStatusBadgeClass(rep.status)"
                  >
                    {{ rep.status_label }}
                  </span>
                  <span v-if="rep.approver_name" class="block text-[9.5px] text-gray-400 mt-0.5">
                    Oleh: {{ rep.approver_name }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Detail Button (Always visible) -->
                    <button
                      @click="openDetailModal(rep)"
                      class="px-2.5 py-1.5 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/60 font-bold text-xs flex items-center gap-1 border border-sky-200 dark:border-sky-800 transition cursor-pointer shadow-2xs active:scale-95"
                      title="Lihat Rincian Laporan Lengkap"
                    >
                      <Eye class="w-3.5 h-3.5" />
                      <span>Detail</span>
                    </button>

                    <!-- Edit Button -->
                    <button
                      v-if="canEditReport(rep)"
                      @click="openEditModal(rep)"
                      class="px-2.5 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/60 font-bold text-xs flex items-center gap-1 border border-amber-200 dark:border-amber-800 transition cursor-pointer shadow-2xs active:scale-95"
                      title="Ubah Laporan Penggunaan"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                      <span>Edit</span>
                    </button>

                    <!-- Ajukan Draf (jika milik operator login & status draft) -->
                    <button
                      v-if="rep.status === 'draft' && (rep.operator_id === $page.props.auth?.user?.id || isSuperOrAdmin)"
                      @click="submitDraftReport(rep)"
                      class="px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs flex items-center gap-1 transition cursor-pointer shadow-xs active:scale-95"
                      title="Ajukan Laporan ke Pimpinan"
                    >
                      <Send class="w-3.5 h-3.5" />
                      <span>Ajukan</span>
                    </button>

                    <!-- Approve (Supervisor / Admin) -->
                    <button
                      v-if="rep.status === 'submitted' && (canApprove || isSuperOrAdmin)"
                      @click="approveReport(rep)"
                      class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1 transition cursor-pointer shadow-xs active:scale-95"
                      title="Sahkan Laporan Penggunaan"
                    >
                      <Check class="w-3.5 h-3.5" />
                      <span>Setujui</span>
                    </button>

                    <!-- Batalkan / Reversal (Supervisor / Admin) -->
                    <button
                      v-if="['submitted', 'approved'].includes(rep.status) && (canApprove || isSuperOrAdmin)"
                      @click="openReversalModal(rep)"
                      class="px-2.5 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 font-bold text-xs border border-rose-200 dark:border-rose-900 transition cursor-pointer shadow-2xs active:scale-95"
                      title="Batalkan Transaksi (Reversal)"
                    >
                      <RotateCcw class="w-3.5 h-3.5" />
                      <span>Batal (Reversal)</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="reports.links && reports.links.length > 3" class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs">
          <span class="text-gray-400">
            Menampilkan {{ reports.from || 0 }} - {{ reports.to || 0 }} dari {{ reports.total }}
          </span>
          <div class="flex gap-1">
            <Link
              v-for="(link, idx) in reports.links"
              :key="idx"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
              :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 border-gray-200 dark:border-zinc-700 hover:bg-gray-50'"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Create Usage Report Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl space-y-5">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
          <div>
            <h3 class="text-lg font-black text-gray-900 dark:text-zinc-100">
              Input Laporan Penggunaan Blangko
            </h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
              Operator: <strong class="text-blue-600 dark:text-blue-400">{{ $page.props.auth?.user?.name }}</strong> | Saldo Gudang: <strong>{{ formatNumber(currentStock) }}</strong> keping
            </p>
          </div>
          <button
            @click="showCreateModal = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 p-1 rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitUsageReport" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Tanggal Pemakaian -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Tanggal Pemakaian *
              </label>
              <input
                v-model="usageForm.usage_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Nama Sesi -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Sesi / Kategori Layanan *
              </label>
              <input
                v-model="usageForm.session_name"
                type="text"
                required
                placeholder="Contoh: Sesi Pelayanan Hari Ini"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Jumlah Berhasil Digunakan -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Berhasil Dicetak (Keping) *
              </label>
              <input
                v-model.number="usageForm.quantity_used"
                type="number"
                min="0"
                required
                placeholder="0"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Jumlah Rusak -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Rusak / Gagal Cetak (Keping) *
              </label>
              <input
                v-model.number="usageForm.quantity_damaged"
                type="number"
                min="0"
                required
                placeholder="0"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <!-- Total Kebutuhan Kalkulasi Otomatis -->
          <div class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900 flex items-center justify-between text-xs">
            <span class="text-blue-800 dark:text-blue-200 font-semibold">Total Keping Dikurangkan dari Stok:</span>
            <span class="font-mono font-black text-base text-blue-900 dark:text-blue-100">
              {{ (usageForm.quantity_used || 0) + (usageForm.quantity_damaged || 0) }} keping
            </span>
          </div>

          <!-- Alasan Kerusakan (wajib jika rusak > 0) -->
          <div v-if="usageForm.quantity_damaged > 0" class="space-y-3 animate-fade-in">
            <div>
              <label class="block text-xs font-bold text-rose-600 dark:text-rose-400 mb-1">
                Alasan Kerusakan Blangko *
              </label>
              <input
                v-model="usageForm.damage_reason"
                type="text"
                required
                placeholder="Contoh: Chip tidak terbaca / pita ribbon robek / kartu tergores"
                class="w-full px-3.5 py-2.5 rounded-xl border border-rose-300 dark:border-rose-800 bg-rose-50/50 dark:bg-rose-950/40 text-xs text-rose-950 dark:text-rose-200 focus:outline-none focus:ring-2 focus:ring-rose-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Unggah Foto Bukti Kartu Rusak (Maks 5MB)
              </label>
              <input
                type="file"
                accept="image/*"
                @change="handleProofUpload"
                class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer"
              />
            </div>
          </div>

          <!-- Keterangan Tambahan -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Catatan / Keterangan (Opsional)
            </label>
            <textarea
              v-model="usageForm.notes"
              rows="2"
              placeholder="Catatan tambahan pelayanan hari ini..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div class="flex justify-between items-center pt-3 border-t border-gray-100 dark:border-zinc-800">
            <button
              type="button"
              @click="submitAsDraft"
              :disabled="usageForm.processing"
              class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800 text-xs font-bold transition cursor-pointer"
            >
              Simpan Sebagai Draf
            </button>

            <div class="flex gap-2">
              <button
                type="button"
                @click="showCreateModal = false"
                class="px-4 py-2.5 rounded-xl text-gray-500 hover:text-gray-700 dark:hover:text-zinc-300 text-xs font-bold transition cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="usageForm.processing"
                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-black text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
              >
                <span v-if="usageForm.processing">Menyimpan...</span>
                <span v-else>Kirim Laporan Resmi</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Reversal Reason Modal (Supervisor only) -->
    <div
      v-if="showReversalModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4">
        <h3 class="text-base font-black text-rose-600 dark:text-rose-400 flex items-center gap-2">
          <AlertTriangle class="w-5 h-5 shrink-0" />
          <span>Batalkan / Reversal Laporan Penggunaan</span>
        </h3>
        <p class="text-xs text-gray-600 dark:text-zinc-400 leading-relaxed">
          Mekanisme reversal akan membuat transaksi pembalik kartu persediaan dan mengembalikan stok sebanyak <strong>{{ selectedReport?.total_quantity }} keping</strong> ke saldo resmi.
        </p>

        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
            Alasan Pembatalan Resmi *
          </label>
          <textarea
            v-model="reversalReason"
            rows="3"
            required
            placeholder="Jelaskan alasan pembatalan atau koreksi transaksi ini..."
            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-rose-500"
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            @click="showReversalModal = false"
            class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:text-gray-700"
          >
            Batal
          </button>
          <button
            type="button"
            @click="executeReversal"
            :disabled="!reversalReason"
            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white font-bold text-xs shadow-md transition"
          >
            Konfirmasi Reversal
          </button>
        </div>
      </div>
    </div>

    <!-- Detail Usage Report Modal -->
    <div
      v-if="showDetailModal && selectedReport"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs animate-fade-in overflow-y-auto"
    >
      <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-5 sm:p-8 max-w-2xl w-full shadow-2xl space-y-5 max-h-[92vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center border border-sky-100 dark:border-sky-900">
              <ClipboardList class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-zinc-100">
                  Rincian Laporan Penggunaan
                </h3>
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border"
                  :class="getStatusBadgeClass(selectedReport.status)"
                >
                  {{ selectedReport.status_label }}
                </span>
              </div>
              <p class="text-xs font-mono font-bold text-gray-500 dark:text-zinc-400 mt-0.5">
                {{ selectedReport.report_number }}
              </p>
            </div>
          </div>
          <button
            @click="showDetailModal = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Metric Numbers Highlight -->
        <div class="grid grid-cols-3 gap-3">
          <div class="p-3 sm:p-4 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/60 text-center">
            <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider block">Digunakan (Cetak)</span>
            <span class="text-xl sm:text-2xl font-black font-mono text-blue-700 dark:text-blue-300 block mt-0.5">
              {{ formatNumber(selectedReport.quantity_used) }}
            </span>
            <span class="text-[9px] text-blue-500 font-semibold">Keping Berhasil</span>
          </div>

          <div class="p-3 sm:p-4 rounded-2xl bg-rose-50/60 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/60 text-center">
            <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider block">Blangko Rusak</span>
            <span class="text-xl sm:text-2xl font-black font-mono text-rose-700 dark:text-rose-300 block mt-0.5">
              {{ formatNumber(selectedReport.quantity_damaged) }}
            </span>
            <span class="text-[9px] text-rose-500 font-semibold">Keping Rusak/Cacat</span>
          </div>

          <div class="p-3 sm:p-4 rounded-2xl bg-slate-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 text-center">
            <span class="text-[10px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider block">Total Keluar</span>
            <span class="text-xl sm:text-2xl font-black font-mono text-gray-900 dark:text-zinc-100 block mt-0.5">
              {{ formatNumber(selectedReport.total_quantity) }}
            </span>
            <span class="text-[9px] text-gray-400 font-semibold">Keping Mutasi</span>
          </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-gray-50/50 dark:bg-zinc-800/40 p-4 rounded-2xl border border-gray-100 dark:border-zinc-800">
          <div>
            <span class="text-gray-400 block text-[11px]">Tanggal Penggunaan:</span>
            <span class="font-bold text-gray-800 dark:text-zinc-200">{{ selectedReport.usage_date }}</span>
          </div>
          <div>
            <span class="text-gray-400 block text-[11px]">Sesi Pelayanan:</span>
            <span class="font-bold text-gray-800 dark:text-zinc-200">{{ selectedReport.session_name || '-' }}</span>
          </div>
          <div>
            <span class="text-gray-400 block text-[11px]">Operator Pencatat:</span>
            <span class="font-bold text-gray-800 dark:text-zinc-200">{{ selectedReport.operator_name }}</span>
          </div>
          <div>
            <span class="text-gray-400 block text-[11px]">Waktu Input Sistem:</span>
            <span class="font-bold text-gray-800 dark:text-zinc-200">{{ selectedReport.created_at || '-' }}</span>
          </div>
          <div>
            <span class="text-gray-400 block text-[11px]">Saldo Persediaan Sebelum:</span>
            <span class="font-mono font-bold text-gray-800 dark:text-zinc-200">{{ selectedReport.balance_before !== null ? formatNumber(selectedReport.balance_before) + ' keping' : '-' }}</span>
          </div>
          <div>
            <span class="text-gray-400 block text-[11px]">Saldo Persediaan Sesudah:</span>
            <span class="font-mono font-bold text-gray-800 dark:text-zinc-200">{{ selectedReport.balance_after !== null ? formatNumber(selectedReport.balance_after) + ' keping' : '-' }}</span>
          </div>
        </div>

        <!-- Bukti Kerusakan & Alasan jika ada -->
        <div v-if="selectedReport.quantity_damaged > 0 || selectedReport.damage_reason" class="p-4 rounded-2xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200/60 dark:border-rose-900/40 space-y-3">
          <div class="flex items-center gap-2 text-rose-800 dark:text-rose-300 font-extrabold text-xs">
            <AlertTriangle class="w-4 h-4 text-rose-500" />
            <span>Keterangan Kerusakan Blangko</span>
          </div>
          <p class="text-xs text-rose-900 dark:text-rose-200 leading-relaxed">
            {{ selectedReport.damage_reason || 'Tidak ada catatan alasan kerusakan.' }}
          </p>
          <div v-if="selectedReport.damage_proof_url" class="pt-1">
            <span class="text-[11px] font-bold text-gray-500 dark:text-zinc-400 block mb-1.5">Foto Bukti Fisik Kerusakan:</span>
            <a :href="selectedReport.damage_proof_url" target="_blank" class="inline-block group relative rounded-xl overflow-hidden border border-rose-200 dark:border-rose-800 shadow-sm max-w-xs">
              <img :src="selectedReport.damage_proof_url" alt="Bukti Kerusakan" class="max-h-48 rounded-xl object-contain bg-white dark:bg-zinc-800" />
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                <ExternalLink class="w-4 h-4" />
                <span>Buka Ukuran Asli</span>
              </div>
            </a>
          </div>
        </div>

        <!-- Catatan Tambahan -->
        <div v-if="selectedReport.notes" class="text-xs">
          <span class="text-gray-400 block text-[11px] font-bold mb-0.5">Catatan Operasional:</span>
          <p class="p-3 rounded-xl bg-gray-50 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 leading-relaxed border border-gray-100 dark:border-zinc-800">
            {{ selectedReport.notes }}
          </p>
        </div>

        <!-- Approval Info -->
        <div v-if="selectedReport.approved_at" class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-xs flex items-center gap-2.5 text-emerald-800 dark:text-emerald-200">
          <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
          <div>
            <span class="font-bold">Telah disahkan oleh {{ selectedReport.approver_name || 'Supervisor' }}</span>
            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 block">pada {{ selectedReport.approved_at }}</span>
          </div>
        </div>

        <!-- Reversal Info -->
        <div v-if="selectedReport.status === 'reversed'" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-xs space-y-1 text-rose-800 dark:text-rose-200">
          <div class="flex items-center gap-2 font-bold">
            <RotateCcw class="w-4 h-4 text-rose-600 shrink-0" />
            <span>Laporan Dibatalkan (Reversal Stok)</span>
          </div>
          <p class="text-[11px] leading-relaxed text-rose-700 dark:text-rose-300">
            Alasan: {{ selectedReport.reversal_reason || 'Tidak ada rincian alasan reversal.' }}
          </p>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-zinc-800">
          <button
            type="button"
            @click="showDetailModal = false"
            class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold transition cursor-pointer"
          >
            Tutup
          </button>

          <div class="flex items-center gap-2">
            <button
              v-if="canEditReport(selectedReport)"
              @click="showDetailModal = false; openEditModal(selectedReport)"
              class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md transition flex items-center gap-1.5 cursor-pointer"
            >
              <Pencil class="w-3.5 h-3.5" />
              <span>Edit Laporan</span>
            </button>
            <button
              v-if="selectedReport.status === 'submitted' && (canApprove || isSuperOrAdmin)"
              @click="showDetailModal = false; approveReport(selectedReport)"
              class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-md transition flex items-center gap-1.5 cursor-pointer"
            >
              <Check class="w-3.5 h-3.5" />
              <span>Sahkan Laporan</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Usage Report Modal -->
    <div
      v-if="showEditModal && editingReport"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs animate-fade-in overflow-y-auto"
    >
      <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-5 sm:p-8 max-w-xl w-full shadow-2xl space-y-4 sm:space-y-5 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
          <div>
            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-zinc-100">
              Edit Laporan Penggunaan Blangko
            </h3>
            <p class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400 mt-0.5">
              {{ editingReport.report_number }}
            </p>
          </div>
          <button
            @click="showEditModal = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitEditForm" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Tanggal Pemakaian -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Tanggal Pemakaian *
              </label>
              <input
                v-model="editForm.usage_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Sesi Pelayanan -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Sesi Pelayanan
              </label>
              <input
                v-model="editForm.session_name"
                type="text"
                placeholder="Pagi / Siang / Pelayanan Harian"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Jumlah Berhasil Dicetak -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Blangko Sukses Dicetak *
              </label>
              <input
                v-model.number="editForm.quantity_used"
                type="number"
                min="0"
                required
                placeholder="0"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Jumlah Blangko Rusak -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Blangko Rusak / Cacat *
              </label>
              <input
                v-model.number="editForm.quantity_damaged"
                type="number"
                min="0"
                required
                placeholder="0"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-rose-500"
              />
            </div>
          </div>

          <!-- Total Calculation Banner -->
          <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 flex items-center justify-between text-xs">
            <span class="text-gray-600 dark:text-zinc-400 font-semibold">Total Pengurangan Stok:</span>
            <span class="font-mono font-black text-sm text-gray-900 dark:text-zinc-100">
              {{ (editForm.quantity_used || 0) + (editForm.quantity_damaged || 0) }} keping
            </span>
          </div>

          <!-- Alasan Kerusakan (Wajib jika damaged > 0) -->
          <div v-if="editForm.quantity_damaged > 0">
            <label class="block text-xs font-bold text-rose-700 dark:text-rose-400 mb-1">
              Alasan / Keterangan Kerusakan Blangko *
            </label>
            <textarea
              v-model="editForm.damage_reason"
              rows="2"
              required
              placeholder="Contoh: Film pita ribbon kusut, chip gagal terbaca printer..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-rose-500"
            ></textarea>
          </div>

          <!-- Unggah Bukti Kerusakan Baru (Opsional) -->
          <div v-if="editForm.quantity_damaged > 0">
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Ganti Berkas / Foto Bukti Fisik Rusak (Opsional, Maks 5MB)
            </label>
            <input
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              @change="handleEditProofUpload"
              class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
            />
            <p v-if="editingReport.damage_proof_url" class="text-[11px] text-gray-400 mt-1">
              Foto bukti saat ini: <a :href="editingReport.damage_proof_url" target="_blank" class="text-sky-500 underline">Lihat berkas tersimpan</a>
            </p>
          </div>

          <!-- Catatan Operasional -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Catatan Tambahan (Opsional)
            </label>
            <textarea
              v-model="editForm.notes"
              rows="2"
              placeholder="Catatan nomor seri atau kendala mesin..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-zinc-800">
            <button
              type="button"
              @click="showEditModal = false"
              class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="editForm.processing"
              class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-black text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
            >
              <span v-if="editForm.processing">Menyimpan Perubahan...</span>
              <span v-else>Simpan Perubahan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  ClipboardList,
  Plus,
  Search,
  X,
  AlertTriangle,
  Eye,
  Pencil,
  Send,
  Check,
  RotateCcw,
  ExternalLink,
  CheckCircle2
} from '@lucide/vue';

const props = defineProps({
  reports: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  canViewAll: {
    type: Boolean,
    default: false,
  },
  canApprove: {
    type: Boolean,
    default: false,
  },
  canCreate: {
    type: Boolean,
    default: true,
  },
  currentStock: {
    type: Number,
    default: 0,
  },
  operators: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const userRoles = computed(() => page.props.auth?.roles || []);
const isSuperAdmin = computed(() => page.props.auth?.is_super_admin || false);
const isSuperOrAdmin = computed(() =>
  isSuperAdmin.value ||
  userRoles.value.some(r => [
    'Super Administrator',
    'Administrator Utama',
    'Super Admin',
    'Supervisor Layanan KTP-el',
    'Supervisor Persediaan Blangko'
  ].includes(r))
);

const showCreateModal = ref(false);
const showDetailModal = ref(false);
const showEditModal = ref(false);
const showReversalModal = ref(false);

const selectedReport = ref(null);
const editingReport = ref(null);
const reversalReason = ref('');

const filterForm = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  operator_id: props.filters.operator_id || '',
});

const applyFilters = () => {
  router.get(route('admin.blank-stocks.usage.index'), {
    search: filterForm.value.search,
    status: filterForm.value.status,
    operator_id: filterForm.value.operator_id,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  filterForm.value.search = '';
  filterForm.value.status = '';
  filterForm.value.operator_id = '';
  applyFilters();
};

const usageForm = useForm({
  usage_date: new Date().toISOString().split('T')[0],
  session_name: 'Pelayanan Harian',
  quantity_used: '',
  quantity_damaged: 0,
  damage_reason: '',
  damage_proof: null,
  notes: '',
  is_draft: false,
});

const handleProofUpload = (e) => {
  usageForm.damage_proof = e.target.files[0];
};

const submitUsageReport = () => {
  usageForm.is_draft = false;
  usageForm.post(route('admin.blank-stocks.usage.store'), {
    onSuccess: () => {
      showCreateModal.value = false;
      usageForm.reset();
    },
  });
};

const submitAsDraft = () => {
  usageForm.is_draft = true;
  usageForm.post(route('admin.blank-stocks.usage.store'), {
    onSuccess: () => {
      showCreateModal.value = false;
      usageForm.reset();
    },
  });
};

// Edit Usage Report
const editForm = useForm({
  usage_date: '',
  session_name: '',
  quantity_used: 0,
  quantity_damaged: 0,
  damage_reason: '',
  damage_proof: null,
  notes: '',
});

const openDetailModal = (rep) => {
  selectedReport.value = rep;
  showDetailModal.value = true;
};

const canEditReport = (rep) => {
  if (!rep) return false;
  if (rep.status === 'reversed') return false;
  const isOwner = rep.operator_id === page.props.auth?.user?.id;
  return isOwner || isSuperOrAdmin.value || props.canApprove;
};

const openEditModal = (rep) => {
  editingReport.value = rep;
  editForm.usage_date = rep.raw_date || rep.usage_date || new Date().toISOString().split('T')[0];
  editForm.session_name = rep.session_name || '';
  editForm.quantity_used = rep.quantity_used || 0;
  editForm.quantity_damaged = rep.quantity_damaged || 0;
  editForm.damage_reason = rep.damage_reason || '';
  editForm.damage_proof = null;
  editForm.notes = rep.notes || '';
  showEditModal.value = true;
};

const handleEditProofUpload = (e) => {
  editForm.damage_proof = e.target.files[0];
};

const submitEditForm = () => {
  if (!editingReport.value) return;
  editForm.post(route('admin.blank-stocks.usage.update', editingReport.value.id), {
    forceFormData: true,
    onSuccess: () => {
      showEditModal.value = false;
      editingReport.value = null;
      editForm.reset();
    },
  });
};

const submitDraftReport = (rep) => {
  if (confirm(`Ajukan draf laporan [${rep.report_number}] secara resmi dan potong persediaan?`)) {
    router.post(route('admin.blank-stocks.usage.submit-draft', rep.id));
  }
};

const approveReport = (rep) => {
  router.post(route('admin.blank-stocks.usage.approve', rep.id));
};

const openReversalModal = (rep) => {
  selectedReport.value = rep;
  reversalReason.value = '';
  showReversalModal.value = true;
};

const executeReversal = () => {
  if (!selectedReport.value || !reversalReason.value) return;
  router.post(route('admin.blank-stocks.usage.reverse', selectedReport.value.id), {
    reversal_reason: reversalReason.value,
  }, {
    onSuccess: () => {
      showReversalModal.value = false;
      selectedReport.value = null;
    },
  });
};

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
};

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
      return 'bg-gray-100 text-gray-700';
  }
};
</script>
