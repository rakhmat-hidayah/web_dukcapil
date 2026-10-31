<template>
  <div class="w-full py-4 mb-8">
    <div class="flex items-center justify-between max-w-3xl mx-auto px-4 relative">
      <!-- Background Connecting Line -->
      <div class="absolute left-10 right-10 top-1/2 -translate-y-1/2 h-1 bg-slate-200 dark:bg-slate-700 -z-0">
        <div
          class="h-full bg-blue-600 transition-all duration-500 rounded-full"
          :style="{ width: progressPercent + '%' }"
        ></div>
      </div>

      <!-- Step Items -->
      <div
        v-for="(step, idx) in steps"
        :key="idx"
        class="flex flex-col items-center relative z-10 text-center"
      >
        <div
          :class="[
            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-md',
            currentStep > idx + 1
              ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/40'
              : currentStep === idx + 1
              ? 'bg-blue-600 text-white ring-4 ring-blue-200 dark:ring-blue-800 scale-110'
              : 'bg-white dark:bg-slate-800 text-slate-400 dark:text-slate-500 border-2 border-slate-200 dark:border-slate-700'
          ]"
        >
          <svg
            v-if="currentStep > idx + 1"
            class="w-5 h-5 text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
          <span v-else>{{ idx + 1 }}</span>
        </div>
        <p
          :class="[
            'text-xs mt-2 font-medium transition-colors hidden sm:block max-w-[90px]',
            currentStep >= idx + 1
              ? 'text-blue-600 dark:text-blue-400 font-semibold'
              : 'text-slate-400 dark:text-slate-500'
          ]"
        >
          {{ step.label }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentStep: {
    type: Number,
    default: 1, // 1 to 4
  },
});

const steps = [
  { label: 'Data Pemohon' },
  { label: 'Unggah Dokumen' },
  { label: 'Pra-Verifikasi' },
  { label: 'Tiket Antrian' },
];

const progressPercent = computed(() => {
  if (props.currentStep <= 1) return 0;
  if (props.currentStep === 2) return 33;
  if (props.currentStep === 3) return 66;
  return 100;
});
</script>
