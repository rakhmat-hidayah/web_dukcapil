<template>
  <AdminLayout title="Profile Page Builder">
    <div class="max-w-7xl mx-auto space-y-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Profile Page Builder</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Atur urutan, aktifkan/nonaktifkan modul profil, dan kustomisasi tampilan</p>
        </div>
        <button @click="saveOrder" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-600/30 transition">
          Simpan Urutan & Status Modul
        </button>
      </div>

      <!-- Sections Drag & Reorder List -->
      <div class="space-y-4">
        <div
          v-for="(sec, idx) in localSections"
          :key="sec.id"
          class="p-6 bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-4"
        >
          <div class="flex items-center gap-4">
            <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center justify-center">
              {{ idx + 1 }}
            </span>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white text-base">{{ sec.name }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ sec.description }} &bull; <code class="text-blue-500 font-mono">{{ sec.key }}</code></p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button
              @click="moveUp(idx)"
              :disabled="idx === 0"
              class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 disabled:opacity-30 text-xs font-bold"
            >
              ↑ Naik
            </button>
            <button
              @click="moveDown(idx)"
              :disabled="idx === localSections.length - 1"
              class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 disabled:opacity-30 text-xs font-bold"
            >
              ↓ Turun
            </button>

            <label class="relative inline-flex items-center cursor-pointer ml-4">
              <input type="checkbox" v-model="sec.is_enabled" class="sr-only peer" />
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              <span class="ml-2 text-xs font-semibold text-slate-700 dark:text-slate-300">{{ sec.is_enabled ? 'Aktif' : 'Nonaktif' }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  sections: Array,
})

const localSections = ref(JSON.parse(JSON.stringify(props.sections)))

const moveUp = (idx) => {
  if (idx > 0) {
    const temp = localSections.value[idx]
    localSections.value[idx] = localSections.value[idx - 1]
    localSections.value[idx - 1] = temp
  }
}

const moveDown = (idx) => {
  if (idx < localSections.value.length - 1) {
    const temp = localSections.value[idx]
    localSections.value[idx] = localSections.value[idx + 1]
    localSections.value[idx + 1] = temp
  }
}

const saveOrder = () => {
  router.post('/admin/profile/builder/reorder', {
    sections: localSections.value.map((s) => ({
      id: s.id,
      is_enabled: s.is_enabled,
    })),
  })
}
</script>
