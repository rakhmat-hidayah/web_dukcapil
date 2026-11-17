<template>
  <Head title="Penerimaan Blangko KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-5 sm:space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-emerald-950 via-teal-900 to-slate-900 rounded-2xl sm:rounded-3xl p-5 sm:p-8 text-white shadow-xl border border-emerald-800/40">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 sm:gap-6">
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
              <Inbox class="w-4 h-4" />
              <span>Modul Penerimaan Resmi Bendahara Barang</span>
            </div>
            <h1 class="text-xl sm:text-3xl font-black tracking-tight text-white">
              Penerimaan Blangko KTP-el
            </h1>
            <p class="text-xs sm:text-sm text-emerald-200/90 max-w-xl leading-relaxed">
              Pencatatan resmi penambahan persediaan blangko dari Ditjen Dukcapil Kemendagri maupun Disdukcapil Provinsi NTB bersumber dokumen BAST.
            </p>
          </div>

          <div class="w-full sm:w-auto shrink-0">
            <button
              v-if="hasCreatePermission"
              @click="showCreateModal = true"
              class="w-full sm:w-auto px-5 py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs sm:text-sm shadow-lg shadow-emerald-500/25 transition flex items-center justify-center gap-2.5 cursor-pointer active:scale-95"
            >
              <Plus class="w-4 h-4 stroke-[3]" />
              <span>+ Input Penerimaan Blangko</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-4 sm:p-5 shadow-sm">
        <form @submit.prevent="applyFilters" class="space-y-3 md:space-y-0 md:flex md:items-center md:gap-3">
          <!-- Search input -->
          <div class="relative flex-1">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              v-model="filterForm.search"
              type="text"
              placeholder="Cari no. penerimaan, nomor BAST, sumber, keterangan..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>

          <!-- Date Range (Grid 2 cols on mobile, flex on desktop) -->
          <div class="grid grid-cols-2 gap-2 w-full md:w-auto md:flex md:items-center">
            <div class="relative flex-1 min-w-0">
              <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 md:hidden">Dari</span>
              <input
                v-model="filterForm.start_date"
                type="date"
                class="w-full px-2.5 sm:px-3 py-2 sm:py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 min-w-0"
                title="Tanggal Awal"
              />
            </div>
            <div class="relative flex-1 min-w-0">
              <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 md:hidden">Sampai</span>
              <input
                v-model="filterForm.end_date"
                type="date"
                class="w-full px-2.5 sm:px-3 py-2 sm:py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 min-w-0"
                title="Tanggal Akhir"
              />
            </div>
          </div>

          <!-- Action buttons -->
          <div class="flex items-center gap-2 w-full md:w-auto pt-1 md:pt-0">
            <button
              type="submit"
              class="flex-1 md:flex-none px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white font-bold text-xs transition cursor-pointer text-center"
            >
              Filter
            </button>

            <button
              v-if="filterForm.search || filterForm.start_date || filterForm.end_date"
              type="button"
              @click="resetFilters"
              class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-400 font-bold text-xs transition cursor-pointer text-center"
            >
              Reset
            </button>
          </div>
        </form>
      </div>

      <!-- Receipts Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-gray-100 dark:border-zinc-800">
          <div>
            <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-zinc-100 tracking-tight">
              Daftar Riwayat Penerimaan Blangko
            </h3>
            <span class="text-xs text-gray-400">
              Total {{ receipts.total }} Transaksi Penerimaan
            </span>
          </div>

          <button
            v-if="hasCreatePermission"
            @click="showCreateModal = true"
            class="sm:hidden w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2 cursor-pointer active:scale-95"
          >
            <Plus class="w-4 h-4 stroke-[3]" />
            <span>+ Catat Penerimaan Baru</span>
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 font-extrabold text-[11px] uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4 rounded-l-xl">No. Penerimaan</th>
                <th class="py-3 px-3">Tanggal Terima</th>
                <th class="py-3 px-3 text-right">Jumlah</th>
                <th class="py-3 px-3">Sumber Penerimaan</th>
                <th class="py-3 px-3">Nomor BAST</th>
                <th class="py-3 px-3">Penerima</th>
                <th class="py-3 px-3 text-right">Saldo Sesudah</th>
                <th class="py-3 px-4 rounded-r-xl">Lampiran & Ket</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="receipts.data.length === 0">
                <td colspan="8" class="text-center py-12 px-4">
                  <div class="max-w-xs mx-auto space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto border border-emerald-100 dark:border-emerald-900">
                      <Inbox class="w-6 h-6" />
                    </div>
                    <div>
                      <p class="text-xs font-bold text-gray-700 dark:text-zinc-300">Belum Ada Penerimaan Blangko</p>
                      <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-0.5">Belum ada riwayat dokumen fisik BAST penerimaan yang dicatat.</p>
                    </div>
                    <button
                      v-if="hasCreatePermission"
                      @click="showCreateModal = true"
                      class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-md transition cursor-pointer active:scale-95"
                    >
                      <Plus class="w-4 h-4 stroke-[3]" />
                      <span>Input Penerimaan Sekarang</span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr
                v-for="rcv in receipts.data"
                :key="rcv.id"
                class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 transition"
              >
                <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ rcv.receipt_number }}
                </td>
                <td class="py-3.5 px-3 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                  {{ rcv.received_date }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                  +{{ formatNumber(rcv.quantity) }} keping
                </td>
                <td class="py-3.5 px-3 text-gray-800 dark:text-zinc-200 font-semibold truncate max-w-[160px]">
                  {{ rcv.source }}
                </td>
                <td class="py-3.5 px-3 font-mono text-gray-600 dark:text-zinc-400 truncate max-w-[150px]">
                  {{ rcv.document_number }}
                  <span class="block text-[10px] text-gray-400">{{ rcv.document_date }}</span>
                </td>
                <td class="py-3.5 px-3 text-gray-700 dark:text-zinc-300 font-medium">
                  {{ rcv.receiver_name }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-bold text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ formatNumber(rcv.balance_after) }}
                </td>
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-2">
                    <a
                      v-if="rcv.attachment_url"
                      :href="rcv.attachment_url"
                      target="_blank"
                      class="px-2 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:underline font-bold text-[10px] flex items-center gap-1 shrink-0"
                    >
                      <Paperclip class="w-3 h-3" />
                      <span>Berkas</span>
                    </a>
                    <span v-else class="text-gray-400 text-[10px]">-</span>
                    <span v-if="rcv.notes" class="text-gray-400 text-[10px] truncate max-w-[120px]" :title="rcv.notes">
                      {{ rcv.notes }}
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="receipts.links && receipts.links.length > 3" class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs">
          <span class="text-gray-400">
            Menampilkan {{ receipts.from || 0 }} - {{ receipts.to || 0 }} dari {{ receipts.total }}
          </span>
          <div class="flex gap-1">
            <Link
              v-for="(link, idx) in receipts.links"
              :key="idx"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
              :class="link.active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 border-gray-200 dark:border-zinc-700 hover:bg-gray-50'"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Create Receipt Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs animate-fade-in overflow-y-auto"
    >
      <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-5 sm:p-8 max-w-xl w-full shadow-2xl space-y-4 sm:space-y-5 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
          <div>
            <h3 class="text-lg font-black text-gray-900 dark:text-zinc-100">
              Catat Penerimaan Blangko Baru
            </h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
              Input bukti fisik BAST penerimaan blangko KTP-el resmi
            </p>
          </div>
          <button
            @click="showCreateModal = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 p-1 rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitReceipt" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Tanggal Penerimaan -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Tanggal Penerimaan Fisik *
              </label>
              <input
                v-model="createForm.received_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <!-- Jumlah Blangko Diterima -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Jumlah Blangko (Keping) *
              </label>
              <input
                v-model="createForm.quantity"
                type="number"
                min="1"
                required
                placeholder="Contoh: 500"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />
            </div>
          </div>

          <!-- Sumber Penerimaan -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Sumber Penerimaan *
            </label>
            <input
              v-model="createForm.source"
              type="text"
              required
              placeholder="Contoh: Ditjen Dukcapil Kemendagri / Disdukcapil Provinsi NTB"
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Nomor Dokumen BAST -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Nomor Surat / BAST *
              </label>
              <input
                v-model="createForm.document_number"
                type="text"
                required
                placeholder="Contoh: 470/123/BAST/2026"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <!-- Tanggal Dokumen -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Tanggal Surat / BAST *
              </label>
              <input
                v-model="createForm.document_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />
            </div>
          </div>

          <!-- Lampiran Dokumen BAST -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Unggah Berkas Bukti BAST (PDF / JPG, Maks 5MB)
            </label>
            <input
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              @change="handleFileUpload"
              class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer"
            />
          </div>

          <!-- Keterangan Tambahan -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Catatan / Keterangan (Opsional)
            </label>
            <textarea
              v-model="createForm.notes"
              rows="2"
              placeholder="Catatan tambahan mengenai kondisi fisik kemasan atau serah terima..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            ></textarea>
          </div>

          <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-zinc-800">
            <button
              type="button"
              @click="showCreateModal = false"
              class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="createForm.processing"
              class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-black text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
            >
              <span v-if="createForm.processing">Menyimpan...</span>
              <span v-else>Simpan Penerimaan</span>
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
import { Inbox, Plus, Search, Paperclip, X } from '@lucide/vue';

const props = defineProps({
  receipts: {
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
  canCreate: {
    type: Boolean,
    default: false,
  },
});

const page = usePage();
const userRoles = computed(() => page.props.auth?.roles || []);
const isSuperAdmin = computed(() => page.props.auth?.is_super_admin || false);
const hasCreatePermission = computed(() => {
  return props.canCreate || 
    isSuperAdmin.value ||
    userRoles.value.includes('Super Administrator') ||
    userRoles.value.includes('Administrator Utama') ||
    userRoles.value.includes('Bendahara Barang') ||
    (page.props.auth?.permissions || []).includes('blank_stocks.receipts.create');
});

const showCreateModal = ref(false);

const filterForm = ref({
  search: props.filters.search || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const applyFilters = () => {
  router.get(route('admin.blank-stocks.receipts.index'), {
    search: filterForm.value.search,
    start_date: filterForm.value.start_date,
    end_date: filterForm.value.end_date,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  filterForm.value.search = '';
  filterForm.value.start_date = '';
  filterForm.value.end_date = '';
  applyFilters();
};

const createForm = useForm({
  received_date: new Date().toISOString().split('T')[0],
  quantity: '',
  source: '',
  document_number: '',
  document_date: new Date().toISOString().split('T')[0],
  notes: '',
  attachment: null,
});

const handleFileUpload = (e) => {
  createForm.attachment = e.target.files[0];
};

const submitReceipt = () => {
  createForm.post(route('admin.blank-stocks.receipts.store'), {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
  });
};

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
};
</script>
