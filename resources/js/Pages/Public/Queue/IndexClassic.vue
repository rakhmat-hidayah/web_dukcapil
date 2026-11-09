<template>
  <Head title="Layanan KTP-el & Pra-Verifikasi Digital - Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="py-8 sm:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
      <!-- Maintenance Alert Banner (If Active) -->
      <div v-if="maintenance?.is_active" class="p-5 sm:p-6 rounded-3xl bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-800 text-amber-950 dark:text-amber-200 backdrop-blur-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-lg shadow-amber-500/5">
        <div class="flex items-start gap-3.5">
          <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>
          <div>
            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-600 text-white mb-1">
              Informasi Pemeliharaan
            </div>
            <h3 class="font-black text-sm sm:text-base text-amber-950 dark:text-amber-200">
              {{ maintenance.title || 'Layanan Pendaftaran Online Sedang Ditutup Sementara' }}
            </h3>
            <p class="text-xs text-amber-900/80 dark:text-amber-300/90 mt-1 max-w-2xl leading-relaxed">
              {{ maintenance.message }}
            </p>
            <p v-if="maintenance.estimated_end" class="text-xs text-amber-800 dark:text-amber-400 font-bold mt-1">
              Estimasi Selesai: {{ maintenance.estimated_end }}
            </p>
          </div>
        </div>
        <Link
          :href="route('queue.apply.create')"
          class="shrink-0 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition shadow-md shadow-amber-600/20"
        >
          Lihat Informasi Lengkap &rarr;
        </Link>
      </div>

      <!-- Hero Banner -->
      <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-900 text-white p-6 sm:p-10 lg:p-12 shadow-2xl border border-blue-800/40">
        <!-- Glow highlights -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-semibold uppercase tracking-wider mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            Layanan KTP-el & Pra-Verifikasi Digital
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">
            Pelayanan KTP-el Cepat, Tepat & Bebas Antre Panjang
          </h1>

          <p class="mt-4 text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl">
            Daftar permohonan layanan KTP-el dari rumah, unggah berkas persyaratan melalui kamera handphone, dan dapatkan kepastian verifikasi dokumen sebelum datang ke loket Dinas Dukcapil Kabupaten Dompu.
          </p>

          <!-- Quick Action Buttons -->
          <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <Link
              :href="route('queue.apply.create')"
              :class="[
                'px-6 py-3.5 rounded-2xl font-bold text-sm shadow-xl transition flex items-center justify-center gap-2 hover:scale-[1.02]',
                maintenance?.is_active
                  ? 'bg-amber-600 hover:bg-amber-500 text-white shadow-amber-600/30'
                  : 'bg-blue-600 hover:bg-blue-500 text-white shadow-blue-600/30'
              ]"
            >
              <span>{{ maintenance?.is_active ? 'Pendaftaran Ditutup Sementara (Info)' : 'Daftar Pelayanan Sekarang' }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </Link>

            <Link
              :href="route('queue.history')"
              class="px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm backdrop-blur border border-white/20 transition flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
              <span>Riwayat Permohonan Saya</span>
            </Link>

            <a
              href="#lacak-antrian"
              class="px-5 py-3.5 rounded-2xl bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white font-semibold text-sm border border-white/10 transition flex items-center justify-center gap-1.5"
            >
              <span>Lacak Nomor</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Live Today Quota & Status Ribbon -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Status Operasional -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
          <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center shrink-0', todayStats.is_open ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-600']">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Status Hari Ini</p>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-0.5">
              {{ todayStats.is_open ? 'Loket Buka' : (todayStats.is_holiday ? 'Hari Libur' : 'Loket Tutup') }}
            </p>
            <p class="text-[11px] text-slate-400">{{ todayStats.open_time }} - {{ todayStats.close_time }} WITA</p>
          </div>
        </div>

        <!-- Sisa Kuota Hari Ini -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sisa Kuota Hari Ini</p>
            <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-0.5">
              {{ todayStats.total_remaining }}
              <span class="text-xs font-normal text-slate-400">/ {{ todayStats.total_quota }} Slot</span>
            </p>
          </div>
        </div>

        <!-- Terisi Hari Ini -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Terverifikasi Hari Ini</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
              {{ todayStats.total_booked }}
              <span class="text-xs font-normal text-slate-400">Antrian</span>
            </p>
          </div>
        </div>

        <!-- Biaya Pelayanan -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Biaya Pelayanan</p>
            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">
              GRATIS / Rp 0
            </p>
            <p class="text-[11px] text-slate-400">Tanpa Pungutan Liar</p>
          </div>
        </div>
      </div>

      <!-- 4 Service Types Grid -->
      <div>
        <div class="mb-8">
          <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
            Pilih Jenis Pelayanan KTP-el
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Silakan pilih layanan yang sesuai dengan kebutuhan Anda untuk melihat persyaratan dan mendaftar antrian.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="service in services"
            :key="service.id"
            class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-7 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
          >
            <div>
              <div class="flex items-start justify-between gap-4 mb-4">
                <div
                  class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold shadow-md"
                  :style="{ backgroundColor: service.color || '#2563eb' }"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                  Kuota {{ service.quota_per_day }}/Hari
                </span>
              </div>

              <h3 class="text-xl font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                {{ service.name }}
              </h3>

              <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                {{ service.short_description }}
              </p>

              <!-- Document Requirements Checklist Preview -->
              <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60 space-y-2.5">
                <div class="flex items-center justify-between">
                  <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Daftar Persyaratan Berkas ({{ service.documents?.length || 0 }}):
                  </p>
                  <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium">Foto / Scan dari HP</span>
                </div>
                <div
                  v-for="(doc, dIdx) in service.documents"
                  :key="doc.id"
                  class="flex items-start justify-between gap-2 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/40 transition"
                >
                  <div class="flex items-start gap-2 flex-1">
                    <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                      {{ doc.sort_order || (dIdx + 1) }}
                    </span>
                    <div class="flex-1">
                      <span class="font-medium leading-tight">
                        {{ doc.label }}
                      </span>
                      <div class="flex flex-wrap items-center gap-1.5 mt-1">
                        <span
                          v-if="doc.is_required"
                          class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400"
                        >
                          Wajib
                        </span>
                        <span
                          v-else
                          class="px-1.5 py-0.5 rounded text-[9px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400"
                        >
                          Opsional
                        </span>
                        <span
                          v-if="doc.applicable_to === 'representative_only'"
                          class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400"
                        >
                          Khusus Diwakilkan
                        </span>
                        <a
                          v-if="doc.template_url"
                          :href="doc.template_url"
                          target="_blank"
                          class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 bg-blue-50 dark:bg-blue-900/50 hover:bg-blue-100 dark:hover:bg-blue-900/80 px-2 py-0.5 rounded transition"
                          @click.stop
                        >
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                          <span>Unduh Blanko</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
              <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                Waktu Proses: {{ service.processing_days_label }}
              </span>
              <Link
                :href="route('queue.apply.create', { layanan: service.slug })"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold transition shadow-sm"
              >
                <span>Daftar Layanan</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- How It Works Flow (4 Steps) -->
      <div class="bg-slate-100/70 dark:bg-slate-800/40 rounded-3xl p-6 sm:p-10 border border-slate-200 dark:border-slate-700/60">
        <div class="text-center max-w-xl mx-auto mb-10">
          <h3 class="text-2xl font-black text-slate-900 dark:text-white">Alur Pendaftaran & Verifikasi</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">4 tahapan mudah mengurus KTP-el tanpa menunggu lama di kantor.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative">
            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center mb-3">1</span>
            <h4 class="font-bold text-slate-900 dark:text-white text-sm">Isi Data Pemohon</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
              Masukkan NIK, nama lengkap, dan nomor WhatsApp aktif untuk menerima tiket antrian.
            </p>
          </div>

          <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative">
            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center mb-3">2</span>
            <h4 class="font-bold text-slate-900 dark:text-white text-sm">Foto Berkas dari HP</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
              Ambil foto KK, KTP lama / Surat Kehilangan dan foto selfie langsung melalui kamera handphone.
            </p>
          </div>

          <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative">
            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center mb-3">3</span>
            <h4 class="font-bold text-slate-900 dark:text-white text-sm">Pra-Verifikasi Petugas</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
              Petugas Dukcapil memeriksa keabsahan berkas secara online. Jika ada kekurangan, Anda bisa perbaiki online.
            </p>
          </div>

          <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative">
            <span class="w-7 h-7 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center mb-3">4</span>
            <h4 class="font-bold text-slate-900 dark:text-white text-sm">Dapat Tiket & Hadir</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
              Setelah disetujui, nomor antrean terbit. Cukup datang sesuai jadwal dan bawa berkas fisik asli.
            </p>
          </div>
        </div>
      </div>

      <!-- Tracking Search Box Anchor Section -->
      <div id="lacak-antrian" class="max-w-2xl mx-auto text-center scroll-mt-24">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200 dark:border-slate-700">
          <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>

          <h3 class="text-xl font-bold text-slate-900 dark:text-white">Pantau Status Pengajuan & Temukan Nomor</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-6">
            Masukkan Nomor Registrasi (contoh: REG-2610-XXXXXX) untuk membuka status pengajuan, atau masukkan 16 digit NIK untuk membuka seluruh Riwayat Permohonan Anda.
          </p>

          <!-- If not logged in -->
          <div v-if="!$page.props.auth?.applicant" class="space-y-4">
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-left flex items-start gap-3">
              <span class="text-xl">🔐</span>
              <div>
                <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200">Login Pemohon Diperlukan</h4>
                <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">Untuk keamanan data kependudukan dan perlindungan privasi, pelacakan status permohonan kini mewajibkan Anda untuk masuk ke Akun Pemohon terlebih dahulu.</p>
              </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
              <Link :href="route('applicant.login')" class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-md">
                Masuk ke Akun Pemohon
              </Link>
              <Link :href="route('applicant.register')" class="px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 font-bold text-xs transition">
                Daftar Akun Baru
              </Link>
            </div>
          </div>

          <!-- If already logged in -->
          <div v-else class="space-y-4">
            <form @submit.prevent="submitSearch" class="flex flex-col sm:flex-row gap-2.5">
              <input
                v-model="searchForm.query"
                type="text"
                placeholder="Masukkan Nomor Registrasi..."
                class="flex-1 px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <button
                type="submit"
                :disabled="searchForm.processing"
                class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-md disabled:opacity-50"
              >
                {{ searchForm.processing ? 'Mencari...' : 'Lacak Status' }}
              </button>
            </form>

            <p v-if="searchForm.errors.query" class="text-xs text-rose-500 text-left mt-2">
              {{ searchForm.errors.query }}
            </p>

            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400">
              <Link :href="route('applicant.dashboard')" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                Buka Seluruh Riwayat Permohonan Saya di Dashboard &rarr;
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

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
});

const searchForm = useForm({
  query: '',
});

function submitSearch() {
  searchForm.post(route('queue.check'));
}
</script>
