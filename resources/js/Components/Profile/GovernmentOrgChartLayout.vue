<template>
  <div class="flex flex-col items-center py-8 select-none w-[1200px] mx-auto text-slate-900 dark:text-white">
    <!-- LEVEL 1: KEPALA DINAS (Centered at top) -->
    <div class="flex flex-col items-center relative z-30">
      <div 
        @click="selectNode(kadisNode)"
        :class="getCardClass(kadisNode, 'bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 shadow-xl shadow-blue-900/20 border-2 border-blue-400')"
        :style="getCardStyle(kadisNode)"
        class="w-72 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl overflow-hidden group cursor-pointer active:scale-95 relative"
      >
        <div class="px-3 py-2 text-center bg-black/20 backdrop-blur-md border-b border-white/20 rounded-t-[14px] flex items-center justify-center gap-1.5">
          <span class="text-xs">👑</span>
          <h3 class="font-black text-xs uppercase tracking-wider text-white drop-shadow-sm">{{ kadisNode?.node_title || 'KEPALA DINAS' }}</h3>
        </div>
        <div class="bg-white dark:bg-slate-900 mx-1.5 my-1.5 p-2.5 rounded-xl flex items-center gap-3 border border-slate-100 dark:border-slate-800 shadow-sm">
          <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0 flex items-center justify-center shadow-sm">
            <img v-if="kadisOfficial?.photo" :src="storageUrl(kadisOfficial.photo)" :alt="kadisOfficial.name" class="w-full h-full object-cover" />
            <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div class="overflow-hidden">
            <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ kadisOfficial?.name || 'Belum dikonfigurasi' }}</h4>
            <span v-if="kadisOfficial?.nip" class="inline-block text-[9.5px] font-mono text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-blue-100 dark:border-slate-700 truncate mt-1">NIP. {{ kadisOfficial.nip }}</span>
          </div>
        </div>
      </div>

      <!-- Central Trunk Vertical Line from Kadis -->
      <div class="w-0.5 h-8 bg-blue-600 dark:bg-blue-500"></div>
    </div>

    <!-- MAIN TREE CANVAS WRAPPER (Exact 1200px width grid) -->
    <div class="w-full relative flex flex-col items-center">
      
      <!-- LEVEL 2 SECTION -->
      <div class="w-full relative flex flex-col items-center">
        <!-- Central Trunk Line through Level 2 -->
        <div class="absolute top-0 bottom-0 left-1/2 -translate-x-1/2 w-0.5 bg-blue-600 dark:bg-blue-500 z-0"></div>

        <!-- T-Junction Dot from Kadis drop line -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-900/50 z-20"></div>

        <!-- LEVEL 2 CONNECTOR LINES -->
        <div class="absolute top-0 left-[25%] right-1/2 h-0.5 border-t-2 border-dashed border-amber-500/90 z-10"></div>
        <div class="absolute top-0 left-1/2 right-[25%] h-0.5 bg-blue-600 dark:bg-blue-500 z-10"></div>

        <!-- Left & Right Junction Dots -->
        <div class="absolute top-0 left-[25%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-amber-500 ring-4 ring-amber-100 dark:ring-amber-900/50 z-20"></div>
        <div class="absolute top-0 right-[25%] translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-900/50 z-20"></div>

        <!-- LEVEL 2 ROW (2 Columns: Left 50% & Right 50%) -->
        <div class="grid grid-cols-2 w-full z-10 pt-0">
          
          <!-- LEFT COLUMN: JABATAN FUNGSIONAL -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 border-l-2 border-dashed border-amber-500/90"></div>
            <div 
              @click="selectNode(fungsionalKadisNode)"
              :class="getCardClass(fungsionalKadisNode, 'bg-gradient-to-r from-amber-600 via-amber-600 to-orange-700 shadow-lg shadow-amber-900/20 border-2 border-amber-400')"
              :style="getCardStyle(fungsionalKadisNode)"
              class="w-64 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-3 py-1.5 text-center bg-black/20 backdrop-blur-md rounded-t-[14px] flex items-center justify-center gap-1">
                <span class="text-[10px]">📊</span>
                <h3 class="font-black text-[11px] uppercase tracking-wider text-white drop-shadow-sm">{{ fungsionalKadisNode?.node_title || 'JABATAN FUNGSIONAL' }}</h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 my-1.5 p-2 rounded-xl border border-slate-100 dark:border-slate-800">
                <div class="grid grid-cols-4 gap-1.5 py-1">
                  <div v-for="i in 8" :key="i" class="h-5 rounded-md border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800 shadow-inner hover:bg-amber-100 transition"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN: SEKRETARIAT + SUB BAGIAN ROW -->
          <div class="flex flex-col items-center px-4 relative">
            <div class="w-0.5 h-6 bg-blue-600 dark:bg-blue-500"></div>
            
            <!-- Sekretariat Card -->
            <div 
              @click="selectNode(sekretarisNode)"
              :class="getCardClass(sekretarisNode, 'bg-gradient-to-r from-emerald-700 via-teal-700 to-emerald-800 shadow-lg shadow-emerald-900/20 border-2 border-emerald-400')"
              :style="getCardStyle(sekretarisNode)"
              class="w-72 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-3 py-1.5 text-center bg-black/20 backdrop-blur-md rounded-t-[14px] flex items-center justify-center gap-1">
                <span class="text-[10px]">⭐</span>
                <h3 class="font-black text-xs uppercase tracking-wider text-white drop-shadow-sm">{{ sekretarisNode?.node_title || 'SEKRETARIAT' }}</h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 my-1.5 p-2.5 rounded-xl flex items-center gap-3 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="sekretarisOfficial?.photo" :src="storageUrl(sekretarisOfficial.photo)" :alt="sekretarisOfficial.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ sekretarisOfficial?.name || 'Arifin, S.Sos' }}</h4>
                  <span v-if="sekretarisOfficial?.nip" class="inline-block text-[9px] font-mono text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-emerald-100 dark:border-slate-700 truncate mt-0.5">NIP. {{ sekretarisOfficial.nip }}</span>
                </div>
              </div>
            </div>

            <!-- Drop Line from Sekretariat -->
            <div class="w-0.5 h-6 bg-blue-600 dark:bg-blue-500"></div>

            <!-- SUB BAGIAN ROW (3 Columns under Sekretariat) -->
            <div class="w-full relative flex flex-col items-center">
              <div class="w-full relative h-4">
                <!-- Sub-horizontal connector line -->
                <div class="absolute top-0 left-[16.67%] right-[16.67%] h-0.5 bg-blue-600 dark:bg-blue-500"></div>
                <!-- T-Junctions -->
                <div class="absolute top-0 left-[16.67%] -translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-blue-600"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-blue-600"></div>
                <div class="absolute top-0 right-[16.67%] translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-blue-600"></div>
              </div>

              <div class="grid grid-cols-3 gap-3 w-full">
                <!-- Sub Bagian 1: Sub Bagian Keuangan -->
                <div class="flex flex-col items-center">
                  <div class="w-0.5 h-4 bg-blue-600 dark:bg-blue-500 -mt-4"></div>
                  <div 
                    @click="selectNode(subNode2)" 
                    :class="getCardClass(subNode2, 'bg-gradient-to-r from-amber-600 to-amber-700 shadow-md border border-amber-300')"
                    :style="getCardStyle(subNode2)"
                    class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg overflow-hidden cursor-pointer active:scale-95"
                  >
                    <div class="px-1.5 py-1 text-center bg-black/20 backdrop-blur-md rounded-t-[10px] min-h-[32px] flex items-center justify-center">
                      <h4 class="font-extrabold text-[10px] uppercase text-white leading-tight drop-shadow-sm">{{ subNode2?.node_title || 'SUB BAGIAN KEUANGAN' }}</h4>
                    </div>
                    <div class="bg-white dark:bg-slate-900 m-1 p-2 rounded-lg flex items-center gap-1.5 border border-slate-100 dark:border-slate-800 min-h-[52px]">
                      <div class="w-7 h-7 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center text-slate-400 shadow-sm">
                        <img v-if="subNode2?.official?.photo" :src="storageUrl(subNode2.official.photo)" :alt="subNode2.official.name" class="w-full h-full object-cover" />
                        <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      </div>
                      <div class="overflow-hidden">
                        <span class="font-bold text-[9.5px] text-slate-800 dark:text-white block truncate">{{ subNode2?.official?.name || 'Staf Keuangan' }}</span>
                        <span v-if="subNode2?.official?.nip" class="text-[8.5px] font-mono text-amber-700 dark:text-amber-300 block truncate">NIP. {{ subNode2.official.nip }}</span>
                        <span v-else class="text-[8.5px] text-slate-400 block truncate">Pengelola Keuangan</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Sub Bagian 2: Sub Bagian Umum & Kepegawaian -->
                <div class="flex flex-col items-center">
                  <div class="w-0.5 h-4 bg-blue-600 dark:bg-blue-500 -mt-4"></div>
                  <div 
                    @click="selectNode(subNode3)" 
                    :class="getCardClass(subNode3, 'bg-gradient-to-r from-amber-600 to-amber-700 shadow-md border border-amber-300')"
                    :style="getCardStyle(subNode3)"
                    class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg overflow-hidden cursor-pointer active:scale-95"
                  >
                    <div class="px-1.5 py-1 text-center bg-black/20 backdrop-blur-md rounded-t-[10px] min-h-[32px] flex items-center justify-center">
                      <h4 class="font-extrabold text-[10px] uppercase text-white leading-tight drop-shadow-sm">{{ subNode3?.node_title || 'SUB BAGIAN UMUM & KEPEG.' }}</h4>
                    </div>
                    <div class="bg-white dark:bg-slate-900 m-1 p-2 rounded-lg flex items-center gap-1.5 border border-slate-100 dark:border-slate-800 min-h-[52px]">
                      <div class="w-7 h-7 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center text-slate-400 shadow-sm">
                        <img v-if="subNode3?.official?.photo" :src="storageUrl(subNode3.official.photo)" :alt="subNode3.official.name" class="w-full h-full object-cover" />
                        <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      </div>
                      <div class="overflow-hidden">
                        <span class="font-bold text-[9.5px] text-slate-800 dark:text-white block truncate">{{ subNode3?.official?.name || 'Indah Dewi Marini, S.E' }}</span>
                        <span v-if="subNode3?.official?.nip" class="text-[8.5px] font-mono text-amber-700 dark:text-amber-300 block truncate">NIP. {{ subNode3.official.nip }}</span>
                        <span v-else class="text-[8.5px] text-slate-400 block truncate">Umum & Kepegawaian</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Sub Bagian 3: Kelompok Jabatan Fungsional -->
                <div class="flex flex-col items-center">
                  <div class="w-0.5 h-4 bg-blue-600 dark:bg-blue-500 -mt-4"></div>
                  <div 
                    @click="selectNode(subNode1)" 
                    :class="getCardClass(subNode1, 'bg-gradient-to-r from-amber-600 to-amber-700 shadow-md border border-amber-300')"
                    :style="getCardStyle(subNode1)"
                    class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg overflow-hidden cursor-pointer active:scale-95"
                  >
                    <div class="px-1.5 py-1 text-center bg-black/20 backdrop-blur-md rounded-t-[10px] min-h-[32px] flex items-center justify-center">
                      <h4 class="font-extrabold text-[10px] uppercase text-white leading-tight drop-shadow-sm">{{ subNode1?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
                    </div>
                    <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                      <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                        <div v-for="i in 8" :key="i" class="h-4 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800 shadow-inner hover:bg-amber-100 transition"></div>
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
      <div class="w-0.5 h-8 bg-blue-600 dark:bg-blue-500 shrink-0 z-10"></div>

      <!-- T-Junction Dot hitting the 4-Bidang bar -->
      <div class="w-2.5 h-2.5 rounded-full bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-900/50 -mt-1.5 z-20"></div>

      <!-- LEVEL 4 SECTION (4 BIDANGS) -->
      <div class="w-full relative flex flex-col items-center z-10 -mt-1">
        <!-- 4-Bidang Horizontal Bar -->
        <div class="w-full relative h-6">
          <div class="absolute top-0 left-[12.5%] right-[12.5%] h-0.5 bg-blue-600 dark:bg-blue-500"></div>
          <!-- 4 T-Junction Dots for Bidangs -->
          <div class="absolute top-0 left-[12.5%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-blue-600"></div>
          <div class="absolute top-0 left-[37.5%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-blue-600"></div>
          <div class="absolute top-0 left-[62.5%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-blue-600"></div>
          <div class="absolute top-0 left-[87.5%] -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-blue-600"></div>
        </div>

        <!-- 4 BIDANG GRID ROW -->
        <div class="grid grid-cols-4 gap-5 w-full px-2">
          <!-- BIDANG 1 -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 bg-blue-600 dark:bg-blue-500 -mt-6"></div>
            <div 
              @click="selectNode(bidang1Node)"
              :class="getCardClass(bidang1Node, 'bg-gradient-to-r from-sky-700 via-blue-700 to-cyan-800 shadow-lg shadow-sky-900/20 border-2 border-sky-300')"
              :style="getCardStyle(bidang1Node)"
              class="w-full rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-2 text-center bg-black/20 backdrop-blur-md rounded-t-[14px] min-h-[44px] flex items-center justify-center gap-1">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight drop-shadow-sm">
                  {{ bidang1Node?.node_title || 'BIDANG PELAYANAN PENDAFTARAN PENDUDUK' }}
                </h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="bidang1Official?.photo" :src="storageUrl(bidang1Official.photo)" :alt="bidang1Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-bold text-[11px] text-slate-900 dark:text-white truncate">{{ bidang1Official?.name || 'Nur Insani, S.Kom' }}</h4>
                  <span v-if="bidang1Official?.nip" class="inline-block text-[8.5px] font-mono text-sky-700 dark:text-sky-300 truncate">NIP. {{ bidang1Official.nip }}</span>
                </div>
              </div>
            </div>

            <!-- Connector to Sub Koordinator -->
            <div class="w-0.5 h-5 bg-blue-600 dark:bg-blue-500"></div>

            <div 
              @click="selectNode(subBidang1Node)"
              :class="getCardClass(subBidang1Node, 'bg-gradient-to-r from-sky-700 to-blue-700 shadow-md border border-sky-300')"
              :style="getCardStyle(subBidang1Node)"
              class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-1 text-center bg-black/20 backdrop-blur-md rounded-t-[10px] min-h-[32px] flex items-center justify-center">
                <h4 class="font-extrabold text-[10px] uppercase text-white leading-tight drop-shadow-sm">{{ subBidang1Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div v-for="i in 8" :key="i" class="h-4 rounded border border-sky-200 dark:border-slate-700 bg-sky-50/60 dark:bg-slate-800 shadow-inner hover:bg-sky-100 transition"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 2 -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 bg-blue-600 dark:bg-blue-500 -mt-6"></div>
            <div 
              @click="selectNode(bidang2Node)"
              :class="getCardClass(bidang2Node, 'bg-gradient-to-r from-sky-700 via-blue-700 to-cyan-800 shadow-lg shadow-sky-900/20 border-2 border-sky-300')"
              :style="getCardStyle(bidang2Node)"
              class="w-full rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-2 text-center bg-black/20 backdrop-blur-md rounded-t-[14px] min-h-[44px] flex items-center justify-center gap-1">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight drop-shadow-sm">
                  {{ bidang2Node?.node_title || 'BIDANG PELAYANAN PENCATATAN SIPIL' }}
                </h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="bidang2Official?.photo" :src="storageUrl(bidang2Official.photo)" :alt="bidang2Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-bold text-[11px] text-slate-900 dark:text-white truncate">{{ bidang2Official?.name || 'Winarno, S.T' }}</h4>
                  <span v-if="bidang2Official?.nip" class="inline-block text-[8.5px] font-mono text-sky-700 dark:text-sky-300 truncate">NIP. {{ bidang2Official.nip }}</span>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-blue-600 dark:bg-blue-500"></div>

            <div 
              @click="selectNode(subBidang2Node)"
              :class="getCardClass(subBidang2Node, 'bg-gradient-to-r from-sky-700 to-blue-700 shadow-md border border-sky-300')"
              :style="getCardStyle(subBidang2Node)"
              class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-1 text-center bg-black/20 backdrop-blur-md rounded-t-[10px] min-h-[32px] flex items-center justify-center">
                <h4 class="font-extrabold text-[10px] uppercase text-white leading-tight drop-shadow-sm">{{ subBidang2Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div v-for="i in 8" :key="i" class="h-4 rounded border border-sky-200 dark:border-slate-700 bg-sky-50/60 dark:bg-slate-800 shadow-inner hover:bg-sky-100 transition"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 3 -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 bg-blue-600 dark:bg-blue-500 -mt-6"></div>
            <div 
              @click="selectNode(bidang3Node)"
              :class="getCardClass(bidang3Node, 'bg-gradient-to-r from-sky-700 via-blue-700 to-cyan-800 shadow-lg shadow-sky-900/20 border-2 border-sky-300')"
              :style="getCardStyle(bidang3Node)"
              class="w-full rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-2 text-center bg-black/20 backdrop-blur-md rounded-t-[14px] min-h-[44px] flex items-center justify-center gap-1">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight drop-shadow-sm">
                  {{ bidang3Node?.node_title || 'BIDANG PENGELOLAAN INFORMASI ADM. KEPENDUDUKAN' }}
                </h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="bidang3Official?.photo" :src="storageUrl(bidang3Official.photo)" :alt="bidang3Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-bold text-[11px] text-slate-900 dark:text-white truncate">{{ bidang3Official?.name || 'Nanang Kurniawan, S.Pt' }}</h4>
                  <span v-if="bidang3Official?.nip" class="inline-block text-[8.5px] font-mono text-sky-700 dark:text-sky-300 truncate">NIP. {{ bidang3Official.nip }}</span>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-blue-600 dark:bg-blue-500"></div>

            <div 
              @click="selectNode(subBidang3Node)"
              :class="getCardClass(subBidang3Node, 'bg-gradient-to-r from-sky-700 to-blue-700 shadow-md border border-sky-300')"
              :style="getCardStyle(subBidang3Node)"
              class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-1 text-center bg-black/20 backdrop-blur-md rounded-t-[10px] min-h-[32px] flex items-center justify-center">
                <h4 class="font-extrabold text-[10px] uppercase text-white leading-tight drop-shadow-sm">{{ subBidang3Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div v-for="i in 8" :key="i" class="h-4 rounded border border-sky-200 dark:border-slate-700 bg-sky-50/60 dark:bg-slate-800 shadow-inner hover:bg-sky-100 transition"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 4 -->
          <div class="flex flex-col items-center">
            <div class="w-0.5 h-6 bg-blue-600 dark:bg-blue-500 -mt-6"></div>
            <div 
              @click="selectNode(bidang4Node)"
              :class="getCardClass(bidang4Node, 'bg-gradient-to-r from-sky-700 via-blue-700 to-cyan-800 shadow-lg shadow-sky-900/20 border-2 border-sky-300')"
              :style="getCardStyle(bidang4Node)"
              class="w-full rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-2 text-center bg-black/20 backdrop-blur-md rounded-t-[14px] min-h-[44px] flex items-center justify-center gap-1">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight drop-shadow-sm">
                  {{ bidang4Node?.node_title || 'BIDANG PEMANFAATAN DATA DAN INOVASI PELAYANAN' }}
                </h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center shadow-sm">
                  <img v-if="bidang4Official?.photo" :src="storageUrl(bidang4Official.photo)" :alt="bidang4Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-bold text-[11px] text-slate-900 dark:text-white truncate">{{ bidang4Official?.name || 'Muhaimin, S.Kom' }}</h4>
                  <span v-if="bidang4Official?.nip" class="inline-block text-[8.5px] font-mono text-sky-700 dark:text-sky-300 truncate">NIP. {{ bidang4Official.nip }}</span>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-blue-600 dark:bg-blue-500"></div>

            <div 
              @click="selectNode(subBidang4Node)"
              :class="getCardClass(subBidang4Node, 'bg-gradient-to-r from-sky-700 to-blue-700 shadow-md border border-sky-300')"
              :style="getCardStyle(subBidang4Node)"
              class="w-full rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg overflow-hidden cursor-pointer active:scale-95"
            >
              <div class="px-2 py-1 text-center bg-black/20 backdrop-blur-md rounded-t-[10px] min-h-[32px] flex items-center justify-center">
                <h4 class="font-extrabold text-[10px] uppercase text-white leading-tight drop-shadow-sm">{{ subBidang4Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div v-for="i in 8" :key="i" class="h-4 rounded border border-sky-200 dark:border-slate-700 bg-sky-50/60 dark:bg-slate-800 shadow-inner hover:bg-sky-100 transition"></div>
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
import { storageUrl } from '@/utils/storage'

const props = defineProps({
  tree: Array,
  searchQuery: String,
})

const emit = defineEmits(['select-node'])

const selectNode = (node) => {
  if (node) emit('select-node', node)
}

const isMatched = (node) => {
  if (!props.searchQuery || !props.searchQuery.trim()) return false
  const q = props.searchQuery.toLowerCase().trim()
  const title = (node?.node_title || '').toLowerCase()
  const name = (node?.official?.name || '').toLowerCase()
  const nip = (node?.official?.nip || '').toLowerCase()
  return title.includes(q) || name.includes(q) || nip.includes(q)
}

// Dynamic color resolution helper functions
const getCardStyle = (node) => {
  const color = node?.color_code?.trim()
  if (!color) return {}
  if (color.startsWith('#') || color.startsWith('rgb') || color.startsWith('hsl')) {
    return { 
      backgroundColor: color, 
      borderColor: color,
      boxShadow: `0 10px 25px -5px ${color}55`
    }
  }
  return {}
}

const getCardClass = (node, defaultClasses) => {
  let classes = defaultClasses
  const color = node?.color_code?.trim()?.toLowerCase()
  if (color) {
    if (color.startsWith('#') || color.startsWith('rgb') || color.startsWith('hsl')) {
      classes = 'border-2 text-white shadow-xl'
    } else if (color === 'pink' || color === 'magenta') {
      classes = 'bg-gradient-to-r from-pink-600 via-rose-600 to-purple-700 shadow-lg shadow-pink-900/20 border-2 border-pink-300 text-white'
    } else if (color === 'red' || color === 'rose') {
      classes = 'bg-gradient-to-r from-rose-600 via-red-600 to-pink-700 shadow-lg shadow-rose-900/20 border-2 border-rose-300 text-white'
    } else if (color === 'orange' || color === 'amber') {
      classes = 'bg-gradient-to-r from-amber-600 via-amber-600 to-orange-700 shadow-lg shadow-amber-900/20 border-2 border-amber-300 text-white'
    } else if (color === 'teal' || color === 'emerald') {
      classes = 'bg-gradient-to-r from-emerald-700 via-teal-700 to-emerald-800 shadow-lg shadow-emerald-900/20 border-2 border-emerald-400 text-white'
    } else if (color === 'blue' || color === 'indigo' || color === '#1e00ff') {
      classes = 'bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 shadow-lg shadow-blue-900/20 border-2 border-blue-400 text-white'
    }
  }

  if (isMatched(node)) {
    classes += ' ring-4 ring-amber-400 ring-offset-2 animate-pulse scale-105 z-30'
  }
  
  return classes
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
