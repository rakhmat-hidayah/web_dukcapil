<template>
  <section id="duties" class="py-12 mb-12 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 md:p-12 shadow-xl border border-slate-100 dark:border-slate-700">
      <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-6">Tugas Pokok & Fungsi</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center font-bold text-lg mb-4">01</div>
          <h3 class="font-bold text-slate-900 dark:text-white mb-2">Pendaftaran Penduduk</h3>
          <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">Pelayanan penerbitan KTP-El, KK, KIA, dan surat keterangan pindah penduduk.</p>
        </div>
        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-lg mb-4">02</div>
          <h3 class="font-bold text-slate-900 dark:text-white mb-2">Pencatatan Sipil</h3>
          <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">Penerbitan akta kelahiran, akta perkawinan, akta perceraian, dan akta kematian.</p>
        </div>
        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center font-bold text-lg mb-4">03</div>
          <h3 class="font-bold text-slate-900 dark:text-white mb-2">Pengelolaan PIAK & IKD</h3>
          <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">Pengelolaan jaringan data SIAK Terpusat dan aktivasi Identitas Kependudukan Digital.</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({ setting: Object })
</script>
