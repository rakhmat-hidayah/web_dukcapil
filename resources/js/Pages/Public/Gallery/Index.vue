<template>
  <Head title="Galeri Dokumentasi & Media" />

  <PublicLayout>
    <div class="space-y-8 text-left">
      <!-- Title section -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Dokumentasi & Galeri Media</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Lihat dokumentasi visual kegiatan pelayanan, sosialisasi kependudukan, serta video informasi resmi.
        </p>
      </div>

      <!-- Filters panel -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center flex-1">
          <!-- Album filter buttons -->
          <div class="flex flex-wrap gap-1.5">
            <button 
              @click="selectType('')"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition"
              :class="[
                !selectedType 
                  ? 'bg-primary-600 text-white border-primary-500 shadow-sm shadow-primary-500/10' 
                  : 'bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400'
              ]"
            >
              Semua Album
            </button>
            <button 
              @click="selectType('photo')"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition flex items-center gap-1.5"
              :class="[
                selectedType === 'photo' 
                  ? 'bg-primary-600 text-white border-primary-500 shadow-sm' 
                  : 'bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400'
              ]"
            >
              <Camera class="w-3.5 h-3.5" />
              Galeri Foto
            </button>
            <button 
              @click="selectType('video')"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition flex items-center gap-1.5"
              :class="[
                selectedType === 'video' 
                  ? 'bg-primary-600 text-white border-primary-500 shadow-sm' 
                  : 'bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400'
              ]"
            >
              <Video class="w-3.5 h-3.5" />
              Galeri Video
            </button>
          </div>
        </div>
      </div>

      <!-- Albums Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-if="albums.length === 0" class="col-span-full text-center py-12 text-gray-400 text-xs">
          Belum ada album galeri dipublikasikan.
        </div>

        <Link 
          v-for="album in albums" 
          :key="album.id"
          :href="route('public.gallery.show', album.slug)"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden flex flex-col justify-between group"
        >
          <!-- Cover -->
          <div class="h-48 bg-slate-900 overflow-hidden flex items-center justify-center relative">
            <img 
              v-if="album.cover_image && !imageErrors[album.id]"
              :src="`/storage/${album.cover_image}`" 
              @error="imageErrors[album.id] = true"
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              alt="Album Cover" 
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-slate-800 to-indigo-950 flex flex-col items-center justify-center text-slate-400">
              <component :is="album.type === 'photo' ? Camera : Video" class="w-10 h-10 mb-1 text-slate-500" />
              <span class="text-[10px] font-semibold text-slate-400">Dokumentasi {{ album.type }}</span>
            </div>
            <span class="absolute top-3 left-3 px-2 py-0.5 bg-black/60 backdrop-blur-md text-white text-[9px] font-bold uppercase rounded tracking-wider flex items-center gap-1">
              <component :is="album.type === 'photo' ? Camera : Video" class="w-3 h-3" />
              {{ album.type }}
            </span>
            <span class="absolute bottom-3 right-3 px-2 py-0.5 bg-black/40 text-white text-[9px] font-mono rounded">
              {{ album.items_count }} media
            </span>
          </div>

          <!-- Body details -->
          <div class="p-6 flex-1 flex flex-col justify-between">
            <div class="space-y-2">
              <h4 class="font-extrabold text-gray-800 dark:text-zinc-50 text-sm leading-snug group-hover:text-primary-600 transition">
                {{ album.title }}
              </h4>
              <p class="text-xs text-gray-400 leading-relaxed line-clamp-2" v-if="album.description">
                {{ album.description }}
              </p>
            </div>

            <div class="border-t border-gray-50 dark:border-zinc-800 mt-4 pt-4 flex justify-between items-center text-[10px] text-gray-400 font-semibold">
              <span>Buka Album →</span>
              <span>{{ formatDate(album.created_at) }}</span>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Camera, Video } from '@lucide/vue';

const props = defineProps({
  albums: Array,
  filters: Object,
});

const imageErrors = ref({});
const selectedType = ref(props.filters.type || '');

const applyFilters = () => {
  router.get(route('public.gallery.index'), {
    type: selectedType.value,
  }, {
    preserveState: true,
  });
};

const selectType = (type) => {
  selectedType.value = type;
  applyFilters();
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>
