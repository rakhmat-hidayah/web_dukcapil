<template>
  <div :class="{ 'dark': isDark }" class="min-h-screen flex flex-col bg-gray-50 dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 transition-colors duration-200">
    
    <!-- Sticky Navigation Wrapper (Top Bar + Header Menu) -->
    <div class="sticky top-0 z-50 shadow-md shadow-gray-200/40 dark:shadow-none">
      <!-- Top info bar -->
      <div class="bg-primary-900 text-white text-[10px] py-2 px-6 flex justify-between items-center font-medium">
        <div class="flex items-center gap-4">
          <span>📍 Dompu, Nusa Tenggara Barat</span>
          <span class="hidden sm:inline">✉️ {{ websiteSettings.office_email || 'dukcapil@dompukab.go.id' }}</span>
          <a v-if="websiteSettings.social_instagram" :href="websiteSettings.social_instagram" target="_blank" rel="noopener noreferrer" class="hidden md:inline hover:text-amber-300 transition font-bold">📸 Instagram</a>
          <a v-if="websiteSettings.social_facebook" :href="websiteSettings.social_facebook" target="_blank" rel="noopener noreferrer" class="hidden md:inline hover:text-amber-300 transition font-bold">🌐 Facebook</a>
        </div>
        <div class="flex items-center gap-4">
          <a href="/api/docs" target="_blank" class="hover:underline text-amber-400 font-bold">API INTEGRATION (OpenAPI)</a>
          <a href="/admin/login" class="hover:underline">Operator Portal</a>
        </div>
      </div>

      <!-- Header Navigation -->
      <header class="bg-white/98 dark:bg-zinc-900/98 backdrop-blur border-b border-gray-100 dark:border-zinc-800/80 transition-colors">
      <div class="max-w-7xl mx-auto px-3 xl:px-8 py-2.5 flex items-center justify-between gap-2 xl:gap-4">
        <!-- Logo Brand (Dual Logo: Logo Daerah Dompu & Logo Disdukcapil) -->
        <Link href="/" class="flex items-center gap-2 select-none group shrink-0">
          <!-- Logo 1: Logo Daerah Kabupaten Dompu -->
          <img 
            src="/img/logo-dompu.png" 
            alt="Logo Kabupaten Dompu" 
            class="h-8 lg:h-9 w-auto object-contain drop-shadow-sm transition-transform duration-200 group-hover:scale-105"
          />

          <!-- Thin Vertical Divider -->
          <div class="h-5 lg:h-6 w-px bg-gray-200 dark:bg-zinc-700"></div>

          <!-- Logo 2: Logo Official Disdukcapil Dompu -->
          <img 
            src="/img/logo-dukcapil.png" 
            alt="Logo Disdukcapil Dompu" 
            class="h-9 lg:h-10 w-auto object-contain drop-shadow-sm dark:brightness-110 transition-transform duration-200 group-hover:scale-105"
          />
        </Link>

        <!-- Navigation items list (Dynamic from DB or fallback to hardcoded) -->
        <nav class="hidden lg:flex flex-1 items-center justify-center gap-3 xl:gap-5 text-[11.5px] xl:text-xs font-semibold text-gray-700 dark:text-zinc-300">
          <!-- Dynamic header menu from CMS (if exists) -->
          <template v-if="headerMenu && headerMenu.items && headerMenu.items.length > 0">
            <template v-for="item in headerMenu.items" :key="item.id">
              <!-- Item with children = dropdown -->
              <div v-if="item.children && item.children.length > 0" class="relative group py-2">
                <component
                  :is="isInternalLink(item.url) ? Link : 'button'"
                  :href="(item.url && item.url !== '#') ? item.url : undefined"
                  class="flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-gray-100/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-150 whitespace-nowrap cursor-pointer"
                >
                  <span>{{ item.label }}</span>
                  <ChevronDown class="w-3 h-3 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-transform duration-200 group-hover:rotate-180" />
                </component>
                <!-- Dropdown panel with hover bridge padding -->
                <div class="absolute left-0 top-full pt-1.5 hidden group-hover:block w-56 z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none py-2 overflow-hidden divide-y divide-gray-50 dark:divide-zinc-800/60">
                    <div class="py-1">
                      <component
                        :is="isInternalLink(child.url) ? Link : 'a'"
                        v-for="child in item.children"
                        :key="child.id"
                        :href="child.url || '#'"
                        :target="child.target || '_self'"
                        class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold whitespace-nowrap transition-colors"
                      >{{ child.label }}</component>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Simple link item -->
              <component
                v-else
                :is="isInternalLink(item.url) ? Link : 'a'"
                :href="item.url || '#'"
                :target="item.target || '_self'"
                class="px-2 py-1 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800/60 hover:text-primary-600 transition whitespace-nowrap"
              >{{ item.label }}</component>
            </template>
          </template>

          <!-- Fallback static navigation when no menu configured in CMS -->
          <template v-else>
            <Link href="/" class="px-2 py-1 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800/60 hover:text-primary-600 transition whitespace-nowrap">Beranda</Link>
            <Link href="/profil" class="px-2 py-1 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800/60 hover:text-primary-600 transition whitespace-nowrap">Profil</Link>
            
            <!-- Layanan Dropdown -->
            <div class="relative group py-2">
              <Link href="/layanan" class="flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-gray-100/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-150 whitespace-nowrap">
                <span>Layanan</span>
                <ChevronDown class="w-3 h-3 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-transform duration-200 group-hover:rotate-180" />
              </Link>
              <div class="absolute left-0 top-full pt-1.5 hidden group-hover:block w-56 z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none py-2 overflow-hidden">
                  <Link href="/layanan" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Persyaratan Layanan</Link>
                  <Link href="/downloads" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Formulir Pendaftaran</Link>
                  <Link href="/layanan/survei" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Survei Kepuasan (IKM)</Link>
                  <Link href="/pengaduan" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Pengaduan Rakyat (Lapor)</Link>
                  <Link href="/pengaduan/lacak" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Tracking Permohonan</Link>
                </div>
              </div>
            </div>

            <!-- PPID Dropdown -->
            <div class="relative group py-2">
              <Link href="/ppid/pengertian" class="flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-gray-100/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-150 whitespace-nowrap">
                <span>PPID</span>
                <ChevronDown class="w-3 h-3 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-transform duration-200 group-hover:rotate-180" />
              </Link>
              <div class="absolute left-0 top-full pt-1.5 hidden group-hover:block w-56 z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none py-2 overflow-hidden">
                  <Link href="/ppid/pengertian" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Pengertian PPID</Link>
                  <Link href="/ppid/profil" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Profil Singkat PPID</Link>
                  <Link href="/ppid/tugas-fungsi" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Tugas dan Fungsi PPID</Link>
                  <Link href="/ppid/sk-ppid" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">SK PPID</Link>
                  <Link href="/ppid/informasi-publik" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Informasi Publik</Link>
                  <Link href="/ppid/prosedur" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Prosedur Layanan</Link>
                  <Link href="/ppid/permohonan" class="block px-4 py-2 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-zinc-800 text-xs font-bold">Ajukan Permohonan</Link>
                </div>
              </div>
            </div>

            <!-- Demografi Dropdown -->
            <div class="relative group py-2">
              <Link href="/statistik-kependudukan" class="flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-gray-100/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-150 whitespace-nowrap">
                <span>Demografi</span>
                <ChevronDown class="w-3 h-3 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-transform duration-200 group-hover:rotate-180" />
              </Link>
              <div class="absolute left-0 top-full pt-1.5 hidden group-hover:block w-56 z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none py-2 overflow-hidden">
                  <Link href="/statistik-kependudukan" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Dashboard Kependudukan</Link>
                  <Link href="/statistik-kependudukan#statistik" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Statistik Penduduk</Link>
                  <Link href="/statistik-kependudukan#dataset" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Dataset Kependudukan</Link>
                  <Link href="/downloads?category=agregat" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Buku Agregat</Link>
                </div>
              </div>
            </div>

            <Link href="/inovasi" class="px-2.5 py-1.5 rounded-xl hover:bg-gray-100/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 transition whitespace-nowrap">Inovasi</Link>
            
            <!-- Informasi Dropdown -->
            <div class="relative group py-2">
              <Link href="/news" class="flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-gray-100/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-150 whitespace-nowrap">
                <span>Informasi</span>
                <ChevronDown class="w-3 h-3 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-transform duration-200 group-hover:rotate-180" />
              </Link>
              <div class="absolute left-0 top-full pt-1.5 hidden group-hover:block w-56 z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none py-2 overflow-hidden">
                  <Link href="/news" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Berita &amp; Artikel</Link>
                  <Link href="/downloads" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Pusat Unduhan (Download)</Link>
                  <Link href="/gallery" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Galeri Foto &amp; Video</Link>
                  <Link href="/faq" class="block px-4 py-2 hover:bg-primary-50/70 dark:hover:bg-zinc-800/80 hover:text-primary-600 dark:hover:text-primary-400 text-xs font-semibold">Tanya Jawab (FAQ)</Link>
                </div>
              </div>
            </div>

            <Link href="/contact" class="px-2 py-1 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800/60 hover:text-primary-600 transition whitespace-nowrap">Kontak</Link>
          </template>
        </nav>

        <!-- Header Actions (right side) -->
        <div class="flex items-center gap-1 sm:gap-1.5 xl:gap-2 shrink-0">
          <!-- SANAI Online Dedicated Call-To-Action Button -->
          <a 
            v-if="sanaiConfig.sanai_is_active && sanaiConfig.sanai_display_navbar"
            :href="sanaiConfig.sanai_url"
            :target="sanaiConfig.sanai_open_new_tab ? '_blank' : '_self'"
            rel="noopener noreferrer"
            aria-label="Buka Portal Layanan Online SANAI Dukcapil Dompu"
            class="flex items-center gap-1 shrink-0 px-2 py-1.5 sm:px-3 lg:px-3.5 lg:py-2 bg-gradient-to-r from-primary-600 via-blue-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white font-black rounded-full shadow-md shadow-primary-500/25 hover:shadow-lg hover:shadow-primary-500/35 hover:-translate-y-0.5 active:scale-95 transition-all duration-200"
          >
            <Globe class="w-3 h-3 text-amber-300 animate-pulse shrink-0" />
            <span class="hidden sm:inline text-[10px] lg:text-[10.5px]">SANAI</span>
            <span class="hidden lg:inline text-[10.5px]"> Online</span>
            <ArrowUpRight class="w-3 h-3 text-amber-300 shrink-0 hidden sm:block" />
          </a>

          <!-- Search icon toggle -->
          <div class="relative">
            <button 
              @click="searchOpen = !searchOpen"
              class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 hover:text-primary-600 dark:text-zinc-400 rounded-lg transition cursor-pointer"
              title="Cari"
            >
              <Search class="w-4 h-4" />
            </button>
            <!-- Search popup -->
            <div v-if="searchOpen" class="absolute right-0 top-full mt-2 w-64 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-xl p-2 z-50">
              <input 
                ref="searchInputRef"
                type="text" 
                placeholder="Cari di website..." 
                v-model="searchQuery"
                @keyup.enter="handleSearch"
                @keyup.escape="searchOpen = false"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-transparent rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>
          </div>

          <!-- Dark mode switch -->
          <button 
            @click="toggleDarkMode" 
            class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 rounded-lg transition cursor-pointer"
            title="Toggle Dark / Light Mode"
            aria-label="Toggle Dark Mode"
          >
            <Sun v-if="isDark" class="w-4 h-4 text-amber-400" />
            <Moon v-else class="w-4 h-4 text-gray-600 dark:text-zinc-300" />
          </button>

          <!-- Mobile menu trigger -->
          <button 
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="p-1.5 lg:hidden hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg text-gray-500"
          >
            <Menu class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Mobile navigation overlay (Accordion Style) -->
      <div 
        v-if="mobileMenuOpen" 
        class="lg:hidden bg-white dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800 divide-y divide-gray-50 dark:divide-zinc-800/70"
      >
        <!-- Dynamic mobile menu from CMS -->
        <template v-if="headerMenu && headerMenu.items && headerMenu.items.length > 0">
          <template v-for="item in headerMenu.items" :key="item.id">
            <!-- Parent with children: accordion toggle -->
            <div v-if="item.children && item.children.length > 0">
              <button 
                @click="toggleMobileAccordion(item.id)"
                class="w-full flex items-center justify-between px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition"
              >
                <span>{{ item.label }}</span>
                <ChevronDown 
                  class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
                  :class="mobileAccordion.includes(item.id) ? 'rotate-180 text-primary-600' : ''"
                />
              </button>
              <div v-if="mobileAccordion.includes(item.id)" class="bg-gray-50/70 dark:bg-zinc-800/30 px-5 py-2 flex flex-col gap-1">
                <component
                  :is="isInternalLink(item.url) ? Link : 'a'"
                  v-if="item.url && item.url !== '#'"
                  :href="item.url"
                  :target="item.target || '_self'"
                  @click="mobileMenuOpen = false"
                  class="py-1.5 text-xs font-bold text-primary-600 dark:text-primary-400 border-b border-gray-100 dark:border-zinc-700 mb-1"
                >→ {{ item.label }}</component>
                <component
                  :is="isInternalLink(child.url) ? Link : 'a'"
                  v-for="child in item.children"
                  :key="child.id"
                  :href="child.url || '#'"
                  :target="child.target || '_self'"
                  @click="mobileMenuOpen = false"
                  class="py-2 pl-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 dark:hover:text-primary-400 border-b border-gray-50 dark:border-zinc-800/50 last:border-0"
                >{{ child.label }}</component>
              </div>
            </div>
            <!-- Simple link item -->
            <component
              v-else
              :is="isInternalLink(item.url) ? Link : 'a'"
              :href="item.url || '#'"
              :target="item.target || '_self'"
              @click="mobileMenuOpen = false"
              class="flex items-center px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 hover:text-primary-600 transition"
            >{{ item.label }}</component>
          </template>
        </template>

        <!-- Fallback static mobile menu -->
        <template v-else>
          <Link href="/" @click="mobileMenuOpen = false" class="flex items-center px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 hover:text-primary-600 transition">Beranda</Link>
          
          <!-- Profil accordion -->
          <div>
            <button @click="toggleMobileAccordion('profil')" class="w-full flex items-center justify-between px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
              <span>Profil</span>
              <ChevronDown class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="mobileAccordion.includes('profil') ? 'rotate-180 text-primary-600' : ''" />
            </button>
            <div v-if="mobileAccordion.includes('profil')" class="bg-gray-50/70 dark:bg-zinc-800/30 px-5 py-2 flex flex-col gap-1">
              <Link href="/profil" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Tentang Dinas</Link>
              <Link href="/profil#speech" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Sambutan Kepala Dinas</Link>
              <Link href="/profil#vision_mission" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Visi &amp; Misi</Link>
              <Link href="/profil/struktur-organisasi" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600">Struktur Organisasi</Link>
            </div>
          </div>

          <!-- Layanan accordion -->
          <div>
            <button @click="toggleMobileAccordion('layanan')" class="w-full flex items-center justify-between px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
              <span>Layanan</span>
              <ChevronDown class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="mobileAccordion.includes('layanan') ? 'rotate-180 text-primary-600' : ''" />
            </button>
            <div v-if="mobileAccordion.includes('layanan')" class="bg-gray-50/70 dark:bg-zinc-800/30 px-5 py-2 flex flex-col gap-1">
              <Link href="/layanan" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Persyaratan Layanan</Link>
              <Link href="/downloads" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Formulir Pendaftaran</Link>
              <Link href="/layanan/survei" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Survei Kepuasan (IKM)</Link>
              <Link href="/pengaduan" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Pengaduan Rakyat</Link>
              <Link href="/pengaduan/lacak" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600">Tracking Permohonan</Link>
            </div>
          </div>

          <!-- PPID accordion -->
          <div>
            <button @click="toggleMobileAccordion('ppid')" class="w-full flex items-center justify-between px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
              <span>PPID</span>
              <ChevronDown class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="mobileAccordion.includes('ppid') ? 'rotate-180 text-primary-600' : ''" />
            </button>
            <div v-if="mobileAccordion.includes('ppid')" class="bg-gray-50/70 dark:bg-zinc-800/30 px-5 py-2 flex flex-col gap-1">
              <Link href="/ppid/pengertian" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Pengertian PPID</Link>
              <Link href="/ppid/informasi-publik" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Informasi Publik</Link>
              <Link href="/ppid/permohonan" @click="mobileMenuOpen = false" class="py-2 text-xs font-bold text-primary-600 hover:text-primary-700">Ajukan Permohonan</Link>
            </div>
          </div>

          <!-- Demografi accordion -->
          <div>
            <button @click="toggleMobileAccordion('demografi')" class="w-full flex items-center justify-between px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
              <span>Demografi</span>
              <ChevronDown class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="mobileAccordion.includes('demografi') ? 'rotate-180 text-primary-600' : ''" />
            </button>
            <div v-if="mobileAccordion.includes('demografi')" class="bg-gray-50/70 dark:bg-zinc-800/30 px-5 py-2 flex flex-col gap-1">
              <Link href="/statistik-kependudukan" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Dashboard Kependudukan</Link>
              <Link href="/statistik-kependudukan#statistik" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Statistik Penduduk</Link>
              <Link href="/statistik-kependudukan#dataset" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Dataset Kependudukan</Link>
              <Link href="/downloads?category=agregat" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600">Buku Agregat</Link>
            </div>
          </div>

          <Link href="/inovasi" @click="mobileMenuOpen = false" class="flex items-center px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 hover:text-primary-600 transition">Inovasi</Link>

          <!-- Informasi accordion -->
          <div>
            <button @click="toggleMobileAccordion('informasi')" class="w-full flex items-center justify-between px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
              <span>Informasi</span>
              <ChevronDown class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="mobileAccordion.includes('informasi') ? 'rotate-180 text-primary-600' : ''" />
            </button>
            <div v-if="mobileAccordion.includes('informasi')" class="bg-gray-50/70 dark:bg-zinc-800/30 px-5 py-2 flex flex-col gap-1">
              <Link href="/news" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Berita &amp; Artikel</Link>
              <Link href="/downloads" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Pusat Unduhan</Link>
              <Link href="/gallery" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600 border-b border-gray-100 dark:border-zinc-800">Galeri Foto &amp; Video</Link>
              <Link href="/faq" @click="mobileMenuOpen = false" class="py-2 text-xs font-medium text-gray-600 dark:text-zinc-400 hover:text-primary-600">Tanya Jawab (FAQ)</Link>
            </div>
          </div>

          <Link href="/contact" @click="mobileMenuOpen = false" class="flex items-center px-5 py-3.5 text-xs font-bold text-gray-800 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-800/50 hover:text-primary-600 transition">Kontak</Link>

          <!-- SANAI Mobile Button -->
          <div class="p-4">
            <a 
              v-if="sanaiConfig.sanai_is_active && sanaiConfig.sanai_display_navbar"
              :href="sanaiConfig.sanai_url"
              :target="sanaiConfig.sanai_open_new_tab ? '_blank' : '_self'"
              rel="noopener noreferrer"
              class="w-full py-3 bg-gradient-to-r from-primary-600 to-indigo-600 text-white text-xs font-black rounded-xl shadow-md flex items-center justify-center gap-2"
            >
              <Globe class="w-4 h-4 text-amber-300" />
              <span>{{ sanaiConfig.sanai_button_label || 'SANAI Online' }}</span>
              <ArrowUpRight class="w-4 h-4 text-amber-300" />
            </a>
          </div>
        </template>
      </div>
    </header>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-6 py-8">
      <slot />
    </main>

    <!-- Public Footer -->
    <footer class="bg-white dark:bg-zinc-900 border-t border-gray-100 dark:border-zinc-800 py-12 text-xs">
      <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-center sm:text-left">
        <!-- Brand Info -->
        <div class="space-y-3 flex flex-col items-center sm:items-start">
          <div class="flex items-center justify-center sm:justify-start gap-3">
            <img src="/img/logo-dompu.png" alt="Logo Dompu" class="h-11 w-auto object-contain" />
            <div class="h-8 w-px bg-gray-200 dark:bg-zinc-700"></div>
            <img src="/img/logo-dukcapil.png" alt="Logo Disdukcapil" class="h-12 w-auto object-contain dark:brightness-110" />
          </div>
          <p class="text-gray-500 dark:text-zinc-400 leading-relaxed">
            Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu resmi menyelenggarakan tertib administrasi kependudukan prima.
          </p>
        </div>

        <!-- Links column - Dynamic or static -->
        <div class="space-y-3 flex flex-col items-center sm:items-start">
          <h4 class="font-extrabold text-gray-800 dark:text-zinc-200">Tautan Pintas</h4>
          <div class="flex flex-col items-center sm:items-start gap-2 font-medium text-gray-500 dark:text-zinc-400">
            <!-- Dynamic footer menu from CMS -->
            <template v-if="footerMenu && footerMenu.items && footerMenu.items.length > 0">
              <component
                :is="isInternalLink(item.url) ? Link : 'a'"
                v-for="item in footerMenu.items"
                :key="item.id"
                :href="item.url || '#'"
                :target="item.target || '_self'"
                class="hover:text-primary-600 transition"
              >{{ item.label }}</component>
            </template>
            <!-- Fallback static footer links -->
            <template v-else>
              <Link href="/" class="hover:text-primary-600 transition">Beranda</Link>
              <Link href="/profil" class="hover:text-primary-600 transition">Profil</Link>
              <Link href="/news" class="hover:text-primary-600 transition">Berita</Link>
              <Link href="/layanan" class="hover:text-primary-600 transition">Layanan</Link>
              <Link href="/statistik-kependudukan" class="hover:text-primary-600 transition">Demografi</Link>
              <Link href="/ppid/pengertian" class="hover:text-primary-600 transition">PPID</Link>
              <Link href="/inovasi" class="hover:text-primary-600 transition">Inovasi</Link>
              <Link href="/downloads" class="hover:text-primary-600 transition">Download</Link>
              <Link href="/faq" class="hover:text-primary-600 transition">FAQ</Link>
              <Link href="/contact" class="hover:text-primary-600 transition">Kontak</Link>
            </template>
          </div>
        </div>

        <!-- Services highlights -->
        <div class="space-y-3 flex flex-col items-center sm:items-start">
          <h4 class="font-extrabold text-gray-800 dark:text-zinc-200">Portal Layanan</h4>
          <div class="flex flex-col items-center sm:items-start gap-2 font-medium text-gray-500 dark:text-zinc-400">
            <a 
              v-if="sanaiConfig.sanai_is_active && sanaiConfig.sanai_display_footer"
              :href="sanaiConfig.sanai_url"
              :target="sanaiConfig.sanai_open_new_tab ? '_blank' : '_self'"
              rel="noopener noreferrer"
              aria-label="Buka Portal Layanan Online SANAI Dukcapil Dompu"
              class="text-primary-600 dark:text-primary-400 font-extrabold hover:underline flex items-center justify-center sm:justify-start gap-1.5"
            >
              <span>SANAI Online</span>
              <ArrowUpRight class="w-3.5 h-3.5" />
            </a>
            <Link href="/layanan" class="hover:text-primary-600 transition">Persyaratan Layanan</Link>
            <Link href="/pengaduan/lacak" class="hover:text-primary-600 transition">Tracking Permohonan</Link>
            <Link href="/downloads" class="hover:text-primary-600 transition">Formulir</Link>
            <Link href="/pengaduan" class="hover:text-primary-600 transition">Pengaduan</Link>
          </div>
        </div>

        <!-- Contact details -->
        <div class="space-y-3 flex flex-col items-center sm:items-start">
          <h4 class="font-extrabold text-gray-800 dark:text-zinc-200">Kontak Kantor</h4>
          <div class="flex flex-col items-center sm:items-start gap-2.5 font-medium text-gray-600 dark:text-zinc-400 text-xs leading-relaxed">
            <div class="flex items-start justify-center sm:justify-start gap-2">
              <span class="p-1 bg-amber-50 dark:bg-amber-950/40 text-amber-600 rounded-md shrink-0 mt-0.5">📍</span>
              <span class="break-words">{{ websiteSettings.office_address || 'Jl. Bhayangkara No. 01, Dompu, NTB' }}</span>
            </div>
            <div class="flex items-center justify-center sm:justify-start gap-2">
              <span class="p-1 bg-blue-50 dark:bg-blue-950/40 text-blue-600 rounded-md shrink-0">✉️</span>
              <a :href="`mailto:${websiteSettings.office_email || 'dukcapil@dompukab.go.id'}`" class="hover:underline font-semibold text-gray-800 dark:text-zinc-200 break-all">
                {{ websiteSettings.office_email || 'dukcapil@dompukab.go.id' }}
              </a>
            </div>

            <!-- Social Media Buttons -->
            <div class="flex flex-row flex-wrap sm:flex-col gap-2 pt-2 justify-center sm:justify-start w-full">
              <a 
                v-if="websiteSettings.social_instagram" 
                :href="websiteSettings.social_instagram" 
                target="_blank" 
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gradient-to-r from-purple-600 via-pink-600 to-amber-500 text-white text-xs font-bold shadow-sm hover:shadow-md hover:shadow-pink-500/25 hover:scale-105 active:scale-95 transition-all duration-200 justify-center flex-1 sm:flex-none"
              >
                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                <span>Instagram</span>
              </a>

              <a 
                v-if="websiteSettings.social_facebook" 
                :href="websiteSettings.social_facebook" 
                target="_blank" 
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-bold shadow-sm hover:shadow-md hover:shadow-blue-500/25 hover:scale-105 active:scale-95 transition-all duration-200 justify-center flex-1 sm:flex-none"
              >
                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.592 0 9 1.583 9 4.615V8z"/></svg>
                <span>Facebook</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-6 border-t border-gray-100 dark:border-zinc-800/60 mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center text-center sm:text-left gap-4 text-gray-400 font-medium">
        <p>© {{ new Date().getFullYear() }} Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu</p>
        <div class="flex items-center gap-4">
          <a href="/sitemap.xml" class="hover:underline">Sitemap</a>
          <a href="/robots.txt" class="hover:underline">Robots.txt</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Search, Sun, Moon, Menu, ChevronDown, Globe, ArrowUpRight } from '@lucide/vue';

const isDark = ref(false);
const mobileMenuOpen = ref(false);
const mobileAccordion = ref([]); // tracks which accordion items are open
const searchQuery = ref('');
const searchOpen = ref(false);
const searchInputRef = ref(null);

// Toggle mobile accordion item open/close
const toggleMobileAccordion = (id) => {
  const idx = mobileAccordion.value.indexOf(id);
  if (idx === -1) {
    mobileAccordion.value.push(id);
  } else {
    mobileAccordion.value.splice(idx, 1);
  }
};

// Reset accordion when mobile menu closes
watch(mobileMenuOpen, (val) => {
  if (!val) mobileAccordion.value = [];
});

// Get shared nav_menus and website_settings from Inertia shared props
const page = usePage();
const websiteSettings = computed(() => page.props.website_settings || {});

const sanaiConfig = computed(() => {
  const ws = page.props.website_settings || {};
  return {
    sanai_name: ws.sanai_name || 'SANAI Online',
    sanai_url: ws.sanai_url || 'https://sanai-dukcapil.dompukab.go.id',
    sanai_description: ws.sanai_description || 'Portal Layanan Administrasi Kependudukan Kabupaten Dompu',
    sanai_button_label: ws.sanai_button_label || 'SANAI Online',
    sanai_is_active: ws.sanai_is_active !== '0',
    sanai_open_new_tab: ws.sanai_open_new_tab !== '0',
    sanai_display_navbar: ws.sanai_display_navbar !== '0',
    sanai_display_homepage: ws.sanai_display_homepage !== '0',
    sanai_display_footer: ws.sanai_display_footer !== '0',
  };
});

const navMenus = computed(() => page.props.nav_menus || {});
const headerMenu = computed(() => navMenus.value['header'] || null);
const footerMenu = computed(() => navMenus.value['footer'] || null);

const isInternalLink = (url) => {
  if (!url) return false;
  return url.startsWith('/') && !url.startsWith('//');
};

const toggleDarkMode = () => {
  isDark.value = !isDark.value;
  localStorage.setItem('public-dark-mode', isDark.value ? 'true' : 'false');
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
};

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.get(route('public.search'), { q: searchQuery.value });
  }
};

onMounted(() => {
  isDark.value = localStorage.getItem('public-dark-mode') === 'true' || 
                 (!('public-dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
});
</script>
