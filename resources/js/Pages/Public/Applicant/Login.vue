<template>
  <Head title="Masuk Akun Pemohon - Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="py-6 sm:py-16 max-w-md mx-auto px-2.5 sm:px-4 space-y-5 sm:space-y-6 w-full">
      <!-- Breadcrumb navigation -->
      <div class="flex items-center justify-between">
        <Link
          :href="route('queue.index')"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition"
        >
          <ArrowLeft class="w-3.5 h-3.5" />
          <span>Kembali ke Portal Antrian</span>
        </Link>
      </div>

      <!-- Login Card -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5 sm:space-y-6 w-full max-w-full overflow-hidden">
        <div class="text-center space-y-2">
          <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto shadow-inner">
            <Lock class="w-7 h-7" />
          </div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Masuk Akun Pemohon
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Gunakan Nomor Induk Kependudukan (NIK) dan password akun Anda untuk mengakses layanan online.
          </p>
        </div>

        <!-- Flash Success Notification (Registration / Verification Success) -->
        <div
          v-if="$page.props.flash?.success"
          class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 flex items-start gap-3 text-emerald-900 dark:text-emerald-100 shadow-sm animate-fade-in"
        >
          <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
          <div class="space-y-1">
            <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-200 uppercase tracking-wider">
              Pendaftaran Berhasil!
            </h4>
            <p class="text-xs leading-relaxed text-emerald-700 dark:text-emerald-300">
              {{ $page.props.flash.success }}
            </p>
          </div>
        </div>

        <!-- Flash Warning / Info Notification -->
        <div
          v-else-if="$page.props.flash?.warning || $page.props.flash?.info"
          class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 flex items-start gap-3 text-amber-900 dark:text-amber-100 shadow-sm"
        >
          <AlertCircle class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
          <div class="space-y-1">
            <p class="text-xs leading-relaxed text-amber-800 dark:text-amber-200">
              {{ $page.props.flash.warning || $page.props.flash.info }}
            </p>
          </div>
        </div>

        <!-- Flash Error Notification -->
        <div
          v-else-if="$page.props.flash?.error"
          class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 flex items-start gap-3 text-rose-900 dark:text-rose-100 shadow-sm"
        >
          <AlertCircle class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" />
          <div class="space-y-1">
            <p class="text-xs leading-relaxed text-rose-800 dark:text-rose-200">
              {{ $page.props.flash.error }}
            </p>
          </div>
        </div>

        <!-- Single Browser Active Security Info Pill -->
        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 flex items-start gap-2.5 text-[11px] text-slate-600 dark:text-slate-300">
          <ShieldAlert class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
          <span>Satu akun hanya dapat aktif pada <strong>satu browser/perangkat</strong> secara bersamaan. Sesi Anda akan tetap tersimpan secara aman di perangkat ini.</span>
        </div>

        <form @submit.prevent="submitLogin" class="space-y-4 pt-1">
          <!-- NIK Input -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Nomor Induk Kependudukan (NIK)
            </label>
            <div class="relative">
              <input
                v-model="form.nik"
                type="text"
                maxlength="16"
                inputmode="numeric"
                placeholder="16 digit angka sesuai KTP/KK..."
                class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <p v-if="form.errors.nik" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.nik }}
            </p>
          </div>

          <!-- Password Input -->
          <div class="space-y-1">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Password
              </label>
              <Link
                :href="route('applicant.recover')"
                class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline"
              >
                Ganti HP / Lupa Password?
              </Link>
            </div>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Masukkan password Anda..."
                class="w-full pl-4 pr-11 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
            <p v-if="form.errors.password" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.password }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm transition shadow-lg shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
          >
            <span v-if="form.processing">Memeriksa Kredensial...</span>
            <span v-else>Masuk ke Layanan</span>
            <ArrowRight class="w-4 h-4" />
          </button>
        </form>

        <!-- Register Link -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
          <span>Belum memiliki akun pemohon? </span>
          <Link :href="route('applicant.register')" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
            Daftar Sekarang &rarr;
          </Link>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { ArrowLeft, ArrowRight, Lock, Eye, EyeOff, ShieldAlert, CheckCircle2, AlertCircle } from '@lucide/vue'

const showPassword = ref(false)

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.scrollTo({ left: 0, top: 0, behavior: 'instant' })
  }
})

const getInitialNik = () => {
  if (typeof window !== 'undefined') {
    const params = new URLSearchParams(window.location.search)
    return params.get('nik') || ''
  }
  return ''
}

const form = useForm({
  nik: getInitialNik(),
  password: '',
})

const submitLogin = () => {
  form.post(route('applicant.login.post'), {
    onFinish: () => form.reset('password'),
  })
}
</script>
