<template>
  <Head title="Kelola Akun Pemohon - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Kelola Akun Pemohon KTP-el
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Manajemen autentikasi NIK pemohon, verifikasi identitas, dan pemantauan perangkat aktif (One Account One Browser).
          </p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs">
          <p class="text-xs text-slate-500 font-bold">Total Akun</p>
          <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ stats.total }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs">
          <p class="text-xs text-emerald-600 font-bold">Akun Aktif</p>
          <p class="text-2xl font-black text-emerald-600 mt-1">{{ stats.active }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs">
          <p class="text-xs text-amber-600 font-bold">Menunggu Verifikasi</p>
          <p class="text-2xl font-black text-amber-600 mt-1">{{ stats.identity_pending }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs">
          <p class="text-xs text-rose-600 font-bold">Ditangguhkan</p>
          <p class="text-2xl font-black text-rose-600 mt-1">{{ stats.suspended }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            @keyup.enter="applyFilters"
            type="text"
            placeholder="Cari nama pemohon, email, atau 16 digit NIK..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <select
          v-model="accountStatus"
          @change="applyFilters"
          class="px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
        >
          <option value="">Semua Status Akun</option>
          <option value="ACTIVE">ACTIVE</option>
          <option value="IDENTITY_PENDING">IDENTITY_PENDING</option>
          <option value="EMAIL_PENDING">EMAIL_PENDING</option>
          <option value="SUSPENDED">SUSPENDED</option>
          <option value="LOCKED">LOCKED</option>
        </select>

        <select
          v-model="identityStatus"
          @change="applyFilters"
          class="px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
        >
          <option value="">Semua Status Identitas</option>
          <option value="VERIFIED">VERIFIED</option>
          <option value="PENDING">PENDING</option>
          <option value="REJECTED">REJECTED</option>
        </select>
      </div>

      <!-- Accounts Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="p-4 font-bold">Pemohon (NIK & Nama)</th>
                <th class="p-4 font-bold">Email</th>
                <th class="p-4 font-bold">Status Akun</th>
                <th class="p-4 font-bold">Status Identitas</th>
                <th class="p-4 font-bold">Perangkat Aktif</th>
                <th class="p-4 font-bold">Terdaftar</th>
                <th class="p-4 font-bold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
              <tr v-if="accounts.data.length === 0">
                <td colspan="7" class="p-8 text-center text-slate-400">
                  Tidak ada data akun pemohon yang sesuai kriteria pencarian.
                </td>
              </tr>
              <tr v-for="acc in accounts.data" :key="acc.id" class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition">
                <td class="p-4">
                  <p class="font-black text-slate-900 dark:text-white text-sm">{{ acc.name }}</p>
                  <p class="font-mono text-xs text-blue-600 dark:text-blue-400">{{ acc.masked_nik }}</p>
                </td>
                <td class="p-4">
                  <span class="font-mono">{{ acc.masked_email }}</span>
                </td>
                <td class="p-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-black"
                    :class="{
                      'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': acc.account_status === 'ACTIVE',
                      'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': acc.account_status === 'IDENTITY_PENDING' || acc.account_status === 'EMAIL_PENDING',
                      'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300': acc.account_status === 'SUSPENDED' || acc.account_status === 'LOCKED',
                    }"
                  >
                    {{ acc.account_status }}
                  </span>
                </td>
                <td class="p-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-black"
                    :class="{
                      'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': acc.identity_status === 'VERIFIED',
                      'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': acc.identity_status === 'PENDING',
                      'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300': acc.identity_status === 'REJECTED',
                    }"
                  >
                    {{ acc.identity_status }}
                  </span>
                </td>
                <td class="p-4">
                  <div v-if="acc.active_device_label" class="space-y-0.5">
                    <p class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                      <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                      <span>{{ acc.active_device_label }}</span>
                    </p>
                    <p class="text-[10px] text-slate-400">Aktif: {{ acc.active_device_seen }}</p>
                  </div>
                  <span v-else class="text-slate-400 text-[11px] italic">Tidak ada perangkat aktif</span>
                </td>
                <td class="p-4 text-slate-500 whitespace-nowrap">
                  {{ acc.created_at }}
                </td>
                <td class="p-4 text-right">
                  <Link
                    :href="route('queue.applicants.show', acc.id)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white font-bold text-xs transition cursor-pointer"
                  >
                    <span>Detail</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="accounts.links && accounts.links.length > 3" class="p-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-1">
          <Link
            v-for="(link, idx) in accounts.links"
            :key="idx"
            :href="link.url || '#'"
            v-html="link.label"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="link.active ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ChevronRight } from '@lucide/vue'

const props = defineProps({
  accounts: Object,
  filters: Object,
  stats: Object,
})

const searchQuery = ref(props.filters.search || '')
const accountStatus = ref(props.filters.account_status || '')
const identityStatus = ref(props.filters.identity_status || '')

const applyFilters = () => {
  router.get(
    route('queue.applicants.index'),
    {
      search: searchQuery.value,
      account_status: accountStatus.value,
      identity_status: identityStatus.value,
    },
    { preserveState: true }
  )
}
</script>
