<template>
  <Head title="Kategori Pengaduan" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Kategori Pengaduan</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola tipe klasifikasi tiket pengaduan masyarakat.</p>
        </div>
        <button 
          @click="openModal(null)"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5"
        >
          <Plus class="w-3.5 h-3.5" /> Tambah Kategori
        </button>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold">
        ✓ {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 text-xs rounded-xl font-semibold">
        ✕ {{ $page.props.flash.error }}
      </div>

      <!-- Categories table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800">
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400 w-16">Icon</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Nama Kategori</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Slug</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Deskripsi</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Urutan</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                <th class="px-5 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
              <tr v-if="categories.length === 0">
                <td colspan="7" class="px-5 py-8 text-center text-gray-400 italic">Belum ada kategori.</td>
              </tr>
              <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                <td class="px-5 py-3 text-center text-lg">{{ cat.icon || '📝' }}</td>
                <td class="px-5 py-3 font-bold text-gray-700 dark:text-zinc-200">{{ cat.name }}</td>
                <td class="px-5 py-3 text-gray-400 font-mono">{{ cat.slug }}</td>
                <td class="px-5 py-3 text-gray-400 max-w-xs truncate">{{ cat.description || '-' }}</td>
                <td class="px-5 py-3 font-mono">{{ cat.sort_order }}</td>
                <td class="px-5 py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-bold uppercase font-mono"
                    :class="cat.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400'"
                  >
                    {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-5 py-3 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button @click="openModal(cat)" class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><SquarePen class="w-3.5 h-3.5" /></button>
                    <button @click="confirmDelete(cat)" class="p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><Trash2 class="w-3.5 h-3.5" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Category Form Modal -->
    <div v-if="modal.open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="modal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 w-full max-w-md shadow-2xl space-y-4">
        <h3 class="font-black text-sm text-gray-900 dark:text-zinc-50">
          {{ modal.editing ? 'Edit Kategori Pengaduan' : 'Tambah Kategori Pengaduan' }}
        </h3>

        <form @submit.prevent="submitForm" class="space-y-3">
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Kategori *</label>
            <input v-model="form.name" required type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
          </div>

          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Slug *</label>
            <input v-model="form.slug" required type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Icon (Emoji / Text)</label>
              <input v-model="form.icon" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Urutan Sortir</label>
              <input v-model="form.sort_order" type="number" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
          </div>

          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Warna Badge (Hex)</label>
            <input v-model="form.color" type="color" class="w-8 h-8 rounded-lg border-0 cursor-pointer" />
          </div>

          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Deskripsi Singkat</label>
            <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
          </div>

          <div class="flex items-center gap-3 py-1">
            <input id="cat_is_active" type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-primary-600" />
            <label for="cat_is_active" class="text-xs text-gray-500 dark:text-zinc-400 cursor-pointer">Kategori aktif (bisa dipilih masyarakat)</label>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-gray-150 dark:border-zinc-800">
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
import { Plus, SquarePen, Trash2 } from '@lucide/vue';

const props = defineProps({
  categories: Array,
});

const modal = reactive({
  open: false,
  editing: null,
});

const form = reactive({
  name: '',
  slug: '',
  icon: '📝',
  color: '#6366f1',
  description: '',
  sort_order: 0,
  is_active: true,
});

const openModal = (cat) => {
  modal.editing = cat;
  if (cat) {
    Object.assign(form, {
      name: cat.name,
      slug: cat.slug,
      icon: cat.icon || '📝',
      color: cat.color || '#6366f1',
      description: cat.description || '',
      sort_order: cat.sort_order || 0,
      is_active: !!cat.is_active,
    });
  } else {
    Object.assign(form, {
      name: '',
      slug: '',
      icon: '📝',
      color: '#6366f1',
      description: '',
      sort_order: 0,
      is_active: true,
    });
  }
  modal.open = true;
};

const submitForm = () => {
  if (modal.editing) {
    router.put(route('admin.complaints.categories.update', modal.editing.id), form, {
      onSuccess: () => {
        modal.open = false;
      }
    });
  } else {
    router.post(route('admin.complaints.categories.store'), form, {
      onSuccess: () => {
        modal.open = false;
      }
    });
  }
};

const confirmDelete = (cat) => {
  if (confirm(`Hapus kategori "${cat.name}"?`)) {
    router.delete(route('admin.complaints.categories.destroy', cat.id));
  }
};
</script>
