<template>
  <PublicLayout :title="`${categoryLabel} — PPID Disdukcapil Kab. Dompu`">
    
    <!-- Modern Dukcapil Identity Hero Banner -->
    <section class="relative bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white py-12 md:py-16 overflow-hidden rounded-3xl mb-8 border border-blue-900/30 shadow-xl shadow-blue-950/20">
      
      <!-- Backdrop Image -->
      <img 
        src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1400&q=80"
        class="absolute inset-0 w-full h-full object-cover opacity-20 filter blur-[3px] scale-105 pointer-events-none z-0"
        alt="Backdrop PPID Dokumen" 
      />

      <!-- Grid Pattern -->
      <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:28px_28px] pointer-events-none z-0"></div>

      <div class="max-w-7xl mx-auto px-6 relative z-10">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs font-semibold text-blue-200/80 mb-4 flex-wrap">
          <Link href="/" class="hover:text-white transition">Beranda</Link>
          <ChevronRight :size="12" class="text-blue-400" />
          <Link :href="route('public.ppid.pengertian')" class="hover:text-white transition">PPID</Link>
          <ChevronRight :size="12" class="text-blue-400" />
          <span class="text-amber-400 font-bold">{{ categoryLabel }}</span>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-500/20 text-amber-300 text-xs font-extrabold uppercase tracking-wider mb-3 border border-amber-500/30 backdrop-blur-md">
          <Shield :size="14" class="text-amber-400" /> Dokumen Publik PPID
        </span>

        <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-sm">
          {{ categoryLabel }}
        </h1>
        <p class="text-xs sm:text-sm text-blue-100/90 font-medium max-w-2xl mt-2">
          Kumpulan Dokumen {{ categoryLabel }} Resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu
        </p>
      </div>
    </section>

    <!-- Main Content Layout -->
    <div class="max-w-7xl mx-auto px-2 sm:px-4 py-4">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Sidebar Navigation -->
        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
          <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-gray-100 dark:border-zinc-800 overflow-hidden shadow-sm">
            <div class="bg-gradient-to-r from-primary-600 to-indigo-600 text-white font-extrabold text-xs uppercase tracking-wider p-4 flex items-center gap-2.5">
              <Shield :size="16" class="text-amber-300" />
              <span>Navigasi PPID</span>
            </div>

            <nav class="p-3 space-y-1">
              <Link
                v-for="p in pages"
                :key="p.slug"
                :href="route('public.ppid.page', p.slug)"
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold text-gray-700 dark:text-zinc-300 hover:bg-primary-50 dark:hover:bg-zinc-800 hover:text-primary-600 transition-all duration-200"
              >
                <span>{{ p.title }}</span>
                <ChevronRight :size="14" class="opacity-40" />
              </Link>

              <div class="my-2 border-t border-gray-100 dark:border-zinc-800"></div>

              <div class="px-3.5 py-1 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-zinc-500 flex items-center gap-1.5">
                <FileText :size="12" /> Dokumen Publik
              </div>

              <Link 
                :href="route('public.ppid.informasi-publik')" 
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all duration-200"
                :class="category === 'informasi_publik' 
                  ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20 font-extrabold' 
                  : 'text-gray-700 dark:text-zinc-300 hover:bg-primary-50 dark:hover:bg-zinc-800 hover:text-primary-600'"
              >
                <span>Informasi Publik</span>
                <ChevronRight :size="14" :class="category === 'informasi_publik' ? 'text-white' : 'opacity-40'" />
              </Link>

              <Link 
                :href="route('public.ppid.prosedur')" 
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all duration-200"
                :class="category === 'prosedur' 
                  ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20 font-extrabold' 
                  : 'text-gray-700 dark:text-zinc-300 hover:bg-primary-50 dark:hover:bg-zinc-800 hover:text-primary-600'"
              >
                <span>Prosedur</span>
                <ChevronRight :size="14" :class="category === 'prosedur' ? 'text-white' : 'opacity-40'" />
              </Link>

              <Link 
                :href="route('public.ppid.layanan-informasi')" 
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all duration-200"
                :class="category === 'layanan_informasi' 
                  ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20 font-extrabold' 
                  : 'text-gray-700 dark:text-zinc-300 hover:bg-primary-50 dark:hover:bg-zinc-800 hover:text-primary-600'"
              >
                <span>Layanan Informasi</span>
                <ChevronRight :size="14" :class="category === 'layanan_informasi' ? 'text-white' : 'opacity-40'" />
              </Link>

              <div class="my-3 border-t border-gray-100 dark:border-zinc-800"></div>

              <div class="space-y-2 pt-1">
                <Link 
                  :href="route('public.ppid.request')" 
                  class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-black text-xs rounded-xl shadow-md shadow-amber-500/20 flex items-center justify-center gap-2 transition active:scale-95"
                >
                  <Send :size="14" class="text-slate-950" />
                  <span>Ajukan Permohonan</span>
                </Link>

                <Link 
                  :href="route('public.ppid.track')" 
                  class="w-full py-2.5 px-4 bg-primary-50 dark:bg-zinc-800 text-primary-600 dark:text-primary-400 hover:bg-primary-100 font-bold text-xs rounded-xl flex items-center justify-center gap-2 border border-primary-200 dark:border-zinc-700 transition"
                >
                  <Search :size="14" />
                  <span>Lacak Permohonan</span>
                </Link>
              </div>
            </nav>
          </div>
        </aside>

        <!-- Main Content Area -->
        <main class="lg:col-span-8 space-y-6">
          <!-- Filters Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
              <div class="flex-1 relative">
                <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
                <input 
                  v-model="localSearch" 
                  type="text" 
                  placeholder="Cari kata kunci dokumen..." 
                  class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <select v-if="subcategories.length" v-model="localSubcat" @change="applyFilters" class="px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-gray-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Subkategori</option>
                <option v-for="s in subcategories" :key="s" :value="s">{{ s }}</option>
              </select>

              <select v-if="years.length" v-model="localYear" @change="applyFilters" class="px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-gray-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Tahun</option>
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
              </select>
            </form>

            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-zinc-800 text-[11px] font-bold text-gray-400 dark:text-zinc-500 flex items-center justify-between">
              <span>Menampilkan {{ documents.total }} berkas dokumen</span>
            </div>
          </div>

          <!-- Document List Empty State -->
          <div v-if="!documents.data.length" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-12 text-center shadow-sm">
            <FolderOpen :size="48" class="mx-auto text-gray-300 dark:text-zinc-600 mb-3" />
            <p class="text-xs font-bold text-gray-500 dark:text-zinc-400">Belum ada dokumen publik pada kategori ini.</p>
          </div>

          <!-- Document Cards -->
          <div v-else class="space-y-4">
            <template v-for="(group, subcat) in groupedDocs" :key="subcat">
              <div v-if="subcat && subcat !== 'null'" class="flex items-center gap-2 pt-2 text-xs font-extrabold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                <Tag :size="14" />
                <span>{{ subcat }}</span>
              </div>

              <div
                v-for="doc in group"
                :key="doc.id"
                class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
              >
                <div class="flex items-start gap-4 flex-1 min-w-0">
                  <div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-zinc-800 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 border border-primary-100 dark:border-zinc-700">
                    <FileText :size="22" />
                  </div>
                  <div class="space-y-1 min-w-0">
                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50 leading-snug">
                      {{ doc.title }}
                    </h3>
                    <p v-if="doc.description" class="text-xs text-gray-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                      {{ doc.description }}
                    </p>
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] font-semibold">
                      <span v-if="doc.year" class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/60">
                        {{ doc.year }}
                      </span>
                      <span v-if="doc.file_type" class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200/60">
                        {{ doc.file_type.toUpperCase() }}
                      </span>
                      <span v-if="doc.file_size_formatted" class="px-2 py-0.5 rounded-md bg-gray-50 text-gray-600 dark:bg-zinc-800 dark:text-zinc-300 border border-gray-200 dark:border-zinc-700">
                        {{ doc.file_size_formatted }}
                      </span>
                      <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200/60 flex items-center gap-1">
                        <Download :size="10" /> {{ doc.download_count }} kali diunduh
                      </span>
                    </div>
                  </div>
                </div>

                <a
                  v-if="doc.file_path || doc.file_url"
                  :href="route('public.ppid.document.download', doc.id)"
                  target="_blank"
                  class="w-full sm:w-auto px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white font-bold text-xs rounded-xl shadow-sm flex items-center justify-center gap-2 transition active:scale-95 shrink-0"
                >
                  <Download :size="14" /> Unduh Berkas
                </a>
              </div>
            </template>
          </div>

          <!-- Pagination -->
          <div v-if="documents.last_page > 1" class="flex justify-center gap-2 pt-4">
            <Link
              v-for="link in documents.links"
              :key="link.label"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3.5 py-2 rounded-xl border text-xs font-bold transition"
              :class="link.active 
                ? 'bg-primary-600 text-white border-primary-600 shadow-sm' 
                : 'bg-white dark:bg-zinc-900 text-gray-700 dark:text-zinc-300 border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800'"
            />
          </div>
        </main>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ChevronRight, Shield, FileText, FolderOpen, Tag, Search, Download, Send } from '@lucide/vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  category: String,
  categoryLabel: String,
  documents: Object,
  subcategories: Array,
  years: Array,
  filters: Object,
  pages: Array,
})

const localSearch = ref(props.filters?.search ?? '')
const localSubcat = ref(props.filters?.subcategory ?? '')
const localYear   = ref(props.filters?.year ?? '')

function applyFilters() {
  const routeMap = {
    informasi_publik: 'public.ppid.informasi-publik',
    prosedur: 'public.ppid.prosedur',
    layanan_informasi: 'public.ppid.layanan-informasi',
  }
  router.get(route(routeMap[props.category]), {
    search: localSearch.value || undefined,
    subcategory: localSubcat.value || undefined,
    year: localYear.value || undefined,
  }, { preserveState: true, replace: true })
}

const groupedDocs = computed(() => {
  const grouped = {}
  props.documents.data.forEach(doc => {
    const key = doc.subcategory ?? 'null'
    if (!grouped[key]) grouped[key] = []
    grouped[key].push(doc)
  })
  return grouped
})
</script>
