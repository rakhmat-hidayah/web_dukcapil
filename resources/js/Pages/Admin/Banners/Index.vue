<template>
  <Head title="Manajemen Banner & Slider" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Banner, Slider & Popup</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Atur gambar slider halaman depan, banner promosi info grafis, dan pop-up iklan layanan.
          </p>
        </div>
        <button 
          @click="openAddModal"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
        >
          <Plus class="w-4 h-4" />
          Tambah Banner
        </button>
      </div>

      <!-- Banner Cards list -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-if="banners.length === 0" class="md:col-span-2 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 text-center text-gray-400 text-xs">
          Belum ada banner diunggah. Klik "Tambah Banner" untuk mengunggah.
        </div>

        <div 
          v-for="banner in banners" 
          :key="banner.id"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden flex flex-col group"
        >
          <!-- Image preview -->
          <div class="h-44 relative bg-slate-900 overflow-hidden flex items-center justify-center">
            <img 
              v-if="banner.image && !imageErrors[banner.id]"
              :src="`/storage/${banner.image}`" 
              @error="imageErrors[banner.id] = true"
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              alt="Banner Image" 
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-blue-950 via-indigo-950 to-slate-900 flex flex-col items-center justify-center text-slate-400 p-4 text-center">
              <Image class="w-10 h-10 mb-1.5 text-blue-400/60" />
              <span class="text-xs font-bold text-slate-300">Pratinjau Banner {{ banner.type }}</span>
              <span class="text-[10px] text-slate-400 mt-0.5">Klik Edit untuk mengunggah berkas gambar</span>
            </div>
            <span class="absolute top-3 left-3 px-2 py-0.5 bg-black/60 backdrop-blur-md text-white text-[9px] font-bold uppercase rounded tracking-wider">
              {{ banner.type }}
            </span>
            <span 
              class="absolute top-3 right-3 px-2 py-0.5 text-[9px] font-bold uppercase rounded tracking-wider border"
              :class="[
                banner.is_active 
                  ? 'bg-emerald-500/90 text-white border-emerald-400' 
                  : 'bg-red-500/90 text-white border-red-400'
              ]"
            >
              {{ banner.is_active ? 'Active' : 'Off' }}
            </span>
          </div>

          <!-- Body details -->
          <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
              <h4 class="font-bold text-gray-800 dark:text-zinc-200 text-sm leading-snug">{{ banner.title }}</h4>
              <p class="text-xs text-gray-400 mt-1" v-if="banner.subtitle">{{ banner.subtitle }}</p>
              <p class="text-[10px] text-gray-500 font-mono mt-2 break-all" v-if="banner.url">
                Link: <a :href="banner.url" target="_blank" class="text-primary-600 hover:underline">{{ banner.url }}</a>
              </p>
            </div>

            <!-- Actions footer -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-4">
              <span class="text-[10px] font-bold text-gray-400">Order: #{{ banner.sort_order }}</span>
              <div class="flex gap-1.5">
                <button 
                  @click="openEditModal(banner)"
                  class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 rounded-lg transition"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteBanner(banner)"
                  class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 rounded-lg transition"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add/Edit Modal Dialog -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">
              {{ isEditing ? 'Edit Banner' : 'Unggah Banner Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Banner</label>
                <input 
                  type="text" 
                  v-model="form.title" 
                  required
                  placeholder="Tulis judul banner/slide..."
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Subtitle -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Sub Judul / Keterangan</label>
                <input 
                  type="text" 
                  v-model="form.subtitle" 
                  placeholder="Deskripsi singkat yang muncul di slide..."
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Type -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Tipe Penempatan</label>
                  <select 
                    v-model="form.type"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option value="hero">Hero Slider (Utama)</option>
                    <option value="popup">Popup Alert (Modal)</option>
                    <option value="sidebar">Sidebar Widget</option>
                    <option value="campaign">Campaign Bawah</option>
                  </select>
                </div>

                <!-- Sort Order -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Urutan (Sort Order)</label>
                  <input 
                    type="number" 
                    v-model="form.sort_order" 
                    required
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  />
                </div>
              </div>

              <!-- URL Link -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tautan URL Klik (Opsional)</label>
                <input 
                  type="url" 
                  v-model="form.url" 
                  placeholder="https://example.com/layanan"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- File image selection & preview -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">File Gambar (Rekomendasi 1920x800px)</label>
                <div v-if="previewUrl || (isEditing && currentBannerImage && !modalImageError)" class="mb-2 h-32 rounded-2xl overflow-hidden bg-slate-900 border border-slate-700 relative flex items-center justify-center">
                  <img 
                    :src="previewUrl || `/storage/${currentBannerImage}`" 
                    @error="modalImageError = true"
                    class="w-full h-full object-cover" 
                  />
                  <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-black/60 text-white text-[9px] font-bold rounded">
                    {{ previewUrl ? 'Pratinjau Berkas Baru' : 'Gambar Saat Ini' }}
                  </span>
                </div>
                <input 
                  type="file" 
                  ref="fileInput" 
                  @change="handleFileUpload" 
                  :required="!isEditing && !currentBannerImage"
                  accept="image/*"
                  class="w-full text-xs"
                />
              </div>

              <!-- Active Status checkbox -->
              <div>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 dark:text-zinc-300">
                  <input type="checkbox" v-model="form.is_active" class="accent-primary-500 rounded border-gray-300" />
                  <span>Aktif (Tampilkan di Halaman Publik)</span>
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
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Banner' }}
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
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Edit, Trash2, Image } from '@lucide/vue';

const props = defineProps({
  banners: Array,
});

const imageErrors = ref({});
const modalImageError = ref(false);
const modalOpen = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const fileInput = ref(null);
const previewUrl = ref(null);
const currentBannerImage = ref('');

const form = useForm({
  title: '',
  subtitle: '',
  type: 'hero',
  sort_order: 1,
  url: '',
  url_target: '_self',
  is_active: true,
  image: null,
});

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  previewUrl.value = null;
  currentBannerImage.value = '';
  modalImageError.value = false;
  form.reset();
  modalOpen.value = true;
};

const openEditModal = (b) => {
  isEditing.value = true;
  editId.value = b.id;
  previewUrl.value = null;
  currentBannerImage.value = b.image || '';
  modalImageError.value = false;
  form.title = b.title;
  form.subtitle = b.subtitle;
  form.type = b.type;
  form.sort_order = b.sort_order;
  form.url = b.url || '';
  form.is_active = b.is_active;
  form.image = null;
  modalOpen.value = true;
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.image = file;
    previewUrl.value = URL.createObjectURL(file);
    modalImageError.value = false;
  }
};

const submit = () => {
  if (isEditing.value) {
    router.post(route('admin.banners.update', editId.value), {
      _method: 'PUT',
      title: form.title,
      subtitle: form.subtitle,
      type: form.type,
      sort_order: form.sort_order,
      url: form.url,
      is_active: form.is_active,
      image: form.image,
    }, {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  } else {
    form.post(route('admin.banners.store'), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  }
};

const deleteBanner = (banner) => {
  if (confirm(`Apakah Anda yakin ingin menghapus banner "${banner.title}"?`)) {
    router.delete(route('admin.banners.destroy', banner.id));
  }
};
</script>
