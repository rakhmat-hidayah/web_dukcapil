<template>
  <AdminLayout title="PPID — Permohonan Informasi">
    <div class="requests-container">
      <div class="requests-header">
        <div>
          <h1 class="requests-title">Permohonan Informasi PPID</h1>
          <p class="requests-sub">Kelola permohonan informasi publik yang masuk dari masyarakat</p>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="summary-cards">
        <div class="summary-card diterima">
          <span class="summary-val">{{ summary.diterima }}</span>
          <span class="summary-lbl">Diterima</span>
        </div>
        <div class="summary-card diproses">
          <span class="summary-val">{{ summary.diproses }}</span>
          <span class="summary-lbl">Diproses</span>
        </div>
        <div class="summary-card selesai">
          <span class="summary-val">{{ summary.selesai }}</span>
          <span class="summary-lbl">Selesai</span>
        </div>
        <div class="summary-card ditolak">
          <span class="summary-val">{{ summary.ditolak }}</span>
          <span class="summary-lbl">Ditolak</span>
        </div>
      </div>

      <!-- Filters -->
      <div class="filter-bar">
        <form @submit.prevent="applyFilters" class="search-form">
          <Search :size="15" />
          <input v-model="localSearch" type="text" placeholder="Cari nomor tiket, nama, email..." />
        </form>
        <select v-model="localStatus" @change="applyFilters" class="select-box">
          <option value="">Semua Status</option>
          <option v-for="(lbl, st) in statusLabels" :key="st" :value="st">{{ lbl }}</option>
        </select>
      </div>

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success" class="flash-msg">
        <CheckCircle :size="16" /> {{ $page.props.flash.success }}
      </div>

      <!-- Table -->
      <div class="table-card">
        <table class="req-table">
          <thead>
            <tr>
              <th>Nomor Tiket</th>
              <th>Pemohon</th>
              <th>Tujuan / Informasi</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!requests.data.length">
              <td colspan="6" class="text-center empty-td">Belum ada permohonan informasi.</td>
            </tr>
            <tr v-for="req in requests.data" :key="req.id">
              <td>
                <span class="ticket-code">{{ req.ticket_number }}</span>
              </td>
              <td>
                <div class="requester-cell">
                  <span class="requester-name">{{ req.requester_name }}</span>
                  <span class="requester-email">{{ req.requester_email }}</span>
                </div>
              </td>
              <td>
                <div class="info-cell">
                  <span class="info-purpose">Tujuan: {{ req.purpose }}</span>
                  <span class="info-req">{{ truncate(req.information_requested, 60) }}</span>
                </div>
              </td>
              <td>{{ formatDate(req.created_at) }}</td>
              <td>
                <span class="status-pill" :class="req.status">
                  {{ statusLabels[req.status] || req.status }}
                </span>
              </td>
              <td class="text-right">
                <div class="action-flex">
                  <Link :href="route('admin.ppid.requests.show', req.id)" class="detail-btn">
                    <Eye :size="14" /> Detail
                  </Link>
                  <button @click="confirmDelete(req)" class="delete-btn" title="Hapus Permohonan">
                    <Trash2 :size="14" /> Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="requests.last_page > 1" class="pagination">
        <Link
          v-for="link in requests.links"
          :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="page-btn"
          :class="{ active: link.active, disabled: !link.url }"
        />
      </div>

      <!-- Modal Konfirmasi Hapus Custom Vue -->
      <div v-if="deletingItem" class="modal-backdrop" @click.self="deletingItem = null">
        <div class="modal-card">
          <div class="modal-header">
            <div class="modal-icon danger">
              <Trash2 :size="22" />
            </div>
            <h3 class="modal-title">Hapus Permohonan PPID</h3>
          </div>
          <div class="modal-body">
            <p>
              Apakah Anda yakin ingin menghapus permohonan <strong>#{{ deletingItem.ticket_number }}</strong> atas nama <strong>{{ deletingItem.requester_name }}</strong>?
            </p>
            <p class="modal-subtext">Tindakan ini permanen dan data permohonan akan dihapus dari sistem.</p>
          </div>
          <div class="modal-footer">
            <button @click="deletingItem = null" class="btn-secondary" :disabled="isDeleting">Batal</button>
            <button @click="executeDelete" class="btn-danger" :disabled="isDeleting">
              {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Permohonan' }}
            </button>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Search, Eye, Trash2, CheckCircle } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  requests: Object,
  summary: Object,
  statusLabels: Object,
  statusColors: Object,
  filters: Object,
})

const localSearch = ref(props.filters?.search ?? '')
const localStatus = ref(props.filters?.status ?? '')

const deletingItem = ref(null)
const isDeleting = ref(false)

function confirmDelete(req) {
  deletingItem.value = req
}

function executeDelete() {
  if (!deletingItem.value) return
  isDeleting.value = true

  router.delete(route('admin.ppid.requests.destroy', deletingItem.value.id), {
    onSuccess: () => {
      deletingItem.value = null
      isDeleting.value = false
    },
    onError: () => {
      isDeleting.value = false
    },
    onFinish: () => {
      isDeleting.value = false
    }
  })
}

function applyFilters() {
  router.get(route('admin.ppid.requests.index'), {
    search: localSearch.value || undefined,
    status: localStatus.value || undefined,
  }, { preserveState: true, replace: true })
}

function truncate(str, len) {
  if (!str) return '-'
  return str.length > len ? str.slice(0, len) + '…' : str
}

function formatDate(dt) {
  if (!dt) return '-'
  return new Date(dt).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<style scoped>
.requests-container { padding: 24px; }
.requests-header { margin-bottom: 24px; }
.requests-title { font-size: 1.5rem; font-weight: 700; color: #0f4c81; }
.requests-sub { font-size: 0.875rem; color: #64748b; margin-top: 4px; }

.summary-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.summary-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px 20px; display: flex; flex-direction: column; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
.summary-val { font-size: 1.8rem; font-weight: 800; }
.summary-lbl { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: #64748b; }
.summary-card.diterima .summary-val { color: #2563eb; }
.summary-card.diproses .summary-val { color: #d97706; }
.summary-card.selesai .summary-val { color: #16a34a; }
.summary-card.ditolak .summary-val { color: #dc2626; }

.filter-bar { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.search-form { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 220px; background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; }
.search-form input { border: none; outline: none; flex: 1; font-size: 0.875rem; }
.select-box { padding: 10px 14px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; font-size: 0.875rem; color: #374151; }

.flash-msg { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #86efac; color: #15803d; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.875rem; }

.table-card { background: white; border-radius: 14px; border: 1px solid #e2e8f0; overflow-x: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.req-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left; }
.req-table th { background: #f8fafc; color: #475569; font-weight: 600; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; whitespace: nowrap; }
.req-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; whitespace: nowrap; }
.ticket-code { font-family: monospace; font-weight: 700; color: #0f4c81; background: #f0f7ff; padding: 2px 8px; border-radius: 6px; }
.requester-cell { display: flex; flex-direction: column; }
.requester-name { font-weight: 600; color: #1e293b; }
.requester-email { font-size: 0.78rem; color: #64748b; }
.info-cell { display: flex; flex-direction: column; }
.info-purpose { font-size: 0.78rem; font-weight: 600; color: #0f4c81; }
.info-req { font-size: 0.82rem; color: #475569; }
.status-pill { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; text-transform: uppercase; }
.status-pill.diterima { background: #dbeafe; color: #1d4ed8; }
.status-pill.diproses { background: #fef9c3; color: #b45309; }
.status-pill.selesai { background: #dcfce7; color: #15803d; }
.status-pill.ditolak { background: #fee2e2; color: #dc2626; }

.action-flex { display: inline-flex; align-items: center; justify-content: flex-end; gap: 8px; }
.detail-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: #f0f7ff; color: #0f4c81; border: 1px solid #bfdbfe; border-radius: 8px; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: background 0.2s; }
.detail-btn:hover { background: #dbeafe; }
.delete-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.delete-btn:hover { background: #fee2e2; }

.empty-td { padding: 40px; color: #94a3b8; }
.text-right { text-align: right; }
.text-center { text-align: center; }

.pagination { display: flex; gap: 6px; justify-content: center; margin-top: 24px; }
.page-btn { padding: 8px 14px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 0.8rem; text-decoration: none; color: #374151; }
.page-btn.active { background: #0f4c81; color: white; border-color: #0f4c81; }
.page-btn.disabled { opacity: 0.4; }

/* Custom Vue Modal Overlay */
.modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 999; padding: 16px; }
.modal-card { background: white; border-radius: 18px; max-width: 440px; width: 100%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); animation: popIn 0.2s ease-out; }
@keyframes popIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.modal-header { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.modal-icon.danger { width: 42px; height: 42px; border-radius: 12px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.modal-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0; }
.modal-body { font-size: 0.875rem; color: #334155; line-height: 1.5; margin-bottom: 20px; }
.modal-subtext { font-size: 0.78rem; color: #94a3b8; margin-top: 6px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
.btn-secondary { padding: 9px 18px; border-radius: 10px; border: 1px solid #cbd5e1; background: white; color: #334155; font-size: 0.85rem; font-weight: 600; cursor: pointer; }
.btn-secondary:hover { background: #f8fafc; }
.btn-danger { padding: 9px 18px; border-radius: 10px; border: none; background: #dc2626; color: white; font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s; }
.btn-danger:hover { opacity: 0.9; }
.btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }

@media (max-width: 768px) { 
  .summary-cards { grid-template-columns: 1fr 1fr; } 
  .requests-container { padding: 12px; }
  .req-table th { padding: 8px 10px; font-size: 0.75rem; }
  .req-table td { padding: 8px 10px; font-size: 0.75rem; }
}
</style>
