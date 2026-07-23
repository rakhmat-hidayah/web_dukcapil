<template>
  <Head title="Dataset Kependudukan" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Dataset Statistik Kependudukan</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola dataset visualisasi untuk ECharts (populasi, agama, pendidikan, dsb).</p>
        </div>
        <button @click="openModal(null)" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5">
          <Plus class="w-3.5 h-3.5" /> Upload Dataset
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
                <th class="text-left px-3 py-2 sm:px-5 sm:py-3 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Kecamatan</th>
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
                    <!-- items format (religion, marital, akta_lahir, disability, etc.) -->
                    <template v-if="ds.data_json.items && Array.isArray(ds.data_json.items)">
                      <div v-for="item in ds.data_json.items" :key="item.name"
                        class="flex items-center gap-1.5 text-[10px]">
                        <span class="text-gray-500 dark:text-zinc-400 min-w-0 truncate max-w-[120px]">{{ item.name }}</span>
                        <span class="font-bold text-gray-800 dark:text-zinc-100 font-mono ml-auto whitespace-nowrap">{{ item.value?.toLocaleString('id-ID') }}</span>
                      </div>
                    </template>

                    <!-- categories + values format (education, blood_type) -->
                    <template v-else-if="ds.data_json.categories && Array.isArray(ds.data_json.categories) && ds.data_json.values">
                      <div v-for="(cat, i) in ds.data_json.categories" :key="cat"
                        class="flex items-center gap-1.5 text-[10px]">
                        <span class="text-gray-500 dark:text-zinc-400 min-w-0 truncate max-w-[120px]">{{ cat }}</span>
                        <span class="font-bold text-gray-800 dark:text-zinc-100 font-mono ml-auto whitespace-nowrap">{{ ds.data_json.values[i]?.toLocaleString('id-ID') }}</span>
                      </div>
                    </template>

                    <!-- population pyramid format -->
                    <template v-else-if="ds.data_json.categories && ds.data_json.male">
                      <div class="text-[10px] text-gray-500">Total: <span class="font-bold text-gray-800 dark:text-zinc-100 font-mono">{{ ds.data_json.total?.toLocaleString('id-ID') }}</span></div>
                      <div v-for="(cat, i) in ds.data_json.categories" :key="cat"
                        class="flex items-center gap-1.5 text-[10px]">
                        <span class="text-gray-500 dark:text-zinc-400 w-8">{{ cat }}</span>
                        <span class="text-blue-500 font-mono">L:{{ ds.data_json.male[i]?.toLocaleString('id-ID') }}</span>
                        <span class="text-pink-500 font-mono">P:{{ ds.data_json.female[i]?.toLocaleString('id-ID') }}</span>
                      </div>
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

    <!-- Dataset Form Modal -->
    <div v-if="modal.open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="modal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 w-full max-w-3xl shadow-2xl space-y-5 max-h-[92vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-gray-100 dark:border-zinc-800 pb-3">
          <h3 class="font-black text-sm text-gray-900 dark:text-zinc-50">{{ modal.editing ? 'Edit Dataset' : 'Upload Dataset Baru' }}</h3>
          <!-- Mode Switcher -->
          <div class="flex items-center gap-1 bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl">
            <button 
              type="button" 
              @click="editorMode = 'visual'"
              class="px-2.5 py-1 text-[10px] font-bold rounded-lg transition"
              :class="editorMode === 'visual' ? 'bg-white dark:bg-zinc-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
            >
              📋 Mode Form Visual (Mudah)
            </button>
            <button 
              type="button" 
              @click="editorMode = 'json'"
              class="px-2.5 py-1 text-[10px] font-bold rounded-lg transition"
              :class="editorMode === 'json' ? 'bg-white dark:bg-zinc-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
            >
              💻 Mode JSON Manual
            </button>
          </div>
        </div>

        <form @submit.prevent="submitDataset" class="space-y-4" enctype="multipart/form-data">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Judul Dataset *</label>
              <input v-model="form.title" required type="text" placeholder="Contoh: Distribusi Agama Dompu 2026 Sem 1" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tahun *</label>
                <input v-model="form.year" type="number" required min="2000" max="2100" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
              </div>
              <div>
                <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Semester *</label>
                <select v-model="form.semester" required class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
                  <option :value="1">Sem 1 (Juni)</option>
                  <option :value="2">Sem 2 (Desember)</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tipe Data *</label>
              <div class="space-y-1.5">
                <select v-model="selectedTypeChoice" @change="onTypeChoiceChange" required class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
                  <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
                  <option value="custom">➕ Tipe Baru (Kustom)...</option>
                </select>
                <input 
                  v-if="selectedTypeChoice === 'custom'" 
                  v-model="customTypeKey" 
                  @input="onCustomTypeInput"
                  type="text" 
                  placeholder="Ketik kode/nama tipe baru (misal: pekerjaan)" 
                  required 
                  class="w-full px-3 py-1.5 bg-white dark:bg-zinc-900 border border-primary-300 dark:border-primary-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none font-mono" 
                />
              </div>
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Level Wilayah *</label>
              <select v-model="form.region_level" @change="onRegionLevelChange" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <option value="regency">Kabupaten Dompu (Keseluruhan)</option>
                <option value="district">Kecamatan</option>
                <option value="village">Desa / Kelurahan</option>
              </select>
            </div>
            <div v-if="form.region_level === 'district'">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Pilih Kecamatan *</label>
              <select v-model="form.region_code" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <option v-for="kec in kecamatans" :key="kec.code" :value="kec.code">{{ kec.name }} ({{ kec.code }})</option>
              </select>
            </div>
            <div v-else-if="form.region_level === 'village'">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Pilih Desa/Kelurahan *</label>
              <select v-model="form.region_code" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <option v-for="d in desas" :key="d.code" :value="d.code">{{ d.name }} ({{ d.code }})</option>
              </select>
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Status *</label>
              <select v-model="form.status" required class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <option value="draft">Draft</option>
                <option value="published">Dipublikasikan</option>
              </select>
            </div>
            <div v-if="!modal.editing" class="col-span-2">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Upload File Sumber (PDF/Excel)</label>
              <input type="file" accept=".pdf,.xlsx,.xls" @change="onFileChange" class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-600 hover:file:bg-primary-100 transition" />
            </div>
          </div>

          <!-- VISUAL BUILDER MODE -->
          <div v-if="editorMode === 'visual'" class="space-y-3 p-4 bg-gray-50/80 dark:bg-zinc-800/50 rounded-2xl border border-gray-100 dark:border-zinc-700">
            <div class="flex justify-between items-center">
              <label class="text-[10px] font-black uppercase tracking-wider text-primary-600 dark:text-primary-400">
                Isi Data Tabel Visual (Tanpa Coding JSON)
              </label>
              <button 
                type="button" 
                @click="loadPresetTemplate" 
                class="px-2.5 py-1 text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 rounded-lg hover:bg-primary-200 transition"
              >
                ✨ Muat Template Standar ( Otomatis )
              </button>
            </div>

            <!-- Type: population (Piramida Usia) -->
            <div v-if="form.type === 'population'" class="space-y-2">
              <div class="space-y-2">
                <div v-for="(item, idx) in visualPyramid" :key="idx" class="flex gap-2 items-center text-xs">
                  <input v-model="item.category" type="text" placeholder="Rentang Usia (0-4)" class="w-28 px-3 py-1.5 bg-white dark:bg-zinc-900 border rounded-xl text-xs" />
                  <input v-model.number="item.male" type="number" min="0" placeholder="Laki-laki" class="flex-1 px-3 py-1.5 bg-white dark:bg-zinc-900 border rounded-xl text-xs" />
                  <input v-model.number="item.female" type="number" min="0" placeholder="Perempuan" class="flex-1 px-3 py-1.5 bg-white dark:bg-zinc-900 border rounded-xl text-xs" />
                  <button type="button" @click="removeVisualPyramid(idx)" class="p-1 text-red-400 hover:text-red-600">✕</button>
                </div>
              </div>
              <button type="button" @click="addVisualPyramid" class="text-[10px] font-bold text-primary-600 hover:underline">+ Tambah Kelompok Usia</button>
            </div>

            <!-- Type: categories & values (Pendidikan / Golongan Darah) -->
            <div v-else-if="form.type === 'education' || form.type === 'blood_type'" class="space-y-2">
              <div class="space-y-2">
                <div v-for="(item, idx) in visualCategories" :key="idx" class="flex gap-2 items-center">
                  <input v-model="item.category" type="text" placeholder="Kategori (misal: SMA/Sederajat)" class="flex-1 px-3 py-1.5 bg-white dark:bg-zinc-900 border rounded-xl text-xs" />
                  <input v-model.number="item.value" type="number" min="0" placeholder="Jumlah Jiwa" class="w-32 px-3 py-1.5 bg-white dark:bg-zinc-900 border rounded-xl text-xs" />
                  <button type="button" @click="removeVisualCategory(idx)" class="p-1 text-red-400 hover:text-red-600">✕</button>
                </div>
              </div>
              <button type="button" @click="addVisualCategory" class="text-[10px] font-bold text-primary-600 hover:underline">+ Tambah Baris Kategori</button>
            </div>

            <!-- Default / Custom Type & Items (Agama, Perkawinan, Pekerjaan, Disabilitas, Custom, dll) -->
            <div v-else class="space-y-2">
              <div class="space-y-2">
                <div v-if="visualItems.length === 0" class="text-xs text-gray-400 italic py-1">
                  Belum ada baris data. Klik "+ Tambah Baris Data" di bawah.
                </div>
                <div v-for="(item, idx) in visualItems" :key="idx" class="flex gap-2 items-center">
                  <input v-model="item.name" type="text" placeholder="Nama Label (misal: Wajib KTP / Pekerjaan)" class="flex-1 px-3 py-1.5 bg-white dark:bg-zinc-900 border rounded-xl text-xs" />
                  <input v-model.number="item.value" type="number" min="0" placeholder="Jumlah Jiwa" class="w-32 px-3 py-1.5 bg-white dark:bg-zinc-900 border rounded-xl text-xs" />
                  <button type="button" @click="removeVisualItem(idx)" class="p-1 text-red-400 hover:text-red-600">✕</button>
                </div>
              </div>
              <button type="button" @click="addVisualItem" class="text-[10px] font-bold text-primary-600 hover:underline">+ Tambah Baris Data</button>
            </div>
          </div>

          <!-- RAW JSON MODE -->
          <div v-else class="space-y-1">
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">
              Data JSON Manual (Format Bebas)
            </label>
            <textarea 
              v-model="form.data_json" 
              rows="8" 
              placeholder='{"items": [{"name": "Islam", "value": 270100}]}'
              class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-primary-500 focus:outline-none"
            ></textarea>
            <p class="text-[9px] text-gray-400">Pastikan sintaks JSON valid sebelum menyimpan.</p>
          </div>

          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Catatan</label>
            <textarea v-model="form.notes" rows="2" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
            <button type="button" @click="modal.open = false" class="px-4 py-2 text-xs font-bold border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 transition">Batal</button>
            <button type="submit" :disabled="submitting" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition disabled:opacity-50">
              {{ submitting ? 'Menyimpan...' : 'Simpan Dataset' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, SquarePen, Trash2 } from '@lucide/vue';

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

// Filter state — initialise from server-sent current filters
const filterRegionLevel = ref(props.filters?.region_level ?? '');
const filterRegionCode  = ref(props.filters?.region_code ?? '');
const filterYear        = ref(props.filters?.year ?? '');
const filterSemester    = ref(props.filters?.semester ?? '');
const filterType        = ref(props.filters?.type ?? '');

watch(() => props.availableSemesters, (newSems) => {
  if (filterSemester.value && newSems && newSems.length > 0 && !newSems.includes(Number(filterSemester.value))) {
    filterSemester.value = '';
  }
}, { immediate: true });

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

const modal        = reactive({ open: false, editing: null });
const submitting   = ref(false);
const editorMode   = ref('visual'); // 'visual' | 'json'

const form = reactive({
  title: '', year: new Date().getFullYear(), semester: 1, type: 'religion',
  region_level: 'regency', region_code: props.regencyCode ?? '5205',
  status: 'published', notes: '', data_json: '',
});
let selectedFile = null;

// Visual builder local states
const visualItems      = ref([]);
const visualCategories = ref([]);
const visualPyramid    = ref([]);

const selectedTypeChoice = ref('religion');
const customTypeKey = ref('');

const onTypeChoiceChange = () => {
  if (selectedTypeChoice.value === 'custom') {
    form.type = customTypeKey.value || 'custom_type';
    if (visualItems.value.length === 0) {
      visualItems.value = [{ name: '', value: 0 }];
    }
  } else {
    form.type = selectedTypeChoice.value;
    loadPresetTemplate();
  }
};

const onCustomTypeInput = () => {
  form.type = customTypeKey.value.trim().toLowerCase().replace(/\s+/g, '_');
};

const openModal = (ds) => {
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
    parseJsonToVisual(ds.data_json, ds.type);
  } else {
    selectedTypeChoice.value = 'religion';
    customTypeKey.value = '';
    Object.assign(form, { title: '', year: new Date().getFullYear(), semester: 1, type: 'religion', region_level: 'regency', region_code: props.regencyCode ?? '5205', status: 'published', notes: '', data_json: '' });
    selectedFile = null;
    loadPresetTemplate();
  }
  modal.open = true;
};

const parseJsonToVisual = (jsonObj, type) => {
  visualItems.value = [];
  visualCategories.value = [];
  visualPyramid.value = [];

  if (!jsonObj) return;

  if (type === 'education' || type === 'blood_type') {
    if (jsonObj.categories && Array.isArray(jsonObj.categories)) {
      visualCategories.value = jsonObj.categories.map((cat, i) => ({
        category: cat,
        value: jsonObj.values ? jsonObj.values[i] || 0 : 0
      }));
    }
  } else if (type === 'population') {
    if (jsonObj.categories && Array.isArray(jsonObj.categories)) {
      visualPyramid.value = jsonObj.categories.map((cat, i) => ({
        category: cat,
        male: jsonObj.male ? jsonObj.male[i] || 0 : 0,
        female: jsonObj.female ? jsonObj.female[i] || 0 : 0,
      }));
    }
  } else {
    if (jsonObj.items && Array.isArray(jsonObj.items)) {
      visualItems.value = jsonObj.items.map(i => ({ name: i.name, value: i.value }));
    }
  }
};

const syncVisualToJson = () => {
  if (editorMode.value !== 'visual') return;
  const type = form.type;
  let result = {};

  if (type === 'education' || type === 'blood_type') {
    result = {
      categories: visualCategories.value.map(c => c.category || ''),
      values: visualCategories.value.map(c => Number(c.value || 0))
    };
  } else if (type === 'population') {
    const maleArr = visualPyramid.value.map(p => Number(p.male || 0));
    const femaleArr = visualPyramid.value.map(p => Number(p.female || 0));
    const totalPop = maleArr.reduce((a, b) => a + b, 0) + femaleArr.reduce((a, b) => a + b, 0);

    result = {
      categories: visualPyramid.value.map(p => p.category || ''),
      male: maleArr,
      female: femaleArr,
      total: totalPop,
    };
  } else {
    const items = visualItems.value.map(i => ({ name: i.name || '', value: Number(i.value || 0) }));
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
};

// Watch visual changes to keep JSON in sync
watch([visualItems, visualCategories, visualPyramid], syncVisualToJson, { deep: true });

const loadPresetTemplate = () => {
  const type = form.type;
  // Always clear previous state first to prevent lingering array data
  visualItems.value = [];
  visualCategories.value = [];
  visualPyramid.value = [];

  if (type === 'religion') {
    visualItems.value = [
      { name: 'Islam', value: 270100 },
      { name: 'Kristen', value: 318 },
      { name: 'Katolik', value: 156 },
      { name: 'Hindu', value: 92 },
      { name: 'Buddha', value: 48 },
      { name: 'Konghucu', value: 12 },
    ];
  } else if (type === 'marital') {
    visualItems.value = [
      { name: 'Belum Kawin', value: 105420 },
      { name: 'Kawin', value: 148900 },
      { name: 'Cerai Hidup', value: 8340 },
      { name: 'Cerai Mati', value: 8106 },
    ];
  } else if (type === 'occupation') {
    visualItems.value = [
      { name: 'Belum/Tidak Bekerja', value: 52140 },
      { name: 'Mengurus Rumah Tangga', value: 48320 },
      { name: 'Pelajar/Mahasiswa', value: 42150 },
      { name: 'PNS/TNI/Polri', value: 8940 },
      { name: 'Wiraswasta', value: 35680 },
      { name: 'Petani/Peternak', value: 54120 },
      { name: 'Nelayan', value: 9230 },
      { name: 'Karyawan Swasta', value: 20180 },
    ];
  } else if (type === 'disability') {
    visualItems.value = [
      { name: 'Cacat Fisik', value: 840 },
      { name: 'Cacat Netra/Buta', value: 310 },
      { name: 'Cacat Rungu/Wicara', value: 420 },
      { name: 'Cacat Mental/Jiwa', value: 290 },
      { name: 'Cacat Fisik & Mental', value: 180 },
    ];
  } else if (type === 'education') {
    visualCategories.value = [
      { category: 'Tidak/Belum Sekolah', value: 42380 },
      { category: 'SD/Sederajat', value: 68120 },
      { category: 'SMP/Sederajat', value: 54230 },
      { category: 'SMA/Sederajat', value: 63480 },
      { category: 'D1/D2/D3', value: 8940 },
      { category: 'S1/D4', value: 27650 },
      { category: 'S2', value: 3280 },
      { category: 'S3', value: 215 },
    ];
  } else if (type === 'blood_type') {
    visualCategories.value = [
      { category: 'A', value: 52340 },
      { category: 'B', value: 64120 },
      { category: 'AB', value: 28940 },
      { category: 'O', value: 89320 },
      { category: 'Tidak Tahu', value: 35846 },
    ];
  } else if (type === 'population') {
    visualPyramid.value = [
      { category: '0-4', male: 8120, female: 7890 },
      { category: '5-9', male: 8450, female: 8210 },
      { category: '10-14', male: 8230, female: 8050 },
      { category: '15-19', male: 7890, female: 7640 },
      { category: '20-24', male: 7340, female: 7180 },
      { category: '25-29', male: 6980, female: 6820 },
      { category: '30-34', male: 6540, female: 6390 },
      { category: '35-39', male: 5980, female: 5840 },
      { category: '40-44', male: 5230, female: 5110 },
      { category: '45-49', male: 4780, female: 4640 },
      { category: '50-54', male: 4120, female: 3980 },
      { category: '55-59', male: 3540, female: 3410 },
      { category: '60-64', male: 2870, female: 2750 },
      { category: '65+', male: 4080, female: 4320 },
    ];
  } else {
    // Sample preset template for custom types
    visualItems.value = [
      { name: 'Sampel 1', value: 100 },
      { name: 'Sampel 2', value: 250 },
      { name: 'Sampel 3', value: 500 },
    ];
  }
  syncVisualToJson();
};

const addVisualItem = () => visualItems.value.push({ name: '', value: 0 });
const removeVisualItem = (i) => visualItems.value.splice(i, 1);

const addVisualCategory = () => visualCategories.value.push({ category: '', value: 0 });
const removeVisualCategory = (i) => visualCategories.value.splice(i, 1);

const addVisualPyramid = () => visualPyramid.value.push({ category: '', male: 0, female: 0 });
const removeVisualPyramid = (i) => visualPyramid.value.splice(i, 1);

const onFileChange = (e) => {
  selectedFile = e.target.files[0] || null;
};

const submitDataset = () => {
  syncVisualToJson();
  submitting.value = true;
  const payload = new FormData();
  Object.entries(form).forEach(([k, v]) => { if (v !== null && v !== undefined) payload.append(k, v); });
  if (selectedFile) payload.append('file', selectedFile);

  if (modal.editing) {
    router.put(route('admin.demographics.datasets.update', modal.editing.id), {
      title: form.title, year: form.year, type: form.type,
      region_level: form.region_level, region_code: form.region_code,
      status: form.status, notes: form.notes, data_json: form.data_json,
    }, { onFinish: () => { submitting.value = false; modal.open = false; } });
  } else {
    router.post(route('admin.demographics.datasets.store'), payload, {
      forceFormData: true,
      onFinish: () => { submitting.value = false; modal.open = false; },
    });
  }
};

const confirmDelete = (ds) => {
  if (confirm(`Hapus dataset "${ds.title}"?`)) {
    router.delete(route('admin.demographics.datasets.destroy', ds.id));
  }
};
</script>
