<template>
  <div class="flex flex-col items-center py-8 select-none w-[1200px] mx-auto text-slate-900 dark:text-white">
    <!-- LEVEL 1: KEPALA DINAS (Centered at top) -->
    <div class="flex flex-col items-center relative z-30">
      <div 
        @click="selectNode(kadisNode)"
        :class="getCardClass(kadisNode, 'bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 shadow-[0_10px_35px_-5px_rgba(37,99,235,0.45)] border-2 border-blue-400/50')"
        :style="getCardStyle(kadisNode)"
        class="w-72 rounded-2xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden group cursor-pointer active:scale-95 relative"
      >
        <div class="px-3 py-2 text-center bg-black/25 backdrop-blur-md border-b border-white/15 flex items-center justify-center gap-1.5">
          <span class="text-xs">👑</span>
          <h3 class="font-black text-xs uppercase tracking-wider text-white drop-shadow">{{ kadisNode?.node_title || 'KEPALA DINAS' }}</h3>
        </div>
        <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md mx-1.5 my-1.5 p-2.5 rounded-xl flex items-center gap-3 border border-white/40 dark:border-slate-800 shadow-inner">
          <div class="w-12 h-12 rounded-xl overflow-hidden bg-gradient-to-tr from-slate-100 to-blue-50 dark:from-slate-800 dark:to-slate-700 border-2 border-blue-200 dark:border-slate-600 shrink-0 flex items-center justify-center shadow-md">
            <img v-if="kadisOfficial?.photo" :src="'/storage/' + kadisOfficial.photo" :alt="kadisOfficial.name" class="w-full h-full object-cover" />
            <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div class="overflow-hidden">
            <h4 class="font-black text-xs text-slate-900 dark:text-white truncate">{{ kadisOfficial?.name || 'Drs. Abd. Najib' }}</h4>
            <span v-if="kadisOfficial?.nip" class="inline-block text-[9.5px] font-mono text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-blue-100 dark:border-slate-700 truncate mt-1">NIP. {{ kadisOfficial.nip }}</span>
          </div>
        </div>
      </div>

      <!-- Central Trunk Vertical Line from Kadis -->
      <div class="w-0.5 h-8 bg-gradient-to-b from-blue-500 to-cyan-400 shadow-[0_0_8px_rgba(56,189,248,0.6)]"></div>
    </div>

    <!-- MAIN TREE CANVAS WRAPPER (Exact 1200px width grid) -->
    <div class="w-full relative flex flex-col items-center">
      
      <!-- LEVEL 2 SECTION -->
      <div class="w-full relative flex flex-col items-center">
        <!-- Central Trunk Line through Level 2 -->
        <div class="absolute top-0 bottom-0 left-1/2 -translate-x-1/2 w-0.5 bg-gradient-to-b from-cyan-400 via-blue-500 to-indigo-500 shadow-[0_0_8px_rgba(56,189,248,0.6)] z-0"></div>

        <!-- T-Junction Dot from Kadis drop line -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full bg-cyan-300 shadow-[0_0_10px_#38bdf8] z-20"></div>

        <!-- LEVEL 2 CONNECTOR LINES -->
        <div class="absolute top-0 left-[25%] right-1/2 h-0.5 border-t-2 border-dashed border-amber-400/90 shadow-[0_0_8px_rgba(251,191,36,0.6)] z-10"></div>
        <div class="absolute top-0 left-1/2 right-[25%] h-0.5 bg-gradient-to-r from-blue-500 to-cyan-400 shadow-[0_0_8px_rgba(56,189,248,0.6)] z-10"></div>

        <!-- Left & Right Junction Dots -->
        <div class="absolute top-0 left-[25%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-amber-400 shadow-[0_0_8px_#fbbf24] z-20"></div>
        <div class="absolute top-0 right-[25%] translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-cyan-400 shadow-[0_0_8px_#38bdf8] z-20"></div>

        <!-- LEVEL 2 ROW (2 Columns: Left 50% & Right 50%) -->
        <div class="grid grid-cols-2 w-full z-10 pt-0">
          
          <!-- LEFT COLUMN: JABATAN FUNGSIONAL -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 border-l-2 border-dashed border-amber-400/90 shadow-[0_0_8px_rgba(251,191,36,0.5)]"></div>
            <div 
              @click="selectNode(fungsionalKadisNode)"
              :class="getCardClass(fungsionalKadisNode, 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 shadow-[0_10px_30px_-5px_rgba(245,158,11,0.4)] border-2 border-amber-300/50')"
              :style="getCardStyle(fungsionalKadisNode)"
              class="w-64 rounded-2xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-3 py-1.5 text-center bg-black/25 backdrop-blur-md flex items-center justify-center gap-1">
                <span class="text-[10px]">📊</span>
                <h3 class="font-black text-[11px] uppercase tracking-wider text-white drop-shadow">{{ fungsionalKadisNode?.node_title || 'JABATAN FUNGSIONAL' }}</h3>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md mx-1.5 my-1.5 p-2 rounded-xl border border-white/40 dark:border-slate-800">
                <div class="grid grid-cols-4 gap-1.5 py-1">
                  <div v-for="i in 8" :key="i" class="h-5 rounded-md border border-amber-200/80 dark:border-amber-700/50 bg-amber-50/80 dark:bg-amber-950/40 shadow-inner hover:bg-amber-100 transition"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN: SEKRETARIAT + SUB BAGIAN ROW -->
          <div class="flex flex-col items-center px-4 relative">
            <div class="w-0.5 h-6 bg-gradient-to-b from-cyan-400 to-blue-500 shadow-[0_0_8px_rgba(56,189,248,0.5)]"></div>
            
            <!-- Sekretariat Card -->
            <div 
              @click="selectNode(sekretarisNode)"
              :class="getCardClass(sekretarisNode, 'bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 shadow-[0_10px_30px_-5px_rgba(13,148,136,0.4)] border-2 border-teal-300/50')"
              :style="getCardStyle(sekretarisNode)"
              class="w-72 rounded-2xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-3 py-1.5 text-center bg-black/25 backdrop-blur-md flex items-center justify-center gap-1">
                <span class="text-[10px]">⭐</span>
                <h3 class="font-black text-xs uppercase tracking-wider text-white drop-shadow">{{ sekretarisNode?.node_title || 'SEKRETARIAT' }}</h3>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md mx-1.5 my-1.5 p-2.5 rounded-xl flex items-center gap-3 border border-white/40 dark:border-slate-800 shadow-inner">
                <div class="w-10 h-10 rounded-xl overflow-hidden bg-gradient-to-tr from-slate-100 to-teal-50 dark:from-slate-800 dark:to-slate-700 border-2 border-teal-200 dark:border-slate-600 shrink-0 flex items-center justify-center shadow">
                  <img v-if="sekretarisOfficial?.photo" :src="'/storage/' + sekretarisOfficial.photo" :alt="sekretarisOfficial.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-xs text-slate-900 dark:text-white truncate">{{ sekretarisOfficial?.name || 'Arifin, S.Sos' }}</h4>
                  <span v-if="sekretarisOfficial?.nip" class="inline-block text-[9px] font-mono text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-teal-100 dark:border-slate-700 truncate mt-0.5">NIP. {{ sekretarisOfficial.nip }}</span>
                </div>
              </div>
            </div>

            <!-- Drop Line from Sekretariat -->
            <div class="w-0.5 h-6 bg-gradient-to-b from-teal-500 to-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]"></div>

            <!-- SUB BAGIAN ROW (3 Columns under Sekretariat) -->
            <div class="w-full relative flex flex-col items-center">
              <div class="w-full relative h-4">
                <!-- Sub-horizontal connector line -->
                <div class="absolute top-0 left-[16.67%] right-[16.67%] h-0.5 bg-gradient-to-r from-amber-400 via-orange-500 to-amber-400 shadow-[0_0_8px_rgba(245,158,11,0.5)]"></div>
                <!-- T-Junctions -->
                <div class="absolute top-0 left-[16.67%] -translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-amber-400 shadow-[0_0_6px_#fbbf24]"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-amber-400 shadow-[0_0_8px_#fbbf24]"></div>
                <div class="absolute top-0 right-[16.67%] translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-amber-400 shadow-[0_0_6px_#fbbf24]"></div>
              </div>

              <div class="grid grid-cols-3 gap-3 w-full">
                <!-- Sub Bagian 1: Sub Bagian Keuangan -->
                <div class="flex flex-col items-center">
                  <div class="w-0.5 h-4 bg-amber-400/90 shadow-[0_0_6px_rgba(251,191,36,0.5)] -mt-4"></div>
                  <div 
                    @click="selectNode(subNode2)" 
                    :class="getCardClass(subNode2, 'bg-gradient-to-r from-amber-500 to-orange-500 shadow-[0_8px_25px_-5px_rgba(245,158,11,0.35)] border border-amber-300/50')"
                    :style="getCardStyle(subNode2)"
                    class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
                  >
                    <div class="px-1.5 py-1 text-center bg-black/20 backdrop-blur-md min-h-[32px] flex items-center justify-center">
                      <h4 class="font-bold text-[10px] uppercase text-white leading-tight drop-shadow">{{ subNode2?.node_title || 'SUB BAGIAN KEUANGAN' }}</h4>
                    </div>
                    <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md m-1 p-2 rounded-lg flex items-center gap-1.5 border border-white/40 dark:border-slate-800 min-h-[52px]">
                      <div class="w-7 h-7 rounded-lg overflow-hidden bg-amber-50 dark:bg-slate-800 border border-amber-200 shrink-0 flex items-center justify-center text-slate-400 shadow-sm">
                        <img v-if="subNode2?.official?.photo" :src="'/storage/' + subNode2.official.photo" :alt="subNode2.official.name" class="w-full h-full object-cover" />
                        <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      </div>
                      <div class="overflow-hidden">
                        <span class="font-bold text-[9.5px] text-slate-800 dark:text-white block truncate">{{ subNode2?.official?.name || 'Staf Keuangan' }}</span>
                        <span v-if="subNode2?.official?.nip" class="text-[8.5px] font-mono text-amber-600 dark:text-amber-400 block truncate">NIP. {{ subNode2.official.nip }}</span>
                        <span v-else class="text-[8.5px] text-slate-400 block truncate">Pengelola Keuangan</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Sub Bagian 2: Sub Bagian Umum & Kepegawaian -->
                <div class="flex flex-col items-center">
                  <div class="w-0.5 h-4 bg-amber-400/90 shadow-[0_0_6px_rgba(251,191,36,0.5)] -mt-4"></div>
                  <div 
                    @click="selectNode(subNode3)" 
                    :class="getCardClass(subNode3, 'bg-gradient-to-r from-amber-500 to-orange-500 shadow-[0_8px_25px_-5px_rgba(245,158,11,0.35)] border border-amber-300/50')"
                    :style="getCardStyle(subNode3)"
                    class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
                  >
                    <div class="px-1.5 py-1 text-center bg-black/20 backdrop-blur-md min-h-[32px] flex items-center justify-center">
                      <h4 class="font-bold text-[10px] uppercase text-white leading-tight drop-shadow">{{ subNode3?.node_title || 'SUB BAGIAN UMUM & KEPEG.' }}</h4>
                    </div>
                    <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md m-1 p-2 rounded-lg flex items-center gap-1.5 border border-white/40 dark:border-slate-800 min-h-[52px]">
                      <div class="w-7 h-7 rounded-lg overflow-hidden bg-amber-50 dark:bg-slate-800 border border-amber-200 shrink-0 flex items-center justify-center text-slate-400 shadow-sm">
                        <img v-if="subNode3?.official?.photo" :src="'/storage/' + subNode3.official.photo" :alt="subNode3.official.name" class="w-full h-full object-cover" />
                        <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      </div>
                      <div class="overflow-hidden">
                        <span class="font-bold text-[9.5px] text-slate-800 dark:text-white block truncate">{{ subNode3?.official?.name || 'Indah Dewi Marini, S.E' }}</span>
                        <span v-if="subNode3?.official?.nip" class="text-[8.5px] font-mono text-amber-600 dark:text-amber-400 block truncate">NIP. {{ subNode3.official.nip }}</span>
                        <span v-else class="text-[8.5px] text-slate-400 block truncate">Umum & Kepegawaian</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Sub Bagian 3: Kelompok Jabatan Fungsional -->
                <div class="flex flex-col items-center">
                  <div class="w-0.5 h-4 bg-amber-400/90 shadow-[0_0_6px_rgba(251,191,36,0.5)] -mt-4"></div>
                  <div 
                    @click="selectNode(subNode1)" 
                    :class="getCardClass(subNode1, 'bg-gradient-to-r from-amber-500 to-orange-500 shadow-[0_8px_25px_-5px_rgba(245,158,11,0.35)] border border-amber-300/50')"
                    :style="getCardStyle(subNode1)"
                    class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
                  >
                    <div class="px-1.5 py-1 text-center bg-black/20 backdrop-blur-md min-h-[32px] flex items-center justify-center">
                      <h4 class="font-bold text-[10px] uppercase text-white leading-tight drop-shadow">{{ subNode1?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
                    </div>
                    <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md m-1 p-1.5 rounded-lg border border-white/40 dark:border-slate-800 min-h-[52px] flex items-center">
                      <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                        <div v-for="i in 8" :key="i" class="h-4 rounded border border-amber-200/80 bg-amber-50/80 dark:bg-slate-800 dark:border-slate-700 shadow-inner hover:bg-amber-100 transition"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TRUNK EXTENSION BETWEEN LEVEL 2 & LEVEL 4 HORIZONTAL BAR -->
      <div class="w-0.5 h-8 bg-gradient-to-b from-indigo-500 via-blue-500 to-teal-400 shadow-[0_0_8px_rgba(56,189,248,0.6)] shrink-0 z-10"></div>

      <!-- T-Junction Dot hitting the 4-Bidang bar -->
      <div class="w-2.5 h-2.5 rounded-full bg-cyan-300 shadow-[0_0_10px_#38bdf8] -mt-1.5 z-20"></div>

      <!-- LEVEL 4 SECTION (4 BIDANGS) -->
      <div class="w-full relative flex flex-col items-center z-10 -mt-1">
        <!-- 4-Bidang Horizontal Bar -->
        <div class="w-full relative h-6">
          <div class="absolute top-0 left-[12.5%] right-[12.5%] h-0.5 bg-gradient-to-r from-teal-400 via-cyan-400 to-teal-400 shadow-[0_0_8px_rgba(45,212,191,0.6)]"></div>
          <!-- 4 T-Junction Dots for Bidangs -->
          <div class="absolute top-0 left-[12.5%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-teal-300 shadow-[0_0_6px_#2dd4bf]"></div>
          <div class="absolute top-0 left-[37.5%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-teal-300 shadow-[0_0_6px_#2dd4bf]"></div>
          <div class="absolute top-0 left-[62.5%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-teal-300 shadow-[0_0_6px_#2dd4bf]"></div>
          <div class="absolute top-0 left-[87.5%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-teal-300 shadow-[0_0_6px_#2dd4bf]"></div>
        </div>

        <!-- 4 BIDANG GRID ROW -->
        <div class="grid grid-cols-4 gap-5 w-full px-2">
          <!-- BIDANG 1 -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 bg-teal-400/90 shadow-[0_0_6px_rgba(45,212,191,0.5)] -mt-6"></div>
            <div 
              @click="selectNode(bidang1Node)"
              :class="getCardClass(bidang1Node, 'bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-600 shadow-[0_10px_30px_-5px_rgba(8,145,178,0.35)] border-2 border-cyan-300/50')"
              :style="getCardStyle(bidang1Node)"
              class="w-full rounded-2xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-2 text-center bg-black/25 backdrop-blur-md min-h-[44px] flex items-center justify-center gap-1">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight drop-shadow">
                  {{ bidang1Node?.node_title || 'BIDANG PELAYANAN PENDAFTARAN PENDUDUK' }}
                </h3>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-white/40 dark:border-slate-800 shadow-inner">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-teal-50 dark:bg-slate-800 border border-teal-200 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="bidang1Official?.photo" :src="'/storage/' + bidang1Official.photo" :alt="bidang1Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang1Official?.name || 'Nur Insani, S.Kom' }}</h4>
                  <span v-if="bidang1Official?.nip" class="inline-block text-[8.5px] font-mono text-teal-600 dark:text-teal-400 truncate">NIP. {{ bidang1Official.nip }}</span>
                </div>
              </div>
            </div>

            <!-- Connector to Sub Koordinator -->
            <div class="w-0.5 h-5 bg-teal-400/90 shadow-[0_0_6px_rgba(45,212,191,0.5)]"></div>

            <div 
              @click="selectNode(subBidang1Node)"
              :class="getCardClass(subBidang1Node, 'bg-gradient-to-r from-teal-600 to-cyan-600 shadow-[0_6px_20px_-5px_rgba(8,145,178,0.3)] border border-cyan-300/40')"
              :style="getCardStyle(subBidang1Node)"
              class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-1 text-center bg-black/20 backdrop-blur-md min-h-[32px] flex items-center justify-center">
                <h4 class="font-bold text-[10px] uppercase text-white leading-tight drop-shadow">{{ subBidang1Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md m-1 p-1.5 rounded-lg border border-white/40 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div v-for="i in 8" :key="i" class="h-4 rounded border border-cyan-200/80 bg-cyan-50/80 dark:bg-slate-800 dark:border-slate-700 shadow-inner hover:bg-cyan-100 transition"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 2 -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 bg-teal-400/90 shadow-[0_0_6px_rgba(45,212,191,0.5)] -mt-6"></div>
            <div 
              @click="selectNode(bidang2Node)"
              :class="getCardClass(bidang2Node, 'bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-600 shadow-[0_10px_30px_-5px_rgba(8,145,178,0.35)] border-2 border-cyan-300/50')"
              :style="getCardStyle(bidang2Node)"
              class="w-full rounded-2xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-2 text-center bg-black/25 backdrop-blur-md min-h-[44px] flex items-center justify-center gap-1">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight drop-shadow">
                  {{ bidang2Node?.node_title || 'BIDANG PELAYANAN PENCATATAN SIPIL' }}
                </h3>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-white/40 dark:border-slate-800 shadow-inner">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-teal-50 dark:bg-slate-800 border border-teal-200 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="bidang2Official?.photo" :src="'/storage/' + bidang2Official.photo" :alt="bidang2Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang2Official?.name || 'Winarno, S.T' }}</h4>
                  <span v-if="bidang2Official?.nip" class="inline-block text-[8.5px] font-mono text-teal-600 dark:text-teal-400 truncate">NIP. {{ bidang2Official.nip }}</span>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-teal-400/90 shadow-[0_0_6px_rgba(45,212,191,0.5)]"></div>

            <div 
              @click="selectNode(subBidang2Node)"
              :class="getCardClass(subBidang2Node, 'bg-gradient-to-r from-teal-600 to-cyan-600 shadow-[0_6px_20px_-5px_rgba(8,145,178,0.3)] border border-cyan-300/40')"
              :style="getCardStyle(subBidang2Node)"
              class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-1 text-center bg-black/20 backdrop-blur-md min-h-[32px] flex items-center justify-center">
                <h4 class="font-bold text-[10px] uppercase text-white leading-tight drop-shadow">{{ subBidang2Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md m-1 p-1.5 rounded-lg border border-white/40 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div v-for="i in 8" :key="i" class="h-4 rounded border border-cyan-200/80 bg-cyan-50/80 dark:bg-slate-800 dark:border-slate-700 shadow-inner hover:bg-cyan-100 transition"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 3 -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 bg-teal-400/90 shadow-[0_0_6px_rgba(45,212,191,0.5)] -mt-6"></div>
            <div 
              @click="selectNode(bidang3Node)"
              :class="getCardClass(bidang3Node, 'bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-600 shadow-[0_10px_30px_-5px_rgba(8,145,178,0.35)] border-2 border-cyan-300/50')"
              :style="getCardStyle(bidang3Node)"
              class="w-full rounded-2xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-2 text-center bg-black/25 backdrop-blur-md min-h-[44px] flex items-center justify-center gap-1">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight drop-shadow">
                  {{ bidang3Node?.node_title || 'BIDANG PENGELOLAAN INFORMASI ADM. KEPENDUDUKAN' }}
                </h3>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-white/40 dark:border-slate-800 shadow-inner">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-teal-50 dark:bg-slate-800 border border-teal-200 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="bidang3Official?.photo" :src="'/storage/' + bidang3Official.photo" :alt="bidang3Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang3Official?.name || 'Nanang Kurniawan, S.Pt' }}</h4>
                  <span v-if="bidang3Official?.nip" class="inline-block text-[8.5px] font-mono text-teal-600 dark:text-teal-400 truncate">NIP. {{ bidang3Official.nip }}</span>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-teal-400/90 shadow-[0_0_6px_rgba(45,212,191,0.5)]"></div>

            <div 
              @click="selectNode(subBidang3Node)"
              :class="getCardClass(subBidang3Node, 'bg-gradient-to-r from-teal-600 to-cyan-600 shadow-[0_6px_20px_-5px_rgba(8,145,178,0.3)] border border-cyan-300/40')"
              :style="getCardStyle(subBidang3Node)"
              class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-1 text-center bg-black/20 backdrop-blur-md min-h-[32px] flex items-center justify-center">
                <h4 class="font-bold text-[10px] uppercase text-white leading-tight drop-shadow">{{ subBidang3Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md m-1 p-1.5 rounded-lg border border-white/40 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div v-for="i in 8" :key="i" class="h-4 rounded border border-cyan-200/80 bg-cyan-50/80 dark:bg-slate-800 dark:border-slate-700 shadow-inner hover:bg-cyan-100 transition"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 4 -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 bg-teal-400/90 shadow-[0_0_6px_rgba(45,212,191,0.5)] -mt-6"></div>
            <div 
              @click="selectNode(bidang4Node)"
              :class="getCardClass(bidang4Node, 'bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-600 shadow-[0_10px_30px_-5px_rgba(8,145,178,0.35)] border-2 border-cyan-300/50')"
              :style="getCardStyle(bidang4Node)"
              class="w-full rounded-2xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-2 text-center bg-black/25 backdrop-blur-md min-h-[44px] flex items-center justify-center gap-1">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight drop-shadow">
                  {{ bidang4Node?.node_title || 'BIDANG PEMANFAATAN DATA DAN INOVASI PELAYANAN' }}
                </h3>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-white/40 dark:border-slate-800 shadow-inner">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-teal-50 dark:bg-slate-800 border border-teal-200 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="bidang4Official?.photo" :src="'/storage/' + bidang4Official.photo" :alt="bidang4Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang4Official?.name || 'Muhaimin, S.Kom' }}</h4>
                  <span v-if="bidang4Official?.nip" class="inline-block text-[8.5px] font-mono text-teal-600 dark:text-teal-400 truncate">NIP. {{ bidang4Official.nip }}</span>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-teal-400/90 shadow-[0_0_6px_rgba(45,212,191,0.5)]"></div>

            <div 
              @click="selectNode(subBidang4Node)"
              :class="getCardClass(subBidang4Node, 'bg-gradient-to-r from-teal-600 to-cyan-600 shadow-[0_6px_20px_-5px_rgba(8,145,178,0.3)] border border-cyan-300/40')"
              :style="getCardStyle(subBidang4Node)"
              class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-1 text-center bg-black/20 backdrop-blur-md min-h-[32px] flex items-center justify-center">
                <h4 class="font-bold text-[10px] uppercase text-white leading-tight drop-shadow">{{ subBidang4Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md m-1 p-1.5 rounded-lg border border-white/40 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div v-for="i in 8" :key="i" class="h-4 rounded border border-cyan-200/80 bg-cyan-50/80 dark:bg-slate-800 dark:border-slate-700 shadow-inner hover:bg-cyan-100 transition"></div>
                </div>
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

// Dynamic color resolution helper functions
const getCardStyle = (node) => {
  const color = node?.color_code?.trim()
  if (!color) return {}
  if (color.startsWith('#') || color.startsWith('rgb') || color.startsWith('hsl')) {
    return { 
      backgroundColor: color, 
      borderColor: color,
      boxShadow: `0 10px 30px -5px ${color}66`
    }
  }
  return {}
}

const getCardClass = (node, defaultClasses) => {
  const color = node?.color_code?.trim()?.toLowerCase()
  if (!color) return defaultClasses
  if (color.startsWith('#') || color.startsWith('rgb') || color.startsWith('hsl')) {
    return 'border-2 text-white shadow-xl'
  }
  if (color === 'pink' || color === 'magenta') return 'bg-gradient-to-r from-pink-600 via-rose-600 to-purple-600 shadow-[0_10px_30px_-5px_rgba(225,29,72,0.4)] border-2 border-pink-300/50 text-white'
  if (color === 'red' || color === 'rose') return 'bg-gradient-to-r from-rose-600 via-red-600 to-pink-600 shadow-[0_10px_30px_-5px_rgba(225,29,72,0.4)] border-2 border-rose-400/50 text-white'
  if (color === 'orange' || color === 'amber') return 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 shadow-[0_10px_30px_-5px_rgba(245,158,11,0.4)] border-2 border-amber-300/50 text-white'
  if (color === 'teal' || color === 'cyan') return 'bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-600 shadow-[0_10px_30px_-5px_rgba(8,145,178,0.35)] border-2 border-cyan-300/50 text-white'
  if (color === 'blue' || color === 'indigo' || color === '#1e00ff') return 'bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 shadow-[0_10px_30px_-5px_rgba(37,99,235,0.45)] border-2 border-blue-400/50 text-white'
  
  return defaultClasses
}

// Find nodes from tree prop
const kadisNode = computed(() => props.tree?.[0] || null)
const kadisOfficial = computed(() => kadisNode.value?.official || null)

const sekretarisNode = computed(() => kadisNode.value?.children?.find(c => c.node_title.toUpperCase().includes('SEKRETARIS')) || null)
const sekretarisOfficial = computed(() => sekretarisNode.value?.official || null)

const fungsionalKadisNode = computed(() => kadisNode.value?.children?.find(c => c.node_title.toUpperCase().includes('FUNGSIONAL') && !c.node_title.toUpperCase().includes('SEKRETARIS')) || null)

const subNodes = computed(() => sekretarisNode.value?.children || [])
const subNode1 = computed(() => subNodes.value.find(c => c.node_title.toUpperCase().includes('FUNGSIONAL')) || { node_title: 'KELOMPOK JABATAN FUNGSIONAL' })
const subNode2 = computed(() => subNodes.value.find(c => c.node_title.toUpperCase().includes('KEUANGAN')) || { node_title: 'SUB BAGIAN KEUANGAN DAN ASET' })
const subNode3 = computed(() => subNodes.value.find(c => c.node_title.toUpperCase().includes('UMUM') || c.node_title.toUpperCase().includes('KEPEGAWAIAN')) || { node_title: 'SUB BAGIAN UMUM & KEPEGAWAIAN' })

const bidangNodes = computed(() => kadisNode.value?.children?.filter(c => c.node_title.toUpperCase().includes('BIDANG')) || [])
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
