<template>
  <Head title="Hierarki Wilayah Administratif" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Hierarki Wilayah Administratif</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola data Kecamatan, Desa, dan Kelurahan di Kabupaten Dompu.</p>
        </div>
        <button @click="openKecamatanModal(null)" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5">
          <Plus class="w-3.5 h-3.5" /> Tambah Kecamatan
        </button>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold">
        ✓ {{ $page.props.flash.success }}
      </div>

      <!-- Kecamatan accordion list -->
      <div class="space-y-4">
        <div v-if="kecamatans.length === 0" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-10 text-center text-gray-400 text-xs">
          Belum ada kecamatan. Klik "Tambah Kecamatan" untuk mulai mengisi data wilayah.
        </div>

        <div 
          v-for="kec in kecamatans" 
          :key="kec.id"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden"
        >
          <!-- Kecamatan header row -->
          <div 
            class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition"
            @click="toggleKecamatan(kec.id)"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                <MapPin class="w-4 h-4 text-primary-600" />
              </div>
              <div>
                <h4 class="font-extrabold text-gray-800 dark:text-zinc-100 text-sm">{{ kec.name }}</h4>
                <p class="text-[9px] text-gray-400 font-mono">
                  Kode: {{ kec.code || '-' }} &nbsp;·&nbsp; {{ kec.desas_count || 0 }} Desa/Kel
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button @click.stop="openDesaModal(kec)" class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition text-[9px] font-bold">+ Desa</button>
              <button @click.stop="openKecamatanModal(kec)" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><SquarePen class="w-3.5 h-3.5" /></button>
              <button @click.stop="confirmDeleteKecamatan(kec)" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><Trash2 class="w-3.5 h-3.5" /></button>
              <ChevronDown class="w-4 h-4 text-gray-400 transition" :class="{ 'rotate-180': expandedKecamatan.includes(kec.id) }" />
            </div>
          </div>

          <!-- Desa table (collapsible) -->
          <div v-if="expandedKecamatan.includes(kec.id)" class="border-t border-gray-50 dark:border-zinc-800">
            <!-- Loading state -->
            <div v-if="loadingDesas[kec.id]" class="px-6 py-4 text-xs text-gray-400">Memuat data desa...</div>

            <template v-else>
              <div v-if="!desasByKecamatan[kec.id] || desasByKecamatan[kec.id].length === 0" class="px-6 py-4 text-xs text-gray-400 italic">
                Belum ada desa/kelurahan. Klik "+ Desa" untuk menambahkan.
              </div>
              <table v-else class="w-full text-xs">
                <thead>
                  <tr class="bg-gray-50 dark:bg-zinc-800/60">
                    <th class="text-left px-5 py-2 text-[9px] font-bold uppercase tracking-wider text-gray-400">Desa/Kelurahan</th>
                    <th class="text-left px-5 py-2 text-[9px] font-bold uppercase tracking-wider text-gray-400">Kode</th>
                    <th class="text-left px-5 py-2 text-[9px] font-bold uppercase tracking-wider text-gray-400">Tipe</th>
                    <th class="px-5 py-2"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
                  <tr v-for="desa in desasByKecamatan[kec.id]" :key="desa.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                    <td class="px-5 py-3 font-bold text-gray-700 dark:text-zinc-300">{{ desa.name }}</td>
                    <td class="px-5 py-3 text-gray-400 font-mono">{{ desa.code || '-' }}</td>
                    <td class="px-5 py-3">
                      <span class="px-1.5 py-0.5 text-[8px] font-bold uppercase rounded font-mono"
                        :class="desa.type === 'kelurahan' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300'">
                        {{ desa.type }}
                      </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                      <div class="flex items-center justify-end gap-1">
                        <button @click="openDesaModal(kec, desa)" class="p-1 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><SquarePen class="w-3 h-3" /></button>
                        <button @click="confirmDeleteDesa(desa)" class="p-1 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><Trash2 class="w-3 h-3" /></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Kecamatan Form Modal -->
    <div v-if="kecamatanModal.open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="kecamatanModal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl space-y-4">
        <h3 class="font-black text-sm text-gray-900 dark:text-zinc-50">{{ kecamatanModal.editing ? 'Edit Kecamatan' : 'Tambah Kecamatan' }}</h3>
        <form @submit.prevent="submitKecamatan" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Kecamatan *</label>
              <input v-model="kecamatanForm.name" required type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Kode Wilayah</label>
              <input v-model="kecamatanForm.code" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Ibukota Kecamatan</label>
              <input v-model="kecamatanForm.ibukota" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Luas (km²)</label>
              <input v-model="kecamatanForm.area_km2" type="number" step="0.01" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Urutan</label>
              <input v-model="kecamatanForm.sort_order" type="number" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="kecamatanModal.open = false" class="px-4 py-2 text-xs font-bold border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 transition">Batal</button>
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Desa Form Modal -->
    <div v-if="desaModal.open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="desaModal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 w-full max-w-md shadow-2xl space-y-4">
        <h3 class="font-black text-sm text-gray-900 dark:text-zinc-50">
          {{ desaModal.editing ? 'Edit Desa/Kelurahan' : `Tambah Desa — ${desaModal.kecamatan?.name}` }}
        </h3>
        <form @submit.prevent="submitDesa" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Desa/Kelurahan *</label>
              <input v-model="desaForm.name" required type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Kode</label>
              <input v-model="desaForm.code" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tipe *</label>
              <select v-model="desaForm.type" required class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <option value="desa">Desa</option>
                <option value="kelurahan">Kelurahan</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="desaModal.open = false" class="px-4 py-2 text-xs font-bold border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 transition">Batal</button>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, MapPin, SquarePen, Trash2, ChevronDown } from '@lucide/vue';
import axios from 'axios';

const props = defineProps({ kecamatans: Array });

const expandedKecamatan = ref([]);
const desasByKecamatan  = ref({});
const loadingDesas      = ref({});

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
    desasByKecamatan.value[kecamatanId] = res.data.desas;
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
  population_total: 0, male_count: 0, female_count: 0,
});

const openKecamatanModal = (kec) => {
  kecamatanModal.editing = kec;
  if (kec) {
    Object.assign(kecamatanForm, { name: kec.name, code: kec.code || '', ibukota: kec.ibukota || '', area_km2: kec.area_km2 || '', sort_order: kec.sort_order || 0, population_total: kec.population_total || 0, male_count: kec.male_count || 0, female_count: kec.female_count || 0 });
  } else {
    Object.assign(kecamatanForm, { name: '', code: '', ibukota: '', area_km2: '', sort_order: 0, population_total: 0, male_count: 0, female_count: 0 });
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
  kecamatan_id: '', name: '', code: '', type: 'desa',
  population_total: 0, male_count: 0, female_count: 0, sort_order: 0,
});

const openDesaModal = (kec, desa = null) => {
  desaModal.kecamatan = kec;
  desaModal.editing   = desa;
  if (desa) {
    Object.assign(desaForm, { kecamatan_id: kec.id, name: desa.name, code: desa.code || '', type: desa.type, population_total: desa.population_total || 0, male_count: desa.male_count || 0, female_count: desa.female_count || 0, sort_order: desa.sort_order || 0 });
  } else {
    Object.assign(desaForm, { kecamatan_id: kec.id, name: '', code: '', type: 'desa', population_total: 0, male_count: 0, female_count: 0, sort_order: 0 });
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

const formatNumber = (n) => (n || 0).toLocaleString('id-ID');
</script>
