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
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Foto Resmi Pejabat</label>
          <input type="file" @change="e => form.photo = e.target.files[0]" accept="image/*" class="text-xs text-slate-500" />
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
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  official: Object,
})

const isEdit = computed(() => !!props.official)

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
  email: props.official?.email || '',
  status: props.official?.status || 'active',
  photo: null,
})

const submit = () => {
  if (isEdit.value) {
    form.post('/admin/profile/officials/' + props.official.id + '?_method=PUT')
  } else {
    form.post('/admin/profile/officials')
  }
}
</script>
