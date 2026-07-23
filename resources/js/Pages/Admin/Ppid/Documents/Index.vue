<template>
  <AdminLayout title="PPID — Kelola Dokumen">
    <div class="ppid-docs-container">
      <div class="ppid-docs-header">
        <div>
          <h1 class="ppid-docs-title">Kelola Dokumen PPID</h1>
          <p class="ppid-docs-sub">Unggah dan atur dokumen Informasi Publik, Prosedur, dan Layanan Informasi</p>
        </div>
        <Link :href="route('admin.ppid.documents.create')" class="ppid-add-btn">
          <Plus :size="16" /> Tambah Dokumen Baru
        </Link>
      </div>

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success" class="ppid-flash">
        <CheckCircle :size="16" /> {{ $page.props.flash.success }}
      </div>

      <!-- Filter bar -->
      <div class="ppid-filter-bar">
        <form @submit.prevent="applyFilters" class="ppid-search-form">
          <Search :size="15" />
          <input v-model="localSearch" type="text" placeholder="Cari judul dokumen..." />
        </form>
        <select v-model="localCategory" @change="applyFilters" class="ppid-select">
          <option value="">Semua Kategori</option>
          <option v-for="(label, key) in categoryLabels" :key="key" :value="key">{{ label }}</option>
        </select>
      </div>

      <!-- Table -->
      <div class="ppid-table-card">
        <table class="ppid-table">
          <thead>
            <tr>
              <th>Dokumen</th>
              <th>Kategori</th>
              <th>Subkategori</th>
              <th>Tahun</th>
              <th>Ukuran</th>
              <th>Unduhan</th>
              <th>Status</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!documents.data.length">
              <td colspan="8" class="text-center empty-td">Belum ada dokumen PPID.</td>
            </tr>
            <tr v-for="doc in documents.data" :key="doc.id">
              <td>
                <div class="doc-title-cell">
                  <span class="doc-title-text">{{ doc.title }}</span>
                  <span v-if="doc.description" class="doc-desc-text">{{ doc.description }}</span>
                </div>
              </td>
              <td><span class="cat-tag">{{ categoryLabels[doc.category] || doc.category }}</span></td>
              <td>{{ doc.subcategory || '-' }}</td>
              <td>{{ doc.year || '-' }}</td>
              <td>{{ doc.file_size_formatted }}</td>
              <td>{{ doc.download_count }}x</td>
              <td>
                <span class="status-badge" :class="doc.is_published ? 'pub' : 'draft'">
                  {{ doc.is_published ? 'Aktif' : 'Draft' }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-flex">
                  <Link :href="route('admin.ppid.documents.edit', doc.id)" class="action-btn edit" title="Edit">
                    <Pencil :size="14" />
                  </Link>
                  <button @click="confirmDelete(doc)" class="action-btn delete" title="Hapus">
                    <Trash2 :size="14" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="documents.last_page > 1" class="ppid-pagination">
        <Link
          v-for="link in documents.links"
          :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="page-btn"
          :class="{ active: link.active, disabled: !link.url }"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Plus, Search, Pencil, Trash2, CheckCircle } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  documents: Object,
  categoryLabels: Object,
  filters: Object,
})

const localSearch   = ref(props.filters?.search ?? '')
const localCategory = ref(props.filters?.category ?? '')

function applyFilters() {
  router.get(route('admin.ppid.documents.index'), {
    search: localSearch.value || undefined,
    category: localCategory.value || undefined,
  }, { preserveState: true, replace: true })
}

function confirmDelete(doc) {
  if (confirm(`Hapus dokumen "${doc.title}"?`)) {
    router.delete(route('admin.ppid.documents.destroy', doc.id))
  }
}
</script>

<style scoped>
.ppid-docs-container { padding: 24px; }
.ppid-docs-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.ppid-docs-title { font-size: 1.5rem; font-weight: 700; color: #0f4c81; }
.ppid-docs-sub { font-size: 0.875rem; color: #64748b; margin-top: 4px; }
.ppid-add-btn { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #0f4c81, #1565c0); color: white; border-radius: 10px; text-decoration: none; font-size: 0.875rem; font-weight: 700; }
.ppid-flash { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #86efac; color: #15803d; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.875rem; }

.ppid-filter-bar { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.ppid-search-form { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 220px; background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; }
.ppid-search-form input { border: none; outline: none; flex: 1; font-size: 0.875rem; }
.ppid-select { padding: 10px 14px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; font-size: 0.875rem; color: #374151; }

.ppid-table-card { background: white; border-radius: 14px; border: 1px solid #e2e8f0; overflow-x: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.ppid-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left; }
.ppid-table th { background: #f8fafc; color: #475569; font-weight: 600; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; }
.ppid-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; }
.doc-title-cell { display: flex; flex-direction: column; gap: 2px; }
.doc-title-text { font-weight: 600; color: #1e293b; }
.doc-desc-text { font-size: 0.78rem; color: #64748b; }
.cat-tag { font-size: 0.75rem; font-weight: 600; background: #f0f7ff; color: #0f4c81; padding: 2px 8px; border-radius: 6px; }
.status-badge { font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; }
.status-badge.pub { background: #dcfce7; color: #15803d; }
.status-badge.draft { background: #f1f5f9; color: #64748b; }
.actions-flex { display: flex; justify-content: flex-end; gap: 6px; }
.action-btn { width: 30px; height: 30px; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; border: none; cursor: pointer; }
.action-btn.edit { background: #f0f7ff; color: #0f4c81; }
.action-btn.delete { background: #fff1f2; color: #e11d48; }
.empty-td { padding: 40px; color: #94a3b8; }
.text-right { text-align: right; }
.text-center { text-align: center; }

.ppid-pagination { display: flex; gap: 6px; justify-content: center; margin-top: 24px; }
.page-btn { padding: 8px 14px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 0.8rem; text-decoration: none; color: #374151; }
.page-btn.active { background: #0f4c81; color: white; border-color: #0f4c81; }
.page-btn.disabled { opacity: 0.4; }
</style>
