<template>
  <PublicLayout title="Ajukan Permohonan Informasi — PPID Disdukcapil Kab. Dompu">
    
    <!-- Modern Dukcapil Identity Hero Banner -->
    <section class="relative bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white py-12 md:py-16 overflow-hidden rounded-3xl mb-8 border border-blue-900/30 shadow-xl shadow-blue-950/20">
      <img 
        src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1400&q=80"
        class="absolute inset-0 w-full h-full object-cover opacity-20 filter blur-[3px] scale-105 pointer-events-none z-0"
        alt="Backdrop PPID Request" 
      />
      <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:28px_28px] pointer-events-none z-0"></div>

      <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-blue-200/80 mb-4 flex-wrap">
          <Link href="/" class="hover:text-white transition">Beranda</Link>
          <ChevronRight :size="12" class="text-blue-400" />
          <Link :href="route('public.ppid.pengertian')" class="hover:text-white transition">PPID</Link>
          <ChevronRight :size="12" class="text-blue-400" />
          <span class="text-amber-400 font-bold">Ajukan Permohonan</span>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-500/20 text-amber-300 text-xs font-extrabold uppercase tracking-wider mb-3 border border-amber-500/30 backdrop-blur-md">
          <Send :size="14" class="text-amber-400" /> Layanan Online PPID
        </span>

        <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-sm">
          Formulir Permohonan Informasi Publik
        </h1>
        <p class="text-xs sm:text-sm text-blue-100/90 font-medium max-w-2xl mt-2">
          Ajukan permohonan informasi publik kepada PPID Disdukcapil Kabupaten Dompu secara online
        </p>
      </div>
    </section>

    <!-- Main Form & Info Grid -->
    <div class="max-w-7xl mx-auto px-2 sm:px-4 py-4">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Info Sidebar -->
        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-primary-600 dark:text-primary-400 flex items-center gap-2">
              <Clock :size="16" /> Waktu Penyelesaian Service Level Agreement (SLA)
            </h3>
            
            <div class="space-y-3 pt-1">
              <div class="flex items-start gap-3">
                <div class="w-3 h-3 rounded-full bg-primary-600 shrink-0 mt-1.5 shadow-sm"></div>
                <div>
                  <strong class="text-xs font-extrabold text-gray-900 dark:text-zinc-50 block">10 Hari Kerja</strong>
                  <p class="text-[11px] text-gray-500 dark:text-zinc-400 font-medium">Waktu respons standar penyampaian pemberitahuan tertulis</p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="w-3 h-3 rounded-full bg-amber-500 shrink-0 mt-1.5 shadow-sm"></div>
                <div>
                  <strong class="text-xs font-extrabold text-gray-900 dark:text-zinc-50 block">+7 Hari Kerja</strong>
                  <p class="text-[11px] text-gray-500 dark:text-zinc-400 font-medium">Perpanjangan waktu jika dokumen membutuhkan koordinasi khusus</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-3">
            <h3 class="text-sm font-extrabold text-amber-600 dark:text-amber-400 flex items-center gap-2">
              <AlertCircle :size="16" /> Ketentuan Penting
            </h3>
            <ul class="space-y-2 text-xs font-medium text-gray-600 dark:text-zinc-300">
              <li class="flex items-start gap-2">
                <span class="text-primary-600 font-bold">•</span>
                <span>Sertakan NIK dan identitas diri yang sah.</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-primary-600 font-bold">•</span>
                <span>Tuliskan rincian serta tujuan penggunaan informasi dengan jelas.</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-primary-600 font-bold">•</span>
                <span>Simpan nomor kode tiket untuk melacak progres permohonan.</span>
              </li>
            </ul>
          </div>

          <Link 
            :href="route('public.ppid.track')" 
            class="w-full py-3 px-4 bg-primary-50 dark:bg-zinc-800 text-primary-600 dark:text-primary-400 hover:bg-primary-100 font-bold text-xs rounded-2xl flex items-center justify-center gap-2 border border-primary-200 dark:border-zinc-700 transition"
          >
            <Search :size="15" />
            <span>Lacak Permohonan Terdaftar</span>
          </Link>
        </aside>

        <!-- Form Card -->
        <main class="lg:col-span-8">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl overflow-hidden shadow-sm">
            <div class="bg-gradient-to-r from-primary-600 to-indigo-600 text-white font-extrabold text-sm p-6 flex items-center gap-3">
              <Send :size="18" class="text-amber-300" />
              <span>Isi Formulir Permohonan Informasi</span>
            </div>

            <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-6">
              
              <!-- Section 1 -->
              <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-primary-600 dark:text-primary-400 pb-2 border-b border-gray-100 dark:border-zinc-800 mb-4">
                  1. Identitas Pemohon
                </h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                      Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input v-model="form.requester_name" type="text" placeholder="Nama sesuai KTP" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none" />
                    <span class="text-[11px] text-rose-500 font-medium mt-1 block" v-if="form.errors.requester_name">{{ form.errors.requester_name }}</span>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                      NIK / Nomor Identitas (KTP)
                    </label>
                    <input v-model="form.requester_id_number" type="text" placeholder="16 digit NIK" maxlength="16" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none" />
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                      Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input v-model="form.requester_email" type="email" placeholder="nama@email.com" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none" />
                    <span class="text-[11px] text-rose-500 font-medium mt-1 block" v-if="form.errors.requester_email">{{ form.errors.requester_email }}</span>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                      Nomor Telepon / WhatsApp
                    </label>
                    <input v-model="form.requester_phone" type="tel" placeholder="08xx-xxxx-xxxx" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none" />
                  </div>
                </div>

                <div class="mt-4">
                  <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Alamat Lengkap</label>
                  <input v-model="form.requester_address" type="text" placeholder="Jl. Raya Dompu..." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none" />
                </div>
              </div>

              <!-- Section 2 -->
              <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-primary-600 dark:text-primary-400 pb-2 border-b border-gray-100 dark:border-zinc-800 mb-4">
                  2. Rincian Informasi Yang Diminta
                </h4>

                <div class="space-y-4">
                  <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                      Tujuan Penggunaan Informasi <span class="text-rose-500">*</span>
                    </label>
                    <input v-model="form.purpose" type="text" placeholder="Contoh: Penelitian Akademis, Referensi Kebijakan, dll." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none" />
                    <span class="text-[11px] text-rose-500 font-medium mt-1 block" v-if="form.errors.purpose">{{ form.errors.purpose }}</span>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                      Rincian Informasi Yang Dibutuhkan <span class="text-rose-500">*</span>
                    </label>
                    <textarea v-model="form.information_requested" rows="4" placeholder="Jelaskan secara spesifik dokumen/data publik yang Anda butuhkan..." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none"></textarea>
                    <span class="text-[11px] text-rose-500 font-medium mt-1 block" v-if="form.errors.information_requested">{{ form.errors.information_requested }}</span>
                  </div>
                </div>
              </div>

              <!-- Section 3 -->
              <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-primary-600 dark:text-primary-400 pb-2 border-b border-gray-100 dark:border-zinc-800 mb-4">
                  3. Metode Penyampaian
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Cara Pengajuan</label>
                    <select v-model="form.request_method" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none">
                      <option value="online">Online (Portal Web PPID)</option>
                      <option value="langsung">Datang Langsung Ke Loket</option>
                      <option value="surat">Surat Resmi PPID</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Cara Penerimaan Berkas</label>
                    <select v-model="form.delivery_method" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none">
                      <option value="email">Surat Elektronik (Email)</option>
                      <option value="langsung">Pengambilan Langsung</option>
                      <option value="salinan">Salinan Fisik (Cetak)</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="pt-4">
                <button 
                  type="submit" 
                  :disabled="form.processing"
                  class="w-full py-3.5 px-6 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 transition active:scale-95 disabled:opacity-50 cursor-pointer"
                >
                  <Loader2 v-if="form.processing" :size="16" class="animate-spin" />
                  <Send v-else :size="16" class="text-slate-950" />
                  <span>{{ form.processing ? 'Mengirimkan Permohonan...' : 'Kirimkan Permohonan Informasi' }}</span>
                </button>
              </div>
            </form>
          </div>
        </main>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ChevronRight, Clock, AlertCircle, Send, Search, Loader2 } from '@lucide/vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps({ pages: Array })

const form = useForm({
  requester_name: '',
  requester_email: '',
  requester_phone: '',
  requester_address: '',
  requester_id_number: '',
  purpose: '',
  information_requested: '',
  request_method: 'online',
  delivery_method: 'email',
})

function submit() {
  form.post(route('public.ppid.request.submit'))
}
</script>
