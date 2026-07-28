<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Quick Actions & Shortcuts"
      subtitle="Pintas cepat untuk pembuatan konten, upload dataset, & pengelolaan sistem"
      badge="Shortcuts"
      :icon="Zap"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6">
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <Link 
          v-for="(act, idx) in quickActions" 
          :key="idx"
          :href="route(act.route)"
          class="p-3.5 rounded-2xl border bg-gray-50/50 dark:bg-zinc-800/30 border-gray-100 dark:border-zinc-800 hover:bg-primary-50 dark:hover:bg-primary-950/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200 group flex items-center gap-3"
        >
          <div class="p-2 rounded-xl bg-white dark:bg-zinc-800 shadow-sm text-primary-600 dark:text-primary-400 group-hover:scale-110 transition">
            <Plus class="w-4 h-4" />
          </div>
          <span class="text-xs font-bold text-gray-800 dark:text-zinc-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition leading-tight">
            {{ act.title }}
          </span>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Zap, Plus } from '@lucide/vue';
import WidgetHeader from './WidgetHeader.vue';

defineProps({
  quickActions: Array,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);
</script>
