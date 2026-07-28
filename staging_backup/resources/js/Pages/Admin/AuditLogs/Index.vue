<template>
  <Head title="Audit Logs" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Title section -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Audit Logs & Activity Timeline</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Pantau seluruh aktivitas login, logout, pembuatan konten, upload data, dan modifikasi tema oleh operator.
        </p>
      </div>

      <!-- Filters panel -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="flex flex-1 flex-col sm:flex-row gap-3 w-full">
          <!-- Search input -->
          <div class="relative flex-1">
            <input 
              type="text" 
              v-model="searchQuery" 
              @input="handleSearch"
              placeholder="Cari deskripsi atau operator..."
              class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-zinc-800 text-xs rounded-xl border border-gray-200 dark:border-zinc-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition"
            />
            <Search class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" />
          </div>

          <!-- Event filter -->
          <div class="w-full sm:w-48">
            <select 
              v-model="selectedEvent" 
              @change="handleSearch"
              class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Semua Event</option>
              <option v-for="evt in events" :key="evt" :value="evt">
                {{ formatEventName(evt) }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Logs Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Waktu</th>
                <th class="px-6 py-4">Operator</th>
                <th class="px-6 py-4">Aktivitas / Deskripsi</th>
                <th class="px-6 py-4">Event</th>
                <th class="px-6 py-4 text-right">Detail</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr v-if="logs.data.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                  Tidak ada data audit log ditemukan.
                </td>
              </tr>
              <tr 
                v-for="log in logs.data" 
                :key="log.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition duration-150"
              >
                <!-- Timestamp -->
                <td class="px-6 py-4 text-gray-400 dark:text-zinc-500 font-medium whitespace-nowrap">
                  {{ log.date }}
                </td>

                <!-- Operator -->
                <td class="px-6 py-4 font-bold text-gray-800 dark:text-zinc-200">
                  {{ log.operator }}
                </td>

                <!-- Description -->
                <td class="px-6 py-4 text-gray-700 dark:text-zinc-300 font-medium max-w-sm truncate" :title="log.description">
                  {{ log.description }}
                </td>

                <!-- Event Badge -->
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-0.5 rounded-md font-semibold text-[9px] uppercase tracking-wide"
                    :class="getEventBadgeClass(log.event)"
                  >
                    {{ log.event }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <button 
                    @click="openDetails(log)"
                    class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 rounded-lg transition"
                    title="Buka Detail"
                  >
                    <Info class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-50 dark:border-zinc-800/60 flex items-center justify-between">
          <span class="text-[10px] text-gray-400 font-semibold">
            Menampilkan {{ logs.from || 0 }} sampai {{ logs.to || 0 }} dari {{ logs.total }} log
          </span>
          <div class="flex gap-1.5">
            <Link 
              v-for="(link, lIdx) in logs.links" 
              :key="lIdx"
              :href="link.url || '#'"
              class="px-3 py-1.5 border rounded-lg text-[10px] font-bold transition"
              :class="[
                link.active 
                  ? 'bg-primary-600 border-primary-600 text-white shadow-sm' 
                  : 'bg-white dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 text-gray-500 hover:text-gray-700 dark:hover:text-zinc-200',
                !link.url ? 'opacity-40 pointer-events-none' : ''
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>

      <!-- Detail Modal -->
      <transition name="fade">
        <div v-if="detailsModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Metadata Audit Log</h3>

            <div class="space-y-4 text-xs">
              <!-- Basic Info -->
              <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-zinc-950 p-4 rounded-2xl border border-gray-100 dark:border-zinc-800/80">
                <div>
                  <h4 class="text-[10px] font-bold text-gray-400 uppercase">Operator</h4>
                  <p class="font-bold text-gray-800 dark:text-zinc-200 mt-0.5">{{ selectedLog.operator }}</p>
                </div>
                <div>
                  <h4 class="text-[10px] font-bold text-gray-400 uppercase">Waktu</h4>
                  <p class="font-semibold text-gray-700 dark:text-zinc-300 mt-0.5">{{ selectedLog.date }}</p>
                </div>
                <div>
                  <h4 class="text-[10px] font-bold text-gray-400 uppercase">Aksi</h4>
                  <p class="font-bold text-gray-800 dark:text-zinc-200 mt-0.5">{{ selectedLog.description }}</p>
                </div>
                <div>
                  <h4 class="text-[10px] font-bold text-gray-400 uppercase">Event</h4>
                  <p class="font-bold text-gray-800 dark:text-zinc-200 mt-0.5 uppercase">{{ selectedLog.event }}</p>
                </div>
              </div>

              <!-- Metadata properties -->
              <div>
                <h4 class="text-[10px] font-bold text-gray-400 uppercase mb-1.5">Parameter / Properties</h4>
                <div class="bg-gray-50 dark:bg-zinc-950 p-4 border border-gray-100 dark:border-zinc-800/80 rounded-2xl overflow-x-auto">
                  <pre class="font-mono text-[10px] text-gray-600 dark:text-zinc-400 leading-relaxed">{{ formatJSON(selectedLog.properties) }}</pre>
                </div>
              </div>
            </div>

            <!-- Footer modal buttons -->
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-6">
              <button 
                type="button" 
                @click="detailsModalOpen = false" 
                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
              >
                Tutup
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Search, Info } from '@lucide/vue';

const props = defineProps({
  logs: Object,
  events: Array,
  filters: Object,
});

const searchQuery = ref(props.filters.search || '');
const selectedEvent = ref(props.filters.event || '');
const detailsModalOpen = ref(false);
const selectedLog = ref(null);

let searchTimeout = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('admin.audit-logs.index'), {
      search: searchQuery.value,
      event: selectedEvent.value,
    }, { preserveState: true, replace: true });
  }, 350);
};

const formatEventName = (event) => {
  if (!event) return '';
  return event.replace(/_/g, ' ').toUpperCase();
};

const getEventBadgeClass = (event) => {
  switch (event) {
    case 'login':
    case 'login_failed':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30';
    case 'logout':
      return 'bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700';
    case 'create_user':
    case 'create_folder':
    case 'upload_file':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30';
    case 'update_user':
    case 'update_theme':
    case 'upload_version':
      return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30';
    case 'delete_user':
    case 'delete_folder':
    case 'delete_file':
      return 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-100 dark:border-red-900/30';
    default:
      return 'bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700';
  }
};

const openDetails = (log) => {
  selectedLog.value = log;
  detailsModalOpen.value = true;
};

const formatJSON = (json) => {
  if (!json) return '{}';
  return JSON.stringify(json, null, 2);
};
</script>
