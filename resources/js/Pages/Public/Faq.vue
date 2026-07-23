<template>
  <Head title="Pertanyaan yang Sering Diajukan (FAQ) — Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="space-y-8 text-left max-w-5xl mx-auto">
      
      <!-- Header section -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
          Pertanyaan yang Sering Diajukan (FAQ)
        </h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Temukan jawaban cepat atas pertanyaan umum seputar pelayanan kependudukan dan pencatatan sipil Kabupaten Dompu.
        </p>
      </div>

      <!-- Filters & Search panel -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <!-- Category filter pills -->
        <div class="flex flex-wrap gap-1.5 items-center flex-1">
          <button 
            @click="setCategory(null)"
            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold border transition"
            :class="[
              !activeCategory 
                ? 'bg-primary-600 text-white border-primary-500 shadow-sm shadow-primary-500/10' 
                : 'bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400'
            ]"
          >
            Semua FAQ
          </button>

          <button 
            v-for="cat in categories" 
            :key="cat"
            @click="setCategory(cat)"
            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold border transition"
            :class="[
              activeCategory === cat 
                ? 'bg-primary-600 text-white border-primary-500 shadow-sm' 
                : 'bg-gray-50 dark:bg-zinc-800 hover:bg-gray-100 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400'
            ]"
          >
            {{ cat }}
          </button>
        </div>

        <!-- Search input box -->
        <div class="relative w-full sm:w-72">
          <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input 
            v-model="searchQuery" 
            @input="applySearch"
            type="text" 
            placeholder="Cari kata kunci..." 
            class="w-full pl-9 pr-8 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" 
          />
          <button v-if="searchQuery" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <X class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

      <!-- Result count & reset action -->
      <div class="flex items-center justify-between text-xs text-gray-400 font-semibold px-1">
        <span>{{ filteredFaqs.length }} pertanyaan ditemukan</span>
        <button v-if="activeCategory || searchQuery" @click="resetFilters" class="text-rose-500 hover:underline flex items-center gap-1 font-bold">
          <X class="w-3.5 h-3.5" /> Reset Filter
        </button>
      </div>

      <!-- Empty state -->
      <div v-if="!filteredFaqs.length" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-12 text-center space-y-3">
        <HelpCircle class="w-12 h-12 text-gray-300 dark:text-zinc-700 mx-auto" />
        <h3 class="font-bold text-sm text-gray-700 dark:text-zinc-300">Tidak ada FAQ yang ditemukan</h3>
        <p class="text-xs text-gray-400 max-w-sm mx-auto">Coba gunakan kata kunci pencarian lain atau pilih kategori FAQ di atas.</p>
        <button @click="resetFilters" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl transition">
          Lihat Semua FAQ
        </button>
      </div>

      <!-- FAQ Accordion grouped by category -->
      <div v-else class="space-y-8">
        <div v-for="(group, category) in groupedFaqs" :key="category" class="space-y-3">
          <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 pt-2">
            <Tag class="w-3.5 h-3.5" />
            <span>{{ category }}</span>
          </div>

          <div class="space-y-3">
            <div 
              v-for="(faq, idx) in group" 
              :key="faq.id"
              class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden transition duration-200"
              :class="{ 'ring-2 ring-primary-500/20 border-primary-500/40': openItems.has(faq.id) }"
            >
              <button 
                @click="toggle(faq.id)" 
                class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50/50 dark:hover:bg-zinc-800/40 transition"
              >
                <div class="flex items-center gap-3.5 pr-4">
                  <span 
                    class="w-7 h-7 rounded-xl text-xs font-bold flex items-center justify-center shrink-0 transition"
                    :class="openItems.has(faq.id) ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400'"
                  >
                    {{ idx + 1 }}
                  </span>
                  <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-sm leading-snug">
                    {{ faq.question }}
                  </h4>
                </div>
                <ChevronDown 
                  class="w-4 h-4 text-gray-400 shrink-0 transition duration-300" 
                  :class="{ 'rotate-180 text-primary-600': openItems.has(faq.id) }" 
                />
              </button>

              <transition name="accordion">
                <div v-if="openItems.has(faq.id)" class="px-6 pb-5 pt-2 text-xs text-gray-600 dark:text-zinc-300 leading-relaxed border-t border-gray-50 dark:border-zinc-800/60 font-medium">
                  <div class="pl-10 space-y-2 prose dark:prose-invert max-w-none text-xs" v-html="faq.answer"></div>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom CTA Card -->
      <div class="bg-gradient-to-br from-primary-600 via-indigo-600 to-slate-900 text-white rounded-3xl p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-1.5 text-center md:text-left">
          <span class="px-2.5 py-0.5 bg-white/20 text-[9px] font-bold uppercase rounded-md tracking-wider">
            Bantuan & Pengaduan
          </span>
          <h3 class="text-lg font-black tracking-tight">Tidak menemukan jawaban yang Anda cari?</h3>
          <p class="text-xs text-white/80 max-w-xl leading-relaxed">
            Sampaikan pertanyaan atau keluhan pelayanan kependudukan Anda secara online melalui Sistem Pengaduan Rakyat (LAPOR!).
          </p>
        </div>
        <div class="flex gap-3 shrink-0">
          <Link :href="route('public.contact')" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 border border-white/30 text-white text-xs font-bold rounded-xl transition">
            Hubungi Kami
          </Link>
          <Link :href="route('public.complaint.create')" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-gray-900 text-xs font-black rounded-xl transition shadow-lg shadow-amber-500/20 active:scale-95">
            Kirim Pengaduan →
          </Link>
        </div>
      </div>

    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, ChevronDown, HelpCircle, Tag, X } from '@lucide/vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  faqs: Array,
  categories: Array,
  filters: Object,
});

const searchQuery = ref(props.filters?.search ?? '');
const activeCategory = ref(props.filters?.category ?? null);
const openItems = reactive(new Set());

// Group FAQs by category
const filteredFaqs = computed(() => {
  return props.faqs.filter(f => {
    if (activeCategory.value && f.category !== activeCategory.value) return false;
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const matchQ = f.question.toLowerCase().includes(q);
      const matchA = f.answer.toLowerCase().includes(q);
      return matchQ || matchA;
    }
    return true;
  });
});

const groupedFaqs = computed(() => {
  const grouped = {};
  filteredFaqs.value.forEach(faq => {
    const cat = faq.category || 'Umum';
    if (!grouped[cat]) grouped[cat] = [];
    grouped[cat].push(faq);
  });
  return grouped;
});

function toggle(id) {
  if (openItems.has(id)) {
    openItems.delete(id);
  } else {
    openItems.add(id);
  }
}

function setCategory(cat) {
  activeCategory.value = cat;
}

function applySearch() {
  // Client side reactive search filtering
}

function clearSearch() {
  searchQuery.value = '';
}

function resetFilters() {
  searchQuery.value = '';
  activeCategory.value = null;
}
</script>

<style scoped>
.accordion-enter-active, .accordion-leave-active {
  transition: all 0.25s ease-out;
  max-height: 500px;
  opacity: 1;
}
.accordion-enter-from, .accordion-leave-to {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
}
</style>
