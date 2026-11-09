<template>
  <Head title="Layanan Online KTP-el - Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-24 md:pb-16 text-slate-800 dark:text-slate-100">
      
      <!-- Top App Container -->
      <div class="max-w-4xl mx-auto px-3 sm:px-6 pt-4 sm:pt-6 space-y-5">

        <!-- Maintenance Alert Banner (If Active) -->
        <div 
          v-if="maintenance?.is_active" 
          class="p-4 sm:p-5 rounded-3xl bg-amber-500/10 dark:bg-amber-500/15 border-2 border-amber-400 dark:border-amber-700 text-amber-950 dark:text-amber-200 backdrop-blur-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-lg shadow-amber-500/5"
        >
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md">
              <AlertCircle class="w-5 h-5" />
            </div>
            <div>
              <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-600 text-white mb-1">
                Informasi Pemeliharaan
              </div>
              <h3 class="font-bold text-sm sm:text-base text-amber-950 dark:text-amber-100">
                {{ maintenance.title || 'Layanan Pendaftaran Online Sedang Ditutup Sementara' }}
              </h3>
              <p class="text-xs text-amber-900/80 dark:text-amber-300/90 mt-0.5 leading-relaxed">
                {{ maintenance.message }}
              </p>
              <p v-if="maintenance.estimated_end" class="text-xs text-amber-800 dark:text-amber-400 font-bold mt-1">
                Estimasi Dibuka: {{ maintenance.estimated_end }}
              </p>
            </div>
          </div>
          <Link
            :href="route('queue.apply.create')"
            class="shrink-0 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition shadow-md"
          >
            Lihat Informasi Lengkap &rarr;
          </Link>
        </div>

        <!-- 2. SECTION LAYANAN & SURVEY IKM BUTTON (Matching photo) -->
        <div class="space-y-4">
          <div class="flex items-center justify-between px-1">
            <div>
              <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">
                Layanan
              </h2>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">
                Pilih menu di bawah ini untuk memulai pengajuan atau memantau status
              </p>
            </div>

            <!-- Green Pill "★ Isi Survey IKM" Button (Exact like Grobogan screenshot) -->
            <Link
              :href="route('public.survey.index')"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-md shadow-emerald-600/25 transition active:scale-95"
            >
              <Star class="w-3.5 h-3.5 fill-current text-amber-300" />
              <span>Isi Survey IKM</span>
            </Link>
          </div>

          <!-- 3. CIRCULAR NEUMORPHIC ICON GRID (3 Columns on Mobile, 4-5 on Desktop) -->
          <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3.5 sm:gap-4 p-4 sm:p-6 bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800">
            
            <!-- Icon 1: KTP Hilang -->
            <button
              type="button"
              @click="openServiceModalBySlug('cetak-ktp-hilang')"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-sky-400 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 ring-4 ring-sky-100 dark:ring-sky-950/60 group-hover:scale-105 transition-transform duration-200">
                <!-- Neumorphic 3D styled inner icon -->
                <CreditCard class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                KTP-el Hilang
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Surat Hilang</span>
            </button>

            <!-- Icon 2: KTP Rusak -->
            <button
              type="button"
              @click="openServiceModalBySlug('cetak-ktp-rusak')"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/25 ring-4 ring-blue-100 dark:ring-blue-950/60 group-hover:scale-105 transition-transform duration-200">
                <RotateCcw class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                KTP-el Rusak
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Fisik Patah/Pudar</span>
            </button>

            <!-- Icon 3: Perubahan Data -->
            <button
              type="button"
              @click="openServiceModalBySlug('perubahan-elemen-data')"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-cyan-400 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-teal-500/25 ring-4 ring-teal-100 dark:ring-teal-950/60 group-hover:scale-105 transition-transform duration-200">
                <FileText class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Ubah Elemen Data
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Status / Alamat</span>
            </button>

            <!-- Icon 4: Perekaman Baru (Pemula) -->
            <button
              type="button"
              @click="openServiceModalBySlug('perekaman-ktp-el-baru')"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-emerald-400 to-green-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25 ring-4 ring-emerald-100 dark:ring-emerald-950/60 group-hover:scale-105 transition-transform duration-200">
                <Camera class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Perekaman Baru
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Pemula 16-17 Thn</span>
            </button>

            <!-- Icon 5: Lacak Permohonan (Search Anchor) -->
            <button
              type="button"
              @click="scrollToTracking"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/25 ring-4 ring-amber-100 dark:ring-amber-950/60 group-hover:scale-105 transition-transform duration-200">
                <Search class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Lacak Status
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">No. Reg / NIK</span>
            </button>

            <!-- Icon 6: Riwayat Permohonan Saya -->
            <Link
              :href="route('queue.history')"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-purple-400 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-purple-500/25 ring-4 ring-purple-100 dark:ring-purple-950/60 group-hover:scale-105 transition-transform duration-200">
                <FolderClock class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Riwayat Saya
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Daftar Tiket</span>
            </Link>

            <!-- Icon 7: Jadwal & Sisa Kuota Loket -->
            <button
              type="button"
              @click="showQuotaModal = true"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-rose-400 to-red-600 flex items-center justify-center text-white shadow-lg shadow-rose-500/25 ring-4 ring-rose-100 dark:ring-rose-950/60 group-hover:scale-105 transition-transform duration-200">
                <Calendar class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Jadwal & Kuota
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Sisa {{ todayStats.total_remaining }} Slot</span>
            </button>

            <!-- Icon 8: Persyaratan Berkas & Blanko -->
            <button
              type="button"
              @click="showRequirementsModal = true"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-blue-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-indigo-400 to-blue-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 ring-4 ring-indigo-100 dark:ring-indigo-950/60 group-hover:scale-105 transition-transform duration-200">
                <ClipboardList class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400">
                Syarat & Blanko
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Formulir F-1.02</span>
            </button>

            <!-- Icon 9: Helpdesk WhatsApp -->
            <a
              :href="'https://wa.me/' + cleanPhone(settings.helpdesk_phone) + '?text=' + encodeURIComponent('Halo Admin Pelayanan KTP-el Disdukcapil Dompu, saya ingin konsultasi mengenai pengajuan KTP-el.')"
              target="_blank"
              class="group flex flex-col items-center text-center p-2 rounded-2xl hover:bg-emerald-50/60 dark:hover:bg-slate-800 transition active:scale-95 focus:outline-none"
            >
              <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-emerald-500 to-green-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25 ring-4 ring-emerald-100 dark:ring-emerald-950/60 group-hover:scale-105 transition-transform duration-200">
                <MessageCircle class="w-7 h-7 sm:w-8 sm:h-8" />
              </div>
              <span class="mt-2.5 text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400">
                Helpdesk WA
              </span>
              <span class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">Konsultasi Loket</span>
            </a>

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

        <!-- 5. QUICK TRACKING SEARCH BOX (Lacak Status Antrian) -->
        <div id="lacak-status-antrian" class="p-5 sm:p-7 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4 scroll-mt-20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
              <Search class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-black text-slate-900 dark:text-white">
                Pantau Status Pengajuan & Tiket Antrian
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Masukkan Nomor Registrasi (contoh: REG-2611-XXXXXX) atau 16 digit NIK Anda
              </p>
            </div>
          </div>

          <form @submit.prevent="submitSearch" class="flex flex-col sm:flex-row gap-2.5">
            <div class="relative flex-1">
              <input
                v-model="searchForm.query"
                type="text"
                placeholder="Masukkan Nomor Registrasi atau NIK..."
                required
                class="w-full pl-4 pr-10 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="searchForm.query" @click="searchForm.query = ''" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 cursor-pointer">
                <X class="w-4 h-4" />
              </span>
            </div>
            <button
              type="submit"
              :disabled="searchForm.processing"
              class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-bold text-xs transition shadow-md shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-1.5"
            >
              <span>{{ searchForm.processing ? 'Memeriksa...' : 'Lacak Status' }}</span>
              <ArrowRight class="w-4 h-4" />
            </button>
          </form>

          <p v-if="searchForm.errors.query" class="text-xs text-rose-500 font-semibold">
            {{ searchForm.errors.query }}
          </p>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Lupa nomor registrasi Anda?</span>
            <Link :href="route('queue.history')" class="font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
              <span>Buka Riwayat Permohonan Saya</span>
              <ChevronRight class="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>

        <!-- 6. ALUR 4 LANGKAH MUDAH (Workflow Preview) -->
        <div class="p-5 sm:p-7 rounded-3xl bg-slate-100/80 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 space-y-4">
          <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">4</span>
            Alur Pelayanan KTP-el Tanpa Antre Panjang
          </h3>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200/80 dark:border-slate-700/80 space-y-1.5">
              <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-black text-[10px] flex items-center justify-center">1</span>
              <h5 class="font-bold text-slate-900 dark:text-white">Pilih Layanan</h5>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">Pilih jenis KTP-el dan lengkapi data pemohon.</p>
            </div>
            <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200/80 dark:border-slate-700/80 space-y-1.5">
              <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-black text-[10px] flex items-center justify-center">2</span>
              <h5 class="font-bold text-slate-900 dark:text-white">Foto Berkas HP</h5>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">Unggah foto KK, surat kehilangan & foto selfie.</p>
            </div>
            <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200/80 dark:border-slate-700/80 space-y-1.5">
              <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-black text-[10px] flex items-center justify-center">3</span>
              <h5 class="font-bold text-slate-900 dark:text-white">Verifikasi Petugas</h5>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">Petugas memeriksa keabsahan berkas secara online.</p>
            </div>
            <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200/80 dark:border-slate-700/80 space-y-1.5">
              <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 font-black text-[10px] flex items-center justify-center">4</span>
              <h5 class="font-bold text-slate-900 dark:text-white">Dapat Tiket & Hadir</h5>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">Nomor antrean terbit. Hadir bawa berkas fisik asli.</p>
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
            @click="scrollToTracking"
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
  const el = document.getElementById('lacak-status-antrian');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
    const input = el.querySelector('input');
    if (input) input.focus();
  }
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cleanPhone(phone) {
  if (!phone) return '6282124824114';
  return phone.replace(/[^0-9]/g, '');
}
</script>
