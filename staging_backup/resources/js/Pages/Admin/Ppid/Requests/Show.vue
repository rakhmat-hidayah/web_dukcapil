<template>
  <AdminLayout :title="`Detail Permohonan #${ppidRequest.ticket_number}`">
    <div class="req-detail-container">
      <div class="req-detail-header">
        <Link :href="route('admin.ppid.requests.index')" class="back-link">
          <ArrowLeft :size="14" /> Kembali ke Daftar Permohonan
        </Link>
        <div class="header-flex">
          <h1 class="req-detail-title">Permohonan Informasi #{{ ppidRequest.ticket_number }}</h1>
          <div class="flex items-center gap-3">
            <span class="status-pill" :class="ppidRequest.status">{{ ppidRequest.status_label }}</span>
            <button @click="showDeleteModal = true" class="delete-header-btn">
              <Trash2 :size="14" /> Hapus Permohonan
            </button>
          </div>
        </div>
      </div>

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success" class="flash-msg">
        <CheckCircle :size="16" /> {{ $page.props.flash.success }}
      </div>

      <!-- Custom Vue Modal Overlay Hapus -->
      <div v-if="showDeleteModal" class="modal-backdrop" @click.self="showDeleteModal = false">
        <div class="modal-card">
          <div class="modal-header">
            <div class="modal-icon danger">
              <Trash2 :size="22" />
            </div>
            <h3 class="modal-title">Hapus Permohonan PPID</h3>
          </div>
          <div class="modal-body">
            <p>
              Apakah Anda yakin ingin menghapus permohonan <strong>#{{ ppidRequest.ticket_number }}</strong> atas nama <strong>{{ ppidRequest.requester_name }}</strong>?
            </p>
            <p class="modal-subtext">Tindakan ini permanen dan data permohonan akan dihapus dari sistem.</p>
          </div>
          <div class="modal-footer">
            <button @click="showDeleteModal = false" class="btn-secondary" :disabled="isDeleting">Batal</button>
            <button @click="executeDelete" class="btn-danger" :disabled="isDeleting">
              {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Permohonan' }}
            </button>
          </div>
        </div>
      </div>

      <div class="detail-grid">
        <!-- Main details -->
        <div class="detail-left">
          <div class="card">
            <h3 class="card-title"><User :size="16" /> Data Pemohon</h3>
            <div class="info-grid-2">
              <div><span class="lbl">Nama Pemohon:</span><strong>{{ ppidRequest.requester_name }}</strong></div>
              <div><span class="lbl">Email:</span><strong>{{ ppidRequest.requester_email }}</strong></div>
              <div><span class="lbl">No. HP:</span><strong>{{ ppidRequest.requester_phone || '-' }}</strong></div>
              <div><span class="lbl">No. NIK (KTP):</span><strong>{{ ppidRequest.requester_id_number || '-' }}</strong></div>
              <div class="col-span-2"><span class="lbl">Alamat:</span><strong>{{ ppidRequest.requester_address || '-' }}</strong></div>
            </div>
          </div>

          <div class="card">
            <h3 class="card-title"><FileText :size="16" /> Rincian Permohonan</h3>
            <div class="req-box">
              <div class="lbl">Tujuan Penggunaan Informasi:</div>
              <p class="purpose-text">{{ ppidRequest.purpose }}</p>

              <div class="lbl">Informasi yang Diminta:</div>
              <p class="desc-text">{{ ppidRequest.information_requested }}</p>
            </div>
            <div class="info-grid-2 meta-row">
              <div><span class="lbl">Cara Pengajuan:</span><strong class="capitalize">{{ ppidRequest.request_method }}</strong></div>
              <div><span class="lbl">Cara Penerimaan:</span><strong class="capitalize">{{ ppidRequest.delivery_method }}</strong></div>
              <div><span class="lbl">Tanggal Pengajuan:</span><strong>{{ formatDate(ppidRequest.created_at) }}</strong></div>
            </div>
          </div>
        </div>

        <!-- Right / Response Form -->
        <div class="detail-right">
          <div class="card response-card">
            <h3 class="card-title"><Send :size="16" /> Berikan Respons / Update Status</h3>
            <form @submit.prevent="submitResponse" class="response-form">
              <div class="field">
                <label>Status Permohonan <span class="req">*</span></label>
                <select v-model="form.status">
                  <option v-for="(lbl, st) in statusLabels" :key="st" :value="st">{{ lbl }}</option>
                </select>
              </div>

              <div class="field">
                <label>Catatan / Tanggapan untuk Pemohon</label>
                <textarea v-model="form.response_notes" rows="6" placeholder="Tuliskan respons atau penjelasan untuk pemohon..."></textarea>
              </div>

              <div class="field">
                <label>Lampirkan File Balasan (PDF/Word)</label>
                <div
                  @click="$refs.responseFileInput.click()"
                  class="border-2 border-dashed border-gray-200 hover:border-blue-400 rounded-xl p-3 text-center cursor-pointer bg-gray-50/60 hover:bg-blue-50/30 transition group"
                >
                  <input
                    type="file"
                    ref="responseFileInput"
                    @change="handleFileUpload"
                    accept=".pdf,.doc,.docx"
                    class="hidden"
                  />
                  <div v-if="form.response_file" class="flex items-center justify-between gap-2 text-left">
                    <div class="flex items-center gap-2 min-w-0">
                      <UploadCloud class="w-4 h-4 text-blue-600 shrink-0" />
                      <span class="text-xs font-bold text-gray-800 truncate">{{ form.response_file.name }}</span>
                    </div>
                    <span class="text-[10px] text-gray-400 font-mono shrink-0">{{ formatBytes(form.response_file.size) }}</span>
                  </div>
                  <div v-else class="py-1 flex flex-col items-center justify-center">
                    <UploadCloud class="w-5 h-5 text-gray-400 group-hover:text-blue-600 mb-1 transition" />
                    <span class="text-xs font-bold text-gray-600">
                      <span class="text-blue-600 hover:underline">Klik untuk melampirkan dokumen balasan</span>
                    </span>
                    <span class="text-[10px] text-gray-400 mt-0.5">Format: PDF, Word (Max 20MB)</span>
                  </div>
                </div>
              </div>

              <button type="submit" class="submit-btn" :disabled="form.processing">
                <Send :size="16" /> {{ form.processing ? 'Menyimpan...' : 'Kirim Respons & Update Status' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, User, FileText, Send, CheckCircle, UploadCloud, Trash2 } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const responseFileInput = ref(null)

const showDeleteModal = ref(false)
const isDeleting = ref(false)

function executeDelete() {
  isDeleting.value = true
  router.delete(route('admin.ppid.requests.destroy', props.ppidRequest.id), {
    onFinish: () => {
      isDeleting.value = false
    }
  })
}

function formatBytes(bytes) {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

const props = defineProps({
  ppidRequest: Object,
  statusLabels: Object,
  statusColors: Object,
})

const form = useForm({
  status: props.ppidRequest.status,
  response_notes: props.ppidRequest.response_notes ?? '',
  response_file: null,
})

function handleFileUpload(e) {
  form.response_file = e.target.files[0]
}

function submitResponse() {
  form.post(route('admin.ppid.requests.respond', props.ppidRequest.id))
}

function formatDate(dt) {
  if (!dt) return '-'
  return new Date(dt).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.req-detail-container { padding: 24px; }
.back-link { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #64748b; text-decoration: none; margin-bottom: 8px; }
.header-flex { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.req-detail-title { font-size: 1.4rem; font-weight: 700; color: #0f4c81; }
.status-pill { padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
.status-pill.diterima { background: #dbeafe; color: #1d4ed8; }
.status-pill.diproses { background: #fef9c3; color: #b45309; }
.status-pill.selesai { background: #dcfce7; color: #15803d; }
.status-pill.ditolak { background: #fee2e2; color: #dc2626; }

.delete-header-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 10px; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: background 0.2s; }
.delete-header-btn:hover { background: #fee2e2; }

.flash-msg { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #86efac; color: #15803d; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.875rem; }

.detail-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; }
.detail-left { display: flex; flex-direction: column; gap: 20px; }
.card { background: white; border-radius: 14px; border: 1px solid #e2e8f0; padding: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.card-title { display: flex; align-items: center; gap: 8px; font-size: 1rem; font-weight: 700; color: #0f4c81; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; }
.info-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; font-size: 0.875rem; }
.col-span-2 { grid-column: span 2; }
.lbl { font-size: 0.78rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; display: block; margin-bottom: 2px; }
.purpose-text { font-weight: 600; color: #0f4c81; margin-bottom: 12px; }
.desc-text { font-size: 0.9rem; color: #334155; line-height: 1.6; white-space: pre-wrap; background: #f8fafc; padding: 12px 16px; border-radius: 8px; margin-bottom: 14px; }
.meta-row { border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 10px; }
.capitalize { text-transform: capitalize; }

.response-card { position: sticky; top: 24px; }
.response-form { display: flex; flex-direction: column; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 0.85rem; font-weight: 600; color: #374151; }
.req { color: #dc2626; }
.field select, .field textarea, .field input { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 0.9rem; outline: none; }
.submit-btn { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #0f4c81, #1565c0); color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 0.875rem; cursor: pointer; }

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

@media (max-width: 900px) { .detail-grid { grid-template-columns: 1fr; } .response-card { position: static; } }
</style>
