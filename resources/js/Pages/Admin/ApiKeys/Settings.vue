<template>
  <Head title="API Configuration Settings" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Title section -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Pengaturan & Ketentuan API</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Konfigurasi batasan akses default (rate limiting) dan ubah Ketentuan Layanan (Terms of Service) API.
        </p>
      </div>

      <!-- Settings Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
        <form @submit.prevent="submit" class="space-y-6 max-w-3xl">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Public Rate Limit -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1.5">Batas Kuota Publik Default (request/jam)</label>
              <input 
                type="number" 
                v-model="form.api_rate_limit_public" 
                required
                min="10"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
              <p class="text-[10px] text-gray-400 mt-1">
                Kuota maksimal per jam untuk pengguna umum tanpa API Key (diidentifikasi berdasarkan IP Address).
              </p>
            </div>

            <!-- Partner Rate Limit -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1.5">Batas Kuota Partner Default (request/jam)</label>
              <input 
                type="number" 
                v-model="form.api_rate_limit_partner" 
                required
                min="100"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
              <p class="text-[10px] text-gray-400 mt-1">
                Kuota maksimal default untuk client terdaftar yang memegang API Key (dapat dioverride per client).
              </p>
            </div>
          </div>

          <!-- Alert box warning -->
          <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-xl flex gap-3 text-xs text-amber-700 dark:text-amber-400">
            <span class="mt-0.5 font-bold animate-pulse">💡 Info:</span>
            <p>
              Batas kuota diproses dinamis pada memori cache server. Pastikan server memiliki driver cache yang memadai (misal Database, Redis, atau Memcached) agar berjalan optimal.
            </p>
          </div>

          <!-- Terms of Service Markdown Textarea -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Ketentuan Layanan Integrasi (Terms of Service)</label>
            <textarea 
              v-model="form.api_terms_of_service" 
              rows="12" 
              required
              class="w-full p-4 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:outline-none leading-relaxed"
              placeholder="# Ketentuan Layanan REST API..."
            ></textarea>
            <p class="text-[10px] text-gray-400 mt-1">
              Ketentuan ditulis dalam format Markdown. Ketentuan ini akan ditampilkan di portal publik dokumentasi API.
            </p>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-zinc-800">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-5 py-3 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition disabled:opacity-50"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan API' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  settings: Object,
});

const form = useForm({
  api_rate_limit_public: props.settings.api_rate_limit_public || 100,
  api_rate_limit_partner: props.settings.api_rate_limit_partner || 1000,
  api_terms_of_service: props.settings.api_terms_of_service || '',
});

const submit = () => {
  form.post(route('admin.api-settings.update'), {
    preserveScroll: true,
  });
};
</script>
