<template>
  <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 text-gray-900 dark:text-zinc-50 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white dark:bg-zinc-900 rounded-3xl shadow-xl border border-gray-100 dark:border-zinc-800 p-8 text-center transition-all-200">
      <div class="w-16 h-16 bg-primary-100 dark:bg-primary-950/50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-primary-600 dark:text-primary-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
      </div>

      <h1 class="text-2xl font-bold font-sans tracking-tight mb-2">
        Dukcapil Dompu
      </h1>
      <p class="text-sm text-gray-500 dark:text-zinc-400 mb-6">
        Sistem Informasi Kependudukan & Layanan Publik Kabupaten Dompu
      </p>

      <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-accent-50 dark:bg-accent-950/30 text-accent-700 dark:text-accent-400 text-xs font-semibold rounded-full mb-8">
        <span class="w-1.5 h-1.5 bg-accent-500 rounded-full animate-pulse"></span>
        Phase 1: Core Foundation Scaffolded
      </div>

      <div class="space-y-3">
        <div class="p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl text-left flex items-center justify-between text-xs">
          <span class="text-gray-500 dark:text-zinc-400">Laravel Version</span>
          <span class="font-mono font-semibold text-gray-700 dark:text-zinc-300">v13</span>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl text-left flex items-center justify-between text-xs">
          <span class="text-gray-500 dark:text-zinc-400">Vue Version</span>
          <span class="font-mono font-semibold text-gray-700 dark:text-zinc-300">v3.x</span>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl text-left flex items-center justify-between text-xs">
          <span class="text-gray-500 dark:text-zinc-400">Tailwind CSS</span>
          <span class="font-mono font-semibold text-gray-700 dark:text-zinc-300">v4.0</span>
        </div>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-100 dark:border-zinc-800 flex justify-between items-center text-xs text-gray-400">
        <span>© 2026 Pemkab Dompu</span>
        <button 
          @click="toggleDarkMode" 
          class="p-2 bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 rounded-xl hover:bg-gray-200 dark:hover:bg-zinc-700 transition-colors"
        >
          {{ isDark ? '☀️ Light' : '🌙 Dark' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isDark = ref(false);

const toggleDarkMode = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('dark-mode', 'true');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('dark-mode', 'false');
  }
};

onMounted(() => {
  isDark.value = localStorage.getItem('dark-mode') === 'true' || 
                 (!('dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
});
</script>
