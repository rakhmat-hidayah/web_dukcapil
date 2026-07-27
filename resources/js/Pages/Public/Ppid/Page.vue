<template>
  <PublicLayout :title="`${page.title} — PPID Disdukcapil Kab. Dompu`" :meta-description="page.meta_description">
    
    <!-- Modern Dukcapil Identity Hero Banner -->
    <section class="relative bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white py-12 md:py-16 overflow-hidden rounded-3xl mb-8 border border-blue-900/30 shadow-xl shadow-blue-950/20">
      
      <!-- Backdrop Image -->
      <img 
        src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1400&q=80"
        class="absolute inset-0 w-full h-full object-cover opacity-20 filter blur-[3px] scale-105 pointer-events-none z-0"
        alt="Backdrop PPID" 
      />

      <!-- Grid Pattern -->
      <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:28px_28px] pointer-events-none z-0"></div>

      <!-- Watermark KTP -->
      <div class="absolute right-6 top-1/2 -translate-y-1/2 opacity-10 pointer-events-none hidden lg:block z-0">
        <svg width="340" height="210" viewBox="0 0 420 260" fill="none" class="text-blue-100">
          <rect x="2" y="2" width="416" height="256" rx="20" stroke="currentColor" stroke-width="4" stroke-dasharray="8 4" />
          <rect x="36" y="60" width="56" height="44" rx="8" stroke="currentColor" stroke-width="3" />
          <path d="M56 60v44M72 60v44M36 82h56" stroke="currentColor" stroke-width="2" />
          <rect x="36" y="124" width="70" height="85" rx="10" stroke="currentColor" stroke-width="3" />
          <circle cx="71" cy="154" r="18" stroke="currentColor" stroke-width="3" />
        </svg>
      </div>

      <div class="max-w-7xl mx-auto px-6 relative z-10">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs font-semibold text-blue-200/80 mb-4 flex-wrap">
          <Link href="/" class="hover:text-white transition">Beranda</Link>
          <ChevronRight :size="12" class="text-blue-400" />
          <span class="text-blue-200/80">PPID</span>
          <ChevronRight :size="12" class="text-blue-400" />
          <span class="text-amber-400 font-bold">{{ page.title }}</span>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-500/20 text-amber-300 text-xs font-extrabold uppercase tracking-wider mb-3 border border-amber-500/30 backdrop-blur-md">
          <Shield :size="14" class="text-amber-400" /> PPID Disdukcapil Dompu
        </span>

        <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-sm">
          {{ page.title }}
        </h1>
        <p class="text-xs sm:text-sm text-blue-100/90 font-medium max-w-2xl mt-2">
          Pejabat Pengelola Informasi dan Dokumentasi — Disdukcapil Kabupaten Dompu
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
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all duration-200"
                :class="page.slug === p.slug 
                  ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20 font-extrabold' 
                  : 'text-gray-700 dark:text-zinc-300 hover:bg-primary-50 dark:hover:bg-zinc-800 hover:text-primary-600'"
              >
                <span>{{ p.title }}</span>
                <ChevronRight :size="14" :class="page.slug === p.slug ? 'text-white' : 'opacity-40'" />
              </Link>

              <div class="my-2 border-t border-gray-100 dark:border-zinc-800"></div>

              <div class="px-3.5 py-1 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-zinc-500 flex items-center gap-1.5">
                <FileText :size="12" /> Dokumen Publik
              </div>

              <Link 
                :href="route('public.ppid.informasi-publik')" 
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold text-gray-700 dark:text-zinc-300 hover:bg-primary-50 dark:hover:bg-zinc-800 hover:text-primary-600 transition-all duration-200"
              >
                <span>Informasi Publik</span>
                <ChevronRight :size="14" class="opacity-40" />
              </Link>

              <Link 
                :href="route('public.ppid.prosedur')" 
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold text-gray-700 dark:text-zinc-300 hover:bg-primary-50 dark:hover:bg-zinc-800 hover:text-primary-600 transition-all duration-200"
              >
                <span>Prosedur</span>
                <ChevronRight :size="14" class="opacity-40" />
              </Link>

              <Link 
                :href="route('public.ppid.layanan-informasi')" 
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold text-gray-700 dark:text-zinc-300 hover:bg-primary-50 dark:hover:bg-zinc-800 hover:text-primary-600 transition-all duration-200"
              >
                <span>Layanan Informasi</span>
                <ChevronRight :size="14" class="opacity-40" />
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

          <!-- Quick Contact Widget -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <h4 class="font-extrabold text-xs text-primary-600 dark:text-primary-400 flex items-center gap-2">
              <Phone :size="14" /> Kontak Layanan PPID
            </h4>
            <p class="text-xs text-gray-600 dark:text-zinc-300 font-semibold">Telp: (0373) 21124</p>
            <p class="text-xs text-gray-600 dark:text-zinc-300 font-semibold">Email: ppid@dompukab.go.id</p>
            <p class="text-[11px] text-gray-400 dark:text-zinc-500 pt-1 leading-relaxed font-medium">
              Senin–Kamis: 08.00–16.00 WITA<br>Jumat: 08.00–11.30 WITA
            </p>
          </div>
        </aside>

        <!-- Main Content Body -->
        <main class="lg:col-span-8">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-10 shadow-sm">
            <div class="border-b border-gray-100 dark:border-zinc-800 pb-5 mb-6">
              <h2 class="text-2xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
                {{ page.title }}
              </h2>
            </div>
            
            <!-- If sk-ppid slug, render SK Cards with integrated download links -->
            <template v-if="page.slug === 'sk-ppid'">
              <div class="space-y-8">
                <!-- Callout Banner -->
                <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50/70 dark:from-zinc-800/60 dark:to-zinc-800/30 border border-blue-100 dark:border-zinc-700/60 rounded-2xl flex items-start gap-4 shadow-xs">
                  <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md">
                    📁
                  </div>
                  <div class="space-y-1">
                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-100">Surat Keputusan (SK) Resmi PPID</h3>
                    <p class="text-xs text-gray-600 dark:text-zinc-300 leading-relaxed">
                      Daftar Surat Keputusan (SK) Kepala Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu terkait penetapan pengelola, SOP, daftar informasi publik, serta daftar informasi yang dikecualikan.
                    </p>
                  </div>
                </div>

                <!-- Document List Grid -->
                <div class="space-y-4">
                  <div class="flex items-center gap-2 border-b border-gray-100 dark:border-zinc-800 pb-3">
                    <span class="text-lg">📑</span>
                    <h3 class="text-lg font-black text-gray-900 dark:text-zinc-50 tracking-tight">Daftar Dokumen SK PPID</h3>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div v-for="sk in skCards" :key="sk.id" class="p-5 bg-white dark:bg-zinc-800/50 border border-gray-100 dark:border-zinc-700/60 rounded-2xl space-y-4 shadow-xs hover:border-blue-300 transition flex flex-col justify-between">
                      <div class="space-y-3">
                        <div class="flex items-center gap-3">
                          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg shrink-0" :class="sk.iconBg">
                            {{ sk.icon }}
                          </div>
                          <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-extrabold text-gray-900 dark:text-zinc-100 leading-snug">
                              {{ sk.title }}
                            </h4>
                            <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ sk.badge }}</p>
                          </div>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">
                          {{ sk.description }}
                        </p>
                      </div>

                      <!-- Integrated Download Button Inside Card -->
                      <div class="pt-2 border-t border-gray-100 dark:border-zinc-700/50">
                        <a 
                          v-if="sk.doc" 
                          :href="route('public.ppid.document.download', sk.doc.id)"
                          target="_blank"
                          class="w-full py-2 px-3 bg-primary-600 hover:bg-primary-500 text-white font-extrabold text-xs rounded-xl shadow-xs flex items-center justify-center gap-1.5 transition active:scale-95 cursor-pointer"
                        >
                          <span>📥 Unduh Berkas SK ({{ (sk.doc.file_type || 'PDF').toUpperCase() }})</span>
                        </a>
                        <div v-else class="w-full py-2 px-3 bg-gray-50 dark:bg-zinc-800/60 text-gray-400 dark:text-zinc-500 text-[11px] font-semibold rounded-xl text-center border border-gray-100 dark:border-zinc-700/40">
                          📌 Berkas Dokumen Fisik Tersedia di Kantor
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Standard Content for other PPID Pages -->
            <template v-else>
              <div class="prose dark:prose-invert max-w-none text-xs sm:text-sm text-gray-700 dark:text-zinc-300 leading-relaxed font-medium space-y-4" v-html="page.content"></div>
            </template>

          </div>
        </main>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ChevronRight, Shield, FileText, Phone, Send, Search } from '@lucide/vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  page: Object,
  pages: Array,
  documents: Array,
})

const skCardDefs = [
  {
    key: 'struktur',
    title: 'SK Penetapan Struktur PPID Disdukcapil',
    badge: 'Format PDF • Resmi',
    description: 'SK Kepala Dinas tentang Pembentukan & Penetapan Pejabat Pengelola Informasi dan Dokumentasi (PPID).',
    icon: '📄',
    iconBg: 'bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-300',
  },
  {
    key: 'dip',
    title: 'SK Daftar Informasi Publik (DIP)',
    badge: 'Format PDF • Berkelanjutan',
    description: 'SK tentang Rincian Penetapan Daftar Informasi Publik yang Terbuka dan Wajib Disediakan.',
    icon: '📊',
    iconBg: 'bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-300',
  },
  {
    key: 'dikecualikan',
    title: 'SK Daftar Informasi yang Dikecualikan',
    badge: 'Format PDF • Teruji Uu KIP',
    description: 'SK tentang Hasil Uji Konsekuensi Informasi Publik yang Bersifat Rahasia/Dikecualikan.',
    icon: '🔒',
    iconBg: 'bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-300',
  },
  {
    key: 'sop',
    title: 'SK Standard Operating Procedure (SOP) PPID',
    badge: 'Format PDF • Standar Pelayanan',
    description: 'SOP Pengelolaan, Pelayanan, Pengajuan Keberatan, dan Penanganan Sengketa Informasi Publik.',
    icon: '📋',
    iconBg: 'bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-300',
  },
]

const skCards = computed(() => {
  const docsList = props.documents || []
  return skCardDefs.map((def, idx) => {
    const doc = docsList.find(d => 
      d.title.toLowerCase().includes(def.title.toLowerCase()) || 
      def.title.toLowerCase().includes(d.title.toLowerCase())
    ) || docsList[idx] || null

    return {
      ...def,
      id: idx + 1,
      doc: doc,
    }
  })
})
</script>
