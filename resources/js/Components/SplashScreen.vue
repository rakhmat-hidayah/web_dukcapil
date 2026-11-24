<template>
  <!-- Splash Screen Overlay -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-500 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="visible"
        class="fixed inset-0 z-[999] flex items-center justify-center p-4"
        @click.self="close"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="close"></div>

        <!-- Modal Card -->
        <div class="relative z-10 w-full max-w-sm bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden animate-fadeIn">

          <!-- Close Button -->
          <button
            @click="close"
            class="absolute top-3 right-3 z-20 w-8 h-8 flex items-center justify-center rounded-full bg-white/90 dark:bg-zinc-800/90 text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100 shadow transition cursor-pointer border border-gray-200 dark:border-zinc-700"
          >
            <X class="w-4 h-4" />
          </button>

          <!-- ===== IKM Widget Type ===== -->
          <template v-if="splash.content_type === 'ikm_widget' && ikmWidget">
            <!-- Header dark -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-950 p-5 text-white">
              <div class="flex items-center justify-between mb-3">
                <span class="px-2 py-0.5 bg-blue-500/30 text-blue-200 text-[9px] font-black uppercase tracking-widest rounded flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse inline-block"></span>
                  {{ ikmWidget.survey_mechanism === 'panrb' ? 'SKM Online KemenPAN-RB' : 'Indeks Kepuasan Masyarakat' }}
                </span>
                <span class="text-[10px] text-slate-400 font-mono">Tahun {{ ikmWidget.year }}</span>
              </div>
              <h2 class="text-sm font-extrabold text-white leading-snug">{{ splash.title }}</h2>
              <p v-if="splash.subtitle" class="text-xs text-slate-300 mt-1">{{ splash.subtitle }}</p>
            </div>

            <!-- IKM Score Body -->
            <div class="p-5 space-y-4">
              <!-- panrb with score -->
              <template v-if="ikmWidget.survey_mechanism === 'panrb' && ikmWidget.score > 0">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-zinc-400">{{ ikmWidget.period_title }}</p>
                    <p class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">
                      Mutu {{ ikmWidget.category }} — {{ ikmWidget.category_label }}
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-4xl font-black text-blue-600 dark:text-blue-400 font-mono">{{ ikmWidget.score }}</span>
                    <span class="text-[9px] block text-gray-400 font-mono">Skala 25-100</span>
                  </div>
                </div>
              </template>

              <!-- panrb without score (Belum Tuntas) -->
              <template v-else-if="ikmWidget.survey_mechanism === 'panrb'">
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-700 dark:text-zinc-300">{{ ikmWidget.period_title }}</p>
                    <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase rounded tracking-wider">Survei Belum Tuntas</span>
                  </div>
                  <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-2xl border border-blue-100 dark:border-blue-900/40 text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
                    <p class="font-bold flex items-center gap-1.5 mb-1">🌐 Penilaian Berlangsung di KemenPAN-RB</p>
                    <p>Nilai IKM belum terbit. Berikan opini & penilaian Anda melalui portal SKM KemenPAN-RB.</p>
                  </div>
                </div>
              </template>

              <!-- Internal survey -->
              <template v-else>
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-zinc-400">{{ ikmWidget.period_title }}</p>
                    <p v-if="ikmWidget.score > 0" class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">
                      Mutu {{ ikmWidget.category }} — {{ ikmWidget.category_label }}
                    </p>
                    <p v-else class="text-sm font-extrabold text-amber-600 mt-0.5">{{ ikmWidget.category_label || 'Dalam Evaluasi' }}</p>
                  </div>
                  <div class="text-right">
                    <span class="text-4xl font-black text-primary-600 dark:text-primary-400 font-mono">{{ ikmWidget.score }}</span>
                    <span class="text-[9px] block text-gray-400 font-mono">Skala 25-100</span>
                  </div>
                </div>
              </template>

              <!-- CTA Buttons -->
              <div class="space-y-2">
                <a
                  v-if="ikmWidget.survey_mechanism === 'panrb' && ikmWidget.external_survey_url && !ikmWidget.score"
                  :href="ikmWidget.external_survey_url"
                  target="_blank" rel="noopener noreferrer"
                  @click="close"
                  class="w-full block text-center py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-black rounded-xl transition shadow flex items-center justify-center gap-2 cursor-pointer"
                >
                  <span>{{ splash.button_label || 'Beri Penilaian di SKM PAN-RB' }}</span>
                  <ExternalLink class="w-3.5 h-3.5" />
                </a>
                <a
                  v-else-if="splash.button_url"
                  :href="splash.button_url"
                  :target="splash.button_external ? '_blank' : '_self'"
                  @click="close"
                  class="w-full block text-center py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 text-white text-xs font-black rounded-xl transition shadow cursor-pointer"
                >
                  {{ splash.button_label || 'Lihat Selengkapnya' }}
                </a>
                <Link
                  href="/layanan/survei"
                  @click="close"
                  class="w-full block text-center py-2 bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 text-[11px] font-extrabold rounded-xl transition border border-gray-200 dark:border-zinc-700 cursor-pointer"
                >
                  Lihat Detail Survei IKM →
                </Link>
              </div>
            </div>
          </template>

          <!-- ===== Announcement Type ===== -->
          <template v-else-if="splash.content_type === 'announcement'">
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-5 text-white">
              <span class="text-xs font-black uppercase tracking-widest opacity-80">📢 Pengumuman</span>
              <h2 class="text-base font-extrabold mt-1 leading-snug">{{ splash.title }}</h2>
              <p v-if="splash.subtitle" class="text-xs opacity-80 mt-1">{{ splash.subtitle }}</p>
            </div>
            <div class="p-5 space-y-4">
              <p class="text-sm text-gray-700 dark:text-zinc-300 leading-relaxed whitespace-pre-wrap">{{ splash.custom_content }}</p>
              <div v-if="splash.button_label && splash.button_url" class="flex gap-2">
                <a :href="splash.button_url" :target="splash.button_external ? '_blank' : '_self'" @click="close"
                  class="flex-1 text-center py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black rounded-xl transition cursor-pointer">
                  {{ splash.button_label }}
                </a>
                <button @click="close" class="px-4 py-2.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 text-xs font-bold rounded-xl transition cursor-pointer">
                  Tutup
                </button>
              </div>
              <button v-else @click="close" class="w-full py-2.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 text-xs font-bold rounded-xl transition cursor-pointer">
                Tutup
              </button>
            </div>
          </template>

          <!-- ===== Image Type ===== -->
          <template v-else-if="splash.content_type === 'image' && splash.image_path">
            <div class="relative">
              <img :src="`/storage/${splash.image_path}`" class="w-full object-cover max-h-80" :alt="splash.title" />
              <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                <h2 class="text-sm font-extrabold text-white">{{ splash.title }}</h2>
                <p v-if="splash.subtitle" class="text-xs text-white/80 mt-0.5">{{ splash.subtitle }}</p>
              </div>
            </div>
            <div v-if="splash.button_label && splash.button_url" class="p-4">
              <a :href="splash.button_url" :target="splash.button_external ? '_blank' : '_self'" @click="close"
                class="w-full block text-center py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-black rounded-xl transition cursor-pointer">
                {{ splash.button_label }}
              </a>
            </div>
          </template>

          <!-- ===== Custom HTML Type ===== -->
          <template v-else-if="splash.content_type === 'custom'">
            <div class="p-5">
              <h2 class="text-base font-extrabold text-gray-900 dark:text-zinc-50 mb-1">{{ splash.title }}</h2>
              <p v-if="splash.subtitle" class="text-xs text-gray-500 dark:text-zinc-400 mb-3">{{ splash.subtitle }}</p>
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div class="prose prose-sm dark:prose-invert max-w-none" v-html="splash.custom_content"></div>
              <div class="mt-4 flex gap-2">
                <a v-if="splash.button_label && splash.button_url" :href="splash.button_url" :target="splash.button_external ? '_blank' : '_self'" @click="close"
                  class="flex-1 text-center py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-black rounded-xl transition cursor-pointer">
                  {{ splash.button_label }}
                </a>
                <button @click="close" class="flex-1 py-2.5 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 text-xs font-bold rounded-xl cursor-pointer hover:bg-gray-200 transition">
                  Tutup
                </button>
              </div>
            </div>
          </template>

          <!-- Fallback close -->
          <div v-else class="p-5 text-center">
            <h2 class="text-sm font-bold text-gray-900 dark:text-zinc-50">{{ splash.title }}</h2>
            <p v-if="splash.subtitle" class="text-xs text-gray-500 mt-1">{{ splash.subtitle }}</p>
            <button @click="close" class="mt-4 px-6 py-2 bg-gray-100 dark:bg-zinc-800 rounded-xl text-xs font-bold cursor-pointer">Tutup</button>
          </div>

          <!-- Footer hint -->
          <div class="border-t border-gray-100 dark:border-zinc-800 px-5 py-2.5 flex items-center justify-between">
            <span class="text-[10px] text-gray-400 dark:text-zinc-500">Disdukcapil Kabupaten Dompu</span>
            <button @click="close" class="text-[10px] text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300 cursor-pointer underline underline-offset-2">
              Tutup &amp; Jangan Tampilkan Lagi Hari Ini
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { X, ExternalLink } from '@lucide/vue'

const props = defineProps({
  splash: {
    type: Object,
    required: true,
  },
  ikmWidget: {
    type: Object,
    default: null,
  },
})

const visible = ref(false)

function getStorageKey() {
  return `splash_shown_${props.splash.id}`
}

function shouldShow() {
  const freq = props.splash.show_frequency
  if (freq === 'always') return true

  const key = getStorageKey()

  if (freq === 'once_per_session') {
    // Use sessionStorage
    return !sessionStorage.getItem(key)
  }

  if (freq === 'once_per_day') {
    const stored = localStorage.getItem(key)
    if (!stored) return true
    const storedDate = new Date(stored).toDateString()
    const today = new Date().toDateString()
    return storedDate !== today
  }

  return true
}

function markShown() {
  const freq = props.splash.show_frequency
  const key = getStorageKey()

  if (freq === 'once_per_session') {
    sessionStorage.setItem(key, '1')
  } else if (freq === 'once_per_day') {
    localStorage.setItem(key, new Date().toISOString())
  }
}

function close() {
  visible.value = false
  markShown()
}

onMounted(() => {
  // Show with a small delay so the page renders first
  setTimeout(() => {
    if (shouldShow()) {
      visible.value = true
      if (props.splash.show_frequency !== 'always') {
        markShown()
      }
    }
  }, 800)
})
</script>
