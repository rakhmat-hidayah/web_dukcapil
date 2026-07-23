<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Visitor & Traffic Analytics"
      subtitle="Tren pengunjung, perangkat, peramban (browser), sistem operasi, & halaman terpopuler"
      badge="Traffic & OS"
      :icon="PieChart"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6 space-y-6">
      <!-- Visitor Trend Area Line Chart -->
      <div class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
          Tren Kunjungan 7 Hari Terakhir
        </h4>
        <div class="h-56 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 relative">
          <div ref="trendChartEl" class="absolute inset-0"></div>
        </div>
      </div>

      <!-- Donut & Bar Charts Grid: Browsers & Platforms -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Browsers Donut Chart -->
        <div class="space-y-3">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
            Peramban (Browser) Pengunjung
          </h4>
          <div class="h-52 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 relative">
            <div ref="browserChartEl" class="absolute inset-0"></div>
          </div>
        </div>

        <!-- Operating Systems Bar Chart -->
        <div class="space-y-3">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
            Sistem Operasi (OS)
          </h4>
          <div class="h-52 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 relative">
            <div ref="platformChartEl" class="absolute inset-0"></div>
          </div>
        </div>

        <!-- Top Pages Table -->
        <div class="space-y-3">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
            Halaman Paling Banyak Dikunjungi
          </h4>
          <div class="border border-gray-100 dark:border-zinc-800 rounded-2xl overflow-hidden">
            <table class="w-full text-xs text-left">
              <thead class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800 text-gray-400 text-[9px] uppercase font-bold">
                <tr>
                  <th class="px-3 py-2">Judul Halaman</th>
                  <th class="px-3 py-2 text-right">Views</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
                <tr v-for="(pg, i) in visitorAnalytics.top_pages" :key="i" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                  <td class="px-3 py-2">
                    <p class="font-bold text-gray-800 dark:text-zinc-200 truncate max-w-[160px]">{{ pg.title }}</p>
                    <span class="text-[9px] text-gray-400 font-mono">{{ pg.path }}</span>
                  </td>
                  <td class="px-3 py-2 text-right font-mono font-bold text-primary-600 dark:text-primary-400">
                    {{ pg.views.toLocaleString('id-ID') }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { PieChart } from '@lucide/vue';
import * as echarts from 'echarts';
import WidgetHeader from './WidgetHeader.vue';

const props = defineProps({
  visitorAnalytics: Object,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);

const trendChartEl = ref(null);
const browserChartEl = ref(null);
const platformChartEl = ref(null);

let trendChart = null;
let browserChart = null;
let platformChart = null;

const initCharts = () => {
  // 1. Visitor Trend Chart (Line Area)
  if (trendChartEl.value) {
    if (trendChart) trendChart.dispose();
    trendChart = echarts.init(trendChartEl.value);
    trendChart.setOption({
      grid: { left: 15, right: 15, top: 15, bottom: 5, containLabel: true },
      tooltip: { trigger: 'axis' },
      xAxis: {
        type: 'category',
        data: props.visitorAnalytics.trend?.categories || ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        axisLabel: { fontSize: 10 }
      },
      yAxis: { type: 'value', axisLabel: { fontSize: 10 } },
      series: [{
        type: 'line',
        smooth: true,
        data: props.visitorAnalytics.trend?.series || [420, 510, 680, 590, 720, 610, 750],
        lineStyle: { width: 3, color: '#6366f1' },
        areaStyle: {
          color: {
            type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
            colorStops: [{ offset: 0, color: 'rgba(99, 102, 241, 0.3)' }, { offset: 1, color: 'rgba(99, 102, 241, 0.0)' }]
          }
        },
        itemStyle: { color: '#6366f1' }
      }]
    });
  }

  // 2. Browser Chart (Donut)
  if (browserChartEl.value) {
    if (browserChart) browserChart.dispose();
    browserChart = echarts.init(browserChartEl.value);
    const browserData = (props.visitorAnalytics.browsers || []).map(b => ({ name: b.browser, value: b.total }));
    browserChart.setOption({
      tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
      legend: { bottom: 0, textStyle: { fontSize: 9 } },
      series: [{
        type: 'pie',
        radius: ['40%', '70%'],
        center: ['50%', '42%'],
        data: browserData.length > 0 ? browserData : [{ name: 'Chrome', value: 85 }, { name: 'Edge', value: 15 }],
        label: { show: false }
      }]
    });
  }

  // 3. Platform OS Chart (Horizontal Bar)
  if (platformChartEl.value) {
    if (platformChart) platformChart.dispose();
    platformChart = echarts.init(platformChartEl.value);
    const platforms = props.visitorAnalytics.platforms || [];
    platformChart.setOption({
      grid: { left: 10, right: 15, top: 10, bottom: 5, containLabel: true },
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      xAxis: { type: 'value', axisLabel: { fontSize: 9 } },
      yAxis: { type: 'category', data: platforms.map(p => p.platform), axisLabel: { fontSize: 9 } },
      series: [{
        type: 'bar',
        data: platforms.map(p => p.total),
        itemStyle: { color: '#06b6d4', borderRadius: [0, 4, 4, 0] },
        barMaxWidth: 16
      }]
    });
  }
};

onMounted(() => {
  setTimeout(initCharts, 150);
});

watch(() => props.visitorAnalytics, () => {
  setTimeout(initCharts, 150);
}, { deep: true });
</script>
