<template>
  <Head :title="album.title" />

  <PublicLayout>
    <div class="space-y-6 text-left max-w-5xl mx-auto">
      
      <!-- Back Header -->
      <div>
        <Link :href="route('public.gallery.index')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
          ← Kembali ke Galeri Album
        </Link>
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight mt-2">{{ album.title }}</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1" v-if="album.description">
          {{ album.description }}
        </p>
      </div>

      <!-- Media items grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
        <div v-if="album.items.length === 0" class="col-span-full bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-12 text-center text-gray-400 text-xs">
          Belum ada media diunggah di album ini.
        </div>

        <div 
          v-for="item in album.items" 
          :key="item.id"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between group cursor-pointer"
          @click="openLightbox(item)"
        >
          <!-- Media image preview -->
          <div class="h-36 bg-gray-100 dark:bg-zinc-950 flex items-center justify-center overflow-hidden relative">
            <img 
              v-if="item.file_type === 'image'"
              :src="`/storage/${item.thumbnail || item.file_path}`" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              alt="Media item" 
            />
            <div 
              v-else 
              class="w-full h-full flex flex-col items-center justify-center bg-zinc-900 text-white gap-2"
            >
              <Video class="w-8 h-8 text-primary-500" />
              <span class="text-[9px] font-bold tracking-wide uppercase font-mono">Play Video</span>
            </div>
            <!-- Zoom overlay icon -->
            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
              <span class="text-white text-xs font-bold font-sans">Zoom</span>
            </div>
          </div>

          <!-- Label -->
          <div class="p-3">
            <p class="text-[10px] font-bold text-gray-700 dark:text-zinc-300 truncate" :title="item.title || 'Untitled'">
              {{ item.title || 'Untitled' }}
            </p>
            <p class="text-[8px] text-gray-400 truncate mt-0.5" v-if="item.caption">{{ item.caption }}</p>
          </div>
        </div>
      </div>

      <!-- Lightbox viewer popup (modal) -->
      <transition name="fade">
        <div 
          v-if="activeItem" 
          class="fixed inset-0 bg-black/95 backdrop-blur-sm z-50 flex items-center justify-center p-4"
          @click="activeItem = null"
        >
          <div class="max-w-4xl max-h-[85vh] relative text-left" @click.stop>
            <button 
              @click="activeItem = null" 
              class="absolute -top-10 right-0 text-white hover:text-gray-300 text-xl font-bold bg-white/10 w-8 h-8 rounded-full flex items-center justify-center"
            >
              ✕
            </button>
            
            <!-- Render based on file type -->
            <img 
              v-if="activeItem.file_type === 'image'"
              :src="`/storage/${activeItem.file_path}`" 
              class="max-w-full max-h-[75vh] rounded-xl object-contain border border-white/10" 
              alt="Full size media"
            />
            <video 
              v-else
              :src="`/storage/${activeItem.file_path}`" 
              controls
              autoplay
              class="max-w-full max-h-[75vh] rounded-xl border border-white/10"
            ></video>

            <!-- Metadata description footer panel -->
            <div class="mt-4 text-white text-xs p-4 bg-white/5 border border-white/5 rounded-2xl">
              <h4 class="font-extrabold text-sm">{{ activeItem.title || 'Untitled' }}</h4>
              <p class="text-gray-400 mt-1 font-semibold" v-if="activeItem.caption">{{ activeItem.caption }}</p>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Video } from '@lucide/vue';

const props = defineProps({
  album: Object,
});

const activeItem = ref(null);

const openLightbox = (item) => {
  activeItem.value = item;
};
</script>
