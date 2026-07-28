<template>
  <AdminLayout title="PPID — Halaman Konten">
    <div class="ppid-pages-container">
      <div class="ppid-pages-header">
        <div>
          <h1 class="ppid-pages-title">Kelola Halaman PPID</h1>
          <p class="ppid-pages-sub">Edit konten halaman statis PPID (Pengertian, Profil, Tugas & Fungsi, Kontak, SK PPID)</p>
        </div>
        <a :href="route('public.ppid.pengertian')" target="_blank" class="ppid-view-btn">
          <ExternalLink :size="15" /> Lihat di Website
        </a>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="ppid-flash">
        <CheckCircle :size="16" /> {{ $page.props.flash.success }}
      </div>

      <!-- Pages grid -->
      <div class="ppid-cards-grid">
        <div v-for="page in pages" :key="page.id" class="ppid-page-card">
          <div class="ppid-page-card-top">
            <div class="ppid-page-icon">
              <FileText :size="22" />
            </div>
            <div class="ppid-page-info">
              <h3 class="ppid-page-title">{{ page.title }}</h3>
              <code class="ppid-page-slug">/ppid/{{ page.slug }}</code>
            </div>
            <span class="ppid-page-status" :class="page.is_published ? 'pub' : 'draft'">
              {{ page.is_published ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>
          <div class="ppid-page-preview" v-html="truncateHtml(page.content, 120)"></div>
          <div class="ppid-page-footer">
            <Link :href="route('admin.ppid.pages.edit', page.id)" class="ppid-edit-btn">
              <Pencil :size="14" /> Edit Konten
            </Link>
            <a :href="route('public.ppid.page', page.slug)" target="_blank" class="ppid-preview-link">
              <Eye :size="14" /> Preview
            </a>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { FileText, Pencil, Eye, ExternalLink, CheckCircle } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ pages: Array })

function truncateHtml(html, maxLen) {
  const text = html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
  return text.length > maxLen ? text.slice(0, maxLen) + '…' : text
}
</script>

<style scoped>
.ppid-pages-container { padding: 24px; }
.ppid-pages-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; gap: 16px; flex-wrap: wrap; }
.ppid-pages-title { font-size: 1.5rem; font-weight: 700; color: #0f4c81; }
.ppid-pages-sub { font-size: 0.875rem; color: #64748b; margin-top: 4px; }
.ppid-view-btn { display: flex; align-items: center; gap: 7px; padding: 10px 18px; background: #f0f7ff; color: #0f4c81; border: 1px solid #bfdbfe; border-radius: 10px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: background 0.2s; }
.ppid-view-btn:hover { background: #dbeafe; }
.ppid-flash { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #86efac; color: #15803d; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.875rem; }
.ppid-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
.ppid-page-card { background: white; border-radius: 14px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: box-shadow 0.2s; }
.ppid-page-card:hover { box-shadow: 0 6px 24px rgba(15,76,129,0.12); }
.ppid-page-card-top { display: flex; align-items: flex-start; gap: 12px; padding: 18px 18px 12px; }
.ppid-page-icon { width: 44px; height: 44px; background: linear-gradient(135deg, #0f4c81, #1565c0); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; }
.ppid-page-info { flex: 1; min-width: 0; }
.ppid-page-title { font-size: 0.975rem; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
.ppid-page-slug { font-size: 0.72rem; color: #64748b; background: #f8fafc; padding: 2px 6px; border-radius: 4px; }
.ppid-page-status { font-size: 0.7rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; flex-shrink: 0; }
.ppid-page-status.pub { background: #dcfce7; color: #15803d; }
.ppid-page-status.draft { background: #f1f5f9; color: #64748b; }
.ppid-page-preview { padding: 0 18px 12px; font-size: 0.82rem; color: #64748b; line-height: 1.5; min-height: 40px; border-bottom: 1px solid #f1f5f9; }
.ppid-page-footer { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; }
.ppid-edit-btn { display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: linear-gradient(135deg, #0f4c81, #1565c0); color: white; border-radius: 8px; text-decoration: none; font-size: 0.82rem; font-weight: 600; transition: opacity 0.2s; }
.ppid-edit-btn:hover { opacity: 0.88; }
.ppid-preview-link { display: flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; font-size: 0.8rem; transition: color 0.2s; }
.ppid-preview-link:hover { color: #0f4c81; }
</style>
