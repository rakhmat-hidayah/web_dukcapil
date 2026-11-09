<template>
  <Head title="Layanan Online KTP-el - Disdukcapil Kabupaten Dompu" />

  <PublicLayout :hide-footer="true">
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-24 md:pb-16 text-slate-800 dark:text-slate-100">
      
      <!-- Top App Container -->
      <div class="max-w-4xl mx-auto px-3 sm:px-6 pt-4 sm:pt-6 space-y-5">

        <!-- Compact Maintenance Alert Bar (If Active) -->
        <div 
          v-if="maintenance?.is_active" 
          @click="showMaintenanceModal = true"
          class="px-3.5 py-2.5 rounded-2xl bg-amber-500/10 dark:bg-amber-500/15 border border-amber-400/60 dark:border-amber-600/50 flex items-center justify-between gap-2.5 shadow-xs text-amber-950 dark:text-amber-200 cursor-pointer hover:bg-amber-500/15 transition group"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-7 h-7 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
              <AlertCircle class="w-4 h-4" />
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-600 text-white leading-none shrink-0">
                  Pemeliharaan
                </span>
                <p class="text-xs font-bold text-amber-950 dark:text-amber-100 truncate">
                  {{ maintenance.title || 'Pendaftaran Online Sedang Ditutup Sementara' }}
                </p>
              </div>
              <p v-if="maintenance.estimated_end" class="text-[10px] text-amber-800 dark:text-amber-300 font-medium truncate mt-0.5">
                Estimasi Dibuka: <strong class="font-bold">{{ maintenance.estimated_end }}</strong>
              </p>
            </div>
          </div>
          <button
            type="button"
            class="shrink-0 px-2.5 py-1.5 rounded-xl bg-amber-600 group-hover:bg-amber-500 text-white font-bold text-[11px] transition shadow-xs flex items-center gap-1 cursor-pointer"
          >
            <span>Detail</span>
            <ChevronRight class="w-3.5 h-3.5" />
          </button>
        </div>

        <!-- 2. SECTION LAYANAN & SURVEY IKM BUTTON (Matching photo) -->
        <div class="space-y-4">
          <div class="flex items-center justify-between px-1">
            <div>
              <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">
                Layanan
              </h2>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">
                Pilih menu pelayanan KTP-el di bawah ini untuk memulai pengajuan
              </p>
            </div>

            <!-- Green Pill "★ Isi Survey IKM" Button (Exact like Grobogan screenshot) -->
            <Link
              :href="route('public.survey.index')"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-md shadow-emerald-600/25 transition active:scale-95 shrink-0"
            >
              <Star class="w-3.5 h-3.5 fill-current text-amber-300" />
              <span>Isi Survey IKM</span>
            </Link>
          </div>

          <!-- 3. CIRCULAR NEUMORPHIC ICON GRID (4 Core Services Only) -->
          <div class="grid grid-cols-4 gap-2 sm:gap-4 p-4 sm:p-6 bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800">
            
            <!-- Icon 1: KTP Hilang -->
            <button
              type="button"
              @click="openServiceModalBySlug('cetak-ktp-hilang')"
              class="group flex flex-col items-center text-center p-1.5 sm:p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none cursor-pointer"
            >
              <div class="w-14 h-14 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-sky-400 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 ring-4 ring-sky-100 dark:ring-sky-950/60 group-hover:scale-105 transition-transform duration-200">
                <!-- Neumorphic 3D styled inner icon -->
                <CreditCard class="w-6 h-6 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2 text-[11px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                KTP-el Hilang
              </span>
              <span class="text-[9px] sm:text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Surat Hilang</span>
            </button>

            <!-- Icon 2: KTP Rusak -->
            <button
              type="button"
              @click="openServiceModalBySlug('cetak-ktp-rusak')"
              class="group flex flex-col items-center text-center p-1.5 sm:p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none cursor-pointer"
            >
              <div class="w-14 h-14 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/25 ring-4 ring-blue-100 dark:ring-blue-950/60 group-hover:scale-105 transition-transform duration-200">
                <RotateCcw class="w-6 h-6 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2 text-[11px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                KTP-el Rusak
              </span>
              <span class="text-[9px] sm:text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Fisik Patah/Pudar</span>
            </button>

            <!-- Icon 3: Perubahan Data -->
            <button
              type="button"
              @click="openServiceModalBySlug('perubahan-elemen-data')"
              class="group flex flex-col items-center text-center p-1.5 sm:p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none cursor-pointer"
            >
              <div class="w-14 h-14 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-cyan-400 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-teal-500/25 ring-4 ring-teal-100 dark:ring-teal-950/60 group-hover:scale-105 transition-transform duration-200">
                <FileText class="w-6 h-6 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2 text-[11px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Ubah Elemen Data
              </span>
              <span class="text-[9px] sm:text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Status / Alamat</span>
            </button>

            <!-- Icon 4: Perekaman Baru (Pemula) -->
            <button
              type="button"
              @click="openServiceModalBySlug('perekaman-ktp-el-baru')"
              class="group flex flex-col items-center text-center p-1.5 sm:p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none cursor-pointer"
            >
              <div class="w-14 h-14 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-emerald-400 to-green-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25 ring-4 ring-emerald-100 dark:ring-emerald-950/60 group-hover:scale-105 transition-transform duration-200">
                <Camera class="w-6 h-6 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2 text-[11px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Perekaman Baru
              </span>
              <span class="text-[9px] sm:text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Pemula 16-17 Thn</span>
            </button>

          </div>
        </div>

        <!-- 4. BANNER CAROUSEL INOVASI & EDUKASI (Exact like Grobogan bottom slider) -->
        <div class="space-y-3 pt-2">
          <div class="flex items-center justify-between px-1">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-blue-500"></span>
              Inovasi & Informasi Pelayanan
            </h3>
            <span class="text-[11px] text-slate-600 dark:text-slate-400">Geser untuk info lengkap &rarr;</span>
          </div>

          <!-- Carousel Slider Component -->
          <div class="relative overflow-hidden rounded-3xl bg-slate-100 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div 
              class="flex transition-transform duration-500 ease-out"
              :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
            >
              <!-- Slide 1: Bebas Pungli / 100% Gratis -->
              <div class="w-full shrink-0 p-5 sm:p-6 bg-gradient-to-r from-blue-700 via-indigo-800 to-slate-900 text-white min-h-[160px] sm:min-h-[180px] flex flex-col justify-between">
                <div>
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400 text-slate-900 mb-2">
                    Inovasi Layanan!
                  </span>
                  <h4 class="text-lg sm:text-xl font-black tracking-tight leading-snug">
                    Pelayanan KTP-el Cepat, Tepat & Bebas Calo
                  </h4>
                  <p class="text-xs text-slate-200 mt-1 max-w-xl leading-relaxed">
                    Seluruh proses pendaftaran dan pencetakan KTP-el di Dinas Dukcapil Kabupaten Dompu 100% GRATIS (Rp 0) tanpa dipungut biaya apapun.
                  </p>
                </div>
                <div class="flex items-center gap-3 pt-3 text-[11px] font-bold text-emerald-300">
                  <span>✓ LEBIH CEPAT</span>
                  <span>•</span>
                  <span>✓ LEBIH AMAN</span>
                  <span>•</span>
                  <span>✓ BEBAS ANTRE</span>
                </div>
              </div>

              <!-- Slide 2: Panduan Foto Selfie & Berkas HP -->
              <div class="w-full shrink-0 p-5 sm:p-6 bg-gradient-to-r from-emerald-800 via-teal-900 to-slate-900 text-white min-h-[160px] sm:min-h-[180px] flex flex-col justify-between">
                <div>
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400 text-slate-900 mb-2">
                    Tips Unggah Berkas
                  </span>
                  <h4 class="text-lg sm:text-xl font-black tracking-tight leading-snug">
                    Pra-Verifikasi Dokumen Cukup Dari Handphone
                  </h4>
                  <p class="text-xs text-emerald-100 mt-1 max-w-xl leading-relaxed">
                    Ambil foto berkas asli menggunakan kamera handphone dengan pencahayaan terang dan tegak lurus agar verifikasi berkas langsung disetujui petugas.
                  </p>
                </div>
                <div class="flex items-center gap-3 pt-3 text-[11px] font-bold text-amber-300">
                  <span>📷 Foto Wajah Selfie Asli</span>
                  <span>•</span>
                  <span>📄 Dokumen Tidak Buram</span>
                </div>
              </div>

              <!-- Slide 3: Database Banners (If any from CMS) -->
              <template v-for="b in banners" :key="b.id">
                <div 
                  v-if="b.image_url"
                  class="w-full shrink-0 min-h-[160px] sm:min-h-[180px] relative bg-slate-900 text-white flex flex-col justify-end p-5 sm:p-6"
                >
                  <img :src="b.image_url" :alt="b.title" class="absolute inset-0 w-full h-full object-cover opacity-40" />
                  <div class="relative z-10">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-500 text-white mb-1.5">
                      Informasi Resmi
                    </span>
                    <h4 class="text-base sm:text-lg font-black tracking-tight">{{ b.title }}</h4>
                    <p class="text-xs text-slate-200 mt-0.5 line-clamp-2 max-w-xl">{{ b.subtitle }}</p>
                  </div>
                </div>
              </template>
            </div>

            <!-- Carousel Controls & Dots -->
            <div class="absolute bottom-3 right-4 flex items-center gap-1.5 z-20">
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

      <!-- 7. FIXED MOBILE BOTTOM NAVIGATION BAR (Exact mobile app feeling) -->
      <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 px-2 py-1.5 shadow-2xl safe-area-pb">
        <div class="grid grid-cols-5 items-center text-center text-[10px] font-bold">
          <!-- Nav 1: Home / Layanan -->
          <button 
            type="button" 
            @click="scrollToTop"
            class="flex flex-col items-center py-1 text-blue-600 dark:text-blue-400 transition"
          >
            <CreditCard class="w-5 h-5 mb-0.5" />
            <span>Layanan</span>
          </button>

          <!-- Nav 2: Persyaratan -->
          <button 
            type="button" 
            @click="showRequirementsModal = true"
            class="flex flex-col items-center py-1 text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition"
          >
            <ClipboardList class="w-5 h-5 mb-0.5" />
            <span>Syarat</span>
          </button>

          <!-- Nav 3: Lacak Status -->
          <button 
            type="button" 
            @click="showTrackingModal = true"
            class="flex flex-col items-center py-1 text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition"
          >
            <Search class="w-5 h-5 mb-0.5" />
            <span>Lacak</span>
          </button>

          <!-- Nav 4: Riwayat -->
          <Link 
            :href="route('queue.history')"
            class="flex flex-col items-center py-1 text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition"
          >
            <FolderClock class="w-5 h-5 mb-0.5" />
            <span>Riwayat</span>
          </Link>

          <!-- Nav 5: Helpdesk WA -->
          <a 
            :href="'https://wa.me/' + cleanPhone(settings.helpdesk_phone)"
            target="_blank"
            class="flex flex-col items-center py-1 text-slate-600 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 transition"
          >
            <MessageCircle class="w-5 h-5 mb-0.5" />
            <span>Bantuan</span>
          </a>
        </div>
      </nav>

    </div>

    <!-- MODAL 1: SERVICE DETAIL & REQUIREMENT CHECKLIST (Opened on service click) -->
    <Teleport to="body">
      <div 
        v-if="selectedService"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-sm transition-opacity"
        @click.self="selectedService = null"
      >
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[85vh] flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-200">
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
                      v-if="doc.template_url"
                      :href="doc.template_url"
                      target="_blank"
                      class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline mt-0.5"
                    >
                      <FileDown class="w-3 h-3" />
                      <span>Unduh Blanko</span>
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
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[85vh] flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-200">
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-center gap-2.5">
              <ClipboardList class="w-5 h-5 text-blue-600" />
              <h3 class="text-base font-black text-slate-900 dark:text-white">Persyaratan Berkas KTP-el</h3>
            </div>
            <button type="button" @click="showRequirementsModal = false" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="p-5 overflow-y-auto space-y-4 text-xs">
            <div v-for="srv in services" :key="srv.id" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 space-y-2">
              <h4 class="font-black text-sm text-slate-900 dark:text-white">{{ srv.name }}</h4>
              <ul class="space-y-1.5 pl-4 list-disc text-slate-600 dark:text-slate-300">
                <li v-for="doc in srv.documents" :key="doc.id">
                  <strong>{{ doc.label }}</strong> 
                  <span v-if="doc.is_required" class="text-rose-500 font-bold"> (Wajib)</span>
                  <span v-else class="text-slate-600 dark:text-slate-400"> (Opsional)</span>
                </li>
              </ul>
              <div class="pt-2">
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
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 animate-in slide-in-from-bottom duration-200">
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
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 animate-in slide-in-from-bottom duration-200">
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
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 animate-in slide-in-from-bottom duration-200">
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

          <form @submit.prevent="submitSearch" class="space-y-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Nomor Registrasi atau NIK
              </label>
              <div class="relative">
                <input
                  v-model="searchForm.query"
                  type="text"
                  placeholder="Contoh: REG-2611-XXXXXX atau 16 digit NIK..."
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
              <span>{{ searchForm.processing ? 'Memeriksa...' : 'Lacak Status Sekarang' }}</span>
              <ArrowRight class="w-4 h-4" />
            </button>
          </form>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
            <span>Lupa nomor registrasi Anda? </span>
            <Link :href="route('queue.history')" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
              Buka Riwayat Permohonan Saya &rarr;
            </Link>
          </div>
        </div>
      </div>
    </Teleport>

  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  CreditCard, RotateCcw, FileText, Camera, Search, FolderClock,
  Calendar, ClipboardList, MessageCircle, Star, AlertCircle, CheckCircle2,
  ArrowRight, ChevronRight, X, FileDown, Info
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
</script>
