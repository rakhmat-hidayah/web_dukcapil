<template>
  <section id="org_chart" class="py-12 mb-12 scroll-mt-24">
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-3xl p-8 md:p-12 shadow-2xl relative overflow-hidden">
      <div class="max-w-3xl">
        <span class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full text-xs font-semibold uppercase tracking-wider mb-4 inline-block border border-blue-400/30">Engine Interaktif</span>
        <h2 class="text-3xl font-black mb-4">Struktur Organisasi Interaktif</h2>
        <p class="text-slate-300 text-sm leading-relaxed mb-6">
          Jelajahi hirarki bagan organisasi Dinas Dukcapil Kabupaten Dompu secara visual. Klik setiap simpul jabatan untuk melihat profil lengkap pejabat, NIP, tugas fungsi, dan unduh SK.
        </p>
        <a href="/profil/struktur-organisasi" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold text-sm transition shadow-lg shadow-blue-600/30">
          Buka Org Chart Fullscreen
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({ setting: Object, orgTree: Array })
</script>
