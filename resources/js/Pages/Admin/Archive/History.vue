<template>
  <Head title="Riwayat Pengarsipan - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-indigo-900/50">
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Audit Trail & Keterlacakan Dokumen
          </div>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
            Riwayat Pengarsipan
          </h1>
          <p class="text-xs sm:text-sm text-indigo-200 mt-1 max-w-2xl leading-relaxed">
            Catatan log audit resmi seluruh aktivitas pengarsipan: pembuatan paket ZIP, riwayat unduhan, konfirmasi manual operator, pengujian integritas hash, serta proses transfer permanen.
          </p>
        </div>
      </div>

      <!-- Filters Bar -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
        <form @submit.prevent="applyFilter" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Jenis Aksi</label>
            <select
              v-model="filterForm.action"
              class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"
            >
              <option value="">Semua Aksi</option>
              <option v-for="act in availableActions" :key="act" :value="act">{{ act }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Operator</label>
            <select
              v-model="filterForm.user_id"
              class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"
            >
              <option value="">Semua Pengguna</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Tanggal</label>
            <input
              v-model="filterForm.date"
              type="date"
              class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5"
            />
          </div>

          <div class="flex items-end gap-2">
            <button
              type="submit"
              class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs rounded-xl transition cursor-pointer"
            >
              Filter Log
            </button>
            <button
              type="button"
              @click="resetFilter"
              class="p-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 rounded-xl transition cursor-pointer"
              title="Reset"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
          </div>
        </form>
      </div>

      <!-- Logs Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 uppercase tracking-wider font-bold border-y border-slate-100 dark:border-slate-700/60">
              <tr>
                <th class="py-3 px-4">Waktu Server</th>
                <th class="py-3 px-4">Aksi Audit</th>
                <th class="py-3 px-4">Paket / Referensi</th>
                <th class="py-3 px-4">Operator / Pengguna</th>
                <th class="py-3 px-4">IP Address</th>
                <th class="py-3 px-4">Rincian Data</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <tr v-if="logs.data.length === 0">
                <td colspan="6" class="text-center py-10 text-slate-400">
                  Tidak ada catatan audit trail yang cocok.
                </td>
              </tr>
              <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition">
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 font-medium whitespace-nowrap">
                  {{ new Date(log.created_at).toLocaleString('id-ID') }}
                </td>
                <td class="py-3.5 px-4 font-mono font-bold">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] tracking-wide inline-flex items-center gap-1"
                    :class="{
                      'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300': log.action.includes('PACKAGE_CREATED'),
                      'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300': log.action.includes('DOWNLOAD'),
                      'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': log.action.includes('CONFIRM') || log.action.includes('VERIFIED'),
                      'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300': log.action.includes('FAILED') || log.action.includes('COMPROMISED'),
                      'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': log.action.includes('SETTINGS') || log.action.includes('MODE'),
                    }"
                  >
                    {{ log.action }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-mono">
                  <Link
                    v-if="log.package"
                    :href="route('admin.archive.packages.show', log.package.id)"
                    class="font-bold text-blue-600 dark:text-blue-400 hover:underline"
                  >
                    {{ log.package.package_code }}
                  </Link>
                  <span v-else class="text-slate-400">-</span>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-800 dark:text-slate-200">
                  {{ log.user?.name || 'Sistem' }}
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-500">
                  {{ log.ip_address || '-' }}
                </td>
                <td class="py-3.5 px-4 text-[11px] font-mono text-slate-600 dark:text-slate-300 max-w-xs truncate" :title="JSON.stringify(log.details)">
                  {{ JSON.stringify(log.details) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="logs.links && logs.links.length > 3" class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
          <div class="flex items-center gap-1">
            <template v-for="(link, i) in logs.links" :key="i">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition"
                :class="link.active ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200'"
              />
              <span v-else v-html="link.label" class="px-3 py-1.5 text-xs text-slate-400" />
            </template>
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
  logs: Object,
  filters: Object,
  availableActions: Array,
  users: Array,
});

const filterForm = ref({
  action: props.filters.action || '',
  user_id: props.filters.user_id || '',
  date: props.filters.date || '',
});

function applyFilter() {
  router.get(route('admin.archive.history'), filterForm.value, {
    preserveState: true,
    preserveScroll: true,
  });
}

function resetFilter() {
  filterForm.value = { action: '', user_id: '', date: '' };
  applyFilter();
}
</script>
