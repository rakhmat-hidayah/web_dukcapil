<template>
  <div class="flex flex-col items-center">
    <!-- Node Card Box (Matching Official Government Style Diagram) -->
    <div
      @click="$emit('select-node', node)"
      :class="[
        'w-64 sm:w-72 rounded-2xl shadow-xl border cursor-pointer transition hover:scale-105 hover:shadow-2xl overflow-hidden relative group',
        node.color_code === 'red' || node.node_title === 'KEPALA DINAS' ? 'border-rose-500 bg-rose-600' :
        node.color_code === 'pink' || node.node_title === 'SEKRETARIS' ? 'border-pink-500 bg-pink-600' :
        node.color_code === 'orange' || node.node_title.startsWith('SUB BAGIAN') ? 'border-amber-400 bg-gradient-to-r from-amber-500 to-orange-500' :
        'border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700',
        isMatched ? 'ring-4 ring-yellow-400 border-yellow-300' : ''
      ]"
    >
      <!-- Header Banner (Node Title) -->
      <div class="px-3 py-2 text-center">
        <h3 class="font-extrabold text-[11px] sm:text-xs text-white uppercase tracking-wider leading-tight">
          {{ node.node_title }}
        </h3>
      </div>

      <!-- White Inner Box (Official Info / Photo) -->
      <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2.5 rounded-xl flex items-center gap-2.5 min-h-[54px] border border-slate-100 dark:border-slate-800">
        <!-- Photo Thumbnail -->
        <div v-if="node.official && node.official.photo" class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0">
          <img :src="'/storage/' + node.official.photo" :alt="node.official.name" class="w-full h-full object-cover" />
        </div>
        <div v-else-if="!node.node_title.includes('FUNGSIONAL')" class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0 flex items-center justify-center text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>

        <!-- Official Name / Grid Placeholders for Fungsional -->
        <div v-if="node.node_title.includes('FUNGSIONAL')" class="w-full grid grid-cols-4 gap-1 py-1">
          <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/50 dark:bg-slate-800"></div>
          <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/50 dark:bg-slate-800"></div>
          <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/50 dark:bg-slate-800"></div>
          <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/50 dark:bg-slate-800"></div>
        </div>
        <div v-else class="overflow-hidden">
          <h4 class="font-black text-xs text-slate-900 dark:text-white leading-tight truncate">
            {{ node.official ? node.official.name : '-' }}
          </h4>
          <p v-if="node.official && node.official.nip" class="text-[10px] text-slate-400 truncate mt-0.5">
            NIP. {{ node.official.nip }}
          </p>
          <p v-else-if="node.official && node.official.position_title" class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold truncate mt-0.5">
            {{ node.official.position_title }}
          </p>
        </div>
      </div>
    </div>

    <!-- Connector Line Down from Parent -->
    <div v-if="node.children && node.children.length > 0" class="w-0.5 h-6 bg-blue-400/80"></div>

    <!-- Children Nodes Row -->
    <div v-if="node.children && node.children.length > 0" class="flex items-start justify-center gap-6 relative">
      <div 
        v-for="(child, idx) in node.children" 
        :key="child.id" 
        class="relative flex flex-col items-center shrink-0"
      >
        <!-- Horizontal Bar Left Segment (for items after first child) -->
        <div v-if="idx > 0" class="absolute top-0 left-0 w-1/2 h-0.5 bg-blue-400/80"></div>
        <!-- Horizontal Bar Right Segment (for items before last child) -->
        <div v-if="idx < node.children.length - 1" class="absolute top-0 right-0 w-1/2 h-0.5 bg-blue-400/80"></div>

        <!-- Vertical Line down to child node -->
        <div class="w-0.5 h-6 bg-blue-400/80 relative z-10"></div>

        <!-- Child Node Recursion -->
        <OrgNodeCard :node="child" :searchQuery="searchQuery" @select-node="$emit('select-node', $event)" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  node: Object,
  searchQuery: String,
})

defineEmits(['select-node'])

const isMatched = computed(() => {
  if (!props.searchQuery) return false
  const q = props.searchQuery.toLowerCase()
  const title = (props.node.node_title || '').toLowerCase()
  const name = (props.node.official?.name || '').toLowerCase()
  const nip = (props.node.official?.nip || '').toLowerCase()
  return title.includes(q) || name.includes(q) || nip.includes(q)
})
</script>
