<template>
  <Head title="Manajemen Kontak & Medsos - Admin CMS" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 dark:border-zinc-800 pb-4">
        <div>
          <h1 class="text-xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
            Pengaturan Kontak, Media Sosial & Peta Kantor
          </h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Kelola alamat instansi, email resmi, jam operasional, media sosial (Instagram & Facebook), WhatsApp, dan koordinat peta.
          </p>
        </div>
      </div>

      <!-- Settings Form Card -->
      <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-6 shadow-sm">
        <form @submit.prevent="submit" class="space-y-6">
          
          <!-- Alert Success -->
          <div v-if="$page.props.flash.success" class="p-4 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold rounded-xl">
            {{ $page.props.flash.success }}
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 1. Alamat Kantor -->
            <div class="md:col-span-2">
              <label class="block text-xs font-extrabold text-gray-700 dark:text-zinc-300 mb-1.5">
                Alamat Lengkap Kantor <span class="text-rose-500">*</span>
              </label>
              <textarea
                v-model="form.office_address"
                rows="3"
                placeholder="Jl. Bhayangkara No. 01, Kel. Bada, Dompu, NTB 84211"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none"
              ></textarea>
              <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1">Ditampilkan pada halaman Kontak, Footer Portal, dan Widget Layanan Informasi.</p>
              <span v-if="form.errors.office_address" class="text-[11px] text-rose-500 font-bold block mt-1">{{ form.errors.office_address }}</span>
            </div>

            <!-- 2. Email Resmi -->
            <div>
              <label class="block text-xs font-extrabold text-gray-700 dark:text-zinc-300 mb-1.5">
                Alamat Email Resmi <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.office_email"
                type="email"
                placeholder="dukcapil@dompukab.go.id"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none"
              />
              <span v-if="form.errors.office_email" class="text-[11px] text-rose-500 font-bold block mt-1">{{ form.errors.office_email }}</span>
            </div>

            <!-- 3. WhatsApp Help Center -->
            <div>
              <label class="block text-xs font-extrabold text-gray-700 dark:text-zinc-300 mb-1.5">
                Nomor WhatsApp Center (Format Internasional) <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.whatsapp_number"
                type="text"
                placeholder="628111222333"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none font-mono"
              />
              <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1">Gunakan kode negara tanpa tanda +, contoh: 628111222333.</p>
              <span v-if="form.errors.whatsapp_number" class="text-[11px] text-rose-500 font-bold block mt-1">{{ form.errors.whatsapp_number }}</span>
            </div>

            <!-- 4. Jam Operasional -->
            <div class="md:col-span-2">
              <label class="block text-xs font-extrabold text-gray-700 dark:text-zinc-300 mb-1.5">
                Jam Operasional Pelayanan Loket <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.office_work_hours"
                type="text"
                placeholder="Senin - Kamis: 08:00 - 16:00 WITA | Jumat: 08:00 - 11:30 WITA"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none"
              />
              <span v-if="form.errors.office_work_hours" class="text-[11px] text-rose-500 font-bold block mt-1">{{ form.errors.office_work_hours }}</span>
            </div>

            <!-- 5. Media Sosial: Instagram -->
            <div>
              <label class="block text-xs font-extrabold text-gray-700 dark:text-zinc-300 mb-1.5 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-pink-600 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                <span>URL / Akun Instagram Resmi</span>
              </label>
              <input
                v-model="form.social_instagram"
                type="text"
                placeholder="https://instagram.com/dukcapil.dompu"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none"
              />
              <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1">Contoh: https://instagram.com/dukcapil.dompu</p>
              <span v-if="form.errors.social_instagram" class="text-[11px] text-rose-500 font-bold block mt-1">{{ form.errors.social_instagram }}</span>
            </div>

            <!-- 6. Media Sosial: Facebook -->
            <div>
              <label class="block text-xs font-extrabold text-gray-700 dark:text-zinc-300 mb-1.5 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600 fill-current" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.592 0 9 1.583 9 4.615V8z"/></svg>
                <span>URL / Akun Facebook Resmi</span>
              </label>
              <input
                v-model="form.social_facebook"
                type="text"
                placeholder="https://facebook.com/dukcapil.dompu"
                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none"
              />
              <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1">Contoh: https://facebook.com/dukcapil.dompu</p>
              <span v-if="form.errors.social_facebook" class="text-[11px] text-rose-500 font-bold block mt-1">{{ form.errors.social_facebook }}</span>
            </div>

            <!-- 7. Map Latitude & Longitude -->
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100 dark:border-zinc-800">
              <div>
                <label class="block text-xs font-extrabold text-gray-700 dark:text-zinc-300 mb-1.5">Latitude Peta Presisi</label>
                <input
                  v-model="form.map_latitude"
                  type="text"
                  placeholder="-8.536780"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none font-mono"
                />
              </div>

              <div>
                <label class="block text-xs font-extrabold text-gray-700 dark:text-zinc-300 mb-1.5">Longitude Peta Presisi</label>
                <input
                  v-model="form.map_longitude"
                  type="text"
                  placeholder="118.461295"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-primary-500 outline-none font-mono"
                />
              </div>
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-zinc-800">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 bg-primary-600 hover:bg-primary-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-primary-500/20 active:scale-95 transition disabled:opacity-50 cursor-pointer"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan Kontak & Medsos' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  settings: Object,
})

const form = useForm({
  office_address: props.settings.office_address || '',
  office_email: props.settings.office_email || '',
  office_work_hours: props.settings.office_work_hours || '',
  whatsapp_number: props.settings.whatsapp_number || '',
  social_instagram: props.settings.social_instagram || '',
  social_facebook: props.settings.social_facebook || '',
  map_latitude: props.settings.map_latitude || '-8.536780',
  map_longitude: props.settings.map_longitude || '118.461295',
})

function submit() {
  form.post(route('admin.office-contact.update'))
}
</script>
