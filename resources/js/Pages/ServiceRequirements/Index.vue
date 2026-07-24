<template>
  <Head title="Manajemen Persyaratan Layanan" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Persyaratan Layanan</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola informasi jenis layanan kependudukan dan persyaratannya.</p>
        </div>
        <button 
          @click="openModal(null)"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5 w-fit self-start sm:self-auto shrink-0"
        >
          <Plus class="w-3.5 h-3.5" /> Tambah Layanan
        </button>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold">
        ✓ {{ $page.props.flash.success }}
      </div>

      <!-- Services table list -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800">
                <th class="px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 w-12 whitespace-nowrap">Icon</th>
                <th class="px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Nama Layanan</th>
                <th class="px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Deskripsi</th>
                <th class="px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Waktu Proses</th>
                <th class="px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Biaya</th>
                <th class="px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Status</th>
                <th class="px-3 py-2 sm:px-5 sm:py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
              <tr v-if="services.length === 0">
                <td colspan="7" class="px-5 py-8 text-center text-gray-400 italic">Belum ada layanan terdaftar.</td>
              </tr>
              <tr v-for="item in services" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 text-center text-lg whitespace-nowrap">{{ item.icon || '📝' }}</td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 font-bold text-gray-700 dark:text-zinc-200 whitespace-nowrap">{{ item.title }}</td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 text-gray-450 max-w-[160px] sm:max-w-xs truncate whitespace-nowrap">{{ item.description || '-' }}</td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 font-mono font-semibold whitespace-nowrap">{{ item.processing_time }}</td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 font-mono font-semibold text-emerald-600 dark:text-emerald-450 whitespace-nowrap">{{ item.cost }}</td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 whitespace-nowrap">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-bold uppercase font-mono"
                    :class="item.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400'"
                  >
                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 text-right whitespace-nowrap">
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

    <!-- Service Form Modal -->
    <div v-if="modal.open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="modal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 w-full max-w-2xl shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <h3 class="font-black text-sm text-gray-900 dark:text-zinc-50">
          {{ modal.editing ? 'Edit Layanan' : 'Tambah Layanan Baru' }}
        </h3>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Layanan *</label>
              <input v-model="form.title" required type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>

            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Waktu Penyelesaian *</label>
              <input v-model="form.processing_time" required type="text" placeholder="Contoh: 1 Hari Kerja" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>

            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Biaya / Tarif *</label>
              <input v-model="form.cost" required type="text" placeholder="Contoh: Gratis / Rp 0" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>

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
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Deskripsi Ringkas</label>
            <input v-model="form.description" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
          </div>

          <!-- Visual Rich Requirements Details Editor -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Syarat & Ketentuan Layanan *</label>
            <RichEditor v-model="form.requirements" placeholder="Tuliskan rincian persyaratan, berkas yang dibawa, dan ketentuan layanan..." />
          </div>

          <div class="flex items-center gap-3">
            <input id="service_is_active" type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-primary-600" />
            <label for="service_is_active" class="text-xs text-gray-500 dark:text-zinc-400 cursor-pointer">Layanan aktif (tampil di website publik)</label>
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
  services: Array,
});

const modal = reactive({
  open: false,
  editing: null,
});

const form = reactive({
  title: '',
  icon: '📝',
  color: '#2563eb',
  description: '',
  requirements: '',
  processing_time: '1 Hari Kerja',
  cost: 'Gratis / Rp 0',
  sort_order: 0,
  is_active: true,
});

const openModal = (item) => {
  modal.editing = item;
  if (item) {
    Object.assign(form, {
      title: item.title,
      icon: item.icon || '📝',
      color: item.color || '#2563eb',
      description: item.description || '',
      requirements: item.requirements || '',
      processing_time: item.processing_time || '1 Hari Kerja',
      cost: item.cost || 'Gratis / Rp 0',
      sort_order: item.sort_order || 0,
      is_active: !!item.is_active,
    });
  } else {
    Object.assign(form, {
      title: '',
      icon: '📝',
      color: '#2563eb',
      description: '',
      requirements: '',
      processing_time: '1 Hari Kerja',
      cost: 'Gratis / Rp 0',
      sort_order: 0,
      is_active: true,
    });
  }
  modal.open = true;
};

const submitForm = () => {
  if (modal.editing) {
    router.put(route('admin.service-requirements.update', modal.editing.id), form, {
      onSuccess: () => {
        modal.open = false;
      }
    });
  } else {
    router.post(route('admin.service-requirements.store'), form, {
      onSuccess: () => {
        modal.open = false;
      }
    });
  }
};

const confirmDelete = (item) => {
  if (confirm(`Hapus persyaratan layanan untuk "${item.title}"?`)) {
    router.delete(route('admin.service-requirements.destroy', item.id));
  }
};
</script>
