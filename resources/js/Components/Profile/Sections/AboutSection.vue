<template>
  <section id="about" class="py-6 mb-8 scroll-mt-24">
    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100 dark:border-zinc-800">
      <div class="flex items-center gap-3.5 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500/15 to-primary-600/5 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 border border-primary-500/20 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Tentang Dinas</h2>
          <p class="text-xs text-gray-500 dark:text-zinc-400">Gambaran Umum & Peran Strategis Instansi</p>
        </div>
      </div>
      <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-zinc-300 leading-relaxed text-xs md:text-sm space-y-4">
        <p>
          Dinas Kependudukan dan Pencatatan Sipil (Dukcapil) Kabupaten Dompu merupakan instansi pemerintah daerah yang bertugas menyelenggarakan urusan pemerintahan di bidang administrasi kependudukan dan pencatatan sipil di wilayah Kabupaten Dompu, Nusa Tenggara Barat.
        </p>
        <p>
          Melalui integrasi Sistem Informasi Administrasi Kependudukan (SIAK) Terpusat dan Identitas Kependudukan Digital (IKD), Dukcapil Kabupaten Dompu terus berinovasi untuk menghadirkan pelayanan publik yang melayani tanpa diskriminasi, cepat, akurat, dan Bebas Pungutan Liar (Rp 0,-).
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  setting: Object,
})
</script>
