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

        <form @submit.prevent="submitStep1" novalidate class="space-y-8">
          <!-- TAHAP 1: PILIH JENIS PELAYANAN -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                1. Pilih Jenis Pelayanan KTP-el <span class="text-rose-500">*</span>
              </label>
              <span class="text-[11px] text-slate-400">Pilih salah satu layanan</span>
            </div>

            <div id="field_service_type_id" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

            <!-- Persyaratan & Formulir Layanan Terpilih -->
            <div
              v-if="selectedServiceObj && selectedServiceObj.documents && selectedServiceObj.documents.length"
              class="bg-blue-50/50 dark:bg-blue-950/30 rounded-2xl p-4 sm:p-5 border border-blue-200/80 dark:border-blue-900/50 space-y-3"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </div>
                  <div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">
                      Berkas & Formulir Layanan Terpilih ({{ selectedServiceObj.documents.length }} Persyaratan):
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                      Disiapkan dan diunggah pada Langkah 2 berikutnya (bisa langsung difoto melalui kamera HP).
                    </p>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                <div
                  v-for="(doc, dIdx) in selectedServiceObj.documents"
                  :key="doc.id"
                  class="bg-white dark:bg-slate-900/80 rounded-xl p-3 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between"
                >
                  <div class="flex items-start gap-2">
                    <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                      {{ doc.sort_order || (dIdx + 1) }}
                    </span>
                    <div class="flex-1 min-w-0">
                      <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-snug">
                        {{ doc.label }}
                      </p>
                      <p v-if="doc.description" class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                        {{ doc.description }}
                      </p>
                    </div>
                  </div>

                  <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span
                        v-if="doc.is_required"
                        class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400"
                      >
                        Wajib
                      </span>
                      <span
                        v-else
                        class="px-2 py-0.5 rounded-full text-[9px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400"
                      >
                        Opsional
                      </span>
                      <span
                        v-if="doc.applicable_to === 'representative_only'"
                        class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400"
                      >
                        Khusus Diwakilkan
                      </span>
                    </div>

                    <a
                      v-if="doc.template_url"
                      :href="doc.template_url"
                      target="_blank"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold shadow-sm transition shrink-0"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                      <span>Unduh Blanko</span>
                    </a>
                  </div>
                </div>
              </div>
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
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                    NIK Pemilik Dokumen (16 Digit) <span class="text-rose-500">*</span>
                  </label>
                  <span class="text-[10px] font-mono" :class="form.applicant_nik.length === 16 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400'">
                    {{ form.applicant_nik.length }}/16 Digit
                  </span>
                </div>
                <input
                  id="field_applicant_nik"
                  :value="form.applicant_nik"
                  @input="onNikInput"
                  @keypress="filterNikKeypress"
                  type="text"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  maxlength="16"
                  autocomplete="off"
                  placeholder="5205xxxxxxxxxxxx"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 font-mono tracking-wider"
                  :class="{'border-rose-500 ring-1 ring-rose-500': form.errors.applicant_nik}"
                  required
                />
                <p v-if="form.errors.applicant_nik" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_nik }}
                </p>
                <p v-else-if="form.applicant_nik.length > 0 && form.applicant_nik.length < 16" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium">
                  Kurang {{ 16 - form.applicant_nik.length }} digit lagi. NIK wajib 16 digit angka.
                </p>
              </div>

              <!-- Nama Lengkap -->
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                  Nama Lengkap Sesuai KK <span class="text-rose-500">*</span>
                </label>
                <input
                  id="field_applicant_name"
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
                  id="field_applicant_birth_place"
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
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                    Tanggal Lahir <span class="text-rose-500">*</span>
                  </label>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="isBiometric ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300'">
                    Min. {{ minRequiredAge }} Tahun ({{ isBiometric ? 'Perekaman' : 'Pencetakan' }})
                  </span>
                </div>
                <input
                  id="field_applicant_birth_date"
                  v-model="form.applicant_birth_date"
                  type="date"
                  :max="maxBirthDate"
                  :min="minBirthDate"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{'border-rose-500 ring-1 ring-rose-500': ageErrorMessage || form.errors.applicant_birth_date}"
                  required
                />

                <!-- Usia info badge -->
                <div v-if="applicantAge !== null" class="mt-1.5 flex items-center justify-between text-xs">
                  <span class="text-slate-500 text-[11px]">
                    Usia Pemohon: <strong :class="ageErrorMessage ? 'text-rose-600 font-bold' : 'text-emerald-600 dark:text-emerald-400 font-bold'">{{ applicantAge }} Tahun</strong>
                  </span>
                  <span v-if="!ageErrorMessage" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                    ✓ Memenuhi Syarat Usia
                  </span>
                </div>

                <!-- Usia error warning -->
                <div v-if="ageErrorMessage" class="mt-2 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs flex items-start gap-2">
                  <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                  <span>{{ ageErrorMessage }}</span>
                </div>

                <p v-if="form.errors.applicant_birth_date" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_birth_date }}
                </p>
              </div>

              <!-- Nomor WhatsApp / HP -->
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                    Nomor WhatsApp / HP Pemohon <span class="text-rose-500">*</span>
                  </label>
                  <span class="text-[10px] font-mono" :class="rawPhoneDigits(form.applicant_phone).length >= 10 && rawPhoneDigits(form.applicant_phone).length <= 13 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400'">
                    {{ rawPhoneDigits(form.applicant_phone).length }}/13 Digit (Min. 10)
                  </span>
                </div>
                <input
                  id="field_applicant_phone"
                  :value="form.applicant_phone"
                  @input="onPhoneInput"
                  @keypress="filterPhoneKeypress"
                  type="tel"
                  inputmode="numeric"
                  maxlength="16"
                  autocomplete="tel"
                  placeholder="Contoh: 0853-xxxx-xxxx"
                  class="w-full px-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 font-mono tracking-wider"
                  :class="{'border-rose-500 ring-1 ring-rose-500': form.errors.applicant_phone}"
                  required
                />
                <p v-if="form.errors.applicant_phone" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_phone }}
                </p>
                <p v-else-if="rawPhoneDigits(form.applicant_phone).length > 0 && rawPhoneDigits(form.applicant_phone).length < 10" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium">
                  Kurang {{ 10 - rawPhoneDigits(form.applicant_phone).length }} digit lagi. Nomor HP harus antara 10 sampai 13 digit angka.
                </p>
              </div>

              <!-- Email (Opsional dengan Default @gmail.com) -->
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                    Alamat Email <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                  </label>
                  <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 px-2 py-0.5 rounded-full border border-blue-200/80 dark:border-blue-900/50">
                    <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Default: @gmail.com
                  </span>
                </div>

                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <input
                    id="field_applicant_email"
                    v-model="form.applicant_email"
                    @blur="normalizeApplicantEmail"
                    type="email"
                    autocomplete="email"
                    placeholder="nama.pemohon (otomatis @gmail.com)"
                    class="w-full pl-10 pr-10 py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 transition font-medium"
                    :class="{'border-rose-500 ring-1 ring-rose-500': form.errors.applicant_email}"
                  />
                  <!-- Tombol Bersihkan / Kosongkan Email jika terisi -->
                  <button
                    v-if="form.applicant_email"
                    type="button"
                    @click="clearApplicantEmail"
                    title="Kosongkan email"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-rose-500 transition cursor-pointer"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>

                <!-- Live Preview jika pemohon hanya mengetik username tanpa domain @ -->
                <div v-if="emailUsernameOnly" class="mt-1.5 flex items-center gap-1.5 text-[11px] text-blue-600 dark:text-blue-400 font-medium bg-blue-50/60 dark:bg-blue-950/40 px-2.5 py-1 rounded-xl border border-blue-200/50 dark:border-blue-900/40">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                  <span>Otomatis disimpan: <strong class="font-mono text-blue-700 dark:text-blue-300">{{ emailUsernameOnly }}@gmail.com</strong></span>
                </div>

                <!-- Quick Domain Selector Pills -->
                <div class="mt-2 flex items-center flex-wrap gap-1.5">
                  <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mr-0.5">Pilihan domain:</span>
                  <button
                    type="button"
                    @click="applyEmailDomain('@gmail.com')"
                    :class="[
                      'px-2.5 py-1 rounded-xl text-[10px] font-bold transition border cursor-pointer inline-flex items-center gap-1',
                      currentEmailDomain === '@gmail.com'
                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm ring-2 ring-blue-500/20'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-750'
                    ]"
                  >
                    <span>@gmail.com</span>
                    <span v-if="currentEmailDomain === '@gmail.com'" class="text-[9px] font-normal opacity-90">(aktif)</span>
                  </button>
                  <button
                    type="button"
                    @click="applyEmailDomain('@yahoo.com')"
                    :class="[
                      'px-2.5 py-1 rounded-xl text-[10px] font-bold transition border cursor-pointer inline-flex items-center gap-1',
                      currentEmailDomain === '@yahoo.com'
                        ? 'bg-purple-600 text-white border-purple-600 shadow-sm ring-2 ring-purple-500/20'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-750'
                    ]"
                  >
                    <span>@yahoo.com</span>
                    <span v-if="currentEmailDomain === '@yahoo.com'" class="text-[9px] font-normal opacity-90">(aktif)</span>
                  </button>
                  <button
                    type="button"
                    @click="applyEmailDomain('@outlook.com')"
                    :class="[
                      'px-2.5 py-1 rounded-xl text-[10px] font-bold transition border cursor-pointer inline-flex items-center gap-1',
                      currentEmailDomain === '@outlook.com'
                        ? 'bg-sky-600 text-white border-sky-600 shadow-sm ring-2 ring-sky-500/20'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-750'
                    ]"
                  >
                    <span>@outlook.com</span>
                    <span v-if="currentEmailDomain === '@outlook.com'" class="text-[9px] font-normal opacity-90">(aktif)</span>
                  </button>
                  <button
                    type="button"
                    @click="applyEmailDomain('@icloud.com')"
                    :class="[
                      'px-2.5 py-1 rounded-xl text-[10px] font-bold transition border cursor-pointer inline-flex items-center gap-1',
                      currentEmailDomain === '@icloud.com'
                        ? 'bg-slate-700 text-white border-slate-700 shadow-sm ring-2 ring-slate-500/20'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-750'
                    ]"
                  >
                    <span>@icloud.com</span>
                    <span v-if="currentEmailDomain === '@icloud.com'" class="text-[9px] font-normal opacity-90">(aktif)</span>
                  </button>
                </div>

                <p v-if="form.errors.applicant_email" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.applicant_email }}
                </p>
                <p v-else class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                  Cukup ketik nama pengguna Anda (misal: <span class="font-mono text-slate-600 dark:text-slate-300 font-semibold">budi123</span>). Sistem otomatis melengkapinya ke <strong class="text-slate-700 dark:text-slate-200 font-semibold">@gmail.com</strong>, atau klik tombol pilihan di atas jika menggunakan email lain.
                </p>
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
                  id="field_applicant_district"
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
              <div id="field_applicant_village">
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
                  id="field_applicant_address"
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
            <div id="field_attendance_type" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
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
                  <div class="flex items-center justify-between mb-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                      NIK Perwakilan (16 Digit) <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] font-mono" :class="form.representative_nik.length === 16 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400'">
                      {{ form.representative_nik.length }}/16 Digit
                    </span>
                  </div>
                  <input
                    id="field_representative_nik"
                    :value="form.representative_nik"
                    @input="onRepresentativeNikInput"
                    @keypress="filterNikKeypress"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="16"
                    autocomplete="off"
                    placeholder="5205xxxxxxxxxxxx"
                    class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 font-mono tracking-wider"
                    :class="{'border-rose-500 ring-1 ring-rose-500': form.errors.representative_nik}"
                    required
                  />
                  <p v-if="form.errors.representative_nik" class="text-xs text-rose-500 font-medium mt-1">
                    {{ form.errors.representative_nik }}
                  </p>
                  <p v-else-if="form.representative_nik.length > 0 && form.representative_nik.length < 16" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium">
                    Kurang {{ 16 - form.representative_nik.length }} digit lagi. NIK wajib 16 digit angka.
                  </p>
                </div>

                <!-- Nama Perwakilan -->
                <div>
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Nama Lengkap Perwakilan <span class="text-rose-500">*</span>
                  </label>
                  <input
                    id="field_representative_name"
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
                  <div class="flex items-center justify-between mb-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                      Nomor WhatsApp / HP Perwakilan <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] font-mono" :class="rawPhoneDigits(form.representative_phone).length >= 10 && rawPhoneDigits(form.representative_phone).length <= 13 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400'">
                      {{ rawPhoneDigits(form.representative_phone).length }}/13 Digit (Min. 10)
                    </span>
                  </div>
                  <input
                    id="field_representative_phone"
                    :value="form.representative_phone"
                    @input="onRepresentativePhoneInput"
                    @keypress="filterPhoneKeypress"
                    type="tel"
                    inputmode="numeric"
                    maxlength="16"
                    autocomplete="tel"
                    placeholder="Contoh: 0853-xxxx-xxxx"
                    class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 font-mono tracking-wider"
                    :class="{'border-rose-500 ring-1 ring-rose-500': form.errors.representative_phone}"
                    required
                  />
                  <p v-if="form.errors.representative_phone" class="text-xs text-rose-500 font-medium mt-1">
                    {{ form.errors.representative_phone }}
                  </p>
                  <p v-else-if="rawPhoneDigits(form.representative_phone).length > 0 && rawPhoneDigits(form.representative_phone).length < 10" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium">
                    Kurang {{ 10 - rawPhoneDigits(form.representative_phone).length }} digit lagi. Nomor HP harus antara 10 sampai 13 digit angka.
                  </p>
                </div>

                <!-- Hubungan Perwakilan -->
                <div>
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                    Hubungan dengan Pemilik Dokumen <span class="text-rose-500">*</span>
                  </label>
                  <select
                    id="field_representative_relationship"
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
                    id="field_absence_reason"
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

    <!-- Popup Notifikasi Kolom Belum Lengkap -->
    <div
      v-if="showIncompleteModal"
      class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full shadow-2xl border border-rose-200 dark:border-rose-900/50 overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="p-5 sm:p-6 bg-gradient-to-r from-rose-500/10 via-amber-500/10 to-transparent border-b border-rose-100 dark:border-rose-900/40 flex items-start gap-3.5">
          <div class="w-12 h-12 rounded-2xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
              Kolom Formulir Belum Lengkap!
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
              Terdapat <strong class="text-rose-600 dark:text-rose-400 font-bold">{{ incompleteErrors.length }} kolom</strong> yang belum terisi atau belum sesuai ketentuan. Mohon periksa daftar berikut:
            </p>
          </div>
          <button
            type="button"
            @click="showIncompleteModal = false"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Modal Body: Error List -->
        <div class="p-4 sm:p-6 overflow-y-auto space-y-2.5 max-h-[52vh]">
          <div
            v-for="(err, idx) in incompleteErrors"
            :key="idx"
            @click="scrollToError(err.elementId)"
            class="p-3.5 rounded-2xl bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 hover:bg-rose-100/90 dark:hover:bg-rose-950/60 cursor-pointer transition flex items-start gap-3 group shadow-xs"
          >
            <span class="w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
              {{ idx + 1 }}
            </span>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2">
                <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition truncate">
                  {{ err.label }}
                </h4>
                <span class="text-[10px] text-rose-600 dark:text-rose-400 font-bold group-hover:underline flex items-center gap-0.5 shrink-0">
                  Isi Sekarang &rarr;
                </span>
              </div>
              <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5 leading-snug">
                {{ err.message }}
              </p>
            </div>
          </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
          <button
            type="button"
            @click="showIncompleteModal = false"
            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
          >
            Tutup
          </button>

          <button
            type="button"
            @click="scrollToError(incompleteErrors[0]?.elementId)"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-1.5 cursor-pointer"
          >
            <span>Lengkapi Kolom Sekarang</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
        </div>
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
  if (form.applicant_phone) {
    form.applicant_phone = formatPhoneChunked(form.applicant_phone);
  }
  if (form.representative_phone) {
    form.representative_phone = formatPhoneChunked(form.representative_phone);
  }
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

// NIK Keypress filter (only numbers 0-9)
function filterNikKeypress(e) {
  if (!/[0-9]/.test(e.key)) {
    e.preventDefault();
  }
}

// NIK Input Sanitizer (strips anything non-digit and caps at 16)
function onNikInput(e) {
  const digits = (e.target.value || '').replace(/\D/g, '').slice(0, 16);
  form.applicant_nik = digits;
  e.target.value = digits;
  if (form.errors.applicant_nik && digits.length === 16) {
    delete form.errors.applicant_nik;
  }
}

function onRepresentativeNikInput(e) {
  const digits = (e.target.value || '').replace(/\D/g, '').slice(0, 16);
  form.representative_nik = digits;
  e.target.value = digits;
  if (form.errors.representative_nik && digits.length === 16) {
    delete form.errors.representative_nik;
  }
}

// Phone Number Formatter & Sanitizer (chunked by 4 digits: e.g. 0853-3344-7003)
function formatPhoneChunked(value) {
  if (!value) return '';
  let digits = String(value).replace(/\D/g, '');
  if (digits.startsWith('62')) {
    digits = '0' + digits.slice(2);
  }
  digits = digits.slice(0, 13);
  if (!digits) return '';
  const chunks = digits.match(/.{1,4}/g);
  return chunks ? chunks.join('-') : digits;
}

function rawPhoneDigits(value) {
  if (!value) return '';
  let digits = String(value).replace(/\D/g, '');
  if (digits.startsWith('62')) {
    digits = '0' + digits.slice(2);
  }
  return digits.slice(0, 13);
}

// Phone Keypress filter (only numbers 0-9 and hyphen)
function filterPhoneKeypress(e) {
  if (!/[0-9\-]/.test(e.key)) {
    e.preventDefault();
  }
}

function handlePhoneInput(e, formKey) {
  const el = e.target;
  const oldVal = el.value || '';
  const cursorPos = el.selectionEnd || oldVal.length;
  const digitsBeforeCursor = oldVal.slice(0, cursorPos).replace(/\D/g, '').length;

  const formatted = formatPhoneChunked(oldVal);
  form[formKey] = formatted;
  el.value = formatted;

  let newCursorPos = 0;
  let digitCount = 0;
  for (let i = 0; i < formatted.length; i++) {
    if (formatted[i] !== '-') {
      digitCount++;
    }
    if (digitCount === digitsBeforeCursor) {
      newCursorPos = i + 1;
      break;
    }
  }
  if (digitCount < digitsBeforeCursor) {
    newCursorPos = formatted.length;
  }

  requestAnimationFrame(() => {
    if (el === document.activeElement && typeof el.setSelectionRange === 'function') {
      el.setSelectionRange(newCursorPos, newCursorPos);
    }
  });

  const digits = rawPhoneDigits(formatted);
  if (form.errors[formKey] && digits.length >= 10 && digits.length <= 13) {
    delete form.errors[formKey];
  }
}

function onPhoneInput(e) {
  handlePhoneInput(e, 'applicant_phone');
}

function onRepresentativePhoneInput(e) {
  handlePhoneInput(e, 'representative_phone');
}

// Applicant Email Helpers & Auto-domain (@gmail.com as Default)
const emailUsernameOnly = computed(() => {
  const val = (form.applicant_email || '').trim();
  if (!val) return '';
  if (!val.includes('@')) {
    return val.replace(/\s+/g, '');
  }
  return '';
});

const currentEmailDomain = computed(() => {
  const val = (form.applicant_email || '').trim().toLowerCase();
  if (!val) return '@gmail.com';
  if (!val.includes('@')) return '@gmail.com';
  const idx = val.indexOf('@');
  return val.slice(idx);
});

function normalizeApplicantEmail() {
  if (!form.applicant_email) return;
  let val = form.applicant_email.trim();
  if (!val) {
    form.applicant_email = '';
    return;
  }
  val = val.replace(/\s+/g, '');
  if (!val.includes('@')) {
    val = `${val}@gmail.com`;
  } else if (val.endsWith('@')) {
    val = `${val}gmail.com`;
  }
  form.applicant_email = val.toLowerCase();
  if (form.errors.applicant_email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (emailRegex.test(form.applicant_email)) {
      delete form.errors.applicant_email;
    }
  }
}

function clearApplicantEmail() {
  form.applicant_email = '';
  if (form.errors.applicant_email) {
    delete form.errors.applicant_email;
  }
}

function applyEmailDomain(domain) {
  const current = (form.applicant_email || '').trim().replace(/\s+/g, '');
  if (!current) {
    form.applicant_email = domain;
    setTimeout(() => {
      const el = document.getElementById('field_applicant_email');
      if (el) {
        el.focus();
        if (typeof el.setSelectionRange === 'function') {
          el.setSelectionRange(0, 0);
        }
      }
    }, 50);
    return;
  }
  const parts = current.split('@');
  const username = parts[0] || '';
  form.applicant_email = `${username}${domain}`.toLowerCase();
  if (form.errors.applicant_email) {
    delete form.errors.applicant_email;
  }
}

// Age & Birth Date Validation
const isBiometric = computed(() => !!selectedServiceObj.value?.is_biometric_first_recording);
const minRequiredAge = computed(() => isBiometric.value ? 16 : 17);

const maxBirthDate = computed(() => {
  const today = new Date();
  const age = minRequiredAge.value;
  const cutoff = new Date(today.getFullYear() - age, today.getMonth(), today.getDate());
  const y = cutoff.getFullYear();
  const m = String(cutoff.getMonth() + 1).padStart(2, '0');
  const d = String(cutoff.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
});

const minBirthDate = computed(() => {
  const today = new Date();
  return `${today.getFullYear() - 120}-01-01`;
});

const applicantAge = computed(() => {
  if (!form.applicant_birth_date) return null;
  const parts = form.applicant_birth_date.split('-');
  if (parts.length !== 3) return null;
  const birthYear = parseInt(parts[0], 10);
  const birthMonth = parseInt(parts[1], 10) - 1;
  const birthDay = parseInt(parts[2], 10);

  const today = new Date();
  let age = today.getFullYear() - birthYear;
  const m = today.getMonth() - birthMonth;
  if (m < 0 || (m === 0 && today.getDate() < birthDay)) {
    age--;
  }
  return age >= 0 ? age : 0;
});

const ageErrorMessage = computed(() => {
  if (applicantAge.value === null) return null;
  if (applicantAge.value < minRequiredAge.value) {
    if (isBiometric.value) {
      return `Usia pemohon saat ini ${applicantAge.value} tahun. Perekaman KTP-el Baru diperuntukkan bagi penduduk pemula minimal usia 16 tahun.`;
    } else {
      return `Usia pemohon saat ini ${applicantAge.value} tahun. Layanan pencetakan/penggantian KTP-el diperuntukkan bagi warga minimal usia 17 tahun. Bagi pemula usia 16 tahun silakan pilih layanan "Perekaman KTP-el Baru".`;
    }
  }
  return null;
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

const showIncompleteModal = ref(false);
const incompleteErrors = ref([]);

function validateForm() {
  const errors = [];
  form.clearErrors();

  // 1. Service Type
  if (!form.service_type_id) {
    const msg = 'Pilih salah satu jenis pelayanan KTP-el.';
    form.errors.service_type_id = msg;
    errors.push({
      field: 'service_type_id',
      elementId: 'field_service_type_id',
      label: 'Jenis Pelayanan KTP-el',
      message: msg,
    });
  }

  // 2. NIK Pemilik Dokumen
  if (!form.applicant_nik) {
    const msg = 'NIK pemilik dokumen wajib diisi.';
    form.errors.applicant_nik = msg;
    errors.push({
      field: 'applicant_nik',
      elementId: 'field_applicant_nik',
      label: 'NIK Pemilik Dokumen (16 Digit)',
      message: msg,
    });
  } else if (form.applicant_nik.length !== 16) {
    const msg = `NIK harus tepat 16 digit angka (saat ini ${form.applicant_nik.length} digit).`;
    form.errors.applicant_nik = msg;
    errors.push({
      field: 'applicant_nik',
      elementId: 'field_applicant_nik',
      label: 'NIK Pemilik Dokumen (16 Digit)',
      message: msg,
    });
  }

  // 3. Nama Lengkap
  if (!form.applicant_name || !form.applicant_name.trim()) {
    const msg = 'Nama lengkap sesuai Kartu Keluarga (KK) wajib diisi.';
    form.errors.applicant_name = msg;
    errors.push({
      field: 'applicant_name',
      elementId: 'field_applicant_name',
      label: 'Nama Lengkap Sesuai KK',
      message: msg,
    });
  }

  // 4. Tempat Lahir
  if (!form.applicant_birth_place || !form.applicant_birth_place.trim()) {
    const msg = 'Tempat lahir pemilik dokumen wajib diisi.';
    form.errors.applicant_birth_place = msg;
    errors.push({
      field: 'applicant_birth_place',
      elementId: 'field_applicant_birth_place',
      label: 'Tempat Lahir',
      message: msg,
    });
  }

  // 5. Tanggal Lahir & Validasi Usia
  if (!form.applicant_birth_date) {
    const msg = 'Tanggal lahir pemilik dokumen wajib diisi.';
    form.errors.applicant_birth_date = msg;
    errors.push({
      field: 'applicant_birth_date',
      elementId: 'field_applicant_birth_date',
      label: 'Tanggal Lahir',
      message: msg,
    });
  } else if (ageErrorMessage.value) {
    form.errors.applicant_birth_date = ageErrorMessage.value;
    errors.push({
      field: 'applicant_birth_date',
      elementId: 'field_applicant_birth_date',
      label: 'Syarat Usia Pemohon',
      message: ageErrorMessage.value,
    });
  }

  // 6. Nomor HP / WhatsApp
  const cleanPhone = (form.applicant_phone || '').replace(/\D/g, '');
  if (!cleanPhone) {
    const msg = 'Nomor WhatsApp / HP aktif wajib diisi.';
    form.errors.applicant_phone = msg;
    errors.push({
      field: 'applicant_phone',
      elementId: 'field_applicant_phone',
      label: 'Nomor WhatsApp / HP',
      message: msg,
    });
  } else if (cleanPhone.length < 10 || cleanPhone.length > 13) {
    const msg = `Nomor WhatsApp / HP harus berupa angka 10 sampai 13 digit (saat ini ${cleanPhone.length} digit).`;
    form.errors.applicant_phone = msg;
    errors.push({
      field: 'applicant_phone',
      elementId: 'field_applicant_phone',
      label: 'Nomor WhatsApp / HP',
      message: msg,
    });
  }

  // 6b. Alamat Email (Opsional - otomatis @gmail.com jika hanya username)
  normalizeApplicantEmail();
  if (form.applicant_email && form.applicant_email.trim()) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(form.applicant_email.trim())) {
      const msg = 'Format alamat email tidak valid (contoh: nama.pemohon@gmail.com).';
      form.errors.applicant_email = msg;
      errors.push({
        field: 'applicant_email',
        elementId: 'field_applicant_email',
        label: 'Alamat Email Pemohon',
        message: msg,
      });
    }
  }

  // 7. Kecamatan
  if (!form.applicant_district || !form.applicant_district.trim()) {
    const msg = 'Kecamatan tempat tinggal wajib dipilih.';
    form.errors.applicant_district = msg;
    errors.push({
      field: 'applicant_district',
      elementId: 'field_applicant_district',
      label: 'Kecamatan Domisili',
      message: msg,
    });
  }

  // 8. Kelurahan / Desa
  if (!form.applicant_village || !form.applicant_village.trim()) {
    const msg = 'Kelurahan / Desa tempat tinggal wajib dipilih atau diisi.';
    form.errors.applicant_village = msg;
    errors.push({
      field: 'applicant_village',
      elementId: 'field_applicant_village',
      label: 'Kelurahan / Desa Domisili',
      message: msg,
    });
  }

  // 9. Alamat Lengkap
  if (!form.applicant_address || !form.applicant_address.trim()) {
    const msg = 'Alamat tempat tinggal lengkap (jalan, RT/RW, Dusun) wajib diisi.';
    form.errors.applicant_address = msg;
    errors.push({
      field: 'applicant_address',
      elementId: 'field_applicant_address',
      label: 'Alamat Tempat Tinggal Lengkap',
      message: msg,
    });
  }

  // 10. Data Perwakilan (jika diwakilkan)
  if (form.attendance_type !== 'self') {
    if (!form.representative_nik) {
      const msg = 'NIK orang yang mewakili wajib diisi.';
      form.errors.representative_nik = msg;
      errors.push({
        field: 'representative_nik',
        elementId: 'field_representative_nik',
        label: 'NIK Perwakilan (16 Digit)',
        message: msg,
      });
    } else if (form.representative_nik.length !== 16) {
      const msg = `NIK perwakilan harus tepat 16 digit angka (saat ini ${form.representative_nik.length} digit).`;
      form.errors.representative_nik = msg;
      errors.push({
        field: 'representative_nik',
        elementId: 'field_representative_nik',
        label: 'NIK Perwakilan (16 Digit)',
        message: msg,
      });
    }

    if (!form.representative_name || !form.representative_name.trim()) {
      const msg = 'Nama lengkap perwakilan sesuai KTP wajib diisi.';
      form.errors.representative_name = msg;
      errors.push({
        field: 'representative_name',
        elementId: 'field_representative_name',
        label: 'Nama Lengkap Perwakilan',
        message: msg,
      });
    }

    const cleanRepPhone = (form.representative_phone || '').replace(/\D/g, '');
    if (!cleanRepPhone) {
      const msg = 'Nomor HP / WhatsApp perwakilan wajib diisi.';
      form.errors.representative_phone = msg;
      errors.push({
        field: 'representative_phone',
        elementId: 'field_representative_phone',
        label: 'Nomor HP Perwakilan',
        message: msg,
      });
    } else if (cleanRepPhone.length < 10 || cleanRepPhone.length > 13) {
      const msg = `Nomor WhatsApp / HP perwakilan harus berupa angka 10 sampai 13 digit (saat ini ${cleanRepPhone.length} digit).`;
      form.errors.representative_phone = msg;
      errors.push({
        field: 'representative_phone',
        elementId: 'field_representative_phone',
        label: 'Nomor HP Perwakilan',
        message: msg,
      });
    }

    if (!form.representative_relationship || !form.representative_relationship.trim()) {
      const msg = 'Hubungan perwakilan dengan pemilik dokumen wajib dipilih.';
      form.errors.representative_relationship = msg;
      errors.push({
        field: 'representative_relationship',
        elementId: 'field_representative_relationship',
        label: 'Hubungan Perwakilan',
        message: msg,
      });
    }

    if (!form.absence_reason || !form.absence_reason.trim()) {
      const msg = 'Alasan pemilik dokumen tidak dapat hadir sendiri wajib dijelaskan.';
      form.errors.absence_reason = msg;
      errors.push({
        field: 'absence_reason',
        elementId: 'field_absence_reason',
        label: 'Alasan Berhalangan Hadir',
        message: msg,
      });
    }
  }

  return errors;
}

function scrollToError(elementId) {
  showIncompleteModal.value = false;
  setTimeout(() => {
    const el = document.getElementById(elementId);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      el.classList.add('ring-4', 'ring-rose-500/60', 'border-rose-500', 'transition-all');
      if (typeof el.focus === 'function') {
        el.focus();
      }
      setTimeout(() => {
        el.classList.remove('ring-4', 'ring-rose-500/60');
      }, 3000);
    }
  }, 150);
}

function submitStep1() {
  normalizeApplicantEmail();
  const errors = validateForm();
  if (errors.length > 0) {
    incompleteErrors.value = errors;
    showIncompleteModal.value = true;
    return;
  }

  form.transform((data) => ({
    ...data,
    applicant_email: data.applicant_email ? data.applicant_email.trim() : null,
    applicant_phone: rawPhoneDigits(data.applicant_phone),
    representative_phone: data.representative_phone ? rawPhoneDigits(data.representative_phone) : '',
  })).post(route('queue.apply.store'), {
    onError: (serverErrors) => {
      const serverList = [];
      for (const [key, val] of Object.entries(serverErrors)) {
        serverList.push({
          field: key,
          elementId: `field_${key}`,
          label: key.replace(/_/g, ' ').toUpperCase(),
          message: Array.isArray(val) ? val[0] : val,
        });
      }
      if (serverList.length > 0) {
        incompleteErrors.value = serverList;
        showIncompleteModal.value = true;
      }
    }
  });
}
</script>
