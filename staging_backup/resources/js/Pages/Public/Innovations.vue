<template>
  <Head title="Inovasi Pelayanan Publik Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="space-y-8 text-left">
      <!-- Title -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Inovasi Pelayanan Kami</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Daftar terobosan dan program inovasi pelayanan adminduk prima yang diselenggarakan oleh Disdukcapil Kabupaten Dompu.
        </p>
      </div>

      <!-- Innovations Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-if="innovations.length === 0" class="col-span-full text-center py-12 text-gray-450 text-xs">
          Belum ada inovasi yang dipublikasikan.
        </div>

        <Link 
          v-for="item in innovations" 
          :key="item.id"
          :href="route('public.innovations.show', item.slug)"
          class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between hover:shadow-md hover:border-gray-200 dark:hover:border-zinc-700 transition group h-64"
        >
          <div class="space-y-3">
            <span class="text-4xl block transition group-hover:scale-110 duration-200 origin-left">{{ item.icon || '🚐' }}</span>
            <h4 class="font-extrabold text-gray-800 dark:text-zinc-50 text-sm leading-snug group-hover:text-primary-600 transition">
              {{ item.title }}
            </h4>
            <p class="text-xs text-gray-400 leading-relaxed line-clamp-3">
              {{ item.description }}
            </p>
          </div>

          <div class="border-t border-gray-50 dark:border-zinc-800 pt-3 flex justify-between items-center text-[10px] text-gray-400 font-semibold font-mono">
            <span>Kabupaten Dompu</span>
            <span class="text-primary-600 font-sans">Selengkapnya →</span>
          </div>
        </Link>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  innovations: Array,
});
</script>
