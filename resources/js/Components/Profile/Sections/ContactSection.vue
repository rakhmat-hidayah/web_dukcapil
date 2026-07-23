<template>
  <section id="contact" class="py-6 mb-8 scroll-mt-24">
    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 sm:p-10 shadow-xl shadow-slate-200/40 dark:shadow-none border border-gray-100 dark:border-zinc-800 relative overflow-hidden">
      <!-- Decorative background blur -->
      <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-primary-500/5 dark:bg-primary-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 relative z-10 border-b border-gray-100 dark:border-zinc-800 pb-6">
        <div>
          <span class="px-3 py-1 bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-primary-200 dark:border-primary-900/40">
            Kantor Pelayanan Resmi
          </span>
          <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-zinc-50 tracking-tight mt-2">
            Kontak & Lokasi Kantor
          </h2>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Informasi alamat, jam operasional loket, serta saluran komunikasi resmi Disdukcapil Kabupaten Dompu.
          </p>
        </div>

        <a 
          :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(websiteSettings.office_address || 'Disdukcapil Kabupaten Dompu')}`"
          target="_blank"
          rel="noopener noreferrer"
          class="px-4 py-2.5 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white text-xs font-bold rounded-2xl shadow-md shadow-primary-500/20 hover:shadow-lg hover:-translate-y-0.5 active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 shrink-0 self-start md:self-auto"
        >
          <MapPin class="w-4 h-4 text-amber-300" />
          <span>Petunjuk Arah Maps</span>
          <ArrowUpRight class="w-3.5 h-3.5" />
        </a>
      </div>

      <!-- 3 Premium Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
        <!-- Card 1: Alamat Kantor -->
        <div class="p-6 rounded-3xl bg-gradient-to-br from-gray-50/80 to-slate-50/40 dark:from-zinc-800/50 dark:to-zinc-800/20 border border-gray-100 dark:border-zinc-800/80 hover:border-amber-300 dark:hover:border-amber-700/60 hover:shadow-lg hover:shadow-amber-500/5 transition-all duration-300 group flex flex-col justify-between space-y-4">
          <div>
            <div class="flex items-center gap-3 mb-4">
              <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-slate-950 shadow-md shadow-amber-500/20 flex items-center justify-center shrink-0 group-hover:scale-110 transition duration-300">
                <MapPin class="w-5 h-5 stroke-[2.5]" />
              </div>
              <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 font-mono">
                  Alamat Kantor
                </h3>
                <span class="text-[10px] text-gray-400 font-bold">Dompu, NTB</span>
              </div>
            </div>

            <p class="text-xs font-semibold text-gray-800 dark:text-zinc-200 leading-relaxed">
              {{ websiteSettings.office_address || 'Jl. Bhayangkara No. 01, Kel. Bada, Dompu, NTB 84211' }}
            </p>
          </div>

          <div class="pt-3 border-t border-gray-200/60 dark:border-zinc-700/50">
            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-amber-600 dark:text-amber-400 group-hover:underline">
              <span>Lokasi Strategis Pusat Kota</span> &rarr;
            </span>
          </div>
        </div>

        <!-- Card 2: Email & Media Sosial -->
        <div class="p-6 rounded-3xl bg-gradient-to-br from-gray-50/80 to-slate-50/40 dark:from-zinc-800/50 dark:to-zinc-800/20 border border-gray-100 dark:border-zinc-800/80 hover:border-blue-300 dark:hover:border-blue-700/60 hover:shadow-lg hover:shadow-blue-500/5 transition-all duration-300 group flex flex-col justify-between space-y-4">
          <div>
            <div class="flex items-center gap-3 mb-4">
              <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20 flex items-center justify-center shrink-0 group-hover:scale-110 transition duration-300">
                <Mail class="w-5 h-5 stroke-[2.5]" />
              </div>
              <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 font-mono">
                  Email & Media Sosial
                </h3>
                <span class="text-[10px] text-gray-400 font-bold">Saluran Resmi</span>
              </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center gap-2 text-xs font-bold text-gray-800 dark:text-zinc-200">
                <span class="text-gray-400 font-normal">Email:</span>
                <a :href="`mailto:${websiteSettings.office_email || 'dukcapil@dompukab.go.id'}`" class="hover:underline text-blue-600 dark:text-blue-400 break-all">
                  {{ websiteSettings.office_email || 'dukcapil@dompukab.go.id' }}
                </a>
              </div>

              <!-- Social Media Pills -->
              <div class="flex items-center gap-2 pt-1 flex-wrap">
                <a 
                  v-if="websiteSettings.social_instagram" 
                  :href="websiteSettings.social_instagram" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-purple-600 via-pink-600 to-amber-500 text-white text-[11px] font-bold shadow-sm hover:shadow-md hover:scale-105 active:scale-95 transition-all duration-200"
                >
                  <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                  <span>Instagram</span>
                  <ArrowUpRight class="w-3 h-3" />
                </a>

                <a 
                  v-if="websiteSettings.social_facebook" 
                  :href="websiteSettings.social_facebook" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[11px] font-bold shadow-sm hover:shadow-md hover:scale-105 active:scale-95 transition-all duration-200"
                >
                  <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.592 0 9 1.583 9 4.615V8z"/></svg>
                  <span>Facebook</span>
                  <ArrowUpRight class="w-3 h-3" />
                </a>
              </div>
            </div>
          </div>

          <div class="pt-3 border-t border-gray-200/60 dark:border-zinc-700/50">
            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-600 dark:text-blue-400 group-hover:underline">
              <span>Layanan Informasi Medsos</span> &rarr;
            </span>
          </div>
        </div>

        <!-- Card 3: Jam Pelayanan Loket -->
        <div class="p-6 rounded-3xl bg-gradient-to-br from-gray-50/80 to-slate-50/40 dark:from-zinc-800/50 dark:to-zinc-800/20 border border-gray-100 dark:border-zinc-800/80 hover:border-emerald-300 dark:hover:border-emerald-700/60 hover:shadow-lg hover:shadow-emerald-500/5 transition-all duration-300 group flex flex-col justify-between space-y-4">
          <div>
            <div class="flex items-center gap-3 mb-4">
              <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-500/20 flex items-center justify-center shrink-0 group-hover:scale-110 transition duration-300">
                <Clock class="w-5 h-5 stroke-[2.5]" />
              </div>
              <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 font-mono">
                  Jam Pelayanan Loket
                </h3>
                <span class="text-[10px] text-gray-400 font-bold">Hari Kerja Usaha</span>
              </div>
            </div>

            <p class="text-xs font-semibold text-gray-800 dark:text-zinc-200 leading-relaxed">
              {{ websiteSettings.office_work_hours || 'Senin - Kamis: 08:00 - 16:00 WITA | Jumat: 08:00 - 16:30 WITA' }}
            </p>
          </div>

          <div class="pt-3 border-t border-gray-200/60 dark:border-zinc-700/50 flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 group-hover:underline">
              <span>Jam Operasional Loket</span> &rarr;
            </span>
            <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold">
              Senin - Jumat
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { MapPin, Mail, Clock, ArrowUpRight } from '@lucide/vue';

defineProps({ setting: Object });

const page = usePage();
const websiteSettings = computed(() => page.props.website_settings || {});
</script>
