<template>
  <Head title="Verifikasi Email OTP - Disdukcapil Kabupaten Dompu" />

  <PublicLayout :hide-footer="true">
    <div class="py-2.5 sm:py-10 max-w-md mx-auto px-3 sm:px-4 space-y-2.5 sm:space-y-4 w-full">
      <!-- OTP Card -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl p-4 sm:p-7 border border-slate-200 dark:border-slate-800 shadow-xl space-y-3.5 sm:space-y-5 w-full max-w-full overflow-hidden">
        <div class="text-center space-y-1 sm:space-y-2">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto shadow-inner">
            <MailCheck class="w-5 h-5 sm:w-6 sm:h-6" />
          </div>
          <h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white">
            Verifikasi Email
          </h1>
          <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">
            Kode verifikasi 6 digit telah dikirimkan ke email:
          </p>
          <p class="text-xs sm:text-sm font-bold text-blue-600 dark:text-blue-400 font-mono">
            {{ email }}
          </p>
        </div>

        <form @submit.prevent="submitOtp" class="space-y-3 sm:space-y-4 pt-1">
          <div class="space-y-1 text-center">
            <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
              Masukkan 6 Digit Kode OTP
            </label>
            <input
              v-model="form.otp"
              type="text"
              maxlength="6"
              inputmode="numeric"
              placeholder="123456"
              class="w-full text-center px-3 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xl sm:text-2xl font-mono tracking-[0.4em] font-black text-blue-600 dark:text-blue-400 outline-none focus:ring-2 focus:ring-blue-500"
              required
              autofocus
            />
            <p v-if="form.errors.otp" class="text-xs text-rose-500 font-semibold pt-1">
              {{ form.errors.otp }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing || form.otp.length !== 6"
            class="w-full py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm transition shadow-md shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
          >
            <span v-if="form.processing">Memverifikasi...</span>
            <span v-else>Verifikasi Sekarang</span>
            <CheckCircle2 class="w-4 h-4" />
          </button>
        </form>

        <!-- Resend OTP Section -->
        <div class="pt-2.5 sm:pt-4 border-t border-slate-100 dark:border-slate-800 text-center text-[11px] sm:text-xs space-y-1.5 sm:space-y-2">
          <p class="text-slate-500 dark:text-slate-400">Tidak menerima kode di kotak masuk maupun spam?</p>
          <button
            type="button"
            @click="resendOtp"
            :disabled="resendForm.processing || cooldown > 0"
            class="font-bold text-blue-600 dark:text-blue-400 hover:underline disabled:opacity-50 cursor-pointer"
          >
            <span v-if="cooldown > 0">Kirim ulang dalam {{ cooldown }} detik</span>
            <span v-else-if="resendForm.processing">Mengirimkan kode baru...</span>
            <span v-else>Kirim Ulang Kode OTP</span>
          </button>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { CheckCircle2, MailCheck } from '@lucide/vue'

const props = defineProps({
  email: {
    type: String,
    required: true,
  },
})

const form = useForm({
  email: props.email,
  otp: '',
})

const resendForm = useForm({
  email: props.email,
})

const cooldown = ref(60)
let timer = null

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.scrollTo({ left: 0, top: 0, behavior: 'instant' })
  }
  timer = setInterval(() => {
    if (cooldown.value > 0) {
      cooldown.value--
    }
  }, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

const submitOtp = () => {
  form.post(route('applicant.verify-otp.post'))
}

const resendOtp = () => {
  resendForm.post(route('applicant.resend-otp'), {
    onSuccess: () => {
      cooldown.value = 60
    },
  })
}
</script>
