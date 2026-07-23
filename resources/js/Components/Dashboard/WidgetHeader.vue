<template>
  <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800/80">
    <div class="flex items-center gap-2.5">
      <div v-if="icon" class="p-2 rounded-xl bg-gray-50 dark:bg-zinc-800 text-primary-600 dark:text-primary-400">
        <component :is="icon" class="w-4 h-4" />
      </div>
      <div>
        <h3 class="text-sm font-black text-gray-900 dark:text-zinc-50 tracking-tight flex items-center gap-2">
          {{ title }}
          <span v-if="badge" class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900/30">
            {{ badge }}
          </span>
        </h3>
        <p v-if="subtitle" class="text-[11px] text-gray-400 dark:text-zinc-400 font-medium leading-none mt-1">
          {{ subtitle }}
        </p>
      </div>
    </div>

    <!-- Widget Controls -->
    <div class="flex items-center gap-1">
      <button 
        v-if="showRefresh" 
        @click="$emit('refresh')" 
        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-800 transition"
        title="Refresh Data"
      >
        <RotateCw class="w-3.5 h-3.5" />
      </button>

      <button 
        v-if="showCollapse" 
        @click="$emit('toggle-collapse')" 
        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-800 transition"
        :title="isCollapsed ? 'Expand Widget' : 'Collapse Widget'"
      >
        <ChevronDown v-if="isCollapsed" class="w-3.5 h-3.5" />
        <ChevronUp v-else class="w-3.5 h-3.5" />
      </button>

      <button 
        v-if="showFullscreen" 
        @click="$emit('toggle-fullscreen')" 
        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-800 transition"
        title="Fullscreen"
      >
        <Maximize2 class="w-3.5 h-3.5" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { RotateCw, ChevronUp, ChevronDown, Maximize2 } from '@lucide/vue';

defineProps({
  title: String,
  subtitle: String,
  badge: String,
  icon: Object,
  isCollapsed: Boolean,
  showRefresh: { type: Boolean, default: true },
  showCollapse: { type: Boolean, default: true },
  showFullscreen: { type: Boolean, default: true },
});

defineEmits(['refresh', 'toggle-collapse', 'toggle-fullscreen']);
</script>
