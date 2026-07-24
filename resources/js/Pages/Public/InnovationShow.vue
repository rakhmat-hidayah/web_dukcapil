<template>
  <Head :title="`Inovasi: ${innovation.title}`" />

  <PublicLayout>
    <div class="space-y-6 text-left max-w-5xl mx-auto">
      
      <!-- Breadcrumb -->
      <div>
        <Link :href="route('public.innovations.index')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
          ← Kembali ke Inovasi Pelayanan
        </Link>
      </div>

      <!-- Main Columns -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Details & Content (Col span 2) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-6">
            
            <!-- Header detail -->
            <div class="space-y-2">
              <span class="text-4xl block">{{ innovation.icon || '🚐' }}</span>
              <h1 class="text-2xl font-extrabold text-gray-950 dark:text-zinc-50 leading-tight">
                {{ innovation.title }}
              </h1>
              <p class="text-xs text-gray-400 mt-1" v-if="innovation.description">
                {{ innovation.description }}
              </p>
            </div>

            <!-- YouTube Video Embed -->
            <div v-if="innovation.youtube_url" class="relative pb-[56.25%] h-0 rounded-2xl overflow-hidden border border-gray-100 dark:border-zinc-800/80 shadow-sm">
              <iframe 
                :src="innovation.youtube_url"
                class="absolute top-0 left-0 w-full h-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
            </div>

            <!-- Main rich HTML text content explanation -->
            <div class="prose prose-xs dark:prose-invert max-w-none text-gray-700 dark:text-zinc-300 leading-relaxed space-y-4">
              <h3 class="text-sm font-black text-gray-900 dark:text-zinc-50 tracking-tight">Mengenal Program Inovasi: {{ innovation.title }}</h3>
              <div v-html="innovation.content" class="innovation-detail-content"></div>
            </div>
          </div>
        </div>

        <!-- Right: Other Innovations sidebar -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-400">Inovasi Lainnya</h3>
            
            <div class="flex flex-col gap-2">
              <Link 
                v-for="other in otherInnovations" 
                :key="other.id"
                :href="route('public.innovations.show', other.slug)"
                class="p-3.5 bg-gray-50 dark:bg-zinc-850 hover:bg-gray-100 dark:hover:bg-zinc-800 border border-transparent hover:border-gray-200 dark:hover:border-zinc-700 rounded-2xl flex items-center gap-3 transition"
              >
                <span class="text-2xl shrink-0">{{ other.icon || '🚐' }}</span>
                <span class="font-bold text-xs text-gray-700 dark:text-zinc-200 line-clamp-1 leading-snug">
                  {{ other.title }}
                </span>
              </Link>
            </div>
          </div>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  innovation: Object,
  otherInnovations: Array,
});
</script>

<style>
/* Style lists and paragraphs inside injected content HTML */
.innovation-detail-content p {
  margin-bottom: 1rem;
  font-size: 0.8rem;
  line-height: 1.625;
}
.innovation-detail-content ol, .innovation-detail-content ul {
  list-style-type: decimal;
  padding-left: 1.25rem;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 0.8rem;
  line-height: 1.625;
}
.innovation-detail-content li {
  margin-bottom: 0.35rem;
}
.innovation-detail-content strong {
  font-weight: 700;
}
</style>
