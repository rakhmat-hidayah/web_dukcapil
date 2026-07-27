<template>
  <Head title="Informasi Layanan & Pelayanan Mobile CMS - Admin Dukcapil" />

  <AdminLayout>
    <div class="space-y-6 max-w-5xl mx-auto pb-12 text-left">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">
            Pengaturan Informasi Layanan &amp; Pelayanan Mobile
          </h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Kelola jadwal Pelayanan Mobile, jenis dokumen yang dilayani, persyaratan berkas, serta kartu bantuan cepat.
          </p>
        </div>

        <button 
          type="button"
          @click="submit"
          :disabled="form.processing"
          class="px-6 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-2xl shadow-lg shadow-primary-500/25 active:scale-95 transition flex items-center gap-2 self-start sm:self-auto"
        >
          <Save class="w-4 h-4" />
          <span>Simpan Semua Pengaturan</span>
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- SECTION 0: Banner Header Halaman Publik (/layanan/mobil) -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg">
              🎨
            </div>
            <div>
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                Banner Header Halaman Publik Pelayanan Mobile
              </h3>
              <p class="text-xs text-gray-500 dark:text-zinc-400">
                Kelola teks tag, judul utama, dan deskripsi pada banner biru atas halaman publik <code>/layanan/mobile</code>.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tag Badge -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Teks Tag Badge Header
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_hero_tag"
                placeholder="Jemput Bola Adminduk"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Judul Utama -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Judul Utama Banner
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_hero_title"
                placeholder="Jadwal Pelayanan Mobile Keliling"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Deskripsi Banner -->
            <div class="space-y-1.5 md:col-span-2">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Deskripsi Singkat Banner
              </label>
              <textarea 
                v-model="form.mobile_service_hero_description"
                rows="2"
                placeholder="Layanan jemput bola penerbitan dokumen kependudukan langsung ke desa..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- SECTION 1: Pelayanan Mobile Utama & Jadwal -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg">
                🚐
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                  Section 1: Pelayanan Mobile (Jadwal &amp; Status)
                </h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400">
                  Tampilkan jadwal jemput bola pelayanan adminduk keliling di beranda &amp; halaman publik.
                </p>
              </div>
            </div>

            <!-- Toggle Active Switch -->
            <label class="relative inline-flex items-center cursor-pointer select-none">
              <input 
                type="checkbox" 
                v-model="form.mobile_service_is_active" 
                class="sr-only peer" 
              />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-zinc-600 peer-checked:bg-emerald-500"></div>
              <span class="ml-2.5 text-xs font-bold text-gray-700 dark:text-zinc-300">
                {{ form.mobile_service_is_active ? 'Aktif (Ditampilkan)' : 'Nonaktif (Disembunyikan)' }}
              </span>
            </label>
          </div>

          <div v-if="form.mobile_service_is_active" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Kecamatan -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                  Kecamatan Target <span class="text-red-500">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="form.mobile_service_kecamatan"
                  placeholder="Contoh: Kecamatan Woja"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
                <p v-if="form.errors.mobile_service_kecamatan" class="text-[11px] text-red-500 font-semibold">
                  {{ form.errors.mobile_service_kecamatan }}
                </p>
              </div>

              <!-- Desa -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                  Desa / Kelurahan (Opsional)
                </label>
                <input 
                  type="text" 
                  v-model="form.mobile_service_desa"
                  placeholder="Contoh: Desa Monta Baru"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
              </div>

              <!-- Tanggal Pelayanan -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                    Tanggal Pelayanan
                  </label>
                  <div class="flex items-center gap-1">
                    <button 
                      type="button" 
                      @click="setDateToday" 
                      class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold rounded-lg hover:bg-emerald-100 transition"
                    >
                      📅 Hari Ini
                    </button>
                    <button 
                      type="button" 
                      @click="setDateTomorrow" 
                      class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-[10px] font-bold rounded-lg hover:bg-blue-100 transition"
                    >
                      📅 Besok
                    </button>
                    <button 
                      type="button" 
                      @click="setDateUpcoming" 
                      class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-bold rounded-lg hover:bg-amber-100 transition"
                    >
                      📅 Lusa
                    </button>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <input 
                    type="date" 
                    v-model="pickedDate" 
                    @change="onDatePick"
                    class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition shrink-0 cursor-pointer"
                  />
                  <input 
                    type="text" 
                    v-model="form.mobile_service_date"
                    placeholder="Contoh: Senin, 28 Juli 2026"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-bold focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                  />
                </div>
              </div>

              <!-- Jam Pelayanan -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                    Jam Pelayanan
                  </label>
                  <div class="flex items-center gap-1">
                    <button 
                      type="button" 
                      @click="setTimePreset('08:00', '14:00')" 
                      class="px-2 py-0.5 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 text-[10px] font-bold rounded-lg hover:bg-gray-200 transition"
                    >
                      ⏰ 08.00 - 14.00
                    </button>
                    <button 
                      type="button" 
                      @click="setTimePreset('08:00', '12:00')" 
                      class="px-2 py-0.5 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 text-[10px] font-bold rounded-lg hover:bg-gray-200 transition"
                    >
                      ⏰ 08.00 - 12.00
                    </button>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <div class="flex items-center gap-1 shrink-0 bg-gray-50 dark:bg-zinc-800 p-1 rounded-xl border border-gray-200 dark:border-zinc-700">
                    <input 
                      type="time" 
                      v-model="timeStart" 
                      @change="updateTimeFromPickers"
                      class="px-2 py-1.5 rounded-lg border-0 bg-transparent text-xs font-bold focus:ring-0 dark:text-zinc-100 cursor-pointer"
                    />
                    <span class="text-xs text-gray-400 font-bold">-</span>
                    <input 
                      type="time" 
                      v-model="timeEnd" 
                      @change="updateTimeFromPickers"
                      class="px-2 py-1.5 rounded-lg border-0 bg-transparent text-xs font-bold focus:ring-0 dark:text-zinc-100 cursor-pointer"
                    />
                  </div>
                  <input 
                    type="text" 
                    v-model="form.mobile_service_time"
                    placeholder="Contoh: 08.00 - 14.00 WITA"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-bold focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                  />
                </div>
              </div>

              <!-- Status Badge Option -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                  Status Badge Pelayanan <span class="text-red-500">*</span>
                </label>
                <select 
                  v-model="form.mobile_service_status"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-bold focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                >
                  <option value="hari_ini">🟢 Hari Ini (Hijau - Active Today)</option>
                  <option value="besok">🔵 Besok (Biru - Tomorrow)</option>
                  <option value="akan_datang">🟠 Akan Datang (Oranye - Upcoming)</option>
                  <option value="selesai">⚪ Selesai (Abu-abu - Completed)</option>
                </select>
              </div>

              <!-- Button Text -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                  Teks Tombol Widget Beranda
                </label>
                <input 
                  type="text" 
                  v-model="form.mobile_service_button_text"
                  placeholder="Lihat Jadwal Lengkap"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
              </div>

              <!-- Button URL -->
              <div class="space-y-1.5 md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                  Link URL Tombol Widget Beranda
                </label>
                <input 
                  type="text" 
                  v-model="form.mobile_service_button_url"
                  placeholder="Contoh: /layanan/mobile"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
              </div>
            </div>

            <!-- Catatan Pelayanan (Under Schedule Card) -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 flex items-center gap-1.5">
                <span>💡 Teks Catatan Pelayanan (Kotak Biru di Halaman Publik)</span>
              </label>
              <textarea 
                v-model="form.mobile_service_notes"
                rows="2"
                placeholder="Harap membawa berkas persyaratan fisik lengkap (ASLI & Fotokopi)..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              ></textarea>
            </div>
          </div>

          <div v-else class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/30 rounded-2xl text-xs text-amber-800 dark:text-amber-300">
            ℹ️ Seksi <strong>Mobil Pelayanan</strong> sedang disembunyikan dari widget beranda.
          </div>
        </div>

        <!-- NEW SUBSECTION: Pilih Jenis Dokumen yang Dilayani di Mobil Keliling -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                🛠️
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                  Pilih Dokumen / Jenis Layanan yang Dilayani di Lokasi Pelayanan Mobile
                </h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400">
                  Centang dokumen mana saja yang saat ini dilayani oleh Tim Pelayanan Mobile pada jadwal lokasi tersebut.
                </p>
              </div>
            </div>

            <!-- Quick Selection Buttons -->
            <div class="flex items-center gap-2 shrink-0">
              <button 
                type="button" 
                @click="selectAllServices"
                class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-bold rounded-xl hover:bg-indigo-100 transition"
              >
                Pilih Semua
              </button>
              <button 
                type="button" 
                @click="deselectAllServices"
                class="px-3 py-1 bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 border border-gray-200 dark:border-zinc-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition"
              >
                Hapus Semua
              </button>
            </div>
          </div>

          <!-- Service Checkbox Badge List -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div 
              v-for="service in allServices" 
              :key="service.id"
              @click="toggleServiceSelection(service.id)"
              class="p-3.5 rounded-2xl border transition-all cursor-pointer select-none flex items-start gap-3"
              :class="form.mobile_service_selected_services.includes(service.id) 
                ? 'bg-indigo-50/70 dark:bg-indigo-950/30 border-indigo-200 dark:border-indigo-800/80 text-indigo-950 dark:text-indigo-100 shadow-xs' 
                : 'bg-gray-50/50 dark:bg-zinc-800/30 border-gray-200 dark:border-zinc-800/60 opacity-60 text-gray-600 dark:text-zinc-400 hover:opacity-100'"
            >
              <!-- Custom Checkbox Icon -->
              <div 
                class="w-5 h-5 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 border transition"
                :class="form.mobile_service_selected_services.includes(service.id)
                  ? 'bg-indigo-600 border-indigo-600 text-white'
                  : 'border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-transparent'"
              >
                ✓
              </div>

              <!-- Service Info -->
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <span class="text-base">{{ service.icon || '📄' }}</span>
                  <span class="text-xs font-extrabold truncate">{{ service.title }}</span>
                </div>
                <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1 line-clamp-1 leading-normal">
                  {{ service.description }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 2: Persyaratan Umum Berkas -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-lg">
              📋
            </div>
            <div>
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                Section 2: Persyaratan Umum Berkas
              </h3>
              <p class="text-xs text-gray-500 dark:text-zinc-400">
                Daftar persyaratan berkas fisik yang tampil di kolom kanan halaman publik <code>/layanan/mobile</code> (Ketik 1 poin per baris).
              </p>
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
              Poin-Poin Persyaratan Berkas (Pisahkan dengan baris baru / Enter)
            </label>
            <textarea 
              v-model="form.mobile_service_requirements"
              rows="5"
              placeholder="Fotokopi Kartu Keluarga (KK) terbaru.&#10;KTP-el lama atau Surat Keterangan...&#10;Surat Keterangan Kehilangan..."
              class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-mono font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition leading-relaxed"
            ></textarea>
          </div>
        </div>

        <!-- SECTION 3: Card Ajukan Kunjungan Mobil (Khusus Kades / Sekolah) -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
              🏫
            </div>
            <div>
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                Section 3: Card Permohonan Kunjungan (Khusus Kades / Sekolah)
              </h3>
              <p class="text-xs text-gray-500 dark:text-zinc-400">
                Kelola kartu permohonan kunjungan tim jemput bola bagi Kepala Desa / Sekolah.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Badge Teks -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Teks Badge
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_request_badge"
                placeholder="Khusus Kepala Desa / Sekolah"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Judul Card -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Judul Card
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_request_title"
                placeholder="Ajukan Kunjungan Mobil Pelayanan"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Deskripsi Card -->
            <div class="space-y-1.5 md:col-span-2">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Deskripsi Singkat Card
              </label>
              <textarea 
                v-model="form.mobile_service_request_description"
                rows="2"
                placeholder="Pemerintah Desa, Kelurahan, atau Kepala Sekolah di Kabupaten Dompu..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              ></textarea>
            </div>

            <!-- Teks Tombol WA -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Teks Tombol WhatsApp
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_request_button_text"
                placeholder="Hubungi Tim Jemput Bola via WA"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Link URL Tombol WA -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Link WhatsApp / URL Permohonan
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_request_button_url"
                placeholder="https://wa.me/6282340001111"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>
          </div>
        </div>

        <!-- SECTION 4: Card Kontak Informasi Lebih Lanjut -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-lg">
              ℹ️
            </div>
            <div>
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                Section 4: Card Kontak Informasi Lebih Lanjut
              </h3>
              <p class="text-xs text-gray-500 dark:text-zinc-400">
                Kelola kartu info kontak tambahan di bagian bawah halaman publik.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Judul -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Judul Card Info
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_contact_title"
                placeholder="Butuh Informasi Lebih Lanjut?"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Teks Tombol -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Teks Tombol Kontak
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_contact_button_text"
                placeholder="Lihat Kontak Kantor"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Deskripsi -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Deskripsi Singkat
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_contact_description"
                placeholder="Hubungi petugas layanan Dukcapil..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- URL Tombol -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                Link URL Tombol Kontak
              </label>
              <input 
                type="text" 
                v-model="form.mobile_service_contact_button_url"
                placeholder="/contact"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>
          </div>
        </div>

        <!-- SECTION 5: Bantuan Cepat Widget Beranda -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
              🤝
            </div>
            <div>
              <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
                Section 5: Widget Bantuan Cepat (Beranda Utama)
              </h3>
              <p class="text-xs text-gray-500 dark:text-zinc-400">
                Kelola tautan WhatsApp &amp; Google Maps yang tampil pada widget Bantuan Cepat di beranda.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- WhatsApp Pelayanan -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 flex items-center gap-1.5">
                <span>💬 WhatsApp Pelayanan</span>
              </label>
              <input 
                type="text" 
                v-model="form.quick_help_whatsapp"
                placeholder="Contoh: 6282340001111 atau https://wa.me/..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <!-- Google Maps URL -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 flex items-center gap-1.5">
                <span>📍 Link Google Maps Kantor</span>
              </label>
              <input 
                type="text" 
                v-model="form.quick_help_google_maps_url"
                placeholder="Contoh: https://maps.google.com/?q=..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>
          </div>
        </div>

        <!-- SECTION 6: Sambutan Pimpinan & Foto Kepala Dinas -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                👔
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100">
                  Sambutan Pimpinan &amp; Foto Kepala Dinas
                </h3>
                <p class="text-xs text-gray-400 dark:text-zinc-500">
                  Kelola foto pimpinan, nama, jabatan, serta isi teks sambutan yang muncul pada halaman profil instansi.
                </p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <!-- Left: Foto Pimpinan & Preview -->
            <div class="md:col-span-4 space-y-4 text-center">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 text-left">
                Foto Resmi Kepala Dinas
              </label>
              <div class="relative w-40 h-40 mx-auto rounded-2xl overflow-hidden bg-gray-100 dark:bg-zinc-800 border-4 border-white dark:border-zinc-700 shadow-md">
                <img 
                  :src="previewPhotoUrl" 
                  alt="Foto Kepala Dinas" 
                  class="w-full h-full object-cover object-top"
                  @error="$event.target.src = '/images/kepala_dinas.jpg'"
                />
              </div>

              <div class="space-y-1.5 text-left">
                <label class="block text-[11px] font-semibold text-gray-500">
                  Unggah Foto Baru (JPG/PNG/WEBP, Maks 5MB):
                </label>
                <input 
                  type="file" 
                  accept="image/*"
                  @change="handlePhotoUpload"
                  class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-800 dark:file:text-zinc-300 transition"
                />
              </div>
            </div>

            <!-- Right: Text Inputs -->
            <div class="md:col-span-8 space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Kepala Dinas -->
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                    Nama Lengkap &amp; Gelar Pimpinan
                  </label>
                  <input 
                    type="text" 
                    v-model="form.leader_name"
                    placeholder="Drs. Abd. Najib"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                  />
                </div>

                <!-- Jabatan Pimpinan -->
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                    Jabatan Pimpinan
                  </label>
                  <input 
                    type="text" 
                    v-model="form.leader_title"
                    placeholder="Kepala Dinas Dukcapil Kabupaten Dompu"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                  />
                </div>
              </div>

              <!-- Judul Kutipan Sambutan -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                  Judul Kutipan Sambutan Pimpinan
                </label>
                <input 
                  type="text" 
                  v-model="form.leader_speech_title"
                  placeholder='"Pelayanan Kependudukan Yang Transparan & Membahagiakan Masyarakat"'
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
              </div>

              <!-- Isi Paragraf Sambutan -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                  Isi Teks Sambutan (Pisahkan Paragraf dengan Enter)
                </label>
                <textarea 
                  v-model="form.leader_speech_content"
                  rows="5"
                  placeholder="Tuliskan isi sambutan di sini..."
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition leading-relaxed"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 7: Visi & Misi Instansi -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg">
                🎯
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100">
                  Visi &amp; Misi Instansi
                </h3>
                <p class="text-xs text-gray-400 dark:text-zinc-500">
                  Kelola judul visi, teks pernyataan visi, judul misi, serta poin-poin misi pelayanan instansi.
                </p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Visi -->
            <div class="space-y-4 bg-gray-50/50 dark:bg-zinc-800/30 p-5 rounded-2xl border border-gray-100 dark:border-zinc-800">
              <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Bagian Visi</h4>
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Judul Visi</label>
                <input 
                  type="text" 
                  v-model="form.profile_visi_title"
                  placeholder="Visi Instansi"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
              </div>
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Teks Pernyataan Visi</label>
                <textarea 
                  v-model="form.profile_visi_text"
                  rows="4"
                  placeholder='"Terwujudnya Pelayanan Kependudukan..."'
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition leading-relaxed"
                ></textarea>
              </div>
            </div>

            <!-- Misi -->
            <div class="space-y-4 bg-gray-50/50 dark:bg-zinc-800/30 p-5 rounded-2xl border border-gray-100 dark:border-zinc-800">
              <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Bagian Misi</h4>
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Judul Misi</label>
                <input 
                  type="text" 
                  v-model="form.profile_misi_title"
                  placeholder="Misi Pelayanan"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
              </div>
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Poin-poin Misi (1 Misi per Baris / Enter)</label>
                <textarea 
                  v-model="form.profile_misi_items"
                  rows="4"
                  placeholder="Tuliskan tiap poin misi pada baris baru..."
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition leading-relaxed"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 8: Tugas Pokok & Fungsi -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
                📌
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100">
                  Tugas Pokok &amp; Fungsi (Tupoksi)
                </h3>
                <p class="text-xs text-gray-400 dark:text-zinc-500">
                  Kelola judul seksi dan rincian poin Tugas Pokok &amp; Fungsi. Tambah atau hapus poin sesuai kebutuhan.
                </p>
              </div>
            </div>

            <button 
              type="button" 
              @click="addDutyItem"
              class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0"
            >
              <span>+ Tambah Poin Tupoksi</span>
            </button>
          </div>

          <div class="space-y-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Judul Seksi Utama</label>
              <input 
                type="text" 
                v-model="form.profile_duties_title"
                placeholder="Tugas Pokok & Fungsi"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
              <div 
                v-for="(item, idx) in form.profile_duties_items" 
                :key="idx" 
                class="p-4 rounded-2xl bg-slate-50 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 space-y-3 relative group"
              >
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-amber-600 dark:text-amber-400 truncate max-w-[220px]">
                    {{ item.title ? item.title : ('Poin ' + String(idx + 1).padStart(2, '0')) }}
                  </span>
                  <button 
                    type="button" 
                    @click="removeDutyItem(idx)"
                    class="text-[11px] font-bold text-red-500 hover:text-red-700 hover:underline px-2 py-0.5 rounded bg-red-50 dark:bg-red-950/40 transition shrink-0"
                  >
                    🗑 Hapus
                  </button>
                </div>
                <input 
                  type="text" 
                  v-model="item.title"
                  placeholder="Judul Poin (Contoh: Tugas Pokok / Fungsi)..."
                  class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-bold focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
                <textarea 
                  v-model="item.desc"
                  rows="3"
                  placeholder="Deskripsi rincian tugas..."
                  class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition leading-relaxed"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 9: Maklumat Pelayanan Publik -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-lg">
                📜
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100">
                  Maklumat Pelayanan Publik
                </h3>
                <p class="text-xs text-gray-400 dark:text-zinc-500">
                  Kelola teks komitmen maklumat pelayanan publik dan sanksi aturan perundangan.
                </p>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Teks Badge Tag</label>
              <input 
                type="text" 
                v-model="form.profile_maklumat_tag"
                placeholder="Maklumat Pelayanan Publik"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Pernyataan Maklumat Utama</label>
              <textarea 
                v-model="form.profile_maklumat_title"
                rows="3"
                placeholder='"Dengan Ini, Kami Menyatakan Sanggup Menyenggarakan Pelayanan..."'
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition leading-relaxed font-bold"
              ></textarea>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Teks Keterangan Sanksi &amp; Konsekuensi</label>
              <input 
                type="text" 
                v-model="form.profile_maklumat_subtitle"
                placeholder="Apabila tidak memenuhi janji ini, kami siap menerima sanksi..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
              />
            </div>
          </div>
        </div>

        <!-- SECTION 10: Tentang Dinas -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-lg">
                ℹ️
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-100">
                  Tentang Dinas (Gambaran Umum)
                </h3>
                <p class="text-xs text-gray-400 dark:text-zinc-500">
                  Kelola judul seksi, sub-judul, serta deskripsi gambaran umum dan peran strategis instansi.
                </p>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Judul Seksi</label>
                <input 
                  type="text" 
                  v-model="form.profile_about_title"
                  placeholder="Tentang Dinas"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Sub-Judul Seksi</label>
                <input 
                  type="text" 
                  v-model="form.profile_about_subtitle"
                  placeholder="Gambaran Umum & Peran Strategis Instansi"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition"
                />
              </div>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">Isi Teks Deskripsi (Pisahkan Paragraf dengan Enter)</label>
              <textarea 
                v-model="form.profile_about_content"
                rows="5"
                placeholder="Dinas Kependudukan dan Pencatatan Sipil..."
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-medium focus:ring-2 focus:ring-primary-500 dark:text-zinc-100 transition leading-relaxed"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Form Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-4">
          <button 
            type="submit"
            :disabled="form.processing"
            class="px-8 py-3.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-2xl shadow-lg shadow-primary-500/25 active:scale-95 transition flex items-center gap-2"
          >
            <Save class="w-4 h-4" />
            <span>Simpan Semua Pengaturan</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save } from '@lucide/vue';

const props = defineProps({
  settings: Object,
  allServices: Array,
});

const pickedDate = ref('');
const timeStart = ref('08:00');
const timeEnd = ref('14:00');

// Initial Selected Service IDs parser
const initialSelectedServices = computed(() => {
  try {
    const raw = props.settings?.mobile_service_selected_services;
    if (!raw) return (props.allServices || []).map(s => s.id);
    const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
    return Array.isArray(parsed) ? parsed.map(Number) : (props.allServices || []).map(s => s.id);
  } catch (e) {
    return (props.allServices || []).map(s => s.id);
  }
});

const formatIndonesianDate = (dateObj) => {
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  
  const dayName = days[dateObj.getDay()];
  const dayDate = dateObj.getDate();
  const monthName = months[dateObj.getMonth()];
  const year = dateObj.getFullYear();
  
  return `${dayName}, ${dayDate} ${monthName} ${year}`;
};

const onDatePick = () => {
  if (!pickedDate.value) return;
  const parts = pickedDate.value.split('-');
  if (parts.length === 3) {
    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
    form.mobile_service_date = formatIndonesianDate(d);
  }
};

const updateTimeFromPickers = () => {
  if (timeStart.value && timeEnd.value) {
    const s = timeStart.value.replace(':', '.');
    const e = timeEnd.value.replace(':', '.');
    form.mobile_service_time = `${s} - ${e} WITA`;
  }
};

const setDateToday = () => {
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const dd = String(today.getDate()).padStart(2, '0');
  pickedDate.value = `${yyyy}-${mm}-${dd}`;
  form.mobile_service_date = formatIndonesianDate(today);
  form.mobile_service_status = 'hari_ini';
};

const setDateTomorrow = () => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const yyyy = tomorrow.getFullYear();
  const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
  const dd = String(tomorrow.getDate()).padStart(2, '0');
  pickedDate.value = `${yyyy}-${mm}-${dd}`;
  form.mobile_service_date = formatIndonesianDate(tomorrow);
  form.mobile_service_status = 'besok';
};

const setDateUpcoming = () => {
  const upcoming = new Date();
  upcoming.setDate(upcoming.getDate() + 2);
  const yyyy = upcoming.getFullYear();
  const mm = String(upcoming.getMonth() + 1).padStart(2, '0');
  const dd = String(upcoming.getDate()).padStart(2, '0');
  pickedDate.value = `${yyyy}-${mm}-${dd}`;
  form.mobile_service_date = formatIndonesianDate(upcoming);
  form.mobile_service_status = 'akan_datang';
};

const setTimePreset = (startStr, endStr) => {
  timeStart.value = startStr;
  timeEnd.value = endStr;
  const s = startStr.replace(':', '.');
  const e = endStr.replace(':', '.');
  form.mobile_service_time = `${s} - ${e} WITA`;
};

const toggleServiceSelection = (serviceId) => {
  const index = form.mobile_service_selected_services.indexOf(serviceId);
  if (index > -1) {
    form.mobile_service_selected_services.splice(index, 1);
  } else {
    form.mobile_service_selected_services.push(serviceId);
  }
};

const selectAllServices = () => {
  form.mobile_service_selected_services = (props.allServices || []).map(s => s.id);
};

const deselectAllServices = () => {
  form.mobile_service_selected_services = [];
};

const photoPreview = ref(null);

const handlePhotoUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.leader_photo_file = file;
    photoPreview.value = URL.createObjectURL(file);
  }
};

const previewPhotoUrl = computed(() => {
  if (photoPreview.value) return photoPreview.value;
  const photo = form.leader_photo;
  if (!photo) return '/images/kepala_dinas.jpg';
  if (photo.startsWith('http://') || photo.startsWith('https://') || photo.startsWith('/images/')) return photo;
  return `/storage/${photo}`;
});

const initialDutiesItems = computed(() => {
  try {
    const raw = props.settings?.profile_duties_items;
    if (!raw) {
      return [
        {
          title: 'Tugas Pokok :',
          desc: "Dinas Kependudukan dan Pencatatan Sipil mempunyai tugas membantu Bupati melaksanakan urusan pemerintahan di bidang Administrasi Kependudukan dan Pencatatan Sipil, meliputi:\n1. Fasilitasi pelayanan administrasi kependudukan.\n2. Pengelolaan informasi administrasi kependudukan (PIAK).\n3. Pemanfaatan data dan inovasi pelayanan kependudukan."
        },
        {
          title: 'Fungsi :',
          desc: "Dalam menyelenggarakan tugas pokok, Dinas Kependudukan dan Pencatatan Sipil menyelenggarakan fungsi:\n1. Penyelenggaraan perumusan kebijakan teknis di bidang administrasi kependudukan dan pencatatan sipil.\n2. Penyelenggaraan pelayanan administrasi kependudukan dan pencatatan sipil.\n3. Penyelenggaraan evaluasi, pelaporan, dan pengendalian sistem informasi.\n4. Penyelenggaraan fungsi dinas lainnya sesuai tugas pokok dan fungsinya."
        },
        {
          title: 'Kewenangan Disdukcapil Kabupaten Dompu',
          desc: "Pemerintah Kabupaten berkewajiban dan bertanggung jawab menyelenggarakan urusan Administrasi Kependudukan dengan kewenangan meliputi:\n1. Koordinasi penyelenggaraan Administrasi Kependudukan.\n2. Pemberian bimbingan, supervisi, dan konsultasi pelaksanaan Pendaftaran Penduduk dan Pencatatan Sipil.\n3. Pembinaan dan sosialisasi penyelenggaraan Administrasi Kependudukan.\n4. Penyajian Data Kependudukan berskala Kabupaten dari SIAK Terpusat.\n5. Koordinasi pengawasan atas penyelenggaraan Administrasi Kependudukan."
        }
      ];
    }
    const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
    return (Array.isArray(parsed) && parsed.length > 0) ? parsed : [
      {
        title: 'Tugas Pokok :',
        desc: "Dinas Kependudukan dan Pencatatan Sipil mempunyai tugas membantu Bupati melaksanakan urusan pemerintahan di bidang Administrasi Kependudukan dan Pencatatan Sipil, meliputi:\n1. Fasilitasi pelayanan administrasi kependudukan.\n2. Pengelolaan informasi administrasi kependudukan (PIAK).\n3. Pemanfaatan data dan inovasi pelayanan kependudukan."
      },
      {
        title: 'Fungsi :',
        desc: "Dalam menyelenggarakan tugas pokok, Dinas Kependudukan dan Pencatatan Sipil menyelenggarakan fungsi:\n1. Penyelenggaraan perumusan kebijakan teknis di bidang administrasi kependudukan dan pencatatan sipil.\n2. Penyelenggaraan pelayanan administrasi kependudukan dan pencatatan sipil.\n3. Penyelenggaraan evaluasi, pelaporan, dan pengendalian sistem informasi.\n4. Penyelenggaraan fungsi dinas lainnya sesuai tugas pokok dan fungsinya."
      },
      {
        title: 'Kewenangan Disdukcapil Kabupaten Dompu',
        desc: "Pemerintah Kabupaten berkewajiban dan bertanggung jawab menyelenggarakan urusan Administrasi Kependudukan dengan kewenangan meliputi:\n1. Koordinasi penyelenggaraan Administrasi Kependudukan.\n2. Pemberian bimbingan, supervisi, dan konsultasi pelaksanaan Pendaftaran Penduduk dan Pencatatan Sipil.\n3. Pembinaan dan sosialisasi penyelenggaraan Administrasi Kependudukan.\n4. Penyajian Data Kependudukan berskala Kabupaten dari SIAK Terpusat.\n5. Koordinasi pengawasan atas penyelenggaraan Administrasi Kependudukan."
      }
    ];
  } catch (e) {
    return [];
  }
});

const addDutyItem = () => {
  form.profile_duties_items.push({ title: '', desc: '' });
};

const removeDutyItem = (index) => {
  form.profile_duties_items.splice(index, 1);
};

const form = useForm({
  // Section 0: Banner Header
  mobile_service_hero_tag: props.settings.mobile_service_hero_tag || 'Jemput Bola Adminduk',
  mobile_service_hero_title: props.settings.mobile_service_hero_title || 'Jadwal Pelayanan Mobile Keliling',
  mobile_service_hero_description: props.settings.mobile_service_hero_description || 'Layanan jemput bola penerbitan dokumen kependudukan langsung ke desa, kelurahan, dan sekolah di seluruh wilayah Kabupaten Dompu secara gratis dan cepat.',

  // Section 1: Pelayanan Mobile Utama
  mobile_service_is_active: props.settings.mobile_service_is_active === '1',
  mobile_service_kecamatan: props.settings.mobile_service_kecamatan || 'Kecamatan Woja',
  mobile_service_desa: props.settings.mobile_service_desa || 'Desa Monta Baru',
  mobile_service_date: props.settings.mobile_service_date || 'Senin, 28 Juli 2026',
  mobile_service_time: props.settings.mobile_service_time || '08.00 - 14.00 WITA',
  mobile_service_status: props.settings.mobile_service_status || 'hari_ini',
  mobile_service_button_text: props.settings.mobile_service_button_text || 'Lihat Jadwal Lengkap',
  mobile_service_button_url: props.settings.mobile_service_button_url || '/layanan/mobile',
  mobile_service_notes: props.settings.mobile_service_notes || '',
  mobile_service_selected_services: initialSelectedServices.value,

  // Section 2: Persyaratan Umum Berkas
  mobile_service_requirements: props.settings.mobile_service_requirements || '',

  // Section 3: Ajukan Kunjungan Mobil (Desa/Sekolah)
  mobile_service_request_badge: props.settings.mobile_service_request_badge || 'Khusus Kepala Desa / Sekolah',
  mobile_service_request_title: props.settings.mobile_service_request_title || 'Ajukan Kunjungan Mobil Pelayanan',
  mobile_service_request_description: props.settings.mobile_service_request_description || 'Pemerintah Desa, Kelurahan, atau Kepala Sekolah di Kabupaten Dompu dapat mengajukan surat permohonan kunjungan tim jemput bola Dukcapil ke wilayah masing-masing.',
  mobile_service_request_button_text: props.settings.mobile_service_request_button_text || 'Hubungi Tim Jemput Bola via WA',
  mobile_service_request_button_url: props.settings.mobile_service_request_button_url || 'https://wa.me/6282340001111',

  // Section 4: Informasi Kontak Lebih Lanjut
  mobile_service_contact_title: props.settings.mobile_service_contact_title || 'Butuh Informasi Lebih Lanjut?',
  mobile_service_contact_description: props.settings.mobile_service_contact_description || 'Hubungi petugas layanan Dukcapil Kabupaten Dompu melalui saluran kontak resmi.',
  mobile_service_contact_button_text: props.settings.mobile_service_contact_button_text || 'Lihat Kontak Kantor',
  mobile_service_contact_button_url: props.settings.mobile_service_contact_button_url || '/contact',

  // Section 5: Widget Bantuan Cepat
  quick_help_whatsapp: props.settings.quick_help_whatsapp || 'https://wa.me/6282340001111',
  quick_help_google_maps_url: props.settings.quick_help_google_maps_url || 'https://maps.google.com/?q=Dukcapil+Dompu',

  // Section 6: Sambutan Pimpinan & Foto Kepala Dinas
  leader_name: props.settings.leader_name || 'Drs. Abd. Najib',
  leader_title: props.settings.leader_title || 'Kepala Dinas Dukcapil Kabupaten Dompu',
  leader_speech_title: props.settings.leader_speech_title || '"Pelayanan Kependudukan Yang Transparan & Membahagiakan Masyarakat"',
  leader_speech_content: props.settings.leader_speech_content || "Assalamu’alaikum Warahmatullahi Wabarakatuh,\n\nSelamat datang di portal resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. Kehadiran portal ini merupakan bagian dari komitmen kami dalam mewujudkan keterbukaan informasi publik dan kemudahan akses layanan administrasi kependudukan bagi seluruh lapisan masyarakat.\n\nSeluruh jajaran Dinas Dukcapil Kabupaten Dompu siap melayani Anda dengan semangat integritas, profesionalisme, dan tanpa pungutan biaya apapun (Rp 0,-).",
  leader_photo: props.settings.leader_photo || '/images/kepala_dinas.jpg',
  leader_photo_file: null,

  // Section 7: Visi Misi Instansi
  profile_visi_title: props.settings.profile_visi_title || 'Visi Instansi',
  profile_visi_text: props.settings.profile_visi_text || '"Terwujudnya Pelayanan Kependudukan dan Pencatatan Sipil yang Prima, Akurat, Terintegrasi, dan Membahagiakan Masyarakat di Kabupaten Dompu."',
  profile_misi_title: props.settings.profile_misi_title || 'Misi Pelayanan',
  profile_misi_items: props.settings.profile_misi_items || "Meningkatkan kualitas dan kecepatan pelayanan administrasi kependudukan dan pencatatan sipil.\nMewujudkan validitas dan akurasi database kependudukan Kabupaten Dompu yang terintegrasi SIAK Terpusat.\nMendorong pemanfaatan data kependudukan secara luas bagi perencanaan pembangunan daerah.",

  // Section 8: Tugas Pokok & Fungsi
  profile_duties_title: props.settings.profile_duties_title || 'Tugas Pokok & Fungsi',
  profile_duties_items: initialDutiesItems.value,

  // Section 9: Maklumat Pelayanan Publik
  profile_maklumat_tag: props.settings.profile_maklumat_tag || 'Maklumat Pelayanan Publik',
  profile_maklumat_title: props.settings.profile_maklumat_title || '"Dengan Ini, Kami Menyatakan Sanggup Menyenggarakan Pelayanan Sesuai Standar Pelayanan Yang Telah Ditetapkan"',
  profile_maklumat_subtitle: props.settings.profile_maklumat_subtitle || 'Apabila tidak memenuhi janji ini, kami siap menerima sanksi sesuai dengan peraturan perundang-undangan yang berlaku.',

  // Section 10: Tentang Dinas
  profile_about_title: props.settings.profile_about_title || 'Tentang Dinas',
  profile_about_subtitle: props.settings.profile_about_subtitle || 'Gambaran Umum & Peran Strategis Instansi',
  profile_about_content: props.settings.profile_about_content || "Dinas Kependudukan dan Pencatatan Sipil (Dukcapil) Kabupaten Dompu merupakan instansi pemerintah daerah yang bertugas menyelenggarakan urusan pemerintahan di bidang administrasi kependudukan dan pencatatan sipil di wilayah Kabupaten Dompu, Nusa Tenggara Barat.\n\nMelalui integrasi Sistem Informasi Administrasi Kependudukan (SIAK) Terpusat dan Identitas Kependudukan Digital (IKD), Dukcapil Kabupaten Dompu terus berinovasi untuk menghadirkan pelayanan publik yang melayani tanpa diskriminasi, cepat, akurat, dan Bebas Pungutan Liar (Rp 0,-).",
});

const submit = () => {
  form.post(route('admin.informasi-layanan.update'), {
    preserveScroll: true,
    forceFormData: true,
  });
};
</script>
