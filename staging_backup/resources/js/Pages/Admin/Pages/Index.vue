<template>
  <Head title="Manajemen Halaman Dinamis" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Halaman Dinamis (Pages)</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Buat halaman profil instansi, visi misi, standar pelayanan, dan informasi keterbukaan publik.
          </p>
        </div>
        <Link 
          :href="route('admin.pages.create')"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
        >
          <Plus class="w-4 h-4" />
          Halaman Baru
        </Link>
      </div>

      <!-- Pages Table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Judul Halaman</th>
                <th class="px-6 py-4">Slug URL</th>
                <th class="px-6 py-4">Template Layout</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Dibuat Oleh</th>
                <th class="px-6 py-4">Terakhir Diupdate</th>
                <th class="px-6 py-4 text-right font-bold">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr v-if="pages.data.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                  Belum ada halaman dinamis dibuat. Klik "Halaman Baru" untuk membuat.
                </td>
              </tr>
              <tr 
                v-for="page in pages.data" 
                :key="page.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition"
              >
                <!-- Title -->
                <td class="px-6 py-4 font-bold text-gray-800 dark:text-zinc-200">
                  {{ page.title }}
                </td>

                <!-- Slug -->
                <td class="px-6 py-4 font-mono text-gray-500">
                  /{{ page.slug }}
                </td>

                <!-- Template -->
                <td class="px-6 py-4 font-semibold text-gray-600 dark:text-zinc-400">
                  {{ page.template }}
                </td>

                <!-- Status -->
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wide border"
                    :class="[
                      page.status === 'published' 
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/30' 
                        : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'
                    ]"
                  >
                    {{ page.status }}
                  </span>
                </td>

                <!-- Author -->
                <td class="px-6 py-4 font-medium text-gray-700 dark:text-zinc-300">
                  {{ page.author ? page.author.name : 'Super Admin' }}
                </td>

                <!-- Updated At -->
                <td class="px-6 py-4 text-gray-400 font-semibold">
                  {{ formatDate(page.updated_at) }}
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-1.5">
                    <Link 
                      :href="route('admin.pages.edit', page.id)"
                      class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 rounded-lg transition"
                      title="Edit Halaman"
                    >
                      <Edit class="w-4 h-4" />
                    </Link>
                    <button 
                      @click="deletePage(page)"
                      class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 rounded-lg transition"
                      title="Hapus Halaman"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Edit, Trash2 } from '@lucide/vue';

const props = defineProps({
  pages: Object,
});

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const deletePage = (page) => {
  if (confirm(`Apakah Anda yakin ingin menghapus halaman "${page.title}"?`)) {
    router.delete(route('admin.pages.destroy', page.id));
  }
};
</script>
