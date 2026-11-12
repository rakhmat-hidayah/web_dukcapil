<template>
  <PublicLayout>
    <Head title="Struktur Organisasi Interaktif - Dinas Dukcapil Kabupaten Dompu" />

    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-7 shadow-xl border border-slate-100 dark:border-slate-700">
        <div>
          <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Struktur Organisasi Interaktif</h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <button @click="zoomIn" class="p-2 sm:p-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition text-xs font-semibold flex items-center gap-1 active:scale-95 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
            Zoom +
          </button>
          <button @click="zoomOut" class="p-2 sm:p-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition text-xs font-semibold flex items-center gap-1 active:scale-95 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/></svg>
            Zoom -
          </button>
          <button @click="fitToScreen" class="p-2 sm:p-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition text-xs font-semibold active:scale-95 shadow-sm">
            Sesuaikan Layar
          </button>
          <button @click="resetZoom" class="p-2 sm:p-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition text-xs font-semibold active:scale-95 shadow-sm">
            Reset ({{ Math.round(zoomScale * 100) }}%)
          </button>
          <button @click="centerChart" class="p-2 sm:p-2.5 bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white rounded-xl transition text-xs font-semibold flex items-center gap-1 active:scale-95 border border-blue-200 dark:border-blue-800 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            Pusatkan
          </button>
          <button @click="printChart" class="p-2 sm:p-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition text-xs font-semibold flex items-center gap-1.5 shadow-lg shadow-blue-600/30 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"/></svg>
            Cetak / PDF
          </button>
        </div>
      </div>

      <!-- Search & Mobile Gesture Hint -->
      <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-96">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari Jabatan, Pejabat, atau NIP di bagan..."
            class="w-full pl-10 pr-9 py-2.5 sm:py-3 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white shadow-md focus:ring-2 focus:ring-blue-500 outline-none"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Touch Hint Badge for Mobile Users -->
        <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300 bg-blue-50/80 dark:bg-slate-800/80 px-3.5 py-2 rounded-xl border border-blue-100 dark:border-slate-700 w-fit">
          <span class="text-sm">🖐️</span>
          <span><strong>Sentuh & geser</strong> untuk menggerakkan bagan • <strong>Cubit</strong> untuk zoom</span>
        </div>
      </div>

      <!-- Canvas Container with Touch Pan & Mouse Drag -->
      <div 
        ref="chartContainer"
        id="printable-org-chart"
        @click.capture="handleContainerClickCapture"
        @mousedown="handleMouseDown"
        @mousemove="handleMouseMove"
        @mouseup="handleMouseUp"
        @mouseleave="handleMouseUp"
        @touchstart="handleTouchStart"
        @touchend="handleTouchEnd"
        @touchcancel="handleTouchEnd"
        @wheel="handleWheel"
        :class="[
          'bg-slate-50/90 dark:bg-slate-950 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 relative select-none',
          'overflow-auto overscroll-contain max-h-[75vh] min-h-[520px] sm:min-h-[680px]',
          isDragging ? 'cursor-grabbing' : 'cursor-grab'
        ]"
        style="-webkit-overflow-scrolling: touch; touch-action: pan-x pan-y;"
      >
        <!-- Background Grid Pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] dark:bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:16px_16px] opacity-40 pointer-events-none rounded-3xl"></div>

        <!-- Inner Scaled Wrapper with Exact Dynamic Dimensions -->
        <div 
          class="relative p-6 sm:p-10 transition-all duration-150 flex flex-col items-center justify-start mx-auto z-10"
          :style="wrapperStyle"
        >
          <div 
            ref="chartContent"
            :style="{
              transform: `scale(${zoomScale})`,
              transformOrigin: 'top left',
              width: '1200px',
            }"
            class="transition-transform duration-150 ease-out will-change-transform"
          >
            <GovernmentOrgChartLayout :tree="tree" :searchQuery="searchQuery" @select-node="openNodeDrawer" />
          </div>
        </div>

        <!-- Floating Quick Controls (Sticky Bottom Right of Canvas) -->
        <div class="sticky bottom-4 right-4 z-20 flex items-center justify-end gap-1.5 p-1.5 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 w-fit ml-auto mr-4 mb-2 pointer-events-auto">
          <button @click.stop="zoomIn" title="Perbesar" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-blue-600 hover:text-white flex items-center justify-center font-bold text-sm transition active:scale-95 text-slate-700 dark:text-slate-200">
            +
          </button>
          <span class="text-[11px] font-mono font-bold px-1 text-slate-700 dark:text-slate-300 min-w-[38px] text-center">
            {{ Math.round(zoomScale * 100) }}%
          </span>
          <button @click.stop="zoomOut" title="Perkecil" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-blue-600 hover:text-white flex items-center justify-center font-bold text-sm transition active:scale-95 text-slate-700 dark:text-slate-200">
            -
          </button>
          <button @click.stop="fitToScreen" title="Sesuaikan Layar" class="px-2 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-blue-600 hover:text-white flex items-center justify-center text-[11px] font-semibold transition active:scale-95 text-slate-700 dark:text-slate-200">
            Fit
          </button>
          <button @click.stop="centerChart" title="Pusatkan Bagan" class="px-2.5 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white flex items-center justify-center text-xs font-semibold gap-1 transition active:scale-95 border border-blue-200 dark:border-blue-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            <span>Pusat</span>
          </button>
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
            <img v-if="selectedNode.official && selectedNode.official.photo" :src="`/uploads/${selectedNode.official.photo.replace(/^\/?(storage|uploads)\//, '')}`" :alt="selectedNode.official.name" class="w-full h-full object-cover" />
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import GovernmentOrgChartLayout from '@/Components/Profile/GovernmentOrgChartLayout.vue'

defineProps({
  tree: Array,
})

const chartContainer = ref(null)
const chartContent = ref(null)
const zoomScale = ref(1)
const searchQuery = ref('')
const selectedNode = ref(null)
const contentHeight = ref(1500)
const isDragging = ref(false)
const hasMoved = ref(false)

let isPointerDown = false
let startX = 0
let startY = 0
let startScrollLeft = 0
let startScrollTop = 0
let initialPinchDistance = 0
let initialPinchScale = 1
let resizeObserver = null

const wrapperStyle = computed(() => {
  const scaledWidth = 1200 * zoomScale.value
  const scaledHeight = contentHeight.value * zoomScale.value
  return {
    width: `${scaledWidth}px`,
    minWidth: `${scaledWidth}px`,
    height: `${scaledHeight}px`,
    minHeight: `${scaledHeight}px`,
  }
})

const updateContentHeight = () => {
  if (chartContent.value) {
    const child = chartContent.value.firstElementChild
    if (child) {
      contentHeight.value = child.offsetHeight || child.scrollHeight || 1500
    }
  }
}

const zoomIn = () => {
  zoomScale.value = Math.min(1.8, Math.round((zoomScale.value + 0.15) * 100) / 100)
}

const zoomOut = () => {
  zoomScale.value = Math.max(0.35, Math.round((zoomScale.value - 0.15) * 100) / 100)
}

const resetZoom = () => {
  zoomScale.value = typeof window !== 'undefined' && window.innerWidth < 640 ? 0.65 : 1.0
  nextTick(() => centerChart())
}

const fitToScreen = () => {
  if (!chartContainer.value) return
  const containerW = chartContainer.value.clientWidth - 48
  const calculated = Math.min(1.2, Math.max(0.35, containerW / 1200))
  zoomScale.value = Math.round(calculated * 100) / 100
  nextTick(() => centerChart())
}

const centerChart = () => {
  if (!chartContainer.value) return
  const container = chartContainer.value
  const maxScrollLeft = container.scrollWidth - container.clientWidth
  if (maxScrollLeft > 0) {
    container.scrollTo({
      left: maxScrollLeft / 2,
      top: 0,
      behavior: 'smooth'
    })
  } else {
    container.scrollTo({ left: 0, top: 0, behavior: 'smooth' })
  }
}

const isInteractiveElement = (target) => {
  if (!target) return false
  return !!target.closest('button, input, select, textarea, a')
}

// Intercept clicks during dragging so cards don't pop open the drawer
const handleContainerClickCapture = (e) => {
  if (hasMoved.value) {
    e.stopPropagation()
    e.preventDefault()
  }
}

// Mouse drag handlers
const handleMouseDown = (e) => {
  if (e.button !== 0 || isInteractiveElement(e.target)) return
  isPointerDown = true
  hasMoved.value = false
  isDragging.value = false
  startX = e.clientX
  startY = e.clientY
  if (chartContainer.value) {
    startScrollLeft = chartContainer.value.scrollLeft
    startScrollTop = chartContainer.value.scrollTop
  }
}

const handleMouseMove = (e) => {
  if (!isPointerDown || !chartContainer.value) return
  const dx = e.clientX - startX
  const dy = e.clientY - startY

  if (!hasMoved.value && (Math.abs(dx) > 5 || Math.abs(dy) > 5)) {
    hasMoved.value = true
    isDragging.value = true
  }

  if (hasMoved.value) {
    chartContainer.value.scrollLeft = startScrollLeft - dx
    chartContainer.value.scrollTop = startScrollTop - dy
    e.preventDefault()
  }
}

const handleMouseUp = () => {
  isPointerDown = false
  isDragging.value = false
  if (hasMoved.value) {
    setTimeout(() => {
      hasMoved.value = false
    }, 120)
  }
}

// Touch drag handlers
const handleTouchStart = (e) => {
  if (isInteractiveElement(e.target)) return

  if (e.touches.length === 1) {
    isPointerDown = true
    hasMoved.value = false
    isDragging.value = false
    startX = e.touches[0].clientX
    startY = e.touches[0].clientY
    if (chartContainer.value) {
      startScrollLeft = chartContainer.value.scrollLeft
      startScrollTop = chartContainer.value.scrollTop
    }
  } else if (e.touches.length === 2) {
    isPointerDown = false
    isDragging.value = false
    hasMoved.value = true
    initialPinchDistance = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
    initialPinchScale = zoomScale.value
  }
}

const handleTouchMove = (e) => {
  if (e.touches.length === 2) {
    if (initialPinchDistance > 0) {
      const currentDistance = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      )
      const scaleDelta = currentDistance / initialPinchDistance
      let newScale = initialPinchScale * scaleDelta
      newScale = Math.min(Math.max(newScale, 0.35), 1.8)
      zoomScale.value = Math.round(newScale * 100) / 100
      if (e.cancelable) e.preventDefault()
    }
    return
  }

  if (!isPointerDown || e.touches.length !== 1 || !chartContainer.value) return

  const currentX = e.touches[0].clientX
  const currentY = e.touches[0].clientY
  const dx = currentX - startX
  const dy = currentY - startY

  if (!hasMoved.value && (Math.abs(dx) > 6 || Math.abs(dy) > 6)) {
    hasMoved.value = true
    isDragging.value = true
  }

  if (hasMoved.value) {
    const prevScrollLeft = chartContainer.value.scrollLeft
    const prevScrollTop = chartContainer.value.scrollTop

    chartContainer.value.scrollLeft = startScrollLeft - dx
    chartContainer.value.scrollTop = startScrollTop - dy

    const scrolledX = chartContainer.value.scrollLeft !== prevScrollLeft
    const scrolledY = chartContainer.value.scrollTop !== prevScrollTop

    if (scrolledX || scrolledY || Math.abs(dx) > Math.abs(dy)) {
      if (e.cancelable) e.preventDefault()
    }
  }
}

const handleTouchEnd = () => {
  isPointerDown = false
  isDragging.value = false
  initialPinchDistance = 0
  if (hasMoved.value) {
    setTimeout(() => {
      hasMoved.value = false
    }, 120)
  }
}

const handleWheel = (e) => {
  if (e.ctrlKey || e.metaKey) {
    e.preventDefault()
    const zoomFactor = e.deltaY < 0 ? 0.08 : -0.08
    const newScale = Math.min(1.8, Math.max(0.35, Math.round((zoomScale.value + zoomFactor) * 100) / 100))
    zoomScale.value = newScale
  }
}

const openNodeDrawer = (node) => {
  if (hasMoved.value) return
  selectedNode.value = node
}

const printChart = () => {
  window.print()
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    if (window.innerWidth < 640) {
      zoomScale.value = 0.65
    } else if (window.innerWidth < 1024) {
      zoomScale.value = 0.85
    } else {
      zoomScale.value = 1.0
    }

    window.addEventListener('mouseup', handleMouseUp)
    window.addEventListener('touchend', handleTouchEnd)
  }

  if (chartContainer.value) {
    chartContainer.value.addEventListener('touchmove', handleTouchMove, { passive: false })
  }

  if (chartContent.value?.firstElementChild && typeof ResizeObserver !== 'undefined') {
    resizeObserver = new ResizeObserver(() => {
      updateContentHeight()
    })
    resizeObserver.observe(chartContent.value.firstElementChild)
  }
  updateContentHeight()

  setTimeout(() => {
    centerChart()
  }, 250)
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('mouseup', handleMouseUp)
    window.removeEventListener('touchend', handleTouchEnd)
  }
  if (chartContainer.value) {
    chartContainer.value.removeEventListener('touchmove', handleTouchMove)
  }
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})

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
</script>

<style scoped>
@media print {
  #printable-org-chart {
    overflow: visible !important;
    max-height: none !important;
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
  }
}
</style>
