<template>
  <Head title="Manajemen Galeri Album" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Galeri Foto & Video</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Buat album dokumentasi kegiatan dinas, sosialisasi kependudukan, dan rilis video edukasi.
          </p>
        </div>
        <button 
          @click="openAddModal"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
        >
          <Plus class="w-4 h-4" />
          Album Baru
        </button>
      </div>

      <!-- Albums Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-if="albums.length === 0" class="lg:col-span-3 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 text-center text-gray-400 text-xs">
          Belum ada album galeri terdaftar. Klik "Album Baru" untuk membuat.
        </div>

        <div 
          v-for="album in albums" 
          :key="album.id"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden flex flex-col justify-between group"
        >
          <!-- Cover Image -->
          <div class="h-44 relative bg-slate-900 overflow-hidden flex items-center justify-center">
            <img 
              v-if="album.cover_image && !imageErrors[album.id]"
              :src="`/storage/${album.cover_image}`" 
              @error="imageErrors[album.id] = true"
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              alt="Album Cover" 
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-slate-800 to-indigo-950 flex flex-col items-center justify-center text-slate-400">
              <component :is="album.type === 'photo' ? Camera : Video" class="w-10 h-10 mb-1 text-slate-500" />
              <span class="text-[10px] font-semibold text-slate-400">Belum ada Sampul</span>
            </div>
            <span class="absolute top-3 left-3 px-2 py-0.5 bg-black/60 backdrop-blur-md text-white text-[9px] font-bold uppercase rounded tracking-wider flex items-center gap-1">
              <component :is="album.type === 'photo' ? Camera : Video" class="w-3 h-3" />
              {{ album.type }}
            </span>
            <span class="absolute bottom-3 right-3 px-2 py-0.5 bg-black/40 text-white text-[9px] font-mono rounded">
              {{ album.items_count || 0 }} media
            </span>
          </div>

          <!-- Card Body -->
          <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
              <h4 class="font-bold text-gray-800 dark:text-zinc-200 text-sm leading-snug">{{ album.title }}</h4>
              <p class="text-xs text-gray-400 mt-1 max-w-sm truncate" v-if="album.description">{{ album.description }}</p>
            </div>

            <!-- Actions footer -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-4">
              <Link 
                :href="route('admin.gallery.show', album.id)"
                class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1"
              >
                Kelola Media →
              </Link>
              <div class="flex gap-1">
                <button 
                  @click="openEditModal(album)"
                  class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 rounded"
                >
                  <Edit class="w-4.5 h-4.5" />
                </button>
                <button 
                  @click="deleteAlbum(album)"
                  class="p-1 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 rounded"
                >
                  <Trash2 class="w-4.5 h-4.5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add/Edit Album Modal -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">
              {{ isEditing ? 'Edit Album' : 'Buat Album Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Album</label>
                <input 
                  type="text" 
                  v-model="form.title" 
                  required
                  placeholder="Contoh: HUT RI Ke-78 Dukcapil"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Deskripsi Album</label>
                <textarea 
                  v-model="form.description" 
                  rows="3"
                  placeholder="Keterangan isi dokumentasi kegiatan..."
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                ></textarea>
              </div>

              <!-- Type Selection (Only on create) -->
              <div v-if="!isEditing">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tipe Media Album</label>
                <select 
                  v-model="form.type"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                >
                  <option value="photo">Foto Dokumentasi (Galeri Gambar)</option>
                  <option value="video">Video Kegiatan (Galeri Video)</option>
                </select>
              </div>

              <!-- Active Status checkbox -->
              <div>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 dark:text-zinc-300">
                  <input type="checkbox" v-model="form.is_published" class="accent-primary-500 rounded border-gray-300" />
                  <span>Diterbitkan (Tampilkan ke Publik)</span>
                </label>
              </div>

              <!-- Footer Buttons -->
              <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-6">
                <button 
                  type="button" 
                  @click="modalOpen = false" 
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  :disabled="form.processing"
                  class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
                >
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Album' }}
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
import { Plus, Edit, Trash2, Camera, Video } from '@lucide/vue';

const props = defineProps({
  albums: Array,
});

const imageErrors = ref({});
const modalOpen = ref(false);
const isEditing = ref(false);
const editId = ref(null);

const form = useForm({
  title: '',
  description: '',
  type: 'photo',
  is_published: true,
});

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  form.reset();
  modalOpen.value = true;
};

const openEditModal = (album) => {
  isEditing.value = true;
  editId.value = album.id;
  form.title = album.title;
  form.description = album.description;
  form.is_published = album.is_published;
  modalOpen.value = true;
};

const submit = () => {
  if (isEditing.value) {
    form.put(route('admin.gallery.album.update', editId.value), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  } else {
    form.post(route('admin.gallery.album.store'), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  }
};

const deleteAlbum = (album) => {
  if (confirm(`Apakah Anda yakin ingin menghapus album "${album.title}" beserta seluruh media di dalamnya?`)) {
    router.delete(route('admin.gallery.album.destroy', album.id));
  }
};
</script>
