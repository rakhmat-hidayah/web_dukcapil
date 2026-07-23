<template>
  <Head title="Manajemen Dokumen Unduhan" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Pusat Unduhan (Download Center)</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Unggah berkas formulir permohonan, dokumen regulasi daerah, serta laporan agregasi penduduk.
          </p>
        </div>
        <div class="flex gap-2">
          <button 
            @click="categoryModalOpen = true"
            class="px-4 py-2.5 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-600 dark:text-zinc-200 text-xs font-bold rounded-xl active:scale-95 transition"
          >
            Kelola Kategori
          </button>
          <button 
            @click="openAddModal"
            class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
          >
            <Plus class="w-4 h-4" />
            Unggah Dokumen
          </button>
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
              placeholder="Cari judul dokumen..." 
              class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              @keyup.enter="applyFilters"
            />
          </div>

          <!-- Status filter -->
          <select 
            v-model="status" 
            class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            @change="applyFilters"
          >
            <option value="">Semua Status</option>
            <option value="published">Diterbitkan</option>
            <option value="draft">Draft</option>
          </select>
        </div>

        <button 
          @click="applyFilters"
          class="px-4 py-2 bg-gray-900 dark:bg-zinc-800 hover:bg-gray-800 dark:hover:bg-zinc-700 text-white text-xs font-bold rounded-xl transition"
        >
          Cari
        </button>
      </div>

      <!-- Downloads Table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Judul Dokumen</th>
                <th class="px-6 py-4">Nomor Dokumen</th>
                <th class="px-6 py-4">Kategori</th>
                <th class="px-6 py-4">Format / Ukuran</th>
                <th class="px-6 py-4">Diunduh</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right font-bold">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr v-if="downloads.data.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                  Belum ada dokumen terdaftar.
                </td>
              </tr>
              <tr 
                v-for="doc in downloads.data" 
                :key="doc.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition"
              >
                <!-- Title & Filename -->
                <td class="px-6 py-4 font-bold text-gray-800 dark:text-zinc-200">
                  <div class="min-w-0">
                    <p class="leading-snug">{{ doc.title }}</p>
                    <p class="text-[9px] text-gray-400 font-mono mt-1 flex items-center gap-1">
                      <FileDown class="w-3 h-3 text-primary-500" />
                      {{ doc.file_name }}
                    </p>
                  </div>
                </td>

                <!-- Document Number -->
                <td class="px-6 py-4 font-semibold text-gray-600 dark:text-zinc-400">
                  {{ doc.document_number || 'N/A' }}
                </td>

                <!-- Category -->
                <td class="px-6 py-4">
                  <span 
                    v-if="doc.category" 
                    class="px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 font-semibold text-[9px] rounded uppercase"
                  >
                    {{ doc.category.name }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>

                <!-- File type & Size -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-1.5 font-mono text-[10px] text-gray-500">
                    <span class="px-1 py-0.5 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 rounded uppercase font-bold text-[8px] border border-blue-100 dark:border-blue-900/30">
                      {{ doc.file_type }}
                    </span>
                    <span>{{ formatBytes(doc.file_size) }}</span>
                  </div>
                </td>

                <!-- Downloads count -->
                <td class="px-6 py-4 font-bold text-gray-500">
                  {{ doc.download_count || 0 }} x
                </td>

                <!-- Status -->
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wide border"
                    :class="[
                      doc.status === 'published' 
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/30' 
                        : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'
                    ]"
                  >
                    {{ doc.status }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-1.5">
                    <button 
                      @click="openEditModal(doc)"
                      class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 rounded-lg transition"
                    >
                      <Edit class="w-4 h-4" />
                    </button>
                    <button 
                      @click="deleteDoc(doc)"
                      class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 rounded-lg transition"
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
        <div v-if="downloads.links && downloads.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800/60 bg-gray-50/50 dark:bg-zinc-900/50 flex justify-center gap-1">
          <Link 
            v-for="(link, i) in downloads.links" 
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

      <!-- Add/Edit Document Modal -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">
              {{ isEditing ? 'Edit Metadata Dokumen' : 'Upload Dokumen Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Dokumen</label>
                <input 
                  type="text" 
                  v-model="form.title" 
                  required
                  placeholder="Contoh: Formulir Permohonan Cetak KIA"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Deskripsi Singkat</label>
                <textarea 
                  v-model="form.description" 
                  rows="2"
                  placeholder="Informasi pelengkap isi dokumen..."
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Category -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori</label>
                  <select 
                    v-model="form.download_category_id"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option :value="null">Pilih Kategori...</option>
                    <template v-for="cat in categories" :key="cat.id">
                      <option :value="cat.id">{{ cat.name }}</option>
                      <option v-for="sub in cat.children" :key="sub.id" :value="sub.id">&nbsp;&nbsp;— {{ sub.name }}</option>
                    </template>
                  </select>
                </div>

                <!-- Status -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Status Rilis</label>
                  <select 
                    v-model="form.status"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option value="draft">Draft (Sembunyikan)</option>
                    <option value="published">Published (Online)</option>
                  </select>
                </div>
              </div>

              <!-- Document Number & Date -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Nomor Surat/Regulasi</label>
                  <input 
                    type="text" 
                    v-model="form.document_number" 
                    placeholder="Contoh: Perbup No 12 Th 2026"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Terbit Dokumen</label>
                  <input 
                    type="date" 
                    v-model="form.document_date"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  />
                </div>
              </div>

              <!-- File Input -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Pilih File Berkas</label>
                <input 
                  type="file" 
                  ref="fileInput" 
                  @change="handleFileUpload" 
                  :required="!isEditing"
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.zip"
                  class="w-full text-xs"
                />
                <p class="text-[9px] text-gray-400 mt-1">Maksimal 50MB (Format: PDF, Word, Excel, ZIP)</p>
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
                  {{ form.processing ? 'Uploading...' : 'Simpan Berkas' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- Category Management Modal -->
      <transition name="fade">
        <div v-if="categoryModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Kelola Kategori Unduhan</h3>

            <div class="space-y-4">
              <!-- Add new category form segment -->
              <form @submit.prevent="submitCategory" class="p-3 bg-gray-50 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 rounded-2xl flex gap-2 items-end">
                <div class="flex-1">
                  <label class="block text-[10px] font-semibold text-gray-400 mb-1">Tambah Kategori Baru</label>
                  <input 
                    type="text" 
                    v-model="catForm.name" 
                    required 
                    placeholder="Contoh: Formulir, Regulasi" 
                    class="w-full px-2.5 py-1.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none"
                  />
                </div>
                <button 
                  type="submit" 
                  class="px-3 py-1.5 bg-gray-900 dark:bg-zinc-700 hover:bg-gray-800 text-white text-xs font-bold rounded-lg transition"
                >
                  Tambah
                </button>
              </form>

              <!-- Category Lists -->
              <div class="max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800 scrollbar-thin">
                <div v-for="cat in categories" :key="cat.id" class="py-2.5 flex justify-between items-center text-xs">
                  <span class="font-bold text-gray-800 dark:text-zinc-200">{{ cat.name }}</span>
                  <button 
                    @click="deleteCategory(cat.id)"
                    class="p-1 hover:bg-red-50 text-red-500 rounded"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <!-- Footer Buttons -->
              <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-4">
                <button 
                  type="button" 
                  @click="categoryModalOpen = false" 
                  class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl transition"
                >
                  Tutup Dialog
                </button>
              </div>
            </div>
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
import { Plus, Search, Edit, Trash2, FileDown } from '@lucide/vue';

const props = defineProps({
  downloads: Object,
  categories: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const modalOpen = ref(false);
const categoryModalOpen = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const fileInput = ref(null);

const form = useForm({
  title: '',
  description: '',
  download_category_id: null,
  document_number: '',
  document_date: '',
  status: 'draft',
  file: null,
});

const catForm = useForm({
  name: '',
  parent_id: null,
});

const applyFilters = () => {
  router.get(route('admin.downloads.index'), {
    search: search.value,
    status: status.value,
  }, {
    preserveState: true,
  });
};

const handleFileUpload = (e) => {
  form.file = e.target.files[0];
};

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  form.reset();
  modalOpen.value = true;
};

const openEditModal = (doc) => {
  isEditing.value = true;
  editId.value = doc.id;
  form.title = doc.title;
  form.description = doc.description;
  form.download_category_id = doc.download_category_id;
  form.document_number = doc.document_number || '';
  form.document_date = doc.document_date || '';
  form.status = doc.status;
  form.file = null;
  modalOpen.value = true;
};

const submit = () => {
  if (isEditing.value) {
    form.post(route('admin.downloads.update', editId.value), {
      forceFormData: true,
      headers: {
        _method: 'PUT'
      },
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  } else {
    form.post(route('admin.downloads.store'), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  }
};

const deleteDoc = (doc) => {
  if (confirm(`Apakah Anda yakin ingin menghapus berkas "${doc.title}"?`)) {
    router.delete(route('admin.downloads.destroy', doc.id));
  }
};

const submitCategory = () => {
  catForm.post(route('admin.downloads.categories.store'), {
    onSuccess: () => {
      catForm.reset();
    }
  });
};

const deleteCategory = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus kategori unduhan ini?')) {
    router.delete(route('admin.downloads.categories.destroy', id));
  }
};
</script>
