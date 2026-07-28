<template>
  <AdminLayout title="Dashboard Profil CMS">
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Profile CMS Architecture</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manajemen Modul Profil Instansi, Visual Org Chart Engine, & Master Official Directory</p>
        </div>
        <div class="flex items-center gap-3">
          <Link href="/admin/profile/builder" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-600/30 transition">
            Page Builder Profil &rarr;
          </Link>
          <Link href="/admin/profile/organization-chart" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-indigo-600/30 transition">
            Visual Tree Editor &rarr;
          </Link>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl">
          <span class="text-xs font-semibold text-slate-400 uppercase">Modul Profil Active</span>
          <div class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ stats.active_sections }} / {{ stats.total_sections }}</div>
          <p class="text-[11px] text-slate-500 mt-1">Modul aktif di halaman profil publik</p>
        </div>
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl">
          <span class="text-xs font-semibold text-slate-400 uppercase">Master Official Directory</span>
          <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ stats.active_officials }} / {{ stats.total_officials }}</div>
          <p class="text-[11px] text-slate-500 mt-1">Single source of truth pejabat</p>
        </div>
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl">
          <span class="text-xs font-semibold text-slate-400 uppercase">Simpul Organisasi</span>
          <div class="text-3xl font-black text-purple-600 dark:text-purple-400 mt-2">{{ stats.active_nodes }} / {{ stats.total_nodes }}</div>
          <p class="text-[11px] text-slate-500 mt-1">Simpul hirarki di Org Chart</p>
        </div>
      </div>

      <!-- Sections Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-slate-700">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Daftar Modul Profil Instansi</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-900 uppercase font-semibold text-slate-400">
              <tr>
                <th class="p-3">Urutan</th>
                <th class="p-3">Modul</th>
                <th class="p-3">Key Identifier</th>
                <th class="p-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
              <tr v-for="sec in sections" :key="sec.id">
                <td class="p-3 font-bold">#{{ sec.sort_order }}</td>
                <td class="p-3 font-bold text-slate-900 dark:text-white">{{ sec.name }}</td>
                <td class="p-3 text-blue-500 font-mono">{{ sec.key }}</td>
                <td class="p-3">
                  <span :class="sec.is_enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px]">
                    {{ sec.is_enabled ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  stats: Object,
  sections: Array,
  recentOfficials: Array,
})
</script>
