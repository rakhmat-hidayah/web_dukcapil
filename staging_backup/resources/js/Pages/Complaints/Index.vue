<template>
  <Head title="Pengaduan & Aspirasi Masuk" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Daftar Pengaduan Masuk</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola tiket aduan pelayanan kependudukan dari masyarakat.</p>
        </div>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold">
        ✓ {{ $page.props.flash.success }}
      </div>

      <!-- Status statistics banner -->
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div 
          v-for="(label, key) in statusLabels" 
          :key="key"
          @click="filterStatus(key)"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 p-4 rounded-2xl shadow-sm flex flex-col justify-between cursor-pointer hover:border-primary-500 transition"
          :class="[filters.status === key ? 'border-primary-500 ring-2 ring-primary-500/20' : '']"
        >
          <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400">{{ label }}</span>
          <span 
            class="text-2xl font-black mt-2 font-mono"
            :class="[
              key === 'pending' ? 'text-amber-500' : '',
              key === 'in_review' ? 'text-blue-500' : '',
              key === 'in_progress' ? 'text-indigo-500' : '',
              key === 'resolved' ? 'text-emerald-500' : '',
              key === 'rejected' ? 'text-red-500' : '',
            ]"
          >
            {{ statusCounts[key] || 0 }}
          </span>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center flex-1">
          <!-- Search box -->
          <div class="relative w-full max-w-xs">
            <Search class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" />
            <input 
              type="text" 
              v-model="search" 
              placeholder="Cari nomor tiket, subjek..." 
              class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              @keyup.enter="applyFilters"
            />
          </div>

          <!-- Category filter -->
          <select 
            v-model="selectedCategory" 
            class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            @change="applyFilters"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <button 
            @click="resetFilters"
            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 text-xs font-bold rounded-xl transition"
          >
            Reset
          </button>
          <button 
            @click="applyFilters"
            class="px-4 py-2 bg-gray-900 dark:bg-zinc-800 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition"
          >
            Saring
          </button>
        </div>
      </div>

      <!-- Complaints list table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800">
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Nomor Tiket</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Pelapor</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Subjek Pengaduan</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Kategori</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Tanggal Masuk</th>
                <th class="px-5 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
              <tr v-if="complaints.data.length === 0">
                <td colspan="7" class="px-5 py-8 text-center text-gray-400 italic">Belum ada pengaduan masuk.</td>
              </tr>
              <tr 
                v-for="item in complaints.data" 
                :key="item.id" 
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition cursor-pointer"
                @click="viewDetail(item.id)"
              >
                <!-- Ticket -->
                <td class="px-5 py-4 font-mono font-bold text-primary-600 select-all">
                  {{ item.ticket_number }}
                </td>
                
                <!-- Submitter -->
                <td class="px-5 py-4">
                  <div class="flex flex-col">
                    <span class="font-bold text-gray-700 dark:text-zinc-200">
                      {{ item.is_anonymous ? 'Anonim' : item.submitter_name }}
                    </span>
                    <span class="text-[10px] text-gray-400 mt-0.5" v-if="!item.is_anonymous">
                      {{ item.submitter_phone || 'No WA —' }}
                    </span>
                  </div>
                </td>

                <!-- Subject -->
                <td class="px-5 py-4 font-semibold text-gray-800 dark:text-zinc-100 max-w-xs truncate">
                  {{ item.subject }}
                </td>

                <!-- Category -->
                <td class="px-5 py-4">
                  <span 
                    v-if="item.category" 
                    class="text-[9px] font-bold font-mono uppercase tracking-wider border px-1.5 py-0.5 rounded"
                    :style="{ 
                      color: item.category.color, 
                      backgroundColor: item.category.color + '15',
                      borderColor: item.category.color + '30'
                    }"
                  >
                    {{ item.category.name }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>

                <!-- Status -->
                <td class="px-5 py-4">
                  <span 
                    class="px-2 py-0.5 rounded text-[9px] font-bold font-mono uppercase tracking-wide"
                    :class="[
                      item.status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300' : '',
                      item.status === 'in_review' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : '',
                      item.status === 'in_progress' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300' : '',
                      item.status === 'resolved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : '',
                      item.status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' : '',
                    ]"
                  >
                    {{ statusLabels[item.status] }}
                  </span>
                </td>

                <!-- Date -->
                <td class="px-5 py-4 text-gray-400 font-mono">
                  {{ formatDate(item.created_at) }}
                </td>

                <!-- Detail action -->
                <td class="px-5 py-4 text-right" @click.stop>
                  <Link 
                    :href="route('admin.complaints.show', item.id)"
                    class="text-xs text-primary-600 hover:underline font-bold"
                  >
                    Proses →
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="complaints.links && complaints.links.length > 3" class="px-5 py-3 border-t border-gray-50 dark:border-zinc-800 flex gap-1">
          <Link 
            v-for="(link, i) in complaints.links" 
            :key="i"
            :href="link.url || '#'"
            v-html="link.label"
            :disabled="!link.url"
            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition"
            :class="[
              link.active ? 'bg-primary-600 text-white border-primary-500 shadow-sm' : 'border-gray-200 dark:border-zinc-700 hover:bg-gray-50 text-gray-500',
              !link.url ? 'opacity-40 pointer-events-none' : ''
            ]"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Search } from '@lucide/vue';

const props = defineProps({
  complaints: Object,
  categories: Array,
  statusCounts: Object,
  statusLabels: Object,
  statusColors: Object,
  filters: Object,
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const activeStatus = ref(props.filters.status || '');

const applyFilters = () => {
  router.get(route('admin.complaints.index'), {
    search: search.value,
    category: selectedCategory.value,
    status: activeStatus.value,
  }, {
    preserveState: true,
  });
};

const filterStatus = (status) => {
  activeStatus.value = activeStatus.value === status ? '' : status;
  applyFilters();
};

const resetFilters = () => {
  search.value = '';
  selectedCategory.value = '';
  activeStatus.value = '';
  applyFilters();
};

const viewDetail = (id) => {
  router.get(route('admin.complaints.show', id));
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>
