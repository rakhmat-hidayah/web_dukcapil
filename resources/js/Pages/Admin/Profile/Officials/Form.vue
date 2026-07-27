<template>
  <AdminLayout :title="isEdit ? 'Edit Pejabat' : 'Tambah Pejabat'">
    <div class="max-w-4xl mx-auto space-y-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ isEdit ? 'Edit Data Pejabat' : 'Tambah Pejabat Baru' }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Master Official Directory Single Source of Truth</p>
        </div>
        <Link href="/admin/profile/officials" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold">
          &larr; Kembali
        </Link>
      </div>

      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-xl border border-slate-100 dark:border-slate-700 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap Pejabat *</label>
            <input v-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">NIP</label>
            <input v-model="form.nip" type="text" placeholder="19680512 199303 1 005" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Jabatan *</label>
            <input v-model="form.position_title" type="text" required placeholder="Kepala Dinas Kependudukan dan Pencatatan Sipil" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Pangkat / Golongan</label>
            <input v-model="form.rank_golongan" type="text" placeholder="Pembina Utama Muda / IV c" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Unit Kerja / Bidang</label>
            <input v-model="form.department" type="text" placeholder="Dinas Kependudukan dan Pencatatan Sipil" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Status Kedinasan</label>
            <select v-model="form.status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
              <option value="active">Active (Aktif)</option>
              <option value="inactive">Inactive (Nonaktif)</option>
              <option value="retired">Retired (Purna Tugas)</option>
              <option value="transferred">Transferred (Mutasi)</option>
            </select>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Nomor Telepon / Kontak</label>
              <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" v-model="form.show_phone" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Tampilkan</span>
              </label>
            </div>
            <input v-model="form.phone" type="text" placeholder="(0373) 21124 / 08123456789" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Alamat Email Resmi Pejabat</label>
              <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" v-model="form.show_email" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Tampilkan</span>
              </label>
            </div>
            <input v-model="form.email" type="email" placeholder="kadis@dukcapil.dompukab.go.id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div class="md:col-span-2">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat Kantor</label>
            <input v-model="form.office_address" type="text" placeholder="Jl. Bhayangkara No. 01, Kel. Bada, Dompu, NTB" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Foto Resmi Pejabat</label>
          <div 
            @click="$refs.photoInput.click()"
            class="border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 rounded-2xl p-4 text-center cursor-pointer bg-slate-50/60 dark:bg-slate-900/40 hover:bg-blue-50/30 dark:hover:bg-blue-950/20 transition group relative overflow-hidden"
          >
            <input 
              type="file" 
              ref="photoInput" 
              @change="handlePhotoUpload" 
              accept="image/*" 
              class="hidden" 
            />

            <!-- Preview Image -->
            <div v-if="photoPreview || (isEdit && official?.photo_url)" class="relative h-44 w-36 mx-auto rounded-xl overflow-hidden bg-slate-900 border border-slate-700/60 shadow-md group/img">
              <img 
                :src="photoPreview || official?.photo_url" 
                class="w-full h-full object-cover group-hover/img:scale-105 transition duration-300" 
              />
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 transition flex items-center justify-center p-2 text-center">
                <span class="px-2.5 py-1 bg-white/90 dark:bg-slate-900/90 text-slate-800 dark:text-slate-100 text-[10px] font-bold rounded-lg shadow-md flex items-center gap-1">
                  <UploadCloud class="w-3.5 h-3.5 text-blue-500" />
                  Ganti Foto
                </span>
              </div>
            </div>

            <!-- Default Upload State -->
            <div v-else class="py-4 flex flex-col items-center justify-center">
              <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:bg-blue-100 dark:group-hover:bg-blue-950/50 flex items-center justify-center mb-2 transition">
                <UploadCloud class="w-5 h-5" />
              </div>
              <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                <span class="text-blue-600 dark:text-blue-400 hover:underline">Klik untuk mengunggah foto pejabat</span> atau tarik file ke sini
              </p>
              <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Max 5MB)</p>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Biografi & Ringkasan Profil</label>
          <textarea v-model="form.biography" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Tugas Pokok & Wewenang</label>
          <textarea v-model="form.main_duties" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
          <Link href="/admin/profile/officials" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold">Batal</Link>
          <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-600/30">Simpan Pejabat</button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { UploadCloud } from '@lucide/vue'

const props = defineProps({
  official: Object,
})

const isEdit = computed(() => !!props.official)
const photoInput = ref(null)
const photoPreview = ref(null)

const form = useForm({
  name: props.official?.name || '',
  nip: props.official?.nip || '',
  position_title: props.official?.position_title || '',
  rank_golongan: props.official?.rank_golongan || '',
  department: props.official?.department || 'Dinas Kependudukan dan Pencatatan Sipil',
  biography: props.official?.biography || '',
  main_duties: props.official?.main_duties || '',
  office_address: props.official?.office_address || 'Jl. Bhayangkara No. 01, Dompu',
  phone: props.official?.phone || '(0373) 21124',
  show_phone: props.official ? Boolean(props.official.show_phone) : true,
  email: props.official?.email || '',
  show_email: props.official ? Boolean(props.official.show_email) : true,
  status: props.official?.status || 'active',
  photo: null,
})

const handlePhotoUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.photo = file
    photoPreview.value = URL.createObjectURL(file)
  }
}

const submit = () => {
  if (isEdit.value) {
    form.post('/admin/profile/officials/' + props.official.id + '?_method=PUT')
  } else {
    form.post('/admin/profile/officials')
  }
}
</script>
