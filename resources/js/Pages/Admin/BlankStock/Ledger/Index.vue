<template>
  <Head title="Kartu Persediaan Blangko KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner (Hidden on Print) -->
      <div class="print:hidden bg-gradient-to-r from-slate-950 via-zinc-900 to-sky-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-sky-900/40">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/20 text-sky-300 border border-sky-400/30 mb-2">
              <History class="w-4 h-4" />
              <span>Buku Mutasi Induk & Kartu Persediaan Immutable</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Kartu Persediaan Blangko KTP-el
            </h1>
            <p class="text-xs sm:text-sm text-sky-200 mt-1 max-w-xl">
              Catatan mutasi saldo berjalan (running balance) tidak dapat diubah secara diam-diam. Setiap perubahan dicatat permanen dalam buku besar audit persediaan.
            </p>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="bg-white/10 backdrop-blur-xs p-4 rounded-2xl border border-white/20 text-right">
              <span class="text-[10px] font-bold uppercase tracking-wider text-sky-200 block">Saldo Gudang Saat Ini</span>
              <span class="text-2xl sm:text-3xl font-black font-mono text-white">
                {{ formatNumber(currentStock) }} <span class="text-xs font-normal">keping</span>
              </span>
            </div>

            <button
              type="button"
              @click="printLedger"
              class="px-4 py-3 rounded-2xl bg-white hover:bg-gray-100 text-gray-900 font-bold text-xs shadow-lg transition flex items-center justify-center gap-2 cursor-pointer active:scale-95 whitespace-nowrap"
            >
              <Printer class="w-4 h-4 text-sky-600 stroke-[2.5]" />
              <span>Cetak Kartu Persediaan</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Filters & Search (Hidden on Print) -->
      <div class="print:hidden bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
        <form @submit.prevent="applyFilters" class="space-y-4">
          <!-- Row 1: Search & Dropdowns -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
            <div class="relative md:col-span-6 lg:col-span-6">
              <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" />
              <input
                v-model="filterForm.search"
                type="text"
                placeholder="Cari nomor transaksi, aktor, keterangan..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500 placeholder-gray-400"
              />
            </div>

            <div class="md:col-span-3 lg:col-span-3">
              <select
                v-model="filterForm.type"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500 cursor-pointer"
              >
                <option value="">Semua Jenis Mutasi</option>
                <option value="RECEIPT">Penerimaan Blangko (+)</option>
                <option value="USAGE">Pemakaian Cetak (-)</option>
                <option value="DAMAGE">Blangko Rusak (-)</option>
                <option value="ADJUSTMENT_ADD">Koreksi Tambah (+)</option>
                <option value="ADJUSTMENT_SUB">Koreksi Kurang (-)</option>
                <option value="REVERSAL">Reversal / Pembatalan (+)</option>
              </select>
            </div>

            <div class="md:col-span-3 lg:col-span-3">
              <select
                v-model="filterForm.operator_id"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500 cursor-pointer"
              >
                <option value="">Semua Operator</option>
                <option v-for="op in operators" :key="op.id" :value="op.id">
                  {{ op.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Row 2: Date Filters & Action Buttons -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 pt-3 border-t border-gray-100 dark:border-zinc-800/70">
            <!-- Date Range Inputs with comfortable width & padding -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
              <span class="text-xs font-bold text-gray-600 dark:text-zinc-300 flex items-center gap-1.5 shrink-0">
                <Calendar class="w-4 h-4 text-sky-600" />
                <span>Rentang Tanggal:</span>
              </span>

              <div class="flex items-center gap-2">
                <div class="relative flex-1 sm:w-44">
                  <input
                    v-model="filterForm.start_date"
                    type="date"
                    title="Tanggal Awal"
                    aria-label="Tanggal Awal"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500 cursor-pointer"
                  />
                </div>
                <span class="text-xs font-bold text-gray-400 shrink-0">s/d</span>
                <div class="relative flex-1 sm:w-44">
                  <input
                    v-model="filterForm.end_date"
                    type="date"
                    title="Tanggal Akhir"
                    aria-label="Tanggal Akhir"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500 cursor-pointer"
                  />
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 self-end sm:self-auto w-full sm:w-auto">
              <button
                type="submit"
                class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white font-bold text-xs transition cursor-pointer shadow-sm text-center"
              >
                Terapkan Filter
              </button>
              <button
                v-if="hasActiveFilters"
                type="button"
                @click="resetFilters"
                class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-400 font-bold text-xs transition cursor-pointer text-center"
              >
                Reset
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Ledger Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4 print:p-0 print:border-none print:shadow-none">
        
        <!-- Official Print Header (Only visible on Print) -->
        <div class="hidden print:block border-b-2 border-black pb-4 text-center space-y-1 mb-6">
          <div class="flex items-center justify-center gap-3 mb-2">
            <img src="/img/logo-dompu.png" alt="Logo Dompu" class="h-14 w-auto object-contain" />
            <div>
              <h2 class="text-base font-black uppercase text-black tracking-wide">
                Pemerintah Kabupaten Dompu
              </h2>
              <h3 class="text-sm font-extrabold uppercase text-black">
                Dinas Kependudukan dan Pencatatan Sipil
              </h3>
              <p class="text-[11px] text-gray-700">
                Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211 | Email: dukcapilkabdompu@gmail.com
              </p>
            </div>
          </div>
          <div class="pt-2 border-t border-black">
            <h4 class="text-xs font-black uppercase tracking-wider text-black">
              Buku Kartu Persediaan Blangko KTP-el (Buku Mutasi Induk)
            </h4>
            <p class="text-[11px] text-gray-700 mt-1">
              Periode: <strong>{{ filterForm.start_date ? formatDateDisplay(filterForm.start_date) : 'Awal Transaksi' }}</strong> s/d <strong>{{ filterForm.end_date ? formatDateDisplay(filterForm.end_date) : 'Saat Ini' }}</strong> 
              &nbsp;|&nbsp; Saldo Akhir Gudang: <strong>{{ formatNumber(currentStock) }} keping</strong>
            </p>
          </div>
        </div>

        <!-- Table Card Header (On screen) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-gray-100 dark:border-zinc-800 print:hidden">
          <div>
            <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 tracking-tight">
              Kartu Mutasi Persediaan Induk (Ledger)
            </h3>
            <span class="text-xs font-bold text-gray-400">
              Total {{ transactions.total }} Baris Transaksi
            </span>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="printLedger"
              class="px-3.5 py-2 rounded-xl bg-sky-50 dark:bg-sky-950/50 hover:bg-sky-100 dark:hover:bg-sky-900/50 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 font-bold text-xs transition flex items-center gap-2 cursor-pointer shadow-xs active:scale-95"
            >
              <Printer class="w-4 h-4 stroke-[2.5]" />
              <span>Cetak Kartu Persediaan</span>
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs print:text-[10px]">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 font-extrabold text-[11px] print:text-[10px] uppercase tracking-wider print:bg-gray-100 print:text-black">
              <tr>
                <th class="py-3 px-4 rounded-l-xl print:rounded-none print:border print:border-black print:px-2 print:py-1.5">No. Transaksi</th>
                <th class="py-3 px-3 print:border print:border-black print:px-2 print:py-1.5">Tanggal</th>
                <th class="py-3 px-3 print:border print:border-black print:px-2 print:py-1.5">Jenis Mutasi</th>
                <th class="py-3 px-3 text-right print:border print:border-black print:px-2 print:py-1.5">Saldo Sebelum</th>
                <th class="py-3 px-3 text-right print:border print:border-black print:px-2 print:py-1.5">Masuk (+)</th>
                <th class="py-3 px-3 text-right print:border print:border-black print:px-2 print:py-1.5">Keluar (-)</th>
                <th class="py-3 px-3 text-right print:border print:border-black print:px-2 print:py-1.5">Saldo Sesudah</th>
                <th class="py-3 px-3 print:border print:border-black print:px-2 print:py-1.5">Aktor / Operator</th>
                <th class="py-3 px-4 rounded-r-xl print:rounded-none print:border print:border-black print:px-2 print:py-1.5">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 print:divide-black">
              <tr v-if="transactions.data.length === 0">
                <td colspan="9" class="text-center py-10 text-gray-400 print:border print:border-black">
                  Tidak ditemukan catatan transaksi mutasi persediaan.
                </td>
              </tr>
              <tr
                v-for="t in transactions.data"
                :key="t.id"
                class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 transition print:hover:bg-transparent"
              >
                <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-zinc-100 whitespace-nowrap print:border print:border-black print:px-2 print:py-1 print:text-black">
                  {{ t.transaction_number }}
                </td>
                <td class="py-3.5 px-3 text-gray-600 dark:text-zinc-400 whitespace-nowrap print:border print:border-black print:px-2 print:py-1 print:text-black">
                  {{ t.transaction_date }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap print:border print:border-black print:px-2 print:py-1">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border print:border-none print:p-0 print:text-black"
                    :class="getTypeBadgeClass(t.transaction_type)"
                  >
                    {{ t.type_label }}
                  </span>
                </td>
                <td class="py-3.5 px-3 text-right font-mono text-gray-500 whitespace-nowrap print:border print:border-black print:px-2 print:py-1 print:text-black">
                  {{ formatNumber(t.balance_before) }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap print:border print:border-black print:px-2 print:py-1 print:text-black">
                  {{ t.qty_in > 0 ? '+' + formatNumber(t.qty_in) : '-' }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-rose-600 dark:text-rose-400 whitespace-nowrap print:border print:border-black print:px-2 print:py-1 print:text-black">
                  {{ (t.qty_out_used + t.qty_out_damaged) > 0 ? '-' + formatNumber(t.qty_out_used + t.qty_out_damaged) : '-' }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-gray-900 dark:text-zinc-100 whitespace-nowrap bg-gray-50/50 dark:bg-zinc-800/50 print:bg-transparent print:border print:border-black print:px-2 print:py-1 print:text-black">
                  {{ formatNumber(t.balance_after) }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap text-gray-700 dark:text-zinc-300 font-semibold truncate max-w-[130px] print:max-w-none print:border print:border-black print:px-2 print:py-1 print:text-black">
                  {{ t.user_name }}
                  <span v-if="t.operator_name" class="block text-[10px] text-gray-400 print:text-gray-600 font-normal truncate">
                    Op: {{ t.operator_name }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-gray-500 dark:text-zinc-400 truncate max-w-[200px] print:max-w-none print:border print:border-black print:px-2 print:py-1 print:text-black" :title="t.notes">
                  {{ t.notes || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Official Signatures (Visible on Print) -->
        <div class="hidden print:grid grid-cols-2 gap-8 pt-10 text-center text-xs">
          <div>
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala Dinas Kependudukan dan Pencatatan Sipil<br />Kabupaten Dompu</p>
            <div class="h-20"></div>
            <p class="font-extrabold underline">( ........................................................... )</p>
            <p class="text-[10px]">NIP. ....................................................</p>
          </div>
          <div>
            <p>Dompu, {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
            <p class="font-bold">Bendahara Barang / Pengurus Persediaan<br />Disdukcapil Kabupaten Dompu</p>
            <div class="h-20"></div>
            <p class="font-extrabold underline">( {{ $page.props.auth?.user?.name || '...........................................................' }} )</p>
            <p class="text-[10px]">NIP. ....................................................</p>
          </div>
        </div>

        <!-- Pagination (Hidden on Print) -->
        <div v-if="transactions.links && transactions.links.length > 3" class="print:hidden flex justify-between items-center pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs">
          <span class="text-gray-400">
            Menampilkan {{ transactions.from || 0 }} - {{ transactions.to || 0 }} dari {{ transactions.total }}
          </span>
          <div class="flex gap-1">
            <Link
              v-for="(link, idx) in transactions.links"
              :key="idx"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
              :class="link.active ? 'bg-sky-600 text-white border-sky-600' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 border-gray-200 dark:border-zinc-700 hover:bg-gray-50'"
            />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { History, Search, Calendar, Printer } from '@lucide/vue';

const props = defineProps({
  transactions: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
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

const filterForm = ref({
  search: props.filters.search || '',
  type: props.filters.type || '',
  operator_id: props.filters.operator_id || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const hasActiveFilters = computed(() => {
  return !!(
    filterForm.value.search ||
    filterForm.value.type ||
    filterForm.value.operator_id ||
    filterForm.value.start_date ||
    filterForm.value.end_date
  );
});

const applyFilters = () => {
  router.get(route('admin.blank-stocks.ledger.index'), {
    search: filterForm.value.search,
    type: filterForm.value.type,
    operator_id: filterForm.value.operator_id,
    start_date: filterForm.value.start_date,
    end_date: filterForm.value.end_date,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  filterForm.value.search = '';
  filterForm.value.type = '';
  filterForm.value.operator_id = '';
  filterForm.value.start_date = '';
  filterForm.value.end_date = '';
  applyFilters();
};

const printLedger = () => {
  window.print();
};

const formatDateDisplay = (dateStr) => {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  } catch {
    return dateStr;
  }
};

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
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