<template>
  <Head title="Manajemen Jenis Pelayanan KTP-el - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Jenis Pelayanan KTP-el & Persyaratan
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Kelola jenis layanan KTP-el, kuota harian dinamis, dan berkas persyaratan yang wajib diunggah pemohon.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('admin.queue.service-types.create')"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jenis Layanan
          </Link>
        </div>
      </div>

      <!-- Service Types Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          v-for="service in serviceTypes"
          :key="service.id"
          class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition"
        >
          <div>
            <!-- Top Card Header -->
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3">
                <div
                  :class="[
                    'w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-white shadow-md',
                    getColorClass(service.color)
                  ]"
                >
                  <span class="font-mono text-base font-black">{{ service.name.substring(0, 2).toUpperCase() }}</span>
                </div>
                <div>
                  <h3 class="font-black text-slate-900 dark:text-white text-base">
                    {{ service.name }}
                  </h3>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                        service.is_active
                          ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400'
                          : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400'
                      ]"
                    >
                      {{ service.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <span class="text-xs text-slate-400">·</span>
                    <span class="text-xs text-slate-400 font-mono">
                      Biaya: {{ service.cost_label || 'Gratis' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Quota Pill -->
              <div class="text-right">
                <span class="text-[10px] font-bold uppercase text-slate-400 block">Kuota Harian</span>
                <span class="text-lg font-black font-mono text-blue-600 dark:text-blue-400">
                  {{ service.quota_per_day }} <span class="text-xs font-normal text-slate-400">/ hari</span>
                </span>
              </div>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 line-clamp-2 leading-relaxed">
              {{ service.short_description || 'Pelayanan administrasi kependudukan penerbitan KTP-el.' }}
            </p>

            <!-- Requirements Preview List -->
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60">
              <div class="flex items-center justify-between text-xs mb-2">
                <span class="font-bold text-slate-700 dark:text-slate-300">
                  Persyaratan Berkas ({{ service.documents?.length || 0 }})
                </span>
                <span v-if="service.requires_face_photo" class="text-[10px] text-blue-600 dark:text-blue-400 font-bold">
                  + Wajib Foto Selfie
                </span>
              </div>

              <div class="space-y-1.5">
                <div
                  v-for="(doc, idx) in service.documents?.slice(0, 4)"
                  :key="doc.id"
                  class="flex items-center justify-between text-[11px] p-2 rounded-xl bg-slate-50 dark:bg-slate-900/60"
                >
                  <span class="text-slate-700 dark:text-slate-300 truncate max-w-[220px]">
                    {{ idx + 1 }}. {{ doc.label }}
                  </span>
                  <span
                    :class="[
                      'text-[9px] font-bold px-1.5 py-0.5 rounded',
                      doc.is_required ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-800'
                    ]"
                  >
                    {{ doc.is_required ? 'Wajib' : 'Opsional' }}
                  </span>
                </div>
                <div v-if="(service.documents?.length || 0) > 4" class="text-[10px] text-slate-400 text-center pt-1 italic">
                  + {{ service.documents.length - 4 }} berkas lainnya
                </div>
              </div>
            </div>
          </div>

          <!-- Card Actions -->
          <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">
              Total pendaftaran: <strong class="text-slate-700 dark:text-slate-200">{{ service.applications_count || 0 }}</strong>
            </span>

            <div class="flex items-center gap-2">
              <Link
                :href="route('admin.queue.service-types.edit', service.id)"
                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Kelola Persyaratan & Edit
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  serviceTypes: Array,
})

const getColorClass = (color) => {
  const map = {
    rose: 'bg-rose-600 shadow-rose-600/30',
    amber: 'bg-amber-600 shadow-amber-600/30',
    indigo: 'bg-indigo-600 shadow-indigo-600/30',
    emerald: 'bg-emerald-600 shadow-emerald-600/30',
    blue: 'bg-blue-600 shadow-blue-600/30',
  }
  return map[color] || 'bg-blue-600 shadow-blue-600/30'
}
</script>
