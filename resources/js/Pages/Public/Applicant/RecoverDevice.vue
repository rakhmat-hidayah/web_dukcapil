<template>
  <Head title="Pemulihan Akun & Ganti Perangkat - Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="py-2.5 sm:py-10 max-w-md mx-auto px-3 sm:px-4 space-y-2.5 sm:space-y-4 w-full">
      <div class="flex items-center justify-between">
        <Link
          :href="route('applicant.login')"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition"
        >
          <ArrowLeft class="w-3.5 h-3.5" />
          <span>Kembali ke Halaman Masuk</span>
        </Link>
      </div>

      <!-- Card -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl p-4 sm:p-7 border border-slate-200 dark:border-slate-800 shadow-xl space-y-3.5 sm:space-y-5 w-full max-w-full overflow-hidden">
        <div class="text-center space-y-1 sm:space-y-2">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto shadow-inner">
            <Smartphone class="w-5 h-5 sm:w-6 sm:h-6" />
          </div>
          <h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white">
            Pemulihan Perangkat
          </h1>
          <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">
            Lepaskan perangkat lama dan aktifkan akun Anda pada browser ini melalui verifikasi keamanan bertingkat.
          </p>
        </div>

        <!-- STEP 1: Request OTP -->
        <div v-if="!step2Active" class="space-y-3 sm:space-y-4">
          <div class="p-2.5 sm:p-3.5 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 text-[11px] sm:text-xs text-slate-600 dark:text-slate-300 space-y-1">
            <p class="font-bold text-slate-800 dark:text-slate-200">Kapan fitur ini digunakan?</p>
            <ul class="list-disc pl-4 space-y-0.5 text-[10.5px] sm:text-[11px] text-slate-500 dark:text-slate-400">
              <li>HP lama hilang, rusak, atau berganti perangkat baru.</li>
              <li>Riwayat/cookie browser lama terhapus.</li>
              <li>Akun Anda terkunci karena terikat di perangkat lain.</li>
            </ul>
          </div>

          <form @submit.prevent="submitStep1" class="space-y-3 sm:space-y-4">
            <div class="space-y-1">
              <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
                Nomor Induk Kependudukan (NIK)
              </label>
              <input
                v-model="step1Form.nik"
                type="text"
                maxlength="16"
                inputmode="numeric"
                placeholder="16 digit NIK..."
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-mono text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-amber-500"
                required
              />
              <p v-if="step1Form.errors.nik" class="text-xs text-rose-500 font-semibold">
                {{ step1Form.errors.nik }}
              </p>
            </div>

            <div class="space-y-1">
              <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
                Password Akun
              </label>
              <input
                v-model="step1Form.password"
                type="password"
                placeholder="Password akun Anda..."
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-amber-500"
                required
              />
              <p v-if="step1Form.errors.password" class="text-xs text-rose-500 font-semibold">
                {{ step1Form.errors.password }}
              </p>
            </div>

            <button
              type="submit"
              :disabled="step1Form.processing"
              class="w-full py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs sm:text-sm transition shadow-md shadow-amber-600/20 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer mt-1"
            >
              <span v-if="step1Form.processing">Memeriksa Data...</span>
              <span v-else>Verifikasi & Kirim Kode OTP</span>
              <ArrowRight class="w-4 h-4" />
            </button>
          </form>
        </div>

        <!-- STEP 2: Confirm OTP -->
        <div v-else class="space-y-3 sm:space-y-4">
          <div class="p-2.5 sm:p-3.5 rounded-xl sm:rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 text-xs text-blue-900 dark:text-blue-300 space-y-1">
            <p class="font-bold">Kode OTP Terkirim</p>
            <p class="text-[10.5px] sm:text-[11px] text-blue-800 dark:text-blue-300">
              Kode OTP 6 digit telah dikirimkan ke alamat email terdaftar: <strong>{{ maskedEmail }}</strong>. Masukkan kode tersebut untuk menyelesaikan pemindahan perangkat.
            </p>
          </div>

          <form @submit.prevent="submitStep2" class="space-y-3 sm:space-y-4">
            <div class="space-y-1 text-center">
              <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
                Kode OTP Pemulihan (6 Digit)
              </label>
              <input
                v-model="step2Form.otp"
                type="text"
                maxlength="6"
                inputmode="numeric"
                placeholder="123456"
                class="w-full text-center px-4 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xl sm:text-2xl font-mono tracking-[0.4em] font-black text-amber-600 dark:text-amber-400 outline-none focus:ring-2 focus:ring-amber-500"
                required
                autofocus
              />
              <p v-if="step2Form.errors.otp" class="text-xs text-rose-500 font-semibold pt-1">
                {{ step2Form.errors.otp }}
              </p>
            </div>

            <button
              type="submit"
              :disabled="step2Form.processing || step2Form.otp.length !== 6"
              class="w-full py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs sm:text-sm transition shadow-md shadow-amber-600/20 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer mt-1"
            >
              <span v-if="step2Form.processing">Memindahkan Perangkat...</span>
              <span v-else>Lepaskan Perangkat Lama & Masuk</span>
              <CheckCircle2 class="w-4 h-4" />
            </button>
          </form>
        </div>

        <!-- Help Notice -->
        <div class="pt-2.5 sm:pt-4 border-t border-slate-100 dark:border-slate-800 text-center text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">
          <span>Tidak memiliki akses ke email atau lupa password? </span>
          <p class="mt-1 text-[10.5px] sm:text-[11px] text-slate-600 dark:text-slate-400">
            Silakan datang langsung ke loket pelayanan Disdukcapil Kabupaten Dompu untuk verifikasi identitas fisik oleh petugas.
          </p>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { ArrowLeft, ArrowRight, Smartphone, CheckCircle2 } from '@lucide/vue'

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.scrollTo({ left: 0, top: 0, behavior: 'instant' })
  }
})

const page = usePage()
const step2Active = ref(Boolean(page.props.flash?.otpSent))
const maskedEmail = ref(page.props.flash?.maskedEmail || '')

const step1Form = useForm({
  nik: '',
  password: '',
})

const step2Form = useForm({
  otp: '',
})

const submitStep1 = () => {
  step1Form.post(route('applicant.recover.send-otp'), {
    onSuccess: (res) => {
      step2Active.value = true
      if (res.props.flash?.maskedEmail) {
        maskedEmail.value = res.props.flash.maskedEmail
      }
    },
  })
}

const submitStep2 = () => {
  step2Form.post(route('applicant.recover.complete'))
}
</script>
