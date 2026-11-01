<template>
  <Head title="Jadwal Pelayanan & Kuota Antrian - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Jadwal Pelayanan & Kuota Loket
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Atur kuota harian per jenis layanan, jam operasional loket, hari libur, dan jadwal massal.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="openBulkModal"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-1.5 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Buat Jadwal Massal (Bulk)
          </button>
        </div>
      </div>

      <!-- Month Filter Bar -->
      <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Pilih Bulan:</span>
          <input
            type="month"
            v-model="monthFilter"
            @change="changeMonth"
            class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="text-xs text-slate-500">
          Total Terjadwal: <strong class="text-slate-900 dark:text-white font-mono">{{ schedules.length }}</strong> hari di bulan ini
        </div>
      </div>

      <!-- Schedule Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-5 py-4">Tanggal & Hari</th>
                <th class="px-5 py-4">Jam Operasional</th>
                <th class="px-5 py-4">Status Layanan</th>
                <th class="px-5 py-4">Rincian Kuota per Layanan</th>
                <th class="px-5 py-4 text-center">Total Kuota</th>
                <th class="px-5 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
              <tr
                v-for="item in schedules"
                :key="item.id"
                :class="[
                  'transition hover:bg-slate-50 dark:hover:bg-slate-700/30',
                  item.is_holiday ? 'bg-rose-50/40 dark:bg-rose-950/20' : ''
                ]"
              >
                <!-- Date -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="font-bold text-slate-900 dark:text-white text-sm">
                    {{ item.schedule_date_formatted || formatDate(item.schedule_date) }}
                  </div>
                  <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                    {{ item.schedule_date_indonesia || formatRawDate(item.schedule_date) }}
                  </div>
                </td>

                <!-- Hours -->
                <td class="px-5 py-4 whitespace-nowrap font-mono text-[11px] text-slate-600 dark:text-slate-300">
                  <span v-if="!item.is_holiday">
                    {{ item.operational_hours_formatted || (item.open_time?.substring(0, 5) + ' - ' + item.close_time?.substring(0, 5) + ' WITA') }}
                  </span>
                  <span v-else class="text-rose-500 font-bold">
                    TUTUP
                  </span>
                </td>

                <!-- Status / Holiday -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <span
                    v-if="item.is_holiday"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400"
                  >
                    Libur ({{ item.holiday_note || 'Hari Libur' }})
                  </span>
                  <span
                    v-else-if="!item.is_open"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300"
                  >
                    Ditutup Sementara
                  </span>
                  <span
                    v-else
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"
                  >
                    Buka Pelayanan
                  </span>
                </td>

                <!-- Slots per service -->
                <td class="px-5 py-4">
                  <div class="flex flex-wrap gap-1.5 max-w-md">
                    <span
                      v-for="slot in item.slots"
                      :key="slot.id"
                      class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700/80 text-[11px] font-mono text-slate-700 dark:text-slate-200"
                    >
                      <strong>{{ slot.service_type?.name?.substring(0, 10) }}...</strong>:
                      <span class="text-blue-600 dark:text-blue-400 font-bold">{{ slot.booked_count }}/{{ slot.quota }}</span>
                    </span>
                  </div>
                </td>

                <!-- Total Quota -->
                <td class="px-5 py-4 text-center whitespace-nowrap font-mono">
                  <span class="text-sm font-black text-slate-900 dark:text-white">
                    {{ item.slots?.reduce((sum, s) => sum + s.booked_count, 0) || 0 }}
                  </span>
                  <span class="text-slate-400 text-xs"> / {{ item.total_quota }}</span>
                </td>

                <!-- Actions -->
                <td class="px-5 py-4 text-right whitespace-nowrap space-x-1">
                  <button
                    @click="openEditModal(item)"
                    class="px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 font-bold text-xs hover:bg-blue-600 hover:text-white transition"
                  >
                    Ubah Kuota
                  </button>
                  <button
                    @click="deleteSchedule(item)"
                    class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 font-bold text-xs hover:bg-rose-600 hover:text-white transition"
                  >
                    Hapus
                  </button>
                </td>
              </tr>

              <tr v-if="!schedules || schedules.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                  Belum ada jadwal pelayanan untuk bulan ini. Klik <strong>Buat Jadwal Massal</strong> untuk membuat hari kerja secara otomatis.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- EDIT SINGLE DATE MODAL -->
    <div v-if="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
          Ubah Jadwal & Kuota — {{ formatDate(editForm.schedule_date) }}
        </h3>

        <form @submit.prevent="saveSingleSchedule" class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jam Buka *</label>
              <input
                v-model="editForm.open_time"
                type="time"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jam Tutup *</label>
              <input
                v-model="editForm.close_time"
                type="time"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white"
              />
            </div>
          </div>

          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                v-model="editForm.is_holiday"
                class="rounded text-rose-600 focus:ring-rose-500"
              />
              <span class="text-xs font-bold text-rose-600">Tandai Sebagai Hari Libur (Loket Tutup)</span>
            </label>

            <div v-if="editForm.is_holiday">
              <input
                v-model="editForm.holiday_note"
                type="text"
                placeholder="Keterangan hari libur (misal: Cuti Bersama)"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white"
              />
            </div>
          </div>

          <!-- Slots Quota Inputs per Service -->
          <div v-if="!editForm.is_holiday" class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Pengaturan Kuota per Layanan:
            </label>
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
              <div
                v-for="(slot, idx) in editForm.slots"
                :key="slot.service_type_id"
                class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs"
              >
                <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[200px]">
                  {{ getServiceName(slot.service_type_id) }}
                </span>
                <div class="flex items-center gap-1.5">
                  <span class="text-slate-400 text-[10px]">Kuota:</span>
                  <input
                    type="number"
                    min="0"
                    max="500"
                    v-model.number="slot.quota"
                    class="w-20 px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-center font-bold text-xs"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700/60">
            <button
              type="button"
              @click="editModalOpen = false"
              class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="editSubmitting"
              class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-blue-600/20"
            >
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- BULK GENERATE MODAL -->
    <div v-if="bulkModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <h3 class="text-lg font-black text-slate-900 dark:text-white">
          Buat Jadwal Massal (Bulk Generate)
        </h3>
        <p class="text-xs text-slate-500">
          Buat jadwal operasional dan kuota loket secara otomatis untuk rentang tanggal tertentu berdasarkan kuota default setiap layanan.
        </p>

        <form @submit.prevent="submitBulk" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai *</label>
              <input
                v-model="bulkForm.start_date"
                type="date"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai *</label>
              <input
                v-model="bulkForm.end_date"
                type="date"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jam Buka *</label>
              <input
                v-model="bulkForm.open_time"
                type="time"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jam Tutup *</label>
              <input
                v-model="bulkForm.close_time"
                type="time"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white"
              />
            </div>
          </div>

          <div class="pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                v-model="bulkForm.skip_weekends"
                class="rounded text-blue-600 focus:ring-blue-500"
              />
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                Lewati Hari Sabtu & Minggu (Hanya Hari Kerja)
              </span>
            </label>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700/60">
            <button
              type="button"
              @click="bulkModalOpen = false"
              class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="bulkSubmitting"
              class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-blue-600/20"
            >
              <span v-if="bulkSubmitting">Memproses...</span>
              <span v-else>Jadwalkan Massal</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  schedules: Array,
  services: Array,
  currentMonth: String,
})

const monthFilter = ref(props.currentMonth || '')

const changeMonth = () => {
  router.get(route('admin.queue.schedules.index'), { month: monthFilter.value }, { preserveState: true })
}

// Edit Single Schedule Modal
const editModalOpen = ref(false)
const editSubmitting = ref(false)
const editForm = ref({
  schedule_date: '',
  open_time: '08:00',
  close_time: '15:00',
  is_holiday: false,
  holiday_note: '',
  is_open: true,
  slots: [],
})

const openEditModal = (schedule) => {
  let cleanDate = schedule.schedule_date || ''
  if (typeof cleanDate === 'string' && cleanDate.includes('T')) {
    cleanDate = cleanDate.split('T')[0]
  }
  editForm.value = {
    schedule_date: cleanDate,
    open_time: schedule.open_time?.substring(0, 5) || '08:00',
    close_time: schedule.close_time?.substring(0, 5) || '15:30',
    is_holiday: !!schedule.is_holiday,
    holiday_note: schedule.holiday_note || '',
    is_open: !!schedule.is_open,
    slots: props.services.map(srv => {
      const existing = schedule.slots?.find(s => s.service_type_id === srv.id)
      return {
        service_type_id: srv.id,
        quota: existing ? existing.quota : srv.quota_per_day,
      }
    }),
  }
  editModalOpen.value = true
}

const saveSingleSchedule = () => {
  editSubmitting.value = true
  router.post(route('admin.queue.schedules.store'), editForm.value, {
    onFinish: () => {
      editSubmitting.value = false
      editModalOpen.value = false
    },
  })
}

const deleteSchedule = (schedule) => {
  const label = schedule.schedule_date_formatted || formatDate(schedule.schedule_date)
  if (confirm(`Hapus jadwal untuk tanggal ${label}?`)) {
    router.delete(route('admin.queue.schedules.destroy', schedule.id))
  }
}

// Helper: Format Date object to YYYY-MM-DD in local browser time (prevents UTC day shifts)
const toLocalDateInputString = (d) => {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

// Bulk Generate Modal
const bulkModalOpen = ref(false)
const bulkSubmitting = ref(false)
const bulkForm = ref({
  start_date: '',
  end_date: '',
  open_time: '08:00',
  close_time: '15:30',
  skip_weekends: true,
})

const openBulkModal = () => {
  const today = new Date()
  const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0)
  bulkForm.value.start_date = toLocalDateInputString(today)
  bulkForm.value.end_date = toLocalDateInputString(endOfMonth)
  bulkForm.value.open_time = '08:00'
  bulkForm.value.close_time = '15:30'
  bulkForm.value.skip_weekends = true
  bulkModalOpen.value = true
}

const submitBulk = () => {
  bulkSubmitting.value = true
  router.post(route('admin.queue.schedules.bulk'), bulkForm.value, {
    onFinish: () => {
      bulkSubmitting.value = false
      bulkModalOpen.value = false
    },
  })
}

const getServiceName = (serviceTypeId) => {
  return props.services.find(s => s.id === serviceTypeId)?.name || 'Layanan'
}

const MONTH_NAMES_ID = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]
const DAY_NAMES_ID = [
  'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
]

const parseDateSafe = (dateString) => {
  if (!dateString) return null
  if (typeof dateString === 'string' && dateString.includes('T')) {
    const cleanDate = dateString.split('T')[0]
    const [y, m, day] = cleanDate.split('-').map(Number)
    return new Date(y, m - 1, day)
  }
  const clean = typeof dateString === 'string' ? dateString.split(' ')[0].trim() : String(dateString)
  if (/^\d{4}-\d{2}-\d{2}$/.test(clean)) {
    const [y, m, day] = clean.split('-').map(Number)
    return new Date(y, m - 1, day)
  }
  if (/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/.test(clean)) {
    const parts = clean.split(/[\/\-]/).map(Number)
    return new Date(parts[2], parts[1] - 1, parts[0])
  }
  const fallback = new Date(dateString)
  return isNaN(fallback.getTime()) ? null : fallback
}

// "Senin, 02 November 2026"
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const d = parseDateSafe(dateString)
  if (!d) return String(dateString)
  const dayName = DAY_NAMES_ID[d.getDay()]
  const dayNum = String(d.getDate()).padStart(2, '0')
  const monthName = MONTH_NAMES_ID[d.getMonth()]
  const year = d.getFullYear()
  return `${dayName}, ${dayNum} ${monthName} ${year}`
}

// "02/11/2026" (Indonesian date format)
const formatRawDate = (dateString) => {
  if (!dateString) return '-'
  const d = parseDateSafe(dateString)
  if (!d) return String(dateString).substring(0, 10)
  const dayNum = String(d.getDate()).padStart(2, '0')
  const monthNum = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  return `${dayNum}/${monthNum}/${year}`
}
</script>
