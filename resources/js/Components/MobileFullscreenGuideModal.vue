<template>
  <Teleport to="body">
    <div 
      v-if="modelValue" 
      class="fixed inset-0 z-[100] flex flex-col justify-end sm:justify-center items-center p-0 sm:p-4 pt-[max(env(safe-area-inset-top,0px),16px)] pb-[max(env(safe-area-inset-bottom,0px),16px)] sm:py-4 bg-black/65 backdrop-blur-sm animate-in fade-in duration-200"
      @click.self="close"
    >
      <div 
        class="bg-white dark:bg-zinc-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl border border-gray-100 dark:border-zinc-800 shadow-2xl max-h-full sm:max-h-[90vh] flex flex-col overflow-hidden text-slate-800 dark:text-zinc-100"
      >
        <!-- Modal Header -->
        <div class="shrink-0 p-5 sm:p-6 pb-3 border-b border-gray-100 dark:border-zinc-800 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-inner">
              <Smartphone class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white leading-tight">
                Mode Layar Penuh (App Mode)
              </h3>
              <p class="text-[10.5px] text-gray-500 dark:text-zinc-400">
                Hilangkan bilah alamat atas &amp; navigasi bawah browser
              </p>
            </div>
          </div>
          <button 
            type="button"
            @click="close"
            class="p-1.5 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer"
            aria-label="Tutup"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-5 sm:p-6 space-y-4">

        <!-- Android Instant Fullscreen Button (If Supported) -->
        <div v-if="canFullscreen" class="p-3.5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/40 dark:to-indigo-950/40 border border-blue-200 dark:border-blue-900/60 rounded-2xl space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-black text-blue-900 dark:text-blue-200 flex items-center gap-1.5">
              <Sparkles class="w-3.5 h-3.5 text-amber-500" />
              Layar Penuh Instan (Android)
            </span>
            <span class="text-[10px] text-blue-700 dark:text-blue-300 font-medium">Tanpa Instal</span>
          </div>
          <button 
            type="button"
            @click="toggleFullscreen" 
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs rounded-xl transition active:scale-95 flex items-center justify-center gap-2 shadow-sm cursor-pointer"
          >
            <Maximize2 v-if="!isFullscreen" class="w-4 h-4" />
            <Minimize2 v-else class="w-4 h-4" />
            <span>{{ isFullscreen ? 'Keluar dari Mode Layar Penuh' : 'Aktifkan Layar Penuh Sekarang' }}</span>
          </button>
        </div>

        <!-- OS Selector Tabs -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-700 dark:text-zinc-300">
              Panduan Hilangkan Bilah Browser:
            </span>
            <div class="inline-flex p-0.5 rounded-lg bg-gray-100 dark:bg-zinc-800 text-[11px] font-bold">
              <button
                type="button"
                @click="activeTab = 'ios'"
                :class="activeTab === 'ios' ? 'bg-white dark:bg-zinc-700 text-blue-600 dark:text-blue-300 shadow-xs' : 'text-gray-500 dark:text-zinc-400'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer"
              >
                iPhone (iOS)
              </button>
              <button
                type="button"
                @click="activeTab = 'android'"
                :class="activeTab === 'android' ? 'bg-white dark:bg-zinc-700 text-blue-600 dark:text-blue-300 shadow-xs' : 'text-gray-500 dark:text-zinc-400'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer"
              >
                Android
              </button>
            </div>
          </div>

          <!-- TAB 1: iOS iPhone Safari Guide -->
          <div v-if="activeTab === 'ios'" class="space-y-2.5 animate-in fade-in duration-150">
            <div class="p-4 bg-gray-50 dark:bg-zinc-800/60 rounded-2xl border border-gray-200/80 dark:border-zinc-700/60 space-y-3">
              <div class="flex items-center gap-2 text-xs font-black text-slate-900 dark:text-white">
                <span class="w-6 h-6 rounded-lg bg-black text-white flex items-center justify-center text-xs shrink-0"></span>
                <span>Cara Hilangkan Bilah di iPhone (Safari):</span>
              </div>
              <p class="text-[11px] text-gray-600 dark:text-zinc-400 leading-relaxed">
                Di iPhone Safari, bilah alamat atas dan bilah navigasi bawah otomatis <strong>hilang 100%</strong> dengan menambahkan aplikasi ke Layar Utama:
              </p>
              <div class="space-y-2">
                <div class="flex items-start gap-2.5 text-[11px] text-gray-700 dark:text-zinc-300">
                  <div class="w-5 h-5 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</div>
                  <div>
                    Ketuk tombol <strong>Bagikan / Share</strong> di bilah bawah browser Safari Anda (ikon kotak dengan panah ke atas: <span class="inline-block px-1.5 py-0.5 bg-gray-200 dark:bg-zinc-700 rounded font-mono font-bold text-blue-600 dark:text-blue-400">⎋</span> atau <span class="font-bold">[↑]</span>).
                  </div>
                </div>
                <div class="flex items-start gap-2.5 text-[11px] text-gray-700 dark:text-zinc-300">
                  <div class="w-5 h-5 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</div>
                  <div>
                    Gulir ke bawah dan ketuk opsi <strong>"Tambah ke Layar Utama" (Add to Home Screen)</strong> (ikon tanda tambah <span class="inline-block px-1.5 py-0.5 bg-gray-200 dark:bg-zinc-700 rounded font-bold">➕</span>).
                  </div>
                </div>
                <div class="flex items-start gap-2.5 text-[11px] text-gray-700 dark:text-zinc-300">
                  <div class="w-5 h-5 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">3</div>
                  <div>
                    Ketuk tombol <strong>"Tambah" (Add)</strong> di sudut kanan atas.
                  </div>
                </div>
                <div class="flex items-start gap-2.5 text-[11px] text-gray-700 dark:text-zinc-300">
                  <div class="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">✓</div>
                  <div class="font-semibold text-emerald-800 dark:text-emerald-300">
                    Buka ikon <strong>Dukcapil Dompu</strong> dari Layar Utama HP Anda. Aplikasi akan langsung berjalan dalam mode layar penuh tanpa bilah web apapun!
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: Android Chrome Guide -->
          <div v-else class="space-y-2.5 animate-in fade-in duration-150">
            <div class="p-4 bg-gray-50 dark:bg-zinc-800/60 rounded-2xl border border-gray-200/80 dark:border-zinc-700/60 space-y-3">
              <div class="flex items-center gap-2 text-xs font-black text-slate-900 dark:text-white">
                <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0">🤖</span>
                <span>Cara Hilangkan Bilah di Android (Chrome):</span>
              </div>
              <p class="text-[11px] text-gray-600 dark:text-zinc-400 leading-relaxed">
                Di Android, Anda bisa menggunakan tombol <strong>Layar Penuh Instan</strong> di atas atau menambahkannya ke Layar Utama:
              </p>
              <div class="space-y-2">
                <div class="flex items-start gap-2.5 text-[11px] text-gray-700 dark:text-zinc-300">
                  <div class="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</div>
                  <div>
                    Ketuk menu titik tiga (<span class="font-bold font-mono">⋮</span>) di sudut kanan atas browser Chrome Anda.
                  </div>
                </div>
                <div class="flex items-start gap-2.5 text-[11px] text-gray-700 dark:text-zinc-300">
                  <div class="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</div>
                  <div>
                    Pilih <strong>"Tambahkan ke Layar Utama" (Add to Home screen)</strong> atau <strong>"Instal Aplikasi"</strong>.
                  </div>
                </div>
                <div class="flex items-start gap-2.5 text-[11px] text-gray-700 dark:text-zinc-300">
                  <div class="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">✓</div>
                  <div class="font-semibold text-emerald-800 dark:text-emerald-300">
                    Buka dari layar beranda HP Anda — aplikasi akan otomatis berjalan dalam mode mandiri (standalone) tanpa bilah URL Chrome.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Dismiss Button -->
        <div class="shrink-0 p-5 sm:p-6 pt-3 border-t border-gray-100 dark:border-zinc-800">
          <button 
            type="button"
            @click="close"
            class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-200 text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Mengerti, Tutup Panduan
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Smartphone, Maximize2, Minimize2, X, Sparkles } from '@lucide/vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'close']);

const close = () => {
  emit('update:modelValue', false);
  emit('close');
};

const activeTab = ref('ios');
const canFullscreen = ref(false);
const isFullscreen = ref(false);

onMounted(() => {
  if (typeof window !== 'undefined') {
    // Detect iOS
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
      (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    activeTab.value = isIos ? 'ios' : 'android';

    // Fullscreen API support check
    canFullscreen.value = typeof document !== 'undefined' && 
      !!(document.fullscreenEnabled || document.webkitFullscreenEnabled);

    document.addEventListener('fullscreenchange', () => {
      isFullscreen.value = !!document.fullscreenElement;
    });
    document.addEventListener('webkitfullscreenchange', () => {
      isFullscreen.value = !!document.webkitFullscreenElement;
    });
  }
});

const toggleFullscreen = () => {
  if (typeof document === 'undefined') return;
  if (!document.fullscreenElement && !document.webkitFullscreenElement) {
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen().catch(() => {});
    } else if (document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen();
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen().catch(() => {});
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
};
</script>
