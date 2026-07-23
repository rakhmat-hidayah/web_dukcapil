<template>
  <Head :title="news.title" />

  <PublicLayout>
    <div class="space-y-8 text-left max-w-4xl mx-auto">
      
      <!-- Back Link -->
      <div>
        <Link :href="route('public.news.index')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
          ← Kembali ke Berita
        </Link>
      </div>

      <!-- Main Article Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-[2rem] p-8 md:p-12 shadow-sm space-y-6">
        <!-- Title and Metadata -->
        <div class="space-y-3">
          <span 
            v-if="news.category" 
            class="text-[9px] font-bold font-mono uppercase tracking-wider border px-1.5 py-0.5 rounded"
            :style="{ 
              color: news.category.color, 
              backgroundColor: news.category.color + '15',
              borderColor: news.category.color + '30'
            }"
          >
            {{ news.category.name }}
          </span>
          
          <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-zinc-50 leading-tight">
            {{ news.title }}
          </h1>

          <div class="flex items-center gap-4 text-[10px] text-gray-400 font-semibold border-b border-gray-50 dark:border-zinc-800 pb-4 mt-4">
            <span>Penulis: {{ news.author ? news.author.name : 'Admin' }}</span>
            <span>•</span>
            <span>Rilis: {{ formatDate(news.published_at) }}</span>
            <span>•</span>
            <span>Dibaca: {{ news.view_count || 0 }} kali</span>
          </div>
        </div>

        <!-- Cover Image -->
        <div 
          v-if="news.thumbnail" 
          class="h-64 sm:h-96 rounded-2xl overflow-hidden border border-gray-100 dark:border-zinc-800"
        >
          <img :src="`/storage/${news.thumbnail}`" class="w-full h-full object-cover" alt="Cover Image" />
        </div>

        <!-- Article Content -->
        <article 
          class="prose prose-xs dark:prose-invert max-w-none text-gray-700 dark:text-zinc-300 leading-relaxed space-y-4"
          v-html="formattedContent"
        ></article>

        <!-- Tags -->
        <div v-if="news.tags && news.tags.length > 0" class="pt-4 flex flex-wrap gap-1.5 border-t border-gray-50 dark:border-zinc-800">
          <span 
            v-for="tag in news.tags" 
            :key="tag.id"
            class="px-2 py-0.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 font-mono text-[9px] rounded uppercase font-semibold text-gray-500"
          >
            #{{ tag.name }}
          </span>
        </div>
      </div>

      <!-- Related Articles Section -->
      <div class="space-y-6 pt-6" v-if="related && related.length > 0">
        <h3 class="text-lg font-black text-gray-900 dark:text-zinc-50 tracking-tight">
          📰 Berita Terkait
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Link 
            v-for="item in related" 
            :key="item.id"
            :href="route('public.news.show', item.slug)"
            class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 shadow-sm overflow-hidden flex flex-col justify-between group"
          >
            <div class="space-y-2">
              <span 
                v-if="item.category" 
                class="text-[8px] font-bold font-mono uppercase tracking-wider border px-1.5 py-0.5 rounded"
                :style="{ 
                  color: item.category.color, 
                  backgroundColor: item.category.color + '15',
                  borderColor: item.category.color + '30'
                }"
              >
                {{ item.category.name }}
              </span>
              <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-xs line-clamp-2 leading-snug group-hover:text-primary-600 transition">
                {{ item.title }}
              </h4>
            </div>
            <p class="text-[9px] text-gray-400 font-semibold mt-4">
              {{ formatDate(item.published_at) }}
            </p>
          </Link>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  news: Object,
  related: Array,
});

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formattedContent = computed(() => {
  if (!props.news?.content && !props.news?.excerpt) return '';
  
  let html = props.news?.content || '';

  // Remove <header>...</header> (WordPress meta)
  html = html.replace(/<header[^>]*>[\s\S]*?<\/header>/gi, '');

  // Remove empty paragraphs like <p></p>, <p><br></p>, <p><strong><br></strong></p>, <p>&nbsp;</p>
  const emptyPRegex = /<p[^>]*>(?:\s|&nbsp;|<br\s*\/?>|<strong>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/strong>|<b>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/b>|<span>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/span>)*<\/p>/gi;
  
  let prev;
  do {
    prev = html;
    html = html.replace(emptyPRegex, '');
  } while (html !== prev);

  let hasVideo = false;

  // 1. YouTube & Google Drive media oembeds
  const mediaPattern = /<figure[^>]*class="media"[^>]*>\s*<oembed[^>]+url=["']([^"']+)["'][^>]*>(?:<\/oembed>)?\s*<\/figure>|<oembed[^>]+url=["']([^"']+)["'][^>]*>(?:<\/oembed>)?/gi;
  
  html = html.replace(mediaPattern, (match, url1, url2) => {
    const url = url1 || url2;
    if (!url) return match;
    
    // YouTube
    const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (ytMatch && ytMatch[1]) {
      hasVideo = true;
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black"><iframe src="https://www.youtube-nocookie.com/embed/${ytMatch[1]}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen title="Video YouTube"></iframe></div>`;
    }

    // Google Drive
    const driveMatch = url.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (driveMatch && driveMatch[1]) {
      hasVideo = true;
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black border border-gray-200 dark:border-zinc-800"><iframe src="https://drive.google.com/file/d/${driveMatch[1]}/preview" class="w-full h-full border-0" allow="autoplay" allowfullscreen title="Video Google Drive"></iframe></div>`;
    }

    return match;
  });

  // 2. Direct Google Drive URLs in plain text or <a> links
  const directDrivePattern = /(?:<p[^>]*>\s*)?(?:<a[^>]*href=["'])?https?:\/\/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)(?:[^\s<"']*)?(?:["'][^>]*>.*?<\/a>)?(?:\s*<\/p>)?/gi;
  html = html.replace(directDrivePattern, (match, driveId) => {
    if (driveId) {
      hasVideo = true;
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black border border-gray-200 dark:border-zinc-800"><iframe src="https://drive.google.com/file/d/${driveId}/preview" class="w-full h-full border-0" allow="autoplay" allowfullscreen title="Video Google Drive"></iframe></div>`;
    }
    return match;
  });

  // 3. Fallback: If no video found in content yet, check excerpt for Google Drive link
  if (!hasVideo && props.news?.excerpt) {
    const excerptDriveMatch = props.news.excerpt.match(/https?:\/\/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/i);
    if (excerptDriveMatch && excerptDriveMatch[1]) {
      const driveId = excerptDriveMatch[1];
      const playerHtml = `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black border border-gray-200 dark:border-zinc-800"><iframe src="https://drive.google.com/file/d/${driveId}/preview" class="w-full h-full border-0" allow="autoplay" allowfullscreen title="Video Google Drive"></iframe></div>`;
      html = playerHtml + html;
    }
  }

  return html;
});

onMounted(() => {
  // Hide broken images and their parent containers inside article
  document.querySelectorAll('article img').forEach(img => {
    img.onerror = () => {
      img.style.display = 'none';
      const parent = img.closest('figure, p');
      if (parent && !parent.textContent.trim()) {
        parent.style.display = 'none';
      }
    };
  });
});
</script>
