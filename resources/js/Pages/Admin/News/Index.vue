<template>
  <Head title="Manajemen Berita & Artikel" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Berita & Artikel</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Tulis, edit, dan jadwalkan artikel berita resmi Dinas Kependudukan dan Pencatatan Sipil.
          </p>
        </div>
        <div class="flex gap-2 flex-wrap">
          <button 
            @click="openReaderAnalytics"
            class="flex items-center gap-1.5 px-4 py-2 border border-sky-200 dark:border-sky-900/40 bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 text-sky-700 dark:text-sky-300 text-xs font-bold rounded-xl active:scale-95 transition"
          >
            <Eye class="w-4 h-4 text-sky-600" />
            Analytics Pembaca
          </button>
          <button 
            @click="openShareAnalytics"
            class="flex items-center gap-1.5 px-4 py-2 border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 text-xs font-bold rounded-xl active:scale-95 transition"
          >
            <Share2 class="w-4 h-4 text-emerald-600" />
            Analytics Share
          </button>
          <Link 
            :href="route('admin.news.categories')"
            class="flex items-center gap-1.5 px-4 py-2 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-600 dark:text-zinc-200 text-xs font-bold rounded-xl active:scale-95 transition"
          >
            Kategori
          </Link>
          <Link 
            :href="route('admin.news.create')"
            class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition"
          >
            <Plus class="w-4 h-4" />
            Tulis Berita
          </Link>
        </div>
      </div>

      <!-- Reader Analytics Summary Cards Grid -->
      <div v-if="readerStats" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Unique Readers</span>
            <Users class="w-4 h-4 text-sky-500" />
          </div>
          <p class="text-2xl font-black text-sky-600 dark:text-sky-400">{{ (readerStats.total_unique_readers || 0).toLocaleString('id-ID') }}</p>
          <p class="text-[10px] text-gray-400">Pengunjung unik (Human 24j)</p>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Page Views</span>
            <Eye class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ (readerStats.total_page_views || 0).toLocaleString('id-ID') }}</p>
          <p class="text-[10px] text-gray-400">Total muat halaman</p>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Views Hari Ini</span>
            <Calendar class="w-4 h-4 text-blue-500" />
          </div>
          <p class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ (readerStats.today_views || 0).toLocaleString('id-ID') }}</p>
          <p class="text-[10px] text-gray-400">Pembaca hari ini</p>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Robot Hits</span>
            <Bot class="w-4 h-4 text-amber-500" />
          </div>
          <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ (readerStats.total_robot_hits || 0).toLocaleString('id-ID') }}</p>
          <p class="text-[10px] text-gray-400">Filtered bot & crawlers</p>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center flex-1">
          <!-- Search input -->
          <div class="relative w-full max-w-xs">
            <Search class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" />
            <input 
              type="text" 
              v-model="search" 
              @input="handleSearch"
              placeholder="Cari judul berita..." 
              class="w-full pl-9 pr-8 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            />
            <button 
              v-if="search" 
              type="button"
              @click="clearSearch"
              class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
              title="Bersihkan Pencarian"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Category filter -->
          <select 
            v-model="category" 
            class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            @change="applyFilters"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>

          <!-- Status filter -->
          <select 
            v-model="status" 
            class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            @change="applyFilters"
          >
            <option value="">Semua Status</option>
            <option value="published">Diterbitkan</option>
            <option value="draft">Draft</option>
            <option value="scheduled">Dijadwalkan</option>
          </select>
        </div>

        <div v-if="search || category || status" class="flex items-center gap-2">
          <button 
            type="button"
            @click="resetAllFilters"
            class="px-3.5 py-2 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-600 dark:text-zinc-300 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
            title="Reset Seluruh Filter"
          >
            <RotateCcw class="w-3.5 h-3.5" />
            Reset Filter
          </button>
        </div>
      </div>

      <!-- Articles Grid Container -->
      <div class="space-y-4">
        <!-- A. Mobile Card View (Visible only on screens < 640px) -->
        <div class="block sm:hidden space-y-3">
          <div v-if="news.data.length === 0" class="p-6 bg-white dark:bg-zinc-900 rounded-3xl border border-gray-100 dark:border-zinc-800 text-center text-gray-400 text-xs font-bold">
            Belum ada artikel berita terdaftar.
          </div>

          <div v-for="item in news.data" :key="'mob-' + item.id" class="p-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm space-y-3">
            <!-- Mobile Card Top: Thumbnail + Category + Badges + Title -->
            <div class="flex items-start gap-3">
              <img 
                :src="item.thumbnail ? `/uploads/${item.thumbnail.replace(/^\/?(storage|uploads)\//, '')}` : '/img/placeholder.webp'" 
                class="w-14 h-14 rounded-xl object-cover border border-gray-100 dark:border-zinc-800 shrink-0" 
                alt="Thumbnail" 
              />
              <div class="min-w-0 flex-1 space-y-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span v-if="item.category" class="px-1.5 py-0.5 font-bold font-mono text-[9px] rounded uppercase border"
                    :style="{ color: item.category.color, backgroundColor: item.category.color + '15', borderColor: item.category.color + '30' }">
                    {{ item.category.name }}
                  </span>
                  <span v-if="item.is_featured" class="px-1.5 py-0.5 bg-amber-50 text-amber-700 dark:bg-amber-950/40 border border-amber-100 text-[9px] font-bold rounded uppercase">
                    Featured
                  </span>
                  <span v-if="item.is_breaking" class="px-1.5 py-0.5 bg-red-50 text-red-700 dark:bg-red-950/40 border border-red-100 text-[9px] font-bold rounded uppercase">
                    Breaking
                  </span>
                </div>
                <h4 class="font-bold text-xs text-gray-900 dark:text-zinc-100 line-clamp-2 leading-snug">
                  {{ item.title }}
                </h4>
              </div>
            </div>

            <!-- Mobile Card Meta Row: Status + Unique Readers + Page Views -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-zinc-800/80 text-[10px]">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wide border"
                  :class="{
                    'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400': item.status === 'published',
                    'bg-gray-100 text-gray-600 border-gray-200 dark:bg-zinc-800 dark:text-zinc-400': item.status === 'draft',
                    'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-950/40 dark:text-blue-400': item.status === 'scheduled'
                  }">
                  {{ item.status }}
                </span>
                <span class="font-bold text-sky-600 dark:text-sky-400">
                  👤 {{ item.unique_readers || item.view_count || 0 }} Unik
                </span>
                <span class="text-gray-400">
                  📄 {{ item.page_views || item.view_count || 0 }} Views
                </span>
              </div>
              <span class="text-gray-400 font-medium">
                {{ item.published_at ? formatDate(item.published_at) : 'Draft' }}
              </span>
            </div>

            <!-- Mobile Card Actions: Edit & Hapus Buttons -->
            <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
              <Link 
                :href="route('admin.news.edit', item.id)"
                class="flex-1 py-2 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 text-gray-700 dark:text-zinc-200 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 transition"
              >
                <Edit class="w-3.5 h-3.5 text-primary-500" />
                <span>Edit Berita</span>
              </Link>
              <button 
                @click="deleteNews(item)"
                class="flex-1 py-2 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 transition"
              >
                <Trash2 class="w-3.5 h-3.5" />
                <span>Hapus Berita</span>
              </button>
            </div>
          </div>
        </div>

        <!-- B. Desktop / Tablet Table View (Visible on screens >= 640px) -->
        <div class="hidden sm:block bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                  <th class="px-6 py-4">Thumbnail / Judul Berita</th>
                  <th class="px-6 py-4 hidden md:table-cell">Kategori</th>
                  <th class="px-6 py-4 hidden lg:table-cell">Penulis</th>
                  <th class="px-6 py-4">Status</th>
                  <th class="px-6 py-4">Dibaca</th>
                  <th class="px-6 py-4">Tanggal Rilis</th>
                  <th class="px-6 py-4 text-center font-bold sticky right-0 bg-gray-50 dark:bg-zinc-800 z-20 border-l border-gray-200/80 dark:border-zinc-700 min-w-[130px]">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
                <tr v-if="news.data.length === 0">
                  <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                    Belum ada artikel berita terdaftar.
                  </td>
                </tr>
                <tr 
                  v-for="item in news.data" 
                  :key="item.id"
                  class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition duration-150"
                >
                  <!-- Image & Title -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3.5 max-w-xs md:max-w-sm">
                      <img 
                        :src="item.thumbnail ? `/uploads/${item.thumbnail.replace(/^\/?(storage|uploads)\//, '')}` : '/img/placeholder.webp'" 
                        class="w-12 h-12 rounded-lg object-cover border border-gray-100 dark:border-zinc-800 shrink-0" 
                        alt="Thumbnail" 
                      />
                      <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                          <span v-if="item.is_featured" class="px-1.5 py-0.5 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30 text-[9px] font-bold rounded uppercase">
                            Featured
                          </span>
                          <span v-if="item.is_breaking" class="px-1.5 py-0.5 bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-100 dark:border-red-900/30 text-[9px] font-bold rounded uppercase">
                            Breaking
                          </span>
                        </div>
                        <p class="font-bold text-gray-800 dark:text-zinc-200 truncate leading-snug" :title="item.title">
                          {{ item.title }}
                        </p>
                        <p class="text-[10px] text-gray-400 truncate mt-0.5">/news/{{ item.slug }}</p>
                      </div>
                    </div>
                  </td>

                  <!-- Category -->
                  <td class="px-6 py-4 hidden md:table-cell">
                    <span 
                      v-if="item.category" 
                      class="px-2 py-0.5 font-bold font-mono text-[9px] rounded uppercase border"
                      :style="{ 
                        color: item.category.color, 
                        backgroundColor: item.category.color + '15',
                        borderColor: item.category.color + '30'
                      }"
                    >
                      {{ item.category.name }}
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>

                  <!-- Author -->
                  <td class="px-6 py-4 hidden lg:table-cell font-medium text-gray-700 dark:text-zinc-300">
                    {{ item.author ? item.author.name : 'Unknown' }}
                  </td>

                  <!-- Status badge -->
                  <td class="px-6 py-4">
                    <span 
                      class="px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wide border"
                      :class="{
                        'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/30': item.status === 'published',
                        'bg-gray-100 text-gray-600 border-gray-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700': item.status === 'draft',
                        'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-900/30': item.status === 'scheduled'
                      }"
                    >
                      {{ item.status }}
                    </span>
                  </td>

                  <!-- Hits view count -->
                  <td class="px-6 py-4">
                    <div class="space-y-0.5 whitespace-nowrap">
                      <span class="text-xs font-black text-sky-600 dark:text-sky-400 block" title="Unique Human Readers (TTL 24j)">
                        👤 {{ item.unique_readers || item.view_count || 0 }} Unik
                      </span>
                      <span class="text-[10px] text-gray-400 block" title="Total Page Views">
                        📄 {{ item.page_views || item.view_count || 0 }} Views
                      </span>
                    </div>
                  </td>

                  <!-- Release Date -->
                  <td class="px-6 py-4 text-gray-400 font-medium whitespace-nowrap">
                    {{ item.published_at ? formatDate(item.published_at) : 'TBD' }}
                  </td>

                  <!-- Actions (Sticky on right edge) -->
                  <td class="px-6 py-4 text-center sticky right-0 bg-white dark:bg-zinc-900 z-20 border-l border-gray-100 dark:border-zinc-800 shadow-xs min-w-[130px]">
                    <div class="relative z-30 flex items-center justify-center gap-1.5 pointer-events-auto">
                      <Link 
                        :href="route('admin.news.edit', item.id)"
                        class="px-2.5 py-1.5 bg-gray-100 dark:bg-zinc-800 hover:bg-primary-500 hover:text-white dark:hover:bg-primary-600 text-gray-700 dark:text-zinc-200 text-xs font-bold rounded-xl transition flex items-center gap-1 cursor-pointer select-none active:scale-95"
                        title="Edit Berita"
                      >
                        <Edit class="w-3.5 h-3.5 text-primary-500 hover:text-white" />
                        <span>Edit</span>
                      </Link>
                      <button 
                        type="button"
                        @click.stop.prevent="deleteNews(item)"
                        class="px-2.5 py-1.5 bg-red-50 dark:bg-red-950/40 hover:bg-rose-600 hover:text-white text-red-600 dark:text-red-400 border border-red-200/60 dark:border-red-900/30 text-xs font-bold rounded-xl transition flex items-center gap-1 cursor-pointer select-none active:scale-95"
                        title="Hapus Berita"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                        <span>Hapus</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="news.links && news.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800/60 bg-gray-50/50 dark:bg-zinc-900/50 flex justify-center gap-1">
            <Link 
              v-for="(link, i) in news.links" 
              :key="i"
              :href="link.url || '#'"
              v-html="link.label"
              :disabled="!link.url"
              class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition"
              :class="[
                link.active 
                  ? 'bg-primary-600 text-white shadow-sm' 
                  : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-600 dark:text-zinc-400',
                !link.url ? 'opacity-40 pointer-events-none' : ''
              ]"
            />
          </div>
        </div>
      </div>

      <!-- Custom Vue Modal Confirm Delete News -->
      <transition name="fade">
        <div v-if="deletingNews" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="deletingNews = null">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 text-left relative space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <Trash2 class="w-5 h-5" />
              </div>
              <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50">Hapus Artikel Berita</h3>
            </div>
            <p class="text-xs text-gray-600 dark:text-zinc-300 leading-relaxed">
              Apakah Anda yakin ingin menghapus berita <strong>"{{ deletingNews.title }}"</strong>? Berita yang dihapus dapat dipulihkan kembali jika diperlukan.
            </p>
            <div class="flex justify-end gap-2 pt-2">
              <button @click="deletingNews = null" class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 transition" :disabled="isDeleting">
                Batal
              </button>
              <button @click="executeDeleteNews" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl active:scale-95 transition" :disabled="isDeleting">
                {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Berita' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Share Analytics Modal -->
      <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
        <div v-if="shareAnalyticsOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-4xl shadow-2xl p-6 text-left relative space-y-6 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                  <Share2 class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="text-base font-black text-gray-900 dark:text-zinc-50">Social Share Analytics</h3>
                  <p class="text-xs text-gray-500 dark:text-zinc-400">Statistik performa share berita ke media sosial & pesan instan.</p>
                </div>
              </div>
              <button @click="shareAnalyticsOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 transition">
                <X class="w-5 h-5" />
              </button>
            </div>

            <div v-if="loadingAnalytics" class="py-12 text-center text-gray-400 text-xs font-bold space-y-2">
              <Loader2 class="w-6 h-6 animate-spin mx-auto text-primary-500" />
              <p>Memuat statistik share berita...</p>
            </div>

            <div v-else-if="analyticsData" class="space-y-6">
              <!-- Summary Cards Grid -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 rounded-2xl space-y-1">
                  <span class="text-[10px] font-bold uppercase text-gray-400">Total Share</span>
                  <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ analyticsData.total_shares }}</p>
                  <span class="text-[9px] text-gray-400">Semua platform</span>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 rounded-2xl space-y-1">
                  <span class="text-[10px] font-bold uppercase text-gray-400">Share Hari Ini</span>
                  <p class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ analyticsData.shares_today }}</p>
                  <span class="text-[9px] text-gray-400">Tercatat hari ini</span>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 rounded-2xl space-y-1">
                  <span class="text-[10px] font-bold uppercase text-gray-400">Share Bulan Ini</span>
                  <p class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ analyticsData.shares_this_month }}</p>
                  <span class="text-[9px] text-gray-400">Bulan {{ new Date().toLocaleString('id-ID', { month: 'long' }) }}</span>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 rounded-2xl space-y-1">
                  <span class="text-[10px] font-bold uppercase text-gray-400">CTR Share</span>
                  <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ analyticsData.ctr_share }}%</p>
                  <span class="text-[9px] text-gray-400">Ratio share / pembaca</span>
                </div>
              </div>

              <!-- Platform Breakdown Grid -->
              <div class="space-y-3">
                <h4 class="text-xs font-black uppercase text-gray-500 tracking-wider">Distribusi Share per Platform</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 rounded-xl flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">WhatsApp</span>
                    <span class="text-sm font-black text-emerald-700 dark:text-emerald-400">{{ analyticsData.platform_breakdown.whatsapp }}</span>
                  </div>
                  <div class="p-3 bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-900/40 rounded-xl flex items-center justify-between">
                    <span class="text-xs font-bold text-blue-800 dark:text-blue-300">Facebook</span>
                    <span class="text-sm font-black text-blue-700 dark:text-blue-400">{{ analyticsData.platform_breakdown.facebook }}</span>
                  </div>
                  <div class="p-3 bg-sky-50/60 dark:bg-sky-950/30 border border-sky-200/60 dark:border-sky-900/40 rounded-xl flex items-center justify-between">
                    <span class="text-xs font-bold text-sky-800 dark:text-sky-300">Telegram</span>
                    <span class="text-sm font-black text-sky-700 dark:text-sky-400">{{ analyticsData.platform_breakdown.telegram }}</span>
                  </div>
                  <div class="p-3 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-300">X (Twitter)</span>
                    <span class="text-sm font-black text-zinc-900 dark:text-zinc-100">{{ analyticsData.platform_breakdown.twitter }}</span>
                  </div>
                  <div class="p-3 bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-900/40 rounded-xl flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-800 dark:text-indigo-300">LinkedIn</span>
                    <span class="text-sm font-black text-indigo-700 dark:text-indigo-400">{{ analyticsData.platform_breakdown.linkedin }}</span>
                  </div>
                  <div class="p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 dark:text-zinc-300">Salin Link</span>
                    <span class="text-sm font-black text-gray-900 dark:text-zinc-100">{{ analyticsData.platform_breakdown.copy_link }}</span>
                  </div>
                </div>
              </div>

              <!-- Top Shared News Articles Table -->
              <div class="space-y-3 pt-2">
                <h4 class="text-xs font-black uppercase text-gray-500 tracking-wider">Top Berita Paling Banyak Dibagikan</h4>
                <div class="overflow-x-auto border border-gray-100 dark:border-zinc-800 rounded-2xl">
                  <table class="w-full text-left border-collapse text-xs">
                    <thead>
                      <tr class="bg-gray-50 dark:bg-zinc-800 text-[10px] font-black uppercase tracking-wider text-gray-400 border-b border-gray-100 dark:border-zinc-800">
                        <th class="p-3 w-10 text-center">No</th>
                        <th class="p-3">Judul Berita</th>
                        <th class="p-3 text-center">Pembaca</th>
                        <th class="p-3 text-center">Total Share</th>
                        <th class="p-3 text-center">WhatsApp</th>
                        <th class="p-3 text-center">Facebook</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                      <tr v-if="!analyticsData.top_shared_news || analyticsData.top_shared_news.length === 0">
                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada statistik share tercatat.</td>
                      </tr>
                      <tr v-for="(item, idx) in analyticsData.top_shared_news" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/50 transition">
                        <td class="p-3 text-center font-bold text-gray-400">{{ idx + 1 }}</td>
                        <td class="p-3 font-bold text-gray-900 dark:text-zinc-100 max-w-xs truncate">{{ item.title }}</td>
                        <td class="p-3 text-center font-mono font-bold text-gray-600 dark:text-zinc-400">{{ item.view_count || 0 }}</td>
                        <td class="p-3 text-center font-mono font-black text-emerald-600 dark:text-emerald-400">{{ item.share_count }}</td>
                        <td class="p-3 text-center font-mono text-xs font-bold text-emerald-700 dark:text-emerald-300">{{ item.whatsapp_shares }}</td>
                        <td class="p-3 text-center font-mono text-xs font-bold text-blue-700 dark:text-blue-300">{{ item.facebook_shares }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div v-else class="py-12 text-center text-gray-400 text-xs font-bold space-y-3">
              <Share2 class="w-8 h-8 text-gray-300 dark:text-zinc-600 mx-auto" />
              <p>Data statistik share belum tersedia atau sedang dimuat.</p>
              <button @click="openShareAnalytics" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-xl text-xs font-bold shadow-md transition active:scale-95">Muat Ulang Data</button>
            </div>
          </div>
        </div>
      </transition>
      <transition name="fade">
        <div v-if="readerAnalyticsOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="readerAnalyticsOpen = false">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-3xl shadow-2xl p-6 text-left relative space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
              <div>
                <h3 class="text-base font-black text-gray-900 dark:text-zinc-50 flex items-center gap-2">
                  <Eye class="w-5 h-5 text-sky-500" />
                  Analisis Pembaca Unik & Trending Articles
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Statistik pengunjung unik (Human 24j), total page views, dan filter bot.</p>
              </div>
              <button @click="readerAnalyticsOpen = false" class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-full text-gray-400 hover:text-gray-600 transition">
                <X class="w-4 h-4" />
              </button>
            </div>

            <div v-if="loadingReaderAnalytics" class="py-12 text-center text-gray-400 text-xs font-bold space-y-2">
              <Loader2 class="w-6 h-6 animate-spin mx-auto text-sky-500" />
              <p>Memuat statistik pembaca...</p>
            </div>

            <div v-else-if="readerAnalyticsData" class="space-y-6">
              <!-- Summary Grid -->
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 bg-sky-50/60 dark:bg-sky-950/30 border border-sky-200/60 dark:border-sky-900/40 rounded-2xl space-y-1">
                  <span class="text-[9px] font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400">Total Page Views</span>
                  <p class="text-xl font-black text-sky-700 dark:text-sky-300">{{ (readerAnalyticsData.total_page_views || 0).toLocaleString('id-ID') }}</p>
                </div>
                <div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 rounded-2xl space-y-1">
                  <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Unique Readers</span>
                  <p class="text-xl font-black text-emerald-700 dark:text-emerald-300">{{ (readerAnalyticsData.total_unique_readers || 0).toLocaleString('id-ID') }}</p>
                </div>
                <div class="p-3 bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-900/40 rounded-2xl space-y-1">
                  <span class="text-[9px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Human Readers</span>
                  <p class="text-xl font-black text-indigo-700 dark:text-indigo-300">{{ (readerAnalyticsData.total_human_readers || 0).toLocaleString('id-ID') }}</p>
                </div>
                <div class="p-3 bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-900/40 rounded-2xl space-y-1">
                  <span class="text-[9px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Robot Hits</span>
                  <p class="text-xl font-black text-amber-700 dark:text-amber-300">{{ (readerAnalyticsData.total_robot_hits || 0).toLocaleString('id-ID') }}</p>
                </div>
              </div>

              <!-- Time Period Grid -->
              <div class="grid grid-cols-3 gap-3">
                <div class="p-3 bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-xl space-y-0.5">
                  <span class="text-[10px] font-bold text-gray-500">Views Hari Ini</span>
                  <p class="text-lg font-extrabold text-gray-900 dark:text-zinc-100">{{ (readerAnalyticsData.today_views || 0).toLocaleString('id-ID') }}</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-xl space-y-0.5">
                  <span class="text-[10px] font-bold text-gray-500">Views Minggu Ini</span>
                  <p class="text-lg font-extrabold text-gray-900 dark:text-zinc-100">{{ (readerAnalyticsData.week_views || 0).toLocaleString('id-ID') }}</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-xl space-y-0.5">
                  <span class="text-[10px] font-bold text-gray-500">Views Bulan Ini</span>
                  <p class="text-lg font-extrabold text-gray-900 dark:text-zinc-100">{{ (readerAnalyticsData.month_views || 0).toLocaleString('id-ID') }}</p>
                </div>
              </div>

              <!-- Article Reaction Analytics Grid -->
              <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
                <h4 class="text-xs font-black uppercase text-gray-500 tracking-wider">💬 Reaksi & Engagement Pembaca</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  <div class="p-3 bg-sky-50/60 dark:bg-sky-950/30 border border-sky-200/60 dark:border-sky-900/40 rounded-xl space-y-0.5">
                    <span class="text-[10px] font-bold text-sky-600 dark:text-sky-400">👍 Total Bermanfaat</span>
                    <p class="text-lg font-black text-sky-700 dark:text-sky-300">{{ (readerAnalyticsData.total_beneficial || 0).toLocaleString('id-ID') }}</p>
                  </div>
                  <div class="p-3 bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-900/40 rounded-xl space-y-0.5">
                    <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400">❤ Total Informatif</span>
                    <p class="text-lg font-black text-rose-700 dark:text-rose-300">{{ (readerAnalyticsData.total_informative || 0).toLocaleString('id-ID') }}</p>
                  </div>
                  <div class="p-3 bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-900/40 rounded-xl space-y-0.5">
                    <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400">🔥 Total Engagement</span>
                    <p class="text-lg font-black text-purple-700 dark:text-purple-300">{{ (readerAnalyticsData.total_engagement || 0).toLocaleString('id-ID') }}</p>
                  </div>
                  <div class="p-3 bg-teal-50/60 dark:bg-teal-950/30 border border-teal-200/60 dark:border-teal-900/40 rounded-xl space-y-0.5">
                    <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400">📊 Persentase Reaksi</span>
                    <p class="text-lg font-black text-teal-700 dark:text-teal-300">{{ readerAnalyticsData.reaction_percent || 0 }}%</p>
                  </div>
                </div>
              </div>

              <!-- Trending Articles Table -->
              <div class="space-y-3">
                <h4 class="text-xs font-black uppercase text-gray-500 tracking-wider">🔥 Trending Articles (Top Read News)</h4>
                <div class="overflow-x-auto border border-gray-100 dark:border-zinc-800 rounded-2xl">
                  <table class="w-full text-left border-collapse text-xs">
                    <thead>
                      <tr class="bg-gray-50 dark:bg-zinc-800 text-[10px] font-black uppercase tracking-wider text-gray-400 border-b border-gray-100 dark:border-zinc-800">
                        <th class="p-3 w-10 text-center">No</th>
                        <th class="p-3">Judul Berita</th>
                        <th class="p-3 text-center">Kategori</th>
                        <th class="p-3 text-center">Unique Readers</th>
                        <th class="p-3 text-center">Page Views</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                      <tr v-if="!readerAnalyticsData.trending_articles || readerAnalyticsData.trending_articles.length === 0">
                        <td colspan="5" class="p-6 text-center text-gray-400">Belum ada statistik pembaca.</td>
                      </tr>
                      <tr v-for="(item, idx) in readerAnalyticsData.trending_articles" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/50 transition">
                        <td class="p-3 text-center font-bold text-gray-400">{{ idx + 1 }}</td>
                        <td class="p-3">
                          <p class="font-bold text-gray-800 dark:text-zinc-200 line-clamp-1">{{ item.title }}</p>
                          <p class="text-[10px] text-gray-400">/news/{{ item.slug }}</p>
                        </td>
                        <td class="p-3 text-center">
                          <span v-if="item.category" class="px-2 py-0.5 rounded font-mono font-bold text-[9px] border" :style="{ color: item.category.color, backgroundColor: item.category.color + '15', borderColor: item.category.color + '30' }">
                            {{ item.category.name }}
                          </span>
                        </td>
                        <td class="p-3 text-center font-black text-sky-600 dark:text-sky-400">
                          👤 {{ (item.unique_readers || item.view_count || 0).toLocaleString('id-ID') }}
                        </td>
                        <td class="p-3 text-center font-semibold text-gray-500">
                          📄 {{ (item.page_views || item.view_count || 0).toLocaleString('id-ID') }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Search, Edit, Trash2, X, RotateCcw, Share2, Loader2, Eye, Users, Bot, Calendar } from '@lucide/vue';
import axios from 'axios';

const props = defineProps({
  news: Object,
  categories: Array,
  readerStats: Object,
  filters: Object,
});

const readerAnalyticsOpen = ref(false);
const loadingReaderAnalytics = ref(false);
const readerAnalyticsData = ref(null);

const openReaderAnalytics = async () => {
  readerAnalyticsOpen.value = true;
  loadingReaderAnalytics.value = true;
  try {
    const res = await axios.get(route('admin.news.reader-analytics'));
    readerAnalyticsData.value = res.data;
  } catch (err) {
    console.error('Failed to load reader analytics:', err);
  } finally {
    loadingReaderAnalytics.value = false;
  }
};

const shareAnalyticsOpen = ref(false);
const loadingAnalytics = ref(false);
const analyticsData = ref(null);

const openShareAnalytics = async () => {
  shareAnalyticsOpen.value = true;
  loadingAnalytics.value = true;
  try {
    const res = await axios.get(route('admin.news.share-analytics'));
    analyticsData.value = res.data;
  } catch (err) {
    console.error('Failed to load share analytics:', err);
  } finally {
    loadingAnalytics.value = false;
  }
};

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const status = ref(props.filters.status || '');

const deletingNews = ref(null);
const isDeleting = ref(false);

let searchTimeout = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

const applyFilters = () => {
  router.get(route('admin.news.index'), {
    search: search.value || undefined,
    category: category.value || undefined,
    status: status.value || undefined,
  }, {
    preserveState: true,
  });
};

const clearSearch = () => {
  search.value = '';
  applyFilters();
};

const resetAllFilters = () => {
  search.value = '';
  category.value = '';
  status.value = '';
  applyFilters();
};

const deleteNews = (item) => {
  deletingNews.value = item;
};

const executeDeleteNews = () => {
  if (!deletingNews.value) return;
  isDeleting.value = true;
  router.delete(route('admin.news.destroy', deletingNews.value.id), {
    onSuccess: () => {
      deletingNews.value = null;
      isDeleting.value = false;
    },
    onError: () => {
      isDeleting.value = false;
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>
