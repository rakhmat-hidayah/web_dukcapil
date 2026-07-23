<template>
  <Head title="Lacak Status Pengaduan" />

  <PublicLayout>
    <div class="space-y-8 text-left max-w-xl mx-auto py-12">
      <!-- Title -->
      <div class="text-center space-y-2">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Lacak Pengaduan</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
          Masukkan nomor tiket pengaduan Anda untuk memantau status respon dari petugas admin Disdukcapil Dompu.
        </p>
      </div>

      <!-- Form card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
        <form @submit.prevent="submitTrack" class="space-y-4">
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nomor Tiket Pengaduan *</label>
            <input 
              v-model="form.ticket_number" 
              required 
              type="text" 
              placeholder="Contoh: DKP-2026-XXXXXX" 
              class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold uppercase placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:outline-none"
            />
            <p v-if="errors.ticket_number" class="text-[9px] text-red-500 font-bold mt-1">{{ errors.ticket_number }}</p>
          </div>

          <button 
            type="submit" 
            :disabled="submitting"
            class="w-full py-3 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition disabled:opacity-50"
          >
            {{ submitting ? 'Mencari Tiket...' : '🔍 Lacak Status Tiket' }}
          </button>
        </form>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const form = reactive({
  ticket_number: '',
});

const errors = ref({});
const submitting = ref(false);

const submitTrack = () => {
  errors.value = {};
  submitting.value = true;

  router.post(route('public.complaint.track.search'), form, {
    onError: (errs) => {
      errors.value = errs;
      submitting.value = false;
    },
    onFinish: () => {
      submitting.value = false;
    }
  });
};
</script>
