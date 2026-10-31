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
            <h3 class="text-base font-black text-slate-900 dark:text-white">
              Data Informasi Layanan
            </h3>

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
                />
              </div>

              <!-- Slug -->
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Slug URL (Otomatis)
                </label>
                <input
                  v-model="form.slug"
                  type="text"
                  placeholder="cetak-hilang"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
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
                  <option value="rose">Rose (Merah)</option>
                  <option value="amber">Amber (Oranye)</option>
                  <option value="indigo">Indigo (Ungu)</option>
                  <option value="emerald">Emerald (Hijau)</option>
                  <option value="blue">Blue (Biru)</option>
                </select>
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
                  placeholder="Gratis (Rp 0)"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
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

              <!-- Toggles -->
              <div class="sm:col-span-2 flex flex-col sm:flex-row gap-4 pt-2">
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
                    v-model="form.requires_face_photo"
                    class="rounded text-blue-600 focus:ring-blue-500"
                  />
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Wajib Foto Wajah Langsung (Selfie Kamera Depan)
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
                :disabled="serviceSubmitting"
                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-1.5"
              >
                <span v-if="serviceSubmitting">Menyimpan...</span>
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
                        @click="openEditDocModal(doc)"
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[11px] transition"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteDoc(doc)"
                        class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 text-rose-600 font-bold text-[11px] transition"
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
                <strong>Dinamis & Fleksibel:</strong> Jangan hardcode persyaratan. Seluruh formulir pendaftaran warga akan secara otomatis mengikuti daftar berkas yang diatur di halaman ini.
              </p>
              <p>
                <strong>Mode Kamera:</strong>
                Gunakan <em>Kamera Belakang</em> untuk dokumen fisik (KTP lama, Kartu Keluarga, Akta) agar pemohon diarahkan menggunakan lensa resolusi tinggi.
              </p>
              <p>
                <strong>Batas Upload:</strong>
                Sesuai konfigurasi server Disdukcapil (maksimal 32 MB per berkas), kompresi adaptif di sisi browser akan merampingkan foto hingga &lt; 2 MB untuk efisiensi penyimpanan server.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DOCUMENT FORM MODAL (Add / Edit Requirement) -->
    <div v-if="docModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-4">
        <h3 class="text-lg font-black text-slate-900 dark:text-white">
          {{ editingDocId ? 'Edit Persyaratan Berkas' : 'Tambah Persyaratan Berkas Baru' }}
        </h3>

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
            />
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
                Urutan (Sort Order)
              </label>
              <input
                v-model.number="docForm.sort_order"
                type="number"
                min="1"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                Wajib Diunggah (Form tidak dapat dikirim tanpa berkas ini)
              </span>
            </label>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700/60">
            <button
              type="button"
              @click="docModalOpen = false"
              class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="docSubmitting"
              class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition flex items-center gap-1.5"
            >
              <span v-if="docSubmitting">Menyimpan...</span>
              <span v-else>Simpan Persyaratan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  serviceType: Object,
})

const isEdit = computed(() => !!props.serviceType)

// Service form
const form = ref({
  name: props.serviceType?.name || '',
  slug: props.serviceType?.slug || '',
  short_description: props.serviceType?.short_description || '',
  long_description: props.serviceType?.long_description || '',
  color: props.serviceType?.color || 'blue',
  quota_per_day: props.serviceType?.quota_per_day || 30,
  processing_days_label: props.serviceType?.processing_days_label || '1 Hari Kerja',
  cost_label: props.serviceType?.cost_label || 'Gratis',
  requires_face_photo: props.serviceType ? !!props.serviceType.requires_face_photo : true,
  is_active: props.serviceType ? !!props.serviceType.is_active : true,
  sort_order: props.serviceType?.sort_order || 1,
})

const serviceSubmitting = ref(false)

const submitService = () => {
  serviceSubmitting.value = true
  if (isEdit.value) {
    router.put(route('admin.queue.service-types.update', props.serviceType.id), form.value, {
      onFinish: () => { serviceSubmitting.value = false },
    })
  } else {
    router.post(route('admin.queue.service-types.store'), form.value, {
      onFinish: () => { serviceSubmitting.value = false },
    })
  }
}

// Requirement document modal
const docModalOpen = ref(false)
const docSubmitting = ref(false)
const editingDocId = ref(null)

const docForm = ref({
  label: '',
  description: '',
  accepted_formats: 'jpg,jpeg,png,pdf',
  max_size_kb: 10240,
  is_required: true,
  capture_mode: 'environment',
  capture_hint: '',
  sort_order: 1,
})

const openAddDocModal = () => {
  editingDocId.value = null
  docForm.value = {
    label: '',
    description: '',
    accepted_formats: 'jpg,jpeg,png,pdf',
    max_size_kb: 10240,
    is_required: true,
    capture_mode: 'environment',
    capture_hint: 'Posisikan dokumen dalam bingkai dan hindari pantulan cahaya',
    sort_order: (props.serviceType.documents?.length || 0) + 1,
  }
  docModalOpen.value = true
}

const openEditDocModal = (doc) => {
  editingDocId.value = doc.id
  docForm.value = {
    label: doc.label,
    description: doc.description || '',
    accepted_formats: doc.accepted_formats || 'jpg,jpeg,png,pdf',
    max_size_kb: doc.max_size_kb || 10240,
    is_required: !!doc.is_required,
    capture_mode: doc.capture_mode || 'environment',
    capture_hint: doc.capture_hint || '',
    sort_order: doc.sort_order || 1,
  }
  docModalOpen.value = true
}

const saveDoc = () => {
  docSubmitting.value = true
  if (editingDocId.value) {
    router.put(
      route('admin.queue.service-types.documents.update', {
        service_type: props.serviceType.id,
        document: editingDocId.value,
      }),
      docForm.value,
      {
        onFinish: () => {
          docSubmitting.value = false
          docModalOpen.value = false
        },
      }
    )
  } else {
    router.post(
      route('admin.queue.service-types.documents.store', props.serviceType.id),
      docForm.value,
      {
        onFinish: () => {
          docSubmitting.value = false
          docModalOpen.value = false
        },
      }
    )
  }
}

const deleteDoc = (doc) => {
  if (confirm(`Hapus persyaratan '${doc.label}'?`)) {
    router.delete(
      route('admin.queue.service-types.documents.destroy', {
        service_type: props.serviceType.id,
        document: doc.id,
      })
    )
  }
}
</script>
