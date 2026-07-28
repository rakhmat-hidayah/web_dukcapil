<template>
  <Head :title="isEdit ? 'Edit Halaman' : 'Buat Halaman Baru'" />

  <AdminLayout>
    <div class="space-y-6 text-left max-w-5xl">
      <!-- Back Header -->
      <div>
        <Link :href="route('admin.pages.index')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
          ← Kembali ke Daftar Halaman
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight mt-2">
          {{ isEdit ? 'Edit Halaman Dinamis' : 'Buat Halaman Baru' }}
        </h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Tulis informasi resmi instansi pemerintah Kabupaten Dompu secara lengkap.
        </p>
      </div>

      <!-- Form Grid -->
      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main editor panel (Col Span 2) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <!-- Title -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Halaman</label>
              <input 
                type="text" 
                v-model="form.title" 
                required
                placeholder="Contoh: Profil Dinas / Visi & Misi..."
                class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <!-- Body Content Visual Rich Editor -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Konten Halaman Lengkap *</label>
              <RichEditor v-model="form.content" placeholder="Tuliskan lengkap isi informasi halaman di sini..." min-height="400px" />
            </div>
          </div>

          <!-- SEO Settings Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">SEO Meta Tags</h3>
            
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Meta Title Tag</label>
              <input 
                type="text" 
                v-model="form.meta_title" 
                placeholder="Judul SEO jika berbeda dari judul utama..."
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Meta Description Tag</label>
              <textarea 
                v-model="form.meta_description" 
                rows="3"
                placeholder="Ringkasan isi halaman yang muncul di Google..."
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Sidebar options (Col Span 1) -->
        <div class="space-y-6">
          <!-- Publish controls -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Publish Setting</h3>

            <!-- Status selector -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
              <select 
                v-model="form.status"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              >
                <option value="draft">Draft (Simpan Internal)</option>
                <option value="published">Published (Online)</option>
              </select>
            </div>

            <!-- Template Selector -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Template Layout</label>
              <select 
                v-model="form.template"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              >
                <option value="default">Default (Dengan Sidebar)</option>
                <option value="full-width">Full Width (Lebar Penuh)</option>
                <option value="sidebar">Custom Left Sidebar</option>
              </select>
            </div>

            <!-- Published At -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Rilis</label>
              <input 
                type="datetime-local" 
                v-model="form.published_at"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <!-- Show in menu quick trigger switch -->
            <div>
              <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.show_in_menu" class="accent-primary-500 rounded border-gray-300" />
                <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300">Tampilkan di Menu Utama</span>
              </label>
            </div>

            <!-- Submit buttons -->
            <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex flex-col gap-2">
              <button 
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition disabled:opacity-50"
              >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Halaman' }}
              </button>
              <Link 
                :href="route('admin.pages.index')"
                class="w-full text-center py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold rounded-xl transition"
              >
                Batal
              </Link>
            </div>
          </div>

          <!-- Cover Image (OG Image) Upload Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Gambar Banner Halaman (1200x630px)</h3>
            
            <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-zinc-800 rounded-2xl p-4 text-center">
              <img 
                v-if="imagePreview" 
                :src="imagePreview" 
                class="w-full max-h-40 object-cover rounded-xl mb-3 border border-gray-100 dark:border-zinc-800"
              />
              <Image class="w-8 h-8 text-gray-300 mb-2" v-if="!imagePreview" />
              
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleFileUpload" 
                class="hidden" 
                accept="image/*"
              />
              <button 
                type="button"
                @click="$refs.fileInput.click()"
                class="px-3 py-1.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 text-gray-600 dark:text-zinc-300 text-[10px] font-bold rounded-lg transition"
              >
                Pilih File Gambar
              </button>
              <p class="text-[9px] text-gray-400 mt-2">Maksimal 3MB (Format: WebP, JPG, PNG)</p>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichEditor from '@/Components/Editors/RichEditor.vue';
import { Image } from '@lucide/vue';

const props = defineProps({
  pageData: Object,
});

const isEdit = computed(() => !!props.pageData);
const fileInput = ref(null);
const imagePreview = ref(props.pageData?.og_image ? `/storage/${props.pageData.og_image}` : null);

const form = useForm({
  title: props.pageData?.title || '',
  content: props.pageData?.content || '',
  template: props.pageData?.template || 'default',
  status: props.pageData?.status || 'draft',
  show_in_menu: props.pageData?.show_in_menu || false,
  meta_title: props.pageData?.meta_title || '',
  meta_description: props.pageData?.meta_description || '',
  published_at: props.pageData?.published_at ? props.pageData.published_at.substring(0, 16) : '',
  og_image: null,
});

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.og_image = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const submit = () => {
  if (isEdit.value) {
    form.transform((data) => ({
      ...data,
      _method: 'PUT'
    })).post(route('admin.pages.update', props.pageData.id), {
      forceFormData: true,
    });
  } else {
    form.post(route('admin.pages.store'));
  }
};
</script>
