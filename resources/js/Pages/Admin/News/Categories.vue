<template>
  <Head title="Manajemen Kategori Berita" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <Link :href="route('admin.news.index')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
            ← Kembali ke Berita
          </Link>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight mt-2">Kategori Berita</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Kelola kategori berita dan warna penanda topik untuk membedakan rubrik tulisan.
          </p>
        </div>
        <button 
          @click="openAddModal"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
        >
          <Plus class="w-4 h-4" />
          Kategori Baru
        </button>
      </div>

      <!-- Categories list grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- List card -->
        <div class="md:col-span-2 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Warna / Kategori</th>
                <th class="px-6 py-4">Slug URL</th>
                <th class="px-6 py-4">Jumlah Artikel</th>
                <th class="px-6 py-4 text-right font-bold">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr v-if="categories.length === 0">
                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                  Belum ada kategori terdaftar.
                </td>
              </tr>
              <tr 
                v-for="cat in categories" 
                :key="cat.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition"
              >
                <!-- Color tag & Name -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <span 
                      class="w-4 h-4 rounded-full border border-white/20 shrink-0" 
                      :style="{ backgroundColor: cat.color }"
                    ></span>
                    <span class="font-bold text-gray-800 dark:text-zinc-200">{{ cat.name }}</span>
                  </div>
                </td>

                <!-- Slug -->
                <td class="px-6 py-4 font-mono font-medium text-gray-500">
                  {{ cat.slug }}
                </td>

                <!-- Count -->
                <td class="px-6 py-4 font-bold text-gray-700 dark:text-zinc-300">
                  {{ cat.news_count || 0 }} berita
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button 
                      @click="openEditModal(cat)"
                      class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 rounded"
                    >
                      <Edit class="w-4 h-4" />
                    </button>
                    <button 
                      @click="deleteCategory(cat)"
                      class="p-1 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 rounded"
                      :disabled="cat.news_count > 0"
                      :title="cat.news_count > 0 ? 'Pindahkan berita di dalam kategori ini terlebih dahulu sebelum menghapus.' : ''"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Guide card -->
        <div class="bg-gray-50 dark:bg-zinc-900/30 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 h-fit text-xs text-gray-600 dark:text-zinc-400">
          <h3 class="font-bold text-gray-800 dark:text-zinc-200 mb-3 flex items-center gap-2">
            💡 Info Kategori Berita
          </h3>
          <p class="leading-relaxed space-y-2">
            Kategori digunakan untuk mengelompokkan rilis berita agar masyarakat mudah menyaring berita yang dicari. 
            <br/><br/>
            Warna penanda akan muncul sebagai badge visual di halaman depan publik dan portal dashboard. 
            <br/><br/>
            Kategori yang sudah terasosiasi dengan berita tidak dapat dihapus langsung untuk menghindari error referensi.
          </p>
        </div>
      </div>

      <!-- Add/Edit Modal Dialog -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">
              {{ isEditing ? 'Edit Kategori' : 'Kategori Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Kategori</label>
                <input 
                  type="text" 
                  v-model="form.name" 
                  required
                  placeholder="Contoh: Kependudukan, Pelayanan, Event"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Color Picker -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Warna Penanda Badge (Hex)</label>
                <div class="flex items-center gap-3">
                  <input 
                    type="color" 
                    v-model="form.color" 
                    class="w-9 h-9 rounded-lg border border-gray-200 dark:border-zinc-700 cursor-pointer overflow-hidden p-0 bg-transparent"
                  />
                  <input 
                    type="text" 
                    v-model="form.color" 
                    placeholder="#3b82f6"
                    required
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono uppercase focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  />
                </div>
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
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Kategori' }}
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
import { Plus, Edit, Trash2 } from '@lucide/vue';

const props = defineProps({
  categories: Array,
});

const modalOpen = ref(false);
const isEditing = ref(false);
const editId = ref(null);

const form = useForm({
  name: '',
  color: '#3b82f6',
});

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  form.reset();
  modalOpen.value = true;
};

const openEditModal = (cat) => {
  isEditing.value = true;
  editId.value = cat.id;
  form.name = cat.name;
  form.color = cat.color;
  modalOpen.value = true;
};

const submit = () => {
  if (isEditing.value) {
    form.put(route('admin.news.categories.update', editId.value), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  } else {
    form.post(route('admin.news.categories.store'), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  }
};

const deleteCategory = (cat) => {
  if (confirm(`Apakah Anda yakin ingin menghapus kategori "${cat.name}"?`)) {
    router.delete(route('admin.news.categories.destroy', cat.id));
  }
};
</script>
