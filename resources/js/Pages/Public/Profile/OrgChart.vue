<template>
  <PublicLayout>
    <Head title="Struktur Organisasi Interaktif - Dinas Dukcapil Kabupaten Dompu" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-xl border border-slate-100 dark:border-slate-700">
        <div>
          <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider mb-2 inline-block">Engine Visual Org Chart</span>
          <h1 class="text-2xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Struktur Organisasi Interaktif</h1>
          <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <button @click="zoomIn" class="p-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition text-xs font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
            Zoom +
          </button>
          <button @click="zoomOut" class="p-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition text-xs font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/></svg>
            Zoom -
          </button>
          <button @click="resetZoom" class="p-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition text-xs font-semibold">
            Reset
          </button>
          <button @click="printChart" class="p-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition text-xs font-semibold flex items-center gap-1.5 shadow-lg shadow-blue-600/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"/></svg>
            Cetak / Export PDF
          </button>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari Jabatan, Pejabat, atau NIP di bagan..."
          class="w-full md:w-96 px-4 py-3 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white shadow-md focus:ring-2 focus:ring-blue-500 outline-none"
        />
      </div>

      <!-- Canvas Container -->
      <div id="printable-org-chart" class="bg-slate-50/90 dark:bg-slate-950 rounded-3xl p-6 md:p-10 shadow-xl border border-slate-200 dark:border-slate-800 overflow-x-auto relative min-h-[680px]">
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] dark:bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:16px_16px] opacity-40 pointer-events-none rounded-3xl"></div>
        <div class="min-w-max mx-auto flex justify-center py-6 relative z-10">
          <div :style="{ transform: `scale(${zoomScale})`, transformOrigin: 'top center' }" class="transition-transform duration-300 ease-out flex flex-col items-center">
            <GovernmentOrgChartLayout :tree="tree" :searchQuery="searchQuery" @select-node="openNodeDrawer" />
          </div>
        </div>
      </div>
    </div>

    <!-- Node Detail Side Drawer Modal -->
    <div v-if="selectedNode" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex justify-end" @click.self="selectedNode = null">
      <div class="w-full max-w-md bg-white dark:bg-slate-800 h-full p-8 shadow-2xl overflow-y-auto border-l border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-slate-700">
          <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">Detail Simpul Organisasi</span>
          <button @click="selectedNode = null" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div class="text-center mb-6">
          <div class="w-28 h-28 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-700 mx-auto mb-4 border-2 border-slate-200 dark:border-slate-600 flex items-center justify-center shadow-lg">
            <img v-if="selectedNode.official && selectedNode.official.photo" :src="'/storage/' + selectedNode.official.photo" :alt="selectedNode.official.name" class="w-full h-full object-cover" />
            <svg v-else class="w-14 h-14 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ selectedNode.official ? selectedNode.official.name : selectedNode.node_title }}</h2>
          <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ selectedNode.node_title }}</p>
          <p v-if="selectedNode.official && selectedNode.official.nip" class="text-xs text-slate-400 mt-0.5">NIP. {{ selectedNode.official.nip }}</p>
        </div>

        <div v-if="selectedNode.official" class="space-y-4 text-xs">
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900">
            <span class="text-slate-400 font-medium">Pangkat / Golongan:</span>
            <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ selectedNode.official.rank_golongan || '-' }}</p>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900">
            <span class="text-slate-400 font-medium">Unit Kerja / Bidang:</span>
            <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ selectedNode.official.department || '-' }}</p>
          </div>
          <!-- Tugas Pokok & Fungsi (Formatted cleanly) -->
          <div v-if="selectedNode.official.main_duties" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/60 dark:border-slate-800">
              <span class="p-1.5 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </span>
              <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 dark:text-white">Tugas Pokok & Fungsi</h3>
            </div>

            <!-- TUGAS UTAMA -->
            <div v-if="parsedDuties.tugas" class="space-y-1">
              <h4 class="font-bold text-xs text-blue-600 dark:text-blue-400">📌 Tugas Utama:</h4>
              <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed pl-1">{{ parsedDuties.tugas }}</p>
            </div>

            <!-- FUNGSI LIST -->
            <div v-if="parsedDuties.fungsi && parsedDuties.fungsi.length > 0" class="space-y-2 pt-1">
              <h4 class="font-bold text-xs text-emerald-600 dark:text-emerald-400">⚡ Fungsi:</h4>
              <ul class="space-y-1.5 pl-1">
                <li v-for="(fItem, fIdx) in parsedDuties.fungsi" :key="fIdx" class="text-xs text-slate-700 dark:text-slate-300 flex items-start gap-2 leading-relaxed">
                  <span class="w-4 h-4 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-[10px] shrink-0 flex items-center justify-center mt-0.5">{{ fIdx + 1 }}</span>
                  <span>{{ fItem }}</span>
                </li>
              </ul>
            </div>

            <!-- RINCIAN SUBBAGIAN / SUB BIDANG -->
            <div v-if="parsedDuties.rincian && parsedDuties.rincian.length > 0" class="space-y-2 pt-1">
              <h4 class="font-bold text-xs text-purple-600 dark:text-purple-400">🏢 {{ parsedDuties.rincianTitle || 'Rincian Unit Kerja:' }}</h4>
              <ul class="space-y-2 pl-1">
                <li v-for="(rItem, rIdx) in parsedDuties.rincian" :key="rIdx" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 leading-relaxed shadow-sm">
                  <span class="font-bold text-blue-600 dark:text-blue-400 mr-1">{{ String.fromCharCode(97 + rIdx) }}).</span>
                  <span>{{ rItem }}</span>
                </li>
              </ul>
            </div>

            <!-- FALLBACK IF UNPARSED -->
            <div v-if="!parsedDuties.tugas && (!parsedDuties.fungsi || !parsedDuties.fungsi.length)" class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
              {{ selectedNode.official.main_duties }}
            </div>
          </div>
          <a :href="'/profil/pejabat/' + (selectedNode.official.slug || selectedNode.official.id)" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold text-xs text-center block shadow-lg shadow-blue-600/30 transition">
            Lihat Profil Lengkap Pejabat &rarr;
          </a>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import GovernmentOrgChartLayout from '@/Components/Profile/GovernmentOrgChartLayout.vue'

defineProps({
  tree: Array,
})

const zoomScale = ref(1)
const searchQuery = ref('')
const selectedNode = ref(null)

const zoomIn = () => { if (zoomScale.value < 1.8) zoomScale.value += 0.15 }
const zoomOut = () => { if (zoomScale.value > 0.5) zoomScale.value -= 0.15 }
const resetZoom = () => { zoomScale.value = 1 }

const openNodeDrawer = (node) => {
  selectedNode.value = node
}

const parsedDuties = computed(() => {
  const raw = selectedNode.value?.official?.main_duties || ''
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

const printChart = () => {
  window.print()
}
</script>
