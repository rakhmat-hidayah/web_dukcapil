<template>
  <Head title="Permohonan Informasi Berhasil Dikirim — PPID" />

  <PublicLayout>
    <div class="max-w-xl mx-auto py-12 px-4 text-left">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-6 text-center">
        <!-- Success Icon -->
        <div class="w-16 h-16 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto text-3xl shadow-sm border border-emerald-200 dark:border-emerald-900/40">
          ✓
        </div>

        <div class="space-y-2">
          <h1 class="text-2xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
            Permohonan Informasi Berhasil Dikirim!
          </h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 leading-relaxed max-w-md mx-auto">
            Terima kasih, permohonan informasi publik Anda telah kami terima dan akan segera ditindaklanjuti oleh Petugas PPID Disdukcapil Kabupaten Dompu.
          </p>
        </div>

        <!-- Ticket Card Box -->
        <div class="p-6 bg-slate-900 text-white rounded-2xl space-y-3 shadow-md relative overflow-hidden">
          <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 font-mono">
            Nomor Tiket Permohonan Anda
          </p>
          <div class="flex items-center justify-center gap-3">
            <span class="text-2xl font-black font-mono tracking-wider text-amber-400">
              {{ request.ticket_number }}
            </span>
            <button 
              @click="copyTicket" 
              class="px-2.5 py-1 bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold rounded-lg transition active:scale-95 cursor-pointer"
            >
              {{ copied ? '✓ Tersalin' : '📋 Salin' }}
            </button>
          </div>
          <p class="text-[10px] text-slate-300 font-medium leading-relaxed">
            Simpan nomor tiket di atas untuk melakukan pengecekan status permohonan secara berkala.
          </p>
        </div>

        <!-- Details -->
        <div class="text-left bg-gray-50 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 space-y-2.5 text-xs">
          <div class="flex justify-between border-b border-gray-200/60 dark:border-zinc-700/60 pb-2">
            <span class="text-gray-500 dark:text-zinc-400 font-medium">Pemohon</span>
            <span class="font-bold text-gray-900 dark:text-zinc-100">{{ request.requester_name }}</span>
          </div>
          <div class="flex justify-between border-b border-gray-200/60 dark:border-zinc-700/60 pb-2">
            <span class="text-gray-500 dark:text-zinc-400 font-medium">Email</span>
            <span class="font-bold text-gray-900 dark:text-zinc-100">{{ request.requester_email }}</span>
          </div>
          <div class="flex justify-between border-b border-gray-200/60 dark:border-zinc-700/60 pb-2">
            <span class="text-gray-500 dark:text-zinc-400 font-medium">Tujuan Informasi</span>
            <span class="font-bold text-gray-900 dark:text-zinc-100 truncate max-w-[200px]">{{ request.purpose }}</span>
          </div>
          <div class="flex justify-between pt-0.5">
            <span class="text-gray-500 dark:text-zinc-400 font-medium">Tanggal Pengajuan</span>
            <span class="font-bold text-gray-900 dark:text-zinc-100 font-mono">{{ formatDate(request.created_at) }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          <Link 
            :href="route('public.ppid.track', { ticket: request.ticket_number })" 
            class="w-full py-3 bg-primary-600 hover:bg-primary-500 text-white font-extrabold text-xs rounded-xl shadow-sm flex items-center justify-center gap-1.5 transition active:scale-95"
          >
            <span>🔍 Lacak Permohonan</span>
          </Link>

          <Link 
            href="/" 
            class="w-full py-3 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-800 dark:text-zinc-200 font-bold text-xs rounded-xl transition text-center"
          >
            <span>🏠 Kembali ke Beranda</span>
          </Link>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  request: Object,
})

const copied = ref(false)

function copyTicket() {
  if (navigator.clipboard && props.request.ticket_number) {
    navigator.clipboard.writeText(props.request.ticket_number)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  }
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>
