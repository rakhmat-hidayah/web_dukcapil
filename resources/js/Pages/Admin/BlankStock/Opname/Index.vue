<template>
  <Head title="Stok Opname Fisik Blangko KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-purple-950 via-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-purple-800/40">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-400/30 mb-2">
              <PackageCheck class="w-4 h-4" />
              <span>Rekonsiliasi Fisik & Berita Acara Opname</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Stok Opname Fisik
            </h1>
            <p class="text-xs sm:text-sm text-purple-200 mt-1 max-w-xl">
              Pencatatan hasil perhitungan fisik berkala di brankas/gudang untuk memverifikasi kesesuaian antara stok sistem dan fisik keping blangko aktual.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <button
              v-if="canCreate"
              @click="showCreateModal = true"
              class="px-5 py-3 rounded-2xl bg-purple-500 hover:bg-purple-400 text-slate-950 font-black text-xs shadow-lg shadow-purple-500/20 transition flex items-center gap-2 cursor-pointer active:scale-95"
            >
              <Plus class="w-4 h-4 stroke-[3]" />
              <span>Input Berita Acara Opname</span>
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
              placeholder="Cari nomor opname atau catatan temuan..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
          </div>

          <div>
            <select
              v-model="filterForm.status"
              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
              <option value="">Semua Status</option>
              <option value="pending_approval">Menunggu Persetujuan</option>
              <option value="approved">Disetujui</option>
              <option value="draft">Draf</option>
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

      <!-- Opname Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 tracking-tight">
            Riwayat Berita Acara Stok Opname
          </h3>
          <span class="text-xs font-bold text-gray-400">
            Total {{ opnames.total }} Dokumen Opname
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 font-extrabold text-[11px] uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4 rounded-l-xl">No. Berita Acara</th>
                <th class="py-3 px-3">Tanggal Opname</th>
                <th class="py-3 px-3 text-right">Stok Sistem</th>
                <th class="py-3 px-3 text-right">Fisik Terhitung</th>
                <th class="py-3 px-3 text-right">Selisih</th>
                <th class="py-3 px-3">Pemeriksa / Pengesah</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-4 rounded-r-xl text-center">Aksi / Dokumen</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
              <tr v-if="opnames.data.length === 0">
                <td colspan="8" class="text-center py-10 text-gray-400">
                  Belum ada dokumen berita acara stok opname fisik.
                </td>
              </tr>
              <tr
                v-for="op in opnames.data"
                :key="op.id"
                class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 transition"
              >
                <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ op.opname_number }}
                </td>
                <td class="py-3.5 px-3 text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                  {{ op.opname_date }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-bold text-gray-700 dark:text-zinc-300 whitespace-nowrap">
                  {{ formatNumber(op.system_stock) }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black text-gray-900 dark:text-zinc-100 whitespace-nowrap">
                  {{ formatNumber(op.physical_stock) }}
                </td>
                <td class="py-3.5 px-3 text-right font-mono font-black whitespace-nowrap">
                  <span
                    class="px-2 py-0.5 rounded-md text-[11px]"
                    :class="op.difference === 0 ? 'bg-emerald-50 text-emerald-700' : (op.difference > 0 ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700')"
                  >
                    {{ op.difference > 0 ? '+' : '' }}{{ formatNumber(op.difference) }}
                  </span>
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span class="font-bold text-gray-800 dark:text-zinc-200">{{ op.checker_name }}</span>
                  <span v-if="op.approver_name" class="block text-[10px] text-gray-400">
                    Disahkan: {{ op.approver_name }}
                  </span>
                </td>
                <td class="py-3.5 px-3 whitespace-nowrap">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border"
                    :class="getStatusBadgeClass(op.status)"
                  >
                    {{ op.status_label }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <a
                      v-if="op.evidence_url"
                      :href="op.evidence_url"
                      target="_blank"
                      class="px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 hover:underline font-bold text-[10px]"
                    >
                      Lihat BA
                    </a>

                    <button
                      v-if="op.status === 'pending_approval' && canApprove"
                      @click="approveOpname(op)"
                      class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] transition cursor-pointer"
                    >
                      Sahkan Opname
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="opnames.links && opnames.links.length > 3" class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs">
          <span class="text-gray-400">
            Menampilkan {{ opnames.from || 0 }} - {{ opnames.to || 0 }} dari {{ opnames.total }}
          </span>
          <div class="flex gap-1">
            <Link
              v-for="(link, idx) in opnames.links"
              :key="idx"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
              :class="link.active ? 'bg-purple-600 text-white border-purple-600' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 border-gray-200 dark:border-zinc-700 hover:bg-gray-50'"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Create Opname Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl space-y-5">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
          <div>
            <h3 class="text-lg font-black text-gray-900 dark:text-zinc-100">
              Input Berita Acara Stok Opname Fisik
            </h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
              Pencatatan ini TIDAK mengubah saldo sistem sebelum diajukan penyesuaian resmi
            </p>
          </div>
          <button
            @click="showCreateModal = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 p-1 rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitOpname" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Tanggal Pelaksanaan Perhitungan Fisik *
            </label>
            <input
              v-model="opnameForm.opname_date"
              type="date"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Saldo Sistem Saat Ini -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Stok Sistem Saat Ini (Otomatis)
              </label>
              <input
                :value="formatNumber(currentStock) + ' keping'"
                type="text"
                readonly
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-xs text-gray-600 dark:text-zinc-400 font-mono font-bold cursor-not-allowed"
              />
            </div>

            <!-- Stok Fisik Terhitung -->
            <div>
              <label class="block text-xs font-bold text-purple-600 dark:text-purple-400 mb-1">
                Stok Fisik Aktual Terhitung (Keping) *
              </label>
              <input
                v-model.number="opnameForm.physical_stock"
                type="number"
                min="0"
                required
                placeholder="Contoh: 450"
                class="w-full px-3.5 py-2.5 rounded-xl border border-purple-300 dark:border-purple-800 bg-purple-50/40 dark:bg-purple-950/40 text-xs text-purple-950 dark:text-purple-200 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-purple-500"
              />
            </div>
          </div>

          <!-- Kalkulasi Selisih Fisik -->
          <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 flex items-center justify-between text-xs">
            <span class="font-semibold text-gray-700 dark:text-zinc-300">Selisih Fisik (Fisik - Sistem):</span>
            <span
              class="font-mono font-black text-base"
              :class="calculatedDifference === 0 ? 'text-emerald-600' : (calculatedDifference > 0 ? 'text-blue-600' : 'text-rose-600')"
            >
              {{ calculatedDifference > 0 ? '+' : '' }}{{ formatNumber(calculatedDifference) }} keping
            </span>
          </div>

          <!-- Unggah Dokumen Berita Acara -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Unggah Dokumen Berita Acara / Foto Brankas (PDF / Gambar, Maks 5MB)
            </label>
            <input
              type="file"
              accept=".pdf,image/*"
              @change="handleEvidenceUpload"
              class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer"
            />
          </div>

          <!-- Keterangan Temuan -->
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
              Catatan / Hasil Temuan Rekonsiliasi Lapangan
            </label>
            <textarea
              v-model="opnameForm.notes"
              rows="3"
              placeholder="Catatan kondisi fisik kemasan, segel, atau penjelasan selisih jika ada..."
              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-purple-500"
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
              :disabled="opnameForm.processing"
              class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white font-black text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
            >
              <span v-if="opnameForm.processing">Menyimpan...</span>
              <span v-else>Simpan Berita Acara</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { PackageCheck, Plus, Search, X } from '@lucide/vue';

const props = defineProps({
  opnames: {
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
  canApprove: {
    type: Boolean,
    default: false,
  },
});

const showCreateModal = ref(false);

const filterForm = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
});

const applyFilters = () => {
  router.get(route('admin.blank-stocks.opname.index'), {
    search: filterForm.value.search,
    status: filterForm.value.status,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  filterForm.value.search = '';
  filterForm.value.status = '';
  applyFilters();
};

const opnameForm = useForm({
  opname_date: new Date().toISOString().split('T')[0],
  physical_stock: '',
  notes: '',
  evidence_file: null,
});

const calculatedDifference = computed(() => {
  if (opnameForm.physical_stock === '' || opnameForm.physical_stock === null || opnameForm.physical_stock === undefined) {
    return 0;
  }
  return Number(opnameForm.physical_stock) - props.currentStock;
});

const handleEvidenceUpload = (e) => {
  opnameForm.evidence_file = e.target.files[0];
};

const submitOpname = () => {
  opnameForm.post(route('admin.blank-stocks.opname.store'), {
    onSuccess: () => {
      showCreateModal.value = false;
      opnameForm.reset();
    },
  });
};

const approveOpname = (op) => {
  if (confirm(`Sahkan Berita Acara Stok Opname [${op.opname_number}]?`)) {
    router.post(route('admin.blank-stocks.opname.approve', op.id));
  }
};

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'draft':
      return 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 border-gray-200 dark:border-zinc-700';
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
