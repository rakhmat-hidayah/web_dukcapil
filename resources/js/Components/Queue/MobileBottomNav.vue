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

        <!-- 2. Syarat -->
        <button
          type="button"
          @click="handleSyaratClick"
          class="flex flex-col items-center py-1 transition cursor-pointer"
          :class="isSyaratActive ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400'"
        >
          <ClipboardList class="w-5 h-5 mb-0.5" />
          <span>Syarat</span>
        </button>

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

    <!-- MODAL PERSYARATAN BERKAS KTP-EL -->
    <Teleport to="body">
      <div
        v-if="showRequirementsModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200"
        @click.self="showRequirementsModal = false"
      >
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-t-3xl rounded-b-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 mb-[calc(56px+env(safe-area-inset-bottom,0px))] sm:mb-0 max-h-[calc(85vh-56px)] sm:max-h-[85vh] flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-200">
          <!-- Modal Header -->
          <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <ClipboardList class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white leading-tight">
                  Persyaratan Berkas KTP-el
                </h3>
                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">
                  Disdukcapil Kabupaten Dompu
                </span>
              </div>
            </div>
            <button
              type="button"
              @click="showRequirementsModal = false"
              class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition cursor-pointer"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Modal Body: Daftar Layanan & Persyaratan (Sync Otomatis dengan CMS) -->
          <div class="p-4 sm:p-5 overflow-y-auto space-y-4 text-xs">
            <div
              v-if="!queueServices || queueServices.length === 0"
              class="py-8 text-center text-slate-400"
            >
              <p>Memuat informasi persyaratan layanan...</p>
            </div>

            <div
              v-for="srv in queueServices"
              :key="srv.id"
              class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 space-y-3"
            >
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                  <span
                    class="w-2.5 h-2.5 rounded-full shrink-0"
                    :style="{ backgroundColor: srv.color || '#2563eb' }"
                  ></span>
                  <h4 class="font-black text-sm text-slate-900 dark:text-white truncate">
                    {{ srv.name }}
                  </h4>
                </div>
                <span
                  v-if="srv.short_description"
                  class="px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 shrink-0"
                >
                  {{ srv.short_description }}
                </span>
              </div>

              <!-- List Persyaratan Dokumen dari CMS -->
              <ul class="space-y-2 text-slate-700 dark:text-slate-300">
                <li
                  v-for="doc in srv.documents"
                  :key="doc.id"
                  class="p-2.5 rounded-xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                >
                  <div class="flex items-start gap-2 min-w-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                    <div class="space-y-0.5">
                      <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-slate-900 dark:text-slate-100">{{ doc.label }}</span>
                        <span
                          :class="[
                            'text-[10px] font-bold',
                            doc.is_required
                              ? 'text-rose-500 font-bold'
                              : 'text-slate-500 dark:text-slate-400'
                          ]"
                        >
                          {{ doc.is_required ? '(Wajib)' : '(Opsional)' }}
                        </span>
                        <span
                          v-if="doc.applicable_to && doc.applicable_to !== 'all'"
                          class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800"
                        >
                          {{ doc.applicable_to === 'representative_only' ? 'Khusus Perwakilan' : 'Khusus Pemohon Langsung' }}
                        </span>
                      </div>
                      <p v-if="doc.description" class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                        {{ doc.description }}
                      </p>
                    </div>
                  </div>

                  <!-- Download Blanko / Formulir Button -->
                  <a
                    v-if="getDownloadLink(doc)"
                    :href="getDownloadLink(doc)"
                    target="_blank"
                    download
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-xs shrink-0 self-start sm:self-auto cursor-pointer"
                  >
                    <FileDown class="w-3.5 h-3.5" />
                    <span>{{ getDownloadButtonLabel(doc) }}</span>
                  </a>
                </li>
              </ul>

              <div class="pt-1 flex justify-end">
                <span
                  v-if="$page.props.queue_maintenance?.is_active"
                  class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 dark:text-amber-400 py-1"
                >
                  <Lock class="w-3.5 h-3.5" />
                  <span>Pendaftaran Ditutup Sementara</span>
                </span>
                <Link
                  v-else
                  :href="route('queue.apply.create', { layanan: srv.slug })"
                  @click="showRequirementsModal = false"
                  class="inline-flex items-center gap-1 font-bold text-blue-600 dark:text-blue-400 hover:underline"
                >
                  <span>Daftar Layanan Ini &rarr;</span>
                </Link>
              </div>
            </div>

            <!-- Download Surat Kuasa F-1.07 jika diwakilkan -->
            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 flex items-center justify-between gap-2">
              <div class="space-y-0.5">
                <span class="font-bold text-slate-900 dark:text-white block">Diwakilkan Keluarga?</span>
                <span class="text-[11px] text-slate-600 dark:text-slate-400 block">Unduh blanko Surat Kuasa F-1.07</span>
              </div>
              <a
                href="/downloads/stream/24"
                target="_blank"
                download
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition shadow-xs shrink-0 cursor-pointer"
              >
                <FileDown class="w-3.5 h-3.5" />
                <span>Formulir F-1.07</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL LACAK STATUS PERMOHONAN -->
    <Teleport to="body">
      <div
        v-if="showTrackingModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200"
        @click.self="showTrackingModal = false"
      >
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl rounded-b-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-4 mb-[calc(56px+env(safe-area-inset-bottom,0px))] sm:mb-0 max-h-[calc(85vh-56px)] sm:max-h-[85vh] overflow-y-auto animate-in slide-in-from-bottom duration-200">
          <div class="flex items-center justify-between border-b pb-3 border-slate-100 dark:border-slate-800">
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
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage, useForm } from '@inertiajs/vue3'
import {
  CreditCard,
  ClipboardList,
  Search,
  FolderClock,
  UserCheck,
  LogIn,
  X,
  FileDown,
  Lock,
} from '@lucide/vue'

const page = usePage()

const showRequirementsModal = ref(false)
const showTrackingModal = ref(false)

const queueServices = computed(() => {
  return page.props.queue_services || page.props.services || []
})

function getDownloadButtonLabel(doc) {
  const label = (doc?.label || '').toLowerCase()
  if (label.includes('f-1.02') || label.includes('f1.02')) return 'Download Formulir F-1.02'
  if (label.includes('f-1.07') || label.includes('f1.07') || label.includes('kuasa')) return 'Download Formulir F-1.07'
  if (label.includes('f-1.06') || label.includes('f1.06') || label.includes('perubahan')) return 'Download Formulir F-1.06'
  return 'Download Formulir'
}

function getDownloadLink(doc) {
  if (doc?.template_url) return doc.template_url
  const label = (doc?.label || '').toLowerCase()
  if (label.includes('f-1.02') || label.includes('f1.02') || label.includes('pendaftaran peristiwa')) {
    return '/downloads/stream/19'
  }
  if (label.includes('f-1.07') || label.includes('f1.07') || label.includes('kuasa')) {
    return '/downloads/stream/24'
  }
  if (label.includes('f-1.06') || label.includes('f1.06') || label.includes('perubahan elemen')) {
    return '/downloads/stream/23'
  }
  return null
}

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

const isSyaratActive = computed(() => {
  return showRequirementsModal.value || currentUrl.value === '/layanan' || currentUrl.value.startsWith('/layanan?')
})

const isLayananActive = computed(() => {
  if (showRequirementsModal.value || showTrackingModal.value) return false
  const url = currentUrl.value
  return (
    url === '/layanan/antrian' ||
    url.startsWith('/layanan/antrian?') ||
    url.startsWith('/layanan/antrian-ktpel') ||
    url.startsWith('/layanan/antrian/daftar')
  )
})

const isTrackingActive = computed(() => {
  if (showRequirementsModal.value) return false
  if (showTrackingModal.value) return true
  const url = currentUrl.value
  return url.startsWith('/layanan/antrian/status') || url.startsWith('/lacak')
})

const isRiwayatActive = computed(() => {
  if (showRequirementsModal.value || showTrackingModal.value) return false
  const url = currentUrl.value
  return url.startsWith('/layanan/antrian/riwayat')
})

const isAkunActive = computed(() => {
  if (showRequirementsModal.value || showTrackingModal.value) return false
  const url = currentUrl.value
  return (
    url.startsWith('/layanan/pemohon') ||
    url.startsWith('/pemohon')
  )
})

function handleSyaratClick() {
  showTrackingModal.value = false
  showRequirementsModal.value = !showRequirementsModal.value
}

function handleLacakClick() {
  showRequirementsModal.value = false
  showTrackingModal.value = !showTrackingModal.value
}

function handleAkunClick() {
  showRequirementsModal.value = false
  showTrackingModal.value = false
}

function handleLayananClick() {
  showRequirementsModal.value = false
  showTrackingModal.value = false
  const url = currentUrl.value
  if (url === '/layanan/antrian' || url.startsWith('/layanan/antrian-ktpel')) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } else {
    router.visit(route('queue.index'))
  }
}

function handleRiwayatClick() {
  showRequirementsModal.value = false
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
