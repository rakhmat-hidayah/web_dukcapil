<template>
  <Head title="Manajemen Tanya Jawab (FAQ)" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Tanya Jawab (FAQ)</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Kelola daftar pertanyaan yang sering diajukan terkait administrasi kependudukan di Kabupaten Dompu.
          </p>
        </div>
        <button 
          @click="openAddModal"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition w-fit self-start sm:self-auto shrink-0"
        >
          <Plus class="w-4 h-4" />
          Tambah FAQ
        </button>
      </div>

      <!-- FAQ Table List -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-3 py-2 sm:px-6 sm:py-4 w-12 text-center font-bold whitespace-nowrap">Sort</th>
                <th class="px-3 py-2 sm:px-6 sm:py-4 whitespace-nowrap">Pertanyaan</th>
                <th class="px-3 py-2 sm:px-6 sm:py-4 whitespace-nowrap">Jawaban</th>
                <th class="px-3 py-2 sm:px-6 sm:py-4 whitespace-nowrap">Kategori</th>
                <th class="px-3 py-2 sm:px-6 sm:py-4 whitespace-nowrap">Status</th>
                <th class="px-3 py-2 sm:px-6 sm:py-4 text-right font-bold whitespace-nowrap">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr v-if="faqs.data.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                  Belum ada tanya jawab terdaftar.
                </td>
              </tr>
              <tr 
                v-for="faq in faqs.data" 
                :key="faq.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition"
              >
                <!-- Drag/Sort indicator icon -->
                <td class="px-3 py-2.5 sm:px-6 sm:py-4 text-center text-gray-300 whitespace-nowrap">
                  <Move class="w-4 h-4 mx-auto" />
                </td>

                <!-- Question -->
                <td class="px-3 py-2.5 sm:px-6 sm:py-4 font-bold text-gray-800 dark:text-zinc-200 max-w-[160px] sm:max-w-xs truncate whitespace-nowrap">
                  {{ faq.question }}
                </td>

                <!-- Answer -->
                <td class="px-3 py-2.5 sm:px-6 sm:py-4 text-gray-500 max-w-[200px] sm:max-w-sm truncate whitespace-nowrap" :title="stripHtml(faq.answer)">
                  {{ stripHtml(faq.answer) }}
                </td>

                <!-- Category -->
                <td class="px-3 py-2.5 sm:px-6 sm:py-4 whitespace-nowrap">
                  <span 
                    v-if="faq.category" 
                    class="px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 font-semibold text-[9px] rounded uppercase"
                  >
                    {{ faq.category }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>

                <!-- Status -->
                <td class="px-3 py-2.5 sm:px-6 sm:py-4 whitespace-nowrap">
                  <span 
                    class="px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wide border"
                    :class="[
                      faq.is_published 
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/30' 
                        : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'
                    ]"
                  >
                    {{ faq.is_published ? 'Online' : 'Draft' }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-3 py-2.5 sm:px-6 sm:py-4 text-right whitespace-nowrap">
                  <div class="flex justify-end gap-1.5">
                    <button 
                      @click="openEditModal(faq)"
                      class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 rounded-lg transition"
                    >
                      <Edit class="w-4 h-4" />
                    </button>
                    <button 
                      @click="deleteFaq(faq)"
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
        <div v-if="faqs.links && faqs.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800/60 bg-gray-50/50 dark:bg-zinc-900/50 flex justify-center gap-1">
          <Link 
            v-for="(link, i) in faqs.links" 
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

      <!-- Add/Edit Modal Dialog -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl p-6 text-left relative scrollbar-thin">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">
              {{ isEditing ? 'Edit FAQ' : 'Tambah FAQ Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Question -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Pertanyaan</label>
                <input 
                  type="text" 
                  v-model="form.question" 
                  required
                  placeholder="Contoh: Bagaimana cara membuat Akta Kelahiran anak?"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Answer Visual Rich Editor -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Jawaban / Solusi *</label>
                <RichEditor v-model="form.answer" placeholder="Langkah-langkah pembuatan akta secara berurutan..." />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Category Group -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Grup Kategori</label>
                  <input 
                    type="text" 
                    v-model="form.category" 
                    placeholder="Contoh: KTP-el, KIA, Akta"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  />
                </div>

                <!-- Status -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Status Publikasi</label>
                  <select 
                    v-model="form.is_published"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option :value="true">Online (Tampilkan)</option>
                    <option :value="false">Draft (Simpan saja)</option>
                  </select>
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
                  {{ form.processing ? 'Menyimpan...' : 'Simpan FAQ' }}
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
import RichEditor from '@/Components/Editors/RichEditor.vue';
import { Plus, Edit, Trash2, Move } from '@lucide/vue';

const props = defineProps({
  faqs: Object,
  categories: Array,
  filters: Object,
});

const stripHtml = (html) => {
  if (!html) return '-';
  const doc = new DOMParser().parseFromString(html, 'text/html');
  return doc.body.textContent || "";
};

const modalOpen = ref(false);
const isEditing = ref(false);
const editId = ref(null);

const form = useForm({
  question: '',
  answer: '',
  category: 'Pelayanan KTP',
  is_published: true,
});

const openAddModal = () => {
  isEditing.value = false;
  editId.value = null;
  form.reset();
  modalOpen.value = true;
};

const openEditModal = (faq) => {
  isEditing.value = true;
  editId.value = faq.id;
  form.question = faq.question;
  form.answer = faq.answer;
  form.category = faq.category || '';
  form.is_published = faq.is_published;
  modalOpen.value = true;
};

const submit = () => {
  if (isEditing.value) {
    form.put(route('admin.faqs.update', editId.value), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  } else {
    form.post(route('admin.faqs.store'), {
      onSuccess: () => {
        modalOpen.value = false;
      }
    });
  }
};

const deleteFaq = (faq) => {
  if (confirm(`Apakah Anda yakin ingin menghapus tanya jawab: "${faq.question}"?`)) {
    router.delete(route('admin.faqs.destroy', faq.id));
  }
};
</script>
