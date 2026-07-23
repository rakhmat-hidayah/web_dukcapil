<template>
  <AdminLayout :title="`Detail Permohonan #${ppidRequest.ticket_number}`">
    <div class="req-detail-container">
      <div class="req-detail-header">
        <Link :href="route('admin.ppid.requests.index')" class="back-link">
          <ArrowLeft :size="14" /> Kembali ke Daftar Permohonan
        </Link>
        <div class="header-flex">
          <h1 class="req-detail-title">Permohonan Informasi #{{ ppidRequest.ticket_number }}</h1>
          <span class="status-pill" :class="ppidRequest.status">{{ ppidRequest.status_label }}</span>
        </div>
      </div>

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success" class="flash-msg">
        <CheckCircle :size="16" /> {{ $page.props.flash.success }}
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
                <input type="file" @change="handleFileUpload" accept=".pdf,.doc,.docx" />
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
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, User, FileText, Send, CheckCircle } from '@lucide/vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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

@media (max-width: 900px) { .detail-grid { grid-template-columns: 1fr; } .response-card { position: static; } }
</style>
