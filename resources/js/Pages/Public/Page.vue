<template>
  <Head :title="page.title" />

  <PublicLayout>
    <div class="space-y-6 text-left max-w-4xl mx-auto">
      <!-- Page cover/OG image if present -->
      <div 
        v-if="page.og_image" 
        class="h-64 sm:h-80 rounded-[2rem] overflow-hidden border border-gray-100 dark:border-zinc-800 shadow-md"
      >
        <img :src="`/storage/${page.og_image}`" class="w-full h-full object-cover" alt="Banner Halaman" />
      </div>

      <!-- Main card container -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-[2rem] p-8 md:p-12 shadow-sm space-y-6">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-zinc-50 leading-tight">
            {{ page.title }}
          </h1>
          <p class="text-[10px] text-gray-400 font-semibold mt-2">
            Terbit: {{ formatDate(page.published_at) }}
          </p>
        </div>

        <!-- Render HTML content safely -->
        <article 
          class="prose prose-xs dark:prose-invert max-w-none text-gray-700 dark:text-zinc-300 leading-relaxed space-y-4"
          v-html="formattedContent"
        ></article>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  page: Object,
});

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formattedContent = computed(() => {
  if (!props.page?.content) return '';
  
  let html = props.page.content;
  html = html.replace(/<header[^>]*>[\s\S]*?<\/header>/gi, '');

  const emptyPRegex = /<p[^>]*>(?:\s|&nbsp;|<br\s*\/?>|<strong>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/strong>|<b>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/b>|<span>\s*(?:&nbsp;|<br\s*\/?>)*\s*<\/span>)*<\/p>/gi;
  
  let prev;
  do {
    prev = html;
    html = html.replace(emptyPRegex, '');
  } while (html !== prev);

  // 1. YouTube & Google Drive media oembeds
  const mediaPattern = /<figure[^>]*class="media"[^>]*>\s*<oembed[^>]+url=["']([^"']+)["'][^>]*>(?:<\/oembed>)?\s*<\/figure>|<oembed[^>]+url=["']([^"']+)["'][^>]*>(?:<\/oembed>)?/gi;
  
  html = html.replace(mediaPattern, (match, url1, url2) => {
    const url = url1 || url2;
    if (!url) return match;
    
    // YouTube
    const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (ytMatch && ytMatch[1]) {
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black"><iframe src="https://www.youtube-nocookie.com/embed/${ytMatch[1]}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen title="Video YouTube"></iframe></div>`;
    }

    // Google Drive
    const driveMatch = url.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (driveMatch && driveMatch[1]) {
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black border border-gray-200 dark:border-zinc-800"><iframe src="https://drive.google.com/file/d/${driveMatch[1]}/preview" class="w-full h-full border-0" allow="autoplay" allowfullscreen title="Video Google Drive"></iframe></div>`;
    }

    return match;
  });

  // 2. Direct Google Drive URLs in plain text or <a> links
  const directDrivePattern = /(?:<p[^>]*>\s*)?(?:<a[^>]*href=["'])?https?:\/\/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)(?:[^\s<"']*)?(?:["'][^>]*>.*?<\/a>)?(?:\s*<\/p>)?/gi;
  html = html.replace(directDrivePattern, (match, driveId) => {
    if (driveId) {
      return `<div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-lg my-6 bg-black border border-gray-200 dark:border-zinc-800"><iframe src="https://drive.google.com/file/d/${driveId}/preview" class="w-full h-full border-0" allow="autoplay" allowfullscreen title="Video Google Drive"></iframe></div>`;
    }
    return match;
  });

  return html;
});

onMounted(() => {
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
