<template>
  <AdminLayout title="Master Official Directory">
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Master Official Directory</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Single Source of Truth Data Pejabat Dinas Dukcapil Kabupaten Dompu</p>
        </div>
        <Link href="/admin/profile/officials/create" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-600/30 transition">
          + Tambah Pejabat Baru
        </Link>
      </div>

      <!-- Table Card -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-slate-700">
        <div class="mb-4">
          <input
            v-model="search"
            @input="debouncedSearch"
            type="text"
            placeholder="Cari pejabat, NIP, atau jabatan..."
            class="w-full md:w-80 px-4 py-2.5 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-900 uppercase font-semibold text-slate-400">
              <tr>
                <th class="p-3">Foto & Nama</th>
                <th class="p-3">Jabatan & NIP</th>
                <th class="p-3">Golongan / Unit</th>
                <th class="p-3">Status</th>
                <th class="p-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
              <tr v-for="off in officials.data" :key="off.id">
                <td class="p-3 flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 overflow-hidden shrink-0 border border-slate-200 dark:border-slate-600 flex items-center justify-center">
                    <img v-if="off.photo" :src="'/storage/' + off.photo" :alt="off.name" class="w-full h-full object-cover" />
                    <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  </div>
                  <div>
                    <span class="font-bold text-slate-900 dark:text-white block">{{ off.name }}</span>
                    <span class="text-[11px] text-slate-400">{{ off.email || '-' }}</span>
                  </div>
                </td>
                <td class="p-3">
                  <span class="font-semibold text-blue-600 dark:text-blue-400 block">{{ off.position_title }}</span>
                  <span class="text-[11px] text-slate-400">NIP. {{ off.nip || '-' }}</span>
                </td>
                <td class="p-3">
                  <span>{{ off.rank_golongan || '-' }}</span>
                  <span class="text-[11px] text-slate-400 block">{{ off.department }}</span>
                </td>
                <td class="p-3">
                  <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px] bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                    {{ off.status }}
                  </span>
                </td>
                <td class="p-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="'/admin/profile/officials/' + off.id + '/edit'" class="p-1.5 bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 transition">
                      Edit
                    </Link>
                    <button @click="deleteOfficial(off)" class="p-1.5 bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400 rounded-lg hover:bg-rose-100 transition">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  officials: Object,
  filters: Object,
})

const search = ref(props.filters.search || '')

let timer = null
const debouncedSearch = () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get('/admin/profile/officials', { search: search.value }, { preserveState: true, replace: true })
  }, 400)
}

const deleteOfficial = (off) => {
  if (confirm(`Hapus data pejabat ${off.name}?`)) {
    router.delete('/admin/profile/officials/' + off.id)
  }
}
</script>
