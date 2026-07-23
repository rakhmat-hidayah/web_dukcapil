<template>
  <section class="relative bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white py-16 md:py-24 overflow-hidden rounded-3xl sm:rounded-[2.5rem] mb-12 border border-blue-900/30 shadow-xl shadow-blue-950/20 flex items-center">
    
    <!-- Institutional Identity Background Layer (Opacity ~10%) -->
    <!-- 1. Blurred Government Public Service Backdrop -->
    <img 
      src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1400&q=80"
      class="absolute inset-0 w-full h-full object-cover opacity-20 filter blur-[3px] scale-105 pointer-events-none z-0"
      alt="Latar Pelayanan Dukcapil" 
    />

    <!-- 2. Modern Grid Line Pattern (10% Opacity) -->
    <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:28px_28px] pointer-events-none z-0"></div>

    <!-- 3. KTP-el Smartcard Watermark Illustration (Opacity ~10%) -->
    <div class="absolute right-6 top-1/2 -translate-y-1/2 opacity-10 pointer-events-none hidden lg:block z-0">
      <svg width="420" height="260" viewBox="0 0 420 260" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-blue-100">
        <rect x="2" y="2" width="416" height="256" rx="20" stroke="currentColor" stroke-width="4" stroke-dasharray="8 4" />
        <rect x="36" y="60" width="56" height="44" rx="8" stroke="currentColor" stroke-width="3" />
        <path d="M56 60v44M72 60v44M36 82h56" stroke="currentColor" stroke-width="2" />
        <rect x="36" y="124" width="70" height="85" rx="10" stroke="currentColor" stroke-width="3" />
        <circle cx="71" cy="154" r="18" stroke="currentColor" stroke-width="3" />
        <path d="M46 199c0-14 11-25 25-25s25 11 25 25" stroke="currentColor" stroke-width="3" />
        <rect x="120" y="35" width="260" height="12" rx="4" fill="currentColor" />
        <rect x="140" y="55" width="220" height="8" rx="3" fill="currentColor" />
        <rect x="130" y="90" width="180" height="8" rx="3" fill="currentColor" />
        <rect x="130" y="110" width="240" height="6" rx="2" fill="currentColor" />
        <rect x="130" y="126" width="200" height="6" rx="2" fill="currentColor" />
        <rect x="130" y="142" width="220" height="6" rx="2" fill="currentColor" />
        <rect x="130" y="158" width="190" height="6" rx="2" fill="currentColor" />
        <rect x="130" y="174" width="210" height="6" rx="2" fill="currentColor" />
        <circle cx="340" cy="180" r="32" stroke="currentColor" stroke-width="3" />
        <circle cx="340" cy="180" r="24" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 3" />
      </svg>
    </div>

    <!-- 4. Dompu Map Outline Watermark (Opacity ~10%) -->
    <div class="absolute right-1/3 top-1/2 -translate-y-1/2 opacity-10 pointer-events-none hidden md:block z-0">
      <svg width="360" height="260" viewBox="0 0 360 260" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-indigo-200">
        <path d="M30 70 Q 80 15, 140 40 T 260 35 T 330 110 T 290 200 T 200 240 T 90 215 T 20 140 Z" stroke="currentColor" stroke-width="3" stroke-dasharray="5 5" fill="currentColor" fill-opacity="0.08" />
        <circle cx="140" cy="100" r="5" fill="currentColor" />
        <circle cx="210" cy="130" r="7" fill="currentColor" />
        <circle cx="170" cy="175" r="5" fill="currentColor" />
        <line x1="140" y1="100" x2="210" y2="130" stroke="currentColor" stroke-width="2" />
        <line x1="210" y1="130" x2="170" y2="175" stroke="currentColor" stroke-width="2" />
        <line x1="140" y1="100" x2="170" y2="175" stroke="currentColor" stroke-width="2" />
      </svg>
    </div>

    <!-- Content overlay -->
    <div class="container mx-auto px-6 relative z-10 text-center max-w-4xl">
      <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-lg bg-amber-500 text-slate-950 text-xs font-black uppercase tracking-wider mb-6 shadow-sm">
        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        Profil Resmi Instansi
      </span>
      <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight mb-6 text-white drop-shadow-md">
        Dinas Kependudukan & Pencatatan Sipil Kabupaten Dompu
      </h1>
      <p class="text-base md:text-lg text-blue-100/90 font-medium leading-relaxed max-w-3xl mx-auto mb-8 drop-shadow-sm">
        Mewujudkan Tata Kelola Administrasi Kependudukan yang Digital, Inklusif, Transparan, dan Membahagiakan Masyarakat Kabupaten Dompu.
      </p>
      <div class="flex flex-wrap items-center justify-center gap-4">
        <a href="#about" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-black text-xs sm:text-sm transition shadow-lg shadow-amber-500/25 active:scale-95 flex items-center gap-2">
          Jelajahi Profil
          <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </a>
        <a href="/profil/struktur-organisasi" class="px-6 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm transition border border-white/20 backdrop-blur-md flex items-center gap-2">
          Struktur Organisasi
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  setting: Object,
})
</script>
