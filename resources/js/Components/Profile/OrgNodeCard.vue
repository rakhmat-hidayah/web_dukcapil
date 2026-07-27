<template>
  <div class="flex flex-col items-center">
    <!-- Node Box -->
    <div
      @click="$emit('select-node', node)"
      :class="[
        'w-64 p-4 rounded-2xl text-white shadow-xl border cursor-pointer transition hover:scale-105 hover:shadow-2xl relative',
        node.node_title === 'Kepala Dinas' ? 'bg-gradient-to-br from-red-600 to-red-800 border-red-400' :
        node.node_title === 'Sekretaris' ? 'bg-gradient-to-br from-sky-600 to-blue-800 border-sky-400' :
        node.node_title.startsWith('Sub Bagian') ? 'bg-gradient-to-br from-emerald-600 to-teal-800 border-emerald-400' :
        node.node_title.startsWith('Bidang') ? 'bg-gradient-to-br from-emerald-600 to-teal-800 border-emerald-400' :
        node.node_title.includes('Fungsional') ? 'bg-gradient-to-br from-amber-600 to-orange-700 border-amber-400' : 'bg-slate-800 border-slate-700',
        isMatched ? 'ring-4 ring-yellow-400 border-yellow-300' : 'hover:brightness-110'
      ]"
    >
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-black/20 overflow-hidden shrink-0 border border-white/20 flex items-center justify-center">
          <img v-if="node.official && node.official.photo" :src="'/storage/' + node.official.photo" :alt="node.official.name" class="w-full h-full object-cover" />
          <svg v-else class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div class="overflow-hidden">
          <h4 class="font-bold text-xs text-white leading-tight line-clamp-2">{{ node.official ? node.official.name : node.node_title }}</h4>
          <p class="text-[11px] text-white/90 font-semibold leading-tight line-clamp-2 mt-0.5">{{ node.node_title }}</p>
          <p v-if="node.official && node.official.nip" class="text-[10px] text-white/70 truncate mt-0.5">NIP. {{ node.official.nip }}</p>
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
