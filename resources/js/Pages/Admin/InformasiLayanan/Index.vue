<template>
  <Head title="CMS Informasi Layanan (Widget Sidebar)" />

  <AdminLayout>
    <div class="space-y-8 text-left max-w-5xl mx-auto pb-12">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border border-blue-800/40">
        <div class="space-y-2 max-w-2xl relative z-10">
          <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm">
              Homepage Sidebar CMS
            </span>
            <span class="px-2.5 py-1 bg-white/10 text-white text-[10px] font-bold rounded-lg backdrop-blur-sm">
              Widget Informasi &amp; Bantuan
            </span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Pengaturan Informasi Layanan
          </h1>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
            Kelola jadwal "Mobil Pelayanan" dan tautan aksi "Bantuan Cepat" yang ditampilkan pada widget sidebar halaman beranda publik.
          </p>
        </div>

        <div class="relative z-10 shrink-0 w-full md:w-auto">
          <button 
            @click="submit"
            :disabled="form.processing"
            class="w-full md:w-auto px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black rounded-2xl shadow-lg shadow-amber-500/20 active:scale-95 transition flex items-center justify-center gap-2"
          >
            <Save class="w-4 h-4" />
            <span>Simpan Perubahan</span>
          </button>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        
        <!-- SECTION 1: Mobil Pelayanan -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg">
                🚐
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                  Section 1: Mobil Pelayanan Keliling
                </h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400">
                  Tampilkan jadwal jemput bola pelayanan adminduk keliling.
                </p>
              </div>
            </div>

            <!-- Enable/Disable Switch -->
            <label class="relative inline-flex items-center cursor-pointer select-none">
              <input 
                type="checkbox" 
                v-model="form.mobile_service_is_active" 
                class="sr-only peer" 
              />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-700 peer-checked:bg-emerald-500"></div>
              <span class="ml-3 text-xs font-bold text-gray-700 dark:text-zinc-300">
                {{ form.mobile_service_is_active ? 'Aktif (Ditampilkan)' : 'Sembunyikan' }}
              </span>
            </label>
          </div>

          <div v-if="form.mobile_service_is_active" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kecamatan -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Kecamatan Target <span class="text-red-500">*</span>
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_kecamatan"
                placeholder="Contoh: Kecamatan Woja"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
              <p v-if="form.errors.mobile_service_kecamatan" class="text-[11px] text-red-500 font-semibold">
                {{ form.errors.mobile_service_kecamatan }}
              </p>
            </div>

            <!-- Desa (Optional) -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Desa / Kelurahan (Opsional)
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_desa"
                placeholder="Contoh: Desa Monta Baru"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
              <p v-if="form.errors.mobile_service_desa" class="text-[11px] text-red-500 font-semibold">
                {{ form.errors.mobile_service_desa }}
              </p>
            </div>

            <!-- Tanggal -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Tanggal Pelayanan
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_date"
                placeholder="Contoh: Senin, 28 Juli 2026"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
              <p v-if="form.errors.mobile_service_date" class="text-[11px] text-red-500 font-semibold">
                {{ form.errors.mobile_service_date }}
              </p>
            </div>

            <!-- Jam Pelayanan -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Jam Pelayanan
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_time"
                placeholder="Contoh: 08.00 - 14.00 WITA"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
              <p v-if="form.errors.mobile_service_time" class="text-[11px] text-red-500 font-semibold">
                {{ form.errors.mobile_service_time }}
              </p>
            </div>

            <!-- Status Badge Option -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Status Badge Pelayanan <span class="text-red-500">*</span>
              </label>
              <select 
                v-model="form.mobile_service_status"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-bold focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              >
                <option value="hari_ini">🟢 Hari Ini (Hijau - Active Today)</option>
                <option value="besok">🔵 Besok (Biru - Tomorrow)</option>
                <option value="akan_datang">🟠 Akan Datang (Oranye - Upcoming)</option>
                <option value="selesai">⚪ Selesai (Abu-abu - Completed)</option>
              </select>
              <p v-if="form.errors.mobile_service_status" class="text-[11px] text-red-500 font-semibold">
                {{ form.errors.mobile_service_status }}
              </p>
            </div>

            <!-- Button Text -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Teks Tombol Jadwal
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_button_text"
                placeholder="Lihat Jadwal Lengkap"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Button URL -->
            <div class="space-y-1.5 md:col-span-2">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Link URL Jadwal Lengkap
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_button_url"
                placeholder="Contoh: /layanan/mobil atau https://..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>
          </div>

          <div v-else class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/30 rounded-2xl text-xs text-amber-800 dark:text-amber-300">
            ℹ️ Seksi <strong>Mobil Pelayanan</strong> sedang disembunyikan. Hanya seksi <strong>Bantuan Cepat</strong> yang akan muncul di widget sidebar beranda.
          </div>
        </div>

        <!-- SECTION 2: Bantuan Cepat -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
              🤝
            </div>
            <div>
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                Section 2: Bantuan Cepat
              </h3>
              <p class="text-xs text-gray-500 dark:text-zinc-400">
                Kelola tautan kontak langsung (WhatsApp, Call Center, Tracking, Maps, Email).
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- WhatsApp Pelayanan -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 flex items-center gap-1.5">
                <span>💬 WhatsApp Pelayanan</span>
              </label>
              <input 
                type="text" 
                v-model="form.quick_help_whatsapp"
                placeholder="Contoh: 6282340001111 atau https://wa.me/..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>



            <!-- Google Maps URL -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 flex items-center gap-1.5">
                <span>📍 Link Google Maps Kantor</span>
              </label>
              <input 
                type="text" 
                v-model="form.quick_help_google_maps_url"
                placeholder="Contoh: https://maps.google.com/?q=..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Email Dukcapil -->
            <div class="space-y-1.5 md:col-span-2">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 flex items-center gap-1.5">
                <span>✉️ Email Resmi Dukcapil</span>
              </label>
              <input 
                type="email" 
                v-model="form.quick_help_email"
                placeholder="Contoh: dukcapilkabdompu@gmail.com"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Primary Button Text -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Teks Tombol Utama Bantuan
              </label>
              <input 
                type="text" 
                v-model="form.quick_help_primary_button_text"
                placeholder="Hubungi Kami"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Primary Button URL -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                URL Tombol Utama Bantuan
              </label>
              <input 
                type="text" 
                v-model="form.quick_help_primary_button_url"
                placeholder="Contoh: /contact"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>
          </div>
        </div>

        <!-- Form Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-4">
          <button 
            type="submit"
            :disabled="form.processing"
            class="px-8 py-3.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-2xl shadow-lg shadow-primary-500/25 active:scale-95 transition flex items-center gap-2"
          >
            <Save class="w-4 h-4" />
            <span>Simpan Semua Pengaturan</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save } from '@lucide/vue';

const props = defineProps({
  settings: Object,
});

const form = useForm({
  // Section 1: Mobil Pelayanan
  mobile_service_is_active: props.settings.mobile_service_is_active === '1',
  mobile_service_kecamatan: props.settings.mobile_service_kecamatan || 'Kecamatan Woja',
  mobile_service_desa: props.settings.mobile_service_desa || 'Desa Monta Baru',
  mobile_service_date: props.settings.mobile_service_date || 'Senin, 28 Juli 2026',
  mobile_service_time: props.settings.mobile_service_time || '08.00 - 14.00 WITA',
  mobile_service_status: props.settings.mobile_service_status || 'hari_ini',
  mobile_service_button_text: props.settings.mobile_service_button_text || 'Lihat Jadwal Lengkap',
  mobile_service_button_url: props.settings.mobile_service_button_url || '/layanan/mobil',

  // Section 2: Bantuan Cepat
  quick_help_whatsapp: props.settings.quick_help_whatsapp || 'https://wa.me/6282340001111',
  quick_help_call_center: props.settings.quick_help_call_center || '(0373) 21123',
  quick_help_tracking_url: props.settings.quick_help_tracking_url || '/layanan/tracking',
  quick_help_google_maps_url: props.settings.quick_help_google_maps_url || 'https://maps.google.com/?q=Dukcapil+Dompu',
  quick_help_email: props.settings.quick_help_email || 'dukcapilkabdompu@gmail.com',
  quick_help_primary_button_text: props.settings.quick_help_primary_button_text || 'Hubungi Kami',
  quick_help_primary_button_url: props.settings.quick_help_primary_button_url || '/contact',
});

const submit = () => {
  form.post(route('admin.informasi-layanan.update'), {
    preserveScroll: true,
  });
};
</script>
