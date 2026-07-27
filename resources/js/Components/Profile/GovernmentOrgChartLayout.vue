<template>
  <div class="flex flex-col items-center py-6 select-none min-w-[1280px]">
    <!-- LEVEL 1: KEPALA DINAS -->
    <div class="flex flex-col items-center relative z-20">
      <div 
        @click="selectNode(kadisNode)"
        class="w-72 rounded-2xl shadow-xl border-2 border-rose-500 bg-rose-600 text-white cursor-pointer transition hover:scale-105 hover:shadow-2xl overflow-hidden group"
      >
        <div class="px-3 py-2 text-center bg-rose-700/60 border-b border-rose-500/50">
          <h3 class="font-black text-xs uppercase tracking-wider text-white">KEPALA DINAS</h3>
        </div>
        <div class="bg-white dark:bg-slate-900 mx-1.5 my-1.5 p-2.5 rounded-xl flex items-center gap-3 border border-slate-100 dark:border-slate-800">
          <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0 flex items-center justify-center">
            <img v-if="kadisOfficial?.photo" :src="'/storage/' + kadisOfficial.photo" :alt="kadisOfficial.name" class="w-full h-full object-cover" />
            <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div class="overflow-hidden">
            <h4 class="font-black text-xs text-slate-900 dark:text-white truncate">{{ kadisOfficial?.name || 'Drs. Abd. Najib' }}</h4>
            <p v-if="kadisOfficial?.nip" class="text-[10px] text-slate-400 truncate mt-0.5">NIP. {{ kadisOfficial.nip }}</p>
          </div>
        </div>
      </div>

      <!-- Connector Line Down from Level 1 to Level 2 Bar -->
      <div class="w-0.5 h-8 bg-blue-400/80"></div>
    </div>

    <!-- LEVEL 2 & 3 CONTAINER -->
    <div class="w-full relative flex flex-col items-center">
      <!-- Horizontal Connector Bar for Level 2 Nodes -->
      <div class="w-full relative h-6">
        <div class="absolute top-0 left-[8.33%] right-[8.33%] h-0.5 bg-blue-400/80"></div>
      </div>

      <!-- LEVEL 2 GRID: 6 COLUMNS (JABATAN FUNGSIONAL + SEKRETARIS + 4 BIDANG) -->
      <div class="grid grid-cols-6 gap-4 w-full px-2">
        <!-- NODE 1: JABATAN FUNGSIONAL (LEFT BRANCH) -->
        <div class="flex flex-col items-center">
          <div class="w-0.5 h-6 bg-blue-400/80 -mt-6"></div>
          <div 
            @click="selectNode(fungsionalKadisNode)"
            class="w-full rounded-2xl shadow-xl border-2 border-amber-400 bg-amber-500 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-2 py-1.5 text-center bg-amber-600/50">
              <h3 class="font-black text-[10px] uppercase tracking-wider text-white">JABATAN FUNGSIONAL</h3>
            </div>
            <div class="bg-white dark:bg-slate-900 mx-1 mb-1 p-1.5 rounded-xl border border-slate-100 dark:border-slate-800">
              <div class="grid grid-cols-4 gap-1 py-0.5">
                <div class="h-5 rounded border border-amber-200 bg-amber-50"></div>
                <div class="h-5 rounded border border-amber-200 bg-amber-50"></div>
                <div class="h-5 rounded border border-amber-200 bg-amber-50"></div>
                <div class="h-5 rounded border border-amber-200 bg-amber-50"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- NODE 2: SEKRETARIS -->
        <div class="flex flex-col items-center">
          <div class="w-0.5 h-6 bg-blue-400/80 -mt-6"></div>
          <div 
            @click="selectNode(sekretarisNode)"
            class="w-full rounded-2xl shadow-xl border-2 border-pink-400 bg-pink-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-2 py-1.5 text-center bg-pink-700/50">
              <h3 class="font-black text-[11px] uppercase tracking-wider text-white">SEKRETARIS</h3>
            </div>
            <div class="bg-white dark:bg-slate-900 mx-1 mb-1 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
              <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                <img v-if="sekretarisOfficial?.photo" :src="'/storage/' + sekretarisOfficial.photo" :alt="sekretarisOfficial.name" class="w-full h-full object-cover" />
                <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <div class="overflow-hidden">
                <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ sekretarisOfficial?.name || 'Arifin, S.Sos' }}</h4>
                <p v-if="sekretarisOfficial?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ sekretarisOfficial.nip }}</p>
              </div>
            </div>
          </div>

          <!-- LEVEL 3 Connector down under Sekretaris -->
          <div class="w-0.5 h-5 bg-blue-400/80"></div>

          <!-- LEVEL 3 SUB-NODES UNDER SEKRETARIS (Sub Bagian Keuangan, Umum, Fungsional) -->
          <div class="w-full space-y-1.5">
            <div @click="selectNode(subNode2)" class="w-full rounded-xl shadow border border-amber-400 bg-amber-500 text-white cursor-pointer transition hover:scale-105 overflow-hidden">
              <div class="px-1.5 py-0.5 text-center bg-amber-600/50">
                <h4 class="font-bold text-[9px] uppercase text-white leading-tight truncate">SUB BAGIAN KEUANGAN & ASET</h4>
              </div>
            </div>
            <div @click="selectNode(subNode3)" class="w-full rounded-xl shadow border border-amber-400 bg-amber-500 text-white cursor-pointer transition hover:scale-105 overflow-hidden">
              <div class="px-1.5 py-0.5 text-center bg-amber-600/50">
                <h4 class="font-bold text-[9px] uppercase text-white leading-tight truncate">SUB BAGIAN UMUM & KEPEG.</h4>
              </div>
            </div>
            <div @click="selectNode(subNode1)" class="w-full rounded-xl shadow border border-amber-400 bg-amber-500 text-white cursor-pointer transition hover:scale-105 overflow-hidden">
              <div class="px-1.5 py-0.5 text-center bg-amber-600/50">
                <h4 class="font-bold text-[9px] uppercase text-white leading-tight truncate">JABATAN FUNGSIONAL</h4>
              </div>
            </div>
          </div>
        </div>

        <!-- NODE 3: BIDANG PELAYANAN PENDAFTARAN PENDUDUK -->
        <div class="flex flex-col items-center">
          <div class="w-0.5 h-6 bg-blue-400/80 -mt-6"></div>
          <div 
            @click="selectNode(bidang1Node)"
            class="w-full rounded-2xl shadow-xl border-2 border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-2 py-1.5 text-center bg-teal-800/40 min-h-[40px] flex items-center justify-center">
              <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-white leading-tight">
                BIDANG PENDAFTARAN PENDUDUK
              </h3>
            </div>
            <div class="bg-white dark:bg-slate-900 mx-1 mb-1 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
              <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                <img v-if="bidang1Official?.photo" :src="'/storage/' + bidang1Official.photo" :alt="bidang1Official.name" class="w-full h-full object-cover" />
                <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <div class="overflow-hidden">
                <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang1Official?.name || 'Nur Insani, S.Kom' }}</h4>
                <p v-if="bidang1Official?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ bidang1Official.nip }}</p>
              </div>
            </div>
          </div>

          <!-- LEVEL 3 Connector down under Bidang 1 -->
          <div class="w-0.5 h-5 bg-blue-400/80"></div>

          <!-- LEVEL 3 SUB-NODE UNDER BIDANG 1 -->
          <div 
            @click="selectNode(subBidang1Node)"
            class="w-full rounded-xl shadow border border-teal-400 bg-teal-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-1.5 py-1 text-center bg-teal-800/40">
              <h4 class="font-bold text-[9px] uppercase text-white leading-tight">SUB KOORDINATOR & FUNGSIONAL</h4>
            </div>
            <div class="bg-white dark:bg-slate-900 m-1 p-1 rounded-lg border border-slate-100 dark:border-slate-800">
              <div class="grid grid-cols-4 gap-1 py-0.5">
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- NODE 4: BIDANG PELAYANAN PENCATATAN SIPIL -->
        <div class="flex flex-col items-center">
          <div class="w-0.5 h-6 bg-blue-400/80 -mt-6"></div>
          <div 
            @click="selectNode(bidang2Node)"
            class="w-full rounded-2xl shadow-xl border-2 border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-2 py-1.5 text-center bg-teal-800/40 min-h-[40px] flex items-center justify-center">
              <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-white leading-tight">
                BIDANG PENCATATAN SIPIL
              </h3>
            </div>
            <div class="bg-white dark:bg-slate-900 mx-1 mb-1 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
              <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                <img v-if="bidang2Official?.photo" :src="'/storage/' + bidang2Official.photo" :alt="bidang2Official.name" class="w-full h-full object-cover" />
                <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <div class="overflow-hidden">
                <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang2Official?.name || 'Winarno, S.T' }}</h4>
                <p v-if="bidang2Official?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ bidang2Official.nip }}</p>
              </div>
            </div>
          </div>

          <!-- LEVEL 3 Connector down under Bidang 2 -->
          <div class="w-0.5 h-5 bg-blue-400/80"></div>

          <!-- LEVEL 3 SUB-NODE UNDER BIDANG 2 -->
          <div 
            @click="selectNode(subBidang2Node)"
            class="w-full rounded-xl shadow border border-teal-400 bg-teal-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-1.5 py-1 text-center bg-teal-800/40">
              <h4 class="font-bold text-[9px] uppercase text-white leading-tight">SUB KOORDINATOR & FUNGSIONAL</h4>
            </div>
            <div class="bg-white dark:bg-slate-900 m-1 p-1 rounded-lg border border-slate-100 dark:border-slate-800">
              <div class="grid grid-cols-4 gap-1 py-0.5">
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- NODE 5: BIDANG PIAK -->
        <div class="flex flex-col items-center">
          <div class="w-0.5 h-6 bg-blue-400/80 -mt-6"></div>
          <div 
            @click="selectNode(bidang3Node)"
            class="w-full rounded-2xl shadow-xl border-2 border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-2 py-1.5 text-center bg-teal-800/40 min-h-[40px] flex items-center justify-center">
              <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-white leading-tight">
                BIDANG PENGELOLAAN PIAK
              </h3>
            </div>
            <div class="bg-white dark:bg-slate-900 mx-1 mb-1 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
              <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                <img v-if="bidang3Official?.photo" :src="'/storage/' + bidang3Official.photo" :alt="bidang3Official.name" class="w-full h-full object-cover" />
                <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <div class="overflow-hidden">
                <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang3Official?.name || 'Nanang Kurniawan, S.Pt' }}</h4>
                <p v-if="bidang3Official?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ bidang3Official.nip }}</p>
              </div>
            </div>
          </div>

          <!-- LEVEL 3 Connector down under Bidang 3 -->
          <div class="w-0.5 h-5 bg-blue-400/80"></div>

          <!-- LEVEL 3 SUB-NODE UNDER BIDANG 3 -->
          <div 
            @click="selectNode(subBidang3Node)"
            class="w-full rounded-xl shadow border border-teal-400 bg-teal-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-1.5 py-1 text-center bg-teal-800/40">
              <h4 class="font-bold text-[9px] uppercase text-white leading-tight">SUB KOORDINATOR & FUNGSIONAL</h4>
            </div>
            <div class="bg-white dark:bg-slate-900 m-1 p-1 rounded-lg border border-slate-100 dark:border-slate-800">
              <div class="grid grid-cols-4 gap-1 py-0.5">
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- NODE 6: BIDANG PEMANFAATAN DATA & INOVASI -->
        <div class="flex flex-col items-center">
          <div class="w-0.5 h-6 bg-blue-400/80 -mt-6"></div>
          <div 
            @click="selectNode(bidang4Node)"
            class="w-full rounded-2xl shadow-xl border-2 border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-2 py-1.5 text-center bg-teal-800/40 min-h-[40px] flex items-center justify-center">
              <h3 class="font-extrabold text-[10px] uppercase tracking-wider text-white leading-tight">
                BIDANG DATA & INOVASI LAYANAN
              </h3>
            </div>
            <div class="bg-white dark:bg-slate-900 mx-1 mb-1 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
              <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                <img v-if="bidang4Official?.photo" :src="'/storage/' + bidang4Official.photo" :alt="bidang4Official.name" class="w-full h-full object-cover" />
                <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <div class="overflow-hidden">
                <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang4Official?.name || 'Muhaimin, S.Kom' }}</h4>
                <p v-if="bidang4Official?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ bidang4Official.nip }}</p>
              </div>
            </div>
          </div>

          <!-- LEVEL 3 Connector down under Bidang 4 -->
          <div class="w-0.5 h-5 bg-blue-400/80"></div>

          <!-- LEVEL 3 SUB-NODE UNDER BIDANG 4 -->
          <div 
            @click="selectNode(subBidang4Node)"
            class="w-full rounded-xl shadow border border-teal-400 bg-teal-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-1.5 py-1 text-center bg-teal-800/40">
              <h4 class="font-bold text-[9px] uppercase text-white leading-tight">SUB KOORDINATOR & FUNGSIONAL</h4>
            </div>
            <div class="bg-white dark:bg-slate-900 m-1 p-1 rounded-lg border border-slate-100 dark:border-slate-800">
              <div class="grid grid-cols-4 gap-1 py-0.5">
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
                <div class="h-4 rounded border border-teal-200 bg-teal-50"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  tree: Array,
  searchQuery: String,
})

const emit = defineEmits(['select-node'])

const selectNode = (node) => {
  if (node) emit('select-node', node)
}

// Find nodes from tree prop
const kadisNode = computed(() => props.tree?.[0] || null)
const kadisOfficial = computed(() => kadisNode.value?.official || null)

const sekretarisNode = computed(() => kadisNode.value?.children?.find(c => c.node_title.includes('SEKRETARIS')) || null)
const sekretarisOfficial = computed(() => sekretarisNode.value?.official || null)

const fungsionalKadisNode = computed(() => kadisNode.value?.children?.find(c => c.node_title.includes('FUNGSIONAL') && !c.node_title.includes('SEKRETARIS')) || null)

const subNodes = computed(() => sekretarisNode.value?.children || [])
const subNode1 = computed(() => subNodes.value[0] || { node_title: 'SUB KOORDINATOR & FUNGSIONAL' })
const subNode2 = computed(() => subNodes.value[1] || { node_title: 'SUB BAGIAN KEUANGAN DAN ASET' })
const subNode3 = computed(() => subNodes.value[2] || { node_title: 'SUB BAGIAN UMUM & KEPEGAWAIAN' })

const bidangNodes = computed(() => kadisNode.value?.children?.filter(c => c.node_title.includes('BIDANG')) || [])
const bidang1Node = computed(() => bidangNodes.value[0] || null)
const bidang1Official = computed(() => bidang1Node.value?.official || null)
const subBidang1Node = computed(() => bidang1Node.value?.children?.[0] || null)

const bidang2Node = computed(() => bidangNodes.value[1] || null)
const bidang2Official = computed(() => bidang2Node.value?.official || null)
const subBidang2Node = computed(() => bidang2Node.value?.children?.[0] || null)

const bidang3Node = computed(() => bidangNodes.value[2] || null)
const bidang3Official = computed(() => bidang3Node.value?.official || null)
const subBidang3Node = computed(() => bidang3Node.value?.children?.[0] || null)

const bidang4Node = computed(() => bidangNodes.value[3] || null)
const bidang4Official = computed(() => bidang4Node.value?.official || null)
const subBidang4Node = computed(() => bidang4Node.value?.children?.[0] || null)
</script>
