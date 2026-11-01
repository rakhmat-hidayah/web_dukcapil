<template>
  <Head title="Kendala Pengambilan KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-300 dark:border-rose-800 mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Penanganan Masalah & Penangguhan Pengambilan
          </div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Kendala Pengambilan KTP-el
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Daftar permohonan yang mengalami hambatan saat proses serah terima fisik (misal: kartu terselip, identitas penerima berbeda, atau berkas kuasa belum lengkap). Petugas dapat menyelesaikan kendala setelah persyaratan terpenuhi.
          </p>
        </div>

        <Link
          :href="route('admin.handover.ready')"
          class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition flex items-center gap-2 self-start sm:self-auto"
        >
          Lihat KTP-el Siap Diambil &rarr;
        </Link>
      </div>

      <!-- Quick Search Bar -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
        <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row items-center gap-3">
          <div class="relative flex-1 w-full">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari No. Referensi / Nama Pemilik / Alasan Kendala..."
              class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <button
            type="submit"
            class="w-full sm:w-auto px-6 py-2.5 rounded-2xl bg-slate-900 dark:bg-slate-700 text-white font-bold text-xs hover:bg-slate-800 transition"
          >
            Cari
          </button>
          <button
            v-if="filters.search"
            type="button"
            @click="resetSearch"
            class="w-full sm:w-auto px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 transition"
          >
            Reset
          </button>
        </form>
      </div>

      <!-- Data Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div v-if="handovers.data.length === 0" class="py-16 text-center text-slate-400 text-xs space-y-2">
          <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 mx-auto flex items-center justify-center mb-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          </div>
          <p class="font-bold text-sm text-slate-700 dark:text-slate-200">Tidak ada kendala pengambilan KTP-el.</p>
          <p class="text-slate-400">Seluruh proses serah terima fisik berjalan lancar tanpa ada penangguhan.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[10px] uppercase font-bold text-slate-400 tracking-wider border-b border-slate-200 dark:border-slate-700">
                <th class="pb-3">No. Referensi</th>
                <th class="pb-3">Pemilik KTP-el</th>
                <th class="pb-3">Alasan Kendala</th>
                <th class="pb-3">Petugas Pencatat</th>
                <th class="pb-3">Waktu Dilaporkan</th>
                <th class="pb-3 text-right">Tindakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <tr v-for="item in handovers.data" :key="item.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-750 transition">
                <td class="py-4 font-mono font-bold text-blue-600 dark:text-blue-400">
                  {{ item.application?.reg_number }}
                </td>
                <td class="py-4 font-semibold text-slate-900 dark:text-white">
                  {{ item.application?.applicant_name }}
                  <span class="block text-[11px] text-slate-400 font-mono">NIK: {{ item.application?.masked_nik }}</span>
                </td>
                <td class="py-4 text-slate-800 dark:text-slate-200 max-w-xs">
                  <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-200 text-xs leading-relaxed font-medium">
                    {{ item.exception_reason }}
                  </div>
                </td>
                <td class="py-4 text-slate-600 dark:text-slate-400">
                  {{ item.exception_reported_user?.name || '-' }}
                </td>
                <td class="py-4 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                  {{ item.exception_reported_at_formatted }}
                </td>
                <td class="py-4 text-right">
                  <div class="inline-flex items-center gap-2">
                    <button
                      type="button"
                      @click="openResolveModal(item)"
                      class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                      Selesaikan Kendala
                    </button>
                    <Link
                      :href="route('admin.handover.show', item.id)"
                      class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs transition"
                    >
                      Detail
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="handovers.links?.length > 3" class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
          <p class="text-xs text-slate-400">
            Menampilkan {{ handovers.from || 0 }} - {{ handovers.to || 0 }} dari total {{ handovers.total }} transaksi berkendala
          </p>
          <div class="flex items-center gap-1">
            <Link
              v-for="(link, idx) in handovers.links"
              :key="idx"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold transition"
              :class="[
                link.active
                  ? 'bg-rose-600 text-white font-bold'
                  : link.url
                  ? 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'
                  : 'text-slate-300 dark:text-slate-600 pointer-events-none'
              ]"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Selesaikan Kendala -->
    <div
      v-if="activeResolveItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    >
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-700 space-y-5 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          </div>
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white">
              Selesaikan Kendala Pengambilan
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              KTP-el akan dikembalikan ke daftar Siap Diambil untuk dapat diserahkan.
            </p>
          </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 text-xs space-y-2">
          <div class="flex justify-between">
            <span class="text-slate-400">No. Registrasi:</span>
            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ activeResolveItem.application?.reg_number }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Nama Pemilik KTP:</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ activeResolveItem.application?.applicant_name }}</span>
          </div>
          <div class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-700">
            <span class="text-slate-400 block mb-1">Kendala Sebelumnya:</span>
            <p class="text-rose-600 dark:text-rose-400 font-medium italic">{{ activeResolveItem.exception_reason }}</p>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
            Catatan Solusi / Penyelesaian (Wajib Diisi):
          </label>
          <textarea
            v-model="resolveNotes"
            rows="3"
            placeholder="Contoh: Pemohon asli telah hadir langsung / Berkas surat kuasa asli bermeterai telah dilengkapi dan diverifikasi sah..."
            class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button
            type="button"
            @click="activeResolveItem = null"
            :disabled="isSubmitting"
            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submitResolve"
            :disabled="isSubmitting || !resolveNotes.trim()"
            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <span v-if="isSubmitting">Memproses...</span>
            <span v-else>Selesaikan & Kembalikan Siap Diambil &rarr;</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  handovers: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '' }),
  },
});

const searchQuery = ref(props.filters.search || '');
const activeResolveItem = ref(null);
const resolveNotes = ref('');
const isSubmitting = ref(false);

function extractRegCode(str) {
  if (!str) return '';
  const match = str.match(/REG-[A-Za-z0-9-]+/i);
  return match ? match[0].toUpperCase() : str.trim();
}

function handleSearch() {
  const clean = extractRegCode(searchQuery.value);
  if (clean && clean.startsWith('REG-')) {
    searchQuery.value = clean;
  }
  router.get(route('admin.handover.exceptions'), { search: searchQuery.value }, { preserveState: true, replace: true });
}

function resetSearch() {
  searchQuery.value = '';
  router.get(route('admin.handover.exceptions'), {}, { preserveState: true, replace: true });
}

function openResolveModal(item) {
  activeResolveItem.value = item;
  resolveNotes.value = '';
}

function submitResolve() {
  if (!activeResolveItem.value || !resolveNotes.value.trim()) return;

  isSubmitting.value = true;
  router.post(
    route('admin.handover.resolve-exception', activeResolveItem.value.id),
    { notes: resolveNotes.value },
    {
      onFinish: () => {
        isSubmitting.value = false;
        activeResolveItem.value = null;
      },
    }
  );
}
</script>
