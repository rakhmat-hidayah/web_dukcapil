<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Analisis Pengunjung & Lalu Lintas Web"
      subtitle="Analisis statistik tren kunjungan, pengunjung unik, tayangan halaman, perangkat, & halaman terpopuler"
      badge="Trafik & Sistem"
      :icon="PieChart"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6 space-y-6">
      <!-- Top Section: Controls & Last Updated -->
      <div class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-gray-100 dark:border-zinc-800">
        <div>
          <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100">Analisis Pengunjung &amp; Lalu Lintas Web</h3>
          <p class="text-xs text-gray-400">Monitoring lalu lintas pengunjung & performa portal secara real-time</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <!-- Compare Mode Toggle Switch -->
          <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 text-xs font-semibold text-gray-700 dark:text-zinc-300 cursor-pointer hover:bg-gray-100 transition">
            <input 
              type="checkbox" 
              v-model="compareMode" 
              class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
            />
            <span>Bandingkan dengan minggu sebelumnya</span>
          </label>

          <!-- Last Updated Badge -->
          <div v-if="analyticsData?.last_updated" class="hidden sm:flex items-center gap-1.5 text-[11px] font-mono text-gray-400 dark:text-zinc-500 bg-gray-50 dark:bg-zinc-800/50 px-3 py-1.5 rounded-xl border border-gray-100 dark:border-zinc-800">
            <Clock class="w-3.5 h-3.5 text-indigo-500" />
            <span>Terakhir diperbarui: {{ analyticsData.last_updated }}</span>
          </div>
        </div>
      </div>

      <!-- 8 Enterprise KPI Cards Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        <!-- Pengunjung Hari Ini -->
        <div class="p-3.5 rounded-2xl border bg-indigo-50/40 dark:bg-indigo-950/20 border-indigo-100 dark:border-indigo-900/30 transition hover:shadow-md">
          <span class="text-[9px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block truncate">Hari Ini</span>
          <p class="text-xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-1">
            {{ kpiData.today_visitors?.toLocaleString('id-ID') || 0 }}
          </p>
          <span class="text-[9px] text-gray-400 block mt-0.5">Pengunjung</span>
        </div>

        <!-- Pengunjung Minggu Ini -->
        <div class="p-3.5 rounded-2xl border bg-violet-50/40 dark:bg-violet-950/20 border-violet-100 dark:border-violet-900/30 transition hover:shadow-md">
          <span class="text-[9px] font-extrabold uppercase tracking-wider text-violet-600 dark:text-violet-400 block truncate">Minggu Ini</span>
          <p class="text-xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-1">
            {{ kpiData.weekly_visitors?.toLocaleString('id-ID') || 0 }}
          </p>
          <span class="text-[9px] text-gray-400 block mt-0.5">Pengunjung</span>
        </div>

        <!-- Pengunjung Bulan Ini -->
        <div class="p-3.5 rounded-2xl border bg-purple-50/40 dark:bg-purple-950/20 border-purple-100 dark:border-purple-900/30 transition hover:shadow-md">
          <span class="text-[9px] font-extrabold uppercase tracking-wider text-purple-600 dark:text-purple-400 block truncate">Bulan Ini</span>
          <p class="text-xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-1">
            {{ kpiData.monthly_visitors?.toLocaleString('id-ID') || 0 }}
          </p>
          <span class="text-[9px] text-gray-400 block mt-0.5">Pengunjung</span>
        </div>

        <!-- Unique Visitor -->
        <div class="p-3.5 rounded-2xl border bg-sky-50/40 dark:bg-sky-950/20 border-sky-100 dark:border-sky-900/30 transition hover:shadow-md">
          <span class="text-[9px] font-extrabold uppercase tracking-wider text-sky-600 dark:text-sky-400 block truncate">Pengunjung Unik</span>
          <p class="text-xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-1">
            {{ kpiData.unique_visitors?.toLocaleString('id-ID') || 0 }}
          </p>
          <span class="text-[9px] text-gray-400 block mt-0.5">IP Unik</span>
        </div>

        <!-- Page Views -->
        <div class="p-3.5 rounded-2xl border bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/30 transition hover:shadow-md">
          <span class="text-[9px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block truncate">Tayangan Halaman</span>
          <p class="text-xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-1">
            {{ kpiData.page_views?.toLocaleString('id-ID') || 0 }}
          </p>
          <span class="text-[9px] text-gray-400 block mt-0.5">Total Hits</span>
        </div>

        <!-- Bounce Rate -->
        <div class="p-3.5 rounded-2xl border bg-amber-50/40 dark:bg-amber-950/20 border-amber-100 dark:border-amber-900/30 transition hover:shadow-md">
          <span class="text-[9px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400 block truncate">Rasio Pantulan</span>
          <p class="text-xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-1">
            {{ kpiData.bounce_rate || '0%' }}
          </p>
          <span class="text-[9px] text-gray-400 block mt-0.5">Satu Halaman</span>
        </div>

        <!-- Avg Session -->
        <div class="p-3.5 rounded-2xl border bg-teal-50/40 dark:bg-teal-950/20 border-teal-100 dark:border-teal-900/30 transition hover:shadow-md">
          <span class="text-[9px] font-extrabold uppercase tracking-wider text-teal-600 dark:text-teal-400 block truncate">Durasi Rata-rata</span>
          <p class="text-base font-black text-gray-900 dark:text-zinc-50 font-mono mt-1">
            {{ kpiData.avg_duration || '2m 15s' }}
          </p>
          <span class="text-[9px] text-gray-400 block mt-0.5">Durasi Rata-rata</span>
        </div>

        <!-- Halaman Terpopuler -->
        <div class="p-3.5 rounded-2xl border bg-rose-50/40 dark:bg-rose-950/20 border-rose-100 dark:border-rose-900/30 transition hover:shadow-md">
          <span class="text-[9px] font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 block truncate">Halaman Terpopuler</span>
          <p class="text-xs font-extrabold text-gray-900 dark:text-zinc-50 truncate mt-1">
            {{ kpiData.top_page || 'Beranda Utama' }}
          </p>
          <span class="text-[9px] text-gray-400 block mt-0.5">Paling Banyak</span>
        </div>
      </div>

      <!-- Main Visitor Trend Area Line Chart -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-zinc-400 flex items-center gap-2">
            <Activity class="w-4 h-4 text-indigo-500" />
            <span>Grafik Tren Kunjungan</span>
          </h4>

          <div v-if="compareMode" class="flex items-center gap-4 text-xs font-semibold">
            <span class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400">
              <span class="w-3 h-3 rounded-full bg-indigo-600 inline-block"></span> Minggu Ini
            </span>
            <span class="flex items-center gap-1.5 text-slate-400">
              <span class="w-3 h-0.5 bg-slate-400 border border-dashed border-slate-400 inline-block"></span> Minggu Lalu
            </span>
          </div>
        </div>

        <!-- Empty State Component -->
        <div 
          v-if="kpiData.page_views === 0" 
          class="h-64 border border-dashed border-gray-200 dark:border-zinc-800 rounded-2xl p-8 flex flex-col items-center justify-center text-center bg-gray-50/50 dark:bg-zinc-800/20"
        >
          <div class="w-14 h-14 rounded-full bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3">
            <Users class="w-7 h-7" />
          </div>
          <h5 class="text-sm font-bold text-gray-800 dark:text-zinc-200">Belum ada data kunjungan.</h5>
          <p class="text-xs text-gray-400 max-w-xs mt-1">Setiap kali warga mengakses website Disdukcapil, statistik kunjungan akan langsung tercatat secara akurat di sini.</p>
        </div>

        <!-- Area Line Chart -->
        <div v-else class="h-64 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 relative bg-white dark:bg-zinc-900 shadow-inner">
          <div ref="trendChartEl" class="absolute inset-0"></div>
        </div>

        <!-- Visitor Insight Engine Box -->
        <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 flex items-start gap-3">
          <div class="p-2 rounded-xl bg-indigo-600 text-white shrink-0 shadow-sm mt-0.5">
            <Sparkles class="w-4 h-4" />
          </div>
          <div>
            <h5 class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300">Analisis Insight Pengunjung</h5>
            <p class="text-xs text-indigo-800 dark:text-indigo-200 mt-0.5 leading-relaxed font-medium">
              {{ analyticsData?.insight || 'Memuat analisis lalu lintas otomatis...' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Donut & Bar Charts Grid: Browsers, Platforms, Top Pages -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-2">
        <!-- Browsers Donut Chart -->
        <div class="space-y-3">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
            Peramban (Browser) Pengunjung
          </h4>
          <div class="h-52 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 relative bg-white dark:bg-zinc-900 shadow-inner">
            <div ref="browserChartEl" class="absolute inset-0"></div>
          </div>
        </div>

        <!-- Operating Systems Bar Chart -->
        <div class="space-y-3">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
            Sistem Operasi (OS)
          </h4>
          <div class="h-52 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 relative bg-white dark:bg-zinc-900 shadow-inner">
            <div ref="platformChartEl" class="absolute inset-0"></div>
          </div>
        </div>

        <!-- Top Pages Table -->
        <div class="space-y-3">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
            Halaman Paling Banyak Dikunjungi
          </h4>
          <div class="border border-gray-100 dark:border-zinc-800 rounded-2xl overflow-hidden bg-white dark:bg-zinc-900 shadow-inner">
            <table class="w-full text-xs text-left">
              <thead class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800 text-gray-400 text-[9px] uppercase font-bold">
                <tr>
                  <th class="px-3 py-2.5">Judul Halaman</th>
                  <th class="px-3 py-2.5 text-right">Tayangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
                <tr v-for="(pg, i) in (analyticsData?.top_pages || visitorAnalytics?.top_pages || [])" :key="i" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                  <td class="px-3 py-2">
                    <p class="font-bold text-gray-800 dark:text-zinc-200 truncate max-w-[160px]">{{ pg.title }}</p>
                    <span class="text-[9px] text-gray-400 font-mono">{{ pg.path }}</span>
                  </td>
                  <td class="px-3 py-2 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">
                    {{ pg.views?.toLocaleString('id-ID') || 0 }}
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
import { ref, computed, onMounted, watch } from 'vue';
import { PieChart, Clock, Activity, Users, Sparkles } from '@lucide/vue';
import * as echarts from 'echarts';
import WidgetHeader from './WidgetHeader.vue';

const props = defineProps({
  visitorAnalytics: Object,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);
const compareMode = ref(false);

const trendChartEl = ref(null);
const browserChartEl = ref(null);
const platformChartEl = ref(null);

let trendChart = null;
let browserChart = null;
let platformChart = null;

const analyticsData = computed(() => props.visitorAnalytics || {});

const kpiData = computed(() => {
  return analyticsData.value?.kpi || {
    today_visitors: 0,
    weekly_visitors: 0,
    monthly_visitors: 0,
    unique_visitors: 0,
    page_views: 0,
    bounce_rate: '0%',
    avg_duration: '2m 15s',
    top_page: 'Beranda Utama',
  };
});

const initCharts = () => {
  // 1. Visitor Trend Chart (Area Line)
  if (trendChartEl.value) {
    if (trendChart) trendChart.dispose();
    trendChart = echarts.init(trendChartEl.value);

    const categories = analyticsData.value?.trend?.categories || ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    const currentSeries = analyticsData.value?.trend?.series_current || analyticsData.value?.trend?.series || [0, 0, 0, 0, 0, 0, 0];
    const prevSeries = analyticsData.value?.trend?.series_previous || [0, 0, 0, 0, 0, 0, 0];

    const seriesConfig = [
      {
        name: 'Minggu Ini',
        type: 'line',
        smooth: true,
        symbol: 'circle',
        symbolSize: 6,
        data: currentSeries,
        lineStyle: { width: 3, color: '#6366f1' },
        areaStyle: {
          color: {
            type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
            colorStops: [{ offset: 0, color: 'rgba(99, 102, 241, 0.35)' }, { offset: 1, color: 'rgba(99, 102, 241, 0.0)' }]
          }
        },
        itemStyle: { color: '#6366f1', borderWidth: 2, borderColor: '#ffffff' }
      }
    ];

    if (compareMode.value) {
      seriesConfig.push({
        name: 'Minggu Lalu',
        type: 'line',
        smooth: true,
        symbol: 'circle',
        symbolSize: 5,
        data: prevSeries,
        lineStyle: { width: 2, color: '#94a3b8', type: 'dashed' },
        itemStyle: { color: '#94a3b8' }
      });
    }

    trendChart.setOption({
      grid: { left: 20, right: 20, top: 20, bottom: 10, containLabel: true },
      tooltip: {
        trigger: 'axis',
        backgroundColor: '#0f172a',
        borderColor: '#334155',
        textStyle: { color: '#f8fafc', fontSize: 11 },
        formatter: (params) => {
          let html = `<div style="font-weight:bold;margin-bottom:4px;color:#818cf8">${params[0].name}</div>`;
          params.forEach(p => {
            html += `<div>${p.seriesName}: <b>${p.value.toLocaleString('id-ID')} visitor</b></div>`;
          });
          return html;
        }
      },
      xAxis: {
        type: 'category',
        data: categories,
        axisLabel: { fontSize: 11, fontWeight: 'bold', color: '#64748b' },
        axisLine: { lineStyle: { color: '#e2e8f0' } }
      },
      yAxis: {
        type: 'value',
        minInterval: 1,
        axisLabel: {
          fontSize: 10,
          color: '#64748b',
          formatter: (v) => Math.floor(v).toLocaleString('id-ID')
        },
        splitLine: { lineStyle: { type: 'dashed', color: '#f1f5f9' } }
      },
      series: seriesConfig
    });
  }

  // 2. Browsers Donut Chart
  if (browserChartEl.value) {
    if (browserChart) browserChart.dispose();
    browserChart = echarts.init(browserChartEl.value);
    const rawBrowsers = analyticsData.value?.browsers || [];
    const browserData = rawBrowsers.map(b => ({ name: b.browser || 'Unknown', value: b.total }));

    browserChart.setOption({
      tooltip: { trigger: 'item', formatter: '{b}: <b>{c}</b> ({d}%)' },
      legend: { bottom: 0, textStyle: { fontSize: 10, color: '#64748b' } },
      series: [{
        type: 'pie',
        radius: ['40%', '70%'],
        center: ['50%', '42%'],
        data: browserData.length > 0 ? browserData : [{ name: 'Chrome', value: 100 }],
        color: ['#6366f1', '#38bdf8', '#10b981', '#f59e0b', '#ec4899'],
        label: { show: false }
      }]
    });
  }

  // 3. Operating Systems Bar Chart
  if (platformChartEl.value) {
    if (platformChart) platformChart.dispose();
    platformChart = echarts.init(platformChartEl.value);
    const rawPlatforms = analyticsData.value?.platforms || [];

    platformChart.setOption({
      grid: { left: 10, right: 20, top: 10, bottom: 5, containLabel: true },
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      xAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 9 } },
      yAxis: { type: 'category', data: rawPlatforms.map(p => p.platform || 'Unknown'), axisLabel: { fontSize: 9, fontWeight: 'bold' } },
      series: [{
        type: 'bar',
        data: rawPlatforms.map(p => p.total),
        itemStyle: { color: '#06b6d4', borderRadius: [0, 4, 4, 0] },
        barMaxWidth: 16
      }]
    });
  }
};

onMounted(() => {
  setTimeout(initCharts, 150);
});

watch(compareMode, () => {
  setTimeout(initCharts, 100);
});

watch(() => props.visitorAnalytics, () => {
  setTimeout(initCharts, 150);
}, { deep: true });
</script>
