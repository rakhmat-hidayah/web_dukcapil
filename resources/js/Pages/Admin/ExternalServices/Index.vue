<template>
  <Head title="Konfigurasi Layanan SANAI Online" />

  <AdminLayout>
    <div class="space-y-6 text-left max-w-4xl">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Konfigurasi Layanan SANAI Online</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Atur tautan, label tombol CTA, deskripsi promo, serta visibilitas portal transaksi SANAI di Navbar, Homepage, dan Footer.
          </p>
        </div>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs rounded-xl font-bold">
        ✓ {{ $page.props.flash.success }}
      </div>

      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-6">
        <form @submit.prevent="submit" class="space-y-6">
          
          <!-- Basic information -->
          <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 dark:border-zinc-800 pb-2">
              Informasi Utama Portal SANAI
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">Nama Layanan / Portal *</label>
                <input 
                  v-model="form.sanai_name"
                  type="text" 
                  required
                  placeholder="SANAI Online"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">Label Tombol CTA *</label>
                <input 
                  v-model="form.sanai_button_label"
                  type="text" 
                  required
                  placeholder="SANAI Online"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">URL Target Portal Layanan (Tautan Eksternal) *</label>
              <input 
                v-model="form.sanai_url"
                type="url" 
                required
                placeholder="https://sanai-dukcapil.dompukab.go.id"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">Deskripsi Singkat Promotional Card</label>
              <textarea 
                v-model="form.sanai_description"
                rows="3"
                placeholder="Urus dokumen kependudukan secara online dengan mudah, cepat, dan tanpa harus datang ke kantor."
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              ></textarea>
            </div>
          </div>

          <!-- Behavior & Visibility settings -->
          <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">
              Perilaku & Pengaturan Visibilitas
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Active status -->
              <label class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-150 dark:border-zinc-800 rounded-2xl cursor-pointer">
                <input type="checkbox" v-model="form.sanai_is_active" class="w-4 h-4 accent-primary-600 rounded" />
                <div>
                  <span class="block text-xs font-bold text-gray-800 dark:text-zinc-200">Status Aktif</span>
                  <span class="block text-[10px] text-gray-400">Aktifkan seluruh integrasi SANAI Online</span>
                </div>
              </label>

              <!-- Open in new tab -->
              <label class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-150 dark:border-zinc-800 rounded-2xl cursor-pointer">
                <input type="checkbox" v-model="form.sanai_open_new_tab" class="w-4 h-4 accent-primary-600 rounded" />
                <div>
                  <span class="block text-xs font-bold text-gray-800 dark:text-zinc-200">Buka Tab Baru (target="_blank")</span>
                  <span class="block text-[10px] text-gray-400">Buka tautan SANAI di jendela/tab baru browser</span>
                </div>
              </label>

              <!-- Display on Navbar -->
              <label class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-150 dark:border-zinc-800 rounded-2xl cursor-pointer">
                <input type="checkbox" v-model="form.sanai_display_navbar" class="w-4 h-4 accent-primary-600 rounded" />
                <div>
                  <span class="block text-xs font-bold text-gray-800 dark:text-zinc-200">Tampilkan di Header Navigasi</span>
                  <span class="block text-[10px] text-gray-400">Tombol CTA pill di sebelah kiri kotak pencarian</span>
                </div>
              </label>

              <!-- Display on Homepage -->
              <label class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-150 dark:border-zinc-800 rounded-2xl cursor-pointer">
                <input type="checkbox" v-model="form.sanai_display_homepage" class="w-4 h-4 accent-primary-600 rounded" />
                <div>
                  <span class="block text-xs font-bold text-gray-800 dark:text-zinc-200">Tampilkan Promo di Homepage</span>
                  <span class="block text-[10px] text-gray-400">Kartu promo khusus tepat di bawah Hero Banner</span>
                </div>
              </label>

              <!-- Display on Footer -->
              <label class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-zinc-800/60 border border-gray-150 dark:border-zinc-800 rounded-2xl cursor-pointer sm:col-span-2">
                <input type="checkbox" v-model="form.sanai_display_footer" class="w-4 h-4 accent-primary-600 rounded" />
                <div>
                  <span class="block text-xs font-bold text-gray-800 dark:text-zinc-200">Tampilkan di Footer Portal</span>
                  <span class="block text-[10px] text-gray-400">Tautan langsung di bagian footer Portal Layanan</span>
                </div>
              </label>
            </div>
          </div>

          <!-- Submit button -->
          <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-zinc-800">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-6 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/20 active:scale-95 transition"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Konfigurasi SANAI' }}
            </button>
          </div>

        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  settings: Object,
});

const form = useForm({
  sanai_name:             props.settings?.sanai_name || 'SANAI Online',
  sanai_url:              props.settings?.sanai_url || 'https://sanai-dukcapil.dompukab.go.id',
  sanai_description:      props.settings?.sanai_description || 'Urus dokumen kependudukan secara online dengan mudah, cepat, dan tanpa harus datang ke kantor.',
  sanai_button_label:     props.settings?.sanai_button_label || 'SANAI Online',
  sanai_is_active:        props.settings?.sanai_is_active !== '0',
  sanai_open_new_tab:     props.settings?.sanai_open_new_tab !== '0',
  sanai_display_navbar:   props.settings?.sanai_display_navbar !== '0',
  sanai_display_homepage: props.settings?.sanai_display_homepage !== '0',
  sanai_display_footer:   props.settings?.sanai_display_footer !== '0',
});

const submit = () => {
  form.post(route('admin.external-services.update'), {
    preserveScroll: true,
  });
};
</script>
