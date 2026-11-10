<template>
  <Head title="Layanan Online KTP-el - Disdukcapil Kabupaten Dompu" />

  <PublicLayout :hide-footer="true" :compact-main="true">
    <div class="bg-slate-50 dark:bg-slate-950 pb-20 md:pb-16 text-slate-800 dark:text-slate-100">
      
      <!-- Top App Container -->
      <div class="max-w-4xl mx-auto px-2 sm:px-6 pt-1.5 sm:pt-4 space-y-3 sm:space-y-4">

        <!-- Compact Maintenance Alert Bar (If Active) -->
        <div 
          v-if="maintenance?.is_active" 
          @click="showMaintenanceModal = true"
          class="px-2.5 py-1.5 rounded-xl bg-amber-500/10 dark:bg-amber-500/15 border border-amber-400/60 dark:border-amber-600/50 flex items-center justify-between gap-2 shadow-xs text-amber-950 dark:text-amber-200 cursor-pointer hover:bg-amber-500/15 transition group"
        >
          <div class="flex items-center gap-2 min-w-0">
            <div class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
              <AlertCircle class="w-3.5 h-3.5" />
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="inline-block px-1.5 py-0.5 rounded text-[8.5px] font-black uppercase tracking-wider bg-amber-600 text-white leading-none shrink-0">
                  Pemeliharaan
                </span>
                <p class="text-[11px] sm:text-xs font-bold text-amber-950 dark:text-amber-100 truncate">
                  {{ maintenance.title || 'Pendaftaran Online Sedang Ditutup Sementara' }}
                </p>
              </div>
              <p v-if="maintenance.estimated_end" class="text-[9px] sm:text-[10px] text-amber-800 dark:text-amber-300 font-medium truncate mt-0.5">
                Estimasi Dibuka: <strong class="font-bold">{{ maintenance.estimated_end }}</strong>
              </p>
            </div>
          </div>
          <button
            type="button"
            class="shrink-0 px-2 py-1 rounded-lg bg-amber-600 group-hover:bg-amber-500 text-white font-bold text-[10.5px] transition shadow-xs flex items-center gap-0.5 cursor-pointer"
          >
            <span>Detail</span>
            <ChevronRight class="w-3 h-3" />
          </button>
        </div>

        <!-- Mobile Mode Layar Penuh Banner (Hilangkan Bilah Alamat Atas & Navigasi Bawah) -->
        <div 
          v-if="!isStandalone" 
          @click="showFullscreenGuide = true"
          class="sm:hidden px-3.5 py-2.5 rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-primary-700 text-white flex items-center justify-between gap-2 shadow-sm cursor-pointer hover:opacity-95 transition active:scale-[0.99]"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
              <Smartphone class="w-4 h-4 text-white" />
            </div>
            <div class="min-w-0">
              <p class="font-black text-[11px] leading-tight truncate">Mode Layar Penuh (App Mode)</p>
              <p class="text-[9.5px] text-blue-100 leading-tight">Hilangkan bilah alamat atas &amp; navigasi bawah</p>
            </div>
          </div>
          <span class="shrink-0 px-2.5 py-1 rounded-lg bg-white text-blue-700 font-black text-[10px] shadow-xs">
            Buka
          </span>
        </div>

        <!-- 2. SECTION LAYANAN & SURVEY IKM BUTTON (Matching photo) -->
        <div class="space-y-2.5 sm:space-y-3.5">
          <div class="flex items-center justify-between px-0.5">
            <div>
              <h2 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Layanan
              </h2>
              <p class="text-[10.5px] sm:text-xs text-slate-500 dark:text-slate-400">
                Pilih menu pelayanan KTP-el di bawah ini untuk memulai pengajuan
              </p>
            </div>

            <!-- Green Pill "★ Isi Survey IKM" Button & Desktop Applicant Login -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
              <Link
                v-if="$page.props.auth?.applicant"
                :href="route('applicant.dashboard')"
                class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs sm:text-sm font-black shadow-sm transition active:scale-95 shrink-0"
              >
                <UserCheck class="w-4 h-4" />
                <span>Akun Pemohon</span>
              </Link>
              <Link
                v-else
                :href="route('applicant.login')"
                class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs sm:text-sm font-black shadow-sm transition active:scale-95 shrink-0"
              >
                <LogIn class="w-4 h-4" />
                <span>Login Pemohon</span>
              </Link>

              <Link
                :href="route('public.survey.index')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] sm:text-xs font-black shadow-sm transition active:scale-95 shrink-0"
              >
                <Star class="w-3.5 h-3.5 fill-current text-amber-300" />
                <span>Isi Survey IKM</span>
              </Link>
            </div>
          </div>

          <!-- 3. CIRCULAR NEUMORPHIC ICON GRID (4 Core Services Only) -->
          <div class="grid grid-cols-4 gap-1.5 sm:gap-4 py-4 px-1.5 sm:py-6 sm:px-5 bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800">
            
            <!-- Icon 1: KTP Hilang -->
            <button
              type="button"
              @click="openServiceModalBySlug('cetak-ktp-hilang')"
              class="group flex flex-col items-center text-center p-1 sm:p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none cursor-pointer"
            >
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-sky-400 to-blue-600 flex items-center justify-center text-white shadow-md shadow-sky-500/25 ring-3 sm:ring-6 ring-sky-100 dark:ring-sky-950/60 group-hover:scale-105 transition-transform duration-200">
                <!-- Neumorphic 3D styled inner icon -->
                <CreditCard class="w-7 h-7 sm:w-9 sm:h-9" />
              </div>
              <span class="mt-2 text-[11.5px] sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                KTP-el Hilang
              </span>
              <span class="text-[9px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5">Surat Hilang</span>
            </button>

            <!-- Icon 2: KTP Rusak -->
            <button
              type="button"
              @click="openServiceModalBySlug('cetak-ktp-rusak')"
              class="group flex flex-col items-center text-center p-1 sm:p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none cursor-pointer"
            >
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25 ring-3 sm:ring-6 ring-blue-100 dark:ring-blue-950/60 group-hover:scale-105 transition-transform duration-200">
                <RotateCcw class="w-7 h-7 sm:w-9 sm:h-9" />
              </div>
              <span class="mt-2 text-[11.5px] sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                KTP-el Rusak
              </span>
              <span class="text-[9px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5">Fisik Patah/Pudar</span>
            </button>

            <!-- Icon 3: Perubahan Data -->
            <button
              type="button"
              @click="openServiceModalBySlug('perubahan-elemen-data')"
              class="group flex flex-col items-center text-center p-1 sm:p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none cursor-pointer"
            >
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-cyan-400 to-teal-600 flex items-center justify-center text-white shadow-md shadow-teal-500/25 ring-3 sm:ring-6 ring-teal-100 dark:ring-teal-950/60 group-hover:scale-105 transition-transform duration-200">
                <FileText class="w-7 h-7 sm:w-9 sm:h-9" />
              </div>
              <span class="mt-2 text-[11.5px] sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Ubah Elemen Data
              </span>
              <span class="text-[9px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5">Status / Alamat</span>
            </button>

            <!-- Icon 4: Perekaman Baru (Pemula) -->
            <button
              type="button"
              @click="openServiceModalBySlug('perekaman-ktp-el-baru')"
              class="group flex flex-col items-center text-center p-1 sm:p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none cursor-pointer"
            >
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-emerald-400 to-green-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/25 ring-3 sm:ring-6 ring-emerald-100 dark:ring-emerald-950/60 group-hover:scale-105 transition-transform duration-200">
                <Camera class="w-7 h-7 sm:w-9 sm:h-9" />
              </div>
              <span class="mt-2 text-[11.5px] sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Perekaman Baru
              </span>
              <span class="text-[9px] sm:text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5">Pemula 16-17 Thn</span>
            </button>

          </div>
        </div>

        <!-- 4. BANNER CAROUSEL INOVASI & EDUKASI (Exact like Grobogan bottom slider) -->
        <div class="space-y-1.5 pt-1 sm:space-y-2 sm:pt-2">
          <div class="flex items-center justify-between px-0.5">
            <h3 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
              Inovasi & Informasi Pelayanan
            </h3>
            <span class="text-[10px] sm:text-[11px] text-slate-600 dark:text-slate-400">Geser untuk info lengkap &rarr;</span>
          </div>

          <!-- Carousel Slider Component -->
          <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-slate-100 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div 
              class="flex transition-transform duration-500 ease-out"
              :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
            >
              <!-- Slide 1: Bebas Pungli / 100% Gratis -->
              <div class="w-full shrink-0 p-4 sm:p-6 bg-gradient-to-r from-blue-700 via-indigo-800 to-slate-900 text-white min-h-[190px] sm:min-h-[220px] flex flex-col justify-between">
                <div>
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-[8.5px] sm:text-[9.5px] font-black uppercase tracking-wider bg-amber-400 text-slate-900 mb-1.5 shadow-xs">
                    Inovasi Layanan!
                  </span>
                  <h4 class="text-base sm:text-xl font-black tracking-tight leading-snug">
                    Pelayanan KTP-el Cepat, Tepat & Bebas Calo
                  </h4>
                  <p class="text-[11px] sm:text-xs text-slate-200 mt-1 max-w-xl leading-relaxed">
                    Seluruh proses pendaftaran dan pencetakan KTP-el di Dinas Dukcapil Kabupaten Dompu 100% GRATIS (Rp 0) tanpa dipungut biaya apapun.
                  </p>
                </div>
                <div class="flex items-center gap-2 pt-2 text-[9.5px] sm:text-xs font-bold text-emerald-300">
                  <span>✓ LEBIH CEPAT</span>
                  <span>•</span>
                  <span>✓ LEBIH AMAN</span>
                  <span>•</span>
                  <span>✓ BEBAS ANTRE</span>
                </div>
              </div>

              <!-- Slide 2: Panduan Foto Selfie & Berkas HP -->
              <div class="w-full shrink-0 p-4 sm:p-6 bg-gradient-to-r from-emerald-800 via-teal-900 to-slate-900 text-white min-h-[190px] sm:min-h-[220px] flex flex-col justify-between">
                <div>
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-[8.5px] sm:text-[9.5px] font-black uppercase tracking-wider bg-emerald-400 text-slate-900 mb-1.5 shadow-xs">
                    Tips Unggah Berkas
                  </span>
                  <h4 class="text-base sm:text-xl font-black tracking-tight leading-snug">
                    Pra-Verifikasi Dokumen Cukup Dari Handphone
                  </h4>
                  <p class="text-[11px] sm:text-xs text-emerald-100 mt-1 max-w-xl leading-relaxed">
                    Ambil foto berkas asli menggunakan kamera handphone dengan pencahayaan terang dan tegak lurus agar verifikasi berkas langsung disetujui petugas.
                  </p>
                </div>
                <div class="flex items-center gap-2 pt-2 text-[9.5px] sm:text-xs font-bold text-amber-300">
                  <span>📷 Foto Wajah Selfie Asli</span>
                  <span>•</span>
                  <span>📄 Dokumen Tidak Buram</span>
                </div>
              </div>

              <!-- Slide 3: Database Banners (If any from CMS) -->
              <template v-for="b in banners" :key="b.id">
                <div 
                  v-if="b.image_url"
                  class="w-full shrink-0 min-h-[190px] sm:min-h-[220px] relative bg-slate-900 text-white flex flex-col justify-end p-4 sm:p-6"
                >
                  <img :src="b.image_url" :alt="b.title" class="absolute inset-0 w-full h-full object-cover opacity-60" />
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
                  <div class="relative z-10">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[8.5px] sm:text-[9.5px] font-black uppercase tracking-wider bg-blue-500 text-white mb-1.5 shadow-xs">
                      Informasi Resmi
                    </span>
                    <h4 class="text-sm sm:text-lg font-black tracking-tight leading-snug">{{ b.title }}</h4>
                    <p class="text-[11px] sm:text-xs text-slate-200 mt-1 line-clamp-2 max-w-xl leading-relaxed">{{ b.subtitle }}</p>
                  </div>
                </div>
              </template>
            </div>

            <!-- Carousel Controls & Dots -->
            <div class="absolute bottom-2.5 right-3 flex items-center gap-1.5 z-20">
              <button
                v-for="(dot, idx) in totalSlides"
                :key="idx"
                type="button"
                @click="currentSlide = idx"
                class="w-2 h-2 rounded-full transition-all"
                :class="currentSlide === idx ? 'bg-white w-5' : 'bg-white/40 hover:bg-white/70'"
                :aria-label="'Slide ' + (idx + 1)"
              ></button>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- MODAL 1: SERVICE DETAIL & REQUIREMENT CHECKLIST (Opened on service click) -->
    <Teleport to="body">
      <div 
        v-if="selectedService"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm transition-opacity"
        @click.self="selectedService = null"
      >
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-t-3xl rounded-b-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 mb-[calc(56px+env(safe-area-inset-bottom,0px))] sm:mb-0 max-h-[calc(85vh-56px)] sm:max-h-[85vh] flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-200">
          <!-- Modal Header -->
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-3">
              <div 
                class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shadow-md font-bold"
                :style="{ backgroundColor: selectedService.color || '#2563eb' }"
              >
                <CreditCard class="w-6 h-6" />
              </div>
              <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white leading-tight">
                  {{ selectedService.name }}
                </h3>
                <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                  <span>Kuota: <strong>{{ selectedService.quota_per_day }}/Hari</strong></span>
                  <span>•</span>
                  <span class="text-emerald-600 dark:text-emerald-400 font-bold">Proses: {{ selectedService.processing_days_label }}</span>
                </div>
              </div>
            </div>
            <button 
              type="button" 
              @click="selectedService = null"
              class="w-8 h-8 rounded-full bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center transition"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Modal Body (Requirements list) -->
          <div class="p-5 overflow-y-auto space-y-4 text-xs">
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
              {{ selectedService.short_description || selectedService.description }}
            </p>

            <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
              <div class="flex items-center justify-between">
                <h4 class="font-extrabold text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                  Dokumen Persyaratan ({{ selectedService.documents?.length || 0 }}):
                </h4>
                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold">Foto langsung dari HP</span>
              </div>

              <div class="space-y-2">
                <div
                  v-for="(doc, idx) in selectedService.documents"
                  :key="doc.id"
                  class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 flex items-start justify-between gap-3"
                >
                  <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                      {{ idx + 1 }}
                    </span>
                    <div>
                      <p class="font-bold text-slate-800 dark:text-slate-200">{{ doc.label }}</p>
                      <p v-if="doc.instruction" class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                        {{ doc.instruction }}
                      </p>
                    </div>
                  </div>

                  <div class="shrink-0 flex flex-col items-end gap-1">
                    <span 
                      v-if="doc.is_required"
                      class="px-2 py-0.5 rounded text-[9px] font-black bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-400"
                    >
                      Wajib
                    </span>
                    <span 
                      v-else
                      class="px-2 py-0.5 rounded text-[9px] font-medium bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300"
                    >
                      Opsional
                    </span>
                    <a
                      v-if="getDownloadLink(doc)"
                      :href="getDownloadLink(doc)"
                      target="_blank"
                      download
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] transition shadow-xs mt-1 cursor-pointer"
                    >
                      <FileDown class="w-3.5 h-3.5" />
                      <span>Download Formulir</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer CTA -->
          <div class="p-4 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <button
              type="button"
              @click="selectedService = null"
              class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            >
              Batal
            </button>
            <Link
              :href="route('queue.apply.create', { layanan: selectedService.slug })"
              class="flex-1 px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-black text-xs transition shadow-md shadow-blue-600/25 flex items-center justify-center gap-2"
            >
              <span>Mulai Pendaftaran Sekarang</span>
              <ArrowRight class="w-4 h-4" />
            </Link>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL 2: PERSYARATAN BERKAS & FORMULIR LENGKAP -->
    <Teleport to="body">
      <div 
        v-if="showRequirementsModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm"
        @click.self="showRequirementsModal = false"
      >
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-t-3xl rounded-b-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 mb-[calc(56px+env(safe-area-inset-bottom,0px))] sm:mb-0 max-h-[calc(85vh-56px)] sm:max-h-[85vh] flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-200">
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
              <ClipboardList class="w-5 h-5 text-blue-600" />
              <h3 class="text-base font-black text-slate-900 dark:text-white">Persyaratan Berkas KTP-el</h3>
            </div>
            <button type="button" @click="showRequirementsModal = false" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center cursor-pointer">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="p-5 overflow-y-auto space-y-4 text-xs">
            <div v-for="srv in services" :key="srv.id" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 space-y-3">
              <h4 class="font-black text-sm text-slate-900 dark:text-white">{{ srv.name }}</h4>
              
              <ul class="space-y-2 text-slate-700 dark:text-slate-300">
                <li 
                  v-for="doc in srv.documents" 
                  :key="doc.id"
                  class="p-2.5 rounded-xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                >
                  <div class="flex items-start gap-2 min-w-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                    <div class="text-xs">
                      <span class="font-bold text-slate-900 dark:text-slate-100">{{ doc.label }}</span>
                      <span v-if="doc.is_required" class="text-rose-500 font-bold text-[11px] ml-1">(Wajib)</span>
                      <span v-else class="text-slate-600 dark:text-slate-400 text-[11px] ml-1">(Opsional)</span>
                    </div>
                  </div>

                  <!-- Download Button for Formulir F-1.02, F-1.07, etc. -->
                  <a
                    v-if="getDownloadLink(doc)"
                    :href="getDownloadLink(doc)"
                    target="_blank"
                    download
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-xs shrink-0 self-start sm:self-auto cursor-pointer"
                  >
                    <FileDown class="w-3.5 h-3.5" />
                    <span>Download Formulir</span>
                  </a>
                </li>
              </ul>

              <div class="pt-2 flex justify-end">
                <Link 
                  :href="route('queue.apply.create', { layanan: srv.slug })" 
                  class="inline-flex items-center gap-1 font-bold text-blue-600 dark:text-blue-400 hover:underline"
                >
                  <span>Daftar Layanan Ini &rarr;</span>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL 3: JADWAL & KUOTA LOKET -->
    <Teleport to="body">
      <div 
        v-if="showQuotaModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm"
        @click.self="showQuotaModal = false"
      >
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl rounded-b-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 mb-[calc(56px+env(safe-area-inset-bottom,0px))] sm:mb-0 max-h-[calc(85vh-56px)] sm:max-h-[85vh] overflow-y-auto animate-in slide-in-from-bottom duration-200">
          <div class="flex items-center justify-between border-b pb-3 border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
              <Calendar class="w-5 h-5 text-blue-600" />
              <h3 class="text-base font-black text-slate-900 dark:text-white">Jadwal & Kuota Pelayanan</h3>
            </div>
            <button type="button" @click="showQuotaModal = false" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-3 text-xs">
            <div class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 flex justify-between items-center">
              <span class="font-medium text-slate-600 dark:text-slate-300">Jam Layanan Loket:</span>
              <span class="font-black text-slate-900 dark:text-white">{{ todayStats.open_time }} - {{ todayStats.close_time }} WITA</span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-center">
              <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900">
                <p class="text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold">Sisa Kuota Hari Ini</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-300 mt-1">{{ todayStats.total_remaining }}</p>
                <p class="text-[10px] text-slate-600 dark:text-slate-400">dari {{ todayStats.total_quota }} Slot</p>
              </div>
              <div class="p-3 rounded-2xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900">
                <p class="text-[11px] text-purple-700 dark:text-purple-400 font-semibold">Terisi / Terdaftar</p>
                <p class="text-2xl font-black text-purple-600 dark:text-purple-300 mt-1">{{ todayStats.total_booked }}</p>
                <p class="text-[10px] text-slate-600 dark:text-slate-400">Pemohon</p>
              </div>
            </div>

            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-start gap-2">
              <Info class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
              <span>Lokasi pengambilan KTP-el: Kantor Dinas Dukcapil Kabupaten Dompu, {{ settings.office_address }}.</span>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL 4: DETAIL INFORMASI PEMELIHARAAN SISTEM -->
    <Teleport to="body">
      <div 
        v-if="showMaintenanceModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm"
        @click.self="showMaintenanceModal = false"
      >
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl rounded-b-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 mb-[calc(56px+env(safe-area-inset-bottom,0px))] sm:mb-0 max-h-[calc(85vh-56px)] sm:max-h-[85vh] overflow-y-auto animate-in slide-in-from-bottom duration-200">
          <div class="flex items-center justify-between border-b pb-3 border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-xs">
                <AlertCircle class="w-4 h-4" />
              </div>
              <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white leading-tight">Informasi Pemeliharaan</h3>
                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold uppercase tracking-wider">Layanan Pendaftaran Online</span>
              </div>
            </div>
            <button 
              type="button" 
              @click="showMaintenanceModal = false" 
              class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 transition cursor-pointer"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-3 text-xs leading-relaxed">
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60">
              <h4 class="font-bold text-amber-950 dark:text-amber-200 text-sm mb-1.5">
                {{ maintenance.title || 'Layanan Pendaftaran Online Sedang Ditutup Sementara' }}
              </h4>
              <p class="text-amber-900/90 dark:text-amber-300 text-xs whitespace-pre-line leading-relaxed">
                {{ maintenance.message }}
              </p>
            </div>

            <div v-if="maintenance.estimated_end" class="p-3 rounded-2xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/60 flex items-center justify-between">
              <span class="font-medium text-blue-900 dark:text-blue-300">Estimasi Selesai:</span>
              <span class="font-black text-blue-700 dark:text-blue-400">{{ maintenance.estimated_end }}</span>
            </div>

            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/80 text-[11px] text-slate-600 dark:text-slate-300 flex items-start gap-2">
              <Info class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
              <span>Bagi masyarakat yang membutuhkan pelayanan mendesak, silakan langsung hadir ke loket kantor Disdukcapil Kabupaten Dompu: {{ settings.office_address || 'Jl. Soekarno Hatta No. 09 Dompu' }}.</span>
            </div>
          </div>

          <div class="pt-2 flex justify-end">
            <button
              type="button"
              @click="showMaintenanceModal = false"
              class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs transition cursor-pointer"
            >
              Saya Mengerti & Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL 5: LACAK STATUS PERMOHONAN -->
    <Teleport to="body">
      <div 
        v-if="showTrackingModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm"
        @click.self="showTrackingModal = false"
      >
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl rounded-b-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 mb-[calc(56px+env(safe-area-inset-bottom,0px))] sm:mb-0 max-h-[calc(85vh-56px)] sm:max-h-[85vh] overflow-y-auto animate-in slide-in-from-bottom duration-200">
          <div class="flex items-center justify-between border-b pb-3 border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-xs">
                <Search class="w-4 h-4" />
              </div>
              <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white leading-tight">Lacak Status Permohonan</h3>
                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">Antrian & Pra-Verifikasi</span>
              </div>
            </div>
            <button 
              type="button" 
              @click="showTrackingModal = false" 
              class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 transition cursor-pointer"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- KONDISI 1: Belum Login Pemohon (Wajib Login Terlebih Dahulu) -->
          <div v-if="!$page.props.auth?.applicant" class="py-1 space-y-4">
            <div class="p-4 rounded-2xl bg-amber-500/10 dark:bg-amber-500/15 border border-amber-300/80 dark:border-amber-600/50 flex items-start gap-3">
              <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                <Lock class="w-5 h-5" />
              </div>
              <div class="space-y-1">
                <h4 class="text-xs font-black text-amber-950 dark:text-amber-100">
                  Login Pemohon Diperlukan
                </h4>
                <p class="text-[11px] text-amber-900/80 dark:text-amber-200 leading-relaxed">
                  Untuk melindungi kerahasiaan data kependudukan dan berkas pribadi Anda, fitur pelacakan status permohonan kini mewajibkan Anda untuk masuk ke <strong>Akun Pemohon</strong> terlebih dahulu.
                </p>
              </div>
            </div>

            <div class="space-y-2 pt-1">
              <Link
                :href="route('applicant.login')"
                class="w-full py-3 px-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-md shadow-blue-600/25 flex items-center justify-center gap-2 cursor-pointer"
              >
                <LogIn class="w-4 h-4" />
                <span>Masuk ke Akun Pemohon</span>
              </Link>

              <Link
                :href="route('applicant.register')"
                class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer"
              >
                <span>Belum punya akun? Daftar Baru</span>
              </Link>
            </div>
          </div>

          <!-- KONDISI 2: Sudah Login Pemohon -->
          <div v-else class="space-y-3.5">
            <div class="p-3 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 flex items-center justify-between gap-2 text-xs">
              <div class="flex items-center gap-2 min-w-0">
                <UserCheck class="w-4 h-4 text-blue-600 shrink-0" />
                <span class="truncate font-semibold text-slate-800 dark:text-slate-200">
                  {{ $page.props.auth.applicant.name }} ({{ $page.props.auth.applicant.masked_nik }})
                </span>
              </div>
              <Link :href="route('applicant.dashboard')" class="text-blue-600 dark:text-blue-400 font-bold hover:underline shrink-0 text-[11px]">
                Dashboard &rarr;
              </Link>
            </div>

            <Link
              :href="route('applicant.dashboard')"
              class="w-full py-2.5 px-4 rounded-2xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-2"
            >
              <FolderClock class="w-4 h-4 text-amber-400" />
              <span>Buka Seluruh Riwayat Permohonan Saya</span>
            </Link>

            <div class="relative flex py-1 items-center">
              <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
              <span class="flex-shrink mx-3 text-[10px] text-slate-400 uppercase font-bold tracking-wider">atau cari nomor registrasi</span>
              <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
            </div>

            <form @submit.prevent="submitSearch" class="space-y-3">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Nomor Registrasi
                </label>
                <div class="relative">
                  <input
                    v-model="searchForm.query"
                    type="text"
                    placeholder="Contoh: REG-2611-XXXXXX"
                    required
                    class="w-full pl-4 pr-10 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <span v-if="searchForm.query" @click="searchForm.query = ''" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 cursor-pointer">
                    <X class="w-4 h-4" />
                  </span>
                </div>
              </div>

              <p v-if="searchForm.errors.query" class="text-xs text-rose-500 font-semibold">
                {{ searchForm.errors.query }}
              </p>

              <button
                type="submit"
                :disabled="searchForm.processing"
                class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-md shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <span>{{ searchForm.processing ? 'Memeriksa...' : 'Lacak Status Pengajuan' }}</span>
                <ArrowRight class="w-4 h-4" />
              </button>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL PANDUAN MODE LAYAR PENUH (APP STANDALONE) -->
    <MobileFullscreenGuideModal v-model="showFullscreenGuide" />

  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import MobileFullscreenGuideModal from '@/Components/MobileFullscreenGuideModal.vue';
import {
  CreditCard, RotateCcw, FileText, Camera, Search, FolderClock,
  Calendar, ClipboardList, MessageCircle, Star, AlertCircle, CheckCircle2,
  ArrowRight, ChevronRight, X, FileDown, Info, UserCheck, LogIn, Lock, Smartphone
} from '@lucide/vue';

const props = defineProps({
  services: {
    type: Array,
    default: () => [],
  },
  todayStats: {
    type: Object,
    default: () => ({}),
  },
  upcomingSchedules: {
    type: Array,
    default: () => [],
  },
  settings: {
    type: Object,
    default: () => ({}),
  },
  maintenance: {
    type: Object,
    default: () => ({}),
  },
  banners: {
    type: Array,
    default: () => [],
  },
  portalLayout: {
    type: String,
    default: 'app_portal',
  },
});

const showFullscreenGuide = ref(false);
const isStandalone = ref(false);

const searchForm = useForm({
  query: '',
});

function submitSearch() {
  searchForm.post(route('queue.check'));
}

const selectedService = ref(null);
const showRequirementsModal = ref(false);
const showQuotaModal = ref(false);
const showMaintenanceModal = ref(false);
const showTrackingModal = ref(false);

function openServiceModalBySlug(slug) {
  const match = props.services.find(s => s.slug === slug);
  if (match) {
    selectedService.value = match;
  } else if (props.services.length > 0) {
    selectedService.value = props.services[0];
  }
}

// Banner Carousel State
const currentSlide = ref(0);
const totalSlides = computed(() => 2 + (props.banners?.length || 0));
let slideTimer = null;

onMounted(() => {
  if (typeof window !== 'undefined') {
    isStandalone.value = window.matchMedia('(display-mode: standalone)').matches || 
                         window.navigator.standalone === true ||
                         document.referrer.includes('android-app://');
  }
  slideTimer = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % totalSlides.value;
  }, 5000);
});

onUnmounted(() => {
  if (slideTimer) clearInterval(slideTimer);
});

function scrollToTracking() {
  showTrackingModal.value = true;
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cleanPhone(phone) {
  if (!phone) return '6282124824114';
  return phone.replace(/[^0-9]/g, '');
}

function getDownloadLink(doc) {
  if (doc?.template_url) return doc.template_url;
  const label = (doc?.label || '').toLowerCase();
  if (label.includes('f-1.02') || label.includes('f1.02') || label.includes('pendaftaran peristiwa')) {
    return '/downloads/stream/19';
  }
  if (label.includes('f-1.07') || label.includes('f1.07') || label.includes('kuasa')) {
    return '/downloads/stream/24';
  }
  if (label.includes('f-1.06') || label.includes('f1.06') || label.includes('perubahan elemen')) {
    return '/downloads/stream/23';
  }
  return null;
}
</script>
