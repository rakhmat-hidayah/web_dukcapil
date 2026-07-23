<template>
  <Head title="Pusat Berita & Informasi Kependudukan" />

  <PublicLayout>
    <div class="space-y-8 text-left">
      <!-- Title section -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Kabar Berita & Rilis Resmi</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Dapatkan berita terkini seputar administrasi kependudukan dan kegiatan Disdukcapil Kabupaten Dompu.
        </p>
      </div>

      <!-- Filters panel -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center flex-1">
          <!-- Search input -->
          <div class="relative w-full max-w-xs">
            <Search class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" />
            <input 
              type="text" 
              v-model="search" 
              placeholder="Cari judul rilis..." 
              class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              @keyup.enter="applyFilters"
            />
          </div>

          <!-- Category filter buttons -->
          <div class="flex flex-wrap gap-1.5">
            <button 
              @click="selectCategory('')"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition"
              :class="[
                !selectedCategorySlug 
                  ? 'bg-primary-600 text-white border-primary-500 shadow-sm shadow-primary-500/10' 
                  : 'bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400'
              ]"
            >
              Semua Kategori
            </button>
            <button 
              v-for="cat in categories" 
              :key="cat.id"
              @click="selectCategory(cat.slug)"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition"
              :class="[
                selectedCategorySlug === cat.slug 
                  ? 'bg-primary-600 text-white border-primary-500 shadow-sm' 
                  : 'bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400'
              ]"
            >
              {{ cat.name }}
            </button>
          </div>
        </div>

        <button 
          @click="applyFilters"
          class="px-4 py-2 bg-gray-900 dark:bg-zinc-800 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition"
        >
          Cari
        </button>
      </div>

      <!-- Articles Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-if="news.data.length === 0" class="col-span-full text-center py-12 text-gray-400 text-xs">
          Belum ada artikel berita dipublikasikan untuk kategori ini.
        </div>

        <Link 
          v-for="item in news.data" 
          :key="item.id"
          :href="route('public.news.show', item.slug)"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden flex flex-col group"
        >
          <!-- Image -->
          <div class="h-48 bg-zinc-100 dark:bg-zinc-950 overflow-hidden flex items-center justify-center">
            <img 
              :src="item.thumbnail ? `/storage/${item.thumbnail}` : 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=400&q=80'" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              alt="Thumbnail" 
            />
          </div>

          <!-- Body details -->
          <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
            <div class="space-y-2">
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
              <h4 class="font-extrabold text-gray-800 dark:text-zinc-50 text-sm leading-snug group-hover:text-primary-600 transition line-clamp-2">
                {{ item.title }}
              </h4>
              <p class="text-xs text-gray-400 leading-relaxed line-clamp-3">
                {{ item.excerpt }}
              </p>
            </div>

            <div class="flex justify-between items-center text-[10px] text-gray-400 font-semibold border-t border-gray-50 dark:border-zinc-800 pt-4">
              <span>Oleh: {{ item.author ? item.author.name : 'Admin' }}</span>
              <span>{{ formatDate(item.published_at) }}</span>
            </div>
          </div>
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="news.links && news.links.length > 3" class="flex justify-center gap-1.5 pt-6">
        <Link 
          v-for="(link, i) in news.links" 
          :key="i"
          :href="link.url || '#'"
          v-html="link.label"
          :disabled="!link.url"
          class="px-3.5 py-2 rounded-xl text-xs font-semibold transition border"
          :class="[
            link.active 
              ? 'bg-primary-600 text-white border-primary-500 shadow-sm shadow-primary-500/10' 
              : 'bg-white dark:bg-zinc-900 border-gray-100 dark:border-zinc-800 hover:bg-gray-50 text-gray-600 dark:text-zinc-400',
            !link.url ? 'opacity-40 pointer-events-none' : ''
          ]"
        />
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Search } from '@lucide/vue';

const props = defineProps({
  news: Object,
  categories: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const selectedCategorySlug = ref(props.filters.category || '');

const applyFilters = () => {
  router.get(route('public.news.index'), {
    search: search.value,
    category: selectedCategorySlug.value,
  }, {
    preserveState: true,
  });
};

const selectCategory = (slug) => {
  selectedCategorySlug.value = slug;
  applyFilters();
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>
