<template>
  <PublicLayout title="Lacak Permohonan PPID — Disdukcapil Kab. Dompu">
    
    <!-- Modern Dukcapil Identity Hero Banner -->
    <section class="relative bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white py-12 md:py-16 overflow-hidden rounded-3xl mb-8 border border-blue-900/30 shadow-xl shadow-blue-950/20">
      <img 
        src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1400&q=80"
        class="absolute inset-0 w-full h-full object-cover opacity-20 filter blur-[3px] scale-105 pointer-events-none z-0"
        alt="Backdrop PPID Track" 
      />
      <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:28px_28px] pointer-events-none z-0"></div>

      <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
        <div class="inline-flex items-center gap-2 text-xs font-semibold text-blue-200/80 mb-4">
          <Link href="/" class="hover:text-white transition">Beranda</Link>
          <ChevronRight :size="12" class="text-blue-400" />
          <Link :href="route('public.ppid.pengertian')" class="hover:text-white transition">PPID</Link>
          <ChevronRight :size="12" class="text-blue-400" />
          <span class="text-amber-400 font-bold">Lacak Permohonan</span>
        </div>

        <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-sm">
          Lacak Permohonan PPID
        </h1>
        <p class="text-xs sm:text-sm text-blue-100/90 font-medium max-w-xl mx-auto mt-2">
          Masukkan nomor tiket permohonan untuk memantau status penyelesaian berkas Anda secara riil
        </p>
      </div>
    </section>

    <!-- Content Area -->
    <div class="max-w-3xl mx-auto px-4 py-4 space-y-6">
      
      <!-- Search Form Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
        <form @submit.prevent="doSearch" class="flex flex-col sm:flex-row gap-3">
          <div class="flex-1 relative">
            <Search :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
            <input
              v-model="ticketInput"
              type="text"
              placeholder="Masukkan nomor tiket (Contoh: PPID-2026-xxxxxx)"
              class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs sm:text-sm text-gray-900 dark:text-zinc-100 font-mono focus:ring-2 focus:ring-primary-500 outline-none"
            />
          </div>
          <button 
            type="submit" 
            class="px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-amber-500/25 transition active:scale-95 flex items-center justify-center gap-2 shrink-0 cursor-pointer"
          >
            <Search :size="16" class="text-slate-950" />
            <span>Lacak Status</span>
          </button>
        </form>
      </div>

      <!-- Result: Not Found -->
      <div v-if="ticket && !ppidRequest" class="bg-white dark:bg-zinc-900 border border-rose-100 dark:border-rose-950/60 rounded-3xl p-10 text-center shadow-sm space-y-2">
        <XCircle :size="44" class="mx-auto text-rose-500/60" />
        <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">Tiket Tidak Ditemukan</h3>
        <p class="text-xs text-gray-500 dark:text-zinc-400 max-w-md mx-auto">
          Nomor tiket <strong class="text-gray-800 dark:text-zinc-200 font-mono">{{ ticket }}</strong> tidak tercatat dalam database PPID. Harap periksa kembali penulisan nomor tiket Anda.
        </p>
      </div>

      <!-- Result: Found Card -->
      <div v-if="ppidRequest" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        
        <!-- Header status -->
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-zinc-800">
          <div class="flex items-center gap-2.5 bg-primary-50 dark:bg-zinc-800 px-3.5 py-1.5 rounded-xl border border-primary-200 dark:border-zinc-700">
            <Ticket :size="16" class="text-primary-600 dark:text-primary-400" />
            <span class="font-mono text-xs font-black text-primary-700 dark:text-primary-300 tracking-wider">
              {{ ppidRequest.ticket_number }}
            </span>
          </div>

          <span 
            class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider"
            :class="{
              'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300': ppidRequest.status === 'diterima',
              'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300': ppidRequest.status === 'diproses',
              'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300': ppidRequest.status === 'selesai',
              'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300': ppidRequest.status === 'ditolak',
            }"
          >
            {{ ppidRequest.status_label || ppidRequest.status }}
          </span>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <div class="space-y-0.5">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-zinc-500">Nama Pemohon</span>
            <p class="font-bold text-gray-800 dark:text-zinc-200">{{ ppidRequest.requester_name }}</p>
          </div>

          <div class="space-y-0.5">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-zinc-500">Tanggal Diajukan</span>
            <p class="font-bold text-gray-800 dark:text-zinc-200">{{ formatDate(ppidRequest.created_at) }}</p>
          </div>

          <div class="space-y-0.5">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-zinc-500">Cara Pengajuan</span>
            <p class="font-bold text-gray-800 dark:text-zinc-200 capitalize">{{ ppidRequest.request_method }}</p>
          </div>

          <div class="space-y-0.5">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-zinc-500">Penerimaan Berkas</span>
            <p class="font-bold text-gray-800 dark:text-zinc-200 capitalize">{{ ppidRequest.delivery_method }}</p>
          </div>
        </div>

        <!-- Detail Rincian -->
        <div class="bg-gray-50 dark:bg-zinc-800/60 rounded-2xl p-4 border border-gray-100 dark:border-zinc-800 text-xs space-y-1">
          <span class="font-extrabold text-primary-600 dark:text-primary-400 block">Informasi Yang Diminta:</span>
          <p class="text-gray-700 dark:text-zinc-300 font-medium leading-relaxed">{{ ppidRequest.information_requested }}</p>
        </div>

        <!-- Response Box -->
        <div v-if="ppidRequest.response_notes" class="bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 rounded-2xl p-4 text-xs space-y-1 text-emerald-900 dark:text-emerald-200">
          <div class="flex items-center gap-2 font-extrabold text-emerald-700 dark:text-emerald-300">
            <MessageSquare :size="15" />
            <span>Respons Resmi Dari Petugas PPID</span>
          </div>
          <p class="font-medium leading-relaxed pt-1">{{ ppidRequest.response_notes }}</p>
          <span v-if="ppidRequest.responded_at" class="text-[10px] opacity-75 block pt-2">
            Waktu Tanggapan: {{ formatDate(ppidRequest.responded_at) }}
          </span>
        </div>

        <!-- Progress Steps -->
        <div class="pt-4 border-t border-gray-100 dark:border-zinc-800">
          <div class="flex items-center justify-between text-center relative">
            <div
              v-for="(step, i) in statusSteps"
              :key="step.key"
              class="flex flex-col items-center flex-1 z-10"
            >
              <div 
                class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 shadow-sm"
                :class="isStepDone(step.key) 
                  ? 'bg-primary-600 text-white ring-4 ring-primary-100 dark:ring-primary-950' 
                  : 'bg-gray-100 dark:bg-zinc-800 text-gray-400 dark:text-zinc-500'"
              >
                <CheckCircle v-if="isStepDone(step.key)" :size="16" />
                <div v-else class="w-2.5 h-2.5 rounded-full bg-current"></div>
              </div>
              <span 
                class="text-[11px] font-extrabold mt-2"
                :class="isStepDone(step.key) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-zinc-500'"
              >
                {{ step.label }}
              </span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ChevronRight, Search, XCircle, Ticket, MessageSquare, CheckCircle } from '@lucide/vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  ppidRequest: Object,
  ticket: String,
  pages: Array,
})

const ticketInput = ref(props.ticket ?? '')

const statusSteps = [
  { key: 'diterima', label: 'Diterima' },
  { key: 'diproses', label: 'Diproses' },
  { key: 'selesai',  label: 'Selesai'  },
]

function isStepDone(key) {
  if (!props.ppidRequest) return false
  const order = ['diterima', 'diproses', 'selesai', 'ditolak']
  return order.indexOf(props.ppidRequest.status) >= order.indexOf(key)
}

function doSearch() {
  if (ticketInput.value.trim()) {
    router.get(route('public.ppid.track'), { ticket: ticketInput.value.trim() }, { preserveState: true })
  }
}

function formatDate(dt) {
  if (!dt) return '-'
  return new Date(dt).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
