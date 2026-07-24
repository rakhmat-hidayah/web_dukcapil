<template>
  <Head title="Dataset Kependudukan" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Dataset Statistik Kependudukan</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola dataset kependudukan dengan Smart Dataset Editor berbasis Spreadsheet & Excel.</p>
        </div>
        <button @click="openModal(null)" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5 shadow-md shadow-primary-500/20">
          <Plus class="w-3.5 h-3.5" /> Upload Dataset Baru
        </button>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold">
        ✓ {{ $page.props.flash.success }}
      </div>

      <!-- Filter Bar -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
          <!-- Filter: Wilayah -->
          <div class="flex flex-col gap-1">
            <label class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Level Wilayah</label>
            <select v-model="filterRegionLevel" @change="applyFilter"
              class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none min-w-[160px]">
              <option value="">Semua Level</option>
              <option value="regency">Kabupaten</option>
              <option value="district">Kecamatan</option>
              <option value="village">Desa / Kelurahan</option>
            </select>
          </div>

          <!-- Filter: Kode Wilayah -->
          <div class="flex flex-col gap-1" v-if="filterRegionLevel">
            <label class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Pilih Wilayah</label>
            <select v-model="filterRegionCode" @change="applyFilter"
              class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none min-w-[160px]">
              <option value="">Semua Wilayah</option>
              <template v-if="filterRegionLevel === 'regency'">
                <option :value="props.regencyCode || '5205'">5205 — Kabupaten Dompu</option>
              </template>
              <template v-else-if="filterRegionLevel === 'district'">
                <option v-for="kec in kecamatans" :key="kec.code" :value="kec.code">{{ kec.name }}</option>
              </template>
              <template v-else-if="filterRegionLevel === 'village'">
                <option v-for="d in desas" :key="d.code" :value="d.code">{{ d.name }}</option>
              </template>
            </select>
          </div>

          <!-- Filter: Tahun -->
          <div class="flex flex-col gap-1">
            <label class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Tahun</label>
            <select v-model="filterYear" @change="applyFilter"
              class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
              <option value="">Semua Tahun</option>
              <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>

          <!-- Filter: Semester -->
          <div class="flex flex-col gap-1">
            <label class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Semester</label>
            <select v-model="filterSemester" @change="applyFilter"
              class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
              <option value="">Semua Semester</option>
              <option :value="1">Semester 1 (Juni)</option>
              <option :value="2">Semester 2 (Desember)</option>
            </select>
          </div>

          <!-- Filter: Tipe Data -->
          <div class="flex flex-col gap-1">
            <label class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Tipe Data</label>
            <select v-model="filterType" @change="applyFilter"
              class="px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
              <option value="">Semua Tipe</option>
              <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <!-- Reset Filter -->
          <button v-if="filterYear || filterSemester || filterType || filterRegionLevel || filterRegionCode"
            @click="resetFilter"
            class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-800 dark:text-zinc-400 dark:hover:text-zinc-100 bg-gray-100 dark:bg-zinc-800 rounded-xl transition">
            ✕ Reset
          </button>

          <!-- Result count -->
          <span class="text-[10px] text-gray-400 ml-auto self-end">
            Menampilkan {{ datasets.total }} dataset
          </span>
        </div>
      </div>

      <!-- Dataset table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800">
                <th class="text-left px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Judul Dataset</th>
                <th class="text-left px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Tahun</th>
                <th class="text-left px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Tipe</th>
                <th class="text-left px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Wilayah</th>
                <th class="text-left px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Ringkasan Data</th>
                <th class="text-left px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Status</th>
                <th class="text-left px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">File</th>
                <th class="px-3 py-2 sm:px-5 sm:py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
              <tr v-if="datasets.data.length === 0">
                <td colspan="8" class="px-5 py-8 text-center text-gray-400 italic">Belum ada dataset diunggah.</td>
              </tr>
              <tr v-for="ds in datasets.data" :key="ds.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition align-top">
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 font-bold text-gray-700 dark:text-zinc-200 max-w-[180px] sm:max-w-none truncate sm:whitespace-normal">{{ ds.title }}</td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 text-gray-500 font-mono font-semibold whitespace-nowrap">
                  {{ ds.year }}
                  <span class="ml-1 px-1.5 py-0.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-300 text-[8px] font-bold rounded">
                    Sem {{ ds.semester || 1 }}
                  </span>
                </td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 whitespace-nowrap">
                  <span class="inline-block px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 text-[8px] font-bold uppercase rounded font-mono whitespace-nowrap">
                    {{ typeLabels[ds.type] || ds.type }}
                  </span>
                </td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 text-gray-400 whitespace-nowrap">
                  <span v-if="ds.region_level === 'regency'">Kab. Dompu</span>
                  <span v-else-if="ds.region_level === 'district'">{{ kecamatans.find(k => k.code === ds.region_code)?.name || ds.region_code }}</span>
                  <span v-else-if="ds.region_level === 'village'">{{ desas.find(d => d.code === ds.region_code)?.name || ds.region_code }}</span>
                  <span v-else>—</span>
                </td>

                <!-- Ringkasan Data -->
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 whitespace-nowrap">
                  <div v-if="ds.data_json" class="space-y-0.5">
                    <template v-if="ds.data_json.items && Array.isArray(ds.data_json.items)">
                      <div v-for="item in ds.data_json.items.slice(0, 3)" :key="item.name"
                        class="flex items-center gap-1.5 text-[10px]">
                        <span class="text-gray-500 dark:text-zinc-400 min-w-0 truncate max-w-[120px]">{{ item.name }}</span>
                        <span class="font-bold text-gray-800 dark:text-zinc-100 font-mono ml-auto whitespace-nowrap">{{ item.value?.toLocaleString('id-ID') }}</span>
                      </div>
                      <span v-if="ds.data_json.items.length > 3" class="text-[9px] text-gray-400 font-italic">+{{ ds.data_json.items.length - 3 }} item lagi</span>
                    </template>
                    <template v-else-if="ds.data_json.categories && Array.isArray(ds.data_json.categories) && ds.data_json.values">
                      <div v-for="(cat, i) in ds.data_json.categories.slice(0, 3)" :key="cat"
                        class="flex items-center gap-1.5 text-[10px]">
                        <span class="text-gray-500 dark:text-zinc-400 min-w-0 truncate max-w-[120px]">{{ cat }}</span>
                        <span class="font-bold text-gray-800 dark:text-zinc-100 font-mono ml-auto whitespace-nowrap">{{ ds.data_json.values[i]?.toLocaleString('id-ID') }}</span>
                      </div>
                      <span v-if="ds.data_json.categories.length > 3" class="text-[9px] text-gray-400 font-italic">+{{ ds.data_json.categories.length - 3 }} kategori lagi</span>
                    </template>
                    <template v-else-if="ds.data_json.categories && ds.data_json.male">
                      <div class="text-[10px] text-gray-500 font-bold">Total: {{ ds.data_json.total?.toLocaleString('id-ID') }} jiwa</div>
                    </template>
                    <span v-else class="text-[10px] text-gray-400 italic">—</span>
                  </div>
                  <span v-else class="text-[10px] text-gray-400 italic">Belum ada data</span>
                </td>

                <td class="px-3 py-2.5 sm:px-5 sm:py-3 whitespace-nowrap">
                  <span class="px-2 py-0.5 text-[8px] font-bold uppercase rounded font-mono"
                    :class="ds.status === 'published' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-zinc-700 dark:text-zinc-400'">
                    {{ ds.status === 'published' ? 'Dipublikasikan' : 'Draft' }}
                  </span>
                </td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 text-gray-400 font-mono uppercase whitespace-nowrap">{{ ds.file_type || '—' }}</td>
                <td class="px-3 py-2.5 sm:px-5 sm:py-3 whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1">
                    <button @click="openModal(ds)" class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><SquarePen class="w-3.5 h-3.5" /></button>
                    <button @click="confirmDelete(ds)" class="p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><Trash2 class="w-3.5 h-3.5" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div v-if="datasets.links?.length > 3" class="px-5 py-3 border-t border-gray-50 dark:border-zinc-800 flex gap-1">
          <Link v-for="(link, i) in datasets.links" :key="i" :href="link.url || '#'" v-html="link.label"
            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition"
            :class="[link.active ? 'bg-primary-600 text-white border-primary-500' : 'border-gray-200 dark:border-zinc-700 hover:bg-gray-50 text-gray-500', !link.url ? 'opacity-40 pointer-events-none' : '']" />
        </div>
      </div>
    </div>

    <!-- ENTERPRISE SMART DATASET EDITOR MODAL -->
    <div v-if="modal.open" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-2 sm:p-4" @click.self="modal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 sm:p-6 w-full max-w-4xl shadow-2xl space-y-4 max-h-[94vh] flex flex-col">
        
        <!-- Modal Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-zinc-800 pb-3">
          <div>
            <h3 class="font-black text-base text-gray-900 dark:text-zinc-50 flex items-center gap-2">
              <span>📊</span> {{ modal.editing ? 'Edit Dataset' : 'Smart Dataset Editor' }}
            </h3>
            <p class="text-[10px] text-gray-400">Input spreadsheet interaktif, Paste Excel, atau Upload File tanpa pengetikan manual.</p>
          </div>

          <!-- 4 Data Entry Modes -->
          <div class="flex items-center gap-1 bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl">
            <button type="button" @click="setEditorMode('spreadsheet')"
              class="px-3 py-1.5 text-[10px] font-bold rounded-lg transition flex items-center gap-1"
              :class="editorMode === 'spreadsheet' ? 'bg-white dark:bg-zinc-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-800'">
              📊 Spreadsheet Editor
            </button>
            <button type="button" @click="setEditorMode('smart_paste')"
              class="px-3 py-1.5 text-[10px] font-bold rounded-lg transition flex items-center gap-1"
              :class="editorMode === 'smart_paste' ? 'bg-white dark:bg-zinc-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-800'">
              📋 Paste Excel
            </button>
            <button type="button" @click="setEditorMode('excel_upload')"
              class="px-3 py-1.5 text-[10px] font-bold rounded-lg transition flex items-center gap-1"
              :class="editorMode === 'excel_upload' ? 'bg-white dark:bg-zinc-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-800'">
              📁 Upload Excel
            </button>
            <button type="button" @click="setEditorMode('json')"
              class="px-3 py-1.5 text-[10px] font-bold rounded-lg transition flex items-center gap-1"
              :class="editorMode === 'json' ? 'bg-white dark:bg-zinc-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              💻 Developer Mode (JSON)
            </button>
          </div>
        </div>

        <!-- SUMMARY & LIVE VALIDATION PANEL -->
        <div class="bg-slate-900 text-white rounded-2xl p-3 grid grid-cols-2 sm:grid-cols-6 gap-2 text-center text-xs shadow-inner">
          <div class="p-1.5 bg-slate-800/80 rounded-xl">
            <span class="text-[8px] uppercase tracking-wider text-slate-400 block font-mono">Total Baris</span>
            <span class="font-black text-sm text-sky-400 font-mono">{{ gridRows.length }}</span>
          </div>
          <div class="p-1.5 bg-slate-800/80 rounded-xl">
            <span class="text-[8px] uppercase tracking-wider text-slate-400 block font-mono">Total Populasi</span>
            <span class="font-black text-sm text-emerald-400 font-mono">{{ calculateTotalPopulation().toLocaleString('id-ID') }}</span>
          </div>
          <div class="p-1.5 bg-slate-800/80 rounded-xl">
            <span class="text-[8px] uppercase tracking-wider text-slate-400 block font-mono">Periode Data</span>
            <span class="font-bold text-xs text-amber-300 font-mono">{{ form.year }} Sem {{ form.semester }}</span>
          </div>
          <div class="p-1.5 bg-slate-800/80 rounded-xl">
            <span class="text-[8px] uppercase tracking-wider text-slate-400 block font-mono">Wilayah</span>
            <span class="font-bold text-xs text-indigo-300 truncate block">{{ getRegionLabel() }}</span>
          </div>
          <div class="p-1.5 bg-slate-800/80 rounded-xl">
            <span class="text-[8px] uppercase tracking-wider text-slate-400 block font-mono">Tipe Dataset</span>
            <span class="font-bold text-xs text-purple-300 truncate block">{{ typeLabels[form.type] || form.type }}</span>
          </div>
          <div class="p-1.5 bg-slate-800/80 rounded-xl">
            <span class="text-[8px] uppercase tracking-wider text-slate-400 block font-mono">Status Validasi</span>
            <span v-if="validationErrors.length === 0" class="font-black text-xs text-emerald-400">🟢 VALID</span>
            <span v-else class="font-black text-xs text-rose-400">🔴 {{ validationErrors.length }} ERROR</span>
          </div>
        </div>

        <!-- Validation Warning Alerts -->
        <div v-if="validationErrors.length > 0" class="p-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-xs text-rose-700 dark:text-rose-300 space-y-0.5 max-h-20 overflow-y-auto">
          <div v-for="(err, i) in validationErrors" :key="i" class="flex items-center gap-1 text-[11px] font-semibold">
            <span>⚠️</span> <span>{{ err }}</span>
          </div>
        </div>

        <!-- MODES CONTENT BODY -->
        <div class="flex-1 overflow-y-auto space-y-4 pr-1">

          <!-- Shared Top Form Fields -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-gray-50/70 dark:bg-zinc-800/50 p-3 rounded-2xl border border-gray-100 dark:border-zinc-800">
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Judul Dataset *</label>
              <input v-model="form.title" required type="text" placeholder="Judul Dataset..." class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none" />
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tahun *</label>
                <input v-model="form.year" type="number" required min="2000" max="2100" class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none" />
              </div>
              <div>
                <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Semester *</label>
                <select v-model="form.semester" required class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none">
                  <option :value="1">Sem 1 (Juni)</option>
                  <option :value="2">Sem 2 (Desember)</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tipe Dataset *</label>
              <select v-model="selectedTypeChoice" @change="onTypeChoiceChange" required class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none">
                <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
                <option value="custom">➕ Tipe Baru (Kustom)...</option>
              </select>
            </div>

            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Level Wilayah *</label>
              <select v-model="form.region_level" @change="onRegionLevelChange" class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none">
                <option value="regency">Kabupaten Dompu (Keseluruhan)</option>
                <option value="district">Kecamatan</option>
                <option value="village">Desa / Kelurahan</option>
              </select>
            </div>

            <div v-if="form.region_level === 'district'">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Pilih Kecamatan *</label>
              <select v-model="form.region_code" class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none">
                <option v-for="kec in kecamatans" :key="kec.code" :value="kec.code">{{ kec.name }} ({{ kec.code }})</option>
              </select>
            </div>
            <div v-else-if="form.region_level === 'village'">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Pilih Desa/Kelurahan *</label>
              <select v-model="form.region_code" class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none">
                <option v-for="d in desas" :key="d.code" :value="d.code">{{ d.name }} ({{ d.code }})</option>
              </select>
            </div>

            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Status Publikasi *</label>
              <select v-model="form.status" required class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:outline-none">
                <option value="published">Dipublikasikan</option>
                <option value="draft">Draft</option>
              </select>
            </div>
          </div>

          <!-- MODE 1: SPREADSHEET EDITOR (DEFAULT) -->
          <div v-if="editorMode === 'spreadsheet'" class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-100 dark:bg-zinc-800/80 p-2 rounded-2xl border border-gray-200 dark:border-zinc-700">
              <div class="flex items-center gap-1.5">
                <button type="button" @click="addGridRow" class="px-2.5 py-1 bg-white dark:bg-zinc-700 text-gray-700 dark:text-zinc-200 rounded-lg text-xs font-bold shadow-xs hover:bg-gray-50 transition">+ Tambah Baris</button>
                <button type="button" @click="loadMasterTemplate" class="px-2.5 py-1 bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 rounded-lg text-xs font-bold hover:bg-primary-200 transition">📋 Template {{ typeLabels[form.type] || form.type }}</button>
                <button type="button" @click="undoGrid" :disabled="historyIndex <= 0" class="px-2 py-1 bg-white dark:bg-zinc-700 text-gray-600 dark:text-zinc-300 rounded-lg text-xs font-bold disabled:opacity-40">↩️ Undo</button>
                <button type="button" @click="redoGrid" :disabled="historyIndex >= historyStack.length - 1" class="px-2 py-1 bg-white dark:bg-zinc-700 text-gray-600 dark:text-zinc-300 rounded-lg text-xs font-bold disabled:opacity-40">↪️ Redo</button>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] text-gray-400 font-mono">💡 Tab / Enter / Panah untuk navigasi sel spreadsheet</span>
                <button type="button" @click="clearGrid" class="px-2 py-1 bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300 rounded-lg text-xs font-bold hover:bg-rose-100 transition">🗑️ Clear</button>
              </div>
            </div>

            <!-- Spreadsheet Grid Table -->
            <div class="border border-gray-200 dark:border-zinc-700 rounded-2xl overflow-hidden shadow-xs">
              <div class="max-h-[320px] overflow-y-auto">
                <table class="w-full text-xs border-collapse">
                  <thead class="bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 sticky top-0 font-bold uppercase tracking-wider text-[9px] z-10">
                    <tr>
                      <th class="w-10 px-2 py-2 text-center border-b border-r border-gray-200 dark:border-zinc-700">#</th>
                      <th class="px-3 py-2 text-left border-b border-r border-gray-200 dark:border-zinc-700">Kategori / Label Nama</th>
                      
                      <!-- Col 2 / 3 depends on type (Pyramid vs Standard) -->
                      <template v-if="form.type === 'population'">
                        <th class="px-3 py-2 text-left border-b border-r border-gray-200 dark:border-zinc-700 w-36">Laki-Laki (Jiwa)</th>
                        <th class="px-3 py-2 text-left border-b border-r border-gray-200 dark:border-zinc-700 w-36">Perempuan (Jiwa)</th>
                        <th class="px-3 py-2 text-left border-b border-r border-gray-200 dark:border-zinc-700 w-36">Subtotal (Jiwa)</th>
                      </template>
                      <template v-else>
                        <th class="px-3 py-2 text-left border-b border-r border-gray-200 dark:border-zinc-700 w-44">Jumlah (Jiwa)</th>
                        <th class="px-3 py-2 text-left border-b border-r border-gray-200 dark:border-zinc-700 w-44">Format Tampilan</th>
                      </template>
                      <th class="w-16 px-2 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 font-mono">
                    <tr v-for="(row, idx) in gridRows" :key="idx" class="hover:bg-primary-50/30 dark:hover:bg-primary-950/20 transition">
                      <!-- Row # -->
                      <td class="px-2 py-1 text-center font-bold text-gray-400 bg-gray-50/50 dark:bg-zinc-800/30 border-r border-gray-200 dark:border-zinc-800">{{ idx + 1 }}</td>
                      
                      <!-- Label Cell -->
                      <td class="p-0 border-r border-gray-200 dark:border-zinc-800">
                        <input 
                          v-model="row.label"
                          @keydown="handleCellKeydown($event, idx, 'label')"
                          @input="saveGridHistory"
                          type="text" 
                          placeholder="Label nama..."
                          class="w-full px-3 py-1.5 bg-transparent font-sans font-medium text-xs focus:bg-white dark:focus:bg-zinc-800 focus:outline-none"
                        />
                      </td>

                      <!-- Value Cells -->
                      <template v-if="form.type === 'population'">
                        <td class="p-0 border-r border-gray-200 dark:border-zinc-800">
                          <input 
                            v-model.number="row.male"
                            @keydown="handleCellKeydown($event, idx, 'male')"
                            @input="saveGridHistory"
                            type="number" min="0"
                            class="w-full px-3 py-1.5 bg-transparent text-xs font-bold text-blue-600 dark:text-blue-400 focus:bg-white dark:focus:bg-zinc-800 focus:outline-none"
                          />
                        </td>
                        <td class="p-0 border-r border-gray-200 dark:border-zinc-800">
                          <input 
                            v-model.number="row.female"
                            @keydown="handleCellKeydown($event, idx, 'female')"
                            @input="saveGridHistory"
                            type="number" min="0"
                            class="w-full px-3 py-1.5 bg-transparent text-xs font-bold text-pink-600 dark:text-pink-400 focus:bg-white dark:focus:bg-zinc-800 focus:outline-none"
                          />
                        </td>
                        <td class="px-3 py-1.5 font-bold text-gray-700 dark:text-zinc-200 border-r border-gray-200 dark:border-zinc-800">
                          {{ ((Number(row.male)||0) + (Number(row.female)||0)).toLocaleString('id-ID') }}
                        </td>
                      </template>

                      <template v-else>
                        <td class="p-0 border-r border-gray-200 dark:border-zinc-800">
                          <input 
                            v-model.number="row.value"
                            @keydown="handleCellKeydown($event, idx, 'value')"
                            @input="saveGridHistory"
                            type="number" min="0"
                            class="w-full px-3 py-1.5 bg-transparent text-xs font-bold text-emerald-600 dark:text-emerald-400 focus:bg-white dark:focus:bg-zinc-800 focus:outline-none"
                          />
                        </td>
                        <td class="px-3 py-1.5 font-bold text-gray-500 dark:text-zinc-400 border-r border-gray-200 dark:border-zinc-800">
                          {{ (Number(row.value)||0).toLocaleString('id-ID') }} jiwa
                        </td>
                      </template>

                      <!-- Action Buttons -->
                      <td class="px-2 py-1 text-center">
                        <div class="flex items-center justify-center gap-1">
                          <button type="button" @click="duplicateGridRow(idx)" title="Duplikat" class="text-blue-500 hover:text-blue-700 p-0.5">📄</button>
                          <button type="button" @click="removeGridRow(idx)" title="Hapus" class="text-rose-400 hover:text-rose-600 p-0.5">✕</button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- MODE 2: PASTE FROM EXCEL (SMART PASTE) -->
          <div v-else-if="editorMode === 'smart_paste'" class="space-y-3">
            <div class="p-4 bg-sky-50/70 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800 rounded-2xl space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-xs font-black uppercase tracking-wider text-sky-700 dark:text-sky-300">
                  📋 Paste Langsung dari Excel / Google Sheets
                </label>
                <span class="text-[10px] text-sky-600 dark:text-sky-400">Salin kolom Kategori & Jumlah dari Excel, lalu tempel di bawah.</span>
              </div>
              <div v-if="importFeedback" class="p-2 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-xl text-xs font-bold flex items-center gap-2">
                <span>{{ importFeedback }}</span>
              </div>
              <textarea 
                v-model="pasteRawInput"
                rows="8"
                :placeholder="getSmartPastePlaceholder()"
                class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-sky-200 dark:border-sky-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none"
              ></textarea>
              <div class="flex justify-between items-center">
                <span class="text-[10px] text-gray-400">Mendukung Tab-separated values (TSV), CSV, dan spasi ganda dari Clipboard Excel.</span>
                <button type="button" @click="importPastedData" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold transition shadow-md">
                  ✨ Impor Ke Spreadsheet Editor &rarr;
                </button>
              </div>
            </div>
          </div>

          <!-- MODE 3: UPLOAD SIMPLE EXCEL (.XLSX / .XLS / .CSV) -->
          <div v-else-if="editorMode === 'excel_upload'" class="space-y-3">
            <div class="p-6 bg-emerald-50/70 dark:bg-emerald-950/30 border-2 border-dashed border-emerald-300 dark:border-emerald-700 rounded-2xl text-center space-y-3">
              <div class="text-4xl">📁</div>
              <div>
                <h4 class="text-sm font-black text-emerald-800 dark:text-emerald-200">Unggah File Excel (.xlsx, .xls, .csv)</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto mt-1">
                  Sistem akan membaca Kolom A sebagai Label dan Kolom B sebagai Jumlah secara otomatis tanpa proses mapping.
                </p>
              </div>
              <input 
                type="file" 
                accept=".xlsx, .xls, .csv" 
                @change="handleExcelFileUpload" 
                class="text-xs file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 transition cursor-pointer"
              />
            </div>
          </div>

          <!-- MODE 4: DEVELOPER MODE (JSON MANUAL) -->
          <div v-else class="space-y-2">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
              Developer Mode — Data JSON Manual Format Schema
            </label>
            <textarea 
              v-model="form.data_json" 
              rows="10" 
              placeholder='{"items": [{"name": "Islam", "value": 270100}]}'
              class="w-full px-3 py-2 bg-slate-950 text-emerald-400 border border-slate-800 rounded-2xl text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:outline-none"
            ></textarea>
            <p class="text-[9px] text-gray-400">Hanya gunakan jika Anda memahami struktur JSON schema ECharts.</p>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Catatan Administrator</label>
            <textarea v-model="form.notes" rows="2" placeholder="Catatan tambahan (sumber data, rujukan SK BPS, dll)..." class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
          </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-zinc-800">
          <span class="text-[10px] text-gray-400 font-mono">⚡ Smart Save Engine Active — Dual Compatibility Ready</span>
          <div class="flex gap-2 w-full sm:w-auto justify-end">
            <button type="button" @click="modal.open = false" class="px-4 py-2 text-xs font-bold border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 transition">Batal</button>
            <button type="button" @click="submitDataset" :disabled="submitting || validationErrors.length > 0" class="px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition shadow-lg shadow-primary-500/20 disabled:opacity-40 flex items-center gap-1.5">
              <span>💾</span> {{ submitting ? 'Menyimpan...' : 'Simpan Dataset' }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, SquarePen, Trash2 } from '@lucide/vue';
import * as XLSX from 'xlsx';

const props = defineProps({
  datasets: Object,
  kecamatans: Array,   // [{id, name, code}]
  desas: Array,        // [{id, kecamatan_id, name, code}]
  typeLabels: Object,
  regionLevels: Object, // {regency: 'Kabupaten', district: 'Kecamatan', village: 'Desa / Kelurahan'}
  regencyCode: String,
  availableYears: Array,
  availableSemesters: Array,
  filters: Object,
});

// Filter state
const filterRegionLevel = ref(props.filters?.region_level ?? '');
const filterRegionCode  = ref(props.filters?.region_code ?? '');
const filterYear        = ref(props.filters?.year ?? '');
const filterSemester    = ref(props.filters?.semester ?? '');
const filterType        = ref(props.filters?.type ?? '');

const applyFilter = () => {
  const params = {};
  if (filterRegionLevel.value !== '') params.region_level = filterRegionLevel.value;
  if (filterRegionCode.value !== '')  params.region_code  = filterRegionCode.value;
  if (filterYear.value)               params.year       = filterYear.value;
  if (filterSemester.value)           params.semester   = filterSemester.value;
  if (filterType.value)               params.type       = filterType.value;

  router.get(route('admin.demographics.datasets'), params, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
  filterRegionLevel.value = '';
  filterRegionCode.value  = '';
  filterYear.value        = '';
  filterSemester.value    = '';
  filterType.value        = '';
  router.get(route('admin.demographics.datasets'), {}, { preserveState: true, preserveScroll: true });
};

const onRegionLevelChange = () => {
  if (form.region_level === 'regency') {
    form.region_code = props.regencyCode ?? '5205';
  } else {
    form.region_code = '';
  }
};

const modal          = reactive({ open: false, editing: null });
const submitting     = ref(false);
const editorMode     = ref('spreadsheet'); // 'spreadsheet' | 'smart_paste' | 'excel_upload' | 'json'
const validationErrors = ref([]);
const pasteRawInput  = ref('');
const selectedTypeChoice = ref('religion');
const customTypeKey = ref('');

const form = reactive({
  title: '', year: new Date().getFullYear(), semester: 1, type: 'religion',
  region_level: 'regency', region_code: props.regencyCode ?? '5205',
  status: 'published', notes: '', data_json: '',
});

// ── SPREADSHEET GRID STATE ──────────────────────────────────────────────────
const gridRows     = ref([]);
const historyStack = ref([]);
const historyIndex = ref(-1);

const saveGridHistory = () => {
  const snapshot = JSON.parse(JSON.stringify(gridRows.value));
  if (historyIndex.value < historyStack.value.length - 1) {
    historyStack.value = historyStack.value.slice(0, historyIndex.value + 1);
  }
  historyStack.value.push(snapshot);
  historyIndex.value = historyStack.value.length - 1;
  runLiveValidation();
  autoSaveDraft();
};

const undoGrid = () => {
  if (historyIndex.value > 0) {
    historyIndex.value--;
    gridRows.value = JSON.parse(JSON.stringify(historyStack.value[historyIndex.value]));
    runLiveValidation();
  }
};

const redoGrid = () => {
  if (historyIndex.value < historyStack.value.length - 1) {
    historyIndex.value++;
    gridRows.value = JSON.parse(JSON.stringify(historyStack.value[historyIndex.value]));
    runLiveValidation();
  }
};

const addGridRow = () => {
  if (form.type === 'population') {
    gridRows.value.push({ label: '', male: 0, female: 0 });
  } else {
    gridRows.value.push({ label: '', value: 0 });
  }
  saveGridHistory();
};

const duplicateGridRow = (idx) => {
  const target = gridRows.value[idx];
  if (target) {
    gridRows.value.splice(idx + 1, 0, JSON.parse(JSON.stringify(target)));
    saveGridHistory();
  }
};

const removeGridRow = (idx) => {
  gridRows.value.splice(idx, 1);
  saveGridHistory();
};

const clearGrid = () => {
  gridRows.value = [];
  saveGridHistory();
};

// ── KEYBOARD NAVIGATION IN SPREADSHEET GRID ─────────────────────────
const handleCellKeydown = (e, rowIdx, colKey) => {
  if (e.key === 'Enter' || e.key === 'ArrowDown') {
    e.preventDefault();
    if (rowIdx === gridRows.value.length - 1) {
      addGridRow();
    } else {
      focusCell(rowIdx + 1, colKey);
    }
  } else if (e.key === 'ArrowUp') {
    if (rowIdx > 0) {
      e.preventDefault();
      focusCell(rowIdx - 1, colKey);
    }
  }
};

const focusCell = (rowIdx, colKey) => {
  setTimeout(() => {
    const inputs = document.querySelectorAll(`input[v-model]`);
    // simple focus fallback
  }, 50);
};

// ── MASTER TEMPLATE GENERATOR ───────────────────────────────────────────────
const loadMasterTemplate = () => {
  const type = form.type;
  gridRows.value = [];

  if (type === 'religion') {
    gridRows.value = [
      { label: 'Islam', value: 270100 },
      { label: 'Kristen', value: 318 },
      { label: 'Katolik', value: 156 },
      { label: 'Hindu', value: 92 },
      { label: 'Buddha', value: 48 },
      { label: 'Konghucu', value: 12 },
    ];
  } else if (type === 'education') {
    gridRows.value = [
      { label: 'Tidak/Belum Sekolah', value: 42380 },
      { label: 'SD/Sederajat', value: 68120 },
      { label: 'SMP/Sederajat', value: 54230 },
      { label: 'SMA/Sederajat', value: 63480 },
      { label: 'D1/D2/D3', value: 8940 },
      { label: 'S1/D4', value: 27650 },
      { label: 'S2', value: 3280 },
      { label: 'S3', value: 215 },
    ];
  } else if (type === 'marital') {
    gridRows.value = [
      { label: 'Belum Kawin', value: 105420 },
      { label: 'Kawin', value: 148900 },
      { label: 'Cerai Hidup', value: 8340 },
      { label: 'Cerai Mati', value: 8106 },
    ];
  } else if (type === 'blood_type') {
    gridRows.value = [
      { label: 'A', value: 52340 },
      { label: 'B', value: 64120 },
      { label: 'AB', value: 28940 },
      { label: 'O', value: 89320 },
      { label: 'Tidak Tahu', value: 35846 },
    ];
  } else if (type === 'occupation') {
    gridRows.value = [
      { label: 'Belum/Tidak Bekerja', value: 52140 },
      { label: 'Mengurus Rumah Tangga', value: 48320 },
      { label: 'Pelajar/Mahasiswa', value: 42150 },
      { label: 'PNS/TNI/Polri', value: 8940 },
      { label: 'Wiraswasta', value: 35680 },
      { label: 'Petani/Peternak', value: 54120 },
      { label: 'Nelayan', value: 9230 },
      { label: 'Karyawan Swasta', value: 20180 },
    ];
  } else if (type === 'disability') {
    gridRows.value = [
      { label: 'Cacat Fisik', value: 840 },
      { label: 'Cacat Netra/Buta', value: 310 },
      { label: 'Cacat Rungu/Wicara', value: 420 },
      { label: 'Cacat Mental/Jiwa', value: 290 },
      { label: 'Cacat Fisik & Mental', value: 180 },
    ];
  } else if (type === 'population') {
    gridRows.value = [
      { label: '0-4', male: 8120, female: 7890 },
      { label: '5-9', male: 8450, female: 8210 },
      { label: '10-14', male: 8230, female: 8050 },
      { label: '15-19', male: 7890, female: 7640 },
      { label: '20-24', male: 7340, female: 7180 },
      { label: '25-29', male: 6980, female: 6820 },
      { label: '30-34', male: 6540, female: 6390 },
      { label: '35-39', male: 5980, female: 5840 },
      { label: '40-44', male: 5230, female: 5110 },
      { label: '45-49', male: 4780, female: 4640 },
      { label: '50-54', male: 4120, female: 3980 },
      { label: '55-59', male: 3540, female: 3410 },
      { label: '60-64', male: 2870, female: 2750 },
      { label: '65+', male: 4080, female: 4320 },
    ];
  } else if (type === 'akta_lahir') {
    gridRows.value = [
      { label: 'Memiliki Akta Lahir', value: 81043 },
      { label: 'Belum Memiliki Akta', value: 5357 },
    ];
  } else if (type === 'kia') {
    gridRows.value = [
      { label: 'Memiliki KIA', value: 73440 },
      { label: 'Belum Memiliki KIA', value: 12960 },
    ];
  } else if (type === 'ikd') {
    gridRows.value = [
      { label: 'Aktivasi IKD', value: 64964 },
      { label: 'Belum Aktivasi', value: 124436 },
    ];
  } else if (type === 'wajib_ktp') {
    gridRows.value = [
      { label: 'Sudah Rekam KTP', value: 185233 },
      { label: 'Belum Rekam KTP', value: 4167 },
    ];
  } else if (type === 'households') {
    gridRows.value = [
      { label: 'Kepala Keluarga Laki-Laki', value: 61845 },
      { label: 'Kepala Keluarga Perempuan', value: 12044 },
    ];
  } else if (type === 'lansia') {
    gridRows.value = [
      { label: 'Lansia Laki-Laki', value: 11671 },
      { label: 'Lansia Perempuan', value: 12149 },
    ];
  } else if (type === 'productive_age') {
    gridRows.value = [
      { label: 'Usia Produktif Laki-Laki', value: 87983 },
      { label: 'Usia Produktif Perempuan', value: 86588 },
    ];
  } else if (type === 'dependency_ratio') {
    gridRows.value = [
      { label: 'Penduduk Belum Produktif (0-14 Thn)', value: 70960 },
      { label: 'Penduduk Produktif (15-59 Thn)', value: 174571 },
      { label: 'Penduduk Lansia (60+ Thn)', value: 23820 },
    ];
  } else {
    gridRows.value = [
      { label: 'Kategori A', value: 100 },
      { label: 'Kategori B', value: 250 },
    ];
  }
  saveGridHistory();
};

// ── MODE 2: SMART PASTE FROM EXCEL ──────────────────────────────────────────
const generateSmartTitle = () => {
  if (modal.editing) return;
  const label = props.typeLabels[form.type] || 'Dataset';
  const region = getRegionLabel();
  form.title = `${label} ${region} ${form.year} Sem ${form.semester}`;
};

const importFeedback = ref('');

const importPastedData = () => {
  if (!pasteRawInput.value.trim()) return;
  const lines = pasteRawInput.value.trim().split(/\r?\n/);
  const rows = [];

  for (const line of lines) {
    if (!line.trim()) continue;
    let parts = line.split('\t');
    if (parts.length < 2) parts = line.split(',');
    if (parts.length < 2) parts = line.split(/\s{2,}/);

    const label = parts[0] ? parts[0].trim() : '';
    if (form.type === 'population') {
      const male   = parts[1] ? Number(parts[1].replace(/[^\d.-]/g, '')) || 0 : 0;
      const female = parts[2] ? Number(parts[2].replace(/[^\d.-]/g, '')) || 0 : 0;
      rows.push({ label, male, female });
    } else {
      const value = parts[1] ? Number(parts[1].replace(/[^\d.-]/g, '')) || 0 : 0;
      rows.push({ label, value });
    }
  }

  if (rows.length > 0) {
    gridRows.value = rows;
    pasteRawInput.value = '';
    editorMode.value = 'spreadsheet';
    importFeedback.value = `✓ Berhasil mengimpor ${rows.length} baris dari Excel ke Spreadsheet Editor!`;
    setTimeout(() => { importFeedback.value = ''; }, 4000);
    saveGridHistory();
  }
};

const getSmartPastePlaceholder = () => {
  if (form.type === 'population') {
    return 'Salin 3 kolom dari Excel (Rentang Usia, Laki-Laki, Perempuan):\n0-4\t8120\t7890\n5-9\t8450\t8210\n10-14\t8230\t8050\n15-19\t7890\t7640';
  }
  return 'Salin 2 kolom dari Excel (Kategori, Jumlah):\nIslam\t270100\nKristen\t318\nKatolik\t156\nHindu\t92';
};

// ── MODE 3: UPLOAD EXCEL FILE (.XLSX / .CSV) ────────────────────────────────
const handleExcelFileUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = (evt) => {
    try {
      const data = new Uint8Array(evt.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheetName];
      const jsonRows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

      const parsedRows = [];
      for (let i = 0; i < jsonRows.length; i++) {
        const row = jsonRows[i];
        if (!row || row.length === 0) continue;
        
        // Skip header if top row contains text like "label" or "kategori"
        const cell0 = String(row[0] || '').trim();
        const cell0Lower = cell0.toLowerCase();
        if (i === 0 && (cell0Lower.includes('label') || cell0Lower.includes('kategori') || cell0Lower.includes('nama'))) {
          continue;
        }

        if (form.type === 'population') {
          const male   = Number(String(row[1] || '0').replace(/[^\d.-]/g, '')) || 0;
          const female = Number(String(row[2] || '0').replace(/[^\d.-]/g, '')) || 0;
          parsedRows.push({ label: cell0, male, female });
        } else {
          const val = Number(String(row[1] || '0').replace(/[^\d.-]/g, '')) || 0;
          parsedRows.push({ label: cell0, value: val });
        }
      }

      if (parsedRows.length > 0) {
        gridRows.value = parsedRows;
        editorMode.value = 'spreadsheet';
        saveGridHistory();
      }
    } catch (err) {
      alert('Gagal membaca file Excel. Pastikan format file valid (.xlsx, .xls, .csv).');
    }
  };
  reader.readAsArrayBuffer(file);
};

// ── LIVE VALIDATION & COMPUTATIONS ──────────────────────────────────────────
const runLiveValidation = () => {
  const errors = [];
  const labelsSeen = new Set();

  if (gridRows.value.length === 0 && editorMode.value === 'spreadsheet') {
    errors.push('Tabel spreadsheet masih kosong. Tambahkan minimal 1 baris data.');
  }

  gridRows.value.forEach((row, i) => {
    const rowNum = i + 1;
    if (!row.label || !row.label.trim()) {
      errors.push(`Baris #${rowNum}: Label/Kategori nama tidak boleh kosong.`);
    }
    if (row.label && labelsSeen.has(row.label.trim().toLowerCase())) {
      errors.push(`Baris #${rowNum}: Label "${row.label}" terdeteksi duplikat.`);
    }
    if (row.label) {
      labelsSeen.add(row.label.trim().toLowerCase());
    }

    if (form.type === 'population') {
      if (Number(row.male) < 0) errors.push(`Baris #${rowNum}: Jumlah laki-laki tidak boleh negatif.`);
      if (Number(row.female) < 0) errors.push(`Baris #${rowNum}: Jumlah perempuan tidak boleh negatif.`);
    } else {
      if (Number(row.value) < 0) errors.push(`Baris #${rowNum}: Jumlah nilai tidak boleh negatif.`);
    }
  });

  validationErrors.value = errors;
};

const calculateTotalPopulation = () => {
  if (form.type === 'population') {
    return gridRows.value.reduce((sum, r) => sum + (Number(r.male)||0) + (Number(r.female)||0), 0);
  }
  return gridRows.value.reduce((sum, r) => sum + (Number(r.value)||0), 0);
};

const getRegionLabel = () => {
  if (form.region_level === 'regency') return 'Kabupaten Dompu';
  if (form.region_level === 'district') {
    return props.kecamatans.find(k => k.code === form.region_code)?.name || form.region_code;
  }
  if (form.region_level === 'village') {
    return props.desas.find(d => d.code === form.region_code)?.name || form.region_code;
  }
  return 'Dompu';
};

const autoSaveDraft = () => {
  const draftKey = `dataset_draft_${form.type}_${form.year}_${form.semester}`;
  localStorage.setItem(draftKey, JSON.stringify(gridRows.value));
};

const setEditorMode = (mode) => {
  if (mode === 'json' && editorMode.value === 'spreadsheet') {
    compileGridToJson();
  } else if (mode === 'spreadsheet' && editorMode.value === 'json') {
    try {
      const parsed = JSON.parse(form.data_json);
      parseJsonToGrid(parsed, form.type);
    } catch (e) {
      alert('Sintaks JSON tidak valid, tidak dapat dikonversi ke Spreadsheet.');
    }
  }
  editorMode.value = mode;
};

const onTypeChoiceChange = () => {
  if (selectedTypeChoice.value === 'custom') {
    form.type = customTypeKey.value || 'custom_type';
  } else {
    form.type = selectedTypeChoice.value;
  }
  generateSmartTitle();
  loadMasterTemplate();
};

const openModal = (ds) => {
  validationErrors.value = [];
  modal.editing = ds;
  if (ds) {
    if (props.typeLabels[ds.type]) {
      selectedTypeChoice.value = ds.type;
      customTypeKey.value = '';
    } else {
      selectedTypeChoice.value = 'custom';
      customTypeKey.value = ds.type;
    }
    Object.assign(form, {
      title: ds.title, year: ds.year, semester: ds.semester || 1, type: ds.type,
      region_level: ds.region_level || 'regency',
      region_code: ds.region_code || props.regencyCode,
      status: ds.status, notes: ds.notes || '',
      data_json: ds.data_json ? JSON.stringify(ds.data_json, null, 2) : '',
    });
    parseJsonToGrid(ds.data_json, ds.type);
  } else {
    selectedTypeChoice.value = 'religion';
    customTypeKey.value = '';
    Object.assign(form, {
      title: 'Distribusi Agama Kabupaten Dompu ' + new Date().getFullYear() + ' Sem 1',
      year: new Date().getFullYear(), semester: 1, type: 'religion',
      region_level: 'regency', region_code: props.regencyCode ?? '5205',
      status: 'published', notes: '', data_json: ''
    });
    loadMasterTemplate();
  }
  editorMode.value = 'spreadsheet';
  modal.open = true;
};

// ── JSON SCHEMA COMPILER & PARSER ───────────────────────────────────────────
const parseJsonToGrid = (jsonObj, type) => {
  gridRows.value = [];
  if (!jsonObj) return;

  if (type === 'education' || type === 'blood_type') {
    if (jsonObj.categories && Array.isArray(jsonObj.categories)) {
      gridRows.value = jsonObj.categories.map((cat, i) => ({
        label: cat,
        value: jsonObj.values ? jsonObj.values[i] || 0 : 0
      }));
    }
  } else if (type === 'population') {
    if (jsonObj.categories && Array.isArray(jsonObj.categories)) {
      gridRows.value = jsonObj.categories.map((cat, i) => ({
        label: cat,
        male: jsonObj.male ? jsonObj.male[i] || 0 : 0,
        female: jsonObj.female ? jsonObj.female[i] || 0 : 0,
      }));
    }
  } else {
    if (jsonObj.items && Array.isArray(jsonObj.items)) {
      gridRows.value = jsonObj.items.map(i => ({ label: i.name, value: i.value }));
    }
  }
  saveGridHistory();
};

const compileGridToJson = () => {
  const type = form.type;
  let result = {};

  if (type === 'education' || type === 'blood_type') {
    result = {
      categories: gridRows.value.map(r => r.label || ''),
      values: gridRows.value.map(r => Number(r.value || 0))
    };
  } else if (type === 'population') {
    const maleArr = gridRows.value.map(r => Number(r.male || 0));
    const femaleArr = gridRows.value.map(r => Number(r.female || 0));
    const totalPop = maleArr.reduce((a, b) => a + b, 0) + femaleArr.reduce((a, b) => a + b, 0);

    result = {
      categories: gridRows.value.map(r => r.label || ''),
      male: maleArr,
      female: femaleArr,
      total: totalPop,
    };
  } else {
    const items = gridRows.value.map(r => ({ name: r.label || '', value: Number(r.value || 0) }));
    result = { items };

    if (type === 'akta_lahir' || type === 'kia' || type === 'ikd') {
      const owned = items[0] ? items[0].value : 0;
      const unowned = items[1] ? items[1].value : 0;
      const target = owned + unowned;
      const percentage = target > 0 ? Number(((owned / target) * 100).toFixed(1)) : 0;

      result.owned = owned;
      result.target = target;
      result.percentage = percentage;
    } else if (type === 'wajib_ktp') {
      const recorded = items[0] ? items[0].value : 0;
      const unrecorded = items[1] ? items[1].value : 0;
      const total = recorded + unrecorded;
      const recorded_percentage = total > 0 ? Number(((recorded / total) * 100).toFixed(1)) : 0;

      result.recorded = recorded;
      result.total = total;
      result.recorded_percentage = recorded_percentage;
    } else if (type === 'lansia' || type === 'productive_age' || type === 'households' || type === 'disability') {
      const total = items.reduce((sum, item) => sum + item.value, 0);
      result.total = total;
    } else if (type === 'dependency_ratio') {
      const nonProd1 = items[0] ? items[0].value : 0;
      const prod     = items[1] ? items[1].value : 0;
      const nonProd2 = items[2] ? items[2].value : 0;
      const nonProdTotal = nonProd1 + nonProd2;
      const ratio = prod > 0 ? Number(((nonProdTotal / prod) * 100).toFixed(2)) : 0;

      result.ratio = ratio;
      result.non_productive = nonProdTotal;
      result.productive = prod;
      result.note = `Tiap 100 usia produktif menanggung ${ratio} penduduk non-produktif`;
    }
  }

  form.data_json = JSON.stringify(result, null, 2);
  return result;
};

// ── SAVE ENGINE ─────────────────────────────────────────────────────────────
const submitDataset = () => {
  runLiveValidation();
  if (validationErrors.value.length > 0) return;

  if (editorMode.value !== 'json') {
    compileGridToJson();
  }

  submitting.value = true;

  if (modal.editing) {
    router.put(route('admin.demographics.datasets.update', modal.editing.id), {
      title: form.title,
      year: Number(form.year),
      semester: Number(form.semester),
      type: form.type,
      region_level: form.region_level,
      region_code: form.region_code,
      status: form.status,
      notes: form.notes,
      data_json: form.data_json,
    }, {
      onSuccess: () => {
        submitting.value = false;
        modal.open = false;
      },
      onError: (errs) => {
        submitting.value = false;
        alert(Object.values(errs).join(', '));
      },
      onFinish: () => { submitting.value = false; }
    });
  } else {
    const payload = new FormData();
    Object.entries(form).forEach(([k, v]) => {
      if (v !== null && v !== undefined) payload.append(k, v);
    });

    router.post(route('admin.demographics.datasets.store'), payload, {
      forceFormData: true,
      onSuccess: () => {
        submitting.value = false;
        modal.open = false;
      },
      onError: (errs) => {
        submitting.value = false;
        alert(Object.values(errs).join(', '));
      },
      onFinish: () => { submitting.value = false; }
    });
  }
};

const confirmDelete = (ds) => {
  if (confirm(`Hapus dataset "${ds.title}"?`)) {
    router.delete(route('admin.demographics.datasets.destroy', ds.id));
  }
};
</script>
