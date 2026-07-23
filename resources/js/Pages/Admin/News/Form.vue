<template>
  <Head :title="isEdit ? 'Edit Berita' : 'Tambah Berita Baru'" />

  <AdminLayout>
    <div class="space-y-6 text-left max-w-5xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center border-b border-gray-100 dark:border-zinc-800 pb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">
            {{ isEdit ? 'Edit Berita' : 'Buat Artikel Berita Baru' }}
          </h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Kelola artikel publikasi, informasi layanan kependudukan, dan foto pendukung.
          </p>
        </div>
        <Link 
          :href="route('admin.news.index')"
          class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold rounded-xl transition"
        >
          ← Kembali
        </Link>
      </div>

      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Form Card (Col Span 2) -->
        <div class="md:col-span-2 space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Konten Utama Berita</h3>

            <!-- Title -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Artikel Berita</label>
              <input 
                type="text" 
                v-model="form.title" 
                required
                placeholder="Tulis judul berita yang menarik..."
                class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <!-- Excerpt -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Ringkasan Singkat (Excerpt)</label>
              <textarea 
                v-model="form.excerpt" 
                rows="2"
                placeholder="Tulis ringkasan singkat berita untuk feed halaman utama (opsional)..."
                class="w-full px-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              ></textarea>
            </div>

            <!-- Visual Tiptap Rich Editor -->
            <div class="space-y-2">
              <label class="block text-xs font-semibold text-gray-500">Isi Berita Lengkap *</label>
              <RichEditor v-model="form.content" placeholder="Tuliskan isi berita, laporan kegiatan, atau pengumuman publik di sini..." min-height="380px" />
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
                placeholder="Judul SEO jika berbeda dari judul berita..."
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Meta Description Tag</label>
              <textarea 
                v-model="form.meta_description" 
                rows="3"
                placeholder="Deskripsi ringkas yang muncul di hasil pencarian Google..."
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
              <label class="block text-xs font-semibold text-gray-500 mb-1">Status Rilis</label>
              <select 
                v-model="form.status"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              >
                <option value="draft">Draft (Simpan Internal)</option>
                <option value="published">Diterbitkan (Online)</option>
                <option value="scheduled">Dijadwalkan (Otomatis)</option>
              </select>
            </div>

            <!-- Published At -->
            <div v-if="form.status === 'scheduled' || form.status === 'published'">
              <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal & Jam Tayang</label>
              <input 
                type="datetime-local" 
                v-model="form.published_at"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <!-- Switches -->
            <div class="space-y-3 pt-2">
              <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.is_featured" class="accent-primary-500 rounded border-gray-300" />
                <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300">Sematkan sebagai Utama (Featured)</span>
              </label>
              <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.is_breaking" class="accent-primary-500 rounded border-gray-300" />
                <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300">Kabar Kilat (Breaking News)</span>
              </label>
            </div>

            <!-- Submit buttons -->
            <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex flex-col gap-2">
              <button 
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition disabled:opacity-50"
              >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Berita' }}
              </button>
              <Link 
                :href="route('admin.news.index')"
                class="w-full text-center py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold rounded-xl transition"
              >
                Batal
              </Link>
            </div>
          </div>

          <!-- Category & Tags card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Taksonomi</h3>

            <!-- Category -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori Rubrik</label>
              <select 
                v-model="form.news_category_id"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              >
                <option :value="null">Pilih Kategori...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>

            <!-- Tag checklist -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tags Label (Topik)</label>
              <div class="p-3 bg-gray-50 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 rounded-xl max-h-40 overflow-y-auto scrollbar-thin flex flex-wrap gap-1.5">
                <label 
                  v-for="tag in tags" 
                  :key="tag.id" 
                  class="flex items-center gap-1.5 px-2 py-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg cursor-pointer text-[10px]"
                >
                  <input type="checkbox" :value="tag.id" v-model="form.tag_ids" class="accent-primary-500 rounded border-gray-300 w-3 h-3" />
                  <span class="font-medium text-gray-700 dark:text-zinc-300">#{{ tag.name }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Thumbnail Upload Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Gambar Cover (Thumbnail)</h3>
            
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
              <div class="flex flex-wrap justify-center gap-2">
                <button 
                  type="button"
                  @click="$refs.fileInput.click()"
                  class="px-3 py-1.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 text-gray-600 dark:text-zinc-300 text-[10px] font-bold rounded-lg transition"
                >
                  {{ imagePreview ? 'Ganti Gambar' : 'Pilih File Gambar' }}
                </button>

                <button 
                  v-if="imagePreview"
                  type="button"
                  @click="removeThumbnail"
                  class="flex items-center gap-1 px-3 py-1.5 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 hover:bg-red-100 border border-red-200 dark:border-red-900/40 text-[10px] font-bold rounded-lg transition"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                  <span>Hapus Cover</span>
                </button>
              </div>
              <p class="text-[9px] text-gray-400 mt-2">Maksimal 10MB (Format: JPG, PNG, WebP)</p>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichEditor from '@/Components/Editors/RichEditor.vue';
import { Image, Camera, Link as LinkIcon, Trash2, Loader2, Video } from '@lucide/vue';
import axios from 'axios';

const props = defineProps({
  news: Object,
  categories: Array,
  tags: Array,
});

const isEdit = computed(() => !!props.news);
const fileInput = ref(null);
const inlinePhotoInput = ref(null);
const editorContainer = ref(null);
const isUploadingInlinePhoto = ref(false);
const isEditorLoading = ref(true);
const imagePreview = ref(props.news?.thumbnail ? `/storage/${props.news.thumbnail}` : null);

let editorInstance = null;

const form = useForm({
  title: props.news?.title || '',
  excerpt: props.news?.excerpt || '',
  content: props.news?.content || '',
  status: props.news?.status || 'draft',
  news_category_id: props.news?.news_category_id || null,
  is_featured: props.news?.is_featured || false,
  is_breaking: props.news?.is_breaking || false,
  meta_title: props.news?.meta_title || '',
  meta_description: props.news?.meta_description || '',
  published_at: props.news?.published_at ? props.news.published_at.substring(0, 16) : '',
  tag_ids: props.news?.tag_ids || [],
  thumbnail: null,
  remove_thumbnail: false,
});

// Extract inline images from content HTML for preview list
const inlineImages = computed(() => {
  if (!form.content) return [];
  const regex = /<img[^>]+src=["']([^"']+)["']/gi;
  const matches = [];
  let match;
  while ((match = regex.exec(form.content)) !== null) {
    matches.push(match[1]);
  }
  return matches;
});

// Insert HTML at editor cursor position (CKEditor 5)
const insertHtmlAtCursor = (htmlToInsert) => {
  if (editorInstance) {
    editorInstance.model.change(writer => {
      const viewFragment = editorInstance.data.processor.toView(htmlToInsert);
      const modelFragment = editorInstance.data.toModel(viewFragment);
      editorInstance.model.insertContent(modelFragment);
    });
    // Sync back to form
    form.content = editorInstance.getData();
  } else {
    form.content += '\n' + htmlToInsert;
  }
};

const handleInlinePhotoUpload = async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  isUploadingInlinePhoto.value = true;
  const formData = new FormData();
  formData.append('image', file);

  try {
    const res = await axios.post(route('admin.news.upload-image'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    if (res.data && res.data.url) {
      const imgTag = `<p><img src="${res.data.url}" alt="Foto Berita" class="rounded-2xl max-w-full my-4 shadow-sm" /></p>`;
      insertHtmlAtCursor(imgTag);
    }
  } catch (err) {
    alert('Gagal mengunggah foto. Pastikan ukuran file < 10MB.');
  } finally {
    isUploadingInlinePhoto.value = false;
    if (inlinePhotoInput.value) inlinePhotoInput.value.value = '';
  }
};

const promptInsertImageUrl = () => {
  const url = prompt('Masukkan URL Gambar (misal: https://example.com/foto.jpg):');
  if (url && url.trim()) {
    const imgTag = `<p><img src="${url.trim()}" alt="Foto Berita" class="rounded-2xl max-w-full my-4 shadow-sm" /></p>`;
    insertHtmlAtCursor(imgTag);
  }
};

const removeInlineImage = (imgUrl) => {
  if (confirm('Apakah Anda yakin ingin menghapus foto ini dari isi berita?')) {
    const escapedUrl = imgUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`<p>\\s*<img[^>]+src=["']${escapedUrl}["'][^>]*>\\s*<\\/p>|<img[^>]+src=["']${escapedUrl}["'][^>]*>`, 'gi');
    const newContent = form.content.replace(regex, '');
    form.content = newContent;
    if (editorInstance) {
      editorInstance.setData(newContent);
    }
  }
};

const extractYouTubeId = (url) => {
  const regExp = /(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/;
  const match = url.match(regExp);
  return (match && match[1]) ? match[1] : null;
};

const promptInsertYouTubeVideo = () => {
  const url = prompt('Masukkan Link Video YouTube (contoh: https://youtu.be/FhfNcfnjNQ0 atau https://www.youtube.com/watch?v=...):');
  if (!url || !url.trim()) return;

  const ytId = extractYouTubeId(url.trim());
  if (ytId) {
    const oembedHtml = `<figure class="media"><oembed url="https://www.youtube.com/watch?v=${ytId}"></oembed></figure><p></p>`;
    insertHtmlAtCursor(oembedHtml);
  } else {
    alert('Format URL YouTube tidak valid. Mohon periksa kembali link video YouTube Anda.');
  }
};

const extractGoogleDriveId = (url) => {
  const match = url.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
  if (match && match[1]) return match[1];
  if (/^[a-zA-Z0-9_-]{25,}$/.test(url.trim())) return url.trim();
  return null;
};

const promptInsertGoogleDriveVideo = () => {
  const url = prompt('Masukkan Link Video Google Drive (contoh: https://drive.google.com/file/d/1KY02_9ADneYBth.../view?usp=drive_link):');
  if (!url || !url.trim()) return;

  const driveId = extractGoogleDriveId(url.trim());
  if (driveId) {
    const oembedHtml = `<figure class="media"><oembed url="https://drive.google.com/file/d/${driveId}/preview"></oembed></figure><p></p>`;
    insertHtmlAtCursor(oembedHtml);
  } else {
    alert('Format URL Google Drive tidak valid. Mohon periksa kembali link video Google Drive Anda.');
  }
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.thumbnail = file;
    form.remove_thumbnail = false;
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeThumbnail = () => {
  imagePreview.value = null;
  form.thumbnail = null;
  form.remove_thumbnail = true;
  if (fileInput.value) fileInput.value.value = '';
};

const submit = () => {
  // Sync editor content before submitting
  if (editorInstance) {
    form.content = editorInstance.getData();
  }
  if (isEdit.value) {
    form.transform((data) => ({
      ...data,
      _method: 'PUT'
    })).post(route('admin.news.update', props.news.id), {
      forceFormData: true,
    });
  } else {
    form.post(route('admin.news.store'));
  }
};

// Load CKEditor 5 from CDN and initialise
onMounted(() => {
  const loadCKEditor = () => {
    return new Promise((resolve, reject) => {
      if (window.ClassicEditor) { resolve(); return; }
      const script = document.createElement('script');
      script.src = 'https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js';
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  };

  loadCKEditor().then(() => {
    if (!window.ClassicEditor || !editorContainer.value) {
      isEditorLoading.value = false;
      return;
    }

    window.ClassicEditor.create(editorContainer.value, {
      toolbar: [
        'undo', 'redo', '|',
        'heading', '|',
        'bold', 'italic', '|',
        'bulletedList', 'numberedList', 'outdent', 'indent', '|',
        'link', 'blockQuote', 'insertTable', 'mediaEmbed',
      ],
      heading: {
        options: [
          { model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph' },
          { model: 'heading1', view: 'h1', title: 'Judul 1', class: 'ck-heading_heading1' },
          { model: 'heading2', view: 'h2', title: 'Judul 2', class: 'ck-heading_heading2' },
          { model: 'heading3', view: 'h3', title: 'Judul 3', class: 'ck-heading_heading3' },
        ],
      },
      mediaEmbed: {
        previewsInData: false,
      },
      initialData: form.content || '',
      placeholder: 'Tulis lengkap isi berita kependudukan di sini...',
    }).then(editor => {
      editorInstance = editor;
      isEditorLoading.value = false;

      // Keep form.content in sync as user types
      editor.model.document.on('change:data', () => {
        form.content = editor.getData();
      });
    }).catch(err => {
      isEditorLoading.value = false;
      console.error('CKEditor init error:', err);
    });
  }).catch(err => {
    isEditorLoading.value = false;
    console.error('Failed to load CKEditor 5 CDN script:', err);
  });
});

onBeforeUnmount(() => {
  if (editorInstance) {
    editorInstance.destroy().catch(() => {});
    editorInstance = null;
  }
});
</script>

<style>
/* CKEditor container styling */
.ck-editor-wrapper .ck.ck-editor {
  border-radius: 1rem;
  overflow: hidden;
  border: none !important;
}
.ck-editor-wrapper .ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content {
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}
.dark .ck-editor-wrapper .ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content {
  background: #18181b;
  border-bottom-color: #3f3f46;
}
.ck-editor-wrapper .ck.ck-editor__main .ck-editor__editable {
  min-height: 420px;
  font-size: 0.875rem;
  line-height: 1.8;
  padding: 1.5rem;
  background: #f9fafb;
}
.dark .ck-editor-wrapper .ck.ck-editor__main .ck-editor__editable {
  background: #18181b;
  color: #e4e4e7;
}
.ck-editor-wrapper .ck.ck-toolbar {
  background: #f9fafb !important;
}
.dark .ck-editor-wrapper .ck.ck-toolbar {
  background: #18181b !important;
  border-color: #3f3f46 !important;
}
</style>
