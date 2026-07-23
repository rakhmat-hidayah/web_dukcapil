<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Executive Summary"
      subtitle="Ringkasan indikator kinerja utama (KPI) portal & ekosistem pelayanan"
      badge="Executive Control"
      :icon="LayoutDashboard"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div 
          v-for="kpi in summaryItems" 
          :key="kpi.id"
          class="p-4 rounded-2xl border bg-gray-50/40 dark:bg-zinc-800/30 border-gray-100 dark:border-zinc-800/80 hover:shadow-md transition-all duration-200 group flex flex-col justify-between"
        >
          <!-- Top Row: Icon & Trend badge -->
          <div class="flex items-center justify-between gap-2">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-zinc-400 truncate">
              {{ kpi.title }}
            </span>
            <span 
              class="px-2 py-0.5 text-[9px] font-bold rounded-full flex items-center gap-0.5 shrink-0"
              :class="kpi.is_positive ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400'"
            >
              {{ kpi.trend }}
            </span>
          </div>

          <!-- Middle Row: Value & Sparkline -->
          <div class="flex items-end justify-between gap-2 my-3">
            <div>
              <div class="text-2xl font-black tracking-tight text-gray-900 dark:text-zinc-50 font-sans">
                {{ kpi.value }}
              </div>
              <span class="text-[10px] text-gray-400 dark:text-zinc-500 font-medium">
                {{ kpi.unit }}
              </span>
            </div>

            <!-- Mini SVG Sparkline -->
            <div class="w-16 h-8 shrink-0">
              <svg class="w-full h-full overflow-visible" viewBox="0 0 60 30">
                <polyline
                  fill="none"
                  :stroke="kpi.is_positive ? '#10b981' : '#f43f5e'"
                  stroke-width="2.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  :points="generateSparklinePoints(kpi.sparkline)"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { LayoutDashboard } from '@lucide/vue';
import WidgetHeader from './WidgetHeader.vue';

defineProps({
  summaryItems: Array,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);

const generateSparklinePoints = (values) => {
  if (!values || values.length === 0) return '0,15 60,15';
  const min = Math.min(...values);
  const max = Math.max(...values);
  const range = max - min || 1;
  const width = 60;
  const height = 24;
  const step = width / (values.length - 1);

  return values.map((val, idx) => {
    const x = idx * step;
    const y = height - ((val - min) / range) * height + 3;
    return `${x},${y}`;
  }).join(' ');
};
</script>
