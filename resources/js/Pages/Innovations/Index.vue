<template>
  <Head title="Manajemen Inovasi Pelayanan" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Inovasi Pelayanan</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola informasi program inovasi pelayanan Disdukcapil Dompu.</p>
        </div>
        <button 
          @click="openModal(null)"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5"
        >
          <Plus class="w-3.5 h-3.5" /> Tambah Inovasi
        </button>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold">
        ✓ {{ $page.props.flash.success }}
      </div>

      <!-- Innovations table list -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800">
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400 w-16">Icon</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Nama Inovasi</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Deskripsi Singkat</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">YouTube Video</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Urutan</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                <th class="px-5 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
              <tr v-if="innovations.length === 0">
                <td colspan="7" class="px-5 py-8 text-center text-gray-400 italic">Belum ada inovasi terdaftar.</td>
              </tr>
              <tr v-for="item in innovations" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                <td class="px-5 py-3 text-center text-lg">{{ item.icon || '🚐' }}</td>
                <td class="px-5 py-3 font-bold text-gray-700 dark:text-zinc-200">{{ item.title }}</td>
                <td class="px-5 py-3 text-gray-450 max-w-xs truncate">{{ item.description || '-' }}</td>
                <td class="px-5 py-3 font-mono text-[10px] text-gray-400 truncate max-w-xs">
                  {{ item.youtube_url ? 'Tersedia (Embed)' : 'Tidak ada' }}
                </td>
                <td class="px-5 py-3 font-mono font-semibold">{{ item.sort_order }}</td>
                <td class="px-5 py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-bold uppercase font-mono"
                    :class="item.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400'"
                  >
                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-5 py-3 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button @click="openModal(item)" class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><SquarePen class="w-3.5 h-3.5" /></button>
                    <button @click="confirmDelete(item)" class="p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><Trash2 class="w-3.5 h-3.5" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Innovation Form Modal -->
    <div v-if="modal.open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="modal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 w-full max-w-2xl shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <h3 class="font-black text-sm text-gray-900 dark:text-zinc-50">
          {{ modal.editing ? 'Edit Inovasi' : 'Tambah Inovasi Baru' }}
        </h3>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Inovasi *</label>
              <input v-model="form.title" required type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>

            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Icon (Emoji)</label>
              <input v-model="form.icon" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>

            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Urutan Sortir</label>
              <input v-model="form.sort_order" type="number" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
          </div>

          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">YouTube Video URL (Opsional)</label>
            <input v-model="form.youtube_url" type="url" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
          </div>

          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Deskripsi Ringkas</label>
            <input v-model="form.description" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
          </div>

          <!-- Visual Rich Content Editor -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Uraian Lengkap Inovasi Pelayanan *</label>
            <RichEditor v-model="form.content" placeholder="Tuliskan rincian, latar belakang, dan tata cara penggunaan inovasi pelayanan..." />
          </div>

          <div class="flex items-center gap-3">
            <input id="inno_is_active" type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-primary-600" />
            <label for="inno_is_active" class="text-xs text-gray-500 dark:text-zinc-400 cursor-pointer">Inovasi aktif (tampil di website publik)</label>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-gray-150 dark:border-zinc-800">
            <button type="button" @click="modal.open = false" class="px-4 py-2 text-xs font-bold border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 transition">Batal</button>
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichEditor from '@/Components/Editors/RichEditor.vue';
import { Plus, SquarePen, Trash2 } from '@lucide/vue';

const props = defineProps({
  innovations: Array,
});

const modal = reactive({
  open: false,
  editing: null,
});

const form = reactive({
  title: '',
  icon: '🚐',
  color: '#4f46e5',
  description: '',
  content: '',
  youtube_url: '',
  sort_order: 0,
  is_active: true,
});

const openModal = (item) => {
  modal.editing = item;
  if (item) {
    Object.assign(form, {
      title: item.title,
      icon: item.icon || '🚐',
      color: item.color || '#4f46e5',
      description: item.description || '',
      content: item.content || '',
      youtube_url: item.youtube_url || '',
      sort_order: item.sort_order || 0,
      is_active: !!item.is_active,
    });
  } else {
    Object.assign(form, {
      title: '',
      icon: '🚐',
      color: '#4f46e5',
      description: '',
      content: '',
      youtube_url: '',
      sort_order: 0,
      is_active: true,
    });
  }
  modal.open = true;
};

const submitForm = () => {
  if (modal.editing) {
    router.put(route('admin.innovations.update', modal.editing.id), form, {
      onSuccess: () => {
        modal.open = false;
      }
    });
  } else {
    router.post(route('admin.innovations.store'), form, {
      onSuccess: () => {
        modal.open = false;
      }
    });
  }
};

const confirmDelete = (item) => {
  if (confirm(`Hapus inovasi pelayanan "${item.title}"?`)) {
    router.delete(route('admin.innovations.destroy', item.id));
  }
};
</script>
