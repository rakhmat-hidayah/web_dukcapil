<template>
  <Head :title="news ? news.title : 'Berita Tidak Ditemukan'" />

  <PublicLayout>
    <!-- Fallback if news is null -->
    <div v-if="!news" class="min-h-[50vh] flex flex-col items-center justify-center p-8 text-center space-y-4 max-w-lg mx-auto">
      <div class="w-16 h-16 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center font-bold text-2xl border border-rose-200 dark:border-rose-900/40">404</div>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-zinc-50">Berita Tidak Ditemukan</h2>
      <p class="text-xs text-gray-500 dark:text-zinc-400">Artikel berita yang Anda cari tidak tersedia, belum dipublikasi, atau telah dihapus.</p>
      <Link href="/news" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-500 text-white rounded-xl font-bold text-xs shadow-md transition">Kembali ke Daftar Berita</Link>
    </div>

    <!-- Main Content when news is valid -->
    <div v-else class="space-y-8 text-left max-w-4xl mx-auto">
      
      <!-- Back Link -->
      <div>
        <Link :href="route('public.news.index')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
          ← Kembali ke Berita
        </Link>
      </div>

      <!-- Main Article Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-[2rem] p-8 md:p-12 shadow-sm space-y-6">

        <!-- === BREADCRUMB NAVIGATION === -->
        <nav aria-label="breadcrumb" class="mb-2">
          <!-- Desktop + Tablet: full breadcrumb -->
          <ol class="hidden sm:flex items-center flex-wrap gap-1 text-[10px] font-semibold text-gray-400 dark:text-zinc-500">
            <li v-for="(crumb, i) in breadcrumbs" :key="i" class="flex items-center gap-1">
              <span v-if="i > 0" class="text-gray-300 dark:text-zinc-600 select-none">›</span>
              <a
                v-if="!crumb.active"
                :href="crumb.url"
                class="hover:text-primary-600 dark:hover:text-primary-400 transition truncate max-w-[120px]"
                :title="crumb.label"
              >{{ crumb.label }}</a>
              <span
                v-else
                class="text-gray-700 dark:text-zinc-200 font-bold truncate max-w-[200px]"
                :title="crumb.label"
                aria-current="page"
              >{{ crumb.label }}</span>
            </li>
          </ol>
          <!-- Mobile: Beranda > ... > Judul -->
          <ol class="flex sm:hidden items-center gap-1 text-[10px] font-semibold text-gray-400 dark:text-zinc-500">
            <li>
              <a :href="breadcrumbs[0]?.url" class="hover:text-primary-600 transition">Beranda</a>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-gray-300 dark:text-zinc-600 select-none">›</span>
              <span class="text-gray-400 dark:text-zinc-500">...</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-gray-300 dark:text-zinc-600 select-none">›</span>
              <span class="text-gray-700 dark:text-zinc-200 font-bold truncate max-w-[160px]" aria-current="page">{{ news.title }}</span>
            </li>
          </ol>
        </nav>

        <!-- Title and Metadata -->
        <div class="space-y-3">
          <span 
            v-if="news.category" 
            class="text-[9px] font-bold font-mono uppercase tracking-wider border px-1.5 py-0.5 rounded"
            :style="{ 
              color: news.category.color, 
              backgroundColor: news.category.color + '15',
              borderColor: news.category.color + '30'
            }"
          >
            {{ news.category.name }}
          </span>
          
          <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-zinc-50 leading-tight">
            {{ news.title }}
          </h1>

          <!-- Publication History Metadata -->
          <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-[10px] text-gray-500 dark:text-zinc-400 font-semibold border-b border-gray-100 dark:border-zinc-800 pb-4 mt-4">
            <span>Penulis: <strong class="text-gray-700 dark:text-zinc-200">{{ news.author ? news.author.name : 'Admin' }}</strong></span>
            <span>•</span>
            <span>Dipublikasikan: <strong class="text-gray-700 dark:text-zinc-200">{{ formatDateTime(news.published_at) }}</strong></span>
            <template v-if="isUpdatedLater">
              <span>•</span>
              <span>Diperbarui: <strong class="text-gray-700 dark:text-zinc-200">{{ formatDateTime(news.updated_at) }}</strong></span>
            </template>
            <span>•</span>
            <span class="inline-flex items-center gap-1 text-primary-600 dark:text-primary-400 font-bold bg-primary-50 dark:bg-primary-950/40 px-2 py-0.5 rounded-lg border border-primary-200 dark:border-primary-900/30">
              👁 Dibaca oleh {{ (news.unique_readers || news.view_count || 0).toLocaleString('id-ID') }} pembaca
            </span>
          </div>
        </div>

        <!-- Social Share Bar Top -->
        <div class="pt-2 border-t border-b border-gray-100 dark:border-zinc-800 py-3 flex flex-wrap items-center justify-between gap-3 select-none">
          <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-zinc-400">
            <Share2 class="w-4 h-4 text-primary-600 dark:text-primary-400" />
            <span>Bagikan Berita Ini:</span>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <!-- Native Web Share API if supported -->
            <button 
              v-if="supportsNativeShare"
              @click="handleShare('native')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-500 text-white rounded-xl text-xs font-bold transition shadow-sm"
              title="Bagikan via Aplikasi Hp"
            >
              <Share2 class="w-3.5 h-3.5" />
              <span>Bagikan</span>
            </button>

            <!-- WhatsApp -->
            <button 
              @click="handleShare('whatsapp')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-sm"
              title="Bagikan ke WhatsApp"
            >
              <MessageCircle class="w-3.5 h-3.5" />
              <span class="hidden sm:inline">WhatsApp</span>
            </button>

            <!-- Facebook -->
            <button 
              @click="handleShare('facebook')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition shadow-sm"
              title="Bagikan ke Facebook"
            >
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              <span class="hidden sm:inline">Facebook</span>
            </button>

            <!-- Telegram -->
            <button 
              @click="handleShare('telegram')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-500 hover:bg-sky-400 text-white rounded-xl text-xs font-bold transition shadow-sm"
              title="Bagikan ke Telegram"
            >
              <Send class="w-3.5 h-3.5" />
              <span class="hidden sm:inline">Telegram</span>
            </button>

            <!-- X (Twitter) -->
            <button 
              @click="handleShare('twitter')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-xl text-xs font-bold transition shadow-sm border border-zinc-700"
              title="Bagikan ke X (Twitter)"
            >
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              <span class="hidden sm:inline">X / Twitter</span>
            </button>

            <!-- LinkedIn -->
            <button 
              @click="handleShare('linkedin')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-700 hover:bg-sky-600 text-white rounded-xl text-xs font-bold transition shadow-sm"
              title="Bagikan ke LinkedIn"
            >
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
              <span class="hidden sm:inline">LinkedIn</span>
            </button>

            <!-- Copy Link -->
            <button 
              @click="handleShare('copy')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-200 rounded-xl text-xs font-bold transition border border-gray-200 dark:border-zinc-700"
              title="Salin Link Tautan"
            >
              <Copy class="w-3.5 h-3.5" />
              <span>Salin Link</span>
            </button>
          </div>
        </div>

        <!-- Toast Copy Notification -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="transform translate-y-2 opacity-0"
          enter-to-class="transform translate-y-0 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="transform translate-y-0 opacity-100"
          leave-to-class="transform translate-y-2 opacity-0"
        >
          <div v-if="shareNotice" class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-2 border border-gray-700 text-xs font-bold">
            <CheckCircle2 class="w-4 h-4 text-emerald-400" />
            <span>{{ shareNoticeMsg }}</span>
          </div>
        </Transition>

        <!-- ===== PREMIUM HERO IMAGE ===== -->
        <div v-if="heroImageSrc" class="space-y-2.5">
          <!-- Hero Image Container -->
          <div
            class="relative w-full overflow-hidden rounded-[20px] shadow-xl bg-gray-100 dark:bg-zinc-800 cursor-zoom-in group"
            style="aspect-ratio: 16/9;"
            @click="openLightbox"
            aria-label="Klik untuk memperbesar gambar"
            role="button"
            tabindex="0"
            @keyup.enter="openLightbox"
          >
            <!-- Blur Placeholder -->
            <div
              class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 dark:from-zinc-700 dark:via-zinc-800 dark:to-zinc-700 animate-pulse transition-opacity duration-500"
              :class="heroImageLoaded ? 'opacity-0 pointer-events-none' : 'opacity-100'"
            ></div>

            <!-- Hero Image -->
            <img
              :src="heroImageSrc"
              :alt="heroAlt"
              class="w-full h-full object-cover transition-all duration-700 group-hover:scale-[1.02]"
              style="object-position: center;"
              :class="heroImageLoaded ? 'opacity-100' : 'opacity-0'"
              loading="eager"
              decoding="async"
              fetchpriority="high"
              width="1200"
              height="675"
              @load="heroImageLoaded = true"
              @error="onHeroImageError"
            />

            <!-- Zoom Overlay Icon -->
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-black/10">
              <div class="w-10 h-10 rounded-full bg-white/90 dark:bg-zinc-900/90 shadow-lg flex items-center justify-center">
                <ZoomIn class="w-5 h-5 text-gray-800 dark:text-zinc-100" />
              </div>
            </div>
          </div>

          <!-- Caption -->
          <p
            v-if="heroCaption"
            class="text-[10px] text-gray-400 dark:text-zinc-500 italic text-center leading-relaxed px-2"
          >
            📷 {{ heroCaption }}
          </p>
        </div>

        <!-- Article Divider -->
        <div class="flex items-center gap-3">
          <div class="flex-1 h-px bg-gradient-to-r from-transparent via-gray-200 dark:via-zinc-700 to-transparent"></div>
        </div>

        <!-- Article Content -->
        <article 
          class="prose prose-xs dark:prose-invert max-w-none text-gray-700 dark:text-zinc-300 leading-relaxed space-y-4"
          v-html="formattedContent"
        ></article>

        <!-- Article Reaction System -->
        <div class="p-6 bg-gray-50/80 dark:bg-zinc-800/60 rounded-2xl border border-gray-200/80 dark:border-zinc-700/80 space-y-4 text-center">
          <h4 class="text-xs font-bold text-gray-800 dark:text-zinc-100">Apakah informasi ini membantu?</h4>
          <div class="flex items-center justify-center gap-3 flex-wrap">
            <!-- Beneficial Button -->
            <button 
              @click="handleReaction('beneficial')" 
              :disabled="isReacting"
              class="px-5 py-2.5 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all shadow-xs active:scale-95 disabled:opacity-50"
              :class="activeReaction === 'beneficial' 
                ? 'bg-sky-600 text-white border-sky-600 ring-2 ring-sky-300 dark:ring-sky-900' 
                : 'bg-white dark:bg-zinc-900 text-gray-700 dark:text-zinc-200 border-gray-200 dark:border-zinc-700 hover:bg-sky-50 dark:hover:bg-zinc-800'"
              aria-label="Tandai Bermanfaat"
            >
              <span class="text-base">👍</span>
              <span>Bermanfaat</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black" :class="activeReaction === 'beneficial' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300'">
                {{ beneficialCount }}
              </span>
            </button>

            <!-- Informative Button -->
            <button 
              @click="handleReaction('informative')" 
              :disabled="isReacting"
              class="px-5 py-2.5 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all shadow-xs active:scale-95 disabled:opacity-50"
              :class="activeReaction === 'informative' 
                ? 'bg-rose-600 text-white border-rose-600 ring-2 ring-rose-300 dark:ring-rose-900' 
                : 'bg-white dark:bg-zinc-900 text-gray-700 dark:text-zinc-200 border-gray-200 dark:border-zinc-700 hover:bg-rose-50 dark:hover:bg-zinc-800'"
              aria-label="Tandai Informatif"
            >
              <span class="text-base">❤</span>
              <span>Informatif</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black" :class="activeReaction === 'informative' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300'">
                {{ informativeCount }}
              </span>
            </button>
          </div>
        </div>

        <!-- Smart Tags & Bottom Share Bar -->
        <div class="pt-6 space-y-4 border-t border-gray-100 dark:border-zinc-800">
          <div v-if="news.tags && news.tags.length > 0" class="space-y-2">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Tags</span>
            <div class="flex flex-wrap gap-1.5">
              <Link 
                v-for="tag in news.tags" 
                :key="tag.id"
                :href="route('public.news.tag', tag.slug)"
                class="px-3 py-1 bg-gray-50 hover:bg-primary-50 dark:bg-zinc-800 dark:hover:bg-primary-950/40 border border-gray-200 dark:border-zinc-700 hover:border-primary-300 font-mono text-[10px] rounded-xl uppercase font-bold text-gray-600 dark:text-zinc-300 hover:text-primary-600 transition"
              >
                #{{ tag.name }}
              </Link>
            </div>
          </div>

          <!-- Bottom Share Bar -->
          <div class="p-4 bg-gray-50 dark:bg-zinc-800/60 rounded-2xl border border-gray-200/60 dark:border-zinc-700/60 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs font-bold text-gray-700 dark:text-zinc-200 flex items-center gap-2">
              <Share2 class="w-4 h-4 text-primary-500" />
              <span>Suka artikel ini? Bagikan ke teman & keluarga Anda!</span>
            </div>
            <div class="flex items-center gap-2">
              <button @click="handleShare('whatsapp')" class="p-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-500 transition" title="WhatsApp"><MessageCircle class="w-4 h-4" /></button>
              <button @click="handleShare('facebook')" class="p-2 bg-blue-600 text-white rounded-xl hover:bg-blue-500 transition" title="Facebook"><svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></button>
              <button @click="handleShare('telegram')" class="p-2 bg-sky-500 text-white rounded-xl hover:bg-sky-400 transition" title="Telegram"><Send class="w-4 h-4" /></button>
              <button @click="handleShare('twitter')" class="p-2 bg-zinc-900 text-white rounded-xl hover:bg-zinc-800 transition" title="X"><svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button @click="handleShare('copy')" class="p-2 bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-zinc-200 rounded-xl hover:bg-gray-300 transition" title="Salin Link"><Copy class="w-4 h-4" /></button>
            </div>
          </div>

          <!-- Smart Navigation (Previous / Next Article) -->
          <div v-if="navigation && (navigation.previous || navigation.next)" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-gray-100 dark:border-zinc-800">
            <!-- Previous Article -->
            <Link 
              v-if="navigation.previous"
              :href="route('public.news.show', navigation.previous.slug)"
              class="p-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-xs hover:border-primary-400 transition group flex items-center gap-3 text-left"
            >
              <div class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-zinc-800 text-gray-500 group-hover:bg-primary-50 group-hover:text-primary-600 flex items-center justify-center shrink-0 font-bold transition">←</div>
              <div class="min-w-0 flex-1 space-y-0.5">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">← Berita Sebelumnya</span>
                <h5 class="text-xs font-bold text-gray-800 dark:text-zinc-100 line-clamp-1 group-hover:text-primary-600 transition">{{ navigation.previous.title }}</h5>
                <span class="text-[9px] text-gray-400 block">{{ formatDate(navigation.previous.published_at) }}</span>
              </div>
            </Link>
            <div v-else class="hidden sm:block"></div>

            <!-- Next Article -->
            <Link 
              v-if="navigation.next"
              :href="route('public.news.show', navigation.next.slug)"
              class="p-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-xs hover:border-primary-400 transition group flex items-center gap-3 text-right"
            >
              <div class="min-w-0 flex-1 space-y-0.5">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Berita Selanjutnya →</span>
                <h5 class="text-xs font-bold text-gray-800 dark:text-zinc-100 line-clamp-1 group-hover:text-primary-600 transition">{{ navigation.next.title }}</h5>
                <span class="text-[9px] text-gray-400 block">{{ formatDate(navigation.next.published_at) }}</span>
              </div>
              <div class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-zinc-800 text-gray-500 group-hover:bg-primary-50 group-hover:text-primary-600 flex items-center justify-center shrink-0 font-bold transition">→</div>
            </Link>
            <div v-else class="hidden sm:block"></div>
          </div>
        </div>
      </div>

      <!-- Related Articles Section -->
      <div class="space-y-6 pt-6" v-if="related && related.length > 0">
        <h3 class="text-lg font-black text-gray-900 dark:text-zinc-50 tracking-tight">
          📰 Berita Terkait
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Link 
            v-for="item in related" 
            :key="item.id"
            :href="route('public.news.show', item.slug)"
            class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm overflow-hidden flex flex-col justify-between group"
          >
            <div class="space-y-2">
              <span 
                v-if="item.category" 
                class="text-[8px] font-bold font-mono uppercase tracking-wider border px-1.5 py-0.5 rounded"
                :style="{ 
                  color: item.category.color, 
                  backgroundColor: item.category.color + '15',
                  borderColor: item.category.color + '30'
                }"
              >
                {{ item.category.name }}
              </span>
              <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-xs line-clamp-2 leading-snug group-hover:text-primary-600 transition">
                {{ item.title }}
              </h4>
            </div>
            <p class="text-[9px] text-gray-400 font-semibold mt-4">
              {{ formatDate(item.published_at) }}
            </p>
          </Link>
        </div>
      </div>
    </div>

    <!-- ===== LIGHTBOX MODAL ===== -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="lightboxOpen"
        class="fixed inset-0 z-[9999] bg-black/95 backdrop-blur-sm flex items-center justify-center p-4 select-none"
        @click.self="closeLightbox"
        @keyup.esc="closeLightbox"
        tabindex="-1"
        ref="lightboxEl"
        aria-modal="true"
        role="dialog"
        :aria-label="heroAlt"
      >
        <!-- Close Button -->
        <button
          @click="closeLightbox"
          class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition backdrop-blur-sm border border-white/20"
          aria-label="Tutup"
        >
          <X class="w-5 h-5" />
        </button>

        <!-- Zoom Controls -->
        <div class="absolute top-4 left-1/2 -translate-x-1/2 z-10 flex items-center gap-2">
          <button
            @click="lightboxScale = Math.max(0.5, lightboxScale - 0.25)"
            class="w-9 h-9 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition backdrop-blur-sm border border-white/20 text-lg font-bold"
            aria-label="Perkecil"
          >−</button>
          <span class="text-white text-xs font-bold bg-white/10 px-3 py-1.5 rounded-full backdrop-blur-sm border border-white/20 min-w-[54px] text-center">
            {{ Math.round(lightboxScale * 100) }}%
          </span>
          <button
            @click="lightboxScale = Math.min(4, lightboxScale + 0.25)"
            class="w-9 h-9 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition backdrop-blur-sm border border-white/20 text-lg font-bold"
            aria-label="Perbesar"
          >+</button>
          <button
            @click="lightboxScale = 1; lightboxOffsetX = 0; lightboxOffsetY = 0"
            class="w-9 h-9 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition backdrop-blur-sm border border-white/20 text-[9px] font-bold"
            aria-label="Reset"
          >1:1</button>
        </div>

        <!-- Image Wrapper (draggable) -->
        <div
          class="w-full max-w-5xl max-h-[90vh] overflow-hidden flex items-center justify-center"
          @mousedown="onLightboxDragStart"
          @mousemove="onLightboxDragMove"
          @mouseup="onLightboxDragEnd"
          @mouseleave="onLightboxDragEnd"
          @touchstart.passive="onLightboxTouchStart"
          @touchmove.passive="onLightboxTouchMove"
          @touchend="onLightboxDragEnd"
          :style="{ cursor: lightboxDragging ? 'grabbing' : (lightboxScale > 1 ? 'grab' : 'default') }"
        >
          <img
            :src="heroImageSrc"
            :alt="heroAlt"
            class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl transition-transform duration-100 will-change-transform pointer-events-none"
            :style="{ transform: `scale(${lightboxScale}) translate(${lightboxOffsetX / lightboxScale}px, ${lightboxOffsetY / lightboxScale}px)` }"
            loading="eager"
          />
        </div>

        <!-- Caption in lightbox -->
        <div v-if="heroCaption" class="absolute bottom-4 left-1/2 -translate-x-1/2 w-full max-w-2xl px-4 text-center">
          <p class="text-white/70 text-xs italic bg-black/40 px-4 py-2 rounded-xl backdrop-blur-sm inline-block">
            📷 {{ heroCaption }}
          </p>
        </div>
      </div>
    </Transition>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import axios from 'axios';
import { 
  Share2, MessageCircle, Send, Copy, CheckCircle2, Globe, ZoomIn, X
} from '@lucide/vue';

const props = defineProps({
  news: Object,
  userReaction: String,
  navigation: Object,
  related: Array,
  seo: Object,
  breadcrumbs: Array,
});

// ---- Share State ----
const shareNotice = ref(false);
const shareNoticeMsg = ref('');
const supportsNativeShare = computed(() => typeof navigator !== 'undefined' && !!navigator.share);

// ---- Reaction State ----
const activeReaction = ref(props.userReaction || null);
const beneficialCount = ref(props.news?.beneficial_count || 0);
const informativeCount = ref(props.news?.informative_count || 0);
const isReacting = ref(false);

// ---- Hero Image State ----
const heroImageLoaded = ref(false);
const heroImageFailed = ref(false);

// ---- Lightbox State ----
const lightboxOpen = ref(false);
const lightboxScale = ref(1);
const lightboxOffsetX = ref(0);
const lightboxOffsetY = ref(0);
const lightboxDragging = ref(false);
const lightboxDragStart = ref({ x: 0, y: 0 });
const lightboxEl = ref(null);

// ---- Computed ----
const isUpdatedLater = computed(() => {
  if (!props.news?.published_at || !props.news?.updated_at) return false;
  const pub = new Date(props.news.published_at).getTime();
  const upd = new Date(props.news.updated_at).getTime();
  return (upd - pub) > 60000;
});

/**
 * Hero Image Priority:
 * 1. social_image / og_image
 * 2. thumbnail
 * 3. First <img> in content
 * 4. Logo fallback
 */
const heroImageSrc = computed(() => {
  if (heroImageFailed.value) return null;
  const n = props.news;
  if (!n) return null;

  // 1. social_image / og_image
  if (n.social_image && !n.social_image.startsWith('data:')) return n.social_image;
  if (n.og_image && !n.og_image.startsWith('data:')) return n.og_image;

  // 2. thumbnail (already transformed by controller)
  if (n.thumbnail && !n.thumbnail.startsWith('data:')) return n.thumbnail;

  // 3. First img tag in content
  if (n.content) {
    const m = n.content.match(/<img[^>]+src=["']([^"']+)["']/i);
    if (m && m[1] && !m[1].startsWith('data:')) return m[1];
  }

  return null;
});

const heroAlt = computed(() => {
  return props.news?.thumbnail_alt || props.news?.image_alt || props.news?.title || 'Gambar Berita';
});

const heroCaption = computed(() => {
  return props.news?.thumbnail_caption || null;
});

// ---- Lightbox Functions ----
const openLightbox = () => {
  lightboxOpen.value = true;
  lightboxScale.value = 1;
  lightboxOffsetX.value = 0;
  lightboxOffsetY.value = 0;
  nextTick(() => lightboxEl.value?.focus());
};

const closeLightbox = () => {
  lightboxOpen.value = false;
};

const onLightboxDragStart = (e) => {
  if (lightboxScale.value <= 1) return;
  lightboxDragging.value = true;
  lightboxDragStart.value = { x: e.clientX - lightboxOffsetX.value, y: e.clientY - lightboxOffsetY.value };
};

const onLightboxDragMove = (e) => {
  if (!lightboxDragging.value) return;
  lightboxOffsetX.value = e.clientX - lightboxDragStart.value.x;
  lightboxOffsetY.value = e.clientY - lightboxDragStart.value.y;
};

const onLightboxDragEnd = () => {
  lightboxDragging.value = false;
};

let touchStartX = 0, touchStartY = 0;
const onLightboxTouchStart = (e) => {
  if (e.touches.length === 1) {
    touchStartX = e.touches[0].clientX - lightboxOffsetX.value;
    touchStartY = e.touches[0].clientY - lightboxOffsetY.value;
    lightboxDragging.value = true;
  }
};

const onLightboxTouchMove = (e) => {
  if (!lightboxDragging.value || e.touches.length !== 1) return;
  lightboxOffsetX.value = e.touches[0].clientX - touchStartX;
  lightboxOffsetY.value = e.touches[0].clientY - touchStartY;
};

// Keyboard handler
const onKeyDown = (e) => {
  if (lightboxOpen.value && e.key === 'Escape') closeLightbox();
};

// ---- Hero Image Error Handler ----
const onHeroImageError = () => {
  heroImageFailed.value = true;
};

// ---- Reaction Handler ----
const handleReaction = async (type) => {
  if (!props.news?.id || isReacting.value) return;
  isReacting.value = true;
  try {
    const res = await axios.post(route('public.news.react', props.news.id), { reaction_type: type });
    if (res.data && res.data.success) {
      activeReaction.value = res.data.user_reaction;
      beneficialCount.value = res.data.beneficial_count;
      informativeCount.value = res.data.informative_count;
    }
  } catch (e) {
    console.error('Reaction submission error:', e);
  } finally {
    isReacting.value = false;
  }
};

// ---- Share Handler ----
const trackShare = async (platform) => {
  if (!props.news?.id) return;
  try {
    await axios.post(route('public.news.track-share', props.news.id), { platform });
  } catch (e) { /* silent */ }
};

const handleShare = (platform) => {
  trackShare(platform);
  const currentUrl = typeof window !== 'undefined' ? window.location.href : (props.seo?.canonical || '');
  const targetUrl = props.seo?.canonical || currentUrl;
  
  const url = encodeURIComponent(targetUrl);
  const title = encodeURIComponent(props.news?.title || '');

  if (platform === 'whatsapp') {
    window.open(`https://api.whatsapp.com/send?text=${title}%20${url}`, '_blank');
  } else if (platform === 'facebook') {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
  } else if (platform === 'telegram') {
    window.open(`https://t.me/share/url?url=${url}&text=${title}`, '_blank');
  } else if (platform === 'twitter') {
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank');
  } else if (platform === 'linkedin') {
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank');
  } else if (platform === 'email') {
    window.location.href = `mailto:?subject=${title}&body=${url}`;
  } else if (platform === 'copy') {
    if (navigator.clipboard) navigator.clipboard.writeText(targetUrl);
    shareNoticeMsg.value = 'Link berita berhasil disalin ke clipboard!';
    shareNotice.value = true;
    setTimeout(() => { shareNotice.value = false; }, 3000);
  } else if (platform === 'native' && navigator.share) {
    navigator.share({
      title: props.news?.title,
      text: props.news?.excerpt,
      url: targetUrl,
    }).catch(() => {});
  }
};

// ---- Date Formatters ----
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const dateFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  const timeFormatted = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
  return `${dateFormatted} ${timeFormatted} WITA`;
};

// ---- Content Formatter (YouTube / Drive embeds) ----
const formattedContent = computed(() => {
  if (!props.news?.content && !props.news?.excerpt) return '';
  
  let html = props.news?.content || '';

  // Remove <header>...</header> (WordPress meta)
  html = html.replace(/<header[^>]*>[\s\S]*?<\/header>/gi, '');

  // Remove empty paragraphs
  const emptyPRegex = /<p[^>]*>(?:\s|&nbsp;|<br\s*\/?>|<strong>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/strong>|<b>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/b>|<span>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/span>)*<\/p>/gi;
  let prev;
  do {
    prev = html;
    html = html.replace(emptyPRegex, '');
  } while (html !== prev);

  let hasVideo = false;

  // YouTube & Google Drive oembeds
  const mediaPattern = /<figure[^>]*class="media"[^>]*>\s*<oembed[^>]+url=["']([^"']+)["'][^>]*>(?:<\/oembed>)?\s*<\/figure>|<oembed[^>]+url=["']([^"']+)["'][^>]*>(?:<\/oembed>)?/gi;
  html = html.replace(mediaPattern, (match, url1, url2) => {
    const url = url1 || url2;
    if (!url) return match;
    
    const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (ytMatch && ytMatch[1]) {
      hasVideo = true;
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black"><iframe src="https://www.youtube-nocookie.com/embed/${ytMatch[1]}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen title="Video YouTube"></iframe></div>`;
    }

    const driveMatch = url.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (driveMatch && driveMatch[1]) {
      hasVideo = true;
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black border border-gray-200 dark:border-zinc-800"><iframe src="https://drive.google.com/file/d/${driveMatch[1]}/preview" class="w-full h-full border-0" allow="autoplay" allowfullscreen title="Video Google Drive"></iframe></div>`;
    }
    return match;
  });

  // Direct Google Drive URLs
  const directDrivePattern = /(?:<p[^>]*>\s*)?(?:<a[^>]*href=["'])?https?:\/\/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)(?:[^\s<"']*)?(?:["'][^>]*>.*?<\/a>)?(?:\s*<\/p>)?/gi;
  html = html.replace(directDrivePattern, (match, driveId) => {
    if (driveId) {
      hasVideo = true;
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black border border-gray-200 dark:border-zinc-800"><iframe src="https://drive.google.com/file/d/${driveId}/preview" class="w-full h-full border-0" allow="autoplay" allowfullscreen title="Video Google Drive"></iframe></div>`;
    }
    return match;
  });

  if (!hasVideo && props.news?.excerpt) {
    const excerptDriveMatch = props.news.excerpt.match(/https?:\/\/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/i);
    if (excerptDriveMatch && excerptDriveMatch[1]) {
      const driveId = excerptDriveMatch[1];
      html = `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black border border-gray-200 dark:border-zinc-800"><iframe src="https://drive.google.com/file/d/${driveId}/preview" class="w-full h-full border-0" allow="autoplay" allowfullscreen title="Video Google Drive"></iframe></div>` + html;
    }
  }

  return html;
});

// ---- Inject BreadcrumbList JSON-LD ----
const injectBreadcrumbSchema = () => {
  if (!props.breadcrumbs || !props.breadcrumbs.length) return;
  const existing = document.getElementById('breadcrumb-jsonld');
  if (existing) existing.remove();
  
  const schema = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": props.breadcrumbs.map((crumb, idx) => ({
      "@type": "ListItem",
      "position": idx + 1,
      "name": crumb.label,
      "item": crumb.url
    }))
  };
  const script = document.createElement('script');
  script.type = 'application/ld+json';
  script.id = 'breadcrumb-jsonld';
  script.textContent = JSON.stringify(schema);
  document.head.appendChild(script);
};

onMounted(() => {
  // Hide broken images inside article
  document.querySelectorAll('article img').forEach(img => {
    img.onerror = () => {
      img.style.display = 'none';
      const parent = img.closest('figure, p');
      if (parent && !parent.textContent.trim()) parent.style.display = 'none';
    };
  });

  // Inject BreadcrumbList JSON-LD
  injectBreadcrumbSchema();

  // Keyboard listener for lightbox Esc
  window.addEventListener('keydown', onKeyDown);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeyDown);
});
</script>

<style scoped>
/* Phase 1: Hero Image - object-position:center, no focus point picker needed */
.hero-image-container img {
  width: 100%;
  aspect-ratio: 16 / 9;
  object-fit: cover;
  object-position: center;
}

:deep(article img) {
  display: block;
  max-width: 100% !important;
  max-height: 520px !important;
  width: auto !important;
  height: auto !important;
  object-fit: contain !important;
  margin: 1.5rem auto !important;
  border-radius: 1.25rem !important;
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04) !important;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
