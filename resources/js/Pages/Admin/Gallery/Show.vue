<template>
  <Head :title="album.title" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div>
        <Link :href="route('admin.gallery.index')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
          ← Kembali ke Galeri Album
        </Link>
        <div class="flex justify-between items-start mt-2">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">{{ album.title }}</h1>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1" v-if="album.description">
              {{ album.description }}
            </p>
          </div>
          <button 
            @click="openUploadModal"
            class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
          >
            <Plus class="w-4 h-4" />
            Upload Media
          </button>
        </div>
      </div>

      <!-- Media Items Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <div v-if="album.items.length === 0" class="col-span-full bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-12 text-center text-gray-400 text-xs">
          Belum ada media diunggah di album ini. Klik "Upload Media" untuk mengunggah.
        </div>

        <div 
          v-for="item in album.items" 
          :key="item.id"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between group relative"
        >
          <!-- Delete absolute badge overlay -->
          <button 
            @click="deleteItem(item)"
            class="absolute top-2 right-2 p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow opacity-0 group-hover:opacity-100 transition duration-200 z-10"
            title="Hapus Media"
          >
            <Trash2 class="w-3.5 h-3.5" />
          </button>

          <!-- Media Preview (Image or Video icon) -->
          <div class="h-32 bg-gray-100 dark:bg-zinc-950 flex items-center justify-center overflow-hidden">
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
              <Video class="w-8 h-8 text-primary-500 animate-pulse" />
              <span class="text-[9px] font-bold tracking-wide uppercase font-mono">Video File</span>
            </div>
          </div>

          <!-- Caption or label -->
          <div class="p-3">
            <p class="text-[10px] font-bold text-gray-700 dark:text-zinc-300 truncate" :title="item.title || 'Untitled'">
              {{ item.title || 'Untitled' }}
            </p>
            <p class="text-[8px] text-gray-400 truncate mt-0.5" v-if="item.caption">{{ item.caption }}</p>
          </div>
        </div>
      </div>

      <!-- Upload Media Modal -->
      <transition name="fade">
        <div v-if="uploadModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Upload File Media</h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Media</label>
                <input 
                  type="text" 
                  v-model="form.title" 
                  placeholder="Contoh: Upacara Bendera HUT RI"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Caption -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Keterangan / Caption</label>
                <textarea 
                  v-model="form.caption" 
                  rows="2"
                  placeholder="Keterangan singkat kegiatan..."
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                ></textarea>
              </div>

              <!-- File Input -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Pilih File (Foto / Video)</label>
                <input 
                  type="file" 
                  ref="fileInput" 
                  @change="handleFileUpload" 
                  required
                  :accept="album.type === 'photo' ? 'image/*' : 'video/*'"
                  class="w-full text-xs"
                />
                <p class="text-[9px] text-gray-400 mt-1">
                  {{ album.type === 'photo' ? 'Format: JPG, PNG, WebP (Max 5MB)' : 'Format: MP4, MOV (Max 50MB)' }}
                </p>
              </div>

              <!-- Footer Buttons -->
              <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-6">
                <button 
                  type="button" 
                  @click="uploadModalOpen = false" 
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  :disabled="form.processing"
                  class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
                >
                  {{ form.processing ? 'Uploading...' : 'Upload' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Trash2, Video } from '@lucide/vue';

const props = defineProps({
  album: Object,
});

const uploadModalOpen = ref(false);
const fileInput = ref(null);

const form = useForm({
  title: '',
  caption: '',
  file: null,
});

const openUploadModal = () => {
  form.reset();
  uploadModalOpen.value = true;
};

const handleFileUpload = (e) => {
  form.file = e.target.files[0];
};

const submit = () => {
  form.post(route('admin.gallery.item.upload', props.album.id), {
    forceFormData: true,
    onSuccess: () => {
      uploadModalOpen.value = false;
    }
  });
};

const deleteItem = (item) => {
  if (confirm('Apakah Anda yakin ingin menghapus media ini?')) {
    router.delete(route('admin.gallery.item.destroy', item.id));
  }
};
</script>
