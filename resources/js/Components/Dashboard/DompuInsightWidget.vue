<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Dompu Insight Engine & Region Coverage"
      subtitle="Monitoring kelengkapan & kesehatan dataset statistik wilayah kependudukan"
      badge="Dompu Insight"
      :icon="DatabaseZap"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6 space-y-6">
      <!-- Top Metrics Bar -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-gradient-to-br from-blue-50/50 to-indigo-50/30 dark:from-blue-950/20 dark:to-indigo-950/10 border border-blue-100/60 dark:border-blue-900/30">
        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-400">Periode Semester</span>
          <p class="text-sm font-black text-blue-900 dark:text-blue-200 mt-0.5">{{ dompuInsight.current_semester }}</p>
        </div>
        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-400">Kesehatan Dataset</span>
          <p class="text-sm font-black text-emerald-600 dark:text-emerald-400 mt-0.5 flex items-center gap-1">
            <CheckCircle2 class="w-4 h-4" /> {{ dompuInsight.health_status }}
          </p>
        </div>
        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-400">Terakhir Diimpor</span>
          <p class="text-sm font-black text-gray-800 dark:text-zinc-200 mt-0.5 font-mono">{{ dompuInsight.last_import }}</p>
        </div>
        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-400">Validasi Tertunda</span>
          <p class="text-sm font-black text-gray-800 dark:text-zinc-200 mt-0.5 font-mono">{{ dompuInsight.pending_validation }} dataset</p>
        </div>
      </div>

      <!-- Coverage Progress Bars -->
      <div class="space-y-4">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-zinc-500">
          Cakupan Upload Dataset per Level Wilayah
        </h4>

        <div v-for="(cov, key) in dompuInsight.coverage" :key="key" class="space-y-1.5">
          <div class="flex items-center justify-between text-xs font-bold text-gray-700 dark:text-zinc-300">
            <span>{{ cov.label }}</span>
            <span class="font-mono text-primary-600 dark:text-primary-400">
              {{ cov.count }} / {{ cov.total }} ({{ cov.percentage }}%)
            </span>
          </div>

          <div class="w-full h-3 bg-gray-100 dark:bg-zinc-800 rounded-full overflow-hidden p-0.5">
            <div 
              class="h-full rounded-full transition-all duration-500 bg-gradient-to-r"
              :class="cov.percentage === 100 ? 'from-emerald-500 to-teal-400' : 'from-primary-600 to-cyan-400'"
              :style="{ width: cov.percentage + '%' }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Missing Region Alert Banner (if any) -->
      <div v-if="dompuInsight.missing_districts && dompuInsight.missing_districts.length > 0" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 font-semibold">
          <AlertCircle class="w-5 h-5 shrink-0" />
          <span>
            Ada {{ dompuInsight.missing_districts.length }} kecamatan yang belum mengunggah dataset semester ini: 
            <strong>{{ dompuInsight.missing_districts.map(d => d.name).join(', ') }}</strong>
          </span>
        </div>
        <Link 
          :href="route('admin.demographics.datasets')" 
          class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition shrink-0"
        >
          Upload Sekarang &rarr;
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { DatabaseZap, CheckCircle2, AlertCircle } from '@lucide/vue';
import WidgetHeader from './WidgetHeader.vue';

defineProps({
  dompuInsight: Object,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);
</script>
