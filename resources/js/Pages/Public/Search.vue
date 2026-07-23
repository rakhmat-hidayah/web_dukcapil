<template>
  <Head :title="`Hasil Pencarian: ${keyword}`" />

  <PublicLayout>
    <div class="space-y-8 text-left max-w-4xl mx-auto">
      <!-- Title section -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Hasil Pencarian Global</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Menampilkan hasil pencarian untuk kata kunci: <strong class="text-primary-600">"{{ keyword }}"</strong>
        </p>
      </div>

      <!-- Search results sets -->
      <div class="space-y-6">
        
        <!-- 1. News results -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-[2rem] p-6 shadow-sm space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
            📰 Artikel Berita ({{ newsResults.length }})
          </h3>
          <div class="divide-y divide-gray-50 dark:divide-zinc-800/60" v-if="newsResults.length > 0">
            <Link 
              v-for="item in newsResults" 
              :key="item.slug" 
              :href="route('public.news.show', item.slug)"
              class="py-3 flex flex-col hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 px-2 rounded-xl transition"
            >
              <span class="font-bold text-gray-800 dark:text-zinc-200 text-xs">{{ item.title }}</span>
              <p class="text-[10px] text-gray-400 mt-1 leading-relaxed line-clamp-2">{{ item.excerpt }}</p>
            </Link>
          </div>
          <p class="text-[10px] text-gray-400 p-2 font-medium" v-else>Tidak ada artikel berita ditemukan.</p>
        </div>

        <!-- 2. Pages results -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-[2rem] p-6 shadow-sm space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
            📄 Halaman Profil & Layanan ({{ pageResults.length }})
          </h3>
          <div class="divide-y divide-gray-50 dark:divide-zinc-800/60" v-if="pageResults.length > 0">
            <Link 
              v-for="item in pageResults" 
              :key="item.slug" 
              :href="route('public.pages.show', item.slug)"
              class="py-3 flex justify-between items-center hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 px-2 rounded-xl transition"
            >
              <span class="font-bold text-gray-800 dark:text-zinc-200 text-xs">{{ item.title }}</span>
              <span class="text-[9px] text-primary-600 font-bold font-mono">Buka Halaman →</span>
            </Link>
          </div>
          <p class="text-[10px] text-gray-400 p-2 font-medium" v-else>Tidak ada halaman profil ditemukan.</p>
        </div>

        <!-- 3. Downloads results -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-[2rem] p-6 shadow-sm space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
            📥 Berkas Unduhan ({{ downloadResults.length }})
          </h3>
          <div class="divide-y divide-gray-50 dark:divide-zinc-800/60" v-if="downloadResults.length > 0">
            <Link 
              v-for="item in downloadResults" 
              :key="item.id" 
              :href="route('public.downloads.index')"
              class="py-3 flex justify-between items-center hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 px-2 rounded-xl transition"
            >
              <div>
                <h4 class="font-bold text-gray-800 dark:text-zinc-200 text-xs leading-snug">{{ item.title }}</h4>
                <span class="text-[9px] text-gray-400 uppercase font-bold font-mono">{{ item.file_type }} ({{ formatBytes(item.file_size) }})</span>
              </div>
              <span class="text-[9px] text-primary-600 font-bold font-mono">Pindah Ke Unduhan →</span>
            </Link>
          </div>
          <p class="text-[10px] text-gray-400 p-2 font-medium" v-else>Tidak ada berkas unduhan ditemukan.</p>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  keyword: String,
  newsResults: Array,
  pageResults: Array,
  downloadResults: Array,
});

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>
