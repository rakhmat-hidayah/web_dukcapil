<template>
  <AdminLayout title="Visual Tree Editor">
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Visual Tree Editor Org Chart</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola simpul hirarki, relasi atasan-bawahan, dan penugasan pejabat pada bagan organisasi</p>
        </div>
        <button @click="openAddNodeModal(null)" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-600/30 transition">
          + Tambah Simpul Organisasi
        </button>
      </div>

      <!-- Node Management Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-slate-700">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Daftar Simpul Hirarki Organisasi</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-900 uppercase font-semibold text-slate-400">
              <tr>
                <th class="p-3">Nama Simpul Jabatan</th>
                <th class="p-3">Pejabat Terhubung</th>
                <th class="p-3">Atasan (Parent)</th>
                <th class="p-3">Warna Simpul</th>
                <th class="p-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
              <tr v-for="node in allNodes" :key="node.id">
                <td class="p-3 font-bold text-slate-900 dark:text-white">{{ node.node_title }}</td>
                <td class="p-3">
                  <span v-if="node.official" class="font-semibold text-blue-600 dark:text-blue-400 block">{{ node.official.name }}</span>
                  <span v-else class="text-slate-400 italic">Belum Ditugaskan</span>
                </td>
                <td class="p-3 text-slate-500">
                  {{ node.parent_id ? (allNodes.find(n => n.id === node.parent_id)?.node_title || 'ID: ' + node.parent_id) : 'ROOT (Pimpinan)' }}
                </td>
                <td class="p-3">
                  <span class="w-4 h-4 rounded-full inline-block align-middle mr-1.5" :style="{ backgroundColor: node.color_code }"></span>
                  <span class="font-mono text-[11px]">{{ node.color_code }}</span>
                </td>
                <td class="p-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openEditNodeModal(node)" class="p-1.5 bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 transition">
                      Edit
                    </button>
                    <button @click="deleteNode(node)" class="p-1.5 bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400 rounded-lg hover:bg-rose-100 transition">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Node Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4" @click.self="showModal = false">
      <div class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl border border-slate-100 dark:border-slate-700">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">{{ isEditNode ? 'Edit Simpul Organisasi' : 'Tambah Simpul Baru' }}</h2>

        <form @submit.prevent="saveNode" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Judul Simpul Jabatan *</label>
            <input v-model="nodeForm.node_title" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pejabat Terhubung</label>
            <select v-model="nodeForm.official_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
              <option :value="null">-- Tidak Ada / Kosong --</option>
              <option v-for="off in officials" :key="off.id" :value="off.id">{{ off.name }} ({{ off.position_title }})</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Atasan (Parent Node)</label>
            <select v-model="nodeForm.parent_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
              <option :value="null">-- Tidak Ada (Pimpinan Utama / Root) --</option>
              <option v-for="pn in allNodes.filter(n => n.id !== editingNodeId)" :key="pn.id" :value="pn.id">{{ pn.node_title }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Warna Akses Simpul</label>
            <input v-model="nodeForm.color_code" type="color" class="h-10 w-full rounded-xl cursor-pointer" />
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
            <button type="button" @click="showModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold">Batal</button>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-600/30">Simpan Simpul</button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  tree: Array,
  positions: Array,
  officials: Array,
  allNodes: Array,
})

const showModal = ref(false)
const isEditNode = ref(false)
const editingNodeId = ref(null)

const nodeForm = ref({
  node_title: '',
  official_id: null,
  parent_id: null,
  color_code: '#2563eb',
})

const openAddNodeModal = (parent = null) => {
  isEditNode.value = false
  editingNodeId.value = null
  nodeForm.value = {
    node_title: '',
    official_id: null,
    parent_id: parent ? parent.id : null,
    color_code: '#2563eb',
  }
  showModal.value = true
}

const openEditNodeModal = (node) => {
  isEditNode.value = true
  editingNodeId.value = node.id
  nodeForm.value = {
    node_title: node.node_title,
    official_id: node.official_id,
    parent_id: node.parent_id,
    color_code: node.color_code || '#2563eb',
  }
  showModal.value = true
}

const saveNode = () => {
  if (isEditNode.value) {
    router.put('/admin/profile/organization-chart/node/' + editingNodeId.value, nodeForm.value, {
      onSuccess: () => { showModal.value = false }
    })
  } else {
    router.post('/admin/profile/organization-chart/node', nodeForm.value, {
      onSuccess: () => { showModal.value = false }
    })
  }
}

const deleteNode = (node) => {
  if (confirm(`Hapus simpul ${node.node_title}?`)) {
    router.delete('/admin/profile/organization-chart/node/' + node.id)
  }
}
</script>
