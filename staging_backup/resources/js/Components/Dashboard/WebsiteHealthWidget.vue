<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Website & System Health"
      subtitle="Monitoring status real-time infrastruktur & integrasi modul"
      badge="11 Services Online"
      :icon="Activity"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div 
          v-for="(srv, idx) in services" 
          :key="idx"
          class="p-4 rounded-2xl border transition-all duration-200 hover:shadow-md bg-gray-50/50 dark:bg-zinc-800/30 border-gray-100 dark:border-zinc-800"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-gray-800 dark:text-zinc-200 truncate">
              {{ srv.name }}
            </span>
            <span 
              class="w-2.5 h-2.5 rounded-full animate-pulse"
              :class="srv.status === 'healthy' ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : (srv.status === 'warning' ? 'bg-amber-500' : 'bg-red-500')"
            ></span>
          </div>

          <div class="flex items-baseline gap-1 mt-3">
            <span class="text-lg font-black text-gray-900 dark:text-zinc-50 font-mono">
              {{ srv.response_time }}
            </span>
          </div>

          <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200/50 dark:border-zinc-700/50 text-[10px]">
            <span class="font-bold text-emerald-600 dark:text-emerald-400 capitalize">
              {{ srv.status === 'healthy' ? 'Healthy' : srv.status }}
            </span>
            <span class="text-gray-400 dark:text-zinc-500 font-mono">
              {{ srv.last_check }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Activity } from '@lucide/vue';
import WidgetHeader from './WidgetHeader.vue';

defineProps({
  services: Array,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);
</script>
