<template>
  <AdminLayout :title="`Edit ${page.title} — PPID`">
    <div class="ppid-edit-container">
      <div class="ppid-edit-header">
        <div>
          <Link :href="route('admin.ppid.pages.index')" class="ppid-back-link">
            <ArrowLeft :size="14" /> Kembali ke Daftar Halaman
          </Link>
          <h1 class="ppid-edit-title">Edit Halaman: {{ page.title }}</h1>
        </div>
        <a :href="route('public.ppid.page', page.slug)" target="_blank" class="ppid-preview-btn">
          <Eye :size="15" /> Lihat di Website
        </a>
      </div>

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success" class="ppid-flash">
        <CheckCircle :size="16" /> {{ $page.props.flash.success }}
      </div>

      <form @submit.prevent="submit" class="ppid-edit-form">
        <div class="ppid-form-card">
          <div class="ppid-grid-2">
            <div class="ppid-field">
              <label>Judul Halaman <span class="req">*</span></label>
              <input v-model="form.title" type="text" placeholder="Judul Halaman" />
              <span v-if="form.errors.title" class="ppid-error">{{ form.errors.title }}</span>
            </div>
            <div class="ppid-field">
              <label>Slug URL (read-only)</label>
              <input :value="`/ppid/${page.slug}`" type="text" disabled class="disabled-input" />
            </div>
          </div>

          <div class="ppid-field">
            <label>Konten Halaman (HTML/Rich Text) <span class="req">*</span></label>
            <textarea v-model="form.content" rows="16" class="code-textarea" placeholder="Konten HTML..."></textarea>
            <span v-if="form.errors.content" class="ppid-error">{{ form.errors.content }}</span>
            <span class="ppid-help">Anda dapat memasukkan tag HTML seperti &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;table&gt;, dll.</span>
          </div>

          <div class="ppid-grid-2">
            <div class="ppid-field">
              <label>Meta Title (SEO)</label>
              <input v-model="form.meta_title" type="text" placeholder="Meta Title..." />
            </div>
            <div class="ppid-field">
              <label>Urutan Tampil (Sort Order)</label>
              <input v-model="form.sort_order" type="number" min="0" />
            </div>
          </div>

          <div class="ppid-field">
            <label>Meta Description (SEO)</label>
            <textarea v-model="form.meta_description" rows="2" placeholder="Meta description untuk mesin pencari..."></textarea>
          </div>

          <div class="ppid-checkbox-field">
            <label class="ppid-checkbox-label">
              <input type="checkbox" v-model="form.is_published" />
              <span>Publikasikan Halaman Ini</span>
            </label>
          </div>

          <div class="ppid-actions">
            <Link :href="route('admin.ppid.pages.index')" class="ppid-cancel-btn">Batal</Link>
            <button type="submit" class="ppid-save-btn" :disabled="form.processing">
              <Save :size="16" /> {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Eye, Save, CheckCircle } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ page: Object })

const form = useForm({
  title: props.page.title,
  icon: props.page.icon,
  content: props.page.content,
  meta_title: props.page.meta_title ?? '',
  meta_description: props.page.meta_description ?? '',
  sort_order: props.page.sort_order ?? 0,
  is_published: Boolean(props.page.is_published),
})

function submit() {
  form.put(route('admin.ppid.pages.update', props.page.id))
}
</script>

<style scoped>
.ppid-edit-container { padding: 24px; max-width: 960px; }
.ppid-edit-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.ppid-back-link { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #64748b; text-decoration: none; margin-bottom: 6px; }
.ppid-back-link:hover { color: #0f4c81; }
.ppid-edit-title { font-size: 1.4rem; font-weight: 700; color: #0f4c81; }
.ppid-preview-btn { display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: #f0f7ff; color: #0f4c81; border: 1px solid #bfdbfe; border-radius: 8px; text-decoration: none; font-size: 0.825rem; font-weight: 600; }
.ppid-flash { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #86efac; color: #15803d; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.875rem; }
.ppid-form-card { background: white; border-radius: 14px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 18px; }
.ppid-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.ppid-field { display: flex; flex-direction: column; gap: 6px; }
.ppid-field label { font-size: 0.85rem; font-weight: 600; color: #374151; }
.req { color: #dc2626; }
.ppid-field input, .ppid-field textarea { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 0.9rem; color: #1e293b; outline: none; }
.ppid-field input:focus, .ppid-field textarea:focus { border-color: #1a6bb5; box-shadow: 0 0 0 3px rgba(26,107,181,0.1); }
.disabled-input { background: #f8fafc; color: #64748b; cursor: not-allowed; }
.code-textarea { font-family: monospace; font-size: 0.875rem; line-height: 1.5; background: #fafafa; }
.ppid-help { font-size: 0.78rem; color: #64748b; }
.ppid-error { font-size: 0.78rem; color: #dc2626; }
.ppid-checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: #374151; cursor: pointer; }
.ppid-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
.ppid-cancel-btn { padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; color: #64748b; text-decoration: none; font-size: 0.875rem; font-weight: 600; }
.ppid-save-btn { display: flex; align-items: center; gap: 8px; padding: 10px 24px; background: linear-gradient(135deg, #0f4c81, #1565c0); color: white; border: none; border-radius: 8px; font-size: 0.875rem; font-weight: 700; cursor: pointer; }
@media (max-width: 640px) { .ppid-grid-2 { grid-template-columns: 1fr; } }
</style>
