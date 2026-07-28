<template>
  <Head title="Hierarki Wilayah Administratif" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Page -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 rounded-lg">
              Kode BPS: {{ summary?.bps_code || '5205' }}
            </span>
            <span class="text-xs text-gray-400 font-medium">Kabupaten Dompu, NTB</span>
          </div>
          <h1 class="text-2xl font-black text-gray-900 dark:text-zinc-50 tracking-tight mt-1">
            Hierarki & Luas Wilayah Administrasi
          </h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Data resmi rekapitulasi luas wilayah 8 Kecamatan dan 81 Desa/Kelurahan di Kabupaten Dompu.
          </p>
        </div>

        <button 
          @click="openKecamatanModal(null)" 
          class="px-4 py-2.5 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white text-xs font-black rounded-2xl shadow-lg shadow-primary-500/20 transition flex items-center justify-center gap-2 shrink-0 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Kecamatan</span>
        </button>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="px-5 py-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-xs rounded-2xl font-bold flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 text-emerald-600" />
        <span>{{ $page.props.flash.success }}</span>
      </div>

      <!-- Overview Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Luas -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Luas Wilayah</span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 flex items-center justify-center">
              <MapPin class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-mono tracking-tight">
              {{ formatDecimal(summary?.total_area || 2324.55) }} <span class="text-xs font-bold text-gray-400">km²</span>
            </div>
            <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
              100,00% Wilayah Kabupaten Dompu
            </p>
          </div>
        </div>

        <!-- Card 2: Total Kecamatan -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Jumlah Kecamatan</span>
            <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 flex items-center justify-center">
              <Building2 class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-mono tracking-tight">
              {{ summary?.total_kecamatans || 8 }} <span class="text-xs font-bold text-gray-400">Kecamatan</span>
            </div>
            <p class="text-[10px] font-medium text-gray-400 mt-1">
              Kec. Terluas: <strong class="text-gray-700 dark:text-zinc-300 font-bold">Pekat (788,91 km²)</strong>
            </p>
          </div>
        </div>

        <!-- Card 3: Kelurahan vs Desa -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Struktur Wilayah</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center">
              <Layers class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-black text-gray-900 dark:text-zinc-50 font-mono tracking-tight">
              {{ summary?.total_wilayah || 81 }} <span class="text-xs font-bold text-gray-400">Total Wilayah</span>
            </div>
            <div class="flex items-center gap-2 text-[10px] font-bold mt-1">
              <span class="text-purple-600 dark:text-purple-400">{{ summary?.total_kelurahans || 9 }} Kelurahan</span>
              <span class="text-gray-300">•</span>
              <span class="text-sky-600 dark:text-sky-400">{{ summary?.total_desas || 72 }} Desa</span>
            </div>
          </div>
        </div>

        <!-- Card 4: Ibu Kota Kabupaten -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Pusat Pemerintahan</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center">
              <Landmark class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-lg font-black text-gray-900 dark:text-zinc-50 tracking-tight">
              Kecamatan Dompu
            </div>
            <p class="text-[10px] font-medium text-gray-400 mt-1">
              172,85 km² (15 Wilayah: 6 Kel, 9 Desa)
            </p>
          </div>
        </div>
      </div>

      <!-- 📊 Rekapitulasi Tabel Luas Wilayah per Kecamatan -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-extrabold text-sm text-gray-900 dark:text-zinc-50 flex items-center gap-2">
              <span>📊 Tabel Rekapitulasi Luas Wilayah per Kecamatan</span>
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Peringkat 8 Kecamatan berdasarkan proporsi luas geografis (km²)</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="border-b border-gray-100 dark:border-zinc-800 text-[10px] uppercase font-extrabold tracking-wider text-gray-400 bg-gray-50/60 dark:bg-zinc-800/40">
                <th class="py-3 px-4 text-center w-12">No</th>
                <th class="py-3 px-4 text-left">Nama Kecamatan</th>
                <th class="py-3 px-4 text-center">Kode Wilayah</th>
                <th class="py-3 px-4 text-center">Jumlah Desa / Kelurahan</th>
                <th class="py-3 px-4 text-right">Luas Wilayah (km²)</th>
                <th class="py-3 px-4 text-left pl-6">Proporsi (%)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60 font-medium">
              <tr 
                v-for="(kec, idx) in sortedKecamatans" 
                :key="kec.id"
                class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/50 transition-colors"
              >
                <td class="py-3 px-4 text-center font-bold text-gray-400">{{ idx + 1 }}</td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <span class="font-black text-gray-900 dark:text-zinc-100">{{ kec.name }}</span>
                    <span v-if="kec.name === 'Pekat'" class="px-2 py-0.5 text-[9px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 rounded-md border border-amber-200 dark:border-amber-900/40">
                      Kec. Terluas
                    </span>
                    <span v-if="kec.name === 'Dompu'" class="px-2 py-0.5 text-[9px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 rounded-md border border-blue-200 dark:border-blue-900/40">
                      Ibu Kota
                    </span>
                  </div>
                </td>
                <td class="py-3 px-4 text-center font-mono font-bold text-gray-500 dark:text-zinc-400">{{ kec.code || '-' }}</td>
                <td class="py-3 px-4 text-center">
                  <span class="px-2.5 py-1 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 font-bold rounded-lg text-[11px]">
                    {{ kec.notes || (kec.desa_count + ' Desa') }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right font-black font-mono text-gray-800 dark:text-zinc-200">
                  {{ formatDecimal(kec.area_km2) }} km²
                </td>
                <td class="py-3 px-4 pl-6">
                  <div class="flex items-center gap-3">
                    <div class="w-24 bg-gray-100 dark:bg-zinc-800 h-2.5 rounded-full overflow-hidden shrink-0">
                      <div 
                        class="h-full bg-gradient-to-r from-primary-500 to-indigo-500 rounded-full"
                        :style="{ width: Math.min(100, (kec.percentage || 0) * 2.8) + '%' }"
                      ></div>
                    </div>
                    <span class="font-bold text-xs font-mono text-primary-600 dark:text-primary-400">
                      {{ formatDecimal(kec.percentage) }}%
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="bg-primary-900 text-white font-black text-xs border-t-2 border-primary-700">
                <td class="py-3.5 px-4 text-center">-</td>
                <td class="py-3.5 px-4 uppercase tracking-wider">TOTAL KABUPATEN DOMPU</td>
                <td class="py-3.5 px-4 text-center font-mono font-bold text-amber-300">5205</td>
                <td class="py-3.5 px-4 text-center text-amber-300 font-bold">
                  {{ summary?.total_kelurahans || 9 }} Kelurahan, {{ summary?.total_desas || 72 }} Desa
                </td>
                <td class="py-3.5 px-4 text-right font-mono font-black text-amber-300">
                  {{ formatDecimal(summary?.total_area || 2324.55) }} km²
                </td>
                <td class="py-3.5 px-4 pl-6 font-mono text-amber-300 font-black">
                  100,00%
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- 📌 Perincian rincian Kecamatan Accordion -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-extrabold text-sm text-gray-900 dark:text-zinc-50">
            📌 Perincian Wilayah Desa & Kelurahan di 8 Kecamatan
          </h3>
          <span class="text-xs text-gray-400 font-medium">Klik pada kecamatan untuk melihat daftar desa/kelurahan</span>
        </div>

        <div 
          v-for="kec in kecamatans" 
          :key="kec.id"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden transition-all duration-200"
        >
          <!-- Header Kecamatan Row -->
          <div 
            class="flex items-center justify-between px-6 py-4 cursor-pointer hover:bg-gray-50/80 dark:hover:bg-zinc-800/60 transition"
            @click="toggleKecamatan(kec.id)"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-primary-50 dark:bg-primary-950/60 border border-primary-100 dark:border-primary-900/40 rounded-2xl flex items-center justify-center shrink-0">
                <MapPin class="w-5 h-5 text-primary-600 dark:text-primary-400" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h4 class="font-black text-gray-900 dark:text-zinc-100 text-sm tracking-tight">Kecamatan {{ kec.name }}</h4>
                  <span class="px-2 py-0.5 text-[9px] font-mono font-bold bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 rounded-md">
                    Kode {{ kec.code }}
                  </span>
                </div>
                <p class="text-[11px] text-gray-400 font-medium mt-0.5 flex items-center gap-2">
                  <span>Luas: <strong class="text-gray-700 dark:text-zinc-300 font-mono">{{ formatDecimal(kec.area_km2) }} km²</strong> ({{ formatDecimal(kec.percentage) }}%)</span>
                  <span>•</span>
                  <span>{{ kec.notes || (kec.desa_count + ' Desa/Kel') }}</span>
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button 
                @click.stop="openDesaModal(kec)" 
                class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40 rounded-xl transition text-xs font-bold flex items-center gap-1 cursor-pointer"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>Tambah Desa</span>
              </button>
              <button @click.stop="openKecamatanModal(kec)" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition cursor-pointer" title="Edit Kecamatan"><SquarePen class="w-4 h-4" /></button>
              <button @click.stop="confirmDeleteKecamatan(kec)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition cursor-pointer" title="Hapus Kecamatan"><Trash2 class="w-4 h-4" /></button>
              <div class="w-8 h-8 rounded-xl bg-gray-50 dark:bg-zinc-800 flex items-center justify-center">
                <ChevronDown class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180 text-primary-600': expandedKecamatan.includes(kec.id) }" />
              </div>
            </div>
          </div>

          <!-- Detail Desa Table (Collapsible) -->
          <div v-if="expandedKecamatan.includes(kec.id)" class="border-t border-gray-100 dark:border-zinc-800 bg-gray-50/40 dark:bg-zinc-900/40 p-4">
            <!-- Loading state -->
            <div v-if="loadingDesas[kec.id]" class="px-6 py-6 text-xs text-gray-400 flex items-center gap-2">
              <div class="w-4 h-4 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
              <span>Memuat data desa & kelurahan...</span>
            </div>

            <template v-else>
              <div v-if="!desasByKecamatan[kec.id] || desasByKecamatan[kec.id].length === 0" class="px-6 py-8 text-center text-xs text-gray-400 italic bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800">
                Belum ada desa/kelurahan terdaftar. Klik "+ Tambah Desa" untuk memasukkan data desa.
              </div>
              
              <div v-else class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <table class="w-full text-xs">
                  <thead>
                    <tr class="bg-gray-50/80 dark:bg-zinc-800/50 border-b border-gray-100 dark:border-zinc-800 text-[10px] uppercase font-black text-gray-400 tracking-wider">
                      <th class="py-2.5 px-4 text-center w-10">No</th>
                      <th class="py-2.5 px-4 text-left">Nama Desa / Kelurahan</th>
                      <th class="py-2.5 px-4 text-center">Kode BPS</th>
                      <th class="py-2.5 px-4 text-center">Tipe Admin</th>
                      <th class="py-2.5 px-4 text-right">Luas Wilayah (km²)</th>
                      <th class="py-2.5 px-4 text-right">Kontribusi kec (%)</th>
                      <th class="py-2.5 px-4 text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60 font-medium">
                    <tr 
                      v-for="(desa, dIdx) in desasByKecamatan[kec.id]" 
                      :key="desa.id" 
                      class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/40 transition-colors"
                    >
                      <td class="py-3 px-4 text-center font-bold text-gray-400">{{ dIdx + 1 }}</td>
                      <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                          <span class="font-extrabold text-gray-900 dark:text-zinc-100 text-xs">
                            {{ desa.type === 'kelurahan' ? 'Kel.' : 'Desa' }} {{ desa.name }}
                          </span>
                          <span v-if="desa.name === 'Sori Tatanga'" class="px-2 py-0.5 text-[8px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 rounded font-mono">
                            Desa Terluas (457,45 km²)
                          </span>
                        </div>
                      </td>
                      <td class="py-3 px-4 text-center font-mono font-bold text-gray-400">{{ desa.code || '-' }}</td>
                      <td class="py-3 px-4 text-center">
                        <span 
                          class="px-2 py-0.5 text-[9px] font-black uppercase rounded-lg font-mono border"
                          :class="desa.type === 'kelurahan' 
                            ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200 dark:border-purple-900/40' 
                            : 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200 dark:border-sky-900/40'"
                        >
                          {{ desa.type }}
                        </span>
                      </td>
                      <td class="py-3 px-4 text-right font-black font-mono text-gray-800 dark:text-zinc-200">
                        {{ formatDecimal(desa.area_km2) }} km²
                      </td>
                      <td class="py-3 px-4 text-right font-mono font-bold text-primary-600 dark:text-primary-400">
                        {{ formatDecimal(kec.area_km2 > 0 ? (desa.area_km2 / kec.area_km2) * 100 : 0) }}%
                      </td>
                      <td class="py-3 px-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                          <button @click="openDesaModal(kec, desa)" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition" title="Edit Desa"><SquarePen class="w-3.5 h-3.5" /></button>
                          <button @click="confirmDeleteDesa(desa)" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Hapus Desa"><Trash2 class="w-3.5 h-3.5" /></button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Kecamatan Form Modal -->
    <div v-if="kecamatanModal.open" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4" @click.self="kecamatanModal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
          <h3 class="font-black text-base text-gray-900 dark:text-zinc-50">
            {{ kecamatanModal.editing ? 'Edit Kecamatan' : 'Tambah Kecamatan Baru' }}
          </h3>
          <button @click="kecamatanModal.open = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
        </div>
        
        <form @submit.prevent="submitKecamatan" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Nama Kecamatan *</label>
              <input v-model="kecamatanForm.name" required type="text" placeholder="mis. Pekat, Woja, Dompu..." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Kode Wilayah BPS</label>
              <input v-model="kecamatanForm.code" type="text" placeholder="mis. 520501" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Ibukota Kecamatan</label>
              <input v-model="kecamatanForm.ibukota" type="text" placeholder="Ibukota..." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Luas Wilayah (km²) *</label>
              <input v-model="kecamatanForm.area_km2" type="number" step="0.01" placeholder="mis. 788.91" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Urutan Tampil</label>
              <input v-model="kecamatanForm.sort_order" type="number" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-zinc-800">
            <button type="button" @click="kecamatanModal.open = false" class="px-4 py-2 text-xs font-bold border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 transition cursor-pointer">Batal</button>
            <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl shadow-md transition cursor-pointer">Simpan Kecamatan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Desa Form Modal -->
    <div v-if="desaModal.open" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4" @click.self="desaModal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
          <h3 class="font-black text-base text-gray-900 dark:text-zinc-50">
            {{ desaModal.editing ? 'Edit Desa/Kelurahan' : `Tambah Desa — ${desaModal.kecamatan?.name}` }}
          </h3>
          <button @click="desaModal.open = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
        </div>

        <form @submit.prevent="submitDesa" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Nama Desa / Kelurahan *</label>
              <input v-model="desaForm.name" required type="text" placeholder="mis. Sori Tatanga, Kandai I..." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Kode BPS</label>
              <input v-model="desaForm.code" type="text" placeholder="mis. 5205011001" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Tipe Wilayah *</label>
              <select v-model="desaForm.type" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <option value="desa">Desa</option>
                <option value="kelurahan">Kelurahan</option>
              </select>
            </div>
            <div class="col-span-2">
              <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Luas Wilayah (km²)</label>
              <input v-model="desaForm.area_km2" type="number" step="0.01" placeholder="mis. 457.45" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-zinc-800">
            <button type="button" @click="desaModal.open = false" class="px-4 py-2 text-xs font-bold border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 transition cursor-pointer">Batal</button>
            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl shadow-md transition cursor-pointer">Simpan Desa</button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, MapPin, SquarePen, Trash2, ChevronDown, CheckCircle2, Building2, Layers, Landmark } from '@lucide/vue';
import axios from 'axios';

const props = defineProps({ 
  kecamatans: Array,
  summary: Object,
});

const expandedKecamatan = ref([]);
const desasByKecamatan  = ref({});
const loadingDesas      = ref({});

const sortedKecamatans = computed(() => {
  return [...(props.kecamatans || [])].sort((a, b) => (b.area_km2 || 0) - (a.area_km2 || 0));
});

const toggleKecamatan = async (id) => {
  if (expandedKecamatan.value.includes(id)) {
    expandedKecamatan.value = expandedKecamatan.value.filter(i => i !== id);
  } else {
    expandedKecamatan.value.push(id);
    if (!desasByKecamatan.value[id]) {
      await fetchDesas(id);
    }
  }
};

const fetchDesas = async (kecamatanId) => {
  loadingDesas.value[kecamatanId] = true;
  try {
    const res = await axios.get(route('admin.demographics.desas', kecamatanId));
    desasByKecamatan.value[kecamatanId] = Array.isArray(res.data) ? res.data : (res.data.desas || []);
  } catch (e) {
    desasByKecamatan.value[kecamatanId] = [];
  } finally {
    loadingDesas.value[kecamatanId] = false;
  }
};

// ── Kecamatan Modal ─────────────────────────────
const kecamatanModal = reactive({ open: false, editing: null });
const kecamatanForm  = reactive({
  name: '', code: '', ibukota: '', area_km2: '', sort_order: 0,
});

const openKecamatanModal = (kec) => {
  kecamatanModal.editing = kec;
  if (kec) {
    Object.assign(kecamatanForm, { 
      name: kec.name, 
      code: kec.code || '', 
      ibukota: kec.ibukota || '', 
      area_km2: kec.area_km2 || '', 
      sort_order: kec.sort_order || 0 
    });
  } else {
    Object.assign(kecamatanForm, { 
      name: '', code: '', ibukota: '', area_km2: '', sort_order: 0 
    });
  }
  kecamatanModal.open = true;
};

const submitKecamatan = () => {
  const payload = { ...kecamatanForm };
  if (kecamatanModal.editing) {
    router.put(route('admin.demographics.kecamatan.update', kecamatanModal.editing.id), payload, { onSuccess: () => { kecamatanModal.open = false; } });
  } else {
    router.post(route('admin.demographics.kecamatan.store'), payload, { onSuccess: () => { kecamatanModal.open = false; } });
  }
};

const confirmDeleteKecamatan = (kec) => {
  if (confirm(`Hapus Kecamatan "${kec.name}"? Semua data desa terkait akan ikut dihapus.`)) {
    router.delete(route('admin.demographics.kecamatan.destroy', kec.id));
  }
};

// ── Desa Modal ───────────────────────────────────
const desaModal = reactive({ open: false, editing: null, kecamatan: null });
const desaForm  = reactive({
  kecamatan_id: '', name: '', code: '', type: 'desa', area_km2: '', sort_order: 0,
});

const openDesaModal = (kec, desa = null) => {
  desaModal.kecamatan = kec;
  desaModal.editing   = desa;
  if (desa) {
    Object.assign(desaForm, { 
      kecamatan_id: kec.id, 
      name: desa.name, 
      code: desa.code || '', 
      type: desa.type, 
      area_km2: desa.area_km2 || '',
      sort_order: desa.sort_order || 0 
    });
  } else {
    Object.assign(desaForm, { 
      kecamatan_id: kec.id, 
      name: '', code: '', type: 'desa', area_km2: '', sort_order: 0 
    });
  }
  desaModal.open = true;
};

const submitDesa = () => {
  const payload = { ...desaForm };
  if (desaModal.editing) {
    router.put(route('admin.demographics.desa.update', desaModal.editing.id), payload, {
      onSuccess: () => {
        desaModal.open = false;
        delete desasByKecamatan.value[desaModal.kecamatan.id];
        fetchDesas(desaModal.kecamatan.id);
      }
    });
  } else {
    router.post(route('admin.demographics.desa.store'), payload, {
      onSuccess: () => {
        desaModal.open = false;
        delete desasByKecamatan.value[desaForm.kecamatan_id];
        fetchDesas(desaForm.kecamatan_id);
      }
    });
  }
};

const confirmDeleteDesa = (desa) => {
  if (confirm(`Hapus "${desa.name}"?`)) {
    router.delete(route('admin.demographics.desa.destroy', desa.id), {
      onSuccess: () => {
        const kId = desa.kecamatan_id;
        delete desasByKecamatan.value[kId];
        fetchDesas(kId);
      }
    });
  }
};

const formatDecimal = (num) => {
  if (num === null || num === undefined || isNaN(num)) return '0,00';
  return Number(num).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>
