<template>
  <PublicLayout>
    <div class="min-h-screen">
      <Head title="Survei Kepuasan Masyarakat (IKM) — Disdukcapil Dompu" />

      <!-- MAIN CONTENT -->
      <div class="space-y-10 text-left max-w-7xl mx-auto">

        <!-- HERO HEADER -->
        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 border border-blue-900/40 p-8 md:p-12 text-white shadow-xl">
          <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>
          <div class="relative z-10 space-y-4 max-w-3xl">
            <div class="flex items-center gap-2 flex-wrap">
              <span v-if="activeSurvey?.survey_mechanism === 'panrb'" class="px-3 py-1 bg-sky-500 text-white text-[10px] font-black uppercase tracking-wider rounded-lg flex items-center gap-1.5 shadow-sm">
                <Globe class="w-3.5 h-3.5" />
                SKM Online KemenPAN-RB
              </span>
              <span v-else class="px-3 py-1 bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider rounded-lg">
                PermenPANRB No. 14 Tahun 2017
              </span>
              <span class="px-3 py-1 bg-white/10 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg border border-white/10">Transparan &amp; Akuntabel</span>
              <span v-if="activeSurvey?.survey_mechanism === 'panrb'" class="px-3 py-1 bg-emerald-500/20 text-emerald-300 text-[10px] font-bold uppercase tracking-wider rounded-lg border border-emerald-400/30">
                Terintegrasi skm.go.id
              </span>
            </div>

            <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-tight">
              Survei Kepuasan Masyarakat <span :class="activeSurvey?.survey_mechanism === 'panrb' ? 'text-sky-400' : 'text-amber-400'">(SKM)</span>
            </h1>

            <p v-if="activeSurvey?.survey_mechanism === 'panrb'" class="text-xs md:text-sm text-blue-100/90 leading-relaxed">
              Pengukuran mutu dan evaluasi pelayanan administrasi kependudukan di Kabupaten Dompu kini terintegrasi langsung dengan platform nasional <strong>SKM Online Kementerian Pendayagunaan Aparatur Negara dan Reformasi Birokrasi (KemenPAN-RB)</strong> melalui portal <strong>skm.go.id</strong>.
            </p>
            <p v-else class="text-xs md:text-sm text-blue-100/90 leading-relaxed">
              Laporan pengukuran secara berkala atas tingkat kepuasan masyarakat terhadap mutu dan kualitas pelayanan administrasi kependudukan di Kabupaten Dompu.
            </p>

            <div class="pt-2 flex items-center gap-4 flex-wrap">
              <!-- When mechanism is panrb -->
              <template v-if="activeSurvey?.survey_mechanism === 'panrb'">
                <a 
                  v-if="activeSurvey.external_survey_url"
                  :href="activeSurvey.external_survey_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="px-6 py-3.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white text-xs font-black rounded-xl transition shadow-xl shadow-sky-900/40 active:scale-95 flex items-center gap-2 cursor-pointer"
                >
                  <Globe class="w-4 h-4" />
                  <span>Beri Penilaian di SKM PAN-RB</span>
                  <ExternalLink class="w-3.5 h-3.5" />
                </a>
                <button
                  v-if="activeSurvey.external_survey_url"
                  type="button"
                  @click="copyUrl(activeSurvey.external_survey_url)"
                  class="px-4 py-3.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl transition border border-white/15 flex items-center gap-2 cursor-pointer"
                >
                  <Copy v-if="!copiedUrl" class="w-3.5 h-3.5" />
                  <Check v-else class="w-3.5 h-3.5 text-emerald-400" />
                  <span>{{ copiedUrl ? 'Tautan Tersalin!' : 'Salin Tautan' }}</span>
                </button>
                <a href="#mekanisme-panrb" class="px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white text-xs font-extrabold rounded-xl transition border border-white/15">
                  Informasi Integrasi ↓
                </a>
              </template>

              <!-- When mechanism is internal -->
              <template v-else>
                <!-- Already submitted -->
                <div v-if="alreadySubmitted" class="flex items-center gap-2 px-4 py-2 bg-emerald-500/20 border border-emerald-400/40 rounded-xl">
                  <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span class="text-sm font-bold text-emerald-200">Anda sudah mengisi survei ini. Terima kasih!</span>
                </div>
                <template v-else>
                  <button @click="startSurvey" class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black rounded-xl transition shadow-lg active:scale-95 flex items-center gap-2 cursor-pointer">
                    <span>Isi Survei Sekarang</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                  </button>
                  <a href="#hasil-ikm" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white text-xs font-extrabold rounded-xl transition border border-white/15">Lihat Statistik ↓</a>
                </template>
              </template>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- PAN-RB MECHANISM SECTION (WHEN MECHANISM IS PANRB) -->
        <!-- ============================================== -->
        <div v-if="activeSurvey?.survey_mechanism === 'panrb'" id="mekanisme-panrb" class="space-y-6">
          
          <!-- PORTAL INTEGRATION HERO CARD -->
          <div class="bg-gradient-to-br from-white via-sky-50/40 to-blue-50/30 dark:from-zinc-900 dark:via-zinc-900 dark:to-blue-950/20 border border-sky-100 dark:border-zinc-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-zinc-800">
              <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-sky-100 dark:bg-sky-950/70 text-sky-800 dark:text-sky-300 text-[10px] font-black uppercase tracking-wider rounded-lg border border-sky-200 dark:border-sky-800">
                  <ShieldCheck class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" />
                  Sistem Resmi Terpusat Kementerian PAN-RB
                </div>
                <h2 class="text-xl md:text-2xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">
                  SKM Online Terintegrasi (skm.go.id)
                </h2>
                <p class="text-xs md:text-sm text-gray-600 dark:text-zinc-400 leading-relaxed">
                  Untuk menjamin transparansi, integritas, dan akuntabilitas publik tanpa manipulasi data, seluruh penilaian kepuasan masyarakat terhadap layanan Disdukcapil Kabupaten Dompu dialirkan langsung ke platform nasional SKM Online Kementerian PAN-RB.
                </p>
              </div>

              <!-- Quick Action Card -->
              <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0 lg:w-72">
                <a
                  v-if="activeSurvey.external_survey_url"
                  :href="activeSurvey.external_survey_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="px-5 py-3.5 bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs rounded-2xl shadow-md transition flex items-center justify-center gap-2 text-center cursor-pointer"
                >
                  <Globe class="w-4 h-4" />
                  <span>Buka Portal Penilaian</span>
                  <ExternalLink class="w-3.5 h-3.5" />
                </a>
                <button
                  v-if="activeSurvey.external_survey_url"
                  type="button"
                  @click="copyUrl(activeSurvey.external_survey_url)"
                  class="px-5 py-3 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-200 border border-gray-200 dark:border-zinc-700 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2 cursor-pointer"
                >
                  <Copy v-if="!copiedUrl" class="w-3.5 h-3.5 text-gray-400" />
                  <Check v-else class="w-3.5 h-3.5 text-emerald-500" />
                  <span>{{ copiedUrl ? 'Tautan Berhasil Disalin!' : 'Salin Tautan Kuesioner' }}</span>
                </button>
              </div>
            </div>

            <!-- URL Display Bar -->
            <div v-if="activeSurvey.external_survey_url" class="p-3.5 bg-gray-50 dark:bg-zinc-800/80 rounded-2xl border border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="flex items-center gap-2.5 overflow-hidden">
                <Globe class="w-4 h-4 text-sky-600 shrink-0" />
                <span class="text-[11px] font-bold text-gray-500 shrink-0">Tautan Resmi:</span>
                <span class="text-xs font-mono font-bold text-blue-700 dark:text-blue-400 truncate select-all">{{ activeSurvey.external_survey_url }}</span>
              </div>
              <a 
                :href="activeSurvey.external_survey_url" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="text-xs font-black text-sky-600 dark:text-sky-400 hover:underline shrink-0 flex items-center gap-1 self-end sm:self-auto"
              >
                Kunjungi Portal ↗
              </a>
            </div>

            <!-- 3 Pillar Feature Highlights -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
              <div class="p-4 rounded-2xl bg-white dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 space-y-2">
                <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950/80 flex items-center justify-center text-blue-700 dark:text-blue-400 font-black text-xs">
                  01
                </div>
                <h4 class="text-xs font-extrabold text-gray-900 dark:text-zinc-100">Transparan &amp; Terpusat</h4>
                <p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">
                  Setiap penilaian dari masyarakat langsung tersimpan di server KemenPAN-RB tanpa melalui perantara, menjaga keaslian opini warga.
                </p>
              </div>

              <div class="p-4 rounded-2xl bg-white dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 space-y-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 flex items-center justify-center text-emerald-700 dark:text-emerald-400 font-black text-xs">
                  02
                </div>
                <h4 class="text-xs font-extrabold text-gray-900 dark:text-zinc-100">Evaluasi Nyata &amp; Akuntabel</h4>
                <p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">
                  Hasil pengolahan indeks dipakai secara resmi dalam evaluasi Penyelenggaraan Pelayanan Publik dan Indeks Reformasi Birokrasi nasional.
                </p>
              </div>

              <div class="p-4 rounded-2xl bg-white dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 space-y-2">
                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/80 flex items-center justify-center text-amber-700 dark:text-amber-400 font-black text-xs">
                  03
                </div>
                <h4 class="text-xs font-extrabold text-gray-900 dark:text-zinc-100">Cepat Lewat Smartphone</h4>
                <p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">
                  Masyarakat dapat mengisi penilaian kuesioner kapan saja dalam 3-5 menit melalui smartphone saat mengurus berkas kependudukan.
                </p>
              </div>
            </div>

            <!-- Notes from Admin / PAN-RB -->
            <div v-if="activeSurvey.external_notes" class="p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/30 border border-sky-100 dark:border-sky-900/50 flex items-start gap-3">
              <Info class="w-4 h-4 text-sky-600 shrink-0 mt-0.5" />
              <div class="text-xs text-sky-900 dark:text-sky-200 leading-relaxed">
                <span class="font-bold">Catatan Penyelenggara: </span>
                {{ activeSurvey.external_notes }}
              </div>
            </div>
          </div>

          <!-- PAN-RB SCORE CARD (IF RELEASED) -->
          <div v-if="stats && (stats.ikm_value > 0 || activeSurvey.external_ikm_value)" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
              <div class="space-y-2 text-center md:text-left">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 text-[10px] font-black uppercase tracking-wider rounded-md">
                  <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                  Rilis Hasil Evaluasi KemenPAN-RB
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">{{ activeSurvey.title }}</h2>
                <p class="text-xs text-gray-500 dark:text-zinc-400">
                  Berdasarkan penilaian {{ stats.total_respondents || activeSurvey.external_total_respondents || 0 }} responden di portal SKM Online KemenPAN-RB.
                </p>
              </div>
              <div class="flex flex-col items-center p-6 bg-sky-50/50 dark:bg-zinc-800/80 rounded-2xl border border-sky-100 dark:border-zinc-700 min-w-[14rem]">
                <span class="text-5xl font-black text-sky-600 dark:text-sky-400 font-mono">{{ stats.ikm_value || activeSurvey.external_ikm_value }}</span>
                <span class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-wider">Skala (25 - 100)</span>
                <div 
                  class="mt-3 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border"
                  :class="categoryInfo.category === 'A' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : categoryInfo.category === 'B' ? 'bg-blue-50 text-blue-600 border-blue-200' : categoryInfo.category === 'C' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
                >
                  Mutu {{ categoryInfo.category }} — {{ categoryInfo.label }}
                </div>
              </div>
            </div>

            <div class="lg:col-span-4 grid grid-cols-2 gap-4">
              <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Total Responden</span>
                <span class="text-3xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-2">{{ stats.total_respondents || activeSurvey.external_total_respondents || 0 }}</span>
                <span class="text-[11px] text-gray-500 mt-1">Pemohon di skm.go.id</span>
              </div>
              <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Status Sistem</span>
                <span class="text-lg font-black text-sky-600 dark:text-sky-400 mt-2">Terintegrasi</span>
                <span class="text-[11px] text-gray-500 mt-1">skm.go.id PAN-RB</span>
              </div>
            </div>
          </div>

          <!-- WHEN IN EVALUATION / PENDING RELEASE -->
          <div v-else class="p-6 bg-gradient-to-r from-amber-50/70 via-sky-50/50 to-blue-50/50 dark:from-amber-950/20 dark:via-zinc-900 dark:to-zinc-900 border border-amber-200/70 dark:border-zinc-800 rounded-3xl shadow-sm flex flex-col sm:flex-row items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-950/80 flex items-center justify-center shrink-0 text-amber-700 dark:text-amber-400 font-bold text-2xl shadow-xs">
              ⏳
            </div>
            <div class="space-y-1.5 text-center sm:text-left flex-1">
              <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-amber-200/60 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 text-[10px] font-black uppercase tracking-wider rounded-md">
                Survei Belum Tuntas (Sedang Berjalan)
              </div>
              <h4 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50">Nilai IKM Belum Tersedia / Dalam Proses Pengumpulan di KemenPAN-RB</h4>
              <p class="text-xs text-gray-600 dark:text-zinc-400 leading-relaxed">
                Nilai capaian IKM resmi belum diterbitkan karena proses survei periode ini belum tuntas dan sedang aktif dikumpulkan langsung melalui portal SKM Online KemenPAN-RB. Mari berpartisipasi dengan mengisi kuesioner resmi.
              </p>
            </div>
            <a
              v-if="activeSurvey.external_survey_url"
              :href="activeSurvey.external_survey_url"
              target="_blank"
              rel="noopener noreferrer"
              class="px-5 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-xl shadow-sm shrink-0 transition flex items-center gap-1.5 cursor-pointer"
            >
              <span>Isi Penilaian di SKM PAN-RB ↗</span>
            </a>
          </div>

          <!-- 9 SERVICE ELEMENTS STANDARD (PAN-RB SCOPE) -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
                  9 Ruang Lingkup Unsur Pelayanan yang Dinilai
                </h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                  Standar penilaian mutu pelayanan publik pada portal SKM Online mengacu pada PermenPAN-RB No. 14 Tahun 2017:
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="(unsur, uIdx) in panrbElements" :key="uIdx" class="p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 space-y-1.5">
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-black text-sky-700 dark:text-sky-400 font-mono">U{{ uIdx + 1 }}</span>
                  <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest font-mono">PermenPAN-RB</span>
                </div>
                <h4 class="text-xs font-black text-gray-900 dark:text-zinc-100">{{ unsur.name }}</h4>
                <p class="text-[11px] text-gray-500 dark:text-zinc-400 leading-relaxed">{{ unsur.desc }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- INTERNAL SURVEY SECTION (WHEN MECHANISM IS INTERNAL) -->
        <!-- ============================================== -->
        <template v-else>
          <!-- IKM SCORE CARD -->
          <div v-if="stats && categoryInfo" id="hasil-ikm" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
              <div class="space-y-2 text-center md:text-left">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 font-mono">Nilai Indeks Terbaru</span>
                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">{{ activeSurvey ? activeSurvey.title : 'Survei Kepuasan Masyarakat' }}</h2>
                <p class="text-xs text-gray-500 dark:text-zinc-400">Berdasarkan {{ stats.total_respondents }} responden terverifikasi.</p>
              </div>
              <div class="flex flex-col items-center p-6 bg-gray-50 dark:bg-zinc-800/80 rounded-2xl border border-gray-100 dark:border-zinc-700 min-w-[14rem]">
                <span class="text-5xl font-black text-blue-600 dark:text-blue-400 font-mono">{{ stats.ikm_value }}</span>
                <span class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-wider">Skala (25 - 100)</span>
                <div class="mt-3 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border"
                  :class="categoryInfo.category === 'A' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : categoryInfo.category === 'B' ? 'bg-blue-50 text-blue-600 border-blue-200' : categoryInfo.category === 'C' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
                >Mutu {{ categoryInfo.category }} — {{ categoryInfo.label }}</div>
              </div>
            </div>
            <div class="lg:col-span-4 grid grid-cols-2 gap-4">
              <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Total Responden</span>
                <span class="text-3xl font-black text-gray-900 dark:text-zinc-50 font-mono mt-2">{{ stats.total_respondents }}</span>
                <span class="text-[11px] text-gray-500 mt-1">Pemohon Pelayanan</span>
              </div>
              <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between">
                <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Response Rate</span>
                <span class="text-3xl font-black text-amber-500 font-mono mt-2">{{ stats.response_rate }}%</span>
                <span class="text-[11px] text-gray-500 mt-1">Capaian Target</span>
              </div>
            </div>
          </div>

          <!-- RESULT BREAKDOWN TABLE -->
          <div v-if="chartData && chartData.breakdown && chartData.breakdown.length > 0" class="space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Breakdown Unsur Pelayanan IKM</h3>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="border-b border-gray-100 dark:border-zinc-800 text-[11px] font-black uppercase text-gray-400 font-mono">
                      <th class="py-3 px-4">No</th>
                      <th class="py-3 px-4">Unsur Pelayanan</th>
                      <th class="py-3 px-4 text-center">Rata-Rata</th>
                      <th class="py-3 px-4 text-center">Nilai IKM</th>
                      <th class="py-3 px-4 text-center">Kategori</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-50 dark:divide-zinc-800 text-xs font-medium">
                    <tr v-for="(item, idx) in chartData.breakdown" :key="idx" class="hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                      <td class="py-3 px-4 font-mono font-bold text-gray-400">U{{ idx + 1 }}</td>
                      <td class="py-3 px-4 font-bold text-gray-800 dark:text-zinc-200">{{ item.category }}</td>
                      <td class="py-3 px-4 text-center font-mono font-bold text-gray-700 dark:text-zinc-300">{{ item.average_rating }}</td>
                      <td class="py-3 px-4 text-center font-mono font-black text-blue-600 dark:text-blue-400">{{ item.ikm_unsur }}</td>
                      <td class="py-3 px-4 text-center">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase"
                          :class="item.ikm_unsur >= 88.31 ? 'bg-emerald-50 text-emerald-700' : (item.ikm_unsur >= 76.61 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700')">
                          {{ item.ikm_unsur >= 88.31 ? 'A' : (item.ikm_unsur >= 76.61 ? 'B' : 'C') }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </template>

        <!-- METHODOLOGY CARDS (APPLIES TO BOTH) -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-4">
          <h3 class="text-xs font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">Metodologi &amp; Klasifikasi Kategori PermenPANRB No. 14 Tahun 2017</h3>
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

        <!-- ARCHIVE -->
        <div v-if="archives && archives.length > 0" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-6">
          <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight border-b border-gray-100 dark:border-zinc-800 pb-4">Arsip Laporan IKM Periode Sebelumnya</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
              v-for="arc in archives"
              :key="arc.id"
              class="p-5 rounded-2xl border space-y-3 flex flex-col justify-between"
              :class="arc.has_score === false
                ? 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/40'
                : 'bg-gray-50 dark:bg-zinc-800/80 border-gray-100 dark:border-zinc-800'"
            >
              <div>
                <div class="flex items-center justify-between gap-1 mb-1">
                  <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Tahun {{ arc.year }} — Semester {{ arc.semester }}</span>
                  <!-- Badge panrb -->
                  <span
                    v-if="arc.survey_mechanism === 'panrb'"
                    class="px-1.5 py-0.5 bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50 text-[8px] font-black uppercase rounded tracking-wider shrink-0"
                  >KemenPAN-RB</span>
                </div>
                <h4 class="text-xs font-bold text-gray-900 dark:text-zinc-100 mt-0.5 leading-snug">{{ arc.title }}</h4>
              </div>
              <div class="pt-2 border-t border-gray-200/60 dark:border-zinc-700">
                <!-- Has Score -->
                <div v-if="arc.has_score !== false" class="flex items-center justify-between">
                  <div>
                    <span class="text-xl font-black text-blue-600 dark:text-blue-400 font-mono">{{ arc.ikm_score }}</span>
                    <span class="text-[10px] font-bold text-gray-400 ml-1 font-mono">({{ arc.quality_category }})</span>
                  </div>
                  <span class="text-[10px] font-medium text-gray-500">{{ arc.respondents }} Responden</span>
                </div>
                <!-- No Score Yet (Survei Belum Tuntas) -->
                <div v-else class="flex items-center justify-between">
                  <span class="px-2 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase rounded tracking-wider">
                    ⏳ Survei Belum Tuntas
                  </span>
                  <span class="text-[10px] font-medium text-gray-400">— Responden</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ======================== -->
      <!-- SURVEY WIZARD MODAL (FOR INTERNAL SURVEY ONLY) -->
      <!-- ======================== -->
      <div v-if="showWizard && activeSurvey?.survey_mechanism !== 'panrb'" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl max-w-2xl w-full shadow-2xl my-8 overflow-hidden">

          <!-- WIZARD HEADER -->
          <div class="bg-sky-600 text-white px-6 py-3 flex items-center justify-between">
            <span class="font-bold text-sm">Indeks Kepuasan Masyarakat (IKM)</span>
            <button @click="showWizard = false" class="text-white/70 hover:text-white text-2xl leading-none cursor-pointer">&times;</button>
          </div>

          <!-- INTRO TEXT -->
          <div class="px-6 py-3 bg-sky-50 dark:bg-sky-950/30 border-b border-sky-100 dark:border-sky-900/50">
            <p class="text-xs text-sky-800 dark:text-sky-300 leading-relaxed">
              Formulir kuesioner survei kepuasan masyarakat bagi pemohon layanan kependudukan yang mengacu pada PermenPAN-RB Nomor 14 Tahun 2017.
            </p>
          </div>

          <!-- STEP TABS -->
          <div class="flex border-b border-gray-200 dark:border-zinc-700">
            <div v-for="(tab, i) in steps" :key="i"
              :class="['flex-1 px-4 py-3 text-xs font-bold flex items-start gap-2 border-r last:border-r-0 border-gray-200 dark:border-zinc-700 transition-colors',
                currentStep === i + 1 ? 'bg-blue-600 text-white' :
                currentStep > i + 1 ? 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400' :
                'bg-white dark:bg-zinc-900 text-gray-400']"
            >
              <span :class="['w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0 mt-0.5',
                currentStep === i + 1 ? 'bg-white text-blue-600' :
                currentStep > i + 1 ? 'bg-blue-600 text-white' :
                'bg-gray-200 dark:bg-zinc-700 text-gray-500']">
                {{ currentStep > i + 1 ? '✓' : i + 1 }}
              </span>
              <div>
                <div class="font-black leading-tight">{{ tab.title }}</div>
                <div class="text-[10px] font-normal opacity-80 leading-tight mt-0.5">{{ tab.desc }}</div>
              </div>
            </div>
          </div>

          <!-- FORM -->
          <form novalidate @submit.prevent="handleSubmit" class="p-6 space-y-5">

            <!-- STEP 1: DATA RESPONDEN -->
            <div v-if="currentStep === 1" class="space-y-4">
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100 text-center">Data Responden IKM</h3>

              <div class="grid grid-cols-[130px_1fr] items-center gap-3">
                <label class="text-sm text-gray-700 dark:text-zinc-300 text-right">Nama</label>
                <input v-model="form.respondent_name" type="text" @input="filterNameInput" placeholder="Nama lengkap pemohon (atau inisial)..." class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>

              <div class="grid grid-cols-[130px_1fr] items-start gap-3">
                <label class="text-sm text-gray-700 dark:text-zinc-300 text-right pt-2">Umur / JK <span class="text-red-500">*</span></label>
                <div class="space-y-1">
                  <div class="flex gap-2">
                    <div class="relative flex-1">
                      <input v-model="form.respondent_age" type="text" inputmode="numeric" @input="filterAgeInput" maxlength="3" placeholder="Umur" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-zinc-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" :class="step1Errors.age ? 'border-red-400' : 'border-gray-300 dark:border-zinc-600'" />
                      <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-blue-600 font-semibold pointer-events-none">thn</span>
                    </div>
                    <select v-model="form.respondent_gender" class="flex-1 px-3 py-2 border rounded-lg bg-white dark:bg-zinc-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" :class="step1Errors.gender ? 'border-red-400' : 'border-gray-300 dark:border-zinc-600'">
                      <option value="">Jenis Kelamin</option>
                      <option value="Laki-laki">Laki-laki</option>
                      <option value="Perempuan">Perempuan</option>
                    </select>
                  </div>
                  <p v-if="step1Errors.age || step1Errors.gender" class="text-[10px] text-red-500">Umur (min. 18 thn) dan jenis kelamin wajib diisi.</p>
                  <p v-else class="text-[10px] text-gray-400">Minimal 18 Tahun</p>
                </div>
              </div>

              <div class="grid grid-cols-[130px_1fr] items-start gap-3">
                <label class="text-sm text-gray-700 dark:text-zinc-300 text-right pt-2">Nomor HP <span class="text-red-500">*</span></label>
                <div class="space-y-1">
                  <div class="flex items-center border rounded-lg overflow-hidden" :class="step1Errors.phone ? 'border-red-400' : 'border-gray-300 dark:border-zinc-600'">
                    <span class="bg-gray-100 dark:bg-zinc-700 px-3 py-2 text-xs font-bold text-blue-700 border-r border-gray-300 dark:border-zinc-600 shrink-0">+62</span>
                    <input v-model="form.respondent_phone" type="text" inputmode="numeric" @input="filterPhoneInput" placeholder="81211112222" maxlength="13" class="flex-1 px-3 py-2 bg-white dark:bg-zinc-800 text-xs focus:outline-none" />
                  </div>
                  <p v-if="step1Errors.phone" class="text-[10px] text-red-500">Nomor HP wajib diisi dengan angka. Contoh: 81211112222</p>
                  <p v-else class="text-[10px] text-gray-400">Contoh: 81211112222</p>
                </div>
              </div>

              <div class="grid grid-cols-[130px_1fr] items-start gap-3">
                <label class="text-sm text-gray-700 dark:text-zinc-300 text-right pt-2">Domisili <span class="text-red-500">*</span></label>
                <div class="space-y-1">
                  <select v-model="form.respondent_domicile" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-zinc-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" :class="step1Errors.domicile ? 'border-red-400' : 'border-gray-300 dark:border-zinc-600'">
                    <option value="">Pilih Kecamatan</option>
                    <option v-for="wil in wilayahOptions" :key="wil" :value="wil">{{ wil }}</option>
                  </select>
                  <p v-if="step1Errors.domicile" class="text-[10px] text-red-500">Domisili wajib diisi.</p>
                </div>
              </div>

              <div class="grid grid-cols-[130px_1fr] items-start gap-3">
                <label class="text-sm text-gray-700 dark:text-zinc-300 text-right pt-2">Pendidikan <span class="text-red-500">*</span></label>
                <div class="space-y-1">
                  <select v-model="form.respondent_education" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-zinc-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" :class="step1Errors.education ? 'border-red-400' : 'border-gray-300 dark:border-zinc-600'">
                    <option value="">Pilih Pendidikan</option>
                    <option value="SD / Sederajat">SD / Sederajat</option>
                    <option value="SMP / Sederajat">SMP / Sederajat</option>
                    <option value="SMA / SMK / Sederajat">SMA / SMK / Sederajat</option>
                    <option value="D1 / D2 / D3">D1 / D2 / D3</option>
                    <option value="S1 / D4">S1 / D4</option>
                    <option value="S2 / S3">S2 / S3</option>
                  </select>
                  <p v-if="step1Errors.education" class="text-[10px] text-red-500">Pendidikan wajib diisi.</p>
                </div>
              </div>

              <div class="grid grid-cols-[130px_1fr] items-start gap-3">
                <label class="text-sm text-gray-700 dark:text-zinc-300 text-right pt-2">Pekerjaan <span class="text-red-500">*</span></label>
                <div class="space-y-1">
                  <select v-model="form.respondent_job" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-zinc-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" :class="step1Errors.job ? 'border-red-400' : 'border-gray-300 dark:border-zinc-600'">
                    <option value="">Pilih Pekerjaan</option>
                    <option value="PNS / TNI / Polri">PNS / TNI / Polri</option>
                    <option value="Pegawai Swasta">Pegawai Swasta</option>
                    <option value="Wiraswasta / Wirausaha">Wiraswasta / Wirausaha</option>
                    <option value="Pelajar / Mahasiswa">Pelajar / Mahasiswa</option>
                    <option value="Petani / Nelayan">Petani / Nelayan</option>
                    <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                    <option value="Tidak Bekerja">Tidak Bekerja</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                  <p v-if="step1Errors.job" class="text-[10px] text-red-500">Pekerjaan wajib diisi.</p>
                </div>
              </div>

              <div class="grid grid-cols-[130px_1fr] items-start gap-3">
                <label class="text-sm text-gray-700 dark:text-zinc-300 text-right pt-2">Layanan <span class="text-red-500">*</span></label>
                <div class="space-y-1">
                  <select v-model="form.service_accessed" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-zinc-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" :class="step1Errors.service ? 'border-red-400' : 'border-gray-300 dark:border-zinc-600'">
                    <option value="">Pilih Layanan Yang Didapat</option>
                    <option value="Perekaman KTP-el">Perekaman KTP-el</option>
                    <option value="Kartu Keluarga (KK)">Kartu Keluarga (KK)</option>
                    <option value="Akta Kelahiran">Akta Kelahiran</option>
                    <option value="Akta Kematian">Akta Kematian</option>
                    <option value="Akta Perkawinan">Akta Perkawinan</option>
                    <option value="Akta Perceraian">Akta Perceraian</option>
                    <option value="Kartu Identitas Anak (KIA)">Kartu Identitas Anak (KIA)</option>
                    <option value="Pindah Datang Penduduk">Pindah Datang Penduduk</option>
                    <option value="Identitas Kependudukan Digital (IKD)">Identitas Kependudukan Digital (IKD)</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                  <p v-if="step1Errors.service" class="text-[10px] text-red-500">Layanan wajib dipilih.</p>
                </div>
              </div>
            </div>

            <!-- STEP 2: KUESIONER -->
            <div v-if="currentStep === 2" class="space-y-4">
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100 text-center">Form Kuesioner IKM</h3>
              <p class="text-xs text-gray-500 text-center">Berikan penilaian (1 = Tidak Baik, 4 = Sangat Baik)</p>

              <div v-if="activeSurvey && activeSurvey.questions" class="space-y-3">
                <div v-for="(q, idx) in activeSurvey.questions" :key="q.id" class="border border-gray-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                  <div class="bg-gray-50 dark:bg-zinc-800 px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex items-start gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">{{ idx + 1 }}</span>
                    <p class="text-xs font-bold text-gray-900 dark:text-zinc-100">{{ q.question_text }}</p>
                  </div>
                  <div class="px-4 py-3 grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <label v-for="star in 4" :key="star"
                      :class="['flex items-center gap-2 p-2 rounded-lg border cursor-pointer transition-all text-xs font-bold',
                        form.answers[q.id] == star ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40 text-blue-700' : 'border-gray-200 dark:border-zinc-700 hover:border-blue-300 text-gray-700 dark:text-zinc-300']"
                    >
                      <input type="radio" :name="'q_' + q.id" :value="star" v-model="form.answers[q.id]" class="sr-only" />
                      <span :class="['w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0',
                        form.answers[q.id] == star ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-500']">{{ star }}</span>
                      <span class="leading-tight">{{ ratingLabels[star - 1] }}</span>
                    </label>
                  </div>
                  <div v-if="step2Errors[q.id]" class="px-4 pb-2 text-[10px] text-red-500">Pilihan wajib diisi.</div>
                </div>
              </div>
              <div v-else class="py-8 text-center text-gray-400 text-sm">Tidak ada pertanyaan tersedia.</div>
            </div>

            <!-- STEP 3: KESAN & PESAN -->
            <div v-if="currentStep === 3" class="space-y-4">
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100 text-center">Kesan &amp; Pesan</h3>
              <p class="text-xs text-gray-500 text-center">Mohon isi kesan dan pesan Anda terkait pelayanan yang diterima.</p>

              <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-2">Saran / Kesan Terhadap Pelayanan</label>
                <textarea v-model="form.suggestion" rows="5" placeholder="Tuliskan kesan, masukan, dan saran perbaikan..." class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
              </div>

              <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 space-y-2">
                <p class="text-xs font-extrabold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Ringkasan Data Anda</p>
                <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-xs">
                  <div class="flex gap-1"><span class="text-gray-500 shrink-0">Nama:</span><span class="font-semibold text-gray-800 dark:text-zinc-200">{{ form.respondent_name || 'Anonim' }}</span></div>
                  <div class="flex gap-1"><span class="text-gray-500 shrink-0">Umur:</span><span class="font-semibold text-gray-800 dark:text-zinc-200">{{ form.respondent_age ? form.respondent_age + ' Thn' : '-' }}</span></div>
                  <div class="flex gap-1"><span class="text-gray-500 shrink-0">JK:</span><span class="font-semibold text-gray-800 dark:text-zinc-200">{{ form.respondent_gender || '-' }}</span></div>
                  <div class="flex gap-1"><span class="text-gray-500 shrink-0">Domisili:</span><span class="font-semibold text-gray-800 dark:text-zinc-200">{{ form.respondent_domicile || '-' }}</span></div>
                  <div class="flex gap-1"><span class="text-gray-500 shrink-0">Pendidikan:</span><span class="font-semibold text-gray-800 dark:text-zinc-200">{{ form.respondent_education || '-' }}</span></div>
                  <div class="flex gap-1"><span class="text-gray-500 shrink-0">Pekerjaan:</span><span class="font-semibold text-gray-800 dark:text-zinc-200">{{ form.respondent_job || '-' }}</span></div>
                  <div class="col-span-2 flex gap-1"><span class="text-gray-500 shrink-0">Layanan:</span><span class="font-semibold text-gray-800 dark:text-zinc-200">{{ form.service_accessed || '-' }}</span></div>
                </div>
              </div>
            </div>

            <!-- NAVIGATION BUTTONS -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-zinc-800 gap-3">
              <button v-if="currentStep > 1" type="button" @click="currentStep--" class="px-5 py-2.5 text-gray-600 dark:text-zinc-400 font-bold text-xs border border-gray-300 dark:border-zinc-600 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                &larr; Kembali
              </button>
              <div v-else></div>
              <div class="flex gap-2">
                <button type="button" @click="showWizard = false" class="px-5 py-2.5 text-gray-500 font-bold text-xs cursor-pointer hover:text-gray-700">Batal</button>
                <button v-if="currentStep < 3" type="button" @click="nextStep" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer">
                  Selanjutnya &rarr;
                </button>
                <button v-else type="button" @click="handleSubmit" :disabled="isSubmitting" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md transition disabled:opacity-50 cursor-pointer flex items-center gap-2">
                  <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                  <span>{{ isSubmitting ? 'Menyimpan...' : 'Kirim Penilaian IKM ✓' }}</span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- SUCCESS MODAL -->
      <div v-if="showSuccessModal" class="fixed inset-0 z-[90] bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl max-w-md w-full shadow-2xl p-8 text-center space-y-5 border border-gray-100 dark:border-zinc-800">
          <div class="flex justify-center">
            <div class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-950/60 flex items-center justify-center">
              <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
          <div>
            <h3 class="text-xl font-black text-gray-900 dark:text-zinc-50">Terima Kasih!</h3>
            <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-1">Survei IKM Berhasil Terkirim 🎉</p>
          </div>
          <p class="text-xs text-gray-500 dark:text-zinc-400 leading-relaxed">
            Penilaian dan masukan Anda sangat berarti bagi peningkatan mutu pelayanan Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu.
          </p>
          <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 text-left space-y-1">
            <p class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Responden</p>
            <p class="text-xs font-bold text-gray-800 dark:text-zinc-200">{{ submittedName }}</p>
            <p class="text-xs text-gray-500">{{ submittedService }}</p>
          </div>
          <button @click="showSuccessModal = false" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-sm rounded-xl shadow-lg transition cursor-pointer">
            Tutup &amp; Kembali ke Halaman
          </button>
        </div>
      </div>

      <!-- TOAST NOTIFICATION -->
      <transition name="toast">
        <div v-if="showToast" class="fixed bottom-6 right-6 z-[100] flex items-start gap-3 bg-white dark:bg-zinc-900 border border-emerald-200 dark:border-emerald-800 rounded-2xl shadow-2xl p-4 max-w-sm w-full">
          <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/60 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-extrabold text-emerald-700 dark:text-emerald-400">Survei Berhasil Terkirim! 🎉</p>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">Terima kasih telah berpartisipasi dalam Survei IKM Disdukcapil Kabupaten Dompu.</p>
          </div>
          <button @click="showToast = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none cursor-pointer shrink-0">&times;</button>
        </div>
      </transition>

    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Globe, ExternalLink, Copy, Check, ShieldCheck, CheckCircle2, Award, Info, FileText } from '@lucide/vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  activeSurvey: Object,
  stats: Object,
  categoryInfo: Object,
  chartData: Object,
  archives: Array,
})

const showWizard = ref(false)
const currentStep = ref(1)
const showToast = ref(false)
const showSuccessModal = ref(false)
const alreadySubmitted = ref(false)
const submittedName = ref('')
const submittedService = ref('')
const isSubmitting = ref(false)
const copiedUrl = ref(false)

const STORAGE_KEY = 'ikm_submitted_' + (props.activeSurvey ? props.activeSurvey.id : 'survey')

onMounted(() => {
  alreadySubmitted.value = !!localStorage.getItem(STORAGE_KEY)
})

function copyUrl(url) {
  if (!url) return
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(() => {
      copiedUrl.value = true
      setTimeout(() => { copiedUrl.value = false }, 2500)
    }).catch(() => {
      fallbackCopy(url)
    })
  } else {
    fallbackCopy(url)
  }
}

function fallbackCopy(url) {
  const el = document.createElement('textarea')
  el.value = url
  document.body.appendChild(el)
  el.select()
  document.execCommand('copy')
  document.body.removeChild(el)
  copiedUrl.value = true
  setTimeout(() => { copiedUrl.value = false }, 2500)
}

const panrbElements = [
  { name: 'Persyaratan Pelayanan', desc: 'Syarat administratif dan teknis yang harus dipenuhi oleh pemohon layanan kependudukan.' },
  { name: 'Sistem, Mekanisme, dan Prosedur', desc: 'Kemudahan tahapan tata cara pelayanan yang dibakukan bagi pihak pemberi dan penerima layanan.' },
  { name: 'Waktu Penyelesaian', desc: 'Jangka waktu yang diperlukan untuk menyelesaikan seluruh proses pelayanan dokumen hingga terbit.' },
  { name: 'Biaya / Tarif Pelayanan', desc: 'Seluruh pelayanan adminduk Disdukcapil Dompu tidak dipungut biaya apapun (Gratis / Rp 0,-).' },
  { name: 'Produk Spesifikasi Jenis Pelayanan', desc: 'Hasil pelayanan yang diterima sesuai dengan ketentuan (KTP-el, KK, Akta, dsb).' },
  { name: 'Kompetensi Pelaksana', desc: 'Kemampuan, keahlian, keterampilan, dan pengalaman petugas dalam memberikan pelayanan.' },
  { name: 'Perilaku Pelaksana', desc: 'Sikap petugas dalam memberikan pelayanan: ramah, sopan, bersahabat, dan responsif.' },
  { name: 'Penanganan Pengaduan & Saran', desc: 'Kecepatan dan kejelasan tindak lanjut atas saran, keluhan, dan konsultasi dari warga.' },
  { name: 'Sarana dan Prasarana', desc: 'Kondisi fasilitas fisik, ruang tunggu, loket, kebersihan, dan kemudahan akses portal digital.' },
]

const steps = [
  { title: 'Data Responden', desc: 'Nama, Umur, Jenis Kelamin, Pekerjaan, dan Pendidikan' },
  { title: 'Form Kuesioner IKM', desc: 'Isian Penilaian Kualitas Layanan' },
  { title: 'Kesan & Pesan', desc: 'Masukan & Saran Pelayanan' },
]

function filterNameInput(e) {
  form.respondent_name = e.target.value.replace(/[^a-zA-Z\s\.\']/g, '');
}

function filterAgeInput(e) {
  let val = e.target.value.replace(/[^0-9]/g, '');
  if (val.length > 3) val = val.slice(0, 3);
  if (parseInt(val) > 120) val = '120';
  form.respondent_age = val;
}

function filterPhoneInput(e) {
  form.respondent_phone = e.target.value.replace(/[^0-9]/g, '');
}

const ratingLabels = ['Tidak Baik', 'Kurang Baik', 'Baik', 'Sangat Baik']

const wilayahOptions = ['Dompu', 'Woja', 'Kilo', 'Manggalewa', 'Kempo', 'Huu', 'Pajo', 'Pekat']

const form = useForm({
  survey_period_id: props.activeSurvey ? props.activeSurvey.id : '',
  respondent_name: '',
  respondent_phone: '',
  respondent_domicile: '',
  respondent_age: '',
  respondent_gender: '',
  respondent_education: '',
  respondent_job: '',
  service_accessed: '',
  suggestion: '',
  answers: {},
})

const step1Errors = reactive({ age: false, gender: false, phone: false, domicile: false, education: false, job: false, service: false })
const step2Errors = reactive({})

function startSurvey() {
  if (props.activeSurvey?.survey_mechanism === 'panrb' && props.activeSurvey?.external_survey_url) {
    window.open(props.activeSurvey.external_survey_url, '_blank', 'noopener,noreferrer')
    return
  }
  if (alreadySubmitted.value) return
  currentStep.value = 1
  Object.keys(step1Errors).forEach(k => { step1Errors[k] = false })
  if (props.activeSurvey) {
    form.survey_period_id = props.activeSurvey.id
    if (props.activeSurvey.questions) {
      props.activeSurvey.questions.forEach(q => {
        if (!form.answers[q.id]) form.answers[q.id] = 4
      })
    }
  }
  showWizard.value = true
}

function validateStep1() {
  step1Errors.age = !form.respondent_age || parseInt(form.respondent_age) < 18
  step1Errors.gender = !form.respondent_gender
  step1Errors.phone = !form.respondent_phone
  step1Errors.domicile = !form.respondent_domicile
  step1Errors.education = !form.respondent_education
  step1Errors.job = !form.respondent_job
  step1Errors.service = !form.service_accessed
  return !Object.values(step1Errors).some(Boolean)
}

function validateStep2() {
  if (!props.activeSurvey || !props.activeSurvey.questions) return true
  let valid = true
  props.activeSurvey.questions.forEach(q => {
    step2Errors[q.id] = !form.answers[q.id]
    if (!form.answers[q.id]) valid = false
  })
  return valid
}

function nextStep() {
  if (currentStep.value === 1 && !validateStep1()) return
  if (currentStep.value === 2 && !validateStep2()) return
  currentStep.value++
}

function handleSubmit() {
  if (isSubmitting.value) return
  isSubmitting.value = true

  if (!form.survey_period_id && props.activeSurvey) {
    form.survey_period_id = props.activeSurvey.id
  }
  if (form.respondent_age) {
    form.respondent_age = String(form.respondent_age)
  }

  form.post(route('public.survey.store'), {
    preserveScroll: true,
    onSuccess: () => {
      submittedName.value = form.respondent_name || 'Anonim'
      submittedService.value = form.service_accessed || 'Pelayanan Dukcapil'
      showWizard.value = false
      currentStep.value = 1
      localStorage.setItem(STORAGE_KEY, Date.now().toString())
      alreadySubmitted.value = true
      showSuccessModal.value = true
      setTimeout(() => {
        showToast.value = true
        setTimeout(() => { showToast.value = false }, 6000)
      }, 300)
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
      const errList = Object.values(errors).flat().join('\n• ')
      alert('Gagal mengirim survei:\n• ' + errList)
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}
</script>

<style scoped>
.toast-enter-active { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.3s ease-in; }
.toast-enter-from { opacity: 0; transform: translateY(20px) scale(0.95); }
.toast-leave-to { opacity: 0; transform: translateY(10px); }
</style>

<style scoped>
.toast-enter-active { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.3s ease-in; }
.toast-enter-from { opacity: 0; transform: translateY(20px) scale(0.95); }
.toast-leave-to { opacity: 0; transform: translateY(10px); }
</style>
