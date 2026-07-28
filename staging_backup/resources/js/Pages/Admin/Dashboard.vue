<template>
  <Head title="Enterprise Executive Control Center" />

  <AdminLayout>
    <div class="space-y-6 text-left pb-12">
      <!-- Top Welcome Banner -->
      <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <span class="px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-[10px] font-bold uppercase tracking-wider">
                Enterprise Executive Control Center
              </span>
              <span class="text-xs text-blue-200/60 font-mono">| Dukcapil Portal + Dompu Insight</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Selamat Datang, {{ $page.props.auth.user ? $page.props.auth.user.name : 'Super Administrator' }}!
            </h1>
            <p class="text-xs text-blue-100/70 mt-1 max-w-2xl leading-relaxed">
              Pusat kendali operasional, monitoring kesehatan infrastruktur, kinerja layanan publik, serta integritas dataset kependudukan Kabupaten Dompu.
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 shrink-0">
            <button 
              @click="refreshDashboard" 
              :disabled="refreshing"
              class="px-4 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white text-xs font-bold rounded-2xl transition flex items-center gap-2 active:scale-95"
            >
              <RotateCw class="w-4 h-4" :class="{ 'animate-spin': refreshing }" />
              {{ refreshing ? 'Memuat Data...' : 'Refresh Dashboard' }}
            </button>
          </div>
        </div>
      </div>

      <!-- SECTION 1: WEBSITE HEALTH -->
      <WebsiteHealthWidget 
        :services="healthServices"
        @refresh="refreshDashboard"
        @toggle-fullscreen="openFullscreen('Website & System Health', 'health')"
      />

      <!-- SECTION 2: EXECUTIVE SUMMARY -->
      <ExecutiveSummaryWidget 
        :summary-items="executiveSummary"
        @refresh="refreshDashboard"
        @toggle-fullscreen="openFullscreen('Executive Summary', 'summary')"
      />

      <!-- SECTION 3: CONTENT MANAGEMENT -->
      <ContentManagementWidget 
        :content-status="contentStatus"
        @refresh="refreshDashboard"
        @toggle-fullscreen="openFullscreen('Content Management Center', 'content')"
      />

      <!-- SECTION 4: DOMPU INSIGHT ENGINE -->
      <DompuInsightWidget 
        :dompu-insight="dompuInsight"
        @refresh="refreshDashboard"
        @toggle-fullscreen="openFullscreen('Dompu Insight Engine', 'dompu')"
      />

      <!-- SECTION 5: SERVICE ANALYTICS -->
      <ServiceAnalyticsWidget 
        :service-analytics="serviceAnalytics"
        @refresh="refreshDashboard"
        @toggle-fullscreen="openFullscreen('Service Performance & Analytics', 'service')"
      />

      <!-- SECTION 6: VISITOR ANALYTICS -->
      <VisitorAnalyticsWidget 
        :visitor-analytics="visitorAnalytics"
        @refresh="refreshDashboard"
        @toggle-fullscreen="openFullscreen('Visitor & Traffic Analytics', 'visitor')"
      />

      <!-- SECTION 7 & 8 GRID: OPERATOR ACTIVITY & PENDING TASKS -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- SECTION 7: OPERATOR ACTIVITY -->
        <OperatorActivityWidget 
          :timeline="timeline"
          :current-period="currentPeriod"
          @change-period="changeActivityPeriod"
          @refresh="refreshDashboard"
          @toggle-fullscreen="openFullscreen('Timeline Aktivitas Operator', 'operator')"
        />

        <!-- SECTION 8: PENDING TASKS -->
        <PendingTasksWidget 
          :pending-tasks="pendingTasks"
          @refresh="refreshDashboard"
          @toggle-fullscreen="openFullscreen('Pending Tasks & Alerts', 'pending')"
        />
      </div>

      <!-- SECTION 9: QUICK ACTIONS -->
      <QuickActionsWidget 
        :quick-actions="quickActions"
        @refresh="refreshDashboard"
        @toggle-fullscreen="openFullscreen('Quick Actions & Shortcuts', 'quick')"
      />
    </div>

    <!-- Fullscreen Inspection Modal -->
    <transition name="fade">
      <div v-if="fullscreenModal.open" class="fixed inset-0 bg-black/70 backdrop-blur-md z-50 flex items-center justify-center p-6" @click.self="fullscreenModal.open = false">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-5xl h-[85vh] shadow-2xl p-6 flex flex-col justify-between">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4 mb-4">
            <h3 class="text-base font-black text-gray-900 dark:text-zinc-50">
              {{ fullscreenModal.title }}
            </h3>
            <button @click="fullscreenModal.open = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 rounded-xl hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
              ✕
            </button>
          </div>

          <div class="flex-1 overflow-y-auto pr-2 scrollbar-thin space-y-6">
            <WebsiteHealthWidget v-if="fullscreenModal.type === 'health'" :services="healthServices" />
            <ExecutiveSummaryWidget v-else-if="fullscreenModal.type === 'summary'" :summary-items="executiveSummary" />
            <ContentManagementWidget v-else-if="fullscreenModal.type === 'content'" :content-status="contentStatus" />
            <DompuInsightWidget v-else-if="fullscreenModal.type === 'dompu'" :dompu-insight="dompuInsight" />
            <ServiceAnalyticsWidget v-else-if="fullscreenModal.type === 'service'" :service-analytics="serviceAnalytics" />
            <VisitorAnalyticsWidget v-else-if="fullscreenModal.type === 'visitor'" :visitor-analytics="visitorAnalytics" />
            <OperatorActivityWidget v-else-if="fullscreenModal.type === 'operator'" :timeline="timeline" :current-period="currentPeriod" />
            <PendingTasksWidget v-else-if="fullscreenModal.type === 'pending'" :pending-tasks="pendingTasks" />
            <QuickActionsWidget v-else-if="fullscreenModal.type === 'quick'" :quick-actions="quickActions" />
          </div>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { RotateCw } from '@lucide/vue';

// Import Widgets
import WebsiteHealthWidget from '@/Components/Dashboard/WebsiteHealthWidget.vue';
import ExecutiveSummaryWidget from '@/Components/Dashboard/ExecutiveSummaryWidget.vue';
import ContentManagementWidget from '@/Components/Dashboard/ContentManagementWidget.vue';
import DompuInsightWidget from '@/Components/Dashboard/DompuInsightWidget.vue';
import ServiceAnalyticsWidget from '@/Components/Dashboard/ServiceAnalyticsWidget.vue';
import VisitorAnalyticsWidget from '@/Components/Dashboard/VisitorAnalyticsWidget.vue';
import OperatorActivityWidget from '@/Components/Dashboard/OperatorActivityWidget.vue';
import PendingTasksWidget from '@/Components/Dashboard/PendingTasksWidget.vue';
import QuickActionsWidget from '@/Components/Dashboard/QuickActionsWidget.vue';

const props = defineProps({
  healthServices: Array,
  executiveSummary: Array,
  contentStatus: Object,
  dompuInsight: Object,
  serviceAnalytics: Object,
  visitorAnalytics: Object,
  timeline: Array,
  pendingTasks: Array,
  quickActions: Array,
  currentPeriod: String,
});

const refreshing = ref(false);
const fullscreenModal = ref({ open: false, title: '', type: '' });

const refreshDashboard = () => {
  refreshing.value = true;
  router.reload({
    onFinish: () => {
      refreshing.value = false;
    }
  });
};

const changeActivityPeriod = (period) => {
  router.get(route('admin.dashboard'), { period }, { preserveState: true, replace: true });
};

const openFullscreen = (title, type) => {
  fullscreenModal.value = { open: true, title, type };
};
</script>
