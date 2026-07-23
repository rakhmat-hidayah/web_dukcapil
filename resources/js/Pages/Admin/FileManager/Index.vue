<template>
  <Head title="Media & File Manager" />

  <AdminLayout>
    <div class="h-[calc(100vh-8rem)] flex gap-6 text-left relative">
      <!-- Main Uploader & Browser Panel -->
      <div class="flex-1 flex flex-col min-w-0 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
        
        <!-- Header & Action bar -->
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center border-b border-gray-50 dark:border-zinc-800 pb-4 mb-4">
          <!-- Directory Breadcrumbs -->
          <div class="flex items-center gap-1.5 text-xs text-gray-400 font-semibold overflow-x-auto max-w-full whitespace-nowrap scrollbar-none">
            <button 
              @click="navigateToFolder(null)" 
              class="hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1 shrink-0"
            >
              <Home class="w-4 h-4" />
              Drive Utama
            </button>
            <div v-for="crumb in breadcrumbs" :key="crumb.id" class="flex items-center gap-1.5 shrink-0">
              <ChevronRight class="w-3.5 h-3.5 text-gray-300" />
              <button 
                @click="navigateToFolder(crumb.id)" 
                class="hover:text-primary-600 dark:hover:text-primary-400 max-w-[120px] truncate"
              >
                {{ crumb.name }}
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2 shrink-0">
            <!-- Search -->
            <div class="relative">
              <input 
                type="text" 
                v-model="searchQuery" 
                @input="handleSearch"
                placeholder="Cari berkas..."
                class="pl-8 pr-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-xl border border-gray-200 dark:border-zinc-700 w-44 focus:outline-none focus:ring-2 focus:ring-primary-500 transition"
              />
              <Search class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-gray-400" />
            </div>

            <!-- Create Folder -->
            <button 
              @click="openNewFolderModal" 
              class="p-2 bg-gray-50 hover:bg-gray-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700 rounded-xl text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition"
              title="Folder Baru"
            >
              <FolderPlus class="w-4 h-4" />
            </button>

            <!-- Upload File button -->
            <label class="flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl cursor-pointer shadow-md shadow-primary-500/10 active:scale-95 transition">
              <Upload class="w-4 h-4" />
              Upload Berkas
              <input type="file" @change="uploadFile" class="hidden" />
            </label>
          </div>
        </div>

        <!-- Contents View Grid -->
        <div class="flex-1 overflow-y-auto pr-2 scrollbar-thin">
          <!-- Folder Section -->
          <div v-if="folders.length > 0" class="mb-6">
            <h3 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">Folders</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
              <div 
                v-for="folder in folders" 
                :key="folder.id"
                @dblclick="navigateToFolder(folder.id)"
                class="p-4 bg-gray-50 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 rounded-2xl flex items-center justify-between group cursor-pointer hover:bg-gray-100/50 dark:hover:bg-zinc-800 transition select-none"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <Folder class="w-6 h-6 text-yellow-500 shrink-0 fill-yellow-400/20" />
                  <span class="text-xs font-semibold truncate">{{ folder.name }}</span>
                </div>
                <button 
                  @click.stop="deleteFolder(folder)"
                  class="p-1 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 rounded-lg opacity-0 group-hover:opacity-100 transition"
                  title="Hapus Folder"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Files Section -->
          <div>
            <h3 v-if="folders.length > 0" class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">Files</h3>
            
            <div v-if="files.length === 0 && folders.length === 0" class="h-64 flex flex-col items-center justify-center text-gray-400">
              <FolderOpen class="w-12 h-12 text-gray-300 stroke-[1.5] mb-2" />
              <p class="text-xs font-semibold">Folder ini kosong</p>
              <p class="text-[10px] text-gray-400 mt-0.5">Tarik dan letakkan file atau klik tombol Upload untuk menambahkan.</p>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
              <div 
                v-for="file in files" 
                :key="file.id"
                @click="selectFile(file)"
                class="p-4 bg-gray-50 dark:bg-zinc-800/40 border rounded-2xl flex flex-col items-center text-center cursor-pointer hover:bg-gray-100/50 dark:hover:bg-zinc-800 transition select-none group"
                :class="[selectedFile && selectedFile.id === file.id ? 'border-primary-500 bg-primary-50/10 dark:bg-primary-950/10' : 'border-gray-100 dark:border-zinc-800']"
              >
                <!-- Thumbnail Preview or Icon -->
                <div class="w-20 h-20 rounded-xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 flex items-center justify-center overflow-hidden mb-3 shadow-inner relative">
                  <img v-if="isImage(file)" :src="file.url" class="object-cover w-full h-full" />
                  <component v-else :is="getFileIcon(file)" class="w-8 h-8 text-primary-500" />
                  
                  <span class="absolute bottom-1 right-1 text-[8px] px-1 bg-zinc-800/75 text-white font-bold rounded">
                    v{{ file.version }}
                  </span>
                </div>

                <span class="text-xs font-bold truncate w-full px-1" :title="file.name">
                  {{ file.name }}
                </span>
                <span class="text-[10px] text-gray-400 dark:text-zinc-500 mt-0.5">
                  {{ file.formatted_size }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- File Details Sliding Panel (Right) -->
      <transition name="slide">
        <div 
          v-if="selectedFile" 
          class="w-80 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-xl flex flex-col h-full overflow-hidden shrink-0 text-left"
        >
          <!-- Header details panel -->
          <div class="flex justify-between items-center border-b border-gray-50 dark:border-zinc-800 pb-3 mb-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Detail Berkas</h3>
            <button @click="selectedFile = null" class="p-1 hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-400 hover:text-white rounded-lg transition">
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Content Details Panel -->
          <div class="flex-1 overflow-y-auto space-y-5 pr-1 scrollbar-thin">
            <!-- Preview image/icon -->
            <div class="w-full h-36 bg-gray-50 dark:bg-zinc-950 rounded-2xl flex items-center justify-center border border-gray-100 dark:border-zinc-800 overflow-hidden shadow-inner relative">
              <img v-if="isImage(selectedFile)" :src="selectedFile.url" class="object-contain w-full h-full" />
              <component v-else :is="getFileIcon(selectedFile)" class="w-12 h-12 text-primary-500" />
            </div>

            <!-- Details -->
            <div class="space-y-3">
              <div>
                <h4 class="text-[10px] font-bold text-gray-400 uppercase">Nama File</h4>
                <p class="text-xs font-bold text-gray-800 dark:text-zinc-200 break-all select-all">{{ selectedFile.name }}</p>
              </div>
              <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                  <h4 class="text-[10px] font-bold text-gray-400 uppercase">Ekstensi</h4>
                  <p class="font-semibold text-gray-700 dark:text-zinc-300 uppercase">{{ selectedFile.extension }}</p>
                </div>
                <div>
                  <h4 class="text-[10px] font-bold text-gray-400 uppercase">Ukuran</h4>
                  <p class="font-semibold text-gray-700 dark:text-zinc-300">{{ selectedFile.formatted_size }}</p>
                </div>
              </div>
              <div>
                <h4 class="text-[10px] font-bold text-gray-400 uppercase">Diunggah Oleh</h4>
                <p class="text-xs font-semibold text-gray-700 dark:text-zinc-300">
                  {{ selectedFile.creator ? selectedFile.creator.name : 'System' }}
                </p>
              </div>
              
              <!-- Public Link -->
              <div>
                <h4 class="text-[10px] font-bold text-gray-400 uppercase mb-1">Tautan Publik</h4>
                <div class="flex gap-1">
                  <input 
                    type="text" 
                    readonly 
                    :value="selectedFile.url" 
                    class="flex-1 px-2.5 py-1 bg-gray-50 dark:bg-zinc-800 text-[10px] font-mono border border-gray-100 dark:border-zinc-800 rounded-lg select-all focus:outline-none" 
                  />
                  <button 
                    @click="copyLink(selectedFile.url)" 
                    class="px-2 py-1 bg-gray-50 hover:bg-gray-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700 rounded-lg text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition"
                    title="Salin Link"
                  >
                    <Copy class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Version Control -->
            <div class="border-t border-gray-100 dark:border-zinc-800/80 pt-4 space-y-3">
              <div class="flex justify-between items-center">
                <h4 class="text-[10px] font-bold text-gray-400 uppercase">Riwayat Versi (v{{ selectedFile.version }})</h4>
                <label class="text-[10px] text-primary-600 dark:text-primary-400 font-bold hover:underline cursor-pointer flex items-center gap-1">
                  <UploadCloud class="w-3.5 h-3.5" />
                  Upload Baru
                  <input type="file" @change="uploadNewVersion" class="hidden" />
                </label>
              </div>

              <!-- List of subversions -->
              <div v-if="selectedFile.versions && selectedFile.versions.length > 0" class="space-y-2 max-h-36 overflow-y-auto p-1 bg-gray-50 dark:bg-zinc-950 rounded-xl border border-gray-100 dark:border-zinc-800/60 scrollbar-thin">
                <div 
                  v-for="ver in selectedFile.versions" 
                  :key="ver.id"
                  class="p-2 hover:bg-white dark:hover:bg-zinc-800 rounded-lg border border-transparent hover:border-gray-100 dark:hover:border-zinc-700 flex justify-between items-center text-[10px] transition"
                >
                  <div>
                    <p class="font-bold">Versi {{ ver.version }}</p>
                    <p class="text-gray-400 mt-0.5">{{ ver.formatted_size }}</p>
                  </div>
                  <a 
                    :href="ver.url" 
                    download
                    class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-lg text-gray-500" 
                    title="Download Versi Ini"
                  >
                    <Download class="w-3.5 h-3.5" />
                  </a>
                </div>
              </div>
              <div v-else class="text-[10px] text-gray-400 text-center py-2 bg-gray-50 dark:bg-zinc-950 rounded-xl border border-dashed border-gray-200 dark:border-zinc-800">
                Belum ada versi sebelumnya.
              </div>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="pt-4 border-t border-gray-100 dark:border-zinc-800/80 flex gap-2">
            <a 
              :href="selectedFile.url" 
              download
              class="flex-1 py-2 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-xl text-center text-xs flex items-center justify-center gap-1.5 shadow-md shadow-primary-500/10 transition"
            >
              <Download class="w-3.5 h-3.5" />
              Download
            </a>
            <button 
              @click="deleteFile(selectedFile)" 
              class="px-3.5 py-2 border border-red-200 hover:bg-red-50 dark:border-red-900/50 dark:hover:bg-red-950/20 text-red-500 rounded-xl transition"
              title="Hapus berkas"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Create Folder Modal -->
    <transition name="fade">
      <div v-if="folderModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 text-left">
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Buat Folder Baru</h3>
          
          <form @submit.prevent="submitFolder" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Folder</label>
              <input 
                type="text" 
                v-model="folderForm.name" 
                required
                placeholder="Folder Tanpa Nama"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
              <button 
                type="button" 
                @click="folderModalOpen = false" 
                class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
              >
                Batal
              </button>
              <button 
                type="submit" 
                :disabled="folderForm.processing"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
              >
                Buat Folder
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Home, ChevronRight, Search, Plus, FolderPlus, Upload, 
  Folder, FolderOpen, Trash2, X, Download, Copy, UploadCloud,
  FileText, FileArchive, FileImage, FileSpreadsheet, FileBox, FileVideo, File
} from '@lucide/vue';

const props = defineProps({
  folders: Array,
  files: Array,
  breadcrumbs: Array,
  currentFolderId: Number,
  filters: Object,
});

const searchQuery = ref(props.filters.search || '');
const selectedFile = ref(null);
const folderModalOpen = ref(false);

const folderForm = useForm({
  name: '',
  parent_id: props.currentFolderId,
});

// Search handling with standard Inertia reload (debounced/direct)
let searchTimeout = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('admin.files.index'), {
      folder_id: props.currentFolderId,
      search: searchQuery.value,
    }, { preserveState: true, replace: true });
  }, 350);
};

// Navigation
const navigateToFolder = (id) => {
  selectedFile.value = null;
  router.get(route('admin.files.index'), {
    folder_id: id,
  });
};

// Select File
const selectFile = (file) => {
  selectedFile.value = file;
};

// Image check
const isImage = (file) => {
  if (!file) return false;
  const mimeMatch = file.mime_type && file.mime_type.startsWith('image/');
  const extMatch = file.extension && ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp'].includes(file.extension.toLowerCase());
  return Boolean(mimeMatch || extMatch);
};

// Icon resolver for files
const getFileIcon = (file) => {
  const mime = file.mime_type || '';
  const ext = file.extension || '';

  if (mime.startsWith('image/')) return FileImage;
  if (mime.startsWith('video/')) return FileVideo;
  if (['zip', 'rar', 'tar', 'gz', '7z'].includes(ext)) return FileArchive;
  if (['xls', 'xlsx', 'csv'].includes(ext)) return FileSpreadsheet;
  if (['doc', 'docx', 'pdf', 'txt', 'rtf'].includes(ext)) return FileText;
  
  return File;
};

// Create Folder modal
const openNewFolderModal = () => {
  folderForm.name = '';
  folderForm.parent_id = props.currentFolderId;
  folderModalOpen.value = true;
};

const submitFolder = () => {
  folderForm.post(route('admin.files.folder.create'), {
    onSuccess: () => {
      folderModalOpen.value = false;
    }
  });
};

// Upload new File
const uploadFile = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const form = useForm({
    file: file,
    folder_id: props.currentFolderId,
  });

  form.post(route('admin.files.upload'), {
    preserveScroll: true,
  });
};

// Upload new Version
const uploadNewVersion = (event) => {
  const file = event.target.files[0];
  if (!file || !selectedFile.value) return;

  const form = useForm({
    file: file,
  });

  form.post(route('admin.files.version.upload', selectedFile.value.id), {
    preserveScroll: true,
    onSuccess: (page) => {
      // Re-resolve selected file details with new version updates
      const updatedFile = page.props.files.find(f => f.id === selectedFile.value.id);
      if (updatedFile) {
        selectedFile.value = updatedFile;
      } else {
        selectedFile.value = null;
      }
    }
  });
};

// Copy URL link to clipboard
const copyLink = (url) => {
  navigator.clipboard.writeText(url);
  alert('Tautan berkas berhasil disalin!');
};

// Delete File
const deleteFile = (file) => {
  if (confirm(`Apakah Anda yakin ingin menghapus berkas "${file.name}"?`)) {
    router.delete(route('admin.files.destroy', file.id), {
      onSuccess: () => {
        selectedFile.value = null;
      }
    });
  }
};

// Delete Folder
const deleteFolder = (folder) => {
  if (confirm(`Apakah Anda yakin ingin menghapus folder "${folder.name}" beserta seluruh isinya?`)) {
    router.delete(route('admin.files.folder.destroy', folder.id));
  }
};
</script>

<style>
.slide-enter-active, .slide-leave-active {
  transition: all 0.3s ease;
}
.slide-enter-from, .slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
