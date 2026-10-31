<template>
  <Head title="Riwayat Penyerahan KTP-el - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800 mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Arsip Transaksi Resmi Serah Terima Fisik
          </div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Riwayat Penyerahan KTP-el
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Seluruh data fisik KTP-el yang telah selesai diserahkan kepada pemohon atau perwakilan resminya, lengkap dengan bukti foto dokumentasi kamera dan waktu server.
          </p>
        </div>

        <Link
          :href="route('admin.handover.dashboard')"
          class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 font-bold text-xs transition flex items-center gap-2 self-start sm:self-auto"
        >
          &larr; Kembali ke Dashboard
        </Link>
      </div>

      <!-- Search & Date Filter Bar -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
        <form @submit.prevent="handleSearch" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
          <div class="relative sm:col-span-7">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari No. Referensi / Nama Pemilik / Nama Penerima / NIK..."
              class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>

          <div class="sm:col-span-3">
            <input
              v-model="dateQuery"
              type="date"
              class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>

          <div class="sm:col-span-2 flex items-center gap-2">
            <button
              type="submit"
              class="flex-1 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm"
            >
              Filter
            </button>
            <button
              v-if="filters.search || filters.date"
              type="button"
              @click="resetSearch"
              class="px-3 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 transition"
            >
              Reset
            </button>
          </div>
        </form>
      </div>

      <!-- History Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div v-if="handovers.data.length === 0" class="py-16 text-center text-slate-400 text-xs space-y-2">
          <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p class="font-bold text-sm text-slate-600 dark:text-slate-300">Belum ada riwayat penyerahan yang cocok.</p>
          <p class="text-slate-400">Silakan ubah kriteria pencarian atau tanggal filter.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[10px] uppercase font-bold text-slate-400 tracking-wider border-b border-slate-200 dark:border-slate-700">
                <th class="pb-3">No. Referensi</th>
                <th class="pb-3">Pemilik KTP-el</th>
                <th class="pb-3">Penerima Aktual</th>
                <th class="pb-3">Hubungan</th>
                <th class="pb-3">Waktu Penyerahan</th>
                <th class="pb-3">Petugas</th>
                <th class="pb-3 text-center">Foto Bukti</th>
                <th class="pb-3 text-right">Aksi</th>
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
                <td class="py-4 font-semibold text-slate-800 dark:text-slate-200">
                  {{ item.actual_receiver_name || item.application?.applicant_name }}
                  <span v-if="item.actual_receiver_nik" class="block text-[11px] text-slate-400 font-mono">
                    NIK: {{ item.actual_receiver_nik.slice(0, 4) }}****{{ item.actual_receiver_nik.slice(-4) }}
                  </span>
                </td>
                <td class="py-4">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded text-[10px] font-bold',
                      item.receiver_relationship === 'Sendiri'
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                        : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'
                    ]"
                  >
                    {{ item.receiver_relationship || 'Sendiri' }}
                  </span>
                </td>
                <td class="py-4 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                  {{ item.handover_at_formatted }}
                </td>
                <td class="py-4 text-slate-600 dark:text-slate-400">
                  {{ item.handover_user?.name || '-' }}
                </td>
                <td class="py-4 text-center">
                  <button
                    v-if="item.photo_path"
                    type="button"
                    @click="previewPhoto(item)"
                    class="px-2.5 py-1 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white font-bold text-[11px] transition inline-flex items-center gap-1 cursor-pointer"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Lihat Foto
                  </button>
                  <span v-else class="text-[10px] text-slate-400 italic">
                    Arsip Lama
                  </span>
                </td>
                <td class="py-4 text-right">
                  <Link
                    :href="route('admin.handover.show', item.id)"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs transition"
                  >
                    Detail
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="handovers.links?.length > 3" class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
          <p class="text-xs text-slate-400">
            Menampilkan {{ handovers.from || 0 }} - {{ handovers.to || 0 }} dari total {{ handovers.total }} transaksi penyerahan
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
                  ? 'bg-emerald-600 text-white font-bold'
                  : link.url
                  ? 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'
                  : 'text-slate-300 dark:text-slate-600 pointer-events-none'
              ]"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Pratinjau Foto Bukti Penyerahan (Private Storage Stream) -->
    <div
      v-if="activePhotoItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
      @click.self="activePhotoItem = null"
    >
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
          <div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">
              Bukti Foto Dokumentasi Penyerahan KTP-el
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
              No. Ref: {{ activePhotoItem.application?.reg_number }} · Penerima: {{ activePhotoItem.actual_receiver_name }}
            </p>
          </div>
          <button
            type="button"
            @click="activePhotoItem = null"
            class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 transition"
          >
            &times;
          </button>
        </div>

        <!-- Secure Image -->
        <div class="relative bg-slate-900 rounded-2xl overflow-hidden flex items-center justify-center min-h-[300px] border border-slate-200 dark:border-slate-700">
          <img
            :src="route('admin.handover.photo', activePhotoItem.id)"
            alt="Bukti Foto Penyerahan KTP-el"
            class="max-h-[460px] w-auto object-contain"
          />
        </div>

        <!-- Metadata Security Footprint -->
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-3 text-[11px] text-slate-500 dark:text-slate-400 space-y-1 font-mono">
          <div class="flex justify-between">
            <span>Waktu Server:</span>
            <span class="font-bold text-slate-700 dark:text-slate-300">{{ activePhotoItem.handover_at_formatted }}</span>
          </div>
          <div class="flex justify-between">
            <span>Petugas Penyerah:</span>
            <span class="font-bold text-slate-700 dark:text-slate-300">{{ activePhotoItem.handover_user?.name }}</span>
          </div>
          <div v-if="activePhotoItem.photo_hash" class="truncate">
            <span>SHA-256: </span>
            <span class="text-slate-400">{{ activePhotoItem.photo_hash }}</span>
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

const props = defineProps({
  handovers: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '', date: '' }),
  },
});

const searchQuery = ref(props.filters.search || '');
const dateQuery = ref(props.filters.date || '');
const activePhotoItem = ref(null);

function handleSearch() {
  router.get(
    route('admin.handover.history'),
    { search: searchQuery.value, date: dateQuery.value },
    { preserveState: true, replace: true }
  );
}

function resetSearch() {
  searchQuery.value = '';
  dateQuery.value = '';
  router.get(route('admin.handover.history'), {}, { preserveState: true, replace: true });
}

function previewPhoto(item) {
  activePhotoItem.value = item;
}
</script>
