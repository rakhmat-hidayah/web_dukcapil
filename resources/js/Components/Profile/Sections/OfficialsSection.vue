<template>
  <section id="officials" class="py-12 mb-12 scroll-mt-24">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white">Direktori Pejabat Utama</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Single Source of Truth Jajaran Pimpinan Instansi</p>
      </div>
      <a href="/profil/pejabat" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
        Lihat Semua Pejabat &rarr;
      </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="official in officials.data" :key="official.id" class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-slate-700 hover:shadow-2xl transition group flex flex-col justify-between">
        <div>
          <div class="w-24 h-24 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-700 mb-4 mx-auto border-2 border-slate-200 dark:border-slate-600 flex items-center justify-center">
            <img v-if="official.photo" :src="'/storage/' + official.photo" :alt="official.name" class="w-full h-full object-cover" />
            <svg v-else class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center group-hover:text-blue-600 transition">{{ official.name }}</h3>
          <p class="text-xs font-medium text-blue-600 dark:text-blue-400 text-center mt-1">{{ official.position_title }}</p>
          <p v-if="official.nip" class="text-[11px] text-slate-400 text-center mt-0.5">NIP. {{ official.nip }}</p>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 text-center">
          <a :href="'/profil/pejabat/' + (official.slug || official.id)" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold transition">
            Lihat Profil & Riwayat
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({ setting: Object, officials: Object })
</script>
