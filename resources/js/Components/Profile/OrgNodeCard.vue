<template>
  <div class="flex flex-col items-center">
    <!-- Node Box -->
    <div
      @click="$emit('select-node', node)"
      :class="[
        'w-64 p-4 rounded-2xl bg-slate-800 text-white shadow-xl border cursor-pointer transition hover:scale-105 hover:shadow-2xl relative',
        isMatched ? 'ring-4 ring-amber-400 border-amber-400' : 'border-slate-700 hover:border-blue-500'
      ]"
    >
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-slate-700 overflow-hidden shrink-0 border border-slate-600 flex items-center justify-center">
          <img v-if="node.official && node.official.photo" :src="'/storage/' + node.official.photo" :alt="node.official.name" class="w-full h-full object-cover" />
          <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div class="overflow-hidden">
          <h4 class="font-bold text-xs text-white truncate">{{ node.official ? node.official.name : node.node_title }}</h4>
          <p class="text-[11px] text-blue-400 font-medium truncate mt-0.5">{{ node.node_title }}</p>
          <p v-if="node.official && node.official.nip" class="text-[10px] text-slate-400 truncate">NIP. {{ node.official.nip }}</p>
        </div>
      </div>
    </div>

    <!-- Connector Line Down -->
    <div v-if="node.children && node.children.length > 0" class="w-0.5 h-8 bg-blue-500/60 my-0.5"></div>

    <!-- Children Nodes Row -->
    <div v-if="node.children && node.children.length > 0" class="flex items-start gap-8 relative pt-2">
      <!-- Horizontal Connector Bar -->
      <div v-if="node.children.length > 1" class="absolute top-0 left-12 right-12 h-0.5 bg-blue-500/60"></div>
      <div v-for="child in node.children" :key="child.id" class="flex flex-col items-center">
        <div class="w-0.5 h-4 bg-blue-500/60 mb-0.5"></div>
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
