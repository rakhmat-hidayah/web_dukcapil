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
              <span v-if="official.show_phone && official.phone" class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15">📞 {{ official.phone }}</span>
              <span v-if="official.show_email && official.email" class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15">✉️ {{ official.email }}</span>
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

          <!-- Main Duties (Formatted cleanly) -->
          <div v-if="official.main_duties" class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-100 dark:border-slate-700 space-y-5">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
              <span class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </span>
              Tugas Pokok & Wewenang
            </h2>

            <!-- TUGAS UTAMA -->
            <div v-if="parsedOfficialDuties.tugas" class="space-y-1.5">
              <h3 class="font-bold text-sm text-blue-600 dark:text-blue-400 flex items-center gap-1.5">📌 Tugas Utama:</h3>
              <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed pl-1">{{ parsedOfficialDuties.tugas }}</p>
            </div>

            <!-- FUNGSI LIST -->
            <div v-if="parsedOfficialDuties.fungsi && parsedOfficialDuties.fungsi.length > 0" class="space-y-2.5 pt-2">
              <h3 class="font-bold text-sm text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">⚡ Fungsi & Tanggung Jawab:</h3>
              <ul class="space-y-2 pl-1">
                <li v-for="(fItem, fIdx) in parsedOfficialDuties.fungsi" :key="fIdx" class="text-sm text-slate-700 dark:text-slate-300 flex items-start gap-2.5 leading-relaxed">
                  <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs shrink-0 flex items-center justify-center mt-0.5">{{ fIdx + 1 }}</span>
                  <span>{{ fItem }}</span>
                </li>
              </ul>
            </div>

            <!-- RINCIAN SUBBAGIAN / SUB BIDANG -->
            <div v-if="parsedOfficialDuties.rincian && parsedOfficialDuties.rincian.length > 0" class="space-y-2.5 pt-2">
              <h3 class="font-bold text-sm text-purple-600 dark:text-purple-400 flex items-center gap-1.5">🏢 {{ parsedOfficialDuties.rincianTitle || 'Rincian Unit Kerja:' }}</h3>
              <ul class="space-y-2.5 pl-1">
                <li v-for="(rItem, rIdx) in parsedOfficialDuties.rincian" :key="rIdx" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 leading-relaxed shadow-sm">
                  <span class="font-bold text-blue-600 dark:text-blue-400 mr-1.5">{{ String.fromCharCode(97 + rIdx) }}).</span>
                  <span>{{ rItem }}</span>
                </li>
              </ul>
            </div>

            <!-- FALLBACK IF UNPARSED -->
            <div v-if="!parsedOfficialDuties.tugas && (!parsedOfficialDuties.fungsi || !parsedOfficialDuties.fungsi.length)" class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
              {{ official.main_duties }}
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
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  official: Object,
  relatedNews: Array,
})

const parsedOfficialDuties = computed(() => {
  const raw = props.official?.main_duties || ''
  if (!raw) return { tugas: '', fungsi: [], rincianTitle: '', rincian: [] }

  let text = raw.trim()
  let tugasText = ''
  let fungsiList = []
  let rincianTitle = ''
  let rincianList = []

  const fungsiMatchIndex = text.search(/Fungsi\s*[:\d]/i)
  if (fungsiMatchIndex !== -1) {
    tugasText = text.substring(0, fungsiMatchIndex).replace(/^Tugas\s*[:\s]*/i, '').trim()
    text = text.substring(fungsiMatchIndex)
  } else {
    if (/^Tugas\s*[:\s]*/i.test(text)) {
      tugasText = text.replace(/^Tugas\s*[:\s]*/i, '').trim()
      text = ''
    } else {
      tugasText = text
      text = ''
    }
  }

  if (text) {
    const rincianMatch = text.match(/(Sekretariat\s+Dinas\s+terdiri\s+atas:?|Subbagian\s+terdiri\s+atas:?|[A-Z][a-z\s]+terdiri\s+atas:?)/i)
    let fungsiSection = text
    let rincianSection = ''

    if (rincianMatch) {
      const idx = text.indexOf(rincianMatch[0])
      fungsiSection = text.substring(0, idx)
      rincianSection = text.substring(idx)
      rincianTitle = rincianMatch[0].trim()
      rincianSection = rincianSection.substring(rincianMatch[0].length).trim()
    }

    fungsiSection = fungsiSection.replace(/^Fungsi\s*[:\s]*/i, '').trim()
    const numberedItems = fungsiSection.split(/(?=\b\d+[\.\)])/)
    for (let item of numberedItems) {
      const cleaned = item.replace(/^\d+[\.\)]\s*/, '').replace(/;$/, '').trim()
      if (cleaned) {
        fungsiList.push(cleaned)
      }
    }

    if (rincianSection) {
      const alphaItems = rincianSection.split(/(?=\b[a-z]\s*[\.\)])/i)
      for (let item of alphaItems) {
        const cleaned = item.replace(/^[a-z]\s*[\.\)]\s*/i, '').replace(/;$/, '').trim()
        if (cleaned) {
          rincianList.push(cleaned)
        }
      }
    }
  }

  return {
    tugas: tugasText,
    fungsi: fungsiList,
    rincianTitle: rincianTitle,
    rincian: rincianList
  }
})
</script>
