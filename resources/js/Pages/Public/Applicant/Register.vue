<template>
  <Head title="Pendaftaran Akun Pemohon - Disdukcapil Kabupaten Dompu" />

  <PublicLayout :hide-footer="true">
    <div class="py-2.5 sm:py-10 max-w-md mx-auto px-3 sm:px-4 space-y-2.5 sm:space-y-4 w-full">
      <!-- Registration Card -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl p-4 sm:p-7 border border-slate-200 dark:border-slate-800 shadow-xl space-y-3.5 sm:space-y-5 w-full max-w-full overflow-hidden">
        <div class="text-center space-y-1 sm:space-y-2">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto shadow-inner">
            <UserPlus class="w-5 h-5 sm:w-6 sm:h-6" />
          </div>
          <h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white">
            Pendaftaran Akun
          </h1>
          <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">
            Daftarkan NIK dan email aktif untuk pengajuan layanan online.
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
                  Pendaftaran Ditutup Sementara
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

        <form @submit.prevent="submitRegister" class="space-y-3 sm:space-y-4">
          <!-- NIK Input -->
          <div class="space-y-1">
            <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
              Nomor Induk Kependudukan (NIK)
            </label>
            <input
              v-model="form.nik"
              type="text"
              maxlength="16"
              inputmode="numeric"
              placeholder="16 digit angka sesuai KTP/KK..."
              class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-mono text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
            <p v-if="form.errors.nik" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.nik }}
            </p>
          </div>

          <!-- Nama Lengkap Input -->
          <div class="space-y-1">
            <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
              Nama Lengkap (Sesuai KTP/KK)
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Contoh: AHMAD DAHLAN..."
              class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm uppercase text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
            <p v-if="form.errors.name" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Nomor Handphone Input (Wajib) -->
          <div class="space-y-1">
            <div class="flex items-center justify-between">
              <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
                Nomor Handphone / WhatsApp <span class="text-rose-500">*</span>
              </label>
              <!-- Live Digit Counter -->
              <span
                :class="[
                  'text-[10px] font-mono transition-colors',
                  phoneDigitsCount >= 10 && phoneDigitsCount <= 13
                    ? 'text-emerald-600 dark:text-emerald-400 font-bold'
                    : (phoneDigitsCount > 0 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-400')
                ]"
              >
                <template v-if="phoneDigitsCount >= 10 && phoneDigitsCount <= 13">
                  ✓ {{ phoneDigitsCount }}/13 Digit
                </template>
                <template v-else-if="phoneDigitsCount > 0">
                  {{ phoneDigitsCount }}/13 Digit (Min. 10)
                </template>
                <template v-else>
                  10 - 13 Digit
                </template>
              </span>
            </div>

            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <Smartphone class="w-4 h-4" />
              </div>
              <input
                id="field_applicant_phone"
                :value="displayPhone"
                @input="handlePhoneInput"
                @keydown="handlePhoneKeydown"
                @paste="handlePhonePaste"
                type="text"
                inputmode="numeric"
                maxlength="16"
                placeholder="08xx-xxxx-xxxx..."
                class="w-full pl-10 pr-9 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-mono text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <button
                v-if="form.phone"
                type="button"
                @click="clearPhone"
                title="Hapus nomor HP"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition cursor-pointer"
              >
                <X class="w-4 h-4" />
              </button>
            </div>

            <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 pt-0.5">
              <span>Pemisah otomatis: <strong>08xx-xxxx-xxxx</strong> (Hanya angka)</span>
              <span v-if="phoneDigitsCount > 0 && phoneDigitsCount < 10" class="text-amber-600 dark:text-amber-400 font-medium">
                Kurang {{ 10 - phoneDigitsCount }} digit
              </span>
            </div>

            <p v-if="form.errors.phone" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.phone }}
            </p>
          </div>

          <!-- Email Input -->
          <div class="space-y-1 sm:space-y-1.5">
            <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
              Alamat Email Aktif (Untuk Kode OTP)
            </label>
            <div class="relative">
              <input
                id="field_applicant_email"
                v-model="form.email"
                type="email"
                @blur="normalizeEmail"
                placeholder="nama@email.com..."
                class="w-full pl-3.5 pr-9 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <button
                v-if="form.email"
                type="button"
                @click="clearEmail"
                title="Hapus email"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition cursor-pointer"
              >
                <X class="w-4 h-4" />
              </button>
            </div>

            <!-- Quick Domain Selector Pills -->
            <div class="space-y-1 pt-0.5">
              <div class="flex items-center flex-wrap gap-1.5">
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold mr-0.5">Pilihan domain:</span>
                <button
                  type="button"
                  @click="applyEmailDomain('@gmail.com')"
                  :class="[
                    'px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg sm:rounded-xl text-[10px] sm:text-[11px] font-bold transition border cursor-pointer inline-flex items-center gap-1',
                    currentEmailDomain === '@gmail.com'
                      ? 'bg-blue-600 text-white border-blue-600 shadow-sm ring-2 ring-blue-500/20'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'
                  ]"
                >
                  <span>@gmail.com</span>
                  <span v-if="currentEmailDomain === '@gmail.com'" class="text-[9px] font-normal opacity-90">✓</span>
                </button>
                <button
                  type="button"
                  @click="applyEmailDomain('@yahoo.com')"
                  :class="[
                    'px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg sm:rounded-xl text-[10px] sm:text-[11px] font-bold transition border cursor-pointer inline-flex items-center gap-1',
                    currentEmailDomain === '@yahoo.com'
                      ? 'bg-purple-600 text-white border-purple-600 shadow-sm ring-2 ring-purple-500/20'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'
                  ]"
                >
                  <span>@yahoo.com</span>
                  <span v-if="currentEmailDomain === '@yahoo.com'" class="text-[9px] font-normal opacity-90">✓</span>
                </button>
                <button
                  type="button"
                  @click="applyEmailDomain('@outlook.com')"
                  :class="[
                    'px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg sm:rounded-xl text-[10px] sm:text-[11px] font-bold transition border cursor-pointer inline-flex items-center gap-1',
                    currentEmailDomain === '@outlook.com'
                      ? 'bg-sky-600 text-white border-sky-600 shadow-sm ring-2 ring-sky-500/20'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'
                  ]"
                >
                  <span>@outlook.com</span>
                  <span v-if="currentEmailDomain === '@outlook.com'" class="text-[9px] font-normal opacity-90">✓</span>
                </button>
                <button
                  type="button"
                  @click="applyEmailDomain('@icloud.com')"
                  :class="[
                    'px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg sm:rounded-xl text-[10px] sm:text-[11px] font-bold transition border cursor-pointer inline-flex items-center gap-1',
                    currentEmailDomain === '@icloud.com'
                      ? 'bg-slate-700 text-white border-slate-700 shadow-sm ring-2 ring-slate-500/20'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'
                  ]"
                >
                  <span>@icloud.com</span>
                  <span v-if="currentEmailDomain === '@icloud.com'" class="text-[9px] font-normal opacity-90">✓</span>
                </button>
              </div>

              <!-- Live Preview jika pemohon hanya mengetik username tanpa domain @ -->
              <div v-if="emailUsernameOnly" class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-blue-600 dark:text-blue-400 font-medium bg-blue-50/70 dark:bg-blue-950/40 px-2.5 py-1 rounded-xl border border-blue-200/60 dark:border-blue-900/40">
                <Sparkles class="w-3 h-3 shrink-0 text-amber-500" />
                <span>Otomatis disimpan: <strong class="font-mono font-bold text-blue-700 dark:text-blue-300">{{ emailUsernameOnly }}@gmail.com</strong></span>
              </div>
            </div>

            <p v-if="form.errors.email" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password Input -->
          <div class="space-y-1">
            <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
              Password Baru (Minimal 8 Karakter)
            </label>
            <input
              v-model="form.password"
              type="password"
              placeholder="Buat password yang kuat..."
              class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
            <p v-if="form.errors.password" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Password Confirmation Input -->
          <div class="space-y-1">
            <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
              Konfirmasi Password Baru
            </label>
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Ulangi password di atas..."
              class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <!-- Foto Selfie Wajah Pemohon (Wajib) -->
          <div class="space-y-1.5 pt-1" id="field_applicant_selfie">
            <div class="flex items-center justify-between">
              <label class="block text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300">
                Foto Selfie Wajah Pemohon <span class="text-rose-500">*</span>
              </label>
              <span
                v-if="selfiePreviewUrl"
                class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1"
              >
                <CheckCircle2 class="w-3.5 h-3.5" />
                <span>Foto Siap</span>
              </span>
              <span v-else class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">
                Wajib Dilampirkan
              </span>
            </div>

            <!-- Preview Card when photo is taken/uploaded -->
            <div
              v-if="selfiePreviewUrl"
              class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-emerald-300 dark:border-emerald-800 flex items-center gap-3 relative overflow-hidden group shadow-xs"
            >
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-black shrink-0 border border-slate-200 dark:border-slate-700 relative shadow-inner">
                <img
                  :src="selfiePreviewUrl"
                  alt="Preview Selfie"
                  class="w-full h-full object-cover"
                />
              </div>

              <div class="flex-1 min-w-0 space-y-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300">
                    Foto Tersimpan
                  </span>
                  <span class="text-[10px] text-slate-400 font-mono">{{ selfieFileSize }}</span>
                </div>
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                  {{ selfieFileName || 'Foto-Selfie-Pemohon.jpg' }}
                </p>
                <div class="flex items-center gap-2 pt-0.5">
                  <button
                    type="button"
                    @click="openSelfieCamera"
                    class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer"
                  >
                    <Camera class="w-3.5 h-3.5" />
                    <span>Ambil Ulang</span>
                  </button>
                  <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                  <button
                    type="button"
                    @click="removeSelfie"
                    class="text-[11px] font-bold text-rose-500 hover:underline inline-flex items-center gap-1 cursor-pointer"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                    <span>Hapus</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Upload / Camera Buttons when no photo yet -->
            <div
              v-else
              class="p-3.5 sm:p-4 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 bg-slate-50/70 dark:bg-slate-800/50 transition text-center space-y-2.5"
            >
              <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto shadow-inner">
                <Camera class="w-6 h-6" />
              </div>
              <div class="space-y-1">
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">
                  Ambil Foto Selfie Wajah Anda
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
                  Pastikan wajah terlihat jelas, menghadap kamera lurus, dan tidak memakai masker/kacamata hitam.
                </p>
              </div>

              <div class="flex flex-col sm:flex-row items-center justify-center gap-2 pt-1">
                <button
                  type="button"
                  @click="openSelfieCamera"
                  class="w-full sm:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer"
                >
                  <Camera class="w-4 h-4" />
                  <span>Buka Kamera Selfie</span>
                </button>
                <label
                  class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer border border-slate-200 dark:border-slate-600"
                >
                  <Upload class="w-3.5 h-3.5" />
                  <span>Pilih Berkas / Galeri</span>
                  <input
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="handleFileInput"
                  />
                </label>
              </div>
            </div>

            <!-- Privacy Destruction Notice Banner -->
            <div class="p-2.5 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/70 dark:border-blue-900/50 text-[10.5px] leading-relaxed text-blue-900 dark:text-blue-300 flex items-start gap-2">
              <ShieldCheck class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
              <span>
                <strong>Jaminan Privasi (UU PDP):</strong> Foto selfie hanya digunakan oleh verifikator Disdukcapil untuk pencocokan identitas pendaftar, dan akan <strong>otomatis dimusnahkan secara permanen</strong> dari server setelah akun selesai diverifikasi.
              </span>
            </div>

            <p v-if="form.errors.selfie" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.selfie }}
            </p>
          </div>

          <div v-if="$page.props.queue_maintenance?.is_active" class="p-3 sm:p-3.5 rounded-xl sm:rounded-2xl bg-amber-100 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-800 text-center font-bold text-xs text-amber-800 dark:text-amber-200 flex items-center justify-center gap-2 mt-1">
            <Lock class="w-4 h-4 text-amber-600 shrink-0" />
            <span>Pendaftaran Akun Baru Ditutup Sementara</span>
          </div>
          <button
            v-else
            type="submit"
            :disabled="form.processing"
            class="w-full py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm transition shadow-md shadow-emerald-600/20 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer mt-1"
          >
            <span v-if="form.processing">Memproses Pendaftaran...</span>
            <span v-else>Daftar & Kirim Kode OTP</span>
            <ArrowRight class="w-4 h-4" />
          </button>
        </form>

        <div class="pt-2.5 sm:pt-4 border-t border-slate-100 dark:border-slate-800 text-center text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">
          <span>Sudah memiliki akun? </span>
          <Link :href="route('applicant.login')" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
            Masuk di sini &rarr;
          </Link>
        </div>
      </div>
    </div>

    <!-- Camera Capture Modal for Selfie -->
    <CameraCapture
      v-if="showCameraModal"
      document-label="Foto Selfie Wajah Pemohon (Pendaftaran Akun)"
      capture-mode="user"
      capture-hint="Posisikan wajah Anda tepat di dalam bingkai panduan kamera. Foto selfie hanya digunakan untuk verifikasi akun dan akan otomatis terhapus setelah verifikasi."
      :is-representative="false"
      @close="showCameraModal = false"
      @captured="handleCameraCaptured"
    />
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import CameraCapture from '@/Components/Queue/CameraCapture.vue'
import {
  ArrowRight,
  UserPlus,
  Sparkles,
  X,
  AlertCircle,
  Clock,
  Lock,
  Smartphone,
  Camera,
  CheckCircle2,
  ShieldCheck,
  Trash2,
  Upload
} from '@lucide/vue'

const form = useForm({
  nik: '',
  name: '',
  phone: '',
  email: '',
  password: '',
  password_confirmation: '',
  selfie: null,
})

const showCameraModal = ref(false)
const selfiePreviewUrl = ref(null)
const selfieFileName = ref('')
const selfieFileSize = ref('')

const openSelfieCamera = () => {
  if (form.errors.selfie) {
    delete form.errors.selfie
  }
  showCameraModal.value = true
}

const handleCameraCaptured = (rawFile) => {
  if (!rawFile) return
  if (selfiePreviewUrl.value) {
    URL.revokeObjectURL(selfiePreviewUrl.value)
  }
  form.selfie = rawFile
  selfiePreviewUrl.value = URL.createObjectURL(rawFile)
  selfieFileName.value = rawFile.name || 'foto-selfie-pemohon.jpg'
  const kb = Math.round(rawFile.size / 1024)
  selfieFileSize.value = kb >= 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${kb} KB`
  delete form.errors.selfie
  showCameraModal.value = false
}

const handleFileInput = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  if (selfiePreviewUrl.value) {
    URL.revokeObjectURL(selfiePreviewUrl.value)
  }
  form.selfie = file
  selfiePreviewUrl.value = URL.createObjectURL(file)
  selfieFileName.value = file.name
  const kb = Math.round(file.size / 1024)
  selfieFileSize.value = kb >= 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${kb} KB`
  delete form.errors.selfie
  e.target.value = ''
}

const removeSelfie = () => {
  if (selfiePreviewUrl.value) {
    URL.revokeObjectURL(selfiePreviewUrl.value)
  }
  form.selfie = null
  selfiePreviewUrl.value = null
  selfieFileName.value = ''
  selfieFileSize.value = ''
}

// Format digits with hyphen separators: 4-4-5 format (e.g. 0812-3456-7890 or 0812-3456-78901)
const formatPhoneDisplay = (digits) => {
  if (!digits) return ''
  const clean = digits.replace(/\D/g, '').slice(0, 13)
  if (clean.length <= 4) return clean
  if (clean.length <= 8) return `${clean.slice(0, 4)}-${clean.slice(4)}`
  return `${clean.slice(0, 4)}-${clean.slice(4, 8)}-${clean.slice(8)}`
}

const displayPhone = ref(formatPhoneDisplay(form.phone || ''))

const phoneDigitsCount = computed(() => {
  return (form.phone || '').replace(/\D/g, '').length
})

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.scrollTo({ left: 0, top: 0, behavior: 'instant' })
  }
  if (form.phone) {
    displayPhone.value = formatPhoneDisplay(form.phone)
  }
})

const handlePhoneInput = (e) => {
  const input = e.target
  const rawValue = input.value || ''
  const digits = rawValue.replace(/\D/g, '').slice(0, 13)
  form.phone = digits
  displayPhone.value = formatPhoneDisplay(digits)
  input.value = displayPhone.value

  // Automatically clear error once user reaches a valid 10-13 digit number
  if (form.errors.phone && digits.length >= 10 && digits.length <= 13) {
    delete form.errors.phone
  }
}

const handlePhoneKeydown = (e) => {
  // Allow navigation, control keys, backspace, delete, tab, enter
  if (
    [
      'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
      'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
      'Home', 'End'
    ].includes(e.key) ||
    ((e.ctrlKey || e.metaKey) && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase()))
  ) {
    return
  }

  // Strictly block any character that is not a numeric digit 0-9
  if (!/^[0-9]$/.test(e.key)) {
    e.preventDefault()
    return
  }

  // Block further typing if already 13 digits and no text selected
  const currentDigits = (form.phone || '').replace(/\D/g, '')
  const hasSelection = e.target.selectionStart !== e.target.selectionEnd
  if (currentDigits.length >= 13 && !hasSelection) {
    e.preventDefault()
  }
}

const handlePhonePaste = (e) => {
  e.preventDefault()
  const pasted = (e.clipboardData || window.clipboardData)?.getData('text') || ''
  const digits = pasted.replace(/\D/g, '').slice(0, 13)
  if (digits) {
    form.phone = digits
    displayPhone.value = formatPhoneDisplay(digits)
    if (e.target) {
      e.target.value = displayPhone.value
    }
    if (form.errors.phone && digits.length >= 10 && digits.length <= 13) {
      delete form.errors.phone
    }
  }
}

const clearPhone = () => {
  form.phone = ''
  displayPhone.value = ''
  if (form.errors.phone) {
    delete form.errors.phone
  }
  const el = document.getElementById('field_applicant_phone')
  if (el) el.focus()
}

// Email domain helpers
const emailUsernameOnly = computed(() => {
  const val = (form.email || '').trim()
  if (!val) return ''
  if (!val.includes('@')) {
    return val.replace(/\s+/g, '')
  }
  return ''
})

const currentEmailDomain = computed(() => {
  const val = (form.email || '').trim().toLowerCase()
  if (!val) return ''
  if (!val.includes('@')) return ''
  const idx = val.indexOf('@')
  return val.slice(idx)
})

const applyEmailDomain = (domain) => {
  const current = (form.email || '').trim().replace(/\s+/g, '')
  if (!current) {
    form.email = domain
    setTimeout(() => {
      const el = document.getElementById('field_applicant_email')
      if (el) {
        el.focus()
        if (typeof el.setSelectionRange === 'function') {
          el.setSelectionRange(0, 0)
        }
      }
    }, 50)
    return
  }
  const parts = current.split('@')
  const username = parts[0] || ''
  form.email = `${username}${domain}`.toLowerCase()
  if (form.errors.email) {
    delete form.errors.email
  }
}

const clearEmail = () => {
  form.email = ''
  if (form.errors.email) {
    delete form.errors.email
  }
}

const normalizeEmail = () => {
  if (!form.email) return
  let val = form.email.trim().replace(/\s+/g, '')
  if (!val) {
    form.email = ''
    return
  }
  if (!val.includes('@')) {
    val = `${val}@gmail.com`
  } else if (val.endsWith('@')) {
    val = `${val}gmail.com`
  }
  form.email = val.toLowerCase()
  if (form.errors.email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (emailRegex.test(form.email)) {
      delete form.errors.email
    }
  }
}

const submitRegister = () => {
  normalizeEmail()

  const phoneDigits = (form.phone || '').replace(/\D/g, '')
  if (!phoneDigits) {
    form.errors.phone = 'Nomor Handphone wajib diisi.'
    const el = document.getElementById('field_applicant_phone')
    if (el) el.focus()
    return
  }
  if (phoneDigits.length < 10) {
    form.errors.phone = `Nomor Handphone minimal 10 digit (saat ini ${phoneDigits.length} digit).`
    const el = document.getElementById('field_applicant_phone')
    if (el) el.focus()
    return
  }
  if (phoneDigits.length > 13) {
    form.errors.phone = `Nomor Handphone maksimal 13 digit (saat ini ${phoneDigits.length} digit).`
    const el = document.getElementById('field_applicant_phone')
    if (el) el.focus()
    return
  }

  if (!form.selfie) {
    form.errors.selfie = 'Foto selfie wajah pemohon wajib dilampirkan untuk verifikasi pendaftaran akun.'
    const el = document.getElementById('field_applicant_selfie')
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    return
  }

  form.phone = phoneDigits

  form.post(route('applicant.register.post'), {
    forceFormData: true,
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
