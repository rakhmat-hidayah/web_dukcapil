<template>
  <Head :title="`Detail Pengaduan #${complaint.ticket_number}`" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Top header / actions -->
      <div class="flex items-center justify-between border-b border-gray-150 dark:border-zinc-800 pb-4">
        <div>
          <Link :href="route('admin.complaints.index')" class="text-xs font-bold text-primary-600 hover:underline">
            ← Kembali ke Daftar Pengaduan
          </Link>
          <h1 class="text-lg font-black text-gray-900 dark:text-zinc-50 tracking-tight mt-1">
            Detail Tiket #{{ complaint.ticket_number }}
          </h1>
        </div>

        <!-- Status update triggers -->
        <div class="flex items-center gap-2">
          <select 
            v-model="statusForm.status"
            class="px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-250 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none"
          >
            <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
          </select>
          <button 
            @click="submitStatusChange"
            class="px-4 py-1.5 bg-gray-900 dark:bg-zinc-700 hover:bg-gray-800 text-white text-xs font-black rounded-xl transition"
          >
            Update Status
          </button>
        </div>
      </div>

      <!-- Main layouts grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Detail and Reply box (Col span 2) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-6">
            <div>
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
              <h2 class="text-base font-extrabold text-gray-950 dark:text-zinc-50 mt-2">
                {{ complaint.subject }}
              </h2>
              <p class="text-[10px] text-gray-400 font-semibold mt-1">
                Diajukan oleh: <strong class="text-gray-600 dark:text-zinc-300">{{ complaint.is_anonymous ? 'Anonim' : complaint.submitter_name }}</strong> &nbsp;·&nbsp; {{ formatDate(complaint.created_at) }}
              </p>
            </div>

            <!-- Message content -->
            <div class="text-xs text-gray-700 dark:text-zinc-300 leading-relaxed border-t border-gray-50 dark:border-zinc-800 pt-4 whitespace-pre-line">
              {{ complaint.message }}
            </div>

            <!-- Attachment section -->
            <div v-if="complaint.attachment_path" class="border-t border-gray-50 dark:border-zinc-800 pt-4 space-y-2">
              <h4 class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Lampiran Dokumen</h4>
              <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-zinc-800/40 rounded-xl max-w-md">
                <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300 truncate max-w-[200px]">
                  {{ complaint.attachment_name || 'lampiran_dokumen' }}
                </span>
                <a 
                  :href="`/storage/${complaint.attachment_path}`" 
                  target="_blank" 
                  class="px-3 py-1 bg-primary-600 hover:bg-primary-500 text-white text-[10px] font-bold rounded-lg transition"
                >
                  Buka File
                </a>
              </div>
            </div>
          </div>

          <!-- History timeline replies logs -->
          <div class="space-y-4">
            <h3 class="text-sm font-black text-gray-900 dark:text-zinc-50">
              💬 Tanggapan & Audit Log Pengaduan
            </h3>

            <div class="space-y-4 pl-4 border-l border-gray-200 dark:border-zinc-800">
              <div v-if="complaint.replies.length === 0" class="text-xs text-gray-400 italic">
                Belum ada tanggapan atau pembaruan status.
              </div>

              <div 
                v-for="reply in complaint.replies" 
                :key="reply.id"
                class="relative pl-6 space-y-1"
              >
                <!-- Dot icon marker -->
                <div 
                  class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-zinc-950 flex items-center justify-center"
                  :class="[
                    reply.type === 'status_change' ? 'bg-indigo-500' : 'bg-emerald-500',
                  ]"
                ></div>

                <div class="flex items-center gap-2">
                  <span class="text-[9px] font-bold text-gray-450 font-mono">{{ formatDate(reply.created_at) }}</span>
                  <span 
                    class="text-[8px] font-bold font-mono px-1.5 py-0.5 rounded uppercase"
                    :class="[
                      reply.type === 'status_change' ? 'bg-indigo-50 dark:bg-indigo-900/35 text-indigo-600' : 'bg-emerald-50 dark:bg-emerald-900/35 text-emerald-600',
                    ]"
                  >
                    {{ reply.type === 'status_change' ? 'Status Update' : 'Respon Admin' }}
                  </span>
                  <span 
                    v-if="!reply.is_visible_to_submitter"
                    class="text-[8px] font-bold font-mono px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 uppercase"
                  >
                    Internal Note (Private)
                  </span>
                </div>

                <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 p-4 rounded-2xl max-w-xl">
                  <h4 class="text-xs font-bold text-gray-800 dark:text-zinc-200">
                    {{ reply.user ? reply.user.name : 'Sistem' }}
                  </h4>
                  <p class="text-xs text-gray-600 dark:text-zinc-400 mt-1 whitespace-pre-line leading-relaxed">
                    {{ reply.message }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Add Response / Reply form -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Tulis Tanggapan Resmi</h3>
            
            <form @submit.prevent="submitReply" class="space-y-4">
              <div>
                <textarea 
                  v-model="replyForm.message"
                  required
                  rows="4"
                  placeholder="Ketik balasan atau catatan di sini..."
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                ></textarea>
              </div>

              <!-- Visibility toggle -->
              <div class="flex items-center gap-3">
                <input 
                  id="is_visible" 
                  type="checkbox" 
                  v-model="replyForm.is_visible_to_submitter"
                  class="w-4 h-4 accent-primary-600"
                />
                <label for="is_visible" class="text-xs text-gray-500 dark:text-zinc-400 cursor-pointer">
                  Tampilkan tanggapan ini di halaman pelacakan pelapor (Public)
                </label>
              </div>

              <button 
                type="submit"
                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl transition"
              >
                Kirim Tanggapan
              </button>
            </form>
          </div>
        </div>

        <!-- Submitter Details & Audit (Col span 1) -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Metadata Pelapor</h3>
            
            <div class="space-y-3 text-xs">
              <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest font-mono">Nama Pengadu</p>
                <p class="font-bold text-gray-800 dark:text-zinc-200 mt-0.5">
                  {{ complaint.is_anonymous ? 'Anonim (Disamarkan)' : complaint.submitter_name }}
                </p>
              </div>
              <div v-if="!complaint.is_anonymous">
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest font-mono">Nomor HP</p>
                <p class="font-bold text-gray-800 dark:text-zinc-200 mt-0.5">{{ complaint.submitter_phone || 'Tidak dicantumkan' }}</p>
              </div>
              <div v-if="!complaint.is_anonymous">
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest font-mono">Email</p>
                <p class="font-bold text-gray-800 dark:text-zinc-200 mt-0.5">{{ complaint.submitter_email || 'Tidak dicantumkan' }}</p>
              </div>
              <div class="border-t border-gray-50 dark:border-zinc-800 pt-3">
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest font-mono">IP Address</p>
                <p class="font-bold text-gray-500 font-mono mt-0.5">{{ complaint.ip_address || '—' }}</p>
              </div>
              <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest font-mono">User Agent</p>
                <p class="font-medium text-gray-400 mt-0.5 text-[10px] break-all leading-normal">
                  {{ complaint.user_agent || '—' }}
                </p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  complaint: Object,
  statusLabels: Object,
  statusColors: Object,
});

const replyForm = reactive({
  message: '',
  is_visible_to_submitter: true,
});

const statusForm = reactive({
  status: props.complaint.status,
  note: '',
});

const submitReply = () => {
  router.post(route('admin.complaints.reply', props.complaint.id), replyForm, {
    onSuccess: () => {
      replyForm.message = '';
    }
  });
};

const submitStatusChange = () => {
  const note = prompt('Ketik alasan atau catatan perubahan status (opsional):') || '';
  statusForm.note = note;
  
  router.post(route('admin.complaints.status', props.complaint.id), statusForm);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>
