<template>
  <section id="duties" class="py-12 mb-12 scroll-mt-24">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 md:p-12 shadow-xl border border-slate-100 dark:border-slate-700">
      <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-6">{{ sectionTitle }}</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div 
          v-for="(item, idx) in dutyItems" 
          :key="idx" 
          :class="[
            'p-6 rounded-2xl bg-slate-50/80 dark:bg-slate-900/80 border flex flex-col transition-all duration-300 hover:shadow-lg',
            idx === 0 ? 'border-blue-200 dark:border-blue-900/50' : idx === 1 ? 'border-emerald-200 dark:border-emerald-900/50' : 'border-purple-200 dark:border-purple-900/50'
          ]"
        >
          <div class="flex items-center gap-2 mb-3">
            <span class="text-lg">{{ idx === 0 ? '📌' : idx === 1 ? '⚡' : '🏢' }}</span>
            <h3 class="font-extrabold text-slate-900 dark:text-white text-base leading-snug">{{ item.title }}</h3>
          </div>
          <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line flex-1">{{ item.desc }}</p>
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
  // Check website_settings.profile_duties_items first
  if (ws.value.profile_duties_items) {
    let parsed = ws.value.profile_duties_items
    if (typeof parsed === 'string') {
      try {
        parsed = JSON.parse(parsed)
      } catch (e) {
        parsed = null
      }
    }
    if (Array.isArray(parsed) && parsed.length > 0) return parsed
  }

  // Check section props setting
  const items = props.setting?.content_data?.duty_items
  if (Array.isArray(items) && items.length > 0) return items

  // Fallback defaults
  return [
    {
      title: 'Tugas Pokok',
      desc: "Dinas Kependudukan dan Pencatatan Sipil mempunyai tugas membantu Bupati melaksanakan urusan pemerintahan di bidang Administrasi Kependudukan dan Pencatatan Sipil, meliputi:\n1. Fasilitasi pelayanan administrasi kependudukan.\n2. Pengelolaan informasi administrasi kependudukan (PIAK).\n3. Pemanfaatan data dan inovasi pelayanan kependudukan."
    },
    {
      title: 'Fungsi',
      desc: "Dalam menyelenggarakan tugas pokok, Dinas Kependudukan dan Pencatatan Sipil menyelenggarakan fungsi:\n1. Penyelenggaraan perumusan kebijakan teknis di bidang administrasi kependudukan dan pencatatan sipil.\n2. Penyelenggaraan pelayanan administrasi kependudukan dan pencatatan sipil.\n3. Penyelenggaraan evaluasi, pelaporan, dan pengendalian sistem informasi.\n4. Penyelenggaraan fungsi dinas lainnya sesuai tugas pokok dan fungsinya."
    },
    {
      title: 'Kewenangan Disdukcapil Kabupaten Dompu',
      desc: "Pemerintah Kabupaten berkewajiban dan bertanggung jawab menyelenggarakan urusan Administrasi Kependudukan dengan kewenangan meliputi:\n1. Koordinasi penyelenggaraan Administrasi Kependudukan.\n2. Pemberian bimbingan, supervisi, dan konsultasi pelaksanaan Pendaftaran Penduduk dan Pencatatan Sipil.\n3. Pembinaan dan sosialisasi penyelenggaraan Administrasi Kependudukan.\n4. Penyajian Data Kependudukan berskala Kabupaten dari SIAK Terpusat.\n5. Koordinasi pengawasan atas penyelenggaraan Administrasi Kependudukan."
    }
  ]
})
</script>
