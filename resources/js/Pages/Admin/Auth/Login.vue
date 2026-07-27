<template>
  <Head title="Admin Portal Login — Dukcapil Dompu" />

  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-950 via-blue-950 to-zinc-950 p-4 sm:p-6 relative overflow-hidden font-sans">
    <!-- Decorative Glowing Radial Blobs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-primary-500/10 rounded-full blur-[140px] pointer-events-none"></div>

    <div class="w-full max-w-md z-10">
      <!-- Outer Premium Glass Card Container -->
      <div class="bg-zinc-900/90 backdrop-blur-2xl border border-zinc-800/90 rounded-3xl shadow-2xl shadow-black/80 overflow-hidden transition-all duration-300">
        <div class="p-8 sm:p-10">
          
          <!-- Brand Logo Header -->
          <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center p-3 bg-gradient-to-tr from-blue-600 via-primary-600 to-indigo-600 rounded-2xl shadow-xl shadow-blue-500/30 mb-4 group ring-4 ring-blue-500/20">
              <img src="/img/logo-dukcapil.png" alt="Logo Dukcapil" class="h-10 w-auto object-contain brightness-200 drop-shadow" />
            </div>
            <h2 class="text-2xl font-black tracking-tight text-white">Dukcapil Dompu</h2>
            <p class="text-xs font-medium text-zinc-400 mt-1">Sistem Informasi Kependudukan & Administrator Portal</p>
          </div>

          <!-- Login Form -->
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Rate Limit / Global Error -->
            <div v-if="form.errors.email && form.errors.email.includes('Terlalu banyak')" class="p-3.5 bg-rose-950/80 border border-rose-500/40 rounded-2xl text-xs font-semibold text-rose-300 shadow-sm">
              ⚠️ {{ form.errors.email }}
            </div>

            <!-- Honeypot (hidden field for bots) -->
            <div class="hidden">
              <input type="text" v-model="form.honeypot" tabindex="-1" autocomplete="off" />
            </div>

            <!-- Email Address Field -->
            <div>
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-zinc-300 mb-1.5">Alamat Email</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400 pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                  </svg>
                </span>
                <input 
                  type="email" 
                  v-model="form.email" 
                  required
                  placeholder="admin@dompukab.go.id"
                  class="w-full pl-11 pr-4 py-3 bg-zinc-950/90 border border-zinc-700/80 rounded-2xl text-white placeholder-zinc-500 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner"
                />
              </div>
              <span v-if="form.errors.email && !form.errors.email.includes('Terlalu banyak')" class="text-xs font-semibold text-rose-400 mt-1.5 block">
                {{ form.errors.email }}
              </span>
            </div>

            <!-- Password Field -->
            <div>
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-zinc-300 mb-1.5">Kata Sandi</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400 pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </span>
                <input 
                  type="password" 
                  v-model="form.password" 
                  required
                  placeholder="••••••••"
                  class="w-full pl-11 pr-4 py-3 bg-zinc-950/90 border border-zinc-700/80 rounded-2xl text-white placeholder-zinc-500 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner"
                />
              </div>
              <span v-if="form.errors.password" class="text-xs font-semibold text-rose-400 mt-1.5 block">
                {{ form.errors.password }}
              </span>
            </div>

            <!-- Security Captcha Verification Field -->
            <div>
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-zinc-300 mb-1.5">Verifikasi Keamanan</label>
              <div class="grid grid-cols-2 gap-3 items-center">
                <!-- Crisp High-Contrast White CAPTCHA Frame -->
                <div class="relative bg-white border border-zinc-300 rounded-2xl p-1 flex items-center justify-between overflow-hidden shadow-inner group">
                  <img :src="currentCaptcha" class="h-10 object-contain w-full rounded-xl bg-white select-none" alt="CAPTCHA Verification Code" />
                  <button 
                    type="button"
                    @click="refreshCaptcha" 
                    class="absolute right-1.5 top-1.5 p-1.5 bg-slate-900/90 hover:bg-slate-900 text-white rounded-xl shadow-md transition-transform hover:scale-105 active:scale-95"
                    title="Perbarui Kode CAPTCHA Baru"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3 3 3" />
                    </svg>
                  </button>
                </div>

                <!-- Input CAPTCHA Code -->
                <input 
                  type="text" 
                  v-model="form.captcha" 
                  required
                  placeholder="Kode CAPTCHA"
                  autocomplete="off"
                  class="w-full px-4 py-3 bg-zinc-950/90 border border-zinc-700/80 rounded-2xl text-white placeholder-zinc-500 uppercase tracking-widest text-center font-mono font-bold text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner"
                />
              </div>
              <span v-if="form.errors.captcha" class="text-xs font-semibold text-rose-400 mt-1.5 block">
                {{ form.errors.captcha }}
              </span>
            </div>

            <!-- Remember Me Option -->
            <div class="flex items-center justify-between text-xs pt-1">
              <label class="flex items-center gap-2 cursor-pointer text-zinc-400 hover:text-white transition select-none font-medium">
                <input type="checkbox" v-model="form.remember" class="accent-blue-500 rounded border-zinc-700 w-4 h-4" />
                <span>Ingat saya</span>
              </label>
            </div>

            <!-- Submit Login Button -->
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full py-3.5 bg-gradient-to-r from-blue-600 via-primary-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black text-sm rounded-2xl shadow-xl shadow-blue-600/30 active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50 disabled:scale-100 cursor-pointer"
            >
              <span v-if="form.processing" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses Log Masuk...
              </span>
              <span v-else>Masuk ke Portal Administrator</span>
            </button>
          </form>
        </div>
      </div>
      
      <!-- Footer Copyright -->
      <div class="text-center mt-6 text-xs font-semibold text-zinc-500 tracking-wide">
        Dinas Kependudukan &amp; Pencatatan Sipil Kabupaten Dompu NTB © 2026
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
    if (res.data && res.data.image) {
      currentCaptcha.value = res.data.image;
      form.captcha = '';
    }
  } catch (err) {
    console.error('Failed to refresh CAPTCHA', err);
  }
};

const submit = () => {
  form.post(route('admin.login.store'), {
    onFinish: () => {
      form.password = '';
      form.captcha = '';
      refreshCaptcha();
    },
  });
};
</script>
