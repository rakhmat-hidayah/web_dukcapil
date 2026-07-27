<template>
  <Head title="System Health Dashboard" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">🖥️ System Health Dashboard</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
            Monitor status, performa, dan kesehatan sistem secara real-time
          </p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs text-gray-400 font-mono">{{ serverTime }}</span>
          <span 
            class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border"
            :class="statusClass(overallStatus)"
          >
            {{ statusIcon(overallStatus) }} {{ overallStatus === 'healthy' ? 'Sistem Sehat' : overallStatus === 'warning' ? 'Ada Peringatan' : 'Sistem Kritis' }}
          </span>
        </div>
      </div>

      <!-- System Info -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm text-center">
          <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">PHP Version</p>
          <p class="text-sm font-black text-gray-900 dark:text-zinc-100 mt-1">{{ system.php_version }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm text-center">
          <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Laravel</p>
          <p class="text-sm font-black text-gray-900 dark:text-zinc-100 mt-1">v{{ system.laravel_version }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm text-center">
          <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Environment</p>
          <p 
            class="text-sm font-black mt-1"
            :class="system.environment === 'production' ? 'text-emerald-600' : 'text-amber-600'"
          >{{ system.environment }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm text-center">
          <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Debug Mode</p>
          <p 
            class="text-sm font-black mt-1"
            :class="system.debug_mode ? 'text-rose-600' : 'text-emerald-600'"
          >{{ system.debug_mode ? '⚠️ ON' : '✅ OFF' }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm text-center">
          <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Timezone</p>
          <p class="text-sm font-black text-gray-900 dark:text-zinc-100 mt-1">{{ system.timezone }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm text-center">
          <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Storage Link</p>
          <p 
            class="text-sm font-black mt-1"
            :class="storageLink ? 'text-emerald-600' : 'text-rose-600'"
          >{{ storageLink ? '✅ Active' : '❌ Missing' }}</p>
        </div>
      </div>

      <!-- Core Services Status -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Database -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base">🗄️</div>
              <div>
                <h3 class="text-xs font-black text-gray-800 dark:text-zinc-200">Database</h3>
                <p class="text-[10px] text-gray-400">MySQL · {{ db.version }}</p>
              </div>
            </div>
            <StatusBadge :status="db.status" />
          </div>
          <p class="text-[11px] text-gray-500 dark:text-zinc-400 font-medium">{{ db.message }}</p>
        </div>

        <!-- Cache -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base">⚡</div>
              <div>
                <h3 class="text-xs font-black text-gray-800 dark:text-zinc-200">Cache</h3>
                <p class="text-[10px] text-gray-400">Driver: {{ cache.driver }}</p>
              </div>
            </div>
            <StatusBadge :status="cache.status" />
          </div>
          <p class="text-[11px] text-gray-500 dark:text-zinc-400 font-medium">{{ cache.message }}</p>
        </div>

        <!-- Storage -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base">💾</div>
              <div>
                <h3 class="text-xs font-black text-gray-800 dark:text-zinc-200">Storage</h3>
                <p class="text-[10px] text-gray-400">Disk: {{ storage.disk }}</p>
              </div>
            </div>
            <StatusBadge :status="storage.status" />
          </div>
          <p class="text-[11px] text-gray-500 dark:text-zinc-400 font-medium">{{ storage.message }}</p>
        </div>

        <!-- Queue -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base">🔄</div>
              <div>
                <h3 class="text-xs font-black text-gray-800 dark:text-zinc-200">Queue</h3>
                <p class="text-[10px] text-gray-400">Driver: {{ queue.driver }}</p>
              </div>
            </div>
            <StatusBadge :status="queue.status" />
          </div>
          <p class="text-[11px] text-gray-500 dark:text-zinc-400 font-medium">
            {{ queue.pending }} pending · {{ queue.failed }} failed jobs
          </p>
        </div>
      </div>

      <!-- Disk & Memory Usage -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Disk Usage -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-800 dark:text-zinc-200 flex items-center gap-2">
              💿 Penggunaan Disk
            </h3>
            <StatusBadge :status="disk.status" />
          </div>
          <div class="space-y-2">
            <div class="flex justify-between text-xs font-mono">
              <span class="text-gray-500">{{ disk.used }} / {{ disk.total }}</span>
              <span class="font-black" :class="disk.used_pct > 90 ? 'text-rose-600' : disk.used_pct > 75 ? 'text-amber-600' : 'text-emerald-600'">
                {{ disk.used_pct }}%
              </span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-zinc-800 rounded-full h-3">
              <div 
                class="h-3 rounded-full transition-all duration-500"
                :class="disk.used_pct > 90 ? 'bg-rose-500' : disk.used_pct > 75 ? 'bg-amber-500' : 'bg-emerald-500'"
                :style="`width: ${disk.used_pct}%`"
              ></div>
            </div>
            <p class="text-[10px] text-gray-400">Tersisa: {{ disk.free }}</p>
          </div>
        </div>

        <!-- Memory Usage -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-800 dark:text-zinc-200 flex items-center gap-2">
              🧠 Penggunaan Memori
            </h3>
            <StatusBadge :status="memory.status" />
          </div>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-500">Penggunaan Saat Ini</span>
              <span class="text-sm font-black font-mono text-primary-600 dark:text-primary-400">{{ memory.usage }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-500">Peak Usage</span>
              <span class="text-sm font-black font-mono text-gray-800 dark:text-zinc-200">{{ memory.peak }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Grid -->
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <StatCard label="Visitor Hari Ini" :value="stats.visitor_today" icon="👥" color="blue" />
        <StatCard label="Login Gagal" :value="stats.failed_login_today" icon="🔐" :color="stats.failed_login_today > 5 ? 'red' : 'gray'" />
        <StatCard label="Error Hari Ini" :value="stats.error_count" icon="⚠️" :color="stats.error_count > 0 ? 'amber' : 'gray'" />
        <StatCard label="Pengaduan Pending" :value="stats.pending_complaints" icon="📩" :color="stats.pending_complaints > 0 ? 'amber' : 'green'" />
        <StatCard label="PPID Pending" :value="stats.pending_ppid" icon="📋" :color="stats.pending_ppid > 0 ? 'amber' : 'green'" />
        <StatCard label="Total Operator" :value="stats.total_users" icon="👤" color="gray" />
        <StatCard label="Draft Berita" :value="stats.draft_news" icon="📰" color="gray" />
      </div>

      <!-- Production Checklist -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
        <h3 class="text-sm font-black text-gray-800 dark:text-zinc-200 mb-5 flex items-center gap-2">
          ✅ Checklist Kesiapan Production
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <ChecklistItem label="APP_ENV = production" :ok="system.environment === 'production'" />
          <ChecklistItem label="APP_DEBUG = false" :ok="!system.debug_mode" />
          <ChecklistItem label="Database Terhubung" :ok="db.status === 'healthy'" />
          <ChecklistItem label="Cache Berfungsi" :ok="cache.status === 'healthy'" />
          <ChecklistItem label="Storage Writable" :ok="storage.status === 'healthy'" />
          <ChecklistItem label="Storage Link Aktif" :ok="storageLink" />
          <ChecklistItem label="Queue Berjalan Normal" :ok="queue.status !== 'critical'" />
          <ChecklistItem label="Disk Usage < 75%" :ok="disk.used_pct < 75" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  system: Object,
  db: Object,
  cache: Object,
  storage: Object,
  storageLink: Boolean,
  disk: Object,
  memory: Object,
  queue: Object,
  stats: Object,
  serverTime: String,
  overallStatus: String,
});

const statusClass = (status) => ({
  'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-900/50': status === 'healthy',
  'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-900/50': status === 'warning',
  'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-900/50': status === 'critical',
});

const statusIcon = (status) => status === 'healthy' ? '🟢' : status === 'warning' ? '🟡' : '🔴';

// Sub-components defined inline
const StatusBadge = {
  props: ['status'],
  template: `<span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border" :class="cls">{{ icon }} {{ label }}</span>`,
  computed: {
    cls() {
      return {
        'bg-emerald-50 text-emerald-700 border-emerald-200': this.status === 'healthy',
        'bg-amber-50 text-amber-700 border-amber-200': this.status === 'warning',
        'bg-rose-50 text-rose-700 border-rose-200': this.status === 'critical',
      };
    },
    icon() { return this.status === 'healthy' ? '🟢' : this.status === 'warning' ? '🟡' : '🔴'; },
    label() { return this.status === 'healthy' ? 'Healthy' : this.status === 'warning' ? 'Warning' : 'Critical'; },
  },
};

const StatCard = {
  props: ['label', 'value', 'icon', 'color'],
  template: `
    <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm text-center space-y-1">
      <div class="text-xl">{{ icon }}</div>
      <p class="text-lg font-black text-gray-900 dark:text-zinc-100">{{ value }}</p>
      <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider leading-tight">{{ label }}</p>
    </div>
  `,
};

const ChecklistItem = {
  props: ['label', 'ok'],
  template: `
    <div class="flex items-center gap-3 py-2 px-3 rounded-xl" :class="ok ? 'bg-emerald-50 dark:bg-emerald-950/30' : 'bg-rose-50 dark:bg-rose-950/30'">
      <span class="text-base">{{ ok ? '✅' : '❌' }}</span>
      <span class="text-xs font-semibold" :class="ok ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'">{{ label }}</span>
    </div>
  `,
};
</script>
