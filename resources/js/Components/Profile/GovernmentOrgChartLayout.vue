<template>
  <div class="flex flex-col items-center py-8 select-none w-[1200px] mx-auto text-slate-900 dark:text-white">
    <!-- LEVEL 1: KEPALA DINAS (Centered at top) -->
    <div class="flex flex-col items-center relative z-30">
      <div 
        @click="selectNode(kadisNode)"
        class="w-72 rounded-2xl shadow-2xl border-2 border-rose-500 bg-rose-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden group"
      >
        <div class="px-3 py-2 text-center bg-rose-700/60 border-b border-rose-500/50">
          <h3 class="font-black text-xs uppercase tracking-wider text-white">{{ kadisNode?.node_title || 'KEPALA DINAS' }}</h3>
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

      <!-- Central Trunk Vertical Line from Kadis -->
      <div class="w-0.5 h-8 bg-blue-400/80"></div>
    </div>

    <!-- MAIN TREE CANVAS WRAPPER (Exact 1200px width grid) -->
    <div class="w-full relative flex flex-col items-center">
      
      <!-- Central Vertical Trunk Continuation (STOPS EXACTLY at the 4-Bidang horizontal bar) -->
      <div class="absolute top-0 h-[296px] left-1/2 -translate-x-1/2 w-0.5 bg-blue-400/80 z-0"></div>

      <!-- LEVEL 2 ROW (2 Columns: Left 50% & Right 50%) -->
      <div class="grid grid-cols-2 w-full z-10 pt-0">
        
        <!-- LEFT COLUMN: JABATAN FUNGSIONAL -->
        <div class="flex flex-col items-center relative">
          <!-- Top Horizontal connector segment (from center 50% to right edge 100%) -->
          <div class="w-full relative h-6 flex justify-center">
            <div class="absolute top-0 left-1/2 right-0 h-0.5 border-t-2 border-dashed border-slate-400"></div>
            <div class="w-0.5 h-6 border-l-2 border-dashed border-slate-400"></div>
          </div>

          <div 
            @click="selectNode(fungsionalKadisNode)"
            class="w-64 rounded-2xl shadow-xl border-2 border-amber-400 bg-amber-500 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-3 py-1.5 text-center bg-amber-600/50">
              <h3 class="font-black text-[11px] uppercase tracking-wider text-white">{{ fungsionalKadisNode?.node_title || 'JABATAN FUNGSIONAL' }}</h3>
            </div>
            <div class="bg-white dark:bg-slate-900 mx-1.5 my-1.5 p-2 rounded-xl border border-slate-100 dark:border-slate-800">
              <div class="grid grid-cols-4 gap-1.5 py-1">
                <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800"></div>
                <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800"></div>
                <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800"></div>
                <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800"></div>
                <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800"></div>
                <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800"></div>
                <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800"></div>
                <div class="h-6 rounded border border-amber-200 dark:border-slate-700 bg-amber-50/60 dark:bg-slate-800"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: SEKRETARIAT + SUB BAGIAN ROW -->
        <div class="flex flex-col items-center px-4 relative">
          <!-- Top Horizontal connector segment (from left edge 0% to center 50%) -->
          <div class="w-full relative h-6 flex justify-center">
            <div class="absolute top-0 left-0 right-1/2 h-0.5 bg-blue-400/80"></div>
            <div class="w-0.5 h-6 bg-blue-400/80"></div>
          </div>
          
          <!-- Sekretariat Card -->
          <div 
            @click="selectNode(sekretarisNode)"
            class="w-72 rounded-2xl shadow-xl border-2 border-pink-400 bg-pink-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
          >
            <div class="px-3 py-1.5 text-center bg-pink-700/50">
              <h3 class="font-black text-xs uppercase tracking-wider text-white">{{ sekretarisNode?.node_title || 'SEKRETARIAT' }}</h3>
            </div>
            <div class="bg-white dark:bg-slate-900 mx-1.5 my-1.5 p-2.5 rounded-xl flex items-center gap-3 border border-slate-100 dark:border-slate-800">
              <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0 flex items-center justify-center">
                <img v-if="sekretarisOfficial?.photo" :src="'/storage/' + sekretarisOfficial.photo" :alt="sekretarisOfficial.name" class="w-full h-full object-cover" />
                <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <div class="overflow-hidden">
                <h4 class="font-black text-xs text-slate-900 dark:text-white truncate">{{ sekretarisOfficial?.name || 'Arifin, S.Sos' }}</h4>
                <p v-if="sekretarisOfficial?.nip" class="text-[10px] text-slate-400 truncate mt-0.5">NIP. {{ sekretarisOfficial.nip }}</p>
              </div>
            </div>
          </div>

          <div class="w-0.5 h-6 bg-blue-400/80"></div>

          <!-- SUB BAGIAN ROW (3 Columns under Sekretariat) -->
          <div class="w-full relative">
            <div class="grid grid-cols-3 gap-3 w-full">
              <!-- Sub Bagian 1: Sub Bagian Keuangan -->
              <div class="flex flex-col items-center relative">
                <!-- Top horizontal connector from center (50%) to right edge (100%) -->
                <div class="w-full relative h-4 flex justify-center">
                  <div class="absolute top-0 left-1/2 right-0 h-0.5 bg-blue-400/80"></div>
                  <div class="w-0.5 h-4 bg-blue-400/80"></div>
                </div>
                <div @click="selectNode(subNode2)" class="w-full rounded-xl shadow-lg border border-amber-400 bg-amber-500 text-white cursor-pointer transition hover:scale-105 overflow-hidden">
                  <div class="px-1.5 py-1 text-center bg-amber-600/50 min-h-[32px] flex items-center justify-center">
                    <h4 class="font-bold text-[10px] uppercase text-white leading-tight">{{ subNode2?.node_title || 'SUB BAGIAN KEUANGAN' }}</h4>
                  </div>
                  <div class="bg-white dark:bg-slate-900 m-1 p-2 rounded-lg flex items-center gap-1.5 border border-slate-100 dark:border-slate-800 min-h-[52px]">
                    <div class="w-7 h-7 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center text-slate-400">
                      <img v-if="subNode2?.official?.photo" :src="'/storage/' + subNode2.official.photo" :alt="subNode2.official.name" class="w-full h-full object-cover" />
                      <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="overflow-hidden">
                      <span class="font-bold text-[9.5px] text-slate-800 dark:text-white block truncate">{{ subNode2?.official?.name || 'Staf Keuangan' }}</span>
                      <span v-if="subNode2?.official?.nip" class="text-[8.5px] text-slate-400 block truncate">NIP. {{ subNode2.official.nip }}</span>
                      <span v-else class="text-[8.5px] text-slate-400 block truncate">Pengelola Keuangan</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sub Bagian 2: Sub Bagian Umum & Kepegawaian -->
              <div class="flex flex-col items-center relative">
                <!-- Top horizontal connector full width (0% to 100%) -->
                <div class="w-full relative h-4 flex justify-center">
                  <div class="absolute top-0 left-0 right-0 h-0.5 bg-blue-400/80"></div>
                  <div class="w-0.5 h-4 bg-blue-400/80"></div>
                </div>
                <div @click="selectNode(subNode3)" class="w-full rounded-xl shadow-lg border border-amber-400 bg-amber-500 text-white cursor-pointer transition hover:scale-105 overflow-hidden">
                  <div class="px-1.5 py-1 text-center bg-amber-600/50 min-h-[32px] flex items-center justify-center">
                    <h4 class="font-bold text-[10px] uppercase text-white leading-tight">{{ subNode3?.node_title || 'SUB BAGIAN UMUM & KEPEG.' }}</h4>
                  </div>
                  <div class="bg-white dark:bg-slate-900 m-1 p-2 rounded-lg flex items-center gap-1.5 border border-slate-100 dark:border-slate-800 min-h-[52px]">
                    <div class="w-7 h-7 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center text-slate-400">
                      <img v-if="subNode3?.official?.photo" :src="'/storage/' + subNode3.official.photo" :alt="subNode3.official.name" class="w-full h-full object-cover" />
                      <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="overflow-hidden">
                      <span class="font-bold text-[9.5px] text-slate-800 dark:text-white block truncate">{{ subNode3?.official?.name || 'Indah Dewi Marini, S.E' }}</span>
                      <span v-if="subNode3?.official?.nip" class="text-[8.5px] text-slate-400 block truncate">NIP. {{ subNode3.official.nip }}</span>
                      <span v-else class="text-[8.5px] text-slate-400 block truncate">Umum & Kepegawaian</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sub Bagian 3: Kelompok Jabatan Fungsional -->
              <div class="flex flex-col items-center relative">
                <!-- Top horizontal connector from left edge (0%) to center (50%) -->
                <div class="w-full relative h-4 flex justify-center">
                  <div class="absolute top-0 left-0 right-1/2 h-0.5 bg-blue-400/80"></div>
                  <div class="w-0.5 h-4 bg-blue-400/80"></div>
                </div>
                <div @click="selectNode(subNode1)" class="w-full rounded-xl shadow-lg border border-amber-400 bg-amber-500 text-white cursor-pointer transition hover:scale-105 overflow-hidden">
                  <div class="px-1.5 py-1 text-center bg-amber-600/50 min-h-[32px] flex items-center justify-center">
                    <h4 class="font-bold text-[10px] uppercase text-white leading-tight">{{ subNode1?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
                  </div>
                  <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                    <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                      <div class="h-4 rounded border border-amber-200 bg-amber-50 dark:bg-slate-800 dark:border-slate-700"></div>
                      <div class="h-4 rounded border border-amber-200 bg-amber-50 dark:bg-slate-800 dark:border-slate-700"></div>
                      <div class="h-4 rounded border border-amber-200 bg-amber-50 dark:bg-slate-800 dark:border-slate-700"></div>
                      <div class="h-4 rounded border border-amber-200 bg-amber-50 dark:bg-slate-800 dark:border-slate-700"></div>
                      <div class="h-4 rounded border border-amber-200 bg-amber-50 dark:bg-slate-800 dark:border-slate-700"></div>
                      <div class="h-4 rounded border border-amber-200 bg-amber-50 dark:bg-slate-800 dark:border-slate-700"></div>
                      <div class="h-4 rounded border border-amber-200 bg-amber-50 dark:bg-slate-800 dark:border-slate-700"></div>
                      <div class="h-4 rounded border border-amber-200 bg-amber-50 dark:bg-slate-800 dark:border-slate-700"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SPACER & CONNECTOR TO LEVEL 4 (4 BIDANG ROW) -->
      <div class="w-full relative flex flex-col items-center mt-10 z-10">
        <!-- 4 BIDANG GRID ROW -->
        <div class="grid grid-cols-4 gap-5 w-full px-2">
          <!-- BIDANG 1 -->
          <div class="flex flex-col items-center relative">
            <div class="w-full relative h-6 flex justify-center">
              <div class="absolute top-0 left-1/2 right-0 h-0.5 bg-blue-400/80"></div>
              <div class="w-0.5 h-6 bg-blue-400/80"></div>
            </div>
            <div 
              @click="selectNode(bidang1Node)"
              class="w-full rounded-2xl shadow-xl border-2 border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
            >
              <div class="px-2 py-2 text-center bg-teal-800/40 min-h-[44px] flex items-center justify-center">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight">
                  {{ bidang1Node?.node_title || 'BIDANG PELAYANAN PENDAFTARAN PENDUDUK' }}
                </h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                  <img v-if="bidang1Official?.photo" :src="'/storage/' + bidang1Official.photo" :alt="bidang1Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang1Official?.name || 'Nur Insani, S.Kom' }}</h4>
                  <p v-if="bidang1Official?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ bidang1Official.nip }}</p>
                </div>
              </div>
            </div>

            <!-- Connector to Sub Koordinator -->
            <div class="w-0.5 h-5 bg-blue-400/80"></div>

            <div 
              @click="selectNode(subBidang1Node)"
              class="w-full rounded-xl shadow-lg border border-teal-400 bg-teal-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
            >
              <div class="px-2 py-1 text-center bg-teal-800/40 min-h-[32px] flex items-center justify-center">
                <h4 class="font-bold text-[10px] uppercase text-white leading-tight">{{ subBidang1Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 2 -->
          <div class="flex flex-col items-center relative">
            <div class="w-full relative h-6 flex justify-center">
              <div class="absolute top-0 left-0 right-0 h-0.5 bg-blue-400/80"></div>
              <div class="w-0.5 h-6 bg-blue-400/80"></div>
            </div>
            <div 
              @click="selectNode(bidang2Node)"
              class="w-full rounded-2xl shadow-xl border-2 border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
            >
              <div class="px-2 py-2 text-center bg-teal-800/40 min-h-[44px] flex items-center justify-center">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight">
                  {{ bidang2Node?.node_title || 'BIDANG PELAYANAN PENCATATAN SIPIL' }}
                </h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                  <img v-if="bidang2Official?.photo" :src="'/storage/' + bidang2Official.photo" :alt="bidang2Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang2Official?.name || 'Winarno, S.T' }}</h4>
                  <p v-if="bidang2Official?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ bidang2Official.nip }}</p>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-blue-400/80"></div>

            <div 
              @click="selectNode(subBidang2Node)"
              class="w-full rounded-xl shadow-lg border border-teal-400 bg-teal-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
            >
              <div class="px-2 py-1 text-center bg-teal-800/40 min-h-[32px] flex items-center justify-center">
                <h4 class="font-bold text-[10px] uppercase text-white leading-tight">{{ subBidang2Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 3 -->
          <div class="flex flex-col items-center relative">
            <div class="w-full relative h-6 flex justify-center">
              <div class="absolute top-0 left-0 right-0 h-0.5 bg-blue-400/80"></div>
              <div class="w-0.5 h-6 bg-blue-400/80"></div>
            </div>
            <div 
              @click="selectNode(bidang3Node)"
              class="w-full rounded-2xl shadow-xl border-2 border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
            >
              <div class="px-2 py-2 text-center bg-teal-800/40 min-h-[44px] flex items-center justify-center">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight">
                  {{ bidang3Node?.node_title || 'BIDANG PENGELOLAAN INFORMASI ADM. KEPENDUDUKAN' }}
                </h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                  <img v-if="bidang3Official?.photo" :src="'/storage/' + bidang3Official.photo" :alt="bidang3Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang3Official?.name || 'Nanang Kurniawan, S.Pt' }}</h4>
                  <p v-if="bidang3Official?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ bidang3Official.nip }}</p>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-blue-400/80"></div>

            <div 
              @click="selectNode(subBidang3Node)"
              class="w-full rounded-xl shadow-lg border border-teal-400 bg-teal-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
            >
              <div class="px-2 py-1 text-center bg-teal-800/40 min-h-[32px] flex items-center justify-center">
                <h4 class="font-bold text-[10px] uppercase text-white leading-tight">{{ subBidang3Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BIDANG 4 -->
          <div class="flex flex-col items-center relative">
            <div class="w-full relative h-6 flex justify-center">
              <div class="absolute top-0 left-0 right-1/2 h-0.5 bg-blue-400/80"></div>
              <div class="w-0.5 h-6 bg-blue-400/80"></div>
            </div>
            <div 
              @click="selectNode(bidang4Node)"
              class="w-full rounded-2xl shadow-xl border-2 border-teal-400 bg-gradient-to-r from-teal-600 to-cyan-700 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
            >
              <div class="px-2 py-2 text-center bg-teal-800/40 min-h-[44px] flex items-center justify-center">
                <h3 class="font-extrabold text-[11px] uppercase tracking-wider text-white leading-tight">
                  {{ bidang4Node?.node_title || 'BIDANG PEMANFAATAN DATA DAN INOVASI PELAYANAN' }}
                </h3>
              </div>
              <div class="bg-white dark:bg-slate-900 mx-1.5 mb-1.5 p-2 rounded-xl flex items-center gap-2 border border-slate-100 dark:border-slate-800">
                <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                  <img v-if="bidang4Official?.photo" :src="'/storage/' + bidang4Official.photo" :alt="bidang4Official.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                  <h4 class="font-black text-[11px] text-slate-900 dark:text-white truncate">{{ bidang4Official?.name || 'Muhaimin, S.Kom' }}</h4>
                  <p v-if="bidang4Official?.nip" class="text-[9px] text-slate-400 truncate">NIP. {{ bidang4Official.nip }}</p>
                </div>
              </div>
            </div>

            <div class="w-0.5 h-5 bg-blue-400/80"></div>

            <div 
              @click="selectNode(subBidang4Node)"
              class="w-full rounded-xl shadow-lg border border-teal-400 bg-teal-600 text-white cursor-pointer transition hover:scale-105 overflow-hidden"
            >
              <div class="px-2 py-1 text-center bg-teal-800/40 min-h-[32px] flex items-center justify-center">
                <h4 class="font-bold text-[10px] uppercase text-white leading-tight">{{ subBidang4Node?.node_title || 'KELOMPOK JABATAN FUNGSIONAL' }}</h4>
              </div>
              <div class="bg-white dark:bg-slate-900 m-1 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 min-h-[52px] flex items-center">
                <div class="grid grid-cols-4 gap-1 w-full py-0.5">
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
                  <div class="h-4 rounded border border-teal-200 bg-teal-50 dark:bg-slate-800 dark:border-slate-700"></div>
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
