<template>
  <section id="duties" class="py-12 mb-12 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 md:p-12 shadow-xl border border-slate-100 dark:border-slate-700">
      <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-6">{{ sectionTitle }}</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="(item, idx) in dutyItems" :key="idx" class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <div 
            :class="[
              'w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg mb-4',
              idx === 0 ? 'bg-blue-500/10 text-blue-600' : idx === 1 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-purple-500/10 text-purple-600'
            ]"
          >
            0{{ idx + 1 }}
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white mb-2">{{ item.title }}</h3>
          <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ item.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({ setting: Object })

const ws = computed(() => page.props.website_settings || {})

const sectionTitle = computed(() => {
  return ws.value.profile_duties_title || props.setting?.content_data?.duties_title || 'Tugas Pokok & Fungsi'
})

const dutyItems = computed(() => {
  const items = props.setting?.content_data?.duty_items
  if (Array.isArray(items) && items.length > 0) return items

  return [
    {
      title: ws.value.profile_duties_item_1_title || 'Pendaftaran Penduduk',
      desc: ws.value.profile_duties_item_1_desc || 'Pelayanan penerbitan KTP-El, KK, KIA, dan surat keterangan pindah penduduk.',
    },
    {
      title: ws.value.profile_duties_item_2_title || 'Pencatatan Sipil',
      desc: ws.value.profile_duties_item_2_desc || 'Penerbitan akta kelahiran, akta perkawinan, akta perceraian, dan akta kematian.',
    },
    {
      title: ws.value.profile_duties_item_3_title || 'Pengelolaan PIAK & IKD',
      desc: ws.value.profile_duties_item_3_desc || 'Pengelolaan jaringan data SIAK Terpusat dan aktivasi Identitas Kependudukan Digital.',
    },
  ]
})
</script>
