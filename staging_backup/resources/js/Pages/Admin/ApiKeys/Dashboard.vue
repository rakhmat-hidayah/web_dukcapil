<template>
  <Head title="API Performance Monitor" />

  <AdminLayout>
    <div class="space-y-8 text-left">
      <!-- Title section -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Monitor Performa & Trafik API</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Pantau statistik request, rata-rata latency, kegagalan integrasi, dan pembatasan rate limit secara real-time.
        </p>
      </div>

      <!-- KPI cards grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Total Request API</span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-500 flex items-center justify-center">
              <Activity class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-4 flex items-baseline gap-2">
            <span class="text-3xl font-bold font-sans tracking-tight text-gray-900 dark:text-zinc-50">{{ stats.total_requests }}</span>
            <span class="text-xs text-gray-400">hits</span>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Rata-rata Latency</span>
            <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-500 flex items-center justify-center">
              <Cpu class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-4 flex items-baseline gap-2">
            <span class="text-3xl font-bold font-sans tracking-tight text-gray-900 dark:text-zinc-50">{{ stats.avg_duration }}</span>
            <span class="text-xs text-gray-400">ms</span>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Gagal Request (>=400)</span>
            <div class="w-8 h-8 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-500 flex items-center justify-center">
              <AlertOctagon class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-4 flex items-baseline gap-2">
            <span class="text-3xl font-bold font-sans tracking-tight text-gray-900 dark:text-zinc-50">{{ stats.failed_requests }}</span>
            <span class="text-xs text-gray-400">gagal</span>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Rate Limit Violations</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center">
              <ShieldAlert class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-4 flex items-baseline gap-2">
            <span class="text-3xl font-bold font-sans tracking-tight text-gray-900 dark:text-zinc-50">{{ stats.rate_limit_violations }}</span>
            <span class="text-xs text-gray-400">blokir</span>
          </div>
        </div>
      </div>

      <!-- Volume chart over last 24h -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col h-96">
        <h3 class="text-sm font-bold text-gray-800 dark:text-zinc-200 mb-4">Volume Request API (24 Jam Terakhir)</h3>
        <div class="flex-1 min-h-0 relative">
          <div ref="hourlyChartContainer" class="absolute inset-0"></div>
        </div>
      </div>

      <!-- Top Clients, Top Endpoints, Failures Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Clients -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col h-[28rem]">
          <h3 class="text-sm font-bold text-gray-800 dark:text-zinc-200 mb-4">Client Paling Aktif</h3>
          <div class="flex-1 overflow-y-auto pr-2 divide-y divide-gray-50 dark:divide-zinc-800/60 scrollbar-thin">
            <div v-if="topClients.length === 0" class="h-full flex items-center justify-center text-xs text-gray-400">
              Belum ada data.
            </div>
            <div 
              v-for="(client, idx) in topClients" 
              :key="idx" 
              class="py-3.5 flex justify-between items-center text-xs"
            >
              <div class="flex items-center gap-3">
                <span class="w-5 h-5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 flex items-center justify-center font-bold text-[10px] rounded-lg">
                  {{ idx + 1 }}
                </span>
                <span class="font-bold text-gray-800 dark:text-zinc-200">{{ client.client_name }}</span>
              </div>
              <span class="font-semibold text-gray-500 font-mono">{{ client.total }} hits</span>
            </div>
          </div>
        </div>

        <!-- Top Endpoints -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col h-[28rem]">
          <h3 class="text-sm font-bold text-gray-800 dark:text-zinc-200 mb-4">Endpoint Paling Populer</h3>
          <div class="flex-1 overflow-y-auto pr-2 divide-y divide-gray-50 dark:divide-zinc-800/60 scrollbar-thin">
            <div v-if="topEndpoints.length === 0" class="h-full flex items-center justify-center text-xs text-gray-400">
              Belum ada data.
            </div>
            <div 
              v-for="(ep, idx) in topEndpoints" 
              :key="idx" 
              class="py-3.5 flex justify-between items-center text-xs"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 font-semibold font-mono text-[9px] rounded uppercase">
                  {{ ep.method }}
                </span>
                <span class="font-semibold text-gray-700 dark:text-zinc-300 truncate" :title="ep.endpoint">/{{ ep.endpoint }}</span>
              </div>
              <span class="font-semibold text-gray-500 shrink-0 font-mono">{{ ep.total }} hits</span>
            </div>
          </div>
        </div>

        <!-- Failed Requests -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col h-[28rem]">
          <h3 class="text-sm font-bold text-gray-800 dark:text-zinc-200 mb-4">Kegagalan Request Terbaru</h3>
          <div class="flex-1 overflow-y-auto pr-2 divide-y divide-gray-50 dark:divide-zinc-800/60 scrollbar-thin">
            <div v-if="failures.length === 0" class="h-full flex items-center justify-center text-xs text-gray-400">
              Tidak ada kegagalan log.
            </div>
            <div 
              v-for="fail in failures" 
              :key="fail.id" 
              class="py-3 flex flex-col text-xs"
            >
              <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                  <span class="px-1.5 py-0.5 bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-100 dark:border-red-900/30 font-bold font-mono text-[9px] rounded">
                    {{ fail.code }}
                  </span>
                  <span class="font-bold text-gray-800 dark:text-zinc-200 truncate max-w-[120px]">{{ fail.client }}</span>
                </div>
                <span class="text-[9px] text-gray-400 font-medium">{{ fail.time }}</span>
              </div>
              <p class="font-mono text-[10px] text-gray-500 dark:text-zinc-400 mt-1 break-all">
                {{ fail.method }} /{{ fail.endpoint }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Activity, Cpu, AlertOctagon, ShieldAlert } from '@lucide/vue';
import * as echarts from 'echarts';

const props = defineProps({
  stats: Object,
  topClients: Array,
  topEndpoints: Array,
  failures: Array,
  hourlyChart: Array,
});

const hourlyChartContainer = ref(null);
let myChart = null;

const initChart = () => {
  if (hourlyChartContainer.value) {
    myChart = echarts.init(hourlyChartContainer.value);
    const xData = props.hourlyChart.map(h => h.label);
    const yData = props.hourlyChart.map(h => h.count);

    myChart.setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'line',
          lineStyle: {
            color: '#aaaaaa',
            width: 1
          }
        }
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        top: '5%',
        containLabel: true
      },
      xAxis: {
        type: 'category',
        boundaryGap: false,
        data: xData,
        axisLabel: {
          fontSize: 10,
          color: '#888'
        },
        axisLine: {
          lineStyle: {
            color: '#eee'
          }
        }
      },
      yAxis: {
        type: 'value',
        splitLine: {
          lineStyle: {
            color: '#f3f4f6'
          }
        },
        axisLabel: {
          fontSize: 10,
          color: '#888'
        }
      },
      series: [
        {
          name: 'Hits API',
          type: 'line',
          smooth: true,
          showSymbol: false,
          areaStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
              { offset: 0, color: 'rgba(14, 145, 235, 0.3)' },
              { offset: 1, color: 'rgba(14, 145, 235, 0.0)' }
            ])
          },
          itemStyle: {
            color: '#0e91eb'
          },
          data: yData
        }
      ]
    });
  }
};

const handleResize = () => {
  if (myChart) myChart.resize();
};

onMounted(() => {
  initChart();
  window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize);
  if (myChart) myChart.dispose();
});
</script>
