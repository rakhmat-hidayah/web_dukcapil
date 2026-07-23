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
        <div class="flex gap-2">
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

      <!-- Filters & Search -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center flex-1">
          <!-- Search input -->
          <div class="relative w-full max-w-xs">
            <Search class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" />
            <input 
              type="text" 
              v-model="search" 
              placeholder="Cari judul berita..." 
              class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              @keyup.enter="applyFilters"
            />
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

        <button 
          @click="applyFilters"
          class="px-4 py-2 bg-gray-900 dark:bg-zinc-800 hover:bg-gray-800 dark:hover:bg-zinc-700 text-white text-xs font-bold rounded-xl transition"
        >
          Cari
        </button>
      </div>

      <!-- Articles Grid -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Thumbnail / Judul Berita</th>
                <th class="px-6 py-4">Kategori</th>
                <th class="px-6 py-4">Penulis</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Dibaca</th>
                <th class="px-6 py-4">Tanggal Rilis</th>
                <th class="px-6 py-4 text-right font-bold">Aksi</th>
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
                  <div class="flex items-center gap-3.5 max-w-sm">
                    <img 
                      :src="item.thumbnail ? `/storage/${item.thumbnail}` : '/img/placeholder.webp'" 
                      class="w-12 h-12 rounded-lg object-cover border border-gray-100 dark:border-zinc-800" 
                      alt="Thumbnail" 
                    />
                    <div class="min-w-0">
                      <div class="flex items-center gap-2 mb-1">
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
                <td class="px-6 py-4">
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
                <td class="px-6 py-4 font-medium text-gray-700 dark:text-zinc-300">
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
                <td class="px-6 py-4 font-semibold text-gray-500">
                  {{ item.view_count || 0 }} x
                </td>

                <!-- Release Date -->
                <td class="px-6 py-4 text-gray-400 font-medium">
                  {{ item.published_at ? formatDate(item.published_at) : 'TBD' }}
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-1.5">
                    <Link 
                      :href="route('admin.news.edit', item.id)"
                      class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 rounded-lg transition"
                      title="Edit Berita"
                    >
                      <Edit class="w-4 h-4" />
                    </Link>
                    <button 
                      @click="deleteNews(item)"
                      class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition"
                      title="Hapus Berita"
                    >
                      <Trash2 class="w-4 h-4" />
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
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Search, Edit, Trash2 } from '@lucide/vue';

const props = defineProps({
  news: Object,
  categories: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
  router.get(route('admin.news.index'), {
    search: search.value,
    category: category.value,
    status: status.value,
  }, {
    preserveState: true,
  });
};

const deleteNews = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus berita "${item.title}"?`)) {
    router.delete(route('admin.news.destroy', item.id));
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>
