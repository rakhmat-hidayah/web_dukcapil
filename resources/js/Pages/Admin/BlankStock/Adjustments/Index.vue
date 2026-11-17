<template>
  <Head title="Penyesuaian Stok Blangko KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-amber-950 via-slate-900 to-stone-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-amber-800/40">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30 mb-2">
              <Layers class="w-4 h-4" />
              <span>Modul Koreksi & Penyesuaian Saldo Berwenang</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Penyesuaian & Koreksi Saldo
            </h1>
            <p class="text-xs sm:text-sm text-amber-200 mt-1 max-w-xl">
              Prosedur formal penyesuaian persediaan berdasarkan temuan Berita Acara Stok Opname atau koreksi administratif resmi. Mutasi saldo terjadi setelah persetujuan pimpinan.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <button
              v-if="canCreate"
              @click="showCreateModal = true"
              class="px-5 py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2 cursor-pointer active:scale-95"
            >
              <Plus class="w-4 h-4 stroke-[3]" />
              <span>Ajukan Usulan Koreksi</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm">
        <form @submit.prevent="applyFilters" class="flex flex-col md:flex-row gap-3">
          <div class="relative flex-1">
            <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" />
            <input
              v-model="filterForm.search"
              type="text"
              placeholder="Cari nomor penyesuaian atau alasan koreksi..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-amber-500"
            />
          </div>

          <div>
            <select
              v-model="filterForm.status"
              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-amber-500"
            >
              <option value="">Semua Status</option>
              <option value="pending_approval">Menunggu Persetujuan</option>
              <option value="approved">Disetujui & Mutasi Diterapkan</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="submit"
              class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white font-bold text-xs transition cursor-pointer"
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

      <!-- Adjustments Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 tracking-tight">
            Riwayat Usulan & Mutasi Penyesuaian
          </h3>
          <span class="text-xs font-bold text-gray-400">
            Total {{ adjustments.total }} Usulan
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 font-extrabold text-[11px] uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4 rounded-l-xl">No. Penyesuaian</th>
                <th class="py-3 px-3">Tanggal</th>
                <th class="py-3 px-3">Jenis Koreksi</th>
                <th class="py-3 px-3 text-right">Kuantiti</th>
                <th class="py-3 px-3">Relasi Opname</th>
                <th class="py-3 px-3">Pengusul / Penyetuju</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-4 rounded-r-xl text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="adjustments.data.length === 0">
                <td colspan="8" class="text-center py-10 text-gray-400">
                  Belum ada usulan penyesuaian atau koreksi saldo.
                </td>
              </tr>
              <tr
                v-for="adj in adjustments.data"
                :key="adj.id"
                class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 transition"
              >
                <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ adj.adjustment_number }}
                </td>
                <td class="py-3.5 px-3 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                  {{ adj.adjustment_date }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border"
                    :class="adj.type === 'ADDITION' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                  >
                    {{ adj.type === 'ADDITION' ? 'Penambahan (+)' : 'Pengurangan (-)' }}
                  </span>
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-sm whitespace-nowrap" :class="adj.type === 'ADDITION' ? 'text-emerald-600' : 'text-rose-600'">
                  {{ adj.type === 'ADDITION' ? '+' : '-' }}{{ formatNumber(adj.quantity) }}
                </td>
                <td class="py-3.5 px-3 text-gray-600 dark:text-zinc-400 font-mono text-[11px] whitespace-nowrap">
                  {{ adj.opname_number || 'Koreksi Langsung' }}
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span class="font-bold text-gray-800 dark:text-zinc-200">{{ adj.requester_name }}</span>
                  <span v-if="adj.approver_name" class="block text-[10px] text-gray-400">
                    Disetujui: {{ adj.approver_name }}
                  </span>
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border"
                    :class="getStatusBadgeClass(adj.status)"
                  >
                    {{ adj.status_label }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <button
                      v-if="adj.status === 'pending_approval' && canApprove"
                      @click="approveAdjustment(adj)"
                      class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] transition cursor-pointer"
                    >
                      Setujui & Mutasi Stok
                    </button>
                    <span v-else-if="adj.status === 'approved'" class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                      Saldo: {{ formatNumber(adj.balance_after) }}
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="adjustments.links && adjustments.links.length > 3" class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs">
          <span class="text-gray-400">
            Menampilkan {{ adjustments.from || 0 }} - {{ adjustments.to || 0 }} dari {{ adjustments.total }}
          </span>
          <div class="flex gap-1">
            <Link
              v-for="(link, idx) in adjustments.links"
              :key="idx"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
              :class="link.active ? 'bg-amber-600 text-white border-amber-600' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 border-gray-200 dark:border-zinc-700 hover:bg-gray-50'"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Create Adjustment Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl space-y-5">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
          <div>
            <h3 class="text-lg font-black text-gray-900 dark:text-zinc-100">
              Ajukan Usulan Penyesuaian Saldo
            </h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
              Saldo Gudang saat ini: <strong>{{ formatNumber(currentStock) }}</strong> keping
            </p>
          </div>
          <button
            @click="showCreateModal = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 p-1 rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitAdjustment" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Tanggal Penyesuaian -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Tanggal Usulan *
              </label>
              <input
                v-model="createForm.adjustment_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-amber-500"
              />
            </div>

            <!-- Jenis Koreksi -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Arah Penyesuaian *
              </label>
              <select
                v-model="createForm.type"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold"
              >
                <option value="ADDITION">Penambahan Saldo (+)</option>
                <option value="DEDUCTION">Pengurangan Saldo (-)</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Jumlah Keping -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Jumlah Penyesuaian (Keping) *
              </label>
              <input
                v-model.number="createForm.quantity"
                type="number"
                min="1"
                required
                placeholder="Contoh: 10"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-amber-500"
              />
            </div>

            <!-- Tautan Opname -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Berdasarkan Dokumen Opname (Opsional)
              </label>
              <select
                v-model="createForm.opname_id"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-amber-500"
              >
                <option value="">-- Pilih Berita Acara Opname --</option>
                <option v-for="op in approvedOpnames" :key="op.id" :value="op.id">
                  {{ op.opname_number }} (Selisih: {{ op.difference }})
                </option>
              </select>
            </div>
          </div>

          <!-- Alasan Resmi -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Alasan Resmi Penyesuaian *
            </label>
            <textarea
              v-model="createForm.reason"
              rows="3"
              required
              placeholder="Jelaskan dasar pertimbangan dan rekomendasi Berita Acara yang mendasari koreksi ini..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-amber-500"
            ></textarea>
          </div>

          <!-- Lampiran Surat / SK -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Unggah Dokumen Pendukung / SK Koreksi (PDF / Gambar)
            </label>
            <input
              type="file"
              accept=".pdf,image/*"
              @change="handleAttachmentUpload"
              class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer"
            />
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
              class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 disabled:opacity-50 text-white font-black text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
            >
              <span v-if="createForm.processing">Menyimpan...</span>
              <span v-else>Kirim Usulan Koreksi</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Layers, Plus, Search, X } from '@lucide/vue';

const props = defineProps({
  adjustments: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  approvedOpnames: {
    type: Array,
    default: () => [],
  },
  canCreate: {
    type: Boolean,
    default: false,
  },
  canApprove: {
    type: Boolean,
    default: false,
  },
  currentStock: {
    type: Number,
    default: 0,
  },
});

const showCreateModal = ref(false);

const filterForm = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
});

const applyFilters = () => {
  router.get(route('admin.blank-stocks.adjustments.index'), {
    search: filterForm.value.search,
    status: filterForm.value.status,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  filterForm.value.search = '';
  filterForm.value.status = '';
  applyFilters();
};

const createForm = useForm({
  adjustment_date: new Date().toISOString().split('T')[0],
  type: 'ADDITION',
  quantity: '',
  opname_id: '',
  reason: '',
  attachment: null,
});

const handleAttachmentUpload = (e) => {
  createForm.attachment = e.target.files[0];
};

const submitAdjustment = () => {
  createForm.post(route('admin.blank-stocks.adjustments.store'), {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
  });
};

const approveAdjustment = (adj) => {
  if (confirm(`Setujui usulan penyesuaian [${adj.adjustment_number}] dan mutasikan saldo persediaan sekarang?`)) {
    router.post(route('admin.blank-stocks.adjustments.approve', adj.id));
  }
};

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'pending_approval':
      return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
    case 'approved':
      return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
    case 'rejected':
      return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800';
    default:
      return 'bg-gray-100 text-gray-700';
  }
};
</script>
