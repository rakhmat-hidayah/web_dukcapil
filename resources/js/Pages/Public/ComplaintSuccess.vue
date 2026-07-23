<template>
  <Head title="Pengaduan Berhasil Dikirim" />

  <PublicLayout>
    <div class="space-y-8 text-center max-w-xl mx-auto py-12">
      <!-- Checked Icon with animate ring -->
      <div class="flex justify-center">
        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-950/40 border-2 border-emerald-500 rounded-full flex items-center justify-center text-emerald-600 animate-bounce">
          <Check class="w-8 h-8" />
        </div>
      </div>

      <!-- Detail text -->
      <div class="space-y-3">
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Pengaduan Berhasil Dikirim!</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
          Terima kasih atas laporan Anda. Pengaduan Anda dengan subjek <strong>"{{ subject }}"</strong> telah kami terima dan terdaftar dalam sistem.
        </p>
      </div>

      <!-- Ticket Number card -->
      <div class="bg-gray-50 dark:bg-zinc-900 border border-gray-150 dark:border-zinc-800 rounded-3xl p-6 space-y-4 max-w-md mx-auto">
        <div>
          <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Nomor Tiket Anda</p>
          <p class="text-2xl font-black text-primary-600 font-mono tracking-wider mt-1 select-all cursor-pointer" title="Klik untuk salin" @click="copyTicket">
            {{ ticket }}
          </p>
        </div>
        <p class="text-[10px] text-gray-400">
          Simpan nomor tiket di atas dengan baik. Anda dapat menggunakannya untuk melacak perkembangan status penanganan pengaduan Anda.
        </p>
      </div>

      <!-- Action buttons -->
      <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
        <Link 
          :href="route('public.complaint.status', ticket)"
          class="px-5 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-sm transition"
        >
          Lacak Status Pengaduan
        </Link>
        <Link 
          :href="route('public.home')"
          class="px-5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 text-gray-600 dark:text-zinc-300 text-xs font-bold rounded-xl transition"
        >
          Kembali ke Beranda
        </Link>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Check } from '@lucide/vue';

const props = defineProps({
  ticket: String,
  subject: String,
});

const copyTicket = () => {
  navigator.clipboard.writeText(props.ticket);
  alert('Nomor tiket berhasil disalin ke clipboard!');
};
</script>
