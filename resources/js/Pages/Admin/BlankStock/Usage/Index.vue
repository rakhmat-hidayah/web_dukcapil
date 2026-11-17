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
                    <!-- Ajukan Draf (jika milik operator login & status draft) -->
                    <button
                      v-if="rep.status === 'draft' && rep.operator_id === $page.props.auth?.user?.id"
                      @click="submitDraftReport(rep)"
                      class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-[10px] transition cursor-pointer"
                    >
                      Ajukan
                    </button>

                    <!-- Approve (Supervisor) -->
                    <button
                      v-if="rep.status === 'submitted' && canApprove"
                      @click="approveReport(rep)"
                      class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] transition cursor-pointer"
                    >
                      Setujui
                    </button>

                    <!-- Batalkan / Reversal (Supervisor) -->
                    <button
                      v-if="['submitted', 'approved'].includes(rep.status) && canApprove"
                      @click="openReversalModal(rep)"
                      class="px-2 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 font-bold text-[10px] border border-rose-200 dark:border-rose-900 transition cursor-pointer"
                    >
                      Batal (Reversal)
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
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ClipboardList, Plus, Search, X, AlertTriangle } from '@lucide/vue';

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

const showCreateModal = ref(false);
const showReversalModal = ref(false);
const selectedReport = ref(null);
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
