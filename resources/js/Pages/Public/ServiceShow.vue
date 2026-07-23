<template>
  <Head :title="`Persyaratan: ${service.title}`" />

  <PublicLayout>
    <div class="space-y-6 text-left max-w-5xl mx-auto">
      
      <!-- Breadcrumb -->
      <div>
        <Link :href="route('public.services.index')" class="text-xs font-semibold text-primary-600 hover:underline flex items-center gap-1">
          ← Kembali ke Persyaratan Layanan
        </Link>
      </div>

      <!-- Main Columns -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Requirements & Details (Col span 2) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-6">
            
            <!-- Header detail -->
            <div class="flex items-start justify-between gap-4">
              <div class="space-y-2">
                <span class="text-4xl block">{{ service.icon || '📝' }}</span>
                <h1 class="text-2xl font-extrabold text-gray-950 dark:text-zinc-50 leading-tight">
                  {{ service.title }}
                </h1>
                <p class="text-xs text-gray-400 mt-1" v-if="service.description">
                  {{ service.description }}
                </p>
              </div>

              <!-- Print Action button -->
              <button 
                @click="printRequirements"
                class="px-3.5 py-2 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-750 text-gray-700 dark:text-zinc-200 text-xs font-bold rounded-xl flex items-center gap-1.5 transition shrink-0"
              >
                <Printer class="w-3.5 h-3.5" />
                Cetak
              </button>
            </div>

            <!-- Metadata info cards -->
            <div class="grid grid-cols-2 gap-4 border-y border-gray-50 dark:border-zinc-800 py-4 font-mono text-xs">
              <div class="p-3 bg-gray-50 dark:bg-zinc-850 rounded-2xl">
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mb-1">Estimasi Waktu Proses</p>
                <span class="font-extrabold text-gray-700 dark:text-zinc-200 font-sans">{{ service.processing_time }}</span>
              </div>
              <div class="p-3 bg-gray-50 dark:bg-zinc-850 rounded-2xl">
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mb-1">Tarif / Biaya Layanan</p>
                <span class="font-extrabold text-emerald-600 dark:text-emerald-450 font-sans">{{ service.cost }}</span>
              </div>
            </div>

            <!-- Bullet point requirements html content -->
            <div class="prose prose-xs dark:prose-invert max-w-none text-gray-700 dark:text-zinc-300 leading-relaxed space-y-4">
              <h3 class="text-sm font-black text-gray-900 dark:text-zinc-50 tracking-tight">📝 Dokumen Persyaratan Yang Harus Dipersiapkan:</h3>
              <div v-html="service.requirements" class="requirements-content"></div>
            </div>
          </div>
        </div>

        <!-- Right: Other Services list sidebar -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-400">Layanan Kependudukan Lainnya</h3>
            
            <div class="flex flex-col gap-2">
              <Link 
                v-for="other in otherServices" 
                :key="other.id"
                :href="route('public.services.show', other.slug)"
                class="p-3.5 bg-gray-50 dark:bg-zinc-850 hover:bg-gray-100 dark:hover:bg-zinc-800 border border-transparent hover:border-gray-200 dark:hover:border-zinc-700 rounded-2xl flex items-center gap-3 transition"
              >
                <span class="text-2xl shrink-0">{{ other.icon || '📝' }}</span>
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
import { Printer } from '@lucide/vue';

const props = defineProps({
  service: Object,
  otherServices: Array,
});

const printRequirements = () => {
  window.print();
};
</script>

<style>
/* Style lists inside injected requirements HTML to match layout */
.requirements-content ul {
  list-style-type: disc;
  padding-left: 1.25rem;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 0.8rem;
  line-height: 1.625;
}
.requirements-content li {
  margin-bottom: 0.35rem;
}
.requirements-content h3 {
  font-size: 0.875rem;
  font-weight: 800;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}
</style>
