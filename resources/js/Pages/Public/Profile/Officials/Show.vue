<template>
  <PublicLayout>
    <Head :title="official.name + ' - Profil Pejabat Dukcapil Dompu'" />

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6">
        <Link href="/" class="hover:underline">Beranda</Link>
        <span>/</span>
        <Link href="/profil/pejabat" class="hover:underline">Direktori Pejabat</Link>
        <span>/</span>
        <span class="text-slate-900 dark:text-white font-medium">{{ official.name }}</span>
      </nav>

      <!-- Glassmorphism Profile Header Card -->
      <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-3xl p-8 md:p-12 shadow-2xl mb-8 relative overflow-hidden">
        <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
          <div class="w-36 h-36 md:w-44 md:h-44 rounded-3xl overflow-hidden bg-white/10 border-4 border-white/20 shadow-xl shrink-0 flex items-center justify-center">
            <img v-if="official.photo" :src="'/storage/' + official.photo" :alt="official.name" class="w-full h-full object-cover" />
            <svg v-else class="w-20 h-20 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div class="text-center md:text-left">
            <span class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full text-xs font-semibold uppercase tracking-wider mb-2 inline-block border border-blue-400/30">Profil Resmi Pejabat</span>
            <h1 class="text-2xl md:text-4xl font-black text-white tracking-tight mb-2">{{ official.name }}</h1>
            <p class="text-base text-blue-200 font-semibold mb-1">{{ official.position_title }}</p>
            <p v-if="official.nip" class="text-xs text-blue-300/80 mb-4">NIP. {{ official.nip }} &bull; {{ official.rank_golongan }}</p>
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs">
              <span v-if="official.phone" class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15">📞 {{ official.phone }}</span>
              <span v-if="official.email" class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15">✉️ {{ official.email }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-8 space-y-8">
          <!-- Biography -->
          <div v-if="official.biography" class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-100 dark:border-slate-700">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Biografi & Ringkasan Profil</h2>
            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ official.biography }}</p>
          </div>

          <!-- Main Duties -->
          <div v-if="official.main_duties" class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-100 dark:border-slate-700">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Tugas Pokok & Wewenang</h2>
            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ official.main_duties }}</p>
          </div>

          <!-- Education Timeline -->
          <div v-if="official.educations && official.educations.length > 0" class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-100 dark:border-slate-700">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Riwayat Pendidikan</h2>
            <div class="border-l-2 border-blue-500 ml-3 pl-6 space-y-6">
              <div v-for="edu in official.educations" :key="edu.id">
                <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ edu.start_year }} - {{ edu.end_year }}</span>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm mt-0.5">{{ edu.degree }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ edu.institution }} {{ edu.major ? ' - ' + edu.major : '' }}</p>
              </div>
            </div>
          </div>

          <!-- Achievements -->
          <div v-if="official.achievements && official.achievements.length > 0" class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-100 dark:border-slate-700">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Prestasi & Penghargaan</h2>
            <div class="space-y-4">
              <div v-for="ach in official.achievements" :key="ach.id" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 flex items-start gap-4">
                <div class="p-2.5 bg-amber-500 text-white rounded-xl font-bold text-xs shrink-0">{{ ach.year }}</div>
                <div>
                  <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ ach.title }}</h3>
                  <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">{{ ach.issuer }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-4 space-y-6">
          <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-4">Informasi Jabatan</h3>
            <div class="space-y-3 text-xs">
              <div>
                <span class="text-slate-400 block">Unit Kerja:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ official.department }}</span>
              </div>
              <div>
                <span class="text-slate-400 block">Status:</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 inline-block mt-1">{{ official.status }}</span>
              </div>
              <div v-if="official.office_address">
                <span class="text-slate-400 block">Alamat Kantor:</span>
                <span class="text-slate-700 dark:text-slate-300">{{ official.office_address }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps({
  official: Object,
  relatedNews: Array,
})
</script>
