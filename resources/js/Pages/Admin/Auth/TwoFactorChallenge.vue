<template>
  <Head title="Verifikasi 2FA — Dukcapil Dompu" />

  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-950 via-blue-950 to-zinc-950 p-4 sm:p-6 relative overflow-hidden font-sans">
    <!-- Decorative Glowing Radial Blobs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="w-full max-w-md z-10">
      <!-- Outer Premium Glass Card Container -->
      <div class="bg-zinc-900/90 backdrop-blur-2xl border border-zinc-800/90 rounded-3xl shadow-2xl shadow-black/80 overflow-hidden transition-all duration-300">
        <div class="p-8 sm:p-10">
          
          <!-- Security Shield Header -->
          <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-3xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-blue-500/10">
              <ShieldCheck class="w-9 h-9 text-blue-400" />
            </div>
            <h2 class="text-lg font-black text-white tracking-tight">Verifikasi Dua Faktor (2FA)</h2>
            <p class="text-xs font-semibold text-zinc-400 mt-1">
              Selamat datang, <span class="text-blue-400 font-mono">@{{ username }}</span> ({{ name }})
            </p>
          </div>

          <!-- Description Hint -->
          <div class="mb-6 text-center">
            <p v-if="!useRecovery" class="text-xs text-zinc-300 leading-relaxed bg-zinc-950/60 p-3.5 rounded-2xl border border-zinc-800">
              Buka aplikasi Authenticator Anda (Google Authenticator, Microsoft Authenticator, 2FAS, Aegis, dll.) dan masukkan <strong class="text-white">kode 6-digit</strong>.
            </p>
            <p v-else class="text-xs text-amber-300 leading-relaxed bg-amber-950/40 p-3.5 rounded-2xl border border-amber-900/40">
              Masukkan salah satu dari <strong class="text-white">10 Kode Pemulihan</strong> sekali pakai (format: <code class="font-mono text-amber-200">DKCP-XXXX-XXXX</code>).
            </p>
          </div>

          <!-- Lockout / Too Many Attempts Banner -->
          <div v-if="form.errors.code && form.errors.code.includes('terkunci')" class="p-3.5 bg-rose-950/80 border border-rose-500/40 rounded-2xl text-xs font-semibold text-rose-300 mb-5 shadow-sm">
            ⚠️ {{ form.errors.code }}
          </div>

          <!-- Challenge Form -->
          <form @submit.prevent="submit" class="space-y-5">
            <!-- OTP Input (6 Digit) -->
            <div v-if="!useRecovery">
              <label for="otp-code" class="block text-[11px] font-extrabold uppercase tracking-wider text-zinc-300 mb-1.5 text-center">
                Kode TOTP 6-Digit
              </label>
              <div class="relative">
                <input
                  id="otp-code"
                  ref="codeInputRef"
                  type="text"
                  v-model="form.code"
                  required
                  maxlength="6"
                  placeholder="000000"
                  autocomplete="one-time-code"
                  inputmode="numeric"
                  class="w-full px-4 py-3.5 bg-zinc-950/90 border border-zinc-700/80 rounded-2xl text-white placeholder-zinc-600 text-center font-mono font-bold text-2xl tracking-[0.4em] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner"
                  @input="handleOtpInput"
                />
              </div>
              <span v-if="form.errors.code && !form.errors.code.includes('terkunci')" class="text-xs font-semibold text-rose-400 mt-1.5 block text-center">
                {{ form.errors.code }}
              </span>
            </div>

            <!-- Recovery Code Input -->
            <div v-else>
              <label for="recovery-code" class="block text-[11px] font-extrabold uppercase tracking-wider text-zinc-300 mb-1.5 text-center">
                Kode Pemulihan Darurat
              </label>
              <div class="relative">
                <input
                  id="recovery-code"
                  type="text"
                  v-model="form.code"
                  required
                  placeholder="DKCP-XXXX-XXXX"
                  autocomplete="off"
                  class="w-full px-4 py-3 bg-zinc-950/90 border border-zinc-700/80 rounded-2xl text-white placeholder-zinc-600 text-center font-mono font-bold text-base uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition shadow-inner"
                  @input="form.code = form.code.toUpperCase()"
                />
              </div>
              <span v-if="form.errors.code && !form.errors.code.includes('terkunci')" class="text-xs font-semibold text-rose-400 mt-1.5 block text-center">
                {{ form.errors.code }}
              </span>
            </div>

            <!-- Remember Device 6 Hours Option -->
            <div class="pt-1">
              <label class="flex items-center gap-2.5 cursor-pointer text-zinc-300 hover:text-white transition select-none font-medium bg-zinc-950/70 p-3 rounded-2xl border border-zinc-800 text-xs">
                <input type="checkbox" v-model="form.remember" class="accent-blue-500 rounded border-zinc-700 w-4 h-4 shrink-0 cursor-pointer" />
                <span class="leading-tight">Ingat perangkat ini (bebas OTP selama 6 jam)</span>
              </label>
            </div>

            <!-- Submit Verification Button -->
            <button
              type="submit"
              :disabled="form.processing || !form.code"
              class="w-full py-3.5 bg-gradient-to-r from-blue-600 via-primary-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black text-sm rounded-2xl shadow-xl shadow-blue-600/30 active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50 disabled:scale-100 cursor-pointer"
            >
              <span v-if="form.processing" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memverifikasi Kode...
              </span>
              <span v-else>Verifikasi &amp; Masuk</span>
            </button>
          </form>

          <!-- Toggle Recovery Code Mode -->
          <div class="mt-6 text-center border-t border-zinc-800/80 pt-4">
            <button
              type="button"
              @click="toggleMode"
              class="text-xs text-zinc-400 hover:text-white transition underline font-medium"
            >
              {{ !useRecovery ? 'Gunakan Kode Pemulihan Darurat (Recovery Code)' : 'Gunakan Kode OTP dari Aplikasi Authenticator' }}
            </button>
          </div>

          <!-- Cancel & Return to Login -->
          <div class="mt-3 text-center">
            <Link
              :href="route('admin.login')"
              method="post"
              as="button"
              class="text-[11px] text-zinc-500 hover:text-zinc-300 transition"
            >
              ← Batal &amp; Kembali ke Halaman Login
            </Link>
          </div>
        </div>
      </div>
      
      <!-- Footer Copyright -->
      <div class="text-center mt-6 text-xs font-semibold text-zinc-500 tracking-wide">
        Dinas Kependudukan &amp; Pencatatan Sipil Kabupaten Dompu © 2026
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';

const props = defineProps({
  username: String,
  name:     String,
  remember: {
    type: Boolean,
    default: true,
  },
});

const useRecovery    = ref(false);
const codeInputRef   = ref(null);

const form = useForm({
  code: '',
  remember: props.remember ?? true,
});

const toggleMode = () => {
  useRecovery.value = !useRecovery.value;
  form.code = '';
  form.clearErrors();
  nextTick(() => {
    const el = document.getElementById(useRecovery.value ? 'recovery-code' : 'otp-code');
    if (el) el.focus();
  });
};

const handleOtpInput = () => {
  form.code = form.code.replace(/[^0-9]/g, '');
  if (form.code.length === 6) {
    submit();
  }
};

const submit = () => {
  form.post(route('admin.login.two-factor.store'), {
    onFinish: () => {
      form.code = '';
    },
  });
};

onMounted(() => {
  nextTick(() => {
    if (codeInputRef.value) codeInputRef.value.focus();
  });
});
</script>
