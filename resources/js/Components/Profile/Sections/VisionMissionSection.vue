<template>
  <section id="vision_mission" class="py-12 mb-12 scroll-mt-24">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Visi -->
      <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-3xl p-8 shadow-xl flex flex-col justify-between">
        <div>
          <span class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider mb-4 inline-block">Visi</span>
          <h3 class="text-2xl font-bold mb-4">Visi Instansi</h3>
          <p class="text-blue-100 text-base leading-relaxed font-medium italic">
            "Terwujudnya Pelayanan Kependudukan dan Pencatatan Sipil yang Prima, Akurat, Terintegrasi, dan Membahagiakan Masyarakat di Kabupaten Dompu."
          </p>
        </div>
      </div>
      <!-- Misi -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-100 dark:border-slate-700">
        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider mb-4 inline-block">Misi</span>
        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">Misi Pelayanan</h3>
        <ul class="space-y-3 text-slate-600 dark:text-slate-300 text-sm">
          <li class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</span>
            <span>Meningkatkan kualitas dan kecepatan pelayanan administrasi kependudukan dan pencatatan sipil.</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</span>
            <span>Mewujudkan validitas dan akurasi database kependudukan Kabupaten Dompu yang terintegrasi SIAK Terpusat.</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</span>
            <span>Mendorong pemanfaatan data kependudukan secara luas bagi perencanaan pembangunan daerah.</span>
          </li>
        </ul>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({ setting: Object })
</script>
