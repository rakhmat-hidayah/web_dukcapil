<template>
  <section id="maklumat" class="py-12 mb-12 scroll-mt-24">
    <div class="bg-gradient-to-r from-emerald-700 to-teal-800 text-white rounded-3xl p-8 md:p-12 shadow-2xl text-center max-w-4xl mx-auto">
      <span class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider mb-4 inline-block">{{ maklumatTag }}</span>
      <h2 class="text-2xl md:text-4xl font-black mb-6">{{ maklumatTitle }}</h2>
      <p class="text-emerald-100 text-sm md:text-base leading-relaxed">
        {{ maklumatSubtitle }}
      </p>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({ setting: Object })

const ws = computed(() => page.props.website_settings || {})

const maklumatTag = computed(() => {
  return ws.value.profile_maklumat_tag || props.setting?.content_data?.maklumat_tag || 'Maklumat Pelayanan Publik'
})

const maklumatTitle = computed(() => {
  return ws.value.profile_maklumat_title || props.setting?.content_data?.maklumat_title || '"Dengan Ini, Kami Menyatakan Sanggup Menyenggarakan Pelayanan Sesuai Standar Pelayanan Yang Telah Ditetapkan"'
})

const maklumatSubtitle = computed(() => {
  return ws.value.profile_maklumat_subtitle || props.setting?.content_data?.maklumat_subtitle || 'Apabila tidak memenuhi janji ini, kami siap menerima sanksi sesuai dengan peraturan perundang-undangan yang berlaku.'
})
</script>
