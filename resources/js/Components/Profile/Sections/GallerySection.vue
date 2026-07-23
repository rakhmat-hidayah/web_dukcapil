<template>
  <section id="gallery" class="py-12 mb-12 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-100 dark:border-slate-700">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Galeri Kegiatan</h2>
        <a href="/gallery" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua Galeri &rarr;</a>
      </div>
      <p class="text-xs text-slate-500 dark:text-slate-400">Dokumentasi pelayanan mobile perekaman KTP-El, sosialisasi KIA, dan kegiatan publik Dukcapil Dompu.</p>
    </div>
  </section>
</template>

<script setup>
defineProps({ setting: Object })
</script>
