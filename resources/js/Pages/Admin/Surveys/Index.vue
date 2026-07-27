<template>
  <Head title="Manajemen Survei Kepuasan Masyarakat (IKM) - Admin CMS" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 dark:border-zinc-800 pb-4">
        <div>
          <h1 class="text-xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">
            Survei Kepuasan Masyarakat (IKM) CMS
          </h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Sistem Informasi Manajemen Evaluasi &amp; Pengukuran Mutu Pelayanan Publik Sesuai PermenPANRB 14/2017.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <button 
            @click="openPeriodModal()" 
            class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white font-black text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer"
          >
            <span>+ Tambah Periode Survei</span>
          </button>

          <button 
            @click="recalculateStats" 
            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer"
          >
            <RefreshCw class="w-3.5 h-3.5" />
            <span>Kalkulasi Ulang Nilai IKM</span>
          </button>
        </div>
      </div>

      <!-- Alert Success -->
      <div v-if="$page.props.flash.success" class="p-4 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold rounded-xl">
        {{ $page.props.flash.success }}
      </div>

      <!-- CMS Navigation Tabs -->
      <div class="flex items-center gap-2 border-b border-gray-100 dark:border-zinc-800 overflow-x-auto pb-1">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-4 py-2 text-xs font-bold rounded-xl whitespace-nowrap transition cursor-pointer"
          :class="activeTab === tab.id 
            ? 'bg-primary-600 text-white shadow-sm' 
            : 'text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-zinc-100 bg-gray-50 dark:bg-zinc-800/60'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- TAB 1: DASHBOARD OVERVIEW -->
      <div v-if="activeTab === 'dashboard'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Active Survey Card -->
          <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 space-y-2">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Survei Aktif</span>
            <p class="text-sm font-bold text-gray-900 dark:text-zinc-100 line-clamp-1">{{ activePeriod?.title || 'Belum Ada' }}</p>
            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded">Status Published</span>
          </div>

          <!-- Current IKM Score -->
          <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 space-y-2">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Nilai IKM</span>
            <p class="text-2xl font-black text-primary-600 dark:text-primary-400 font-mono">{{ activePeriod?.statistic?.ikm_value || 0 }}</p>
            <span class="text-[11px] font-bold text-gray-500">Mutu Pelayanan {{ activePeriod?.statistic?.service_quality_category || 'B' }}</span>
          </div>

          <!-- Total Respondents -->
          <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 space-y-2">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Jumlah Responden</span>
            <p class="text-2xl font-black text-gray-900 dark:text-zinc-100 font-mono">{{ activePeriod?.statistic?.total_respondents || 0 }}</p>
            <span class="text-[11px] font-bold text-gray-500">Pemohon Terverifikasi</span>
          </div>

          <!-- Response Rate -->
          <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 space-y-2">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Response Rate</span>
            <p class="text-2xl font-black text-amber-500 font-mono">
              {{ activePeriod && activePeriod.target_respondents > 0 ? Math.round(((activePeriod.statistic?.total_respondents || 0) / activePeriod.target_respondents) * 100) : 100 }}%
            </p>
            <span class="text-[11px] font-bold text-gray-500">Target {{ activePeriod?.target_respondents }} Pemohon</span>
          </div>
        </div>

        <!-- Quick Actions Panel -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 space-y-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-gray-400">Aksi Cepat</h3>
          <div class="flex items-center gap-3 flex-wrap">
            <button @click="openPeriodModal()" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
              + Tambah Periode Survei Baru
            </button>
            <button @click="openQuestionModal()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
              + Tambah Pertanyaan Unsur
            </button>
            <button @click="openRecommendationModal()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
              + Tambah Rekomendasi
            </button>
            <button @click="openFollowUpModal()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
              + Tambah Tindak Lanjut
            </button>
          </div>
        </div>
      </div>

      <!-- TAB 2: SURVEY PERIOD MANAGEMENT -->
      <div v-if="activeTab === 'period'" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 space-y-6">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-black uppercase tracking-wider text-gray-400">Daftar Periode Survei</h3>
          <button @click="openPeriodModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
            + Tambah Periode
          </button>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-gray-100 dark:border-zinc-800 text-[10px] font-mono text-gray-400 uppercase">
                <th class="py-2.5 px-3">Judul Periode</th>
                <th class="py-2.5 px-3">Semester / Tahun</th>
                <th class="py-2.5 px-3">Jadwal</th>
                <th class="py-2.5 px-3">Status</th>
                <th class="py-2.5 px-3">Aktif</th>
                <th class="py-2.5 px-3">Nilai IKM</th>
                <th class="py-2.5 px-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800">
              <tr v-for="p in periods" :key="p.id">
                <td class="py-3 px-3 font-bold text-gray-900 dark:text-zinc-100">{{ p.title }}</td>
                <td class="py-3 px-3 font-mono font-semibold">{{ p.year }} Sem {{ p.semester }}</td>
                <td class="py-3 px-3 font-mono text-gray-500">{{ p.start_date }} s/d {{ p.end_date }}</td>
                <td class="py-3 px-3"><span class="px-2 py-0.5 bg-blue-50 text-blue-600 font-bold rounded text-[10px] uppercase">{{ p.status }}</span></td>
                <td class="py-3 px-3"><span v-if="p.is_active" class="px-2 py-0.5 bg-emerald-50 text-emerald-600 font-black rounded text-[10px]">AKTIF</span></td>
                <td class="py-3 px-3 font-mono font-black text-primary-600">{{ p.statistic?.ikm_value || 0 }}</td>
                <td class="py-3 px-3 text-right">
                  <button @click="openPeriodModal(p)" class="px-3 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold rounded-lg text-xs cursor-pointer">
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 3: SURVEY QUESTIONS BUILDER -->
      <div v-if="activeTab === 'questions'" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 space-y-6">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <h3 class="text-xs font-black uppercase tracking-wider text-gray-400">Pengaturan Pertanyaan Unsur PermenPANRB</h3>
          <div class="flex items-center gap-2">
            <!-- Copy from previous period -->
            <button
              v-if="otherPeriods.length > 0"
              @click="showCopyModal = true"
              class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 transition flex items-center gap-1.5 cursor-pointer"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              Salin dari Periode Lain
            </button>
            <button @click="openQuestionModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
              + Tambah Pertanyaan
            </button>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="!activePeriod?.questions || activePeriod.questions.length === 0" class="py-10 text-center border-2 border-dashed border-gray-200 dark:border-zinc-700 rounded-2xl">
          <svg class="w-10 h-10 text-gray-300 dark:text-zinc-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          <p class="text-sm font-bold text-gray-400">Belum ada pertanyaan</p>
          <p class="text-xs text-gray-400 mt-1">Tambah manual atau salin dari periode sebelumnya</p>
        </div>

        <div v-else class="space-y-3">
          <div v-for="(q, idx) in activePeriod?.questions" :key="q.id" class="p-4 bg-gray-50 dark:bg-zinc-800/80 rounded-xl border border-gray-100 dark:border-zinc-700 flex items-center justify-between gap-4">
            <div class="flex items-start gap-3">
              <span class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">{{ idx + 1 }}</span>
              <div class="space-y-1">
                <span class="text-[10px] font-black uppercase text-primary-600 font-mono">{{ q.service_category }}</span>
                <p class="text-xs font-bold text-gray-900 dark:text-zinc-100">{{ q.question_text }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <button @click="openQuestionModal(q)" class="px-3 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold rounded-lg text-xs cursor-pointer">Edit</button>
              <button @click="deleteQuestion(q)" class="px-3 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold rounded-lg text-xs cursor-pointer">Hapus</button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB: RESPONDENTS -->
      <div v-if="activeTab === 'respondents'" class="space-y-4">
        <!-- Summary Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Total Responden</span>
            <p class="text-2xl font-black text-primary-600 font-mono">{{ respondents ? respondents.length : 0 }}</p>
          </div>
          <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Nilai IKM</span>
            <p class="text-2xl font-black text-amber-500 font-mono">{{ activePeriod?.statistic?.ikm_value || '—' }}</p>
          </div>
          <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Rata-Rata Skor</span>
            <p class="text-2xl font-black text-blue-600 font-mono">
              {{ respondents && respondents.length > 0 ? (respondents.reduce((a,b) => a + parseFloat(b.ikm_score||0), 0) / respondents.length).toFixed(1) : '—' }}
            </p>
          </div>
          <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase text-gray-400 font-mono">Mutu Pelayanan</span>
            <p class="text-2xl font-black text-emerald-600 font-mono">{{ activePeriod?.statistic?.service_quality_category || '—' }}</p>
          </div>
        </div>

        <!-- Respondents Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-100">Daftar Responden IKM</h3>
              <p class="text-[11px] text-gray-400 mt-0.5">{{ activePeriod?.title || '—' }}</p>
            </div>
            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-lg">
              {{ respondents ? respondents.length : 0 }} Responden
            </span>
          </div>

          <div v-if="!respondents || respondents.length === 0" class="py-16 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-3">
              <svg class="w-8 h-8 text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-sm font-bold text-gray-400 dark:text-zinc-500">Belum ada responden yang mengisi survei</p>
            <p class="text-xs text-gray-400 dark:text-zinc-600 mt-1">Data akan muncul setelah masyarakat mengisi form survei publik</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-gray-100 dark:border-zinc-800 text-[10px] font-black uppercase text-gray-400 font-mono bg-gray-50/50 dark:bg-zinc-800/30">
                  <th class="py-3 px-4">#</th>
                  <th class="py-3 px-4">Nama</th>
                  <th class="py-3 px-4">No. HP</th>
                  <th class="py-3 px-4">Domisili</th>
                  <th class="py-3 px-4">Umur/JK</th>
                  <th class="py-3 px-4">Pendidikan</th>
                  <th class="py-3 px-4">Pekerjaan</th>
                  <th class="py-3 px-4">Layanan</th>
                  <th class="py-3 px-4 text-center">Skor IKM</th>
                  <th class="py-3 px-4">Saran</th>
                  <th class="py-3 px-4">Tgl. Isi</th>
                  <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/80">
                <tr v-for="(r, i) in respondents" :key="r.id" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/30 transition-colors">
                  <td class="py-3 px-4 font-mono text-gray-400">{{ i + 1 }}</td>
                  <td class="py-3 px-4 font-bold text-gray-900 dark:text-zinc-100">{{ r.respondent_name || 'Anonim' }}</td>
                  <td class="py-3 px-4 font-mono text-blue-600 dark:text-blue-400">
                    <span v-if="r.respondent_phone">+62{{ r.respondent_phone }}</span>
                    <span v-else class="text-gray-300">—</span>
                  </td>
                  <td class="py-3 px-4 text-gray-700 dark:text-zinc-300">{{ r.respondent_domicile || '—' }}</td>
                  <td class="py-3 px-4 text-gray-600 dark:text-zinc-400">
                    <span>{{ r.respondent_age ? r.respondent_age + ' thn' : '—' }}</span>
                    <span v-if="r.respondent_gender" class="ml-1 text-[10px] px-1.5 py-0.5 rounded font-bold" :class="r.respondent_gender === 'Laki-laki' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600'">
                      {{ r.respondent_gender === 'Laki-laki' ? 'L' : 'P' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-gray-600 dark:text-zinc-400">{{ r.respondent_education || '—' }}</td>
                  <td class="py-3 px-4 text-gray-700 dark:text-zinc-300">{{ r.respondent_job || '—' }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 rounded text-[10px] font-bold">{{ r.service_accessed || '—' }}</span>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span class="px-2.5 py-1 rounded-lg font-black font-mono text-[11px]"
                      :class="r.ikm_score >= 88.31 ? 'bg-emerald-50 text-emerald-700' : r.ikm_score >= 76.61 ? 'bg-blue-50 text-blue-700' : r.ikm_score >= 65 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'">
                      {{ parseFloat(r.ikm_score).toFixed(1) }}
                    </span>
                  </td>
                  <td class="py-3 px-4 max-w-[160px] truncate text-gray-500 dark:text-zinc-500 italic" :title="r.suggestion">
                    {{ r.suggestion || '—' }}
                  </td>
                  <td class="py-3 px-4 font-mono text-gray-400 text-[10px] whitespace-nowrap">
                    {{ new Date(r.created_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) }}
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button @click="deleteRespondent(r)" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 font-bold rounded-lg text-xs transition cursor-pointer flex items-center gap-1 ml-auto">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      Hapus
                    </button>
                  </td>
                </tr>
              </tbody>

            </table>
          </div>
        </div>
      </div>

      <!-- TAB 4: RECOMMENDATIONS -->
      <div v-if="activeTab === 'recommendations'" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 space-y-6">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-black uppercase tracking-wider text-gray-400">Rekomendasi Perbaikan Mutu</h3>
          <button @click="openRecommendationModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
            + Tambah Rekomendasi
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="rec in activePeriod?.recommendations" :key="rec.id" class="p-4 bg-gray-50 dark:bg-zinc-800/80 rounded-xl border border-gray-100 dark:border-zinc-700 space-y-1">
            <div class="flex items-center justify-between gap-3">
              <h4 class="text-xs font-bold text-gray-900 dark:text-zinc-100">{{ rec.title }}</h4>
              <div class="flex items-center gap-1.5 shrink-0">
                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-black rounded uppercase">Prioritas {{ rec.priority }}</span>
                <button @click="openRecommendationModal(rec)" class="px-3 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold rounded-lg text-xs cursor-pointer">Edit</button>
                <button @click="deleteRecommendation(rec)" class="px-3 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold rounded-lg text-xs cursor-pointer">Hapus</button>
              </div>
            </div>
            <p class="text-[11px] text-gray-500 leading-relaxed">{{ rec.description }}</p>
            <p v-if="rec.pic" class="text-[10px] text-gray-400">PIC: <span class="font-semibold">{{ rec.pic }}</span></p>
          </div>
          <p v-if="!activePeriod?.recommendations?.length" class="text-xs text-gray-400 italic">Belum ada rekomendasi perbaikan ditambahkan.</p>
        </div>
      </div>

      <!-- TAB 5: FOLLOW-UP ACTIONS -->
      <div v-if="activeTab === 'follow_up'" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 space-y-6">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-black uppercase tracking-wider text-gray-400">Rencana Tindak Lanjut &amp; Progres</h3>
          <button @click="openFollowUpModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
            + Tambah Tindak Lanjut
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="action in activePeriod?.follow_up_actions" :key="action.id" class="p-4 bg-gray-50 dark:bg-zinc-800/80 rounded-xl border border-gray-100 dark:border-zinc-700 space-y-2">
            <div class="flex items-center justify-between gap-3">
              <div class="space-y-0.5 flex-1">
                <h4 class="text-xs font-bold text-gray-900 dark:text-zinc-100">{{ action.action_name }}</h4>
                <p v-if="action.responsible_unit" class="text-[10px] text-gray-400">Unit: {{ action.responsible_unit }}</p>
              </div>
              <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-xs font-mono font-black text-emerald-600">{{ action.progress }}%</span>
                <button @click="openFollowUpModal(action)" class="px-3 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold rounded-lg text-xs cursor-pointer">Edit</button>
                <button @click="deleteFollowUp(action)" class="px-3 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold rounded-lg text-xs cursor-pointer">Hapus</button>
              </div>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-emerald-500 rounded-full transition-all" :style="{ width: `${action.progress}%` }"></div>
            </div>
          </div>
          <p v-if="!activePeriod?.follow_up_actions?.length" class="text-xs text-gray-400 italic">Belum ada rencana tindak lanjut ditambahkan.</p>
        </div>
      </div>

      <!-- MODAL 1: PERIODE SURVEI MODAL -->
      <div v-if="showPeriodModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
            <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50">{{ editingPeriod ? 'Edit Periode Survei' : 'Tambah Periode Survei Baru' }}</h3>
            <button @click="showPeriodModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
          </div>

          <form @submit.prevent="submitPeriod" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Judul Periode Survei</label>
              <input v-model="periodForm.title" type="text" required placeholder="Contoh: Survei IKM Semester II 2026" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Semester</label>
                <select v-model="periodForm.semester" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs">
                  <option value="1">Semester 1</option>
                  <option value="2">Semester 2</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Tahun</label>
                <input v-model="periodForm.year" type="number" required min="2020" max="2099" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Tanggal Mulai</label>
                <input v-model="periodForm.start_date" type="date" required class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Tanggal Selesai</label>
                <input v-model="periodForm.end_date" type="date" required class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Target Responden</label>
                <input v-model="periodForm.target_respondents" type="number" required min="10" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Status Publikasi</label>
                <select v-model="periodForm.status" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="closed">Closed</option>
                </select>
              </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
              <input v-model="periodForm.is_active" type="checkbox" id="is_active_cb" class="rounded text-primary-600" />
              <label for="is_active_cb" class="text-xs font-bold text-gray-700 dark:text-zinc-300">Set sebagai Survei Aktif saat ini</label>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-zinc-800">
              <button type="button" @click="showPeriodModal = false" class="px-4 py-2 text-xs font-bold text-gray-500">Batal</button>
              <button type="submit" :disabled="periodForm.processing" class="px-5 py-2 bg-primary-600 text-white font-bold text-xs rounded-xl shadow-sm">
                {{ editingPeriod ? 'Simpan Perubahan' : 'Tambah Periode' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- MODAL: SALIN PERTANYAAN -->
      <div v-if="showCopyModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
            <div>
              <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50">Salin Pertanyaan dari Periode Lain</h3>
              <p class="text-xs text-gray-400 mt-0.5">Pertanyaan akan disalin ke: <span class="font-bold text-primary-600">{{ activePeriod?.title }}</span></p>
            </div>
            <button @click="showCopyModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl cursor-pointer">&times;</button>
          </div>

          <form @submit.prevent="submitCopyQuestions" class="space-y-4">
            <!-- Source Period -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Pilih Periode Sumber <span class="text-red-500">*</span>
              </label>
              <select
                v-model="copyForm.source_period_id"
                required
                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <option value="">-- Pilih periode --</option>
                <option v-for="p in otherPeriods" :key="p.id" :value="p.id">
                  {{ p.title }} ({{ p.questions?.length || 0 }} pertanyaan)
                </option>
              </select>
            </div>

            <!-- Preview -->
            <div v-if="copyForm.source_period_id" class="p-3 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-xl space-y-2">
              <p class="text-[10px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Preview Pertanyaan yang Akan Disalin</p>
              <div v-for="(q, i) in selectedSourcePeriod?.questions" :key="q.id" class="flex items-start gap-2">
                <span class="w-4 h-4 rounded-full bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 text-[9px] font-black flex items-center justify-center shrink-0 mt-0.5">{{ i+1 }}</span>
                <p class="text-[11px] text-indigo-800 dark:text-indigo-300 leading-snug">{{ q.question_text }}</p>
              </div>
              <p v-if="!selectedSourcePeriod?.questions?.length" class="text-xs text-indigo-400">Periode ini tidak memiliki pertanyaan.</p>
            </div>

            <!-- Overwrite option -->
            <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/50 rounded-xl">
              <input id="overwriteCheck" v-model="copyForm.keep_existing" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer" />
              <label for="overwriteCheck" class="text-xs font-bold text-amber-800 dark:text-amber-300 cursor-pointer">
                Pertahankan pertanyaan yang sudah ada (tambah tanpa hapus)
              </label>
            </div>
            <p class="text-[10px] text-gray-400 -mt-2">Jika tidak dicentang, pertanyaan lama akan <strong>dihapus</strong> dan diganti dengan yang baru.</p>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
              <button type="button" @click="showCopyModal = false" class="px-4 py-2 text-gray-500 font-bold text-xs cursor-pointer hover:text-gray-700">Batal</button>
              <button
                type="submit"
                :disabled="!copyForm.source_period_id || copyForm.processing"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-sm transition disabled:opacity-50 cursor-pointer flex items-center gap-2"
              >
                <svg v-if="copyForm.processing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Salin Pertanyaan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- MODAL 2: PERTANYAAN MODAL -->
      <div v-if="showQuestionModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
            <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50">{{ editingQuestion ? 'Edit Pertanyaan' : 'Tambah Pertanyaan Unsur Baru' }}</h3>
            <button @click="showQuestionModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
          </div>

          <form @submit.prevent="submitQuestion" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Teks Pertanyaan</label>
              <textarea v-model="questionForm.question_text" rows="3" required placeholder="Tuliskan pertanyaan survei..." class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Kategori Unsur Pelayanan</label>
                <input v-model="questionForm.service_category" type="text" required placeholder="Contoh: Persyaratan Pelayanan" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Tipe Pertanyaan</label>
                <select v-model="questionForm.question_type" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs">
                  <option value="rating">Rating Scale (1-4)</option>
                  <option value="text">Teks Bebas</option>
                  <option value="yes_no">Ya / Tidak</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Urutan (Sort Order)</label>
              <input v-model="questionForm.sort_order" type="number" required min="1" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-zinc-800">
              <button type="button" @click="showQuestionModal = false" class="px-4 py-2 text-xs font-bold text-gray-500">Batal</button>
              <button type="submit" :disabled="questionForm.processing" class="px-5 py-2 bg-primary-600 text-white font-bold text-xs rounded-xl shadow-sm">
                {{ editingQuestion ? 'Simpan Perubahan' : 'Tambah Pertanyaan' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- MODAL 3: REKOMENDASI MODAL -->
      <div v-if="showRecommendationModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
            <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50">{{ editingRecommendation ? 'Edit Rekomendasi' : 'Tambah Rekomendasi Perbaikan' }}</h3>
            <button @click="showRecommendationModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
          </div>

          <form @submit.prevent="submitRecommendation" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Judul Rekomendasi</label>
              <input v-model="recForm.title" type="text" required placeholder="Contoh: Penambahan Unit AC Loket Utama" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Deskripsi Rekomendasi</label>
              <textarea v-model="recForm.description" rows="3" placeholder="Penjelasan rincian perbaikan..." class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Prioritas</label>
                <select v-model="recForm.priority" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs">
                  <option value="high">Tinggi (High)</option>
                  <option value="medium">Sedang (Medium)</option>
                  <option value="low">Rendah (Low)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">PIC / Penanggung Jawab</label>
                <input v-model="recForm.pic" type="text" placeholder="Kasubag Umum" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-zinc-800">
              <button type="button" @click="showRecommendationModal = false" class="px-4 py-2 text-xs font-bold text-gray-500">Batal</button>
              <button type="submit" :disabled="recForm.processing" class="px-5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-sm">
                {{ editingRecommendation ? 'Simpan Perubahan' : 'Simpan Rekomendasi' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- MODAL 4: TINDAK LANJUT MODAL -->
      <div v-if="showFollowUpModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
            <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50">{{ editingFollowUp ? 'Edit Tindak Lanjut' : 'Tambah Rencana Tindak Lanjut' }}</h3>
            <button @click="showFollowUpModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
          </div>

          <form @submit.prevent="submitFollowUp" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Nama Aksi Tindak Lanjut</label>
              <input v-model="followUpForm.action_name" type="text" required placeholder="Contoh: Pengadaan 4 Unit AC Ceiling" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Unit Penanggung Jawab</label>
                <input v-model="followUpForm.responsible_unit" type="text" required placeholder="Subbag Umum & Perlengkapan" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">Progres Realisasi (%)</label>
                <input v-model="followUpForm.progress" type="number" required min="0" max="100" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs" />
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-zinc-800">
              <button type="button" @click="showFollowUpModal = false" class="px-4 py-2 text-xs font-bold text-gray-500">Batal</button>
              <button type="submit" :disabled="followUpForm.processing" class="px-5 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow-sm">
                {{ editingFollowUp ? 'Simpan Perubahan' : 'Simpan Tindak Lanjut' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { RefreshCw } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  periods: Array,
  activePeriod: Object,
  chartData: Object,
  archives: Array,
  respondents: Array,
})

const activeTab = ref('dashboard')

const tabs = [
  { id: 'dashboard', label: 'Dashboard Overview' },
  { id: 'period', label: 'Periode Survei' },
  { id: 'questions', label: 'Form Pertanyaan' },
  { id: 'respondents', label: '📋 Responden' },
  { id: 'recommendations', label: 'Rekomendasi' },
  { id: 'follow_up', label: 'Tindak Lanjut' },
]

// Modal Visibility State
const showPeriodModal = ref(false)
const editingPeriod = ref(null)

const showQuestionModal = ref(false)
const editingQuestion = ref(null)

const showRecommendationModal = ref(false)
const editingRecommendation = ref(null)
const showFollowUpModal = ref(false)
const editingFollowUp = ref(null)

// Copy Questions Modal
const showCopyModal = ref(false)
const copyForm = useForm({
  source_period_id: '',
  keep_existing: false,
})

// Computed: periods excluding the active period
const otherPeriods = computed(() =>
  (props.periods || []).filter(p => p.id !== props.activePeriod?.id)
)

// Computed: selected source period with its questions
const selectedSourcePeriod = computed(() =>
  otherPeriods.value.find(p => p.id == copyForm.source_period_id) || null
)

// Inertia Forms
const periodForm = useForm({
  title: '',
  semester: '1',
  year: 2026,
  start_date: '2026-01-01',
  end_date: '2026-06-30',
  target_respondents: 250,
  status: 'published',
  is_active: true,
})

const questionForm = useForm({
  question_text: '',
  service_category: 'Persyaratan Pelayanan',
  question_type: 'rating',
  sort_order: 1,
  is_required: true,
  is_enabled: true,
})

const recForm = useForm({
  title: '',
  description: '',
  priority: 'high',
  status: 'in_progress',
  target_completion: '',
  pic: '',
})

const followUpForm = useForm({
  action_name: '',
  description: '',
  responsible_unit: 'Subbag Umum & Perlengkapan',
  progress: 50,
  completion_date: '',
  status: 'on_track',
})

// Action Modal Handlers
function openPeriodModal(period = null) {
  editingPeriod.value = period
  if (period) {
    periodForm.title = period.title
    periodForm.semester = String(period.semester)
    periodForm.year = period.year
    periodForm.start_date = period.start_date
    periodForm.end_date = period.end_date
    periodForm.target_respondents = period.target_respondents
    periodForm.status = period.status
    periodForm.is_active = Boolean(period.is_active)
  } else {
    periodForm.reset()
  }
  showPeriodModal.value = true
}

function submitPeriod() {
  if (editingPeriod.value) {
    periodForm.put(route('admin.surveys.periods.update', editingPeriod.value.id), {
      onSuccess: () => { showPeriodModal.value = false }
    })
  } else {
    periodForm.post(route('admin.surveys.periods.store'), {
      onSuccess: () => { showPeriodModal.value = false }
    })
  }
}

function openQuestionModal(question = null) {
  editingQuestion.value = question
  if (question) {
    questionForm.question_text = question.question_text
    questionForm.service_category = question.service_category
    questionForm.question_type = question.question_type
    questionForm.sort_order = question.sort_order
    questionForm.is_required = Boolean(question.is_required)
    questionForm.is_enabled = Boolean(question.is_enabled)
  } else {
    questionForm.reset()
    if (props.activePeriod && props.activePeriod.questions) {
      questionForm.sort_order = props.activePeriod.questions.length + 1
    }
  }
  showQuestionModal.value = true
}

function submitQuestion() {
  if (!props.activePeriod) return
  if (editingQuestion.value) {
    questionForm.put(route('admin.surveys.questions.update', editingQuestion.value.id), {
      onSuccess: () => { showQuestionModal.value = false }
    })
  } else {
    questionForm.post(route('admin.surveys.questions.store', props.activePeriod.id), {
      onSuccess: () => { showQuestionModal.value = false }
    })
  }
}

function deleteQuestion(question) {
  if (confirm('Apakah Anda yakin ingin menghapus pertanyaan survei ini?')) {
    router.delete(route('admin.surveys.questions.destroy', question.id))
  }
}

function openRecommendationModal(rec = null) {
  editingRecommendation.value = rec
  if (rec) {
    recForm.title = rec.title
    recForm.description = rec.description || ''
    recForm.priority = rec.priority
    recForm.status = rec.status || 'in_progress'
    recForm.target_completion = rec.target_completion || ''
    recForm.pic = rec.pic || ''
  } else {
    recForm.reset()
  }
  showRecommendationModal.value = true
}

function submitRecommendation() {
  if (!props.activePeriod) return
  if (editingRecommendation.value) {
    recForm.put(route('admin.surveys.recommendations.update', editingRecommendation.value.id), {
      onSuccess: () => { showRecommendationModal.value = false }
    })
  } else {
    recForm.post(route('admin.surveys.recommendations.store', props.activePeriod.id), {
      onSuccess: () => { showRecommendationModal.value = false }
    })
  }
}

function deleteRecommendation(rec) {
  if (confirm('Hapus rekomendasi ini?')) {
    router.delete(route('admin.surveys.recommendations.destroy', rec.id))
  }
}

function openFollowUpModal(action = null) {
  editingFollowUp.value = action
  if (action) {
    followUpForm.action_name = action.action_name
    followUpForm.description = action.description || ''
    followUpForm.responsible_unit = action.responsible_unit
    followUpForm.progress = action.progress
    followUpForm.completion_date = action.completion_date || ''
    followUpForm.status = action.status || 'on_track'
  } else {
    followUpForm.reset()
  }
  showFollowUpModal.value = true
}

function submitFollowUp() {
  if (!props.activePeriod) return
  if (editingFollowUp.value) {
    followUpForm.put(route('admin.surveys.follow-up.update', editingFollowUp.value.id), {
      onSuccess: () => { showFollowUpModal.value = false }
    })
  } else {
    followUpForm.post(route('admin.surveys.follow-up.store', props.activePeriod.id), {
      onSuccess: () => { showFollowUpModal.value = false }
    })
  }
}

function deleteFollowUp(action) {
  if (confirm('Hapus rencana tindak lanjut ini?')) {
    router.delete(route('admin.surveys.follow-up.destroy', action.id))
  }
}

function submitCopyQuestions() {
  if (!props.activePeriod || !copyForm.source_period_id) return
  copyForm
    .transform(data => ({
      source_period_id: data.source_period_id,
      overwrite: !data.keep_existing,
    }))
    .post(route('admin.surveys.questions.copy', props.activePeriod.id), {
      onSuccess: () => {
        showCopyModal.value = false
        copyForm.reset()
      }
    })
}

function deleteRespondent(r) {
  if (confirm(`Apakah Anda yakin ingin menghapus data responden "${r.respondent_name || 'Anonim'}"?`)) {
    router.delete(route('admin.surveys.responses.destroy', r.id))
  }
}

function recalculateStats() {
  if (props.activePeriod) {
    router.post(route('admin.surveys.recalculate', props.activePeriod.id))
  }
}
</script>
