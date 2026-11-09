<template>
  <Head title="Pusat Unduhan Formulir & Regulasi" />

  <PublicLayout>
    <div class="space-y-8 text-left">
      <!-- Title section -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Pusat Unduhan & Berkas</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Unduh dokumen persyaratan pelayanan kependudukan, blanko permohonan dinas, serta berkas hukum / peraturan daerah.
        </p>
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

          <!-- Category filter -->
          <select 
            v-model="category" 
            class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            @change="applyFilters"
          >
            <option value="">Semua Kategori</option>
            <template v-for="cat in categories" :key="cat.id">
              <option :value="cat.slug">{{ cat.name }}</option>
              <option v-for="sub in cat.children" :key="sub.id" :value="sub.slug">&nbsp;&nbsp;— {{ sub.name }}</option>
            </template>
          </select>
        </div>

        <button 
          @click="applyFilters"
          class="px-4 py-2 bg-gray-900 dark:bg-zinc-800 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition"
        >
          Cari
        </button>
      </div>

      <!-- Downloads grid list -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-if="downloads.data.length === 0" class="col-span-full text-center py-12 text-gray-400 text-xs">
          Belum ada berkas unduhan ditemukan untuk kriteria pencarian ini.
        </div>

        <div 
          v-for="doc in downloads.data" 
          :key="doc.id"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition"
        >
          <div class="space-y-3">
            <span 
              v-if="doc.category" 
              class="text-[9px] font-bold font-mono uppercase tracking-wider border px-1.5 py-0.5 rounded bg-gray-50 dark:bg-zinc-800 border-gray-100 dark:border-zinc-700 text-gray-500"
            >
              {{ doc.category.name }}
            </span>
            <h4 class="font-extrabold text-gray-800 dark:text-zinc-100 text-sm leading-snug line-clamp-2">
              {{ doc.title }}
            </h4>
            <p class="text-xs text-gray-400 leading-relaxed line-clamp-2" v-if="doc.description">
              {{ doc.description }}
            </p>
          </div>

          <!-- Document stats & pre-flight stream download -->
          <div class="border-t border-gray-50 dark:border-zinc-800 pt-4 flex justify-between items-center text-[10px] text-gray-400 font-semibold">
            <div class="flex flex-col gap-0.5">
              <span>Format: <strong class="uppercase text-primary-500">{{ doc.file_type }}</strong> ({{ doc.file_size_human || formatBytes(doc.file_size) }})</span>
              <span>Didownload: {{ doc.download_count || 0 }} kali</span>
            </div>
            
            <button 
              type="button"
              @click="handlePublicDownload(doc)"
              :disabled="downloadingDocId === doc.id"
              class="flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-[10px] font-black rounded-xl shadow shadow-primary-500/10 active:scale-95 transition disabled:opacity-50"
            >
              <FileDown v-if="downloadingDocId !== doc.id" class="w-3.5 h-3.5" />
              <Loader2 v-else class="w-3.5 h-3.5 animate-spin" />
              <span>{{ downloadingDocId === doc.id ? 'Memeriksa...' : 'Unduh' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="downloads.links && downloads.links.length > 3" class="flex justify-center gap-1.5 pt-6">
        <Link 
          v-for="(link, i) in downloads.links" 
          :key="i"
          :href="link.url || '#'"
          v-html="link.label"
          :disabled="!link.url"
          class="px-3.5 py-2 rounded-xl text-xs font-semibold transition border"
          :class="[
            link.active 
              ? 'bg-primary-600 text-white border-primary-500 shadow-sm shadow-primary-500/10' 
              : 'bg-white dark:bg-zinc-900 border-gray-100 dark:border-zinc-800 hover:bg-gray-50 text-gray-600 dark:text-zinc-400',
            !link.url ? 'opacity-40 pointer-events-none' : ''
          ]"
        />
      </div>
    </div>

    <!-- PRE-FLIGHT ERROR ALERT MODAL -->
    <transition name="fade">
      <div v-if="downloadErrorModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 text-left relative space-y-4">
          <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
            <AlertTriangle class="w-6 h-6" />
          </div>
          <div class="text-center space-y-1">
            <h3 class="text-sm font-bold text-gray-900 dark:text-zinc-100">Unduhan Tidak Dapat Diproses</h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 leading-relaxed">
              {{ downloadErrorData?.reason_text || 'Dokumen tidak dapat diunduh saat ini.' }}
            </p>
          </div>
          <button 
            @click="downloadErrorModal = false"
            class="w-full py-2.5 bg-gray-900 dark:bg-zinc-800 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition"
          >
            Tutup
          </button>
        </div>
      </div>
    </transition>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Search, FileDown, Loader2, AlertTriangle } from '@lucide/vue';
import axios from 'axios';

const props = defineProps({
  downloads: Object,
  categories: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');

const downloadingDocId = ref(null);
const downloadErrorModal = ref(false);
const downloadErrorData = ref(null);

const applyFilters = () => {
  router.get(route('public.downloads.index'), {
    search: search.value,
    category: category.value,
  }, {
    preserveState: true,
  });
};

const handlePublicDownload = async (doc) => {
  downloadingDocId.value = doc.id;
  try {
    const checkUrl = doc.check_url || route('public.downloads.check', doc.id);
    const res = await axios.get(checkUrl);
    if (res.data && res.data.allowed) {
      window.location.href = doc.download_url || route('public.downloads.stream', doc.id);
    } else {
      downloadErrorData.value = res.data;
      downloadErrorModal.value = true;
    }
  } catch (err) {
    downloadErrorData.value = {
      reason_text: 'Terjadi kendala jaringan saat melakukan verifikasi dokumen.'
    };
    downloadErrorModal.value = true;
  } finally {
    downloadingDocId.value = null;
  }
};

const formatBytes = (bytes) => {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>
