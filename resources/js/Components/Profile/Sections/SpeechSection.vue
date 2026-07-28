<template>
  <section id="speech" class="py-6 mb-8 scroll-mt-24">
    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100 dark:border-zinc-800">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
        <div class="md:col-span-4 text-center">
          <div class="relative inline-block">
            <div class="w-44 h-44 md:w-52 md:h-52 rounded-2xl overflow-hidden bg-gray-100 dark:bg-zinc-800 border-4 border-white dark:border-zinc-700 shadow-lg mx-auto flex items-center justify-center">
              <img 
                :src="leaderPhotoUrl" 
                :alt="leaderName"
                class="w-full h-full object-cover object-top"
                @error="$event.target.src = '/images/kepala_dinas.jpg'"
              />
            </div>
          </div>
          <h3 class="text-lg font-extrabold text-gray-900 dark:text-zinc-50 mt-4">{{ leaderName }}</h3>
          <p class="text-xs text-primary-600 dark:text-primary-400 font-bold">{{ leaderTitle }}</p>
        </div>
        <div class="md:col-span-8 space-y-3">
          <span class="text-xs font-bold text-primary-600 dark:text-primary-400 uppercase tracking-widest block">Sambutan Pimpinan</span>
          <h2 class="text-xl md:text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight leading-tight">{{ speechTitle }}</h2>
          <div class="text-gray-600 dark:text-zinc-300 text-xs md:text-sm leading-relaxed space-y-3 font-medium">
            <template v-if="speechParagraphs.length > 0">
              <p v-for="(p, idx) in speechParagraphs" :key="idx">{{ p }}</p>
            </template>
            <template v-else>
              <p>Assalamu’alaikum Warahmatullahi Wabarakatuh,</p>
              <p>Selamat datang di portal resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. Kehadiran portal ini merupakan bagian dari komitmen kami dalam mewujudkan keterbukaan informasi publik dan kemudahan akses layanan administrasi kependudukan bagi seluruh lapisan masyarakat.</p>
              <p>Seluruh jajaran Dinas Dukcapil Kabupaten Dompu siap melayani Anda dengan semangat integritas, profesionalisme, dan tanpa pungutan biaya apapun (Rp 0,-).</p>
            </template>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
  setting: Object,
})

const ws = computed(() => page.props.website_settings || {})

const leaderName = computed(() => {
  return ws.value.leader_name || props.setting?.leader_name || 'Belum dikonfigurasi'
})

const leaderTitle = computed(() => {
  return ws.value.leader_title || props.setting?.leader_title || 'Kepala Dinas Dukcapil Kabupaten Dompu'
})

const speechTitle = computed(() => {
  return ws.value.leader_speech_title || props.setting?.speech_title || '"Pelayanan Kependudukan Yang Transparan & Membahagiakan Masyarakat"'
})

const leaderPhotoUrl = computed(() => {
  const photo = ws.value.leader_photo || props.setting?.leader_photo
  if (!photo) return '/images/kepala_dinas.jpg'
  // Full URL or /images/ path → gunakan langsung
  if (photo.startsWith('http://') || photo.startsWith('https://') || photo.startsWith('/images/')) return photo
  // Sudah punya /storage/ prefix → jangan tambah lagi
  if (photo.startsWith('/storage/')) return photo
  // Relative path (misal: leader/xxx.png) → tambah /storage/
  return `/storage/${photo}`
})

const speechParagraphs = computed(() => {
  const content = ws.value.leader_speech_content || props.setting?.speech_content
  if (!content) return []
  return content.split('\n').map(s => s.trim()).filter(Boolean)
})
</script>
