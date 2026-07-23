<template>
  <Head title="Admin Login" />

  <div class="min-h-screen flex items-center justify-center bg-radial from-primary-950 via-zinc-950 to-zinc-950 p-6 relative overflow-hidden">
    <!-- Decorative background blobs -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl"></div>

    <div class="w-full max-w-md z-10">
      <!-- Outer Card -->
      <div class="glass-panel rounded-3xl shadow-2xl overflow-hidden transition-all duration-300">
        <div class="p-8 sm:p-10">
          
          <!-- Logo & Header -->
          <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-tr from-primary-600 to-primary-400 text-white rounded-2xl shadow-lg shadow-primary-500/30 mb-4 animate-bounce">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-white">Dukcapil Dompu</h2>
            <p class="text-xs text-zinc-400 mt-1.5">Sistem Informasi Kependudukan & CMS Administrator</p>
          </div>

          <!-- Form -->
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Flash Message -->
            <div v-if="form.errors.email && form.errors.email.includes('Terlalu banyak')" class="p-3 bg-red-950/50 border border-red-500/30 rounded-xl text-xs text-red-400">
              {{ form.errors.email }}
            </div>

            <!-- Honeypot (hidden) -->
            <div class="hidden">
              <input type="text" v-model="form.honeypot" tabindex="-1" autocomplete="off" />
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Alamat Email</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                  </svg>
                </span>
                <input 
                  type="email" 
                  v-model="form.email" 
                  required
                  placeholder="admin@dompukab.go.id"
                  class="w-full pl-11 pr-4 py-3 bg-zinc-900/50 border border-zinc-800 rounded-2xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                />
              </div>
              <span v-if="form.errors.email" class="text-xs text-red-400 mt-1 block">{{ form.errors.email }}</span>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Kata Sandi</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </span>
                <input 
                  type="password" 
                  v-model="form.password" 
                  required
                  placeholder="••••••••"
                  class="w-full pl-11 pr-4 py-3 bg-zinc-900/50 border border-zinc-800 rounded-2xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                />
              </div>
              <span v-if="form.errors.password" class="text-xs text-red-400 mt-1 block">{{ form.errors.password }}</span>
            </div>

            <!-- Captcha -->
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Verifikasi Keamanan</label>
              <div class="grid grid-cols-2 gap-3 items-center">
                <!-- Captcha Image Frame -->
                <div class="relative bg-zinc-900 border border-zinc-800 rounded-2xl p-1.5 flex items-center justify-between overflow-hidden">
                  <img :src="currentCaptcha" class="h-8 object-contain rounded-xl w-full" alt="Captcha" />
                  <button 
                    type="button"
                    @click="refreshCaptcha" 
                    class="absolute right-2 top-2.5 p-1 bg-zinc-800 hover:bg-zinc-700 text-zinc-400 hover:text-white rounded-lg transition"
                    title="Refresh CAPTCHA"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3 3 3" />
                    </svg>
                  </button>
                </div>

                <!-- Input code -->
                <input 
                  type="text" 
                  v-model="form.captcha" 
                  required
                  placeholder="Kode CAPTCHA"
                  autocomplete="off"
                  class="w-full px-4 py-3 bg-zinc-900/50 border border-zinc-800 rounded-2xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                />
              </div>
              <span v-if="form.errors.captcha" class="text-xs text-red-400 mt-1 block">{{ form.errors.captcha }}</span>
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between text-xs mt-1">
              <label class="flex items-center gap-2 cursor-pointer text-zinc-400 hover:text-white transition">
                <input type="checkbox" v-model="form.remember" class="accent-primary-500 rounded border-zinc-800" />
                <span>Ingat saya</span>
              </label>
            </div>

            <!-- Submit -->
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full py-3.5 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-bold rounded-2xl shadow-lg shadow-primary-500/20 active:scale-[0.98] transition focus:outline-none focus:ring-2 focus:ring-primary-400 disabled:opacity-50 disabled:scale-100"
            >
              <span v-if="form.processing" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
              </span>
              <span v-else>Masuk ke Portal</span>
            </button>
          </form>
        </div>
      </div>
      
      <div class="text-center mt-6 text-xs text-zinc-600">
        Dinas Kependudukan & Pencatatan Sipil Kabupaten Dompu NTB © 2026
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  captchaImage: String,
});

const currentCaptcha = ref(props.captchaImage);

const form = useForm({
  email: '',
  password: '',
  captcha: '',
  honeypot: '',
  remember: false,
});

const refreshCaptcha = async () => {
  try {
    const res = await axios.get(route('captcha.image'));
    currentCaptcha.value = res.data.image;
    form.captcha = '';
  } catch (err) {
    console.error('Failed to reload captcha', err);
  }
};

const submit = () => {
  form.post(route('admin.login'), {
    onFailure: () => {
      refreshCaptcha();
    },
    onError: () => {
      refreshCaptcha();
    }
  });
};
</script>
