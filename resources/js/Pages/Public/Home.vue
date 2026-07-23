<template>
  <Head title="Beranda Resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu" />

  <PublicLayout>
    <div class="space-y-12 text-left">
      
      <!-- Running Announcements Ticker -->
      <div 
        v-if="tickers.length > 0" 
        class="bg-amber-500 text-white py-2 px-4 rounded-2xl flex items-center overflow-hidden gap-3 font-semibold text-xs shadow-sm shadow-amber-500/10"
      >
        <span class="bg-amber-700 px-2 py-0.5 rounded font-black text-[9px] uppercase tracking-wider shrink-0 animate-pulse">
          Pengumuman
        </span>
        <marquee class="flex-1 select-none" scrollamount="4">
          <span v-for="(t, idx) in tickers" :key="idx" class="mr-12">{{ t }}</span>
        </marquee>
      </div>

      <!-- Hero Slider Section -->
      <div class="relative h-64 sm:h-96 md:h-[28rem] rounded-2xl sm:rounded-[2.5rem] overflow-hidden border border-blue-900/30 dark:border-zinc-800 shadow-xl shadow-blue-950/20 bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 flex items-center">
        
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

        <!-- Render banners slide -->
        <div 
          v-for="(banner, idx) in banners" 
          :key="banner.id"
          v-show="currentSlide === idx"
          class="absolute inset-0 transition duration-500 z-10"
        >
          <!-- Soft Glassmorphic Gradient Overlay -->
          <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-blue-950/50 to-transparent z-10"></div>
          <img 
            :src="banner.image === 'banners/placeholder.webp' ? 'https://images.unsplash.com/photo-1541872703-74c5e44368f9?auto=format&fit=crop&w=1200&q=80' : `/storage/${banner.image}`" 
            class="w-full h-full object-cover mix-blend-overlay opacity-80"
            alt="Hero Slide" 
          />

          <!-- Text content overlay -->
          <div class="absolute inset-0 z-20 flex flex-col justify-center px-5 sm:px-12 md:px-16 max-w-2xl text-white space-y-2 sm:space-y-4">
            <span class="px-2.5 py-0.5 bg-primary-600/90 border border-primary-400/40 text-[9px] font-bold uppercase rounded-md tracking-widest w-fit shadow-sm">
              {{ banner.type }}
            </span>
            <h2 class="text-xl sm:text-3xl md:text-4xl font-extrabold tracking-tight leading-tight line-clamp-2 sm:line-clamp-none drop-shadow-md">
              {{ banner.title }}
            </h2>
            <p class="text-xs sm:text-sm text-blue-100/90 leading-relaxed font-medium line-clamp-2 sm:line-clamp-none drop-shadow-sm" v-if="banner.subtitle">
              {{ banner.subtitle }}
            </p>
            <a 
              v-if="banner.url"
              :href="banner.url" 
              :target="banner.url_target"
              class="px-4 py-2 sm:px-5 sm:py-2.5 bg-amber-500 hover:bg-amber-400 text-gray-900 text-[11px] sm:text-xs font-black rounded-xl w-fit transition shadow-md shadow-amber-500/20 active:scale-95 mt-1"
            >
              {{ banner.button_text || 'Selengkapnya →' }}
            </a>
          </div>
        </div>

        <!-- Slider Dots navigation -->
        <div class="absolute bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-30" v-if="banners.length > 1">
          <button 
            v-for="(b, i) in banners" 
            :key="i"
            @click="currentSlide = i"
            class="w-2.5 h-2.5 rounded-full transition"
            :class="[currentSlide === i ? 'bg-white scale-125' : 'bg-white/40']"
          ></button>
        </div>
      </div>

      <!-- SANAI Online Dedicated Promotional Card -->
      <div 
        v-if="sanaiConfig.sanai_is_active && sanaiConfig.sanai_display_homepage"
        class="relative overflow-hidden bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-900 text-white rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-10 shadow-xl shadow-blue-950/20 border border-blue-800/40 group"
      >
        <!-- Background Decorative Ambient Overlay -->
        <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-amber-500/10 transition duration-500"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <!-- Left details & typography -->
          <div class="space-y-3 max-w-2xl text-left">
            <div class="flex items-center gap-2">
              <span class="px-3 py-1 bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm flex items-center gap-1">
                <Monitor class="w-3.5 h-3.5" />
                Layanan Digital Resmi
              </span>
              <span class="px-2.5 py-1 bg-white/10 text-white text-[10px] font-bold rounded-lg backdrop-blur-sm">
                24/7 Online Access
              </span>
            </div>

            <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
              {{ sanaiConfig.sanai_name || 'Layanan Online Dukcapil (SANAI)' }}
            </h3>

            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-medium">
              {{ sanaiConfig.sanai_description || 'Urus dokumen kependudukan secara online dengan mudah, cepat, dan tanpa harus datang ke kantor.' }}
            </p>
          </div>

          <!-- Right Action Buttons -->
          <div class="flex flex-wrap items-center gap-3 shrink-0 w-full md:w-auto">
            <a 
              :href="sanaiConfig.sanai_url"
              :target="sanaiConfig.sanai_open_new_tab ? '_blank' : '_self'"
              rel="noopener noreferrer"
              aria-label="Buka Portal Layanan Online SANAI Dukcapil Dompu"
              class="px-6 py-3.5 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 text-xs sm:text-sm font-black rounded-2xl shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 flex-1 md:flex-initial"
            >
              <Globe class="w-4 h-4 text-slate-950" />
              <span>Buka SANAI Online</span>
              <ArrowUpRight class="w-4 h-4 text-slate-950" />
            </a>

            <Link 
              :href="route('public.services.index')" 
              class="px-5 py-3.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs sm:text-sm font-bold rounded-2xl backdrop-blur-md transition flex items-center justify-center gap-1.5 flex-1 md:flex-initial"
            >
              <span>Pelajari Layanan</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Public Statistics highlights panel -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl sm:rounded-[2.5rem] p-5 sm:p-8 shadow-lg shadow-gray-100/70 dark:shadow-none space-y-4">
        <!-- Data Period Header Badge -->
        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-zinc-800">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <h4 class="text-xs font-black text-gray-800 dark:text-zinc-200 uppercase tracking-widest font-mono">
              Statistik Agregat Kependudukan
            </h4>
          </div>
          <div class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800/50 rounded-xl text-[10px] font-bold text-indigo-700 dark:text-indigo-300 font-mono flex items-center gap-1.5 shadow-sm">
            <Calendar class="w-3.5 h-3.5 text-indigo-500" />
            <span>Periode Data: {{ stats.period_label || 'Semester 1 (s.d. Juni 2026)' }}</span>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-6 lg:divide-x lg:divide-gray-100 lg:dark:divide-zinc-800/80 pt-1">
          <!-- Total Penduduk -->
          <div class="flex flex-col items-center text-center sm:flex-row sm:text-left sm:items-center gap-4 p-4 sm:p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/60 lg:border-none lg:bg-transparent lg:p-0 lg:px-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-primary-600 to-indigo-600 text-white shadow-md shadow-primary-500/25 flex items-center justify-center shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">Total Penduduk</p>
              <p class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-sans tracking-tight leading-tight">{{ formatNumber(stats.total_population) }}</p>
              <p class="text-[9px] text-gray-400 font-semibold mt-0.5">Jiwa Terdaftar</p>
            </div>
          </div>

          <!-- Laki-Laki -->
          <div class="flex flex-col items-center text-center sm:flex-row sm:text-left sm:items-center gap-4 p-4 sm:p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/60 lg:border-none lg:bg-transparent lg:p-0 lg:px-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-cyan-500 text-white shadow-md shadow-blue-500/25 flex items-center justify-center shrink-0 relative overflow-hidden">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="10" cy="14" r="5"></circle>
                <line x1="19" y1="5" x2="13.6" y2="10.4"></line>
                <polyline points="14 5 19 5 19 10"></polyline>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">Laki-Laki</p>
              <p class="text-2xl font-black text-blue-600 dark:text-blue-400 font-sans tracking-tight leading-tight">{{ formatNumber(stats.total_male) }}</p>
              <p class="text-[9px] text-gray-400 font-semibold mt-0.5">Jiwa (Pria)</p>
            </div>
          </div>

          <!-- Perempuan -->
          <div class="flex flex-col items-center text-center sm:flex-row sm:text-left sm:items-center gap-4 p-4 sm:p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/60 lg:border-none lg:bg-transparent lg:p-0 lg:px-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-pink-600 to-rose-500 text-white shadow-md shadow-pink-500/25 flex items-center justify-center shrink-0 relative overflow-hidden">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="9" r="5"></circle>
                <line x1="12" y1="14" x2="12" y2="21"></line>
                <line x1="9" y1="18" x2="15" y2="18"></line>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">Perempuan</p>
              <p class="text-2xl font-black text-rose-600 dark:text-rose-400 font-sans tracking-tight leading-tight">{{ formatNumber(stats.total_female) }}</p>
              <p class="text-[9px] text-gray-400 font-semibold mt-0.5">Jiwa (Wanita)</p>
            </div>
          </div>

          <!-- Kepala Keluarga -->
          <div class="flex flex-col items-center text-center sm:flex-row sm:text-left sm:items-center gap-4 p-4 sm:p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/60 lg:border-none lg:bg-transparent lg:p-0 lg:px-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-500/25 flex items-center justify-center shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">Kepala Keluarga</p>
              <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-sans tracking-tight leading-tight">{{ formatNumber(stats.total_households) }}</p>
              <p class="text-[9px] text-gray-400 font-semibold mt-0.5">KK Terbit</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Dynamic Emergency Alert Popup Modal (client-controlled) -->
      <div 
        v-if="popup && popupOpen" 
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      >
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 relative">
          <button 
            @click="popupOpen = false" 
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200"
          >
            ✕
          </button>
          <span class="px-2 py-0.5 bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-100 dark:border-red-900/30 text-[8px] font-extrabold rounded uppercase tracking-wider">
            Pengumuman Darurat
          </span>
          <h3 class="font-extrabold text-gray-800 dark:text-zinc-50 text-base mt-2 leading-snug">
            {{ popup.title }}
          </h3>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-3 leading-relaxed">
            {{ popup.content }}
          </p>
          <div class="flex justify-end mt-6">
            <button 
              @click="popupOpen = false"
              class="px-4 py-2 bg-gray-900 dark:bg-zinc-800 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition"
            >
              Saya Mengerti
            </button>
          </div>
        </div>
      </div>

      <!-- Main Home Layout Grids -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent News Feed (Col Span 2) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="flex justify-between items-baseline">
            <h3 class="text-lg font-black text-gray-900 dark:text-zinc-50 tracking-tight flex items-center gap-2">
              📰 Berita Terkini
            </h3>
            <Link :href="route('public.news.index')" class="text-xs font-bold text-primary-600 hover:underline">
              Lihat Semua →
            </Link>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-if="recentNews.length === 0" class="col-span-3 text-center py-12 text-gray-400 text-xs">
              Belum ada berita dipublikasikan.
            </div>

            <Link 
              v-for="item in recentNews" 
              :key="item.id"
              :href="route('public.news.show', item.slug)"
              class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden flex flex-col group"
            >
              <div class="h-32 bg-gray-100 dark:bg-zinc-950 overflow-hidden flex items-center justify-center">
                <img 
                  :src="item.thumbnail ? `/storage/${item.thumbnail}` : 'https://images.unsplash.com/photo-1495020689067-958852a6565d?auto=format&fit=crop&w=400&q=80'" 
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                  alt="Thumbnail" 
                />
              </div>
              <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                <div>
                  <span 
                    v-if="item.category" 
                    class="text-[9px] font-bold font-mono uppercase tracking-wider border px-1.5 py-0.5 rounded"
                    :style="{ 
                      color: item.category.color, 
                      backgroundColor: item.category.color + '15',
                      borderColor: item.category.color + '30'
                    }"
                  >
                    {{ item.category.name }}
                  </span>
                  <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-xs mt-2 line-clamp-2 leading-snug group-hover:text-primary-600 transition">
                    {{ item.title }}
                  </h4>
                </div>
                <p class="text-[9px] text-gray-400 font-semibold font-mono">
                  {{ formatDate(item.published_at) }}
                </p>
              </div>
            </Link>
          </div>
        </div>

        <!-- Quick Access Widgets: Downloads & Complaints (Col Span 1) -->
        <div class="space-y-8 text-left">
          <!-- Download shortcuts -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Paling Sering Diunduh</h3>
            
            <div class="divide-y divide-gray-50 dark:divide-zinc-800/60">
              <div v-if="downloadShortcuts.length === 0" class="py-4 text-center text-gray-400 text-xs">
                Belum ada berkas unduhan terdaftar.
              </div>
              <a 
                v-for="doc in downloadShortcuts" 
                :key="doc.id"
                :href="route('public.downloads.index')"
                class="py-3 flex justify-between items-center hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 px-2 rounded-xl transition"
              >
                <div class="min-w-0 pr-2">
                  <h4 class="font-bold text-gray-800 dark:text-zinc-200 text-xs truncate leading-snug">{{ doc.title }}</h4>
                  <span class="text-[9px] text-gray-400 uppercase font-bold font-mono">{{ doc.file_type }}</span>
                </div>
                <span class="w-7 h-7 rounded-lg bg-gray-50 dark:bg-zinc-800 flex items-center justify-center text-gray-500 shrink-0">
                  ⬇️
                </span>
              </a>
            </div>
          </div>

          <!-- Public Complaint CTA -->
          <div class="bg-gradient-to-br from-primary-600 to-indigo-700 text-white rounded-3xl p-6 shadow-md flex flex-col justify-between h-48">
            <div class="space-y-1">
              <span class="px-2 py-0.5 bg-white/20 text-[9px] font-bold uppercase rounded-md tracking-wider w-fit">
                Layanan Pengaduan
              </span>
              <h3 class="text-sm font-extrabold tracking-tight mt-2">Aspirasi &amp; Pengaduan Rakyat (Lapor!)</h3>
              <p class="text-[10px] text-white/80 leading-relaxed font-semibold">
                Sampaikan keluhan pelayanan KTP, KK, Akta, atau pungutan liar secara online &amp; anonim.
              </p>
            </div>
            <button 
              class="w-full text-center py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 text-xs font-black rounded-xl transition"
              @click="goToComplaints"
            >
              Ajukan Pengaduan Sekarang →
            </button>
          </div>

          <!-- Indeks Kepuasan Masyarakat (IKM) Widget Card -->
          <div v-if="ikmWidget" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
              <span class="px-2 py-0.5 bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40 text-[9px] font-black uppercase rounded tracking-wider">
                Indeks Kepuasan Masyarakat
              </span>
              <span class="text-[10px] font-mono font-extrabold text-gray-400">Tahun {{ ikmWidget.year }}</span>
            </div>

            <div class="flex items-center justify-between pt-1">
              <div>
                <h4 class="text-xs font-extrabold text-gray-900 dark:text-zinc-50">{{ ikmWidget.period_title }}</h4>
                <p class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">
                  Mutu {{ ikmWidget.category }} — {{ ikmWidget.category_label }}
                </p>
              </div>

              <div class="text-right shrink-0 ml-3">
                <span class="text-3xl font-black text-primary-600 dark:text-primary-400 font-mono tracking-tight">{{ ikmWidget.score }}</span>
                <span class="text-[9px] block text-gray-400 font-mono">Skala 25-100</span>
              </div>
            </div>

            <Link 
              href="/layanan/survei" 
              class="w-full block text-center py-2.5 bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 dark:hover:bg-zinc-700 text-gray-900 dark:text-zinc-100 text-xs font-extrabold rounded-xl transition border border-gray-200 dark:border-zinc-700"
            >
              Lihat Detail Survei IKM →
            </Link>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Users, User, UserRound, Home as HomeIcon, Globe, ArrowUpRight, Monitor, Calendar } from '@lucide/vue';

const props = defineProps({
  banners: Array,
  tickers: Array,
  popup: Object,
  recentNews: Array,
  downloadShortcuts: Array,
  stats: Object,
  ikmWidget: Object,
});

const page = usePage();
const sanaiConfig = computed(() => {
  const ws = page.props.website_settings || {};
  return {
    sanai_name: ws.sanai_name || 'Layanan Online Dukcapil (SANAI)',
    sanai_url: ws.sanai_url || 'https://sanai-dukcapil.dompukab.go.id',
    sanai_description: ws.sanai_description || 'Urus dokumen kependudukan secara online dengan mudah, cepat, dan tanpa harus datang ke kantor.',
    sanai_button_label: ws.sanai_button_label || 'Buka SANAI Online',
    sanai_is_active: ws.sanai_is_active !== '0',
    sanai_open_new_tab: ws.sanai_open_new_tab !== '0',
    sanai_display_navbar: ws.sanai_display_navbar !== '0',
    sanai_display_homepage: ws.sanai_display_homepage !== '0',
    sanai_display_footer: ws.sanai_display_footer !== '0',
  };
});

const currentSlide = ref(0);
const popupOpen = ref(true);
let slideInterval = null;

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('id-ID').format(num);
};

const goToComplaints = () => {
  router.get(route('public.complaint.create'));
};

onMounted(() => {
  if (props.banners.length > 1) {
    slideInterval = setInterval(() => {
      currentSlide.value = (currentSlide.value + 1) % props.banners.length;
    }, 5000);
  }
});

onBeforeUnmount(() => {
  if (slideInterval) clearInterval(slideInterval);
});
</script>
