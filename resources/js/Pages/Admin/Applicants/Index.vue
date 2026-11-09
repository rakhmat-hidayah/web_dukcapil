<template>
  <Head title="Verifikasi Pendaftar Baru & Akun Pemohon - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Verifikasi Pendaftar Baru & Akun Pemohon
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Pemeriksaan dan verifikasi identitas pendaftar baru KTP-el, validasi NIK resmi, pemantauan perangkat aktif (One Account One Browser), serta pelepasan sesi.
          </p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div 
          @click="filterByTab('', '')"
          class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs cursor-pointer hover:border-blue-400 transition"
          :class="{ 'ring-2 ring-blue-500': !accountStatus && !identityStatus }"
        >
          <p class="text-xs text-slate-500 font-bold">Total Pendaftar</p>
          <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ stats.total }}</p>
        </div>

        <div 
          @click="filterByTab('', 'PENDING')"
          class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 shadow-xs cursor-pointer hover:border-amber-400 transition"
          :class="{ 'ring-2 ring-amber-500': identityStatus === 'PENDING' }"
        >
          <div class="flex items-center justify-between">
            <p class="text-xs text-amber-700 dark:text-amber-300 font-bold">Menunggu Verifikasi</p>
            <span v-if="stats.identity_pending > 0" class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
          </div>
          <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ stats.identity_pending }}</p>
        </div>

        <div 
          @click="filterByTab('ACTIVE', 'VERIFIED')"
          class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs cursor-pointer hover:border-emerald-400 transition"
          :class="{ 'ring-2 ring-emerald-500': accountStatus === 'ACTIVE' && identityStatus === 'VERIFIED' }"
        >
          <p class="text-xs text-emerald-600 font-bold">Terverifikasi & Aktif</p>
          <p class="text-2xl font-black text-emerald-600 mt-1">{{ stats.active }}</p>
        </div>

        <div 
          @click="filterByTab('SUSPENDED', '')"
          class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs cursor-pointer hover:border-rose-400 transition"
          :class="{ 'ring-2 ring-rose-500': accountStatus === 'SUSPENDED' }"
        >
          <p class="text-xs text-rose-600 font-bold">Ditangguhkan</p>
          <p class="text-2xl font-black text-rose-600 mt-1">{{ stats.suspended }}</p>
        </div>
      </div>

      <!-- Quick Filter Pills & Search -->
      <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs space-y-3">
        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            @click="filterByTab('', '')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="!accountStatus && !identityStatus ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          >
            Semua Pendaftar ({{ stats.total }})
          </button>
          <button
            type="button"
            @click="filterByTab('', 'PENDING')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
            :class="identityStatus === 'PENDING' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-100/70 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 hover:bg-amber-200'"
          >
            <span v-if="stats.identity_pending > 0" class="w-2 h-2 rounded-full bg-amber-400"></span>
            <span>Menunggu Verifikasi ({{ stats.identity_pending }})</span>
          </button>
          <button
            type="button"
            @click="filterByTab('ACTIVE', 'VERIFIED')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="accountStatus === 'ACTIVE' && identityStatus === 'VERIFIED' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          >
            Terverifikasi Aktif ({{ stats.active }})
          </button>
          <button
            type="button"
            @click="filterByTab('SUSPENDED', '')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="accountStatus === 'SUSPENDED' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          >
            Ditangguhkan ({{ stats.suspended }})
          </button>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-1">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Cari nama pemohon, email, atau 16 digit NIK..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <select
            v-model="accountStatus"
            @change="applyFilters"
            class="px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
          >
            <option value="">Semua Status Akun</option>
            <option value="ACTIVE">ACTIVE</option>
            <option value="IDENTITY_PENDING">IDENTITY_PENDING</option>
            <option value="EMAIL_PENDING">EMAIL_PENDING</option>
            <option value="SUSPENDED">SUSPENDED</option>
            <option value="LOCKED">LOCKED</option>
          </select>

          <select
            v-model="identityStatus"
            @change="applyFilters"
            class="px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
          >
            <option value="">Semua Status Identitas</option>
            <option value="VERIFIED">VERIFIED</option>
            <option value="PENDING">PENDING</option>
            <option value="REJECTED">REJECTED</option>
          </select>

          <button
            type="button"
            @click="applyFilters"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-xs"
          >
            Cari
          </button>
        </div>
      </div>

      <!-- Accounts Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="p-4 font-bold">Pemohon (Nama & NIK)</th>
                <th class="p-4 font-bold">Email</th>
                <th class="p-4 font-bold">Status Akun</th>
                <th class="p-4 font-bold">Status Identitas</th>
                <th class="p-4 font-bold">Perangkat Aktif (1 Browser)</th>
                <th class="p-4 font-bold">Terdaftar</th>
                <th class="p-4 font-bold text-right">Aksi Verifikasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
              <tr v-if="accounts.data.length === 0">
                <td colspan="7" class="p-8 text-center text-slate-400">
                  Tidak ada data pendaftar pemohon yang sesuai kriteria pencarian.
                </td>
              </tr>
              <tr v-for="acc in accounts.data" :key="acc.id" class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition">
                <td class="p-4">
                  <p class="font-black text-slate-900 dark:text-white text-sm">{{ acc.name }}</p>
                  <p class="font-mono text-xs text-blue-600 dark:text-blue-400 font-bold">{{ acc.masked_nik }}</p>
                </td>
                <td class="p-4">
                  <span class="font-mono text-slate-600 dark:text-slate-400">{{ acc.masked_email }}</span>
                </td>
                <td class="p-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-black"
                    :class="{
                      'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': acc.account_status === 'ACTIVE',
                      'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': acc.account_status === 'IDENTITY_PENDING' || acc.account_status === 'EMAIL_PENDING',
                      'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300': acc.account_status === 'SUSPENDED' || acc.account_status === 'LOCKED',
                    }"
                  >
                    {{ acc.account_status }}
                  </span>
                </td>
                <td class="p-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-black"
                    :class="{
                      'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': acc.identity_status === 'VERIFIED',
                      'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': acc.identity_status === 'PENDING',
                      'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300': acc.identity_status === 'REJECTED',
                    }"
                  >
                    {{ acc.identity_status }}
                  </span>
                </td>
                <td class="p-4">
                  <div v-if="acc.active_device_label" class="space-y-0.5">
                    <p class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                      <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                      <span>{{ acc.active_device_label }}</span>
                    </p>
                    <p class="text-[10px] text-slate-400">Aktif: {{ acc.active_device_seen }}</p>
                  </div>
                  <span v-else class="text-slate-400 text-[11px] italic">Belum terikat browser</span>
                </td>
                <td class="p-4 text-slate-500 whitespace-nowrap">
                  {{ acc.created_at }}
                </td>
                <td class="p-4 text-right">
                  <div class="inline-flex items-center gap-2">
                    <!-- Quick Verify button for PENDING applicants -->
                    <button
                      v-if="acc.identity_status === 'PENDING'"
                      type="button"
                      @click="openQuickVerifyModal(acc)"
                      class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-xs flex items-center gap-1 cursor-pointer"
                    >
                      <UserCheck class="w-3.5 h-3.5" />
                      <span>Verifikasi</span>
                    </button>

                    <Link
                      :href="route('admin.queue.applicants.show', acc.id)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white font-bold text-xs transition cursor-pointer"
                    >
                      <span>Detail</span>
                      <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="accounts.links && accounts.links.length > 3" class="p-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-1">
          <Link
            v-for="(link, idx) in accounts.links"
            :key="idx"
            :href="link.url || '#'"
            v-html="link.label"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="link.active ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          />
        </div>
      </div>
    </div>

    <!-- Quick Verify Modal -->
    <Teleport to="body">
      <div 
        v-if="selectedApplicantForVerify"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
        @click.self="selectedApplicantForVerify = null"
      >
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white">
              Verifikasi Identitas Pendaftar Baru
            </h3>
            <p class="text-xs text-slate-500 mt-1">
              Validasi kesesuaian identitas pemohon kependudukan berdasarkan data resmi Disdukcapil.
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 space-y-1 text-xs">
            <p class="text-slate-500">Nama Lengkap:</p>
            <p class="font-black text-slate-900 dark:text-white text-sm">{{ selectedApplicantForVerify.name }}</p>
            <p class="text-slate-500 pt-1">NIK:</p>
            <p class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ selectedApplicantForVerify.masked_nik }}</p>
          </div>

          <form @submit.prevent="submitQuickVerify" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Keputusan Verifikasi
              </label>
              <select
                v-model="quickVerifyForm.decision"
                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none"
              >
                <option value="VERIFIED">VERIFIED (Setujui & Aktifkan Akun)</option>
                <option value="REJECTED">REJECTED (Tolak Identitas)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Catatan Verifikasi (Opsional)
              </label>
              <input
                v-model="quickVerifyForm.notes"
                type="text"
                placeholder="Contoh: Telah sesuai dokumen SIAK / fisik KTP-el"
                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
              />
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
              <button
                type="button"
                @click="selectedApplicantForVerify = null"
                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs transition"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="quickVerifyForm.processing"
                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm disabled:opacity-50"
              >
                {{ quickVerifyForm.processing ? 'Menyimpan...' : 'Simpan Verifikasi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ChevronRight, UserCheck } from '@lucide/vue'

const props = defineProps({
  accounts: Object,
  filters: Object,
  stats: Object,
})

const searchQuery = ref(props.filters.search || '')
const accountStatus = ref(props.filters.account_status || '')
const identityStatus = ref(props.filters.identity_status || '')

const applyFilters = () => {
  router.get(
    route('admin.queue.applicants.index'),
    {
      search: searchQuery.value,
      account_status: accountStatus.value,
      identity_status: identityStatus.value,
    },
    { preserveState: true }
  )
}

const filterByTab = (accStatus, idStatus) => {
  accountStatus.value = accStatus
  identityStatus.value = idStatus
  applyFilters()
}

// Quick verify modal
const selectedApplicantForVerify = ref(null)
const quickVerifyForm = useForm({
  decision: 'VERIFIED',
  notes: '',
})

const openQuickVerifyModal = (acc) => {
  selectedApplicantForVerify.value = acc
  quickVerifyForm.reset()
  quickVerifyForm.decision = 'VERIFIED'
}

const submitQuickVerify = () => {
  if (!selectedApplicantForVerify.value) return
  quickVerifyForm.post(route('admin.queue.applicants.verify-identity', selectedApplicantForVerify.value.id), {
    onSuccess: () => {
      selectedApplicantForVerify.value = null
    },
  })
}
</script>
