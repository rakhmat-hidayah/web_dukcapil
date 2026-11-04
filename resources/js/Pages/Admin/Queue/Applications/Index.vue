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

      <!-- Operator Special Reminders Banner -->
      <div
        v-if="(urgencyCounts?.overdue || 0) > 0 || (urgencyCounts?.new || 0) > 0"
        class="grid grid-cols-1 md:grid-cols-2 gap-4"
      >
        <!-- Overdue > 24 Hours Alert -->
        <div
          v-if="(urgencyCounts?.overdue || 0) > 0"
          class="p-4 rounded-2xl bg-gradient-to-r from-rose-50 to-rose-100/80 dark:from-rose-950/40 dark:to-rose-900/20 border-2 border-rose-300 dark:border-rose-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition"
        >
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/30">
              <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h4 class="text-xs sm:text-sm font-black text-rose-900 dark:text-rose-200">
                  {{ urgencyCounts.overdue }} Permohonan Belum Diverifikasi >1x24 Jam!
                </h4>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white animate-pulse">
                  Prioritas Utama
                </span>
              </div>
              <p class="text-[11px] text-rose-700 dark:text-rose-300/90 mt-0.5">
                Melewati batas waktu standar pelayanan (SLA 24 jam). Harap segera selesaikan verifikasi berkas.
              </p>
            </div>
          </div>
          <button
            @click="selectUrgency('overdue')"
            class="px-3.5 py-2 rounded-xl text-xs font-black bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20 transition whitespace-nowrap self-start sm:self-auto cursor-pointer flex items-center gap-1.5"
          >
            <span>Verifikasi Sekarang ({{ urgencyCounts.overdue }})</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>

        <!-- New Incoming Unverified Alert -->
        <div
          v-if="(urgencyCounts?.new || 0) > 0"
          class="p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-amber-100/80 dark:from-amber-950/40 dark:to-amber-900/20 border-2 border-amber-300 dark:border-amber-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition"
        >
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/30">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h4 class="text-xs sm:text-sm font-black text-amber-900 dark:text-amber-200">
                  {{ urgencyCounts.new }} Permohonan Baru Masuk
                </h4>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white">
                  Segera Diverifikasi
                </span>
              </div>
              <p class="text-[11px] text-amber-800 dark:text-amber-300/90 mt-0.5">
                Dokumen digital baru diunggah oleh pemohon dan menunggu pra-verifikasi operator.
              </p>
            </div>
          </div>
          <button
            @click="selectUrgency('new')"
            class="px-3.5 py-2 rounded-xl text-xs font-black bg-amber-500 hover:bg-amber-400 text-white shadow-md shadow-amber-500/20 transition whitespace-nowrap self-start sm:self-auto cursor-pointer flex items-center gap-1.5"
          >
            <span>Tinjau Berkas ({{ urgencyCounts.new }})</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>

      <!-- Filter Tabs by Status & Urgency -->
      <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
        <!-- All Status Tab -->
        <button
          @click="selectStatus('')"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            activeStatus === '' && !activeUrgency
              ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
              : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700'
          ]"
        >
          <span>Semua Status</span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono',
              activeStatus === '' && !activeUrgency ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
            ]"
          >
            {{ statusCounts?.all || 0 }}
          </span>
        </button>

        <!-- Overdue >24h Tab -->
        <button
          @click="selectUrgency('overdue')"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            activeUrgency === 'overdue'
              ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20 ring-2 ring-rose-300 dark:ring-rose-800'
              : 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50'
          ]"
        >
          <span class="flex items-center gap-1.5">
            <span v-if="(urgencyCounts?.overdue || 0) > 0" class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            <span>⚠️ Belum Diverifikasi >24 Jam</span>
          </span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold',
              activeUrgency === 'overdue' ? 'bg-rose-500 text-white' : 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300'
            ]"
          >
            {{ urgencyCounts?.overdue || 0 }}
          </span>
        </button>

        <!-- New Incoming Tab -->
        <button
          @click="selectUrgency('new')"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            activeUrgency === 'new'
              ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20 ring-2 ring-amber-300 dark:ring-amber-800'
              : 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50'
          ]"
        >
          <span class="flex items-center gap-1.5">
            <span v-if="(urgencyCounts?.new || 0) > 0" class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span>⚡ Baru Masuk (&lt;24 Jam)</span>
          </span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold',
              activeUrgency === 'new' ? 'bg-amber-400 text-white' : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300'
            ]"
          >
            {{ urgencyCounts?.new || 0 }}
          </span>
        </button>

        <!-- Standard Status Tabs -->
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="selectStatus(tab.value)"
          :class="[
            'px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 cursor-pointer',
            activeStatus === tab.value && !activeUrgency
              ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
              : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700'
          ]"
        >
          <span>{{ tab.label }}</span>
          <span
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-mono',
              activeStatus === tab.value && !activeUrgency ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
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
            v-if="searchQuery || selectedService || activeStatus || activeUrgency"
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
                :class="[
                  'transition group border-b border-slate-100 dark:border-slate-700/60',
                  app.verification_urgency === 'overdue'
                    ? 'bg-rose-50/70 dark:bg-rose-950/25 border-l-4 border-l-rose-500 hover:bg-rose-100/60 dark:hover:bg-rose-900/40'
                    : (app.verification_urgency === 'new'
                      ? 'bg-amber-50/50 dark:bg-amber-950/15 border-l-4 border-l-amber-500 hover:bg-amber-100/50 dark:hover:bg-amber-900/30'
                      : 'hover:bg-slate-50/80 dark:hover:bg-slate-700/30')
                ]"
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

                  <!-- Urgent Reminder Badges -->
                  <div v-if="app.verification_urgency === 'overdue'" class="mt-1.5 flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white shadow-sm animate-pulse">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                      Belum Diverifikasi >24 Jam
                    </span>
                    <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400">
                      ({{ app.waiting_time_human }})
                    </span>
                  </div>
                  <div v-else-if="app.verification_urgency === 'new'" class="mt-1.5 flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-sm">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                      Baru Masuk
                    </span>
                    <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400">
                      ({{ app.waiting_time_human }})
                    </span>
                  </div>
                </td>

                <!-- Applicant & NIK -->
                <td class="px-5 py-4">
                  <div class="font-bold text-slate-900 dark:text-white">
                    {{ app.applicant_name }}
                  </div>
                  <div class="font-mono text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                    <span>NIK: {{ maskNik(app.applicant_nik) }}</span>
                    <span>·</span>
                    <span>{{ formatPhoneChunked(app.applicant_phone) }}</span>
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
                  <div v-if="app.verification_urgency === 'overdue'" class="mt-1">
                    <span class="inline-block text-[10px] font-extrabold text-rose-600 dark:text-rose-400 bg-rose-100 dark:bg-rose-950/80 px-2 py-0.5 rounded-full">
                      ⚠️ Terlambat SLA 1x24 Jam
                    </span>
                  </div>
                  <div v-else-if="app.verification_urgency === 'new'" class="mt-1">
                    <span class="inline-block text-[10px] font-extrabold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/80 px-2 py-0.5 rounded-full">
                      ⚡ Butuh Verifikasi Segera
                    </span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-5 py-4 text-right whitespace-nowrap">
                  <Link
                    v-if="app.verification_urgency === 'overdue'"
                    :href="route('admin.queue.applications.show', app.id)"
                    class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md shadow-rose-600/30 transition animate-pulse"
                  >
                    <span>Verifikasi Sekarang ⚠️</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </Link>
                  <Link
                    v-else-if="app.verification_urgency === 'new'"
                    :href="route('admin.queue.applications.show', app.id)"
                    class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-white font-bold text-xs shadow-md shadow-amber-500/30 transition"
                  >
                    <span>Tinjau Berkas ⚡</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </Link>
                  <Link
                    v-else
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
  urgencyCounts: Object,
  services: Array,
  filters: Object,
})

const activeStatus = ref(props.filters?.status || '')
const activeUrgency = ref(props.filters?.urgency || '')
const searchQuery = ref(props.filters?.search || '')
const selectedService = ref(props.filters?.service_id || '')

const statusTabs = computed(() => [
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
  activeUrgency.value = ''
  applyFilters()
}

const selectUrgency = (urgency) => {
  activeUrgency.value = urgency
  activeStatus.value = ''
  applyFilters()
}

const applyFilters = () => {
  router.get(
    route('admin.queue.applications.index'),
    {
      status: activeStatus.value || undefined,
      urgency: activeUrgency.value || undefined,
      search: searchQuery.value || undefined,
      service_id: selectedService.value || undefined,
    },
    { preserveState: true, replace: true }
  )
}

const resetFilters = () => {
  activeStatus.value = ''
  activeUrgency.value = ''
  searchQuery.value = ''
  selectedService.value = ''
  router.get(route('admin.queue.applications.index'), {}, { preserveState: true, replace: true })
}

const formatPhoneChunked = (phone) => {
  if (!phone) return '-'
  let digits = String(phone).replace(/\D/g, '')
  if (digits.startsWith('62')) {
    digits = '0' + digits.slice(2)
  }
  digits = digits.slice(0, 13)
  if (!digits) return String(phone)
  const chunks = digits.match(/.{1,4}/g)
  return chunks ? chunks.join('-') : digits
}

const maskNik = (nik) => {
  if (!nik || nik.length < 8) return nik
  return nik.substring(0, 6) + '******' + nik.substring(12)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    const d = new Date(dateString)
    if (isNaN(d.getTime())) return String(dateString)
    return d.toLocaleString('id-ID', {
      timeZone: 'Asia/Makassar',
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }) + ' WITA'
  } catch (e) {
    return String(dateString)
  }
}

const MONTH_NAMES_SHORT_ID = [
  'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
  'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
]
const DAY_NAMES_SHORT_ID = [
  'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'
]

const formatScheduleDate = (dateString) => {
  if (!dateString) return '-'
  try {
    const clean = typeof dateString === 'string' ? dateString.split('T')[0].trim() : String(dateString)
    if (/^\d{4}-\d{2}-\d{2}$/.test(clean)) {
      const [y, m, day] = clean.split('-').map(Number)
      const d = new Date(y, m - 1, day)
      const dayName = DAY_NAMES_SHORT_ID[d.getDay()]
      const dayNum = d.getDate()
      const monthName = MONTH_NAMES_SHORT_ID[d.getMonth()]
      const year = d.getFullYear()
      return `${dayName}, ${dayNum} ${monthName} ${year}`
    }
    const fallback = new Date(dateString)
    if (!isNaN(fallback.getTime())) {
      const dayName = DAY_NAMES_SHORT_ID[fallback.getDay()]
      const dayNum = fallback.getDate()
      const monthName = MONTH_NAMES_SHORT_ID[fallback.getMonth()]
      const year = fallback.getFullYear()
      return `${dayName}, ${dayNum} ${monthName} ${year}`
    }
    return String(dateString)
  } catch (e) {
    return String(dateString)
  }
}
</script>
