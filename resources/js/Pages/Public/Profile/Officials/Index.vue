<template>
  <PublicLayout>
    <Head title="Direktori Pejabat - Dinas Dukcapil Kabupaten Dompu" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider mb-2 inline-block">Single Source of Truth</span>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white">Direktori Pejabat Utama</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar Jajaran Pimpinan & Struktur Pejabat Dinas Dukcapil Kabupaten Dompu</p>
      </div>

      <!-- Search Filter Bar -->
      <div class="mb-8 bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row gap-4">
        <input
          v-model="form.search"
          @input="debouncedSearch"
          type="text"
          placeholder="Cari nama pejabat, NIP, atau jabatan..."
          class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Officials Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div v-for="official in officials.data" :key="official.id" class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col justify-between hover:shadow-2xl transition group">
          <div>
            <div class="w-28 h-28 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-700 mb-4 mx-auto border-2 border-slate-200 dark:border-slate-600 flex items-center justify-center">
              <img v-if="official.photo" :src="'/storage/' + official.photo" :alt="official.name" class="w-full h-full object-cover" />
              <svg v-else class="w-14 h-14 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center group-hover:text-blue-600 transition">{{ official.name }}</h3>
            <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 text-center mt-1">{{ official.position_title }}</p>
            <p v-if="official.nip" class="text-[11px] text-slate-400 text-center mt-0.5">NIP. {{ official.nip }}</p>
          </div>
          <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 text-center">
            <Link :href="'/profil/pejabat/' + (official.slug || official.id)" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-600/30 transition">
              Lihat Profil Lengkap Pejabat &rarr;
            </Link>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  officials: Object,
  filters: Object,
})

const form = ref({
  search: props.filters.search || '',
})

let timer = null
const debouncedSearch = () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get('/profil/pejabat', { search: form.value.search }, { preserveState: true, replace: true })
  }, 400)
}
</script>
