<template>
  <Head title="Survei Kepuasan Masyarakat (IKM) — Disdukcapil Dompu" />

  <PublicLayout>
    <div class="space-y-12 text-left max-w-7xl mx-auto">
      
      <!-- 1. HERO HEADER -->
      <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 border border-blue-900/40 p-8 md:p-12 text-white shadow-xl shadow-blue-950/20">
        <!-- Background identity watermark -->
        <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

        <div class="relative z-10 space-y-4 max-w-3xl">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="px-3 py-1 bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider rounded-lg shadow-sm">
              PermenPANRB No. 14 Tahun 2017
            </span>
            <span class="px-3 py-1 bg-white/10 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg backdrop-blur-sm border border-white/10">
              Transparan &amp; Akuntabel
            </span>
          </div>

          <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-tight">
            Survei Kepuasan Masyarakat <span class="text-amber-400">(IKM)</span>
          </h1>

          <p class="text-xs md:text-sm text-blue-100/90 leading-relaxed font-medium">
            Laporan pengukuran secara berkala atas tingkat kepuasan masyarakat terhadap mutu dan kualitas pelayanan administrasi kependudukan di Kabupaten Dompu.
          </p>

          <div class="pt-2 flex items-center gap-4 flex-wrap">
            <button 
              @click="openSurveyModal"
              class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black rounded-xl transition shadow-lg shadow-amber-500/25 active:scale-95 flex items-center gap-2 cursor-pointer"
            >
              <span>Isi Survei Sekarang</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>

            <a 
              href="#hasil-ikm" 
              class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white text-xs font-extrabold rounded-xl transition border border-white/15"
            >
              Lihat Statistik &amp; Grafik ↓
            </a>
          </div>
        </div>
      </div>

      <!-- 2. CURRENT IKM SCORE CARD & CATEGORY BADGE -->
      <div v-if="stats && categoryInfo" id="hasil-ikm" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Main Score Highlight Card -->
        <div class="lg:col-span-8 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
          <div class="space-y-2 text-center md:text-left">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 font-mono">Nilai Indeks Terbaru</span>
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
              {{ activeSurvey?.title || 'Survei Kepuasan Masyarakat' }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-zinc-400 font-medium">
              Berdasarkan {{ stats.total_respondents }} responden terverifikasi.
            </p>
          </div>

          <div class="flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-zinc-800/80 rounded-2xl border border-gray-100 dark:border-zinc-700 min-w-[14rem]">
            <span class="text-5xl font-black text-primary-600 dark:text-primary-400 font-mono tracking-tight">
              {{ stats.ikm_value }}
            </span>
            <span class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-wider">Skala (25 - 100)</span>
            
            <div class="mt-3 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border shadow-sm"
              :class="{
                'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 border-emerald-200': categoryInfo.category === 'A',
                'bg-blue-50 dark:bg-blue-950/60 text-blue-600 border-blue-200': categoryInfo.category === 'B',
                'bg-amber-50 dark:bg-amber-950/60 text-amber-600 border-amber-200': categoryInfo.category === 'C',
                'bg-rose-50 dark:bg-rose-950/60 text-rose-600 border-rose-200': categoryInfo.category === 'D'
              }"
            >
              Mutu {{ categoryInfo.category }} — {{ categoryInfo.label }}
            </div>
          </div>
        </div>

        <!-- Metric Cards -->
        <div class="lg:col-span-4 grid grid-cols-2 gap-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Total Responden</span>
            <span class="text-3xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-2">{{ stats.total_respondents }}</span>
            <span class="text-[11px] text-gray-500 font-medium mt-1">Pemohon Pelayanan</span>
          </div>

          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Response Rate</span>
            <span class="text-3xl font-black text-amber-500 font-mono mt-2">{{ stats.response_rate }}%</span>
            <span class="text-[11px] text-gray-500 font-medium mt-1">Capaian Target</span>
          </div>
        </div>
      </div>

      <!-- 3. SURVEY DESCRIPTION & METHODOLOGY -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-4">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-primary-600 dark:text-primary-400">
          Metodologi &amp; Klasifikasi Kategori PermenPANRB
        </h3>
        <p class="text-xs text-gray-600 dark:text-zinc-300 leading-relaxed font-medium">
          Pengukuran Indeks Kepuasan Masyarakat (IKM) dilaksanakan secara periodik sesuai dengan **Peraturan Menteri Pendayagunaan Aparatur Negara dan Reformasi Birokrasi (PermenPANRB) Nomor 14 Tahun 2017**. Nilai IKM diklasifikasikan ke dalam 4 tingkatan mutu pelayanan:
        </p>

        <!-- Category Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
          <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-xs font-black text-emerald-800 dark:text-emerald-300">Kategori A</span>
              <span class="text-[10px] font-extrabold text-emerald-700 dark:text-emerald-400 font-mono">88.31 - 100.00</span>
            </div>
            <p class="text-xs font-black text-emerald-900 dark:text-emerald-200">Sangat Baik</p>
          </div>

          <div class="p-4 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-xs font-black text-blue-800 dark:text-blue-300">Kategori B</span>
              <span class="text-[10px] font-extrabold text-blue-700 dark:text-blue-400 font-mono">76.61 - 88.30</span>
            </div>
            <p class="text-xs font-black text-blue-900 dark:text-blue-200">Baik</p>
          </div>

          <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-xs font-black text-amber-800 dark:text-amber-300">Kategori C</span>
              <span class="text-[10px] font-extrabold text-amber-700 dark:text-amber-400 font-mono">65.00 - 76.60</span>
            </div>
            <p class="text-xs font-black text-amber-900 dark:text-amber-200">Kurang Baik</p>
          </div>

          <div class="p-4 rounded-2xl bg-rose-50/60 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-xs font-black text-rose-800 dark:text-rose-300">Kategori D</span>
              <span class="text-[10px] font-extrabold text-rose-700 dark:text-rose-400 font-mono">&lt; 65.00</span>
            </div>
            <p class="text-xs font-black text-rose-900 dark:text-rose-200">Tidak Baik</p>
          </div>
        </div>
      </div>

      <!-- 4. SURVEY RESULT CHARTS & BREAKDOWN -->
      <div v-if="chartData" class="space-y-6">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
          <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
            Visualisasi &amp; Breakdown Unsur Pelayanan
          </h3>
          <span class="text-xs text-gray-500 font-medium">Grafik Interaktif IKM</span>
        </div>

        <!-- Breakdown per Unsur Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm overflow-hidden">
          <h4 class="text-xs font-extrabold text-gray-800 dark:text-zinc-200 mb-4 uppercase tracking-wider">
            Rincian Nilai Indeks Per Unsur Pelayanan (9 Unsur PermenPANRB)
          </h4>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-100 dark:border-zinc-800 text-[11px] font-black uppercase text-gray-400 font-mono">
                  <th class="py-3 px-4">No</th>
                  <th class="py-3 px-4">Unsur Pelayanan</th>
                  <th class="py-3 px-4 text-center">Rata-Rata Rating (1-4)</th>
                  <th class="py-3 px-4 text-center">Nilai IKM Unsur (25-100)</th>
                  <th class="py-3 px-4 text-center">Kategori</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-zinc-800 text-xs font-medium">
                <tr v-for="(item, idx) in chartData.breakdown" :key="idx" class="hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                  <td class="py-3 px-4 font-mono font-bold text-gray-400">U{{ idx + 1 }}</td>
                  <td class="py-3 px-4 font-bold text-gray-800 dark:text-zinc-200">{{ item.category }}</td>
                  <td class="py-3 px-4 text-center font-mono font-bold text-gray-700 dark:text-zinc-300">{{ item.average_rating }}</td>
                  <td class="py-3 px-4 text-center font-mono font-black text-primary-600 dark:text-primary-400">{{ item.ikm_unsur }}</td>
                  <td class="py-3 px-4 text-center">
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase"
                      :class="item.ikm_unsur >= 88.31 ? 'bg-emerald-50 text-emerald-700' : (item.ikm_unsur >= 76.61 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700')"
                    >
                      {{ item.ikm_unsur >= 88.31 ? 'A (Sangat Baik)' : (item.ikm_unsur >= 76.61 ? 'B (Baik)' : 'C (Kurang Baik)') }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 5. IMPROVEMENT RECOMMENDATIONS & FOLLOW-UP ACTIONS -->
      <div v-if="activeSurvey && ((activeSurvey.recommendations && activeSurvey.recommendations.length > 0) || (activeSurvey.follow_up_actions && activeSurvey.follow_up_actions.length > 0))" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Recommendations -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
          <h3 class="text-xs font-extrabold uppercase tracking-wider text-primary-600 dark:text-primary-400">
            Rekomendasi Perbaikan Mutu Pelayanan
          </h3>

          <div class="space-y-3">
            <div v-for="rec in activeSurvey.recommendations" :key="rec.id" class="p-4 rounded-2xl bg-gray-50 dark:bg-zinc-800/80 border border-gray-100 dark:border-zinc-800 space-y-1.5">
              <div class="flex items-center justify-between gap-2">
                <h4 class="text-xs font-bold text-gray-900 dark:text-zinc-100">{{ rec.title }}</h4>
                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase"
                  :class="rec.priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600'"
                >Prioritas {{ rec.priority }}</span>
              </div>
              <p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">{{ rec.description }}</p>
              <div class="flex items-center justify-between text-[10px] font-mono text-gray-400 pt-1">
                <span>PIC: {{ rec.pic || 'Penanggung Jawab' }}</span>
                <span>Target: {{ rec.target_completion }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Follow-up Actions -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
          <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
            Rencana Tindak Lanjut &amp; Progres Realisasi
          </h3>

          <div class="space-y-3">
            <div v-for="action in activeSurvey.follow_up_actions" :key="action.id" class="p-4 rounded-2xl bg-gray-50 dark:bg-zinc-800/80 border border-gray-100 dark:border-zinc-800 space-y-2">
              <div class="flex items-center justify-between gap-2">
                <h4 class="text-xs font-bold text-gray-900 dark:text-zinc-100">{{ action.action_name }}</h4>
                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded font-mono">{{ action.progress }}%</span>
              </div>

              <!-- Progress bar -->
              <div class="w-full h-2 bg-gray-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="{ width: `${action.progress}%` }"></div>
              </div>

              <div class="flex items-center justify-between text-[10px] font-mono text-gray-400 pt-1">
                <span>Unit: {{ action.responsible_unit }}</span>
                <span>Target: {{ action.completion_date }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. SURVEY ARCHIVE & REPORT DOWNLOADS -->
      <div v-if="archives && archives.length > 0" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-2 border-b border-gray-100 dark:border-zinc-800 pb-4">
          <div>
            <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
              Arsip Laporan IKM Periode Sebelumnya
            </h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
              Riwayat indeks kepuasan masyarakat dan dokumen publikasi resmi.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="arc in archives" :key="arc.id" class="p-5 rounded-2xl bg-gray-50 dark:bg-zinc-800/80 border border-gray-100 dark:border-zinc-800 space-y-3 flex flex-col justify-between">
            <div>
              <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Tahun {{ arc.year }} — Semester {{ arc.semester }}</span>
              <h4 class="text-xs font-bold text-gray-900 dark:text-zinc-100 mt-1 leading-snug">{{ arc.title }}</h4>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-200/60 dark:border-zinc-700">
              <div>
                <span class="text-xl font-black text-primary-600 dark:text-primary-400 font-mono">{{ arc.ikm_score }}</span>
                <span class="text-[10px] font-bold text-gray-400 ml-1 font-mono">({{ arc.quality_category }})</span>
              </div>
              <span class="text-[10px] font-medium text-gray-500">{{ arc.respondents }} Responden</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 7. INTERACTIVE SURVEY MODAL -->
      <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl max-w-2xl w-full p-6 md:p-8 shadow-2xl space-y-6 my-8">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
            <div>
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">Formulir Survei Kepuasan Masyarakat</h3>
              <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">Isi penilaian Anda secara jujur dan transparan untuk perbaikan mutu pelayanan.</p>
            </div>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 font-black text-lg px-2 cursor-pointer">&times;</button>
          </div>

          <form @submit.prevent="submitSurvey" class="space-y-6 text-left">
            <!-- Respondent identity -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Nama Pemohon (Opsional)</label>
                <input v-model="form.respondent_name" type="text" placeholder="Boleh dikosongkan (Anonim)" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Jenis Layanan yang Diakses</label>
                <select v-model="form.service_accessed" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs">
                  <option value="Perekaman KTP-el">Perekaman KTP-el</option>
                  <option value="Kartu Keluarga (KK)">Kartu Keluarga (KK)</option>
                  <option value="Akta Kelahiran">Akta Kelahiran</option>
                  <option value="Kartu Identitas Anak (KIA)">Kartu Identitas Anak (KIA)</option>
                  <option value="Pindah Datang Penduduk">Pindah Datang Penduduk</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
              </div>
            </div>

            <!-- Rating questions -->
            <div v-if="activeSurvey && activeSurvey.questions" class="space-y-4 pt-2 border-t border-gray-100 dark:border-zinc-800">
              <div v-for="(q, idx) in activeSurvey.questions" :key="q.id" class="p-4 bg-gray-50 dark:bg-zinc-800/80 rounded-2xl space-y-2 border border-gray-100 dark:border-zinc-700">
                <p class="text-xs font-bold text-gray-900 dark:text-zinc-100">
                  U{{ idx + 1 }}. {{ q.question_text }}
                </p>

                <div v-if="q.question_type === 'rating'" class="flex items-center gap-3 pt-1">
                  <label v-for="star in 4" :key="star" class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" :name="`q_${q.id}`" :value="star" v-model="form.answers[q.id]" required class="text-primary-600 focus:ring-primary-500" />
                    <span class="text-xs font-mono font-bold text-gray-700 dark:text-zinc-300">{{ star }} ({{ star === 4 ? 'Sangat Baik' : (star === 3 ? 'Baik' : (star === 2 ? 'Kurang Baik' : 'Tidak Baik')) }})</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Suggestion text -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Saran / Masukan Perbaikan</label>
              <textarea v-model="form.suggestion" rows="3" placeholder="Tuliskan saran perbaikan untuk pelayanan Dukcapil..." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-zinc-800">
              <button type="button" @click="showModal = false" class="px-5 py-2.5 text-gray-500 font-bold text-xs">Batal</button>
              <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-500 text-white font-extrabold text-xs rounded-xl shadow-md transition disabled:opacity-50 cursor-pointer">
                {{ form.processing ? 'Menyimpan...' : 'Kirim Penilaian IKM' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
  activeSurvey: Object,
  stats: Object,
  categoryInfo: Object,
  chartData: Object,
  archives: Array,
});

const showModal = ref(false);

const form = useForm({
  survey_period_id: props.activeSurvey?.id || '',
  respondent_name: '',
  service_accessed: 'Perekaman KTP-el',
  suggestion: '',
  answers: {},
});

function openSurveyModal() {
  if (props.activeSurvey && props.activeSurvey.questions) {
    props.activeSurvey.questions.forEach(q => {
      form.answers[q.id] = 4; // Default to 4 (Sangat Baik)
    });
  }
  showModal.value = true;
}

function submitSurvey() {
  form.post(route('public.survey.store'), {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
    }
  });
}
</script>
