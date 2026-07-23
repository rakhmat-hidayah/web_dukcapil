<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Content Management Center"
      subtitle="Ringkasan status & publikasi konten di portal publik"
      badge="8 Modul Konten"
      :icon="FolderLock"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <Link 
          v-for="(mod, key) in contentStatus" 
          :key="key"
          :href="route(mod.route)"
          class="p-4 rounded-2xl border bg-gray-50/40 dark:bg-zinc-800/30 border-gray-100 dark:border-zinc-800 hover:border-primary-300 dark:hover:border-primary-800 hover:shadow-md transition-all duration-200 group block"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-black text-gray-800 dark:text-zinc-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
              {{ mod.title }}
            </span>
            <ArrowRight class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition" />
          </div>

          <div class="flex items-baseline justify-between">
            <div class="flex items-baseline gap-1.5">
              <span class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-sans">
                {{ mod.published }}
              </span>
              <span class="text-[10px] text-gray-400 dark:text-zinc-500 font-semibold uppercase">
                Online
              </span>
            </div>

            <div class="flex items-center gap-2 text-[10px] font-semibold">
              <span v-if="mod.draft !== undefined" class="text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-full">
                Draft: {{ mod.draft }}
              </span>
              <span v-if="mod.archived" class="text-gray-500 bg-gray-100 dark:bg-zinc-800 px-2 py-0.5 rounded-full">
                Arsip: {{ mod.archived }}
              </span>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FolderLock, ArrowRight } from '@lucide/vue';
import WidgetHeader from './WidgetHeader.vue';

defineProps({
  contentStatus: Object,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);
</script>
