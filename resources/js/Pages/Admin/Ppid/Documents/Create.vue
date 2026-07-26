<template>
  <AdminLayout title="PPID — Tambah Dokumen Baru">
    <div class="doc-form-container">
      <div class="doc-form-header">
        <Link :href="route('admin.ppid.documents.index')" class="back-link">
          <ArrowLeft :size="14" /> Kembali
        </Link>
        <h1 class="doc-form-title">Tambah Dokumen PPID Baru</h1>
      </div>

      <form @submit.prevent="submit" class="doc-form-card">
        <div class="form-grid-2">
          <div class="field">
            <label>Kategori PPID <span class="req">*</span></label>
            <select v-model="form.category">
              <option v-for="(label, key) in categoryLabels" :key="key" :value="key">{{ label }}</option>
            </select>
            <span v-if="form.errors.category" class="error">{{ form.errors.category }}</span>
          </div>

          <div class="field">
            <label>Subkategori</label>
            <input v-model="form.subcategory" type="text" placeholder="Contoh: Informasi Berkala, SOP, Formulir" />
          </div>
        </div>

        <div class="field">
          <label>Judul Dokumen <span class="req">*</span></label>
          <input v-model="form.title" type="text" placeholder="Judul Dokumen" />
          <span v-if="form.errors.title" class="error">{{ form.errors.title }}</span>
        </div>

        <div class="field">
          <label>Deskripsi Dokumen</label>
          <textarea v-model="form.description" rows="3" placeholder="Penjelasan singkat isi dokumen..."></textarea>
        </div>

        <div class="form-grid-2">
          <div class="field">
            <label>Unggah File Dokumen (PDF, Word, Excel)</label>
            <div 
              @click="$refs.fileInput.click()"
              class="border-2 border-dashed border-gray-200 dark:border-zinc-700 hover:border-primary-500 rounded-xl p-3 text-center cursor-pointer bg-gray-50/60 dark:bg-zinc-800/40 hover:bg-primary-50/30 transition group relative"
            >
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleFileUpload" 
                accept=".pdf,.doc,.docx,.xls,.xlsx" 
                class="hidden" 
              />
              <div v-if="form.file" class="flex items-center justify-between gap-2 text-left">
                <div class="flex items-center gap-2 min-w-0">
                  <UploadCloud class="w-4 h-4 text-primary-600 shrink-0" />
                  <span class="text-xs font-bold text-gray-800 dark:text-zinc-100 truncate">{{ form.file.name }}</span>
                </div>
                <span class="text-[10px] text-gray-400 font-mono shrink-0">{{ formatBytes(form.file.size) }}</span>
              </div>
              <div v-else class="py-1 flex flex-col items-center justify-center">
                <UploadCloud class="w-5 h-5 text-gray-400 group-hover:text-primary-600 mb-1 transition" />
                <span class="text-xs font-bold text-gray-700 dark:text-zinc-300">
                  <span class="text-primary-600 hover:underline">Klik untuk memilih dokumen</span> atau tarik berkas ke sini
                </span>
                <span class="text-[10px] text-gray-400 mt-0.5">Format: PDF, Word, Excel (Max 50MB)</span>
              </div>
            </div>
            <span v-if="form.errors.file" class="error">{{ form.errors.file }}</span>
          </div>

          <div class="field">
            <label>Atau URL File Eksternal (Opsional)</label>
            <input v-model="form.file_url" type="url" placeholder="https://..." />
          </div>
        </div>

        <div class="form-grid-2">
          <div class="field">
            <label>Tahun Dokumen</label>
            <input v-model="form.year" type="number" placeholder="2024" min="2000" max="2099" />
          </div>

          <div class="field">
            <label>Urutan Tampil (Sort Order)</label>
            <input v-model="form.sort_order" type="number" min="0" />
          </div>
        </div>

        <div class="field checkbox-field">
          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_published" />
            <span>Publikasikan Dokumen Ini</span>
          </label>
        </div>

        <div class="actions">
          <Link :href="route('admin.ppid.documents.index')" class="cancel-btn">Batal</Link>
          <button type="submit" class="save-btn" :disabled="form.processing">
            <Save :size="16" /> {{ form.processing ? 'Menyimpan...' : 'Simpan Dokumen' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Save, UploadCloud } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ categoryLabels: Object })

const fileInput = ref(null)

const form = useForm({
  category: 'informasi_publik',
  subcategory: '',
  title: '',
  description: '',
  file: null,
  file_url: '',
  year: new Date().getFullYear(),
  sort_order: 0,
  is_published: true,
})

function formatBytes(bytes) {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function handleFileUpload(e) {
  form.file = e.target.files[0]
}

function submit() {
  form.post(route('admin.ppid.documents.store'))
}
</script>

<style scoped>
.doc-form-container { padding: 24px; max-width: 860px; }
.doc-form-header { margin-bottom: 24px; }
.back-link { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #64748b; text-decoration: none; margin-bottom: 6px; }
.doc-form-title { font-size: 1.4rem; font-weight: 700; color: #0f4c81; }
.doc-form-card { background: white; border-radius: 14px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 18px; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 0.85rem; font-weight: 600; color: #374151; }
.req { color: #dc2626; }
.field input, .field textarea, .field select { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 0.9rem; color: #1e293b; outline: none; }
.error { font-size: 0.78rem; color: #dc2626; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: #374151; cursor: pointer; }
.actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
.cancel-btn { padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; color: #64748b; text-decoration: none; font-size: 0.875rem; font-weight: 600; }
.save-btn { display: flex; align-items: center; gap: 8px; padding: 10px 24px; background: linear-gradient(135deg, #0f4c81, #1565c0); color: white; border: none; border-radius: 8px; font-size: 0.875rem; font-weight: 700; cursor: pointer; }
@media (max-width: 640px) { .form-grid-2 { grid-template-columns: 1fr; } }
</style>
