<template>
  <Head title="Keamanan Akun & 2FA" />

  <AdminLayout>
    <div class="space-y-6 text-left max-w-4xl mx-auto">

      <!-- Header Title Section -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Keamanan Akun &amp; 2FA</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Kelola Autentikasi Dua Faktor (2FA TOTP), kata sandi, dan opsi pemulihan darurat.
        </p>
      </div>

      <!-- Flash Success / Error Alerts -->
      <div v-if="$page.props.flash.success" class="p-4 bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-800/40 rounded-2xl text-xs font-semibold text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-sm">
        <span class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
          {{ $page.props.flash.success }}
        </span>
      </div>

      <div v-if="$page.props.flash.warning" class="p-4 bg-amber-50 border border-amber-200 dark:bg-amber-950/30 dark:border-amber-800/40 rounded-2xl text-xs font-semibold text-amber-800 dark:text-amber-300 flex items-center gap-2 shadow-sm">
        <AlertTriangle class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" />
        <div>{{ $page.props.flash.warning }}</div>
      </div>

      <!-- Role Mandatory Requirement Card Banner (if applicable) -->
      <div v-if="requiresTwoFactor && !twoFactorEnabled" class="p-5 bg-gradient-to-r from-amber-500/10 via-rose-500/10 to-purple-500/10 border border-amber-500/30 rounded-3xl backdrop-blur-md">
        <div class="flex items-start gap-3.5">
          <div class="p-2.5 bg-amber-500 text-white rounded-2xl shadow-md shrink-0">
            <ShieldAlert class="w-6 h-6" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-zinc-100">Kebijakan Keamanan Wajib untuk Peran {{ userRole }}</h3>
            <p class="text-xs text-gray-600 dark:text-zinc-300 mt-1 leading-relaxed">
              Berdasarkan kebijakan keamanan Disdukcapil Kabupaten Dompu, akun dengan peran <strong class="text-amber-600 dark:text-amber-400">{{ userRole }}</strong> <strong class="underline">WAJIB</strong> mengaktifkan Autentikasi Dua Faktor (2FA TOTP).
            </p>
            <button
              @click="startSetup"
              class="mt-3.5 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2"
            >
              <ShieldCheck class="w-4 h-4" />
              Aktifkan 2FA Sekarang
            </button>
          </div>
        </div>
      </div>

      <!-- Main Security Status Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100 dark:border-zinc-800/80">
          <div class="flex items-center gap-4">
            <div
              class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-inner shrink-0"
              :class="twoFactorEnabled ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500'"
            >
              <ShieldCheck v-if="twoFactorEnabled" class="w-8 h-8" />
              <ShieldOff v-else class="w-8 h-8" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-gray-900 dark:text-zinc-100">Autentikasi Dua Faktor (2FA TOTP)</h2>
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                  :class="twoFactorEnabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50' : 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400'"
                >
                  {{ twoFactorEnabled ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
                Melindungi akun Anda dari akses tanpa wewenang dengan mewajibkan kode 6-digit dari aplikasi authenticator.
              </p>
            </div>
          </div>

          <div>
            <button
              v-if="!twoFactorEnabled"
              @click="startSetup"
              :disabled="loadingSetup"
              class="px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-600/20 transition active:scale-95 flex items-center gap-2"
            >
              <ShieldCheck class="w-4 h-4" />
              {{ loadingSetup ? 'Memuat...' : 'Aktifkan 2FA' }}
            </button>
            <button
              v-else
              @click="disableModalOpen = true"
              class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/30 dark:hover:bg-rose-900/50 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40 text-xs font-bold rounded-xl transition"
            >
              Nonaktifkan 2FA
            </button>
          </div>
        </div>

        <!-- Detail Metrics & Status Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6">
          <div class="p-4 bg-gray-50 dark:bg-zinc-800/40 rounded-2xl border border-gray-100 dark:border-zinc-800">
            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500">Waktu Diaktifkan</div>
            <div class="text-xs font-bold text-gray-800 dark:text-zinc-200 mt-1 font-mono">
              {{ twoFactorConfirmedAt || '—' }}
            </div>
          </div>

          <div class="p-4 bg-gray-50 dark:bg-zinc-800/40 rounded-2xl border border-gray-100 dark:border-zinc-800">
            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500">Verifikasi Terakhir</div>
            <div class="text-xs font-bold text-gray-800 dark:text-zinc-200 mt-1 font-mono">
              {{ twoFactorLastUsedAt }}
            </div>
          </div>

          <div class="p-4 bg-gray-50 dark:bg-zinc-800/40 rounded-2xl border border-gray-100 dark:border-zinc-800">
            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500">Sisa Kode Pemulihan</div>
            <div class="text-xs font-bold mt-1 flex items-center justify-between">
              <span :class="recoveryCodesRemaining > 3 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500 font-extrabold'">
                {{ recoveryCodesRemaining }} / 10 Kode
              </span>
              <button
                v-if="twoFactorEnabled"
                @click="regenModalOpen = true"
                class="text-[10px] font-bold text-primary-600 dark:text-primary-400 hover:underline"
              >
                Regenerasi
              </button>
            </div>
          </div>
        </div>

        <!-- Compatible Authenticator Apps Info -->
        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-zinc-800/80">
          <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 mb-3">Aplikasi Authenticator Yang Didukung (RFC-6238 Standard)</h4>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div v-for="app in authenticatorApps" :key="app.name" class="p-3 bg-gray-50 dark:bg-zinc-800/30 rounded-xl border border-gray-100 dark:border-zinc-800 flex items-center gap-2.5">
              <Smartphone class="w-4 h-4 text-blue-500 shrink-0" />
              <div>
                <div class="text-xs font-bold text-gray-800 dark:text-zinc-200">{{ app.name }}</div>
                <div class="text-[10px] text-gray-400 dark:text-zinc-500">{{ app.platform }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2FA SETUP MODAL (Wizard) -->
      <transition name="fade">
        <div v-if="setupModalOpen" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 sm:p-8 relative text-left my-8">
            <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 mb-4 flex items-center gap-2">
              <ShieldCheck class="w-5 h-5 text-blue-500" />
              Konfigurasi Autentikasi Dua Faktor (2FA)
            </h3>

            <!-- Step 1: Scan QR Code -->
            <div class="space-y-4">
              <p class="text-xs text-gray-600 dark:text-zinc-300 leading-relaxed">
                1. Buka aplikasi <strong>Authenticator</strong> di ponsel Anda (Google Authenticator, Microsoft Authenticator, Authy, 2FAS, Aegis, dll.).
              </p>

              <!-- QR Code SVG Render Frame -->
              <div class="p-4 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl text-center shadow-inner flex items-center justify-center mx-auto max-w-[240px] w-full min-h-[220px]">
                <div v-html="setupData.qrCodeSvg" class="w-[190px] h-[190px] sm:w-[200px] sm:h-[200px] mx-auto select-none flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:max-w-full [&_svg]:max-h-full"></div>
              </div>

              <!-- Manual Secret Key Option -->
              <div class="p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl border border-gray-200 dark:border-zinc-700 text-xs">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Jika tidak dapat memindai QR, masukkan Kunci Manual:</div>
                <div class="flex items-center justify-between gap-2 font-mono font-bold text-primary-600 dark:text-primary-400">
                  <span class="break-all select-all">{{ setupData.secret }}</span>
                  <button
                    type="button"
                    @click="copySecret"
                    class="px-2.5 py-1 bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-zinc-200 rounded-lg text-[10px] font-bold hover:bg-gray-300 dark:hover:bg-zinc-600 transition shrink-0"
                  >
                    {{ copiedSecret ? 'Tersalin!' : 'Salin' }}
                  </button>
                </div>
              </div>

              <!-- Step 2: Verification Code Entry -->
              <form @submit.prevent="submitConfirm" class="space-y-4 pt-2">
                <div>
                  <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                    2. Masukkan 6-digit kode OTP dari aplikasi untuk verifikasi:
                  </label>
                  <input
                    type="text"
                    v-model="confirmForm.otp"
                    required
                    maxlength="6"
                    placeholder="000000"
                    autocomplete="off"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-center font-mono font-bold text-xl tracking-[0.3em] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @input="confirmForm.otp = confirmForm.otp.replace(/[^0-9]/g, '')"
                  />
                  <span v-if="confirmForm.errors.otp" class="text-xs text-rose-500 font-semibold mt-1 block">
                    {{ confirmForm.errors.otp }}
                  </span>
                </div>

                <!-- Footer Modal Actions -->
                <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800">
                  <button
                    type="button"
                    @click="setupModalOpen = false"
                    class="px-4 py-2.5 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    :disabled="confirmForm.processing || confirmForm.otp.length !== 6"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md transition disabled:opacity-50"
                  >
                    {{ confirmForm.processing ? 'Memverifikasi...' : 'Aktifkan 2FA' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </transition>

      <!-- RECOVERY CODES MODAL (Only shown after initial setup or regeneration) -->
      <transition name="fade">
        <div v-if="recoveryModalOpen" class="fixed inset-0 bg-black/75 backdrop-blur-md z-50 flex items-center justify-center p-4 overflow-y-auto">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 sm:p-8 relative text-left my-8">
            
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center mb-4 border border-amber-200 dark:border-amber-900/40">
              <KeyRound class="w-6 h-6" />
            </div>

            <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 mb-1">Simpan Kode Pemulihan Darurat Anda!</h3>
            <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mb-4 bg-amber-50 dark:bg-amber-950/30 p-3 rounded-xl border border-amber-200 dark:border-amber-900/30">
              ⚠️ PENTING: Kode pemulihan ini hanya ditampilkan <strong>SEKALI</strong>. Unduh atau cetak sekarang juga!
            </p>

            <!-- 10 Recovery Codes Grid -->
            <div class="bg-gray-900 text-white p-4 rounded-2xl font-mono text-xs grid grid-cols-2 gap-2 border border-zinc-800 shadow-inner mb-5">
              <div v-for="code in recoveryCodesList" :key="code" class="p-1.5 bg-zinc-950 rounded-lg text-center tracking-wider text-amber-300 font-bold border border-zinc-800">
                {{ code }}
              </div>
            </div>

            <!-- Action Buttons: Download, Print, Copy -->
            <div class="grid grid-cols-3 gap-2 mb-6">
              <button
                type="button"
                @click="downloadRecoveryCodes"
                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-800 dark:text-zinc-200 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5"
              >
                <Download class="w-3.5 h-3.5" />
                Unduh
              </button>
              <button
                type="button"
                @click="printRecoveryCodes"
                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-800 dark:text-zinc-200 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5"
              >
                <Printer class="w-3.5 h-3.5" />
                Cetak
              </button>
              <button
                type="button"
                @click="copyAllCodes"
                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-800 dark:text-zinc-200 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5"
              >
                <Copy class="w-3.5 h-3.5" />
                {{ copiedAllCodes ? 'Tersalin!' : 'Salin' }}
              </button>
            </div>

            <button
              type="button"
              @click="recoveryModalOpen = false"
              class="w-full py-3 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md transition"
            >
              Saya Sudah Menyimpan Kode Pemulihan
            </button>
          </div>
        </div>
      </transition>

      <!-- DISABLE 2FA CONFIRMATION MODAL -->
      <transition name="fade">
        <div v-if="disableModalOpen" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 relative text-left">
            <h3 class="text-sm font-bold text-gray-900 dark:text-zinc-100 mb-3">Nonaktifkan Autentikasi 2FA?</h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mb-4">
              Harap verifikasi kata sandi saat ini dan kode OTP 6-digit untuk menonaktifkan 2FA.
            </p>

            <form @submit.prevent="submitDisable" class="space-y-3">
              <div>
                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Kata Sandi Saat Ini</label>
                <input
                  type="password"
                  v-model="disableForm.password"
                  required
                  placeholder="••••••••"
                  class="w-full px-3.5 py-2 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs"
                />
                <span v-if="disableForm.errors.password" class="text-[10px] text-rose-500 block mt-0.5">{{ disableForm.errors.password }}</span>
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Kode OTP 6-Digit Saat Ini</label>
                <input
                  type="text"
                  v-model="disableForm.otp"
                  required
                  placeholder="000000"
                  class="w-full px-3.5 py-2 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-center tracking-widest"
                />
                <span v-if="disableForm.errors.otp" class="text-[10px] text-rose-500 block mt-0.5">{{ disableForm.errors.otp }}</span>
              </div>

              <div class="flex justify-end gap-2 pt-3">
                <button
                  type="button"
                  @click="disableModalOpen = false"
                  class="px-3.5 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="disableForm.processing"
                  class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-md transition disabled:opacity-50"
                >
                  {{ disableForm.processing ? 'Memproses...' : 'Ya, Nonaktifkan 2FA' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- REGENERATE RECOVERY CODES MODAL -->
      <transition name="fade">
        <div v-if="regenModalOpen" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 relative text-left">
            <h3 class="text-sm font-bold text-gray-900 dark:text-zinc-100 mb-2">Buat Ulang Kode Pemulihan?</h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mb-4">
              Kode pemulihan sebelumnya akan langsung dihanguskan dan diganti dengan 10 kode baru.
            </p>

            <form @submit.prevent="submitRegen" class="space-y-3">
              <div>
                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Masukkan Kata Sandi</label>
                <input
                  type="password"
                  v-model="regenForm.password"
                  required
                  placeholder="••••••••"
                  class="w-full px-3.5 py-2 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs"
                />
                <span v-if="regenForm.errors.password" class="text-[10px] text-rose-500 block mt-0.5">{{ regenForm.errors.password }}</span>
              </div>

              <div class="flex justify-end gap-2 pt-3">
                <button
                  type="button"
                  @click="regenModalOpen = false"
                  class="px-3.5 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="regenForm.processing"
                  class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md transition disabled:opacity-50"
                >
                  {{ regenForm.processing ? 'Memproses...' : 'Buat Ulang Kode' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  ShieldCheck, ShieldOff, ShieldAlert, KeyRound, Smartphone,
  CheckCircle2, AlertTriangle, Download, Printer, Copy
} from '@lucide/vue';

const page = usePage();

const props = defineProps({
  twoFactorEnabled:       Boolean,
  twoFactorConfirmedAt:   String,
  twoFactorLastUsedAt:    String,
  requiresTwoFactor:      Boolean,
  recoveryCodesRemaining: Number,
  userRole:               String,
  issuer:                 String,
});

const setupModalOpen     = ref(false);
const loadingSetup       = ref(false);
const setupData          = ref({ secret: '', qrCodeSvg: '', otpauthUrl: '' });
const copiedSecret       = ref(false);

const recoveryModalOpen  = ref(false);
const recoveryCodesList  = ref([]);
const copiedAllCodes     = ref(false);

const disableModalOpen   = ref(false);
const regenModalOpen     = ref(false);

const authenticatorApps = [
  { name: 'Google Authenticator', platform: 'Android & iOS' },
  { name: 'Microsoft Authenticator', platform: 'Android & iOS' },
  { name: '2FAS', platform: 'Open Source (Android/iOS)' },
  { name: 'Aegis Authenticator', platform: 'Android (Open Source)' },
];

const confirmForm = useForm({
  secret: '',
  otp:    '',
});

const disableForm = useForm({
  password: '',
  otp:      '',
});

const regenForm = useForm({
  password: '',
});

// Watch for flash recoveryCodes after confirmation or regeneration
watch(() => page.props.flash?.recoveryCodes, (codes) => {
  if (codes && Array.isArray(codes) && codes.length > 0) {
    recoveryCodesList.value = codes;
    recoveryModalOpen.value = true;
  }
}, { immediate: true });

const startSetup = async () => {
  loadingSetup.value = true;
  try {
    const res = await axios.post(route('admin.security.2fa.setup'));
    if (res.data) {
      setupData.value = res.data;
      confirmForm.secret = res.data.secret;
      confirmForm.otp = '';
      confirmForm.clearErrors();
      setupModalOpen.value = true;
    }
  } catch (err) {
    console.error('Failed to initialize 2FA setup', err);
  } finally {
    loadingSetup.value = false;
  }
};

const copySecret = () => {
  navigator.clipboard.writeText(setupData.value.secret);
  copiedSecret.value = true;
  setTimeout(() => copiedSecret.value = false, 2000);
};

const submitConfirm = () => {
  confirmForm.post(route('admin.security.2fa.confirm'), {
    onSuccess: () => {
      setupModalOpen.value = false;
    },
  });
};

const submitDisable = () => {
  disableForm.post(route('admin.security.2fa.disable'), {
    onSuccess: () => {
      disableModalOpen.value = false;
      disableForm.reset();
    },
  });
};

const submitRegen = () => {
  regenForm.post(route('admin.security.2fa.recovery-codes'), {
    onSuccess: () => {
      regenModalOpen.value = false;
      regenForm.reset();
    },
  });
};

const copyAllCodes = () => {
  const text = recoveryCodesList.value.join('\n');
  navigator.clipboard.writeText(text);
  copiedAllCodes.value = true;
  setTimeout(() => copiedAllCodes.value = false, 2000);
};

const downloadRecoveryCodes = () => {
  const text = `=== KODE PEMULIHAN 2FA DARURAT — DISDUKCAPIL DOMPU ===\n` +
               `Disimpan pada: ${new Date().toLocaleString()}\n` +
               `-----------------------------------------------------\n\n` +
               recoveryCodesList.value.join('\n') +
               `\n\nSimpan dokumen ini di tempat aman. Setiap kode hanya dapat digunakan sekali.`;
  const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `kode-pemulihan-2fa-dukcapil-${Date.now()}.txt`;
  a.click();
  URL.revokeObjectURL(url);
};

const printRecoveryCodes = () => {
  const win = window.open('', '_blank');
  win.document.write(`
    <html>
      <head>
        <title>Cetak Kode Pemulihan 2FA — Disdukcapil Dompu</title>
        <style>
          body { font-family: sans-serif; padding: 30px; line-height: 1.5; }
          h2 { margin-bottom: 5px; }
          .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0; font-family: monospace; font-size: 16px; font-weight: bold; }
          .code { padding: 8px; border: 1px solid #ccc; text-align: center; }
          .warn { font-size: 12px; color: #b45309; margin-top: 15px; }
        </style>
      </head>
      <body>
        <h2>Kode Pemulihan Darurat 2FA — Disdukcapil Dompu</h2>
        <p style="font-size: 12px; color: #666;">Dicetak pada: ${new Date().toLocaleString()}</p>
        <div class="grid">
          ${recoveryCodesList.value.map(c => `<div class="code">${c}</div>`).join('')}
        </div>
        <p class="warn">⚠️ Setiap kode hanya dapat digunakan 1 kali saat Anda tidak dapat mengakses aplikasi Authenticator. Simpan di tempat yang aman.</p>
        <script>window.print();<\/script>
      </body>
    </html>
  `);
  win.document.close();
};
</script>
