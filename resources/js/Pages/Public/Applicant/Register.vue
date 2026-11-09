<template>
  <Head title="Pendaftaran Akun Pemohon - Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="py-8 sm:py-16 max-w-md mx-auto px-4 space-y-6">
      <div class="flex items-center justify-between">
        <Link
          :href="route('queue.index')"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition"
        >
          <ArrowLeft class="w-3.5 h-3.5" />
          <span>Kembali ke Portal Antrian</span>
        </Link>
      </div>

      <!-- Registration Card -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-xl space-y-6">
        <div class="text-center space-y-2">
          <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto shadow-inner">
            <UserPlus class="w-7 h-7" />
          </div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Pendaftaran Akun
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Daftarkan NIK dan email aktif Anda untuk kemudahan pengajuan layanan kependudukan online.
          </p>
        </div>

        <form @submit.prevent="submitRegister" class="space-y-4 pt-1">
          <!-- NIK Input -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Nomor Induk Kependudukan (NIK)
            </label>
            <input
              v-model="form.nik"
              type="text"
              maxlength="16"
              inputmode="numeric"
              placeholder="16 digit angka sesuai KTP/KK..."
              class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
            <p v-if="form.errors.nik" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.nik }}
            </p>
          </div>

          <!-- Nama Lengkap Input -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Nama Lengkap (Sesuai KTP/KK)
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Contoh: AHMAD DAHLAN..."
              class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm uppercase text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
            <p v-if="form.errors.name" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email Input -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Alamat Email Aktif (Untuk Kode OTP)
            </label>
            <input
              v-model="form.email"
              type="email"
              placeholder="nama@email.com..."
              class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
            <p v-if="form.errors.email" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password Input -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Password Baru (Minimal 8 Karakter)
            </label>
            <input
              v-model="form.password"
              type="password"
              placeholder="Buat password yang kuat..."
              class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
            <p v-if="form.errors.password" class="text-xs text-rose-500 font-semibold">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Password Confirmation Input -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Konfirmasi Password Baru
            </label>
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Ulangi password di atas..."
              class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm transition shadow-lg shadow-emerald-600/20 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
          >
            <span v-if="form.processing">Memproses Pendaftaran...</span>
            <span v-else>Daftar & Kirim Kode OTP</span>
            <ArrowRight class="w-4 h-4" />
          </button>
        </form>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
          <span>Sudah memiliki akun? </span>
          <Link :href="route('applicant.login')" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
            Masuk di sini &rarr;
          </Link>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { ArrowLeft, ArrowRight, UserPlus } from '@lucide/vue'

const form = useForm({
  nik: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submitRegister = () => {
  form.post(route('applicant.register.post'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
