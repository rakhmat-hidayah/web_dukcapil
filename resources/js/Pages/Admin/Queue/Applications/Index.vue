<template>
  <Head title="Daftar Pengajuan Antrian & Berkas - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Pengajuan Antrian & Verifikasi Berkas
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Daftar seluruh pendaftaran KTP-el online, pemeriksaan persyaratan dokumen, dan status pemanggilan.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('admin.queue.dashboard')"
            class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 0118 0z"/></svg>
            Kembali ke Dashboard
          </Link>
        </div>
      </div>

      <!-- Filter Tabs by Status -->
      <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="selectStatus(tab.value)"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            activeStatus === tab.value
              ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
              : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700'
          ]"
        >
          <span>{{ tab.label }}</span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono',
              activeStatus === tab.value ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
            ]"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
        <div class="flex flex-1 flex-col sm:flex-row gap-3">
          <!-- Search Input -->
          <div class="relative flex-1">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input
              v-model="searchQuery"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Cari No. Registrasi, NIK, Nama, No. HP, No. Antrian..."
              class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Service Filter -->
          <select
            v-model="selectedService"
            @change="applyFilters"
            class="px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Jenis Layanan</option>
            <option v-for="srv in services" :key="srv.id" :value="srv.id">
              {{ srv.name }}
            </option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="applyFilters"
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition"
          >
            Terapkan Filter
          </button>
          <button
            v-if="searchQuery || selectedService || activeStatus"
            @click="resetFilters"
            class="px-3 py-2.5 text-xs text-slate-500 hover:text-rose-600 font-bold transition"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Application Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-5 py-4">No. Registrasi / Antrian</th>
                <th class="px-5 py-4">Pemohon & NIK</th>
                <th class="px-5 py-4">Jenis Layanan</th>
                <th class="px-5 py-4">Jadwal Pelayanan</th>
                <th class="px-5 py-4 text-center">Status</th>
                <th class="px-5 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium text-slate-700 dark:text-slate-200">
              <tr
                v-for="app in applications.data"
                :key="app.id"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition group"
              >
                <!-- Reg & Queue Number -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="font-mono font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ app.reg_number }}</span>
                    <span
                      v-if="app.queue_number"
                      class="px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 text-[11px] font-black font-mono border border-emerald-300 dark:border-emerald-800"
                    >
                      {{ app.queue_number }}
                    </span>
                  </div>
                  <p class="text-[10px] text-slate-400 mt-0.5">
                    Didaftarkan: {{ formatDate(app.submitted_at || app.created_at) }}
                  </p>
                </td>

                <!-- Applicant & NIK -->
                <td class="px-5 py-4">
                  <div class="font-bold text-slate-900 dark:text-white">
                    {{ app.applicant_name }}
                  </div>
                  <div class="font-mono text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                    <span>NIK: {{ maskNik(app.applicant_nik) }}</span>
                    <span>·</span>
                    <span>{{ app.applicant_phone }}</span>
                  </div>
                </td>

                <!-- Service Type -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                    {{ app.service_type?.name || 'Pelayanan KTP-el' }}
                  </span>
                </td>

                <!-- Schedule Date -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <div v-if="app.slot?.schedule?.schedule_date">
                    <p class="font-bold text-slate-900 dark:text-white">
                      {{ formatScheduleDate(app.slot.schedule.schedule_date) }}
                    </p>
                    <p class="text-[10px] text-slate-400">
                      {{ app.slot.schedule.open_time?.substring(0, 5) }} - {{ app.slot.schedule.close_time?.substring(0, 5) }} WITA
                    </p>
                  </div>
                  <span v-else class="text-slate-400 italic text-[11px]">
                    Belum dijadwalkan
                  </span>
                </td>

                <!-- Status Badge -->
                <td class="px-5 py-4 text-center whitespace-nowrap">
                  <StatusBadge :status="app.status" />
                </td>

                <!-- Actions -->
                <td class="px-5 py-4 text-right whitespace-nowrap">
                  <Link
                    :href="route('admin.queue.applications.show', app.id)"
                    class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition"
                  >
                    <span>Periksa & Verifikasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </Link>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="!applications.data || applications.data.length === 0">
                <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                  <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </div>
                  <p class="font-bold text-slate-600 dark:text-slate-300">Tidak ada pengajuan ditemukan</p>
                  <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="applications.links && applications.links.length > 3"
          class="px-5 py-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between"
        >
          <div class="text-xs text-slate-500">
            Menampilkan {{ applications.from || 0 }} - {{ applications.to || 0 }} dari total {{ applications.total }} pendaftaran
          </div>
          <div class="flex items-center gap-1">
            <Link
              v-for="(link, i) in applications.links"
              :key="i"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                link.active
                  ? 'bg-blue-600 text-white'
                  : link.url
                  ? 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200'
                  : 'text-slate-400 cursor-not-allowed'
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/Queue/StatusBadge.vue'

const props = defineProps({
  applications: Object,
  statusCounts: Object,
  services: Array,
  filters: Object,
})

const activeStatus = ref(props.filters.status || '')
const searchQuery = ref(props.filters.search || '')
const selectedService = ref(props.filters.service_id || '')

const statusTabs = computed(() => [
  { label: 'Semua Status', value: '', count: props.statusCounts?.all || 0 },
  { label: 'Menunggu Verifikasi', value: 'SUBMITTED', count: props.statusCounts?.SUBMITTED || 0 },
  { label: 'Sedang Diperiksa', value: 'UNDER_REVIEW', count: props.statusCounts?.UNDER_REVIEW || 0 },
  { label: 'Perlu Perbaikan', value: 'REVISION_REQUIRED', count: props.statusCounts?.REVISION_REQUIRED || 0 },
  { label: 'Disetujui', value: 'APPROVED', count: props.statusCounts?.APPROVED || 0 },
  { label: 'Sudah Hadir', value: 'CHECKED_IN', count: props.statusCounts?.CHECKED_IN || 0 },
  { label: 'Selesai', value: 'COMPLETED', count: props.statusCounts?.COMPLETED || 0 },
  { label: 'Ditolak', value: 'REJECTED', count: props.statusCounts?.REJECTED || 0 },
])

const selectStatus = (status) => {
  activeStatus.value = status
  applyFilters()
}

const applyFilters = () => {
  router.get(
    route('admin.queue.applications.index'),
    {
      status: activeStatus.value || undefined,
      search: searchQuery.value || undefined,
      service_id: selectedService.value || undefined,
    },
    { preserveState: true, replace: true }
  )
}

const resetFilters = () => {
  activeStatus.value = ''
  searchQuery.value = ''
  selectedService.value = ''
  router.get(route('admin.queue.applications.index'), {}, { preserveState: true, replace: true })
}

const maskNik = (nik) => {
  if (!nik || nik.length < 8) return nik
  return nik.substring(0, 6) + '******' + nik.substring(12)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const d = new Date(dateString)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatScheduleDate = (dateString) => {
  if (!dateString) return '-'
  const d = new Date(dateString + 'T00:00:00')
  return d.toLocaleDateString('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}
</script>
