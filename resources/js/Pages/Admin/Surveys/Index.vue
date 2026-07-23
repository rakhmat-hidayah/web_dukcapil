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
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-black uppercase tracking-wider text-gray-400">Pengaturan Pertanyaan Unsur PermenPANRB</h3>
          <button @click="openQuestionModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">
            + Tambah Pertanyaan
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(q, idx) in activePeriod?.questions" :key="q.id" class="p-4 bg-gray-50 dark:bg-zinc-800/80 rounded-xl border border-gray-100 dark:border-zinc-700 flex items-center justify-between gap-4">
            <div class="space-y-1">
              <span class="text-[10px] font-black uppercase text-primary-600 font-mono">Unsur {{ idx + 1 }} — {{ q.service_category }}</span>
              <p class="text-xs font-bold text-gray-900 dark:text-zinc-100">{{ q.question_text }}</p>
            </div>
            <div class="flex items-center gap-2">
              <button @click="openQuestionModal(q)" class="px-3 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold rounded-lg text-xs cursor-pointer">
                Edit
              </button>
              <button @click="deleteQuestion(q)" class="px-3 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold rounded-lg text-xs cursor-pointer">
                Hapus
              </button>
            </div>
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
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { RefreshCw } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  periods: Array,
  activePeriod: Object,
  chartData: Object,
  archives: Array,
})

const activeTab = ref('dashboard')

const tabs = [
  { id: 'dashboard', label: 'Dashboard Overview' },
  { id: 'period', label: 'Periode Survei' },
  { id: 'questions', label: 'Form Pertanyaan' },
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

function recalculateStats() {
  if (props.activePeriod) {
    router.post(route('admin.surveys.recalculate', props.activePeriod.id))
  }
}
</script>
