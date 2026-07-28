<template>
  <Head title="Manajemen Pengumuman Resmi" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Pengumuman & Ticker</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Buat maklumat resmi, popup peringatan darurat, dan teks berjalan (running text ticker) di beranda.
          </p>
        </div>
        <button 
          @click="openAddModal"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
        >
          <Plus class="w-4 h-4" />
          Buat Pengumuman
        </button>
      </div>

      <!-- Announcements Table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Pengumuman</th>
                <th class="px-6 py-4">Prioritas</th>
                <th class="px-6 py-4">Saluran Tampilan</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Jadwal Rilis / Kadaluwarsa</th>
                <th class="px-6 py-4 text-right font-bold">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr v-if="announcements.data.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                  Belum ada pengumuman terdaftar.
                </td>
              </tr>
              <tr 
                v-for="item in announcements.data" 
                :key="item.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition"
              >
                <!-- Title & Pinned badge -->
                <td class="px-6 py-4">
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span v-if="item.is_pinned" class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30 text-[8px] font-bold rounded uppercase">
                        📌 Pinned
                      </span>
                      <p class="font-bold text-gray-800 dark:text-zinc-200 leading-snug">{{ item.title }}</p>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 max-w-sm truncate">{{ item.content }}</p>
                  </div>
                </td>

                <!-- Priority badge -->
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wide border"
                    :class="{
                      'bg-red-50 text-red-700 border-red-100 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/30': item.priority === 'urgent',
                      'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/30': item.priority === 'high',
                      'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-900/30': item.priority === 'normal',
                      'bg-gray-100 text-gray-600 border-gray-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700': item.priority === 'low'
                    }"
                  >
                    {{ item.priority }}
                  </span>
                </td>

                <!-- Channels list -->
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-if="item.is_popup" class="px-1.5 py-0.5 bg-purple-50 text-purple-700 dark:bg-purple-950/30 dark:text-purple-400 border border-purple-100 dark:border-purple-900/20 font-bold text-[9px] rounded uppercase">
                      🚨 Popup
                    </span>
                    <span v-if="item.is_ticker" class="px-1.5 py-0.5 bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 border border-blue-100 dark:border-blue-900/20 font-bold text-[9px] rounded uppercase">
                      🏃 Running Ticker
                    </span>
                    <span v-if="!item.is_popup && !item.is_ticker" class="px-1.5 py-0.5 bg-gray-50 text-gray-600 dark:bg-zinc-800 dark:text-zinc-400 border border-gray-200 dark:border-zinc-700 font-bold text-[9px] rounded uppercase">
                      📰 Feed Beranda
                    </span>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wide border"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/30': item.status === 'published',
                      'bg-gray-100 text-gray-600 border-gray-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700': item.status === 'draft',
                      'bg-red-50 text-red-700 border-red-100 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/30': item.status === 'archived'
                    }"
                  >
                    {{ item.status }}
                  </span>
                </td>

                <!-- Schedule Dates -->
                <td class="px-6 py-4 text-gray-400 font-semibold">
                  <div class="flex flex-col gap-0.5">
                    <span>Mulai: {{ formatDate(item.published_at) }}</span>
                    <span v-if="item.expires_at">Akhir: {{ formatDate(item.expires_at) }}</span>
                    <span v-else class="text-[10px] text-gray-300">Berlaku Selamanya</span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-1.5">
                    <button 
                      @click="openEditModal(item)"
                      class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 rounded-lg transition"
                    >
                      <Edit class="w-4 h-4" />
                    </button>
                    <button 
                      @click="deleteItem(item)"
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
      </div>

      <!-- Add/Edit Modal Dialog -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl p-6 text-left relative scrollbar-thin">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">
              {{ isEditing ? 'Edit Pengumuman' : 'Tulis Pengumuman Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Pengumuman</label>
                <input 
                  type="text" 
                  v-model="form.title" 
                  required
                  placeholder="Tulis judul maklumat..."
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Content -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Isi Pengumuman / Detail *</label>
                <RichEditor v-model="form.content" placeholder="Rincian informasi pengumuman..." />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Priority -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Prioritas Penting</label>
                  <select 
                    v-model="form.priority"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option value="low">Low (Biasa)</option>
                    <option value="normal">Normal</option>
                    <option value="high">High (Penting)</option>
                    <option value="urgent">Urgent (Darurat)</option>
                  </select>
                </div>

                <!-- Status -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Status Rilis</label>
                  <select 
                    v-model="form.status"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                  </select>
                </div>
              </div>

              <!-- Schedules -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Mulai Tayang</label>
                  <input 
                    type="datetime-local" 
                    v-model="form.published_at"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Kedaluwarsa (Opsional)</label>
                  <input 
                    type="datetime-local" 
                    v-model="form.expires_at"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  />
                </div>
              </div>

              <!-- Channels checkboxes -->
              <div class="pt-2 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 dark:text-zinc-300">
                  <input type="checkbox" v-model="form.is_pinned" class="accent-primary-500 rounded border-gray-300" />
                  <span>Sematkan di atas pengumuman lain (Pin to top)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 dark:text-zinc-300">
                  <input type="checkbox" v-model="form.is_popup" class="accent-primary-500 rounded border-gray-300" />
                  <span>Jadikan Alert Popup di Halaman Utama Publik</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 dark:text-zinc-300">
                  <input type="checkbox" v-model="form.is_ticker" class="accent-primary-500 rounded border-gray-300" />
                  <span>Tampilkan sebagai Ticker Running Text Berjalan</span>
                </label>
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
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Pengumuman' }}
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
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichEditor from '@/Components/Editors/RichEditor.vue';
import { Plus, Edit, Trash2 } from '@lucide/vue';

const props = defineProps({
  announcements: Object,
  filters: Object,
});

const modalOpen = ref(false);
const isEditing = ref(false);
const editId = ref(null);

const form = useForm({
  title: '',
  content: '',
  priority: 'normal',
  status: 'draft',
  published_at: '',
  expires_at: '',
  is_pinned: false,
  is_popup: false,
  is_ticker: false,
});

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  form.reset();
  form.published_at = new Date().toISOString().substring(0, 16);
  modalOpen.value = true;
};

const openEditModal = (item) => {
  isEditing.value = true;
  editId.value = item.id;
  form.title = item.title;
  form.content = item.content;
  form.priority = item.priority;
  form.status = item.status;
  form.published_at = item.published_at ? item.published_at.substring(0, 16) : '';
  form.expires_at = item.expires_at ? item.expires_at.substring(0, 16) : '';
  form.is_pinned = item.is_pinned;
  form.is_popup = item.is_popup;
  form.is_ticker = item.is_ticker;
  modalOpen.value = true;
};

const submit = () => {
  if (isEditing.value) {
    form.put(route('admin.announcements.update', editId.value), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  } else {
    form.post(route('admin.announcements.store'), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  }
};

const deleteItem = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus pengumuman "${item.title}"?`)) {
    router.delete(route('admin.announcements.destroy', item.id));
  }
};
</script>
