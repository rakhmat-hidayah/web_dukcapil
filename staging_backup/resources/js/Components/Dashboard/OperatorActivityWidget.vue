<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Timeline Aktivitas Operator"
      subtitle="Riwayat aksi pengelola & operator (Login, Edit, Upload, Import, Publish)"
      badge="Activity Log"
      :icon="History"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6 space-y-4">
      <!-- Filter Period Buttons -->
      <div class="flex items-center justify-between">
        <span class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
          Filter Periode Activity Log
        </span>
        <div class="flex gap-1 text-[10px] font-bold">
          <button 
            v-for="p in [
              { key: 'today', label: 'Hari Ini' },
              { key: '7days', label: '7 Hari' },
              { key: '30days', label: '30 Hari' },
            ]"
            :key="p.key"
            @click="$emit('change-period', p.key)"
            class="px-2.5 py-1 rounded-lg transition"
            :class="currentPeriod === p.key ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-500 hover:text-gray-900 dark:hover:text-zinc-100'"
          >
            {{ p.label }}
          </button>
        </div>
      </div>

      <!-- Timeline List -->
      <div class="space-y-3 max-h-80 overflow-y-auto pr-1 scrollbar-thin">
        <div v-if="!timeline || timeline.length === 0" class="p-8 text-center text-xs text-gray-400 italic">
          Belum ada riwayat aktivitas operator untuk periode ini.
        </div>

        <div 
          v-for="item in timeline" 
          :key="item.id"
          class="p-3 rounded-2xl border border-gray-100 dark:border-zinc-800/80 bg-gray-50/40 dark:bg-zinc-800/20 hover:bg-gray-50 dark:hover:bg-zinc-800/40 transition flex items-start gap-3"
        >
          <!-- Operator Avatar -->
          <div class="w-8 h-8 rounded-full bg-primary-600 text-white font-black flex items-center justify-center text-xs shrink-0 mt-0.5">
            {{ item.operator ? item.operator.charAt(0) : 'A' }}
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
              <p class="text-xs font-bold text-gray-800 dark:text-zinc-200 truncate">
                {{ item.operator }}
              </p>
              <span class="text-[10px] text-gray-400 font-mono shrink-0">
                {{ item.time }} ({{ item.relative_time }})
              </span>
            </div>

            <p class="text-xs text-gray-600 dark:text-zinc-400 mt-0.5 leading-relaxed">
              {{ item.description }}
            </p>

            <div v-if="item.properties && item.properties.ip" class="mt-1 flex items-center gap-2">
              <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-800 text-gray-500 text-[9px] font-mono font-semibold rounded">
                IP: {{ item.properties.ip }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { History } from '@lucide/vue';
import WidgetHeader from './WidgetHeader.vue';

defineProps({
  timeline: Array,
  currentPeriod: String,
});

defineEmits(['refresh', 'toggle-fullscreen', 'change-period']);

const collapsed = ref(false);
</script>
