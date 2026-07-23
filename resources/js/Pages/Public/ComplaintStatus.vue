<template>
  <Head :title="`Status Pengaduan #${complaint.ticket_number}`" />

  <PublicLayout>
    <div class="space-y-8 text-left max-w-3xl mx-auto">
      
      <!-- Header / back link -->
      <div>
        <Link :href="route('public.complaint.track')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
          ← Kembali ke Pelacakan
        </Link>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-3">
          <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Status Pengaduan</h1>
            <p class="text-xs text-gray-400 font-mono mt-1">Tiket: {{ complaint.ticket_number }}</p>
          </div>
          
          <!-- Status Badge -->
          <span 
            class="px-3.5 py-1.5 rounded-full text-xs font-extrabold border font-mono uppercase tracking-wider text-center w-fit"
            :class="[
              complaint.status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900 text-amber-700 dark:text-amber-400' : '',
              complaint.status === 'in_review' ? 'bg-blue-50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900 text-blue-700 dark:text-blue-400' : '',
              complaint.status === 'in_progress' ? 'bg-indigo-50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-900 text-indigo-700 dark:text-indigo-400' : '',
              complaint.status === 'resolved' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900 text-emerald-700 dark:text-emerald-400' : '',
              complaint.status === 'rejected' ? 'bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-900 text-red-700 dark:text-red-400' : '',
            ]"
          >
            {{ complaint.status_label }}
          </span>
        </div>
      </div>

      <!-- Main complaint details card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-6">
        <div class="space-y-3">
          <span 
            v-if="complaint.category" 
            class="text-[9px] font-bold font-mono uppercase tracking-wider border px-1.5 py-0.5 rounded"
            :style="{ 
              color: complaint.category.color, 
              backgroundColor: complaint.category.color + '15',
              borderColor: complaint.category.color + '30'
            }"
          >
            {{ complaint.category.name }}
          </span>

          <h2 class="text-xl font-extrabold text-gray-900 dark:text-zinc-50">{{ complaint.subject }}</h2>
          <p class="text-xs text-gray-400 font-semibold">
            Dikirim Oleh: {{ complaint.submitter_name }} &nbsp;·&nbsp; {{ formatDate(complaint.created_at) }}
          </p>
        </div>

        <div class="text-xs text-gray-700 dark:text-zinc-300 leading-relaxed border-t border-gray-50 dark:border-zinc-800 pt-4 whitespace-pre-line">
          {{ complaint.message }}
        </div>
      </div>

      <!-- Timeline & Responses section -->
      <div class="space-y-6">
        <h3 class="text-md font-black text-gray-900 dark:text-zinc-50 tracking-tight">
          💬 Riwayat & Tanggapan Petugas
        </h3>

        <div class="space-y-6 pl-4 border-l border-gray-200 dark:border-zinc-800">
          <!-- Submission starting point -->
          <div class="relative pl-6">
            <div class="absolute -left-6 top-1 w-3 h-3 bg-primary-600 rounded-full border-2 border-white dark:border-zinc-950"></div>
            <div class="space-y-1">
              <span class="text-[9px] font-bold text-gray-400 font-mono">{{ formatDate(complaint.created_at) }}</span>
              <h4 class="text-xs font-black text-gray-800 dark:text-zinc-200">Pengaduan Diajukan</h4>
              <p class="text-[11px] text-gray-400">Berkas aduan berhasil terdaftar di database pengaduan.</p>
            </div>
          </div>

          <!-- Dynamic responses & status changes -->
          <div 
            v-for="reply in replies" 
            :key="reply.id"
            class="relative pl-6"
          >
            <!-- Timeline dot -->
            <div 
              class="absolute -left-6 top-1 w-3 h-3 rounded-full border-2 border-white dark:border-zinc-950"
              :class="reply.type === 'status_change' ? 'bg-indigo-500' : 'bg-emerald-500'"
            ></div>
            
            <div class="space-y-2">
              <div class="flex items-center gap-2">
                <span class="text-[9px] font-bold text-gray-400 font-mono">{{ formatDate(reply.created_at) }}</span>
                <span 
                  v-if="reply.type === 'status_change'"
                  class="text-[8px] font-bold font-mono px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/40 uppercase"
                >
                  Status Update
                </span>
                <span 
                  v-else
                  class="text-[8px] font-bold font-mono px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/40 uppercase"
                >
                  Tanggapan Admin
                </span>
              </div>

              <!-- Message details -->
              <div class="bg-gray-50 dark:bg-zinc-900 border border-gray-150 dark:border-zinc-800 p-4 rounded-2xl">
                <h4 class="text-xs font-extrabold text-gray-800 dark:text-zinc-200 mb-1">
                  {{ reply.user_name }}
                </h4>
                <p class="text-xs text-gray-600 dark:text-zinc-400 leading-relaxed whitespace-pre-line">
                  {{ reply.message }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  complaint: Object,
  replies: Array,
  statusLabels: Object,
});

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>
