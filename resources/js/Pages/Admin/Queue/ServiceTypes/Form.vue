<template>
  <Head :title="`${isEdit ? 'Edit ' + form.name : 'Tambah Jenis Layanan'} - Admin Disdukcapil`" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex items-center gap-3">
        <Link
          :href="route('admin.queue.service-types.index')"
          class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 transition"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </Link>
        <div>
          <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
            {{ isEdit ? 'Pengaturan Layanan: ' + form.name : 'Tambah Jenis Layanan Baru' }}
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Konfigurasi profil layanan, kuota harian, serta daftar persyaratan berkas pemohon.
          </p>
        </div>
      </div>

      <!-- Main Form Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT 2 COLUMNS: Service Details Form -->
        <div class="lg:col-span-2 space-y-6">
          <form @submit.prevent="submitService" class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-black text-slate-900 dark:text-white">
                Data Informasi Layanan
              </h3>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-bold',
                  form.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700'
                ]"
              >
                {{ form.is_active ? 'Status: Aktif' : 'Status: Nonaktif' }}
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Name -->
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Nama Jenis Layanan *
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="Contoh: Cetak KTP-el Karena Hilang"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{'border-rose-500 ring-1 ring-rose-500': form.errors.name}"
                />
                <p v-if="form.errors.name" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.name }}
                </p>
              </div>

              <!-- Slug -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Slug URL *
                </label>
                <input
                  v-model="form.slug"
                  type="text"
                  required
                  placeholder="cetak-hilang"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{'border-rose-500 ring-1 ring-rose-500': form.errors.slug}"
                />
                <p v-if="form.errors.slug" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.slug }}
                </p>
              </div>

              <!-- Color -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Warna Identitas (Theme) *
                </label>
                <select
                  v-model="form.color"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="#dc2626">Merah (#dc2626 / Rose)</option>
                  <option value="#ea580c">Oranye (#ea580c / Amber)</option>
                  <option value="#0284c7">Biru (#0284c7 / Blue)</option>
                  <option value="#16a34a">Hijau (#16a34a / Emerald)</option>
                  <option value="#7c3aed">Ungu (#7c3aed / Indigo)</option>
                  <option value="rose">Rose (Merah)</option>
                  <option value="amber">Amber (Oranye)</option>
                  <option value="blue">Blue (Biru)</option>
                  <option value="emerald">Emerald (Hijau)</option>
                  <option value="indigo">Indigo (Ungu)</option>
                </select>
                <p v-if="form.errors.color" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.color }}
                </p>
              </div>

              <!-- Daily Quota -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Kuota Pelayanan Per Hari *
                </label>
                <input
                  v-model.number="form.quota_per_day"
                  type="number"
                  min="1"
                  max="500"
                  required
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.quota_per_day" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.quota_per_day }}
                </p>
              </div>

              <!-- Processing Days -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Estimasi Waktu Pelayanan *
                </label>
                <input
                  v-model="form.processing_days_label"
                  type="text"
                  required
                  placeholder="1 Hari Kerja"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.processing_days_label" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.processing_days_label }}
                </p>
              </div>

              <!-- Cost -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Tarif / Biaya *
                </label>
                <input
                  v-model="form.cost_label"
                  type="text"
                  required
                  placeholder="Gratis / Rp 0"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.cost_label" class="text-xs text-rose-500 font-medium mt-1">
                  {{ form.errors.cost_label }}
                </p>
              </div>

              <!-- Sort Order -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Urutan Tampil (Sort Order)
                </label>
                <input
                  v-model.number="form.sort_order"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <!-- Short Description -->
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Deskripsi Singkat
                </label>
                <textarea
                  v-model="form.short_description"
                  rows="2"
                  placeholder="Penjelasan singkat untuk kartu layanan di halaman pendaftaran..."
                  class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
              </div>

              <!-- Long Description -->
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Deskripsi Lengkap / Ketentuan Khusus
                </label>
                <textarea
                  v-model="form.long_description"
                  rows="2"
                  placeholder="Penjelasan lengkap ketentuan dokumen, alur pelayanan..."
                  class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
              </div>

              <!-- Toggles: Status & Biometrik -->
              <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.is_active"
                    class="rounded text-blue-600 focus:ring-blue-500"
                  />
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Layanan Aktif (Dapat dipilih pemohon)
                  </span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.is_biometric_first_recording"
                    class="rounded text-amber-600 focus:ring-amber-500"
                  />
                  <span class="text-xs font-bold text-amber-800 dark:text-amber-300">
                    Layanan Perekaman Biometrik (Wajib Hadir Sendiri, Min. 16 Thn)
                  </span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.requires_face_photo"
                    class="rounded text-blue-600 focus:ring-blue-500"
                  />
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Wajib Foto Wajah Langsung (Selfie Kamera Depan)
                  </span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.allow_family_in_kk"
                    class="rounded text-blue-600 focus:ring-blue-500"
                  />
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Izinkan Diwakilkan Keluarga dalam KK
                  </span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.allow_other"
                    class="rounded text-blue-600 focus:ring-blue-500"
                  />
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Izinkan Diwakilkan Pihak Lain / Kuasa
                  </span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.require_power_of_attorney"
                    class="rounded text-blue-600 focus:ring-blue-500"
                  />
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Wajib Surat Kuasa (Jika Diwakilkan)
                  </span>
                </label>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-end gap-2">
              <Link
                :href="route('admin.queue.service-types.index')"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition"
              >
                Batal
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-1.5 cursor-pointer"
              >
                <span v-if="form.processing">Menyimpan...</span>
                <span v-else>{{ isEdit ? 'Simpan Perubahan Layanan' : 'Buat Layanan' }}</span>
              </button>
            </div>
          </form>

          <!-- REQUIREMENTS MANAGER SECTION (Only when editing existing service) -->
          <div v-if="isEdit" class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-4">
              <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">
                  Daftar Persyaratan Berkas Digital
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                  Persyaratan ini harus diunggah atau dipotret langsung oleh pemohon sebelum pendaftaran dapat diajukan.
                </p>
              </div>

              <button
                type="button"
                @click="openAddDocModal"
                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Persyaratan
              </button>
            </div>

            <!-- Documents Table -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                  <tr>
                    <th class="px-4 py-3">Urutan</th>
                    <th class="px-4 py-3">Nama Berkas</th>
                    <th class="px-4 py-3">Format / Batas</th>
                    <th class="px-4 py-3">Mode Kamera</th>
                    <th class="px-4 py-3 text-center">Wajib?</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                  <tr
                    v-for="doc in serviceType.documents"
                    :key="doc.id"
                    class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition"
                  >
                    <td class="px-4 py-3 font-mono font-bold text-slate-400">
                      {{ doc.sort_order }}
                    </td>
                    <td class="px-4 py-3">
                      <p class="font-bold text-slate-900 dark:text-white">{{ doc.label }}</p>
                      <p v-if="doc.description" class="text-[11px] text-slate-400 mt-0.5">{{ doc.description }}</p>
                      <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span v-if="doc.applicable_to && doc.applicable_to !== 'all'" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                          {{ doc.applicable_to === 'representative_only' ? 'Khusus Perwakilan' : 'Khusus Pemohon Langsung' }}
                        </span>
                        <a
                          v-if="doc.template_url"
                          :href="doc.template_url"
                          target="_blank"
                          class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline bg-blue-50 dark:bg-blue-900/40 px-1.5 py-0.5 rounded"
                        >
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                          <span>Template Blanko</span>
                        </a>
                      </div>
                    </td>
                    <td class="px-4 py-3 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                      {{ doc.accepted_formats }} · Max {{ Math.round(doc.max_size_kb / 1024) }} MB
                    </td>
                    <td class="px-4 py-3 text-[11px]">
                      <span v-if="doc.capture_mode === 'environment'" class="text-blue-600 font-bold">Kamera Belakang (Dokumen)</span>
                      <span v-else-if="doc.capture_mode === 'user'" class="text-indigo-600 font-bold">Kamera Depan (Selfie)</span>
                      <span v-else class="text-slate-500 font-bold">Bebas / Galeri</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span
                        :class="[
                          'px-2 py-0.5 rounded text-[10px] font-bold',
                          doc.is_required ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400'
                        ]"
                      >
                        {{ doc.is_required ? 'Wajib' : 'Opsional' }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                      <button
                        type="button"
                        @click="openEditDocModal(doc)"
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[11px] transition cursor-pointer"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        @click="deleteDoc(doc)"
                        class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 text-rose-600 font-bold text-[11px] transition cursor-pointer"
                      >
                        Hapus
                      </button>
                    </td>
                  </tr>

                  <tr v-if="!serviceType.documents || serviceType.documents.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">
                      Belum ada berkas persyaratan. Klik tombol "Tambah Persyaratan" di atas.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- RIGHT 1 COLUMN: Guide & Info -->
        <div class="space-y-6">
          <div class="bg-gradient-to-br from-blue-900 to-indigo-950 rounded-3xl p-6 text-white space-y-4 shadow-xl">
            <h4 class="text-sm font-black flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Panduan Manajemen Persyaratan
            </h4>
            <div class="text-xs text-blue-100/90 space-y-2.5 leading-relaxed">
              <p>
                <strong>Dinamis & Fleksibel:</strong> Jangan ragu menambah atau memperbarui berkas persyaratan. Formulir pendaftaran warga di publik akan otomatis menyesuaikan dengan data berkas di halaman ini.
              </p>
              <p>
                <strong>Mode Kamera:</strong>
                Gunakan <em>Kamera Belakang</em> untuk dokumen fisik (KTP lama, Kartu Keluarga, Akta) agar pemohon diarahkan menggunakan lensa resolusi tinggi.
              </p>
              <p>
                <strong>Ketentuan Perwakilan:</strong>
                Gunakan pengaturan <em>Khusus Jika Permohonan Diwakilkan</em> untuk berkas yang hanya wajib ketika pengurusan diwakilkan orang lain (misal: Surat Kuasa, KTP Perwakilan).
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DOCUMENT FORM MODAL (Add / Edit Requirement) -->
    <div v-if="docModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
          <h3 class="text-lg font-black text-slate-900 dark:text-white">
            {{ editingDocId ? 'Edit Persyaratan Berkas' : 'Tambah Persyaratan Berkas Baru' }}
          </h3>
          <button
            type="button"
            @click="docModalOpen = false"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <form @submit.prevent="saveDoc" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Nama / Label Berkas Persyaratan *
            </label>
            <input
              v-model="docForm.label"
              type="text"
              required
              placeholder="Contoh: Kartu Keluarga Asli"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{'border-rose-500 ring-1 ring-rose-500': docForm.errors.label}"
            />
            <p v-if="docForm.errors.label" class="text-xs text-rose-500 font-medium mt-1">
              {{ docForm.errors.label }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Penjelasan / Petunjuk untuk Pemohon
            </label>
            <textarea
              v-model="docForm.description"
              rows="2"
              placeholder="Contoh: Unggah foto Kartu Keluarga asli yang masih berlaku, pastikan seluruh tulisan terbaca jelas."
              class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Format File *
              </label>
              <input
                v-model="docForm.accepted_formats"
                type="text"
                required
                placeholder="jpg,jpeg,png,pdf"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p v-if="docForm.errors.accepted_formats" class="text-xs text-rose-500 font-medium mt-1">
                {{ docForm.errors.accepted_formats }}
              </p>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Ukuran Maks (KB) *
              </label>
              <input
                v-model.number="docForm.max_size_kb"
                type="number"
                min="512"
                max="32768"
                required
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p v-if="docForm.errors.max_size_kb" class="text-xs text-rose-500 font-medium mt-1">
                {{ docForm.errors.max_size_kb }}
              </p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Mode Kamera *
              </label>
              <select
                v-model="docForm.capture_mode"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="environment">Kamera Belakang (Dokumen)</option>
                <option value="user">Kamera Depan (Wajah)</option>
                <option value="any">Bebas / Galeri Saja</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Ketentuan Pemohon
              </label>
              <select
                v-model="docForm.applicable_to"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="all">Semua Pemohon</option>
                <option value="self_only">Khusus Hadir Sendiri</option>
                <option value="representative_only">Khusus Jika Diwakilkan</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Urutan Tampil (Sort Order)
              </label>
              <input
                v-model.number="docForm.sort_order"
                type="number"
                min="1"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Link Download Blanko (Opsional)
              </label>
              <input
                v-model="docForm.template_url"
                type="text"
                placeholder="https://dukcapil.../downloads"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Petunjuk Saat Kamera Dibuka
            </label>
            <input
              v-model="docForm.capture_hint"
              type="text"
              placeholder="Posisikan dokumen dalam bingkai dan hindari pantulan cahaya"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                v-model="docForm.is_required"
                class="rounded text-rose-600 focus:ring-rose-500"
              />
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                Wajib Diunggah (Pendaftaran tidak dapat dikirim tanpa berkas ini)
              </span>
            </label>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700/60">
            <button
              type="button"
              @click="docModalOpen = false"
              class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-700"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="docForm.processing"
              class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition flex items-center gap-1.5 cursor-pointer"
            >
              <span v-if="docForm.processing">Menyimpan...</span>
              <span v-else>{{ editingDocId ? 'Simpan Perubahan' : 'Tambah Persyaratan' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  serviceType: Object,
})

const isEdit = computed(() => !!props.serviceType)

// Service details form
const form = useForm({
  name: props.serviceType?.name || '',
  slug: props.serviceType?.slug || '',
  short_description: props.serviceType?.short_description || '',
  long_description: props.serviceType?.long_description || '',
  color: props.serviceType?.color || '#0284c7',
  icon: props.serviceType?.icon || 'FileText',
  quota_per_day: props.serviceType?.quota_per_day || 30,
  processing_days_label: props.serviceType?.processing_days_label || '1 Hari Kerja',
  cost_label: props.serviceType?.cost_label || 'Gratis / Rp 0',
  requires_face_photo: props.serviceType ? !!props.serviceType.requires_face_photo : true,
  allow_self: props.serviceType ? !!props.serviceType.allow_self : true,
  allow_family_in_kk: props.serviceType ? !!props.serviceType.allow_family_in_kk : true,
  allow_family_out_kk: props.serviceType ? !!props.serviceType.allow_family_out_kk : true,
  allow_other: props.serviceType ? !!props.serviceType.allow_other : true,
  require_power_of_attorney: props.serviceType ? !!props.serviceType.require_power_of_attorney : true,
  require_absence_reason: props.serviceType ? !!props.serviceType.require_absence_reason : true,
  is_biometric_first_recording: props.serviceType ? !!props.serviceType.is_biometric_first_recording : false,
  policy_notes: props.serviceType?.policy_notes || '',
  is_active: props.serviceType ? !!props.serviceType.is_active : true,
  sort_order: props.serviceType?.sort_order || 1,
})

const submitService = () => {
  if (isEdit.value) {
    form.put(route('admin.queue.service-types.update', props.serviceType.id), {
      preserveScroll: true,
    })
  } else {
    form.post(route('admin.queue.service-types.store'), {
      preserveScroll: true,
    })
  }
}

// Requirement document modal & form
const docModalOpen = ref(false)
const editingDocId = ref(null)

const docForm = useForm({
  label: '',
  description: '',
  accepted_formats: 'jpg,jpeg,png,pdf',
  max_size_kb: 5120,
  is_required: true,
  capture_mode: 'environment',
  applicable_to: 'all',
  capture_hint: '',
  template_url: '',
  sort_order: 1,
})

const openAddDocModal = () => {
  editingDocId.value = null
  docForm.reset()
  docForm.clearErrors()
  docForm.label = ''
  docForm.description = ''
  docForm.accepted_formats = 'jpg,jpeg,png,pdf'
  docForm.max_size_kb = 5120
  docForm.is_required = true
  docForm.capture_mode = 'environment'
  docForm.applicable_to = 'all'
  docForm.capture_hint = 'Posisikan dokumen dalam bingkai dan hindari pantulan cahaya'
  docForm.template_url = ''
  docForm.sort_order = (props.serviceType?.documents?.length || 0) + 1
  docModalOpen.value = true
}

const openEditDocModal = (doc) => {
  editingDocId.value = doc.id
  docForm.clearErrors()
  docForm.label = doc.label || ''
  docForm.description = doc.description || ''
  docForm.accepted_formats = doc.accepted_formats || 'jpg,jpeg,png,pdf'
  docForm.max_size_kb = doc.max_size_kb || 5120
  docForm.is_required = !!doc.is_required
  docForm.capture_mode = doc.capture_mode || 'environment'
  docForm.applicable_to = doc.applicable_to || 'all'
  docForm.capture_hint = doc.capture_hint || ''
  docForm.template_url = doc.template_url || ''
  docForm.sort_order = doc.sort_order ?? 1
  docModalOpen.value = true
}

const saveDoc = () => {
  if (editingDocId.value) {
    docForm.put(
      route('admin.queue.service-documents.update', {
        serviceTypeId: props.serviceType.id,
        documentId: editingDocId.value,
      }),
      {
        preserveScroll: true,
        onSuccess: () => {
          docModalOpen.value = false
          editingDocId.value = null
        },
      }
    )
  } else {
    docForm.post(
      route('admin.queue.service-documents.store', {
        serviceTypeId: props.serviceType.id,
      }),
      {
        preserveScroll: true,
        onSuccess: () => {
          docModalOpen.value = false
          editingDocId.value = null
        },
      }
    )
  }
}

const deleteDoc = (doc) => {
  if (confirm(`Hapus persyaratan '${doc.label}'?`)) {
    router.delete(
      route('admin.queue.service-documents.destroy', {
        serviceTypeId: props.serviceType.id,
        documentId: doc.id,
      }),
      {
        preserveScroll: true,
      }
    )
  }
}
</script>
