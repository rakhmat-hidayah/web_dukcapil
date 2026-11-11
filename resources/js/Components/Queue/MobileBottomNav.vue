<template>
  <div>
    <!-- Fixed Mobile Bottom Navigation Bar -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-[70] bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 px-2 py-1.5 shadow-2xl safe-area-pb print:hidden">
      <div class="grid grid-cols-5 items-center text-center text-[10px] font-bold">
        <!-- 1. Layanan -->
        <button
          type="button"
          @click="handleLayananClick"
          class="flex flex-col items-center py-1 transition cursor-pointer"
          :class="isLayananActive ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400'"
        >
          <CreditCard class="w-5 h-5 mb-0.5" />
          <span>Layanan</span>
        </button>

        <!-- 2. Pengaduan -->
        <Link
          :href="route('public.complaint.create')"
          @click="handlePengaduanClick"
          class="flex flex-col items-center py-1 transition cursor-pointer"
          :class="isPengaduanActive ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400'"
        >
          <MessageSquareWarning class="w-5 h-5 mb-0.5" />
          <span>Pengaduan</span>
        </Link>

        <!-- 3. Lacak -->
        <button
          type="button"
          @click="handleLacakClick"
          class="flex flex-col items-center py-1 transition cursor-pointer"
          :class="isTrackingActive ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400'"
        >
          <Search class="w-5 h-5 mb-0.5" />
          <span>Lacak</span>
        </button>

        <!-- 4. Riwayat -->
        <button
          type="button"
          @click="handleRiwayatClick"
          class="flex flex-col items-center py-1 transition cursor-pointer"
          :class="isRiwayatActive ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400'"
        >
          <FolderClock class="w-5 h-5 mb-0.5" />
          <span>Riwayat</span>
        </button>

        <!-- 5. Akun / Login -->
        <Link
          v-if="isApplicantLoggedIn"
          :href="route('applicant.dashboard')"
          @click="handleAkunClick"
          class="flex flex-col items-center py-1 transition cursor-pointer"
          :class="isAkunActive ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400'"
        >
          <UserCheck class="w-5 h-5 mb-0.5" />
          <span class="font-extrabold">Akun</span>
        </Link>
        <Link
          v-else
          :href="route('applicant.login')"
          @click="handleAkunClick"
          class="flex flex-col items-center py-1 transition cursor-pointer"
          :class="isAkunActive ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400'"
        >
          <LogIn class="w-5 h-5 mb-0.5" />
          <span>Login</span>
        </Link>
      </div>
    </nav>

    <!-- MODAL LACAK STATUS PERMOHONAN -->
    <Teleport to="body">
      <div
        v-if="showTrackingModal"
        class="fixed inset-0 z-50 flex flex-col justify-end sm:justify-center items-center p-0 sm:p-4 pt-[max(env(safe-area-inset-top,0px),16px)] pb-[calc(56px+env(safe-area-inset-bottom,0px))] sm:py-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200"
        @click.self="showTrackingModal = false"
      >
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl rounded-b-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-h-full sm:max-h-[85vh] flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-200">
          <div class="shrink-0 p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-xs">
                <Search class="w-4 h-4" />
              </div>
              <div>
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white leading-tight">
                  Lacak Status Permohonan
                </h3>
                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">
                  KTP-el Online Dompu
                </span>
              </div>
            </div>
            <button
              type="button"
              @click="showTrackingModal = false"
              class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 transition cursor-pointer"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-5 sm:p-6 space-y-4">

          <!-- KONDISI 1: Belum Login Pemohon -->
          <div v-if="!isApplicantLoggedIn" class="py-1 space-y-3.5">
            <div class="p-3.5 rounded-2xl bg-amber-500/10 dark:bg-amber-500/15 border border-amber-300/80 dark:border-amber-600/50 flex items-start gap-3">
              <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                <Lock class="w-4 h-4" />
              </div>
              <div class="space-y-1">
                <h4 class="text-xs font-black text-amber-950 dark:text-amber-100">
                  Login Akun Pemohon Diperlukan
                </h4>
                <p class="text-[11px] text-amber-900/80 dark:text-amber-200 leading-relaxed">
                  Untuk melindungi kerahasiaan data kependudukan dan berkas pribadi Anda, pelacakan riwayat permohonan mewajibkan Anda masuk ke Akun Pemohon terlebih dahulu.
                </p>
              </div>
            </div>

            <div class="space-y-2 pt-1">
              <Link
                :href="route('applicant.login')"
                @click="showTrackingModal = false"
                class="w-full py-2.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-md shadow-blue-600/25 flex items-center justify-center gap-2 cursor-pointer"
              >
                <LogIn class="w-4 h-4" />
                <span>Masuk ke Akun Pemohon</span>
              </Link>
              <Link
                :href="route('applicant.register')"
                @click="showTrackingModal = false"
                class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer"
              >
                <span>Belum punya akun? Daftar Baru</span>
              </Link>
            </div>

            <!-- Alternative Quick Search with Nomor Registrasi -->
            <div class="relative flex py-1 items-center">
              <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
              <span class="flex-shrink mx-2 text-[10px] text-slate-400 uppercase font-bold tracking-wider">atau cari nomor registrasi</span>
              <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
            </div>

            <form @submit.prevent="submitSearch" class="space-y-3">
              <div class="relative">
                <input
                  v-model="trackingForm.query"
                  type="text"
                  placeholder="Contoh: REG-2611-XXXXXX"
                  required
                  class="w-full pl-3.5 pr-9 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span
                  v-if="trackingForm.query"
                  @click="trackingForm.query = ''"
                  class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                  <X class="w-4 h-4" />
                </span>
              </div>
              <button
                type="submit"
                :disabled="trackingForm.processing || !trackingForm.query.trim()"
                class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Search class="w-3.5 h-3.5" />
                <span>{{ trackingForm.processing ? 'Mencari...' : 'Lacak Status Sekarang' }}</span>
              </button>
            </form>
          </div>

          <!-- KONDISI 2: Sudah Login Pemohon -->
          <div v-else class="space-y-3.5">
            <div class="p-3 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 flex items-center justify-between gap-2 text-xs">
              <div class="flex items-center gap-2 min-w-0">
                <UserCheck class="w-4 h-4 text-blue-600 shrink-0" />
                <span class="truncate font-semibold text-slate-800 dark:text-slate-200">
                  {{ $page.props.auth.applicant.name }} ({{ $page.props.auth.applicant.masked_nik }})
                </span>
              </div>
              <Link
                :href="route('applicant.dashboard')"
                @click="showTrackingModal = false"
                class="text-blue-600 dark:text-blue-400 font-bold hover:underline shrink-0 text-[11px]"
              >
                Dashboard &rarr;
              </Link>
            </div>

            <Link
              :href="route('applicant.dashboard')"
              @click="showTrackingModal = false"
              class="w-full py-2.5 px-4 rounded-2xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-2 cursor-pointer"
            >
              <FolderClock class="w-4 h-4 text-amber-400" />
              <span>Buka Seluruh Riwayat Permohonan Saya</span>
            </Link>

            <div class="relative flex py-1 items-center">
              <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
              <span class="flex-shrink mx-2 text-[10px] text-slate-400 uppercase font-bold tracking-wider">atau cari nomor registrasi</span>
              <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
            </div>

            <form @submit.prevent="submitSearch" class="space-y-3">
              <div class="relative">
                <input
                  v-model="trackingForm.query"
                  type="text"
                  placeholder="Contoh: REG-2611-XXXXXX"
                  required
                  class="w-full pl-3.5 pr-9 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span
                  v-if="trackingForm.query"
                  @click="trackingForm.query = ''"
                  class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                  <X class="w-4 h-4" />
                </span>
              </div>
              <button
                type="submit"
                :disabled="trackingForm.processing || !trackingForm.query.trim()"
                class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-md shadow-blue-600/25 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Search class="w-3.5 h-3.5" />
                <span>{{ trackingForm.processing ? 'Mencari...' : 'Lacak Status Sekarang' }}</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage, useForm } from '@inertiajs/vue3'
import {
  CreditCard,
  MessageSquareWarning,
  Search,
  FolderClock,
  UserCheck,
  LogIn,
  X,
  Lock,
} from '@lucide/vue'

const page = usePage()

const showTrackingModal = ref(false)

const trackingForm = useForm({
  query: '',
})

function submitSearch() {
  if (!trackingForm.query.trim()) return
  trackingForm.post(route('queue.check'), {
    onSuccess: () => {
      showTrackingModal.value = false
    },
  })
}

const isApplicantLoggedIn = computed(() => !!page.props.auth?.applicant)

const currentUrl = computed(() => page.url || '')

const isPengaduanActive = computed(() => {
  if (showTrackingModal.value) return false
  return currentUrl.value.startsWith('/pengaduan')
})

const isLayananActive = computed(() => {
  if (showTrackingModal.value) return false
  const url = currentUrl.value
  return (
    url === '/layanan/antrian' ||
    url.startsWith('/layanan/antrian?') ||
    url.startsWith('/layanan/antrian-ktpel') ||
    url.startsWith('/layanan/antrian/daftar') ||
    url === '/layanan' ||
    url.startsWith('/layanan?')
  )
})

const isTrackingActive = computed(() => {
  if (showTrackingModal.value) return true
  const url = currentUrl.value
  return url.startsWith('/layanan/antrian/status') || url.startsWith('/lacak')
})

const isRiwayatActive = computed(() => {
  if (showTrackingModal.value) return false
  const url = currentUrl.value
  return url.startsWith('/layanan/antrian/riwayat')
})

const isAkunActive = computed(() => {
  if (showTrackingModal.value) return false
  const url = currentUrl.value
  return (
    url.startsWith('/layanan/pemohon') ||
    url.startsWith('/pemohon')
  )
})

function handlePengaduanClick() {
  showTrackingModal.value = false
}

function handleLacakClick() {
  showTrackingModal.value = !showTrackingModal.value
}

function handleAkunClick() {
  showTrackingModal.value = false
}

function handleLayananClick() {
  showTrackingModal.value = false
  const url = currentUrl.value
  if (url === '/layanan/antrian' || url.startsWith('/layanan/antrian-ktpel')) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } else {
    router.visit(route('queue.index'))
  }
}

function handleRiwayatClick() {
  showTrackingModal.value = false
  if (isApplicantLoggedIn.value) {
    const el = document.getElementById('riwayat') || document.getElementById('riwayat-section')
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' })
    } else {
      router.visit(route('applicant.dashboard'))
    }
  } else {
    router.visit(route('applicant.login'))
  }
}
</script>
