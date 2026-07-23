<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Service Performance & Analytics"
      subtitle="Statistik penggunaan layanan online, integrasi SANAI, & Survei Kepuasan (IKM)"
      badge="Layanan & SANAI"
      :icon="BarChart3"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6 space-y-6">
      <!-- 4 Quick Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <!-- SANAI Clicks Today -->
        <div class="p-4 rounded-2xl border bg-cyan-50/40 dark:bg-cyan-950/20 border-cyan-100 dark:border-cyan-900/30">
          <div class="flex items-center justify-between text-cyan-600 dark:text-cyan-400">
            <span class="text-[10px] font-bold uppercase tracking-wider">Redirect SANAI (Hari Ini)</span>
            <ExternalLink class="w-4 h-4" />
          </div>
          <p class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-sans mt-2">
            {{ serviceAnalytics.sanai_today }} <span class="text-xs text-gray-400 font-normal">klik</span>
          </p>
          <span class="text-[10px] text-gray-400 dark:text-zinc-400 mt-1 block">Total: {{ serviceAnalytics.sanai_total }} pengalihan</span>
        </div>

        <!-- IKM Score -->
        <div class="p-4 rounded-2xl border bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/30">
          <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400">
            <span class="text-[10px] font-bold uppercase tracking-wider">Skor IKM (Kepuasan)</span>
            <Award class="w-4 h-4" />
          </div>
          <p class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-sans mt-2">
            {{ serviceAnalytics.ikm_score }} <span class="text-xs text-emerald-600 font-bold">/ 100</span>
          </p>
          <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1 block">{{ serviceAnalytics.ikm_category }}</span>
        </div>

        <!-- Service Requirements Count -->
        <div class="p-4 rounded-2xl border bg-indigo-50/40 dark:bg-indigo-950/20 border-indigo-100 dark:border-indigo-900/30">
          <div class="flex items-center justify-between text-indigo-600 dark:text-indigo-400">
            <span class="text-[10px] font-bold uppercase tracking-wider">Persyaratan Layanan</span>
            <FileCheck class="w-4 h-4" />
          </div>
          <p class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-sans mt-2">
            {{ serviceAnalytics.top_services?.length || 5 }} <span class="text-xs text-gray-400 font-normal">layanan</span>
          </p>
          <span class="text-[10px] text-gray-400 dark:text-zinc-400 mt-1 block">100% Informasi Aktif</span>
        </div>

        <!-- Download Rate -->
        <div class="p-4 rounded-2xl border bg-sky-50/40 dark:bg-sky-950/20 border-sky-100 dark:border-sky-900/30">
          <div class="flex items-center justify-between text-sky-600 dark:text-sky-400">
            <span class="text-[10px] font-bold uppercase tracking-wider">Aktivitas Unduh Berkas</span>
            <Download class="w-4 h-4" />
          </div>
          <p class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-sans mt-2">
            436 <span class="text-xs text-gray-400 font-normal">kali</span>
          </p>
          <span class="text-[10px] text-gray-400 dark:text-zinc-400 mt-1 block">Minggu Ini</span>
        </div>
      </div>

      <!-- Main Layout: Top Services Table & Download Chart -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Services Table -->
        <div class="space-y-3">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
            Layanan Paling Sering Diakses Publik
          </h4>

          <div class="border border-gray-100 dark:border-zinc-800 rounded-2xl overflow-hidden">
            <table class="w-full text-xs text-left">
              <thead class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800 text-gray-400 text-[9px] uppercase font-bold">
                <tr>
                  <th class="px-4 py-2.5">Icon</th>
                  <th class="px-4 py-2.5">Nama Layanan</th>
                  <th class="px-4 py-2.5">Waktu Proses</th>
                  <th class="px-4 py-2.5 text-right">Biaya</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
                <tr v-for="srv in serviceAnalytics.top_services" :key="srv.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                  <td class="px-4 py-2 text-base text-center">{{ srv.icon || '📝' }}</td>
                  <td class="px-4 py-2 font-bold text-gray-800 dark:text-zinc-200">{{ srv.title }}</td>
                  <td class="px-4 py-2 font-mono text-gray-500">{{ srv.processing_time }}</td>
                  <td class="px-4 py-2 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400">{{ srv.cost }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Download Activity Chart Container -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
              Grafik Unduh Berkas Minggu Ini
            </h4>
            <div class="flex gap-1 text-[10px] font-bold">
              <button 
                v-for="t in ['Harian', 'Mingguan', 'Bulanan']" 
                :key="t"
                @click="activePeriod = t"
                class="px-2.5 py-1 rounded-lg transition"
                :class="activePeriod === t ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-500 hover:text-gray-900 dark:hover:text-zinc-100'"
              >
                {{ t }}
              </button>
            </div>
          </div>

          <div class="h-44 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 flex items-center justify-center relative">
            <div ref="downloadChartEl" class="absolute inset-0"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { BarChart3, ExternalLink, Award, FileCheck, Download } from '@lucide/vue';
import * as echarts from 'echarts';
import WidgetHeader from './WidgetHeader.vue';

const props = defineProps({
  serviceAnalytics: Object,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);
const activePeriod = ref('Harian');
const downloadChartEl = ref(null);
let chartInstance = null;

const initChart = () => {
  if (!downloadChartEl.value) return;
  if (chartInstance) chartInstance.dispose();

  chartInstance = echarts.init(downloadChartEl.value);
  chartInstance.setOption({
    grid: { left: 10, right: 15, top: 15, bottom: 5, containLabel: true },
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    xAxis: {
      type: 'category',
      data: props.serviceAnalytics.download_activity?.labels || ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
      axisLabel: { fontSize: 10 }
    },
    yAxis: { type: 'value', axisLabel: { fontSize: 10 } },
    series: [{
      type: 'bar',
      data: props.serviceAnalytics.download_activity?.values || [45, 62, 88, 74, 95, 30, 42],
      itemStyle: {
        color: {
          type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
          colorStops: [{ offset: 0, color: '#0284c7' }, { offset: 1, color: '#38bdf8' }]
        },
        borderRadius: [6, 6, 0, 0]
      },
      barMaxWidth: 24,
    }]
  });
};

onMounted(() => {
  setTimeout(initChart, 150);
});

watch(() => props.serviceAnalytics, () => {
  setTimeout(initChart, 150);
}, { deep: true });
</script>
