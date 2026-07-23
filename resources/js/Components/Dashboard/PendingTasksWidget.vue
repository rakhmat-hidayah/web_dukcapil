<template>
  <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-300">
    <WidgetHeader
      title="Tugas & Tindakan Perlu Perhatian (Pending Tasks)"
      subtitle="Daftar pekerjaan & peringatan sistem yang membutuhkan respon segera"
      badge="Action Required"
      :icon="AlertTriangle"
      :is-collapsed="collapsed"
      @refresh="$emit('refresh')"
      @toggle-collapse="collapsed = !collapsed"
      @toggle-fullscreen="$emit('toggle-fullscreen')"
    />

    <div v-show="!collapsed" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div 
          v-for="task in pendingTasks" 
          :key="task.id"
          class="p-4 rounded-2xl border flex items-center justify-between gap-3 transition-all duration-200"
          :class="[
            task.severity === 'danger' 
              ? 'bg-rose-50/40 dark:bg-rose-950/20 border-rose-100 dark:border-rose-900/30' 
              : (task.severity === 'warning' 
                ? 'bg-amber-50/40 dark:bg-amber-950/20 border-amber-100 dark:border-amber-900/30' 
                : 'bg-blue-50/40 dark:bg-blue-950/20 border-blue-100 dark:border-blue-900/30')
          ]"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div 
              class="w-10 h-10 rounded-xl flex items-center justify-center font-black shrink-0 text-sm"
              :class="[
                task.severity === 'danger' ? 'bg-rose-500 text-white' : (task.severity === 'warning' ? 'bg-amber-500 text-white' : 'bg-blue-500 text-white')
              ]"
            >
              {{ task.count }}
            </div>
            <div class="min-w-0">
              <h5 class="text-xs font-black text-gray-900 dark:text-zinc-50 truncate">
                {{ task.title }}
              </h5>
              <p class="text-[10px] text-gray-500 dark:text-zinc-400 mt-0.5">
                Status: {{ task.count > 0 ? 'Membutuhkan Tindakan' : 'Selesai / Terkendali' }}
              </p>
            </div>
          </div>

          <Link 
            :href="route(task.route)"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 whitespace-nowrap"
            :class="[
              task.severity === 'danger' ? 'bg-rose-600 hover:bg-rose-500 text-white' : (task.severity === 'warning' ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-blue-600 hover:bg-blue-500 text-white')
            ]"
          >
            {{ task.action_label }} &rarr;
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { AlertTriangle } from '@lucide/vue';
import WidgetHeader from './WidgetHeader.vue';

defineProps({
  pendingTasks: Array,
});

defineEmits(['refresh', 'toggle-fullscreen']);

const collapsed = ref(false);
</script>
