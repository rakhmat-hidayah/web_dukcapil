<template>
  <section id="speech" class="py-6 mb-8 scroll-mt-24">
    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100 dark:border-zinc-800">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
        <div class="md:col-span-4 text-center">
          <div class="relative inline-block">
            <div class="w-44 h-44 md:w-52 md:h-52 rounded-2xl overflow-hidden bg-gray-100 dark:bg-zinc-800 border-4 border-white dark:border-zinc-700 shadow-lg mx-auto flex items-center justify-center">
              <svg class="w-24 h-24 text-gray-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
          </div>
          <h3 class="text-lg font-extrabold text-gray-900 dark:text-zinc-50 mt-4">Drs. Abd. Najib</h3>
          <p class="text-xs text-primary-600 dark:text-primary-400 font-bold">Kepala Dinas Dukcapil Kabupaten Dompu</p>
        </div>
        <div class="md:col-span-8 space-y-3">
          <span class="text-xs font-bold text-primary-600 dark:text-primary-400 uppercase tracking-widest block">Sambutan Pimpinan</span>
          <h2 class="text-xl md:text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight leading-tight">"Pelayanan Kependudukan Yang Transparan & Membahagiakan Masyarakat"</h2>
          <div class="text-gray-600 dark:text-zinc-300 text-xs md:text-sm leading-relaxed space-y-3 font-medium">
            <p>
              Assalamu’alaikum Warahmatullahi Wabarakatuh,
            </p>
            <p>
              Selamat datang di portal resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. Kehadiran portal ini merupakan bagian dari komitmen kami dalam mewujudkan keterbukaan informasi publik dan kemudahan akses layanan administrasi kependudukan bagi seluruh lapisan masyarakat.
            </p>
            <p>
              Seluruh jajaran Dinas Dukcapil Kabupaten Dompu siap melayani Anda dengan semangat integritas, profesionalisme, dan tanpa pungutan biaya apapun (Rp 0,-).
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  setting: Object,
})
</script>
