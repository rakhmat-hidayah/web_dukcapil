<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Statistik Jumlah Unduhan Berkas"
      subtitle="Analisis unduhan dokumen publik, regulasi, & formulir pelayanan Disdukcapil"
      badge="Unduhan & Berkas"
      :icon="Download"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6 space-y-6">
      <!-- Top Section: Period Selector & Last Updated -->
      <div class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-gray-100 dark:border-zinc-800">
        <div>
          <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100">Jumlah Unduhan Berkas</h3>
          <p class="text-xs text-gray-400">Ringkasan statistik unduhan dokumen publik secara akurat</p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Filter Tabs: Hari Ini, Minggu Ini, Bulan Ini -->
          <div class="flex p-1 bg-gray-100 dark:bg-zinc-800 rounded-xl">
            <button 
              v-for="p in periods" 
              :key="p.key"
              @click="activePeriod = p.key"
              class="px-3 py-1.5 text-xs font-bold rounded-lg transition cursor-pointer"
              :class="activePeriod === p.key ? 'bg-sky-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-zinc-100'"
            >
              {{ p.label }}
            </button>
          </div>

          <!-- Last Updated Badge -->
          <div v-if="analyticsData?.last_updated" class="hidden sm:flex items-center gap-1.5 text-[11px] font-mono text-gray-400 dark:text-zinc-500 bg-gray-50 dark:bg-zinc-800/50 px-3 py-1.5 rounded-xl border border-gray-100 dark:border-zinc-800">
            <Clock class="w-3.5 h-3.5 text-sky-500" />
            <span>Terakhir diperbarui: {{ analyticsData.last_updated }}</span>
          </div>
        </div>
      </div>

      <!-- 4 KPI Cards Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Unduhan -->
        <div class="p-4 rounded-2xl border bg-sky-50/40 dark:bg-sky-950/20 border-sky-100 dark:border-sky-900/30 transition hover:shadow-md">
          <div class="flex items-center justify-between text-sky-600 dark:text-sky-400 mb-1">
            <span class="text-[10px] font-bold uppercase tracking-wider">Total Unduhan</span>
            <FileDown class="w-4 h-4" />
          </div>
          <p class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-mono">
            {{ kpiData.total_downloads?.toLocaleString('id-ID') || 0 }}
            <span class="text-xs font-normal text-gray-400">berkas</span>
          </p>
          <span class="text-[10px] text-gray-400 dark:text-zinc-400 mt-1 block">Periode {{ currentPeriodLabel }}</span>
        </div>

        <!-- Hari Ini -->
        <div class="p-4 rounded-2xl border bg-indigo-50/40 dark:bg-indigo-950/20 border-indigo-100 dark:border-indigo-900/30 transition hover:shadow-md">
          <div class="flex items-center justify-between text-indigo-600 dark:text-indigo-400 mb-1">
            <span class="text-[10px] font-bold uppercase tracking-wider">Hari Ini</span>
            <Calendar class="w-4 h-4" />
          </div>
          <p class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-mono">
            {{ kpiData.today_downloads?.toLocaleString('id-ID') || 0 }}
            <span class="text-xs font-normal text-gray-400">unduhan</span>
          </p>
          <span class="text-[10px] text-gray-400 dark:text-zinc-400 mt-1 block">Aktivitas 24 jam</span>
        </div>

        <!-- Rata-rata -->
        <div class="p-4 rounded-2xl border bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/30 transition hover:shadow-md">
          <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 mb-1">
            <span class="text-[10px] font-bold uppercase tracking-wider">Rata-rata</span>
            <TrendingUp class="w-4 h-4" />
          </div>
          <p class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-mono">
            {{ kpiData.avg_downloads || '0' }}
            <span class="text-xs font-normal text-gray-400">/ hari</span>
          </p>
          <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1 block">Rataan Harian</span>
        </div>

        <!-- Hari Tertinggi -->
        <div class="p-4 rounded-2xl border bg-amber-50/40 dark:bg-amber-950/20 border-amber-100 dark:border-amber-900/30 transition hover:shadow-md">
          <div class="flex items-center justify-between text-amber-600 dark:text-amber-400 mb-1">
            <span class="text-[10px] font-bold uppercase tracking-wider">Hari Tertinggi</span>
            <Award class="w-4 h-4" />
          </div>
          <p class="text-lg font-extrabold text-gray-900 dark:text-zinc-50 truncate mt-1">
            {{ kpiData.peak_day || '-' }}
          </p>
          <span class="text-[10px] text-gray-400 dark:text-zinc-400 mt-1 block">Puncak Unduhan</span>
        </div>
      </div>

      <!-- Main Graph Container or Empty State -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-zinc-400 flex items-center gap-2">
            <BarChart2 class="w-4 h-4 text-sky-500" />
            <span>Grafik Aktivitas Unduhan (Vertical Bar Chart)</span>
          </h4>
        </div>

        <!-- Empty State Component -->
        <div 
          v-if="kpiData.total_downloads === 0" 
          class="h-64 border border-dashed border-gray-200 dark:border-zinc-800 rounded-2xl p-8 flex flex-col items-center justify-center text-center bg-gray-50/50 dark:bg-zinc-800/20"
        >
          <div class="w-14 h-14 rounded-full bg-sky-100 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center mb-3">
            <FileQuestion class="w-7 h-7" />
          </div>
          <h5 class="text-sm font-bold text-gray-800 dark:text-zinc-200">Belum ada aktivitas unduhan pada periode ini.</h5>
          <p class="text-xs text-gray-400 max-w-xs mt-1">Dokumen publik yang diunduh warga akan otomatis tercatat secara teratur di grafik ini.</p>
        </div>

        <!-- Vertical Bar Chart -->
        <div v-else class="h-64 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 relative bg-white dark:bg-zinc-900 shadow-inner">
          <div ref="downloadChartEl" class="absolute inset-0"></div>
        </div>

        <!-- Insight Engine Box -->
        <div class="p-4 rounded-2xl bg-sky-50/60 dark:bg-sky-950/30 border border-sky-100 dark:border-sky-900/40 flex items-start gap-3">
          <div class="p-2 rounded-xl bg-sky-500 text-white shrink-0 shadow-sm mt-0.5">
            <Sparkles class="w-4 h-4" />
          </div>
          <div>
            <h5 class="text-xs font-bold uppercase tracking-wider text-sky-900 dark:text-sky-300">Automated Insight Engine</h5>
            <p class="text-xs text-sky-800 dark:text-sky-200 mt-0.5 leading-relaxed font-medium">
              {{ currentInsight }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Download, Clock, FileDown, Calendar, TrendingUp, Award, BarChart2, FileQuestion, Sparkles } from '@lucide/vue';
import * as echarts from 'echarts';
import WidgetHeader from './WidgetHeader.vue';

const props = defineProps({
  serviceAnalytics: Object,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);
const activePeriod = ref('7days');
const downloadChartEl = ref(null);
let chartInstance = null;

const periods = [
  { key: 'today', label: 'Hari Ini' },
  { key: '7days', label: 'Minggu Ini' },
  { key: '30days', label: 'Bulan Ini' },
];

const currentPeriodLabel = computed(() => {
  return periods.find(p => p.key === activePeriod.value)?.label || 'Minggu Ini';
});

const analyticsData = computed(() => {
  return props.serviceAnalytics?.download_analytics || null;
});

const periodData = computed(() => {
  const all = analyticsData.value;
  if (!all) return null;
  return all[activePeriod.value] || all['7days'] || null;
});

const kpiData = computed(() => {
  return periodData.value?.kpi || {
    total_downloads: 0,
    today_downloads: 0,
    avg_downloads: 0,
    peak_day: '-',
  };
});

const currentInsight = computed(() => {
  return periodData.value?.insight || 'Belum ada aktivitas unduhan pada periode ini.';
});

const getPeriodDataset = () => {
  const chartData = periodData.value?.chart;
  if (chartData && chartData.labels?.length > 0) {
    return chartData;
  }

  return {
    labels: activePeriod.value === 'today' 
      ? ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'] 
      : (activePeriod.value === '30days' ? ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'] : ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']),
    values: activePeriod.value === '30days' ? [0, 0, 0, 0] : (activePeriod.value === 'today' ? [0, 0, 0, 0, 0, 0] : [0, 0, 0, 0, 0, 0, 0]),
    percentages: [0, 0, 0, 0, 0, 0, 0],
  };
};

const initChart = () => {
  if (!downloadChartEl.value) return;
  if (chartInstance) chartInstance.dispose();

  const dataset = getPeriodDataset();
  if (!dataset.values || dataset.values.every(v => v === 0)) return;

  chartInstance = echarts.init(downloadChartEl.value);
  chartInstance.setOption({
    grid: { left: 20, right: 20, top: 35, bottom: 10, containLabel: true },
    tooltip: {
      trigger: 'axis',
      backgroundColor: '#0f172a',
      borderColor: '#334155',
      textStyle: { color: '#f8fafc', fontSize: 11 },
      formatter: (params) => {
        const item = params[0];
        const idx = item.dataIndex;
        const pct = dataset.percentages[idx] || 0;
        return `
          <div style="font-weight:bold;margin-bottom:4px;color:#38bdf8">${item.name}</div>
          <div>Jumlah Unduhan: <b>${item.value} berkas</b></div>
          <div style="font-size:10px;color:#94a3b8;margin-top:2px">Persentase: <b>${pct}%</b> dari total</div>
        `;
      }
    },
    xAxis: {
      type: 'category',
      data: dataset.labels,
      axisLabel: { fontSize: 11, fontWeight: 'bold', color: '#64748b' },
      axisLine: { lineStyle: { color: '#e2e8f0' } }
    },
    yAxis: {
      type: 'value',
      minInterval: 1, // STRICT INTEGER Y-AXIS
      axisLabel: {
        fontSize: 10,
        color: '#64748b',
        formatter: (val) => Math.floor(val).toLocaleString('id-ID')
      },
      splitLine: { lineStyle: { type: 'dashed', color: '#f1f5f9' } }
    },
    series: [{
      type: 'bar',
      data: dataset.values,
      barMaxWidth: 28,
      label: {
        show: true,
        position: 'top',
        fontSize: 11,
        fontWeight: 'bold',
        color: '#0284c7',
        formatter: (p) => p.value > 0 ? p.value : ''
      },
      itemStyle: {
        color: {
          type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
          colorStops: [{ offset: 0, color: '#0284c7' }, { offset: 1, color: '#38bdf8' }]
        },
        borderRadius: [6, 6, 0, 0]
      }
    }]
  });
};

onMounted(() => {
  setTimeout(initChart, 150);
});

watch(activePeriod, () => {
  setTimeout(initChart, 100);
});

watch(() => props.serviceAnalytics, () => {
  setTimeout(initChart, 150);
}, { deep: true });
</script>
