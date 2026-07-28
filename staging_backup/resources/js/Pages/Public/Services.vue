<template>
  <Head title="Portal Layanan — Dinas Dukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="space-y-12 text-left max-w-7xl mx-auto">
      
      <!-- Hero Header -->
      <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-primary-900 via-blue-900 to-indigo-950 p-8 md:p-12 text-white shadow-xl">
        <div class="relative z-10 space-y-3 max-w-3xl">
          <span class="px-3 py-1 bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm">
            Portal Layanan Kependudukan
          </span>
          <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-tight">
            Layanan Administrasi <span class="text-amber-400">Kependudukan</span>
          </h1>
          <p class="text-xs md:text-sm text-blue-100/90 leading-relaxed font-medium">
            Pusat informasi persyaratan, unduhan formulir, pengaduan, pelacakan permohonan, dan survei kepuasan masyarakat Kabupaten Dompu.
          </p>
        </div>
      </div>

      <!-- 5 MAIN PORTAL LAYANAN CARDS -->
      <div class="space-y-4">
        <h2 class="text-sm font-extrabold uppercase tracking-wider text-gray-400 font-mono">
          Menu Layanan Utama
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- 1. Persyaratan Layanan -->
          <a 
            href="#persyaratan-berkas" 
            class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition flex flex-col justify-between group h-52"
          >
            <div class="space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl group-hover:scale-110 transition">
                📜
              </div>
              <h3 class="font-extrabold text-gray-900 dark:text-zinc-50 text-sm leading-snug group-hover:text-primary-600 transition">
                Persyaratan Layanan
              </h3>
              <p class="text-[11px] text-gray-400 leading-relaxed line-clamp-2">
                Syarat lengkap pengurusan KTP-el, KIA, Akta, dan Kartu Keluarga.
              </p>
            </div>
            <span class="text-[10px] font-bold text-primary-600 font-mono flex items-center gap-1">
              Lihat Syarat ↓
            </span>
          </a>

          <!-- 2. Formulir -->
          <Link 
            href="/downloads" 
            class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 transition flex flex-col justify-between group h-52"
          >
            <div class="space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl group-hover:scale-110 transition">
                📥
              </div>
              <h3 class="font-extrabold text-gray-900 dark:text-zinc-50 text-sm leading-snug group-hover:text-emerald-600 transition">
                Formulir
              </h3>
              <p class="text-[11px] text-gray-400 leading-relaxed line-clamp-2">
                Download berkas &amp; formulir pendaftaran resmi format PDF/Doc.
              </p>
            </div>
            <span class="text-[10px] font-bold text-emerald-600 font-mono flex items-center gap-1">
              Buka Download →
            </span>
          </Link>

          <!-- 3. Survei Kepuasan Masyarakat (IKM) -->
          <Link 
            href="/layanan/survei" 
            class="bg-white dark:bg-zinc-900 border border-amber-200 dark:border-amber-900/60 rounded-3xl p-5 shadow-sm hover:shadow-md hover:border-amber-400 transition flex flex-col justify-between group h-52 relative overflow-hidden"
          >
            <span class="absolute top-3 right-3 px-2 py-0.5 bg-amber-500 text-slate-950 text-[8px] font-black uppercase rounded">PermenPANRB</span>
            <div class="space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl group-hover:scale-110 transition">
                ⭐
              </div>
              <h3 class="font-extrabold text-gray-900 dark:text-zinc-50 text-sm leading-snug group-hover:text-amber-500 transition">
                Survei Kepuasan (IKM)
              </h3>
              <p class="text-[11px] text-gray-400 leading-relaxed line-clamp-2">
                Penilaian &amp; laporan indeks kepuasan masyarakat atas pelayanan.
              </p>
            </div>
            <span class="text-[10px] font-bold text-amber-500 font-mono flex items-center gap-1">
              Buka Survei →
            </span>
          </Link>

          <!-- 4. Pengaduan -->
          <Link 
            href="/pengaduan" 
            class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm hover:shadow-md hover:border-rose-300 dark:hover:border-rose-700 transition flex flex-col justify-between group h-52"
          >
            <div class="space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl group-hover:scale-110 transition">
                📣
              </div>
              <h3 class="font-extrabold text-gray-900 dark:text-zinc-50 text-sm leading-snug group-hover:text-rose-600 transition">
                Pengaduan
              </h3>
              <p class="text-[11px] text-gray-400 leading-relaxed line-clamp-2">
                Sampaikan pengaduan, aspirasi, atau keluhan kendala dokumen.
              </p>
            </div>
            <span class="text-[10px] font-bold text-rose-600 font-mono flex items-center gap-1">
              Kirim Pengaduan →
            </span>
          </Link>

          <!-- 5. Tracking -->
          <Link 
            href="/pengaduan/lacak" 
            class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-700 transition flex flex-col justify-between group h-52"
          >
            <div class="space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl group-hover:scale-110 transition">
                🔍
              </div>
              <h3 class="font-extrabold text-gray-900 dark:text-zinc-50 text-sm leading-snug group-hover:text-indigo-600 transition">
                Tracking Permohonan
              </h3>
              <p class="text-[11px] text-gray-400 leading-relaxed line-clamp-2">
                Cek status dan progress penerbitan berkas permohonan Anda.
              </p>
            </div>
            <span class="text-[10px] font-bold text-indigo-600 font-mono flex items-center gap-1">
              Lacak Berkas →
            </span>
          </Link>
        </div>
      </div>

      <!-- DETAILED SERVICE REQUIREMENTS SECTION -->
      <div id="persyaratan-berkas" class="space-y-6 pt-6 border-t border-gray-100 dark:border-zinc-800">
        <div>
          <h2 class="text-xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Persyaratan Dokumen Kependudukan</h2>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Klik jenis dokumen di bawah untuk melihat rincian persyaratan berkas dan prosedur penerbitan.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-if="services.length === 0" class="col-span-full text-center py-12 text-gray-450 text-xs">
            Belum ada jenis dokumen dipublikasikan.
          </div>

          <Link 
            v-for="item in services" 
            :key="item.id"
            :href="route('public.services.show', item.slug)"
            class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between hover:shadow-md hover:border-gray-200 dark:hover:border-zinc-700 transition group h-64"
          >
            <div class="space-y-3">
              <span class="text-4xl block transition group-hover:scale-110 duration-200 origin-left">{{ item.icon || '📝' }}</span>
              <h4 class="font-extrabold text-gray-800 dark:text-zinc-50 text-sm leading-snug group-hover:text-primary-600 transition">
                {{ item.title }}
              </h4>
              <p class="text-xs text-gray-400 leading-relaxed line-clamp-3">
                {{ item.description || 'Lihat kelengkapan berkas dan prosedur pengurusan selengkapnya.' }}
              </p>
            </div>

            <div class="border-t border-gray-50 dark:border-zinc-800 pt-3 flex justify-between items-center text-[10px] text-gray-400 font-semibold font-mono">
              <span>Biaya: <strong class="text-emerald-600 dark:text-emerald-450 font-sans">{{ item.cost }}</strong></span>
              <span class="text-primary-600 font-sans">Detail Syarat →</span>
            </div>
          </Link>
        </div>
      </div>

    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  services: Array,
});
</script>
