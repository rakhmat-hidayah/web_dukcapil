<template>
  <Head title="API Keys & Integration" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Title section -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Integrasi & API Keys</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Buat dan kelola API Key untuk integrasi aplikasi pihak ketiga / instansi pemerintah eksternal.
          </p>
        </div>
        <button 
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition"
        >
          <Plus class="w-4 h-4" />
          Generate API Key
        </button>
      </div>

      <!-- Keys Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Client / Aplikasi</th>
                <th class="px-6 py-4">API Key Token</th>
                <th class="px-6 py-4">Scope / Scope Permisi</th>
                <th class="px-6 py-4">Rate Limit</th>
                <th class="px-6 py-4">Total Request</th>
                <th class="px-6 py-4">Kedaluwarsa</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right font-bold">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr v-if="apiKeys.length === 0">
                <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                  Belum ada API Key terdaftar. Klik "Generate API Key" untuk membuat.
                </td>
              </tr>
              <tr 
                v-for="key in apiKeys" 
                :key="key.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition duration-150"
              >
                <!-- Client Name -->
                <td class="px-6 py-4 font-bold text-gray-800 dark:text-zinc-200">
                  {{ key.client_name }}
                </td>

                <!-- Key value -->
                <td class="px-6 py-4 font-mono select-all">
                  <div class="flex items-center gap-2">
                    <span class="text-gray-500 font-semibold">{{ isMasked(key.id) ? maskKey(key.api_key) : key.api_key }}</span>
                    <button 
                      @click="toggleMask(key.id)"
                      class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 rounded"
                    >
                      <component :is="isMasked(key.id) ? Eye : EyeOff" class="w-3.5 h-3.5" />
                    </button>
                    <button 
                      @click="copyKey(key.api_key)"
                      class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 rounded"
                      title="Salin Key"
                    >
                      <Copy class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>

                <!-- Scope Permissions -->
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="scope in key.permissions" 
                      :key="scope"
                      class="px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 font-semibold font-mono text-[9px] rounded uppercase"
                    >
                      {{ scope }}
                    </span>
                  </div>
                </td>

                <!-- Rate Limit -->
                <td class="px-6 py-4 font-bold text-gray-700 dark:text-zinc-300">
                  {{ key.client_name === 'Internal System' ? 'Unlimited' : `${key.rate_limit_per_hour} req/jam` }}
                </td>

                <!-- Total requests log count -->
                <td class="px-6 py-4 font-semibold text-gray-500">
                  {{ key.total_requests }} hits
                </td>

                <!-- Expiry Date -->
                <td class="px-6 py-4 text-gray-400 font-medium">
                  {{ key.expires_at }}
                </td>

                <!-- Status Toggle -->
                <td class="px-6 py-4">
                  <button 
                    @click="toggleStatus(key)"
                    class="px-2 py-0.5 rounded font-bold text-[9px] uppercase tracking-wide border"
                    :class="[
                      key.is_active 
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/30' 
                        : 'bg-red-50 text-red-700 border-red-100 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900/30'
                    ]"
                  >
                    {{ key.is_active ? 'Active' : 'Suspended' }}
                  </button>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-1.5">
                    <button 
                      @click="regenerateKey(key)"
                      class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 rounded-lg transition"
                      title="Regenerasi Token"
                    >
                      <RefreshCw class="w-4 h-4" />
                    </button>
                    <button 
                      @click="deleteKey(key)"
                      class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition"
                      title="Hapus API Key"
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

      <!-- Create API Key Modal -->
      <transition name="fade">
        <div v-if="createModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Generate API Key Baru</h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Client Name -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Client / Aplikasi</label>
                <input 
                  type="text" 
                  v-model="form.client_name" 
                  required
                  placeholder="Contoh: Aplikasi Kecamatan Dompu / Dompu Insight"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Rate Limit Override -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Batas Request per Jam (Rate Limit)</label>
                <input 
                  type="number" 
                  v-model="form.rate_limit_per_hour" 
                  required
                  placeholder="Default: 1000"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Expiry Date -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Kedaluwarsa (Kosongkan jika abadi)</label>
                <input 
                  type="date" 
                  v-model="form.expires_at" 
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Permissions scopes checkbox list -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2">Scope Endpoint Permisi</label>
                <div class="grid grid-cols-2 gap-2 p-3 bg-gray-50 dark:bg-zinc-800/30 border border-gray-100 dark:border-zinc-800 rounded-2xl max-h-40 overflow-y-auto scrollbar-thin">
                  <label 
                    v-for="scope in availableScopes" 
                    :key="scope.value" 
                    class="flex items-center gap-2 py-1 cursor-pointer text-xs"
                  >
                    <input 
                      type="checkbox" 
                      :value="scope.value" 
                      v-model="form.permissions" 
                      class="accent-primary-500 rounded border-gray-300"
                    />
                    <span class="font-medium">{{ scope.label }}</span>
                  </label>
                </div>
              </div>

              <!-- Footer Buttons -->
              <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-6">
                <button 
                  type="button" 
                  @click="createModalOpen = false" 
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  :disabled="form.processing"
                  class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
                >
                  {{ form.processing ? 'Generating...' : 'Generate Key' }}
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
import { Plus, Trash2, RefreshCw, Copy, Eye, EyeOff } from '@lucide/vue';

const props = defineProps({
  apiKeys: Array,
});

const createModalOpen = ref(false);
const maskedKeys = ref({});

const availableScopes = [
  { value: 'news', label: 'News / Berita' },
  { value: 'announcements', label: 'Announcements' },
  { value: 'gallery', label: 'Gallery' },
  { value: 'downloads', label: 'Downloads' },
  { value: 'faq', label: 'FAQ Accordion' },
  { value: 'services', label: 'Service Info' },
  { value: 'demographics', label: 'Demographics' },
  { value: 'complaints', label: 'Complaints tracking' },
  { value: '*', label: 'Full Access (All)' },
];

const form = useForm({
  client_name: '',
  rate_limit_per_hour: 1000,
  expires_at: '',
  permissions: ['news', 'demographics'], // standard default
});

// Key masking utility
const isMasked = (id) => {
  return maskedKeys.value[id] !== false; // defaults to true
};

const toggleMask = (id) => {
  maskedKeys.value[id] = !isMasked(id);
};

const maskKey = (key) => {
  if (!key) return '';
  return key.substring(0, 8) + '••••••••••••••••••••••••••••' + key.substring(key.length - 4);
};

const copyKey = (key) => {
  navigator.clipboard.writeText(key);
  alert('API Key berhasil disalin ke clipboard!');
};

// Actions
const openCreateModal = () => {
  form.reset();
  form.clearErrors();
  createModalOpen.value = true;
};

const submit = () => {
  form.post(route('admin.api-keys.store'), {
    onSuccess: () => {
      createModalOpen.value = false;
    }
  });
};

const regenerateKey = (key) => {
  if (confirm(`Apakah Anda yakin ingin meregenerasi token untuk client "${key.client_name}"? Token lama akan segera mati.`)) {
    router.post(route('admin.api-keys.regenerate', key.id));
  }
};

const toggleStatus = (key) => {
  router.post(route('admin.api-keys.toggle', key.id));
};

const deleteKey = (key) => {
  if (confirm(`Apakah Anda yakin ingin menghapus API Key untuk client "${key.client_name}"?`)) {
    router.delete(route('admin.api-keys.destroy', key.id));
  }
};
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
