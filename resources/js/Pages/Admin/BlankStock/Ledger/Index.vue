<template>
  <Head title="Kartu Persediaan Blangko KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-950 via-zinc-900 to-sky-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-sky-900/40">
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

          <div class="bg-white/10 backdrop-blur-xs p-4 rounded-2xl border border-white/20 text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-200 block">Saldo Gudang Saat Ini</span>
            <span class="text-2xl sm:text-3xl font-black font-mono text-white">
              {{ formatNumber(currentStock) }} <span class="text-xs font-normal">keping</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
          <div class="relative lg:col-span-2">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" />
            <input
              v-model="filterForm.search"
              type="text"
              placeholder="Cari nomor transaksi, aktor, keterangan..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
            />
          </div>

          <div>
            <select
              v-model="filterForm.type"
              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
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

          <div>
            <select
              v-model="filterForm.operator_id"
              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
            >
              <option value="">Semua Operator</option>
              <option v-for="op in operators" :key="op.id" :value="op.id">
                {{ op.name }}
              </option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <input
              v-model="filterForm.start_date"
              type="date"
              class="w-full px-2.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
              title="Tanggal Awal"
            />
            <input
              v-model="filterForm.end_date"
              type="date"
              class="w-full px-2.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
              title="Tanggal Akhir"
            />
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

      <!-- Ledger Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 tracking-tight">
            Kartu Mutasi Persediaan Induk (Ledger)
          </h3>
          <span class="text-xs font-bold text-gray-400">
            Total {{ transactions.total }} Baris Transaksi
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 font-extrabold text-[11px] uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4 rounded-l-xl">No. Transaksi</th>
                <th class="py-3 px-3">Tanggal</th>
                <th class="py-3 px-3">Jenis Mutasi</th>
                <th class="py-3 px-3 text-right">Saldo Sebelum</th>
                <th class="py-3 px-3 text-right">Masuk (+)</th>
                <th class="py-3 px-3 text-right">Keluar (-)</th>
                <th class="py-3 px-3 text-right">Saldo Sesudah</th>
                <th class="py-3 px-3">Aktor / Operator</th>
                <th class="py-3 px-4 rounded-r-xl">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="transactions.data.length === 0">
                <td colspan="9" class="text-center py-10 text-gray-400">
                  Tidak ditemukan catatan transaksi mutasi persediaan.
                </td>
              </tr>
              <tr
                v-for="t in transactions.data"
                :key="t.id"
                class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 transition"
              >
                <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ t.transaction_number }}
                </td>
                <td class="py-3.5 px-3 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                  {{ t.transaction_date }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border"
                    :class="getTypeBadgeClass(t.transaction_type)"
                  >
                    {{ t.type_label }}
                  </span>
                </td>
                <td class="py-3.5 px-3 text-right font-mono text-gray-500 whitespace-nowrap">
                  {{ formatNumber(t.balance_before) }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                  {{ t.qty_in > 0 ? '+' + formatNumber(t.qty_in) : '-' }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-rose-600 dark:text-rose-400 whitespace-nowrap">
                  {{ (t.qty_out_used + t.qty_out_damaged) > 0 ? '-' + formatNumber(t.qty_out_used + t.qty_out_damaged) : '-' }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-gray-900 dark:text-zinc-100 whitespace-nowrap bg-gray-50/50 dark:bg-zinc-800/50">
                  {{ formatNumber(t.balance_after) }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap text-gray-700 dark:text-zinc-300 font-semibold truncate max-w-[130px]">
                  {{ t.user_name }}
                  <span v-if="t.operator_name" class="block text-[10px] text-gray-400 font-normal truncate">
                    Op: {{ t.operator_name }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-gray-500 dark:text-zinc-400 truncate max-w-[200px]" :title="t.notes">
                  {{ t.notes || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="transactions.links && transactions.links.length > 3" class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs">
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
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { History, Search } from '@lucide/vue';

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
