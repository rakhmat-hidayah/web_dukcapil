<template>
  <Head title="Pendaftaran Pelayanan KTP-el - Disdukcapil Kabupaten Dompu" />

  <PublicLayout>
    <div class="py-8 sm:py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Wizard Progress (Step 1 of 3 wizard blocks) -->
      <ProgressWizard :current-step="1" />

      <!-- Form Container -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-10 border border-slate-200 dark:border-slate-700 shadow-xl space-y-8">
        <div>
          <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 mb-2 inline-block">
            Tahap 1, 2 & 3: Layanan & Identitas
          </span>
          <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
            Formulir Permohonan Layanan KTP-el
          </h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Silakan lengkapi data pemohon dan tentukan apakah permohonan diurus sendiri atau diwakilkan.
          </p>
        </div>

        <form @submit.prevent="submitStep1" class="space-y-8">
          <!-- TAHAP 1: PILIH JENIS PELAYANAN -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                1. Pilih Jenis Pelayanan KTP-el <span class="text-rose-500">*</span>
              </label>
              <span class="text-[11px] text-slate-400">Pilih salah satu layanan</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label
                v-for="service in services"
                :key="service.id"
                :class="[
                  'cursor-pointer rounded-2xl p-4 border transition-all flex flex-col justify-between gap-2 relative',
                  form.service_type_id == service.id
                    ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-950/40 ring-2 ring-blue-500/20 shadow-sm'
                    : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750'
                ]"
              >
                <div class="flex items-start gap-3">
                  <input
                    type="radio"
                    v-model="form.service_type_id"
                    :value="service.id"
                    @change="handleServiceChanged(service)"
                    class="mt-1 text-blue-600 focus:ring-blue-500"
                  />
                  <div class="flex-1">
                    <span class="text-xs font-bold text-slate-900 dark:text-white block leading-snug">
                      {{ service.name }}
                    </span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                      {{ service.short_description }}
                    </p>
                  </div>
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 mt-1">
                  <span
                    v-if="service.is_biometric_first_recording"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800"
                  >
                    Wajib Hadir Sendiri (Perekaman)
                  </span>
                  <span
                    v-else
                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300"
                  >
                    Dapat Diwakilkan Sesuai SOP
                  </span>
                </div>
              </label>
            </div>
            <p v-if="form.errors.service_type_id" class="text-xs text-rose-500 font-medium">
              {{ form.errors.service_type_id }}
            </p>
          </div>

          <!-- TAHAP 2: DATA PEMOHON (PEMILIK DOKUMEN / YBS) -->
          <div class="pt-6 border-t border-slate-100 dark:border-slate-700/60 space-y-4">
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                2. Data Pemilik Dokumen (Yang Bersangkutan / YBS) <span class="text-rose-500">*</span>
              </h3>
              <p class="text-[11px] text-slate-400">
                Masukkan data kependudukan pemilik KTP-el sesuai dengan Kartu Keluarga (KK).
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- NIK -->
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  NIK Pemilik Dokumen (16 Digit) <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="form.applicant_nik"
                  type="text"
                  maxlength="16"
                  placeholder="5205xxxxxxxxxxxx"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 font-mono tracking-wider"
                  required
                />
                <p v-if="form.errors.applicant_nik" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_nik }}
                </p>
              </div>

              <!-- Nama Lengkap -->
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  Nama Lengkap Sesuai KK <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="form.applicant_name"
                  type="text"
                  placeholder="Nama lengkap pemilik dokumen..."
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 uppercase"
                  required
                />
                <p v-if="form.errors.applicant_name" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_name }}
                </p>
              </div>

              <!-- Tempat Lahir -->
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  Tempat Lahir <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="form.applicant_birth_place"
                  type="text"
                  placeholder="Contoh: Dompu"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
                <p v-if="form.errors.applicant_birth_place" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_birth_place }}
                </p>
              </div>

              <!-- Tanggal Lahir -->
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  Tanggal Lahir <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="form.applicant_birth_date"
                  type="date"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
                <p v-if="form.errors.applicant_birth_date" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_birth_date }}
                </p>
              </div>

              <!-- Nomor WhatsApp / HP -->
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  Nomor WhatsApp / HP Pemohon <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="form.applicant_phone"
                  type="tel"
                  placeholder="Contoh: 0823xxxxxxxx"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                  required
                />
                <p v-if="form.errors.applicant_phone" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_phone }}
                </p>
              </div>

              <!-- Email (Opsional) -->
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  Alamat Email <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                </label>
                <input
                  v-model="form.applicant_email"
                  type="email"
                  placeholder="pemohon@email.com"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <!-- Kecamatan -->
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                    Kecamatan <span class="text-rose-500">*</span>
                  </label>
                  <span v-if="selectedDistrictMode && selectedDistrictMode !== '__OTHER__'" class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">
                    {{ availableVillages.length }} Desa/Kelurahan
                  </span>
                </div>
                <select
                  v-model="selectedDistrictMode"
                  @change="handleDistrictChange"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                  required
                >
                  <option value="" disabled>-- Pilih Kecamatan di Kab. Dompu --</option>
                  <option
                    v-for="dist in districts"
                    :key="dist.id"
                    :value="dist.name"
                  >
                    Kecamatan {{ dist.name }}
                  </option>
                  <option value="__OTHER__">&bull; Lainnya (Luar Kab. Dompu)</option>
                </select>

                <!-- Input Manual jika Luar Dompu -->
                <div v-if="selectedDistrictMode === '__OTHER__'" class="mt-2">
                  <input
                    v-model="customDistrict"
                    @input="handleCustomDistrictInput"
                    type="text"
                    placeholder="Tuliskan nama kecamatan Anda..."
                    class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 uppercase font-medium"
                    required
                  />
                </div>
                <p v-if="form.errors.applicant_district" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_district }}
                </p>
              </div>

              <!-- Desa / Kelurahan -->
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  Kelurahan / Desa <span class="text-rose-500">*</span>
                </label>

                <!-- Dropdown Desa jika Kecamatan Dompu dipilih -->
                <div v-if="selectedDistrictMode && selectedDistrictMode !== '__OTHER__'">
                  <select
                    v-model="selectedVillageMode"
                    @change="handleVillageChange"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                    required
                  >
                    <option value="" disabled>-- Pilih Kelurahan / Desa --</option>
                    <option
                      v-for="vil in availableVillages"
                      :key="vil.id"
                      :value="vil.name"
                    >
                      {{ vil.type === 'kelurahan' ? 'Kel. ' : 'Desa ' }}{{ vil.name }}
                    </option>
                    <option value="__OTHER__">&bull; Lainnya (Tulis Manual)</option>
                  </select>

                  <div v-if="selectedVillageMode === '__OTHER__'" class="mt-2">
                    <input
                      v-model="customVillage"
                      @input="handleCustomVillageInput"
                      type="text"
                      placeholder="Tuliskan nama desa / kelurahan..."
                      class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 uppercase font-medium"
                      required
                    />
                  </div>
                </div>

                <!-- Input Manual jika Luar Dompu -->
                <div v-else-if="selectedDistrictMode === '__OTHER__'">
                  <input
                    v-model="customVillage"
                    @input="handleCustomVillageInput"
                    type="text"
                    placeholder="Tuliskan nama kelurahan / desa..."
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 uppercase font-medium"
                    required
                  />
                </div>

                <!-- Belum Pilih Kecamatan -->
                <div v-else>
                  <select
                    disabled
                    class="w-full px-4 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-sm text-slate-400 dark:text-slate-500 cursor-not-allowed"
                  >
                    <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                  </select>
                </div>

                <p v-if="form.errors.applicant_village" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_village }}
                </p>
              </div>

              <!-- Alamat Lengkap -->
              <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  Alamat Tempat Tinggal Lengkap <span class="text-rose-500">*</span>
                </label>
                <textarea
                  v-model="form.applicant_address"
                  rows="2"
                  placeholder="Nama jalan, RT/RW, Dusun, atau patokan tempat tinggal..."
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                  required
                ></textarea>
                <p v-if="form.errors.applicant_address" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_address }}
                </p>
              </div>
            </div>
          </div>

          <!-- TAHAP 3: DATA KEHADIRAN & PERWAKILAN -->
          <div class="pt-6 border-t border-slate-100 dark:border-slate-700/60 space-y-4">
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                3. Kehadiran ke Kantor Disdukcapil <span class="text-rose-500">*</span>
              </h3>
              <p class="text-sm font-semibold text-slate-900 dark:text-white">
                Siapa yang akan datang langsung ke kantor untuk mengurus permohonan ini?
              </p>
            </div>

            <!-- Lock Notice if Biometric Recording -->
            <div
              v-if="selectedServiceObj && selectedServiceObj.is_biometric_first_recording"
              class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-3"
            >
              <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <div>
                <strong class="font-bold block mb-0.5">Perekaman Biometrik Pertama Wajib Hadir Sendiri</strong>
                <p class="leading-relaxed">
                  Layanan KTP-el Pemula tidak dapat diwakilkan karena memerlukan perekaman data fisik biometrik (sidik jari, iris mata, dan foto) pemilik dokumen secara langsung.
                </p>
              </div>
            </div>

            <!-- Attendance Radio Selection -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <!-- Option 1: Datang Sendiri -->
              <label
                :class="[
                  'cursor-pointer rounded-2xl p-4 border transition-all flex flex-col justify-between gap-2',
                  form.attendance_type === 'self'
                    ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-950/40 ring-2 ring-blue-500/20'
                    : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750'
                ]"
              >
                <div class="flex items-start gap-2.5">
                  <input
                    type="radio"
                    v-model="form.attendance_type"
                    value="self"
                    class="mt-0.5 text-blue-600 focus:ring-blue-500"
                  />
                  <div>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block">
                      Saya Sendiri
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block mt-0.5">
                      Pemilik dokumen hadir langsung ke loket.
                    </span>
                  </div>
                </div>
              </label>

              <!-- Option 2: Diwakilkan Keluarga -->
              <label
                :class="[
                  'rounded-2xl p-4 border transition-all flex flex-col justify-between gap-2',
                  selectedServiceObj && selectedServiceObj.is_biometric_first_recording
                    ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800'
                    : 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-750',
                  form.attendance_type === 'family_in_kk' || form.attendance_type === 'family_out_kk'
                    ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-950/40 ring-2 ring-blue-500/20'
                    : 'border-slate-200 dark:border-slate-700'
                ]"
              >
                <div class="flex items-start gap-2.5">
                  <input
                    type="radio"
                    v-model="form.attendance_type"
                    value="family_in_kk"
                    :disabled="selectedServiceObj && selectedServiceObj.is_biometric_first_recording"
                    class="mt-0.5 text-blue-600 focus:ring-blue-500"
                  />
                  <div>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block">
                      Anggota Keluarga
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block mt-0.5">
                      Diwakilkan anggota keluarga dalam 1 KK.
                    </span>
                  </div>
                </div>
              </label>

              <!-- Option 3: Diwakilkan Orang Lain / Kuasa -->
              <label
                :class="[
                  'rounded-2xl p-4 border transition-all flex flex-col justify-between gap-2',
                  (selectedServiceObj && (selectedServiceObj.is_biometric_first_recording || !selectedServiceObj.allow_other))
                    ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800'
                    : 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-750',
                  form.attendance_type === 'other'
                    ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-950/40 ring-2 ring-blue-500/20'
                    : 'border-slate-200 dark:border-slate-700'
                ]"
              >
                <div class="flex items-start gap-2.5">
                  <input
                    type="radio"
                    v-model="form.attendance_type"
                    value="other"
                    :disabled="selectedServiceObj && (selectedServiceObj.is_biometric_first_recording || !selectedServiceObj.allow_other)"
                    class="mt-0.5 text-blue-600 focus:ring-blue-500"
                  />
                  <div>
                    <span class="text-xs font-bold text-slate-900 dark:text-white block">
                      Pihak Lain / Kuasa
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block mt-0.5">
                      Wajib surat kuasa bermeterai & KTP kuasa.
                    </span>
                  </div>
                </div>
              </label>
            </div>
            <p v-if="form.errors.attendance_type" class="text-xs text-rose-500 font-medium">
              {{ form.errors.attendance_type }}
            </p>

            <!-- REPRESENTATIVE FORM DETAILS (IF REPRESENTED) -->
            <div
              v-if="form.attendance_type !== 'self'"
              class="p-5 sm:p-6 rounded-2xl bg-blue-50/50 dark:bg-slate-900/80 border border-blue-200 dark:border-blue-900/60 space-y-4"
            >
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <h4 class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300">
                  Data Identitas Orang yang Mewakili
                </h4>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- NIK Perwakilan -->
                <div>
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    NIK Perwakilan (16 Digit) <span class="text-rose-500">*</span>
                  </label>
                  <input
                    v-model="form.representative_nik"
                    type="text"
                    maxlength="16"
                    placeholder="5205xxxxxxxxxxxx"
                    class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 font-mono tracking-wider"
                    required
                  />
                  <p v-if="form.errors.representative_nik" class="text-xs text-rose-500 font-medium mt-1">
                    {{ form.errors.representative_nik }}
                  </p>
                </div>

                <!-- Nama Perwakilan -->
                <div>
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Nama Lengkap Perwakilan <span class="text-rose-500">*</span>
                  </label>
                  <input
                    v-model="form.representative_name"
                    type="text"
                    placeholder="Nama orang yang mewakili..."
                    class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 uppercase"
                    required
                  />
                  <p v-if="form.errors.representative_name" class="text-xs text-rose-500 font-medium mt-1">
                    {{ form.errors.representative_name }}
                  </p>
                </div>

                <!-- Kontak Perwakilan -->
                <div>
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Nomor WhatsApp / HP Perwakilan <span class="text-rose-500">*</span>
                  </label>
                  <input
                    v-model="form.representative_phone"
                    type="tel"
                    placeholder="0823xxxxxxxx"
                    class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                    required
                  />
                  <p v-if="form.errors.representative_phone" class="text-xs text-rose-500 font-medium mt-1">
                    {{ form.errors.representative_phone }}
                  </p>
                </div>

                <!-- Hubungan Perwakilan -->
                <div>
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Hubungan dengan Pemilik Dokumen <span class="text-rose-500">*</span>
                  </label>
                  <select
                    v-model="form.representative_relationship"
                    class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  >
                    <option value="" disabled>-- Pilih Hubungan --</option>
                    <option value="Suami / Istri">Suami / Istri</option>
                    <option value="Orang Tua (Ayah / Ibu)">Orang Tua (Ayah / Ibu)</option>
                    <option value="Anak Kandung">Anak Kandung</option>
                    <option value="Kakak / Adik Kandung">Kakak / Adik Kandung</option>
                    <option value="Famili Lain dalam KK">Famili Lain dalam KK</option>
                    <option value="Penerima Kuasa Khusus">Penerima Kuasa Khusus</option>
                  </select>
                  <p v-if="form.errors.representative_relationship" class="text-xs text-rose-500 font-medium mt-1">
                    {{ form.errors.representative_relationship }}
                  </p>
                </div>

                <!-- Alasan Berhalangan Hadir -->
                <div class="sm:col-span-2">
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Alasan Pemilik Dokumen Berhalangan Hadir <span class="text-rose-500">*</span>
                  </label>
                  <textarea
                    v-model="form.absence_reason"
                    rows="2"
                    placeholder="Jelaskan alasan mengapa pemilik dokumen tidak dapat hadir sendiri (contoh: sakit, disabilitas, lansia, sedang di luar kota)..."
                    class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  ></textarea>
                  <p v-if="form.errors.absence_reason" class="text-xs text-rose-500 font-medium mt-1">
                    {{ form.errors.absence_reason }}
                  </p>
                </div>
              </div>

              <!-- Notice on Documents Needed for Representative -->
              <p class="text-[11px] text-blue-700 dark:text-blue-300 italic">
                * Pada tahap berikutnya, Anda wajib mengunggah foto fisik KTP-el Asli Perwakilan dan Kartu Keluarga / Surat Kuasa.
              </p>
            </div>
          </div>

          <!-- Privacy & Disclaimer Notice -->
          <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-400 flex items-start gap-2.5">
            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <p class="leading-relaxed">
              <strong>Jaminan Privasi & Legalitas:</strong> Pengurusan oleh perwakilan wajib berdasar pada ketentuan peraturan perundang-undangan kependudukan. Pemeriksaan fisik identitas dan berkas asli akan dilakukan oleh petugas loket Disdukcapil sebelum pelayanan diberikan.
            </p>
          </div>

          <!-- Navigation Footer -->
          <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700/60">
            <Link
              :href="route('queue.index')"
              class="px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-50 dark:hover:bg-slate-750 transition"
            >
              &larr; Kembali ke Beranda Antrian
            </Link>

            <button
              type="submit"
              :disabled="form.processing"
              class="px-7 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2 disabled:opacity-50 cursor-pointer"
            >
              <span>{{ form.processing ? 'Menyimpan...' : 'Lanjut: Unggah Dokumen' }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </form>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ProgressWizard from '@/Components/Queue/ProgressWizard.vue';

const props = defineProps({
  services: {
    type: Array,
    default: () => [],
  },
  selectedService: {
    type: Object,
    default: null,
  },
  districts: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  service_type_id: props.selectedService ? props.selectedService.id : (props.services[0] ? props.services[0].id : ''),
  applicant_name: '',
  applicant_nik: '',
  applicant_birth_place: '',
  applicant_birth_date: '',
  applicant_phone: '',
  applicant_email: '',
  applicant_address: '',
  applicant_village: '',
  applicant_district: '',
  attendance_type: 'self',
  representative_nik: '',
  representative_name: '',
  representative_phone: '',
  representative_relationship: '',
  representative_kk_status: '',
  absence_reason: '',
});

const selectedDistrictMode = ref('');
const selectedVillageMode = ref('');
const customDistrict = ref('');
const customVillage = ref('');

const availableVillages = computed(() => {
  if (!selectedDistrictMode.value || selectedDistrictMode.value === '__OTHER__') {
    return [];
  }
  const found = props.districts.find(d => d.name === selectedDistrictMode.value);
  return found ? (found.villages || []) : [];
});

function handleDistrictChange() {
  if (selectedDistrictMode.value === '__OTHER__') {
    form.applicant_district = customDistrict.value ? customDistrict.value.trim().toUpperCase() : '';
    form.applicant_village = customVillage.value ? customVillage.value.trim().toUpperCase() : '';
    selectedVillageMode.value = '';
  } else {
    form.applicant_district = selectedDistrictMode.value;
    form.applicant_village = '';
    selectedVillageMode.value = '';
    customVillage.value = '';
  }
}

function handleCustomDistrictInput() {
  form.applicant_district = customDistrict.value.trim().toUpperCase();
}

function handleVillageChange() {
  if (selectedVillageMode.value === '__OTHER__') {
    form.applicant_village = customVillage.value ? customVillage.value.trim().toUpperCase() : '';
  } else {
    form.applicant_village = selectedVillageMode.value;
    customVillage.value = '';
  }
}

function handleCustomVillageInput() {
  form.applicant_village = customVillage.value.trim().toUpperCase();
}

onMounted(() => {
  if (form.applicant_district) {
    const matchKec = props.districts.find(
      d => d.name.toUpperCase() === form.applicant_district.trim().toUpperCase()
    );
    if (matchKec) {
      selectedDistrictMode.value = matchKec.name;
      form.applicant_district = matchKec.name;
      if (form.applicant_village) {
        const matchVil = (matchKec.villages || []).find(
          v => v.name.toUpperCase() === form.applicant_village.trim().toUpperCase()
        );
        if (matchVil) {
          selectedVillageMode.value = matchVil.name;
          form.applicant_village = matchVil.name;
        } else {
          selectedVillageMode.value = '__OTHER__';
          customVillage.value = form.applicant_village;
        }
      }
    } else {
      selectedDistrictMode.value = '__OTHER__';
      customDistrict.value = form.applicant_district;
      customVillage.value = form.applicant_village;
    }
  }
});

const selectedServiceObj = computed(() => {
  return props.services.find(s => s.id == form.service_type_id) || null;
});

function handleServiceChanged(service) {
  if (service.is_biometric_first_recording) {
    form.attendance_type = 'self';
    form.representative_nik = '';
    form.representative_name = '';
    form.representative_phone = '';
    form.representative_relationship = '';
    form.absence_reason = '';
  }
}

function submitStep1() {
  form.post(route('queue.apply.store'));
}
</script>
