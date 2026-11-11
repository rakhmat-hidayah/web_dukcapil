<template>
  <Head title="Masuk Akun Pemohon - Disdukcapil Kabupaten Dompu" />

  <PublicLayout :hide-footer="true">
    <div class="py-2.5 sm:py-10 max-w-md mx-auto px-3 sm:px-4 space-y-2.5 sm:space-y-4 w-full">
      <!-- Login Card -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl p-4 sm:p-7 border border-slate-200 dark:border-slate-800 shadow-xl space-y-3.5 sm:space-y-5 w-full max-w-full overflow-hidden">
        <!-- Optional Fullscreen Toggle Bar -->
        <div v-if="!isStandalone" class="flex items-center justify-between text-xs text-slate-400">
          <span class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Portal Pemohon</span>
          <button
            type="button"
            @click="handleFullscreenClick"
            class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] font-semibold text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition cursor-pointer"
            title="Tampilkan / Sembunyikan Bilah Browser"
          >
            <Maximize2 v-if="!isFullscreen" class="w-3 h-3" />
            <Minimize2 v-else class="w-3 h-3" />
            <span>{{ isFullscreen ? 'Keluar Layar Penuh' : 'Mode Layar Penuh' }}</span>
          </button>
        </div>

        <div class="text-center space-y-1 sm:space-y-2">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto shadow-inner">
            <Lock class="w-5 h-5 sm:w-6 sm:h-6" />
          </div>
          <h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white">
            Masuk Akun Pemohon
          </h1>
          <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">
            Gunakan NIK dan password akun Anda untuk mengakses layanan online.
          </p>
        </div>

        <!-- Maintenance Mode Alert Banner (If Layanan Ditutup) -->
        <div
          v-if="$page.props.queue_maintenance?.is_active"
          class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-800 text-amber-950 dark:text-amber-200 shadow-sm space-y-2 animate-fade-in"
        >
          <div class="flex items-start gap-2.5">
            <div class="w-7 h-7 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
              <AlertCircle class="w-4 h-4" />
            </div>
            <div class="space-y-1 min-w-0 flex-1">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-600 text-white leading-none">
                  Layanan Ditutup Sementara
                </span>
                <span class="text-xs font-black text-amber-900 dark:text-amber-100">
                  {{ $page.props.queue_maintenance.title || 'Layanan Pendaftaran Sedang Dinonaktifkan' }}
                </span>
              </div>
              <p class="text-[11px] sm:text-xs leading-relaxed text-amber-800 dark:text-amber-300">
                {{ $page.props.queue_maintenance.message }}
              </p>
              <div v-if="$page.props.queue_maintenance.estimated_end" class="pt-1 flex items-center gap-1.5 text-[10px] sm:text-[11px] text-amber-900 dark:text-amber-200 font-bold">
                <Clock class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0" />
                <span>Estimasi Dibuka: <strong class="underline">{{ $page.props.queue_maintenance.estimated_end }}</strong></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Flash Success Notification (Registration / Verification Success) -->
        <div
          v-if="$page.props.flash?.success"
          class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 flex items-start gap-2.5 text-emerald-900 dark:text-emerald-100 shadow-sm animate-fade-in"
        >
          <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
          <div class="space-y-0.5">
            <h4 class="text-[11px] sm:text-xs font-bold text-emerald-800 dark:text-emerald-200 uppercase tracking-wider">
              Pendaftaran Berhasil!
            </h4>
            <p class="text-[11px] sm:text-xs leading-relaxed text-emerald-700 dark:text-emerald-300">
              {{ $page.props.flash.success }}
            </p>
          </div>
        </div>

        <!-- Flash Warning / Info Notification -->
        <div
          v-else-if="$page.props.flash?.warning || $page.props.flash?.info"
          class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 flex items-start gap-2.5 text-amber-900 dark:text-amber-100 shadow-sm"
        >
          <AlertCircle class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
          <div class="space-y-0.5">
            <p class="text-[11px] sm:text-xs leading-relaxed text-amber-800 dark:text-amber-200">
              {{ $page.props.flash.warning || $page.props.flash.info }}
            </p>
          </div>
        </div>

        <!-- Flash Error Notification -->
        <div
          v-else-if="$page.props.flash?.error"
          class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 flex items-start gap-2.5 text-rose-900 dark:text-rose-100 shadow-sm"
        >
          <AlertCircle class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" />
          <div class="space-y-0.5">
            <p class="text-[11px] sm:text-xs leading-relaxed text-rose-800 dark:text-rose-200">
              {{ $page.props.flash.error }}
            </p>
          </div>
        </div>

        <!-- Single Browser Active Security Info Pill -->
        <div class="px-3 py-1.5 sm:py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 flex items-center gap-2 text-[10px] sm:text-[11px] text-slate-600 dark:text-slate-300">
          <ShieldAlert class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" />
          <span class="leading-tight">Akses aman: 1 akun aktif pada <strong>1 perangkat/browser</strong>.</span>
        </div>

        <form @submit.prevent="submitLogin" class="space-y-3 sm:space-y-4">
          <!-- NIK Input -->
          <div class="space-y-1">
            <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
              Nomor Induk Kependudukan (NIK)
            </label>
            <div class="relative">
              <input
                v-model="form.nik"
                type="text"
                maxlength="16"
                inputmode="numeric"
                placeholder="16 digit angka sesuai KTP/KK..."
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-mono text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
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
              <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
                Password
              </label>
              <Link
                :href="route('applicant.recover')"
                class="text-[10px] sm:text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline"
              >
                Ganti HP / Lupa Password?
              </Link>
            </div>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Masukkan password Anda..."
                class="w-full pl-3.5 pr-10 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-2.5 sm:top-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
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
            class="w-full py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm transition shadow-md shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer mt-1"
          >
            <span v-if="form.processing">Memeriksa Kredensial...</span>
            <span v-else>Masuk ke Layanan</span>
            <ArrowRight class="w-4 h-4" />
          </button>
        </form>

        <!-- Register Link (Restricted during maintenance) -->
        <div class="pt-2.5 sm:pt-4 border-t border-slate-100 dark:border-slate-800 text-center text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">
          <div v-if="$page.props.queue_maintenance?.is_active" class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 text-amber-800 dark:text-amber-300 font-semibold flex items-center justify-center gap-1.5">
            <Lock class="w-3.5 h-3.5 text-amber-600 shrink-0" />
            <span>Pendaftaran akun baru ditutup sementara selama masa pemeliharaan sistem.</span>
          </div>
          <template v-else>
            <span>Belum memiliki akun pemohon? </span>
            <Link :href="route('applicant.register')" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
              Daftar Sekarang &rarr;
            </Link>
          </template>
        </div>
      </div>
    </div>

    <!-- Modal Panduan Mode Layar Penuh -->
    <MobileFullscreenGuideModal v-model="showFullscreenModal" />
  </PublicLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import MobileFullscreenGuideModal from '@/Components/MobileFullscreenGuideModal.vue'
import { ArrowRight, Lock, Eye, EyeOff, ShieldAlert, CheckCircle2, AlertCircle, Clock, Maximize2, Minimize2 } from '@lucide/vue'

const showPassword = ref(false)
const isFullscreen = ref(false)
const canFullscreen = ref(false)
const isStandalone = ref(false)
const showFullscreenModal = ref(false)

const handleFullscreenClick = () => {
  if (canFullscreen.value) {
    toggleFullscreen()
  } else {
    showFullscreenModal.value = true
  }
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.scrollTo({ left: 0, top: 0, behavior: 'instant' })
    isStandalone.value = window.matchMedia('(display-mode: standalone)').matches || 
                         window.navigator.standalone === true ||
                         document.referrer.includes('android-app://')
    canFullscreen.value = typeof document !== 'undefined' && !!(document.fullscreenEnabled || document.webkitFullscreenEnabled)
    
    document.addEventListener('fullscreenchange', () => {
      isFullscreen.value = !!document.fullscreenElement
    })
    document.addEventListener('webkitfullscreenchange', () => {
      isFullscreen.value = !!document.webkitFullscreenElement
    })
  }
})

const toggleFullscreen = () => {
  if (typeof document === 'undefined') return
  if (!document.fullscreenElement && !document.webkitFullscreenElement) {
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen().catch(() => {})
    } else if (document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen()
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen().catch(() => {})
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen()
    }
  }
}

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
