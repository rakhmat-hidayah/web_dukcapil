<template>
  <Head :title="`Detail Pemohon - ${account.name}`" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Top Bar -->
      <div class="flex items-center justify-between">
        <Link
          :href="route('admin.queue.applicants.index')"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition"
        >
          <ArrowLeft class="w-4 h-4" />
          <span>Kembali ke Verifikasi Pendaftar Baru</span>
        </Link>
      </div>

      <!-- Account Overview Header -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-3">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Nama Pemohon / User
              </span>
              <span
                class="px-2.5 py-0.5 rounded-full text-[10px] font-black"
                :class="{
                  'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': account.account_status === 'ACTIVE',
                  'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': account.account_status === 'IDENTITY_PENDING' || account.account_status === 'EMAIL_PENDING',
                  'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300': account.account_status === 'SUSPENDED' || account.account_status === 'LOCKED',
                }"
              >
                {{ account.account_status_label || account.account_status }}
              </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ account.name }}</h1>
          </div>

          <div class="flex flex-wrap items-center gap-3 text-xs">
            <!-- NIK UTUH (16 DIGIT) -->
            <div class="inline-flex items-center gap-2 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 px-3.5 py-2 rounded-2xl border border-blue-200 dark:border-blue-900">
              <span class="font-sans font-bold text-slate-500 dark:text-slate-400 text-[11px]">NIK:</span>
              <strong class="font-mono font-black text-sm tracking-widest select-all text-blue-700 dark:text-blue-300">
                {{ account.nik || account.masked_nik }}
              </strong>
              <button
                v-if="account.nik"
                type="button"
                @click="copyText(account.nik, 'nik')"
                class="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 transition rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/60 cursor-pointer"
                :title="copiedField === 'nik' ? 'NIK berhasil disalin!' : 'Salin 16 Digit NIK Utuh'"
              >
                <Check v-if="copiedField === 'nik'" class="w-3.5 h-3.5 text-emerald-600" />
                <Copy v-else class="w-3.5 h-3.5" />
              </button>
            </div>

            <!-- Email Utuh -->
            <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 px-3.5 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-mono">
              <span class="font-sans font-bold text-slate-500 dark:text-slate-400 text-[11px]">Email:</span>
              <strong class="font-medium text-xs">{{ account.email || account.masked_email }}</strong>
            </div>

            <!-- Nomor Handphone Utuh -->
            <div class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 px-3.5 py-2 rounded-2xl border border-emerald-200 dark:border-emerald-800 font-mono">
              <span class="font-sans font-bold text-slate-500 dark:text-slate-400 text-[11px]">No. HP:</span>
              <strong class="font-bold text-xs">{{ account.formatted_phone || account.phone || '-' }}</strong>
              <button
                v-if="account.phone"
                type="button"
                @click="copyText(account.phone, 'phone')"
                class="p-1 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 transition rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/60 cursor-pointer ml-1"
                :title="copiedField === 'phone' ? 'No. HP berhasil disalin!' : 'Salin Nomor HP'"
              >
                <Check v-if="copiedField === 'phone'" class="w-3.5 h-3.5 text-emerald-600" />
                <Copy v-else class="w-3.5 h-3.5" />
              </button>
            </div>

            <!-- Status Identitas -->
            <div
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl border text-xs font-bold"
              :class="account.identity_status === 'VERIFIED'
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'"
            >
              <span class="font-sans font-bold text-slate-500 dark:text-slate-400 text-[11px]">Identitas:</span>
              <span class="inline-flex items-center gap-1">
                <CheckCircle2 v-if="account.identity_status === 'VERIFIED'" class="w-3.5 h-3.5 text-emerald-600" />
                <span>{{ account.identity_status_label || account.identity_status }}</span>
              </span>
            </div>
          </div>

          <div v-if="account.identity_verified_at" class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span>Diverifikasi pada: <strong class="text-slate-700 dark:text-slate-300">{{ account.identity_verified_at }}</strong></span>
            <span v-if="account.identity_verified_by">&bull; Petugas: <strong class="text-slate-700 dark:text-slate-300">{{ account.identity_verified_by }}</strong></span>
          </div>

          <p v-if="account.status_reason" class="text-xs text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 p-2.5 rounded-xl border border-rose-200 dark:border-rose-900">
            <strong>Catatan Status:</strong> {{ account.status_reason }}
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
          <!-- Tombol Verifikasi Identitas: Nonaktif jika sudah VERIFIED -->
          <button
            v-if="account.identity_status === 'VERIFIED'"
            type="button"
            disabled
            class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700/50 text-slate-400 dark:text-slate-500 font-bold text-xs border border-slate-200 dark:border-slate-700 cursor-not-allowed inline-flex items-center gap-1.5 opacity-80 shadow-none"
            title="Identitas pemohon sudah terverifikasi (Non-aktif)"
          >
            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
            <span>Sudah Terverifikasi</span>
          </button>
          <button
            v-else
            type="button"
            @click="openVerifyModal"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-xs cursor-pointer inline-flex items-center gap-1.5"
          >
            <UserCheck class="w-3.5 h-3.5" />
            <span>Verifikasi Identitas</span>
          </button>

          <button
            type="button"
            @click="openStatusModal"
            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition shadow-xs cursor-pointer"
          >
            Ubah Status Akun
          </button>
          <button
            type="button"
            @click="showDeleteModal = true"
            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5"
          >
            <Trash2 class="w-3.5 h-3.5" />
            <span>Hapus Akun</span>
          </button>
        </div>
      </div>

      <!-- Grid: Devices & Applications -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 1: Perangkat Terikat & Riwayat Perangkat -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs space-y-4">
          <div class="flex items-center justify-between border-b pb-3 border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
              <Smartphone class="w-4 h-4 text-purple-600" />
              <span>Status Perangkat (One Account One Browser)</span>
            </h2>
          </div>

          <div v-if="devices.length === 0" class="text-xs text-slate-400 italic py-4 text-center">
            Belum ada perangkat yang pernah diikat oleh pemohon ini.
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="d in devices"
              :key="d.id"
              class="p-4 rounded-2xl border text-xs space-y-2 transition"
              :class="d.is_active ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800 ring-1 ring-emerald-500/20' : 'bg-slate-50 dark:bg-slate-900/60 border-slate-200 dark:border-slate-700 opacity-70'"
            >
              <div class="flex items-center justify-between">
                <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full" :class="d.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                  <span>{{ d.device_label }}</span>
                </span>
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="d.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                >
                  {{ d.is_active ? 'PERANGKAT AKTIF' : 'DICABUT' }}
                </span>
              </div>

              <div class="text-[11px] text-slate-500 space-y-0.5">
                <p>Terdaftar: {{ d.registered_at }} &bull; Aktivitas Terakhir: {{ d.last_seen_at }}</p>
                <p v-if="d.ip_address">IP Terakhir: <code class="font-mono text-slate-600 dark:text-slate-300">{{ d.ip_address }}</code></p>
                <p v-if="d.revoked_at" class="text-rose-600 dark:text-rose-400 font-semibold">
                  Dicabut pada: {{ d.revoked_at }} (Alasan: {{ d.revocation_reason }})
                </p>
              </div>

              <!-- Button to revoke this active device -->
              <div v-if="d.is_active" class="pt-1 flex justify-end">
                <button
                  type="button"
                  @click="openRevokeDeviceModal(d)"
                  class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-[11px] transition shadow-xs cursor-pointer flex items-center gap-1"
                >
                  <ShieldAlert class="w-3.5 h-3.5" />
                  <span>Cabut Akses Perangkat Ini</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Permohonan Terkait -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs space-y-4">
          <div class="flex items-center justify-between border-b pb-3 border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
              <FileText class="w-4 h-4 text-blue-600" />
              <span>Permohonan Pelayanan Terkait</span>
            </h2>
            <span class="text-xs text-slate-500 font-bold">{{ applications.length }} Data</span>
          </div>

          <div v-if="applications.length === 0" class="text-xs text-slate-400 italic py-4 text-center">
            Belum ada permohonan antrian yang terhubung dengan akun ini.
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="a in applications"
              :key="a.id"
              class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs flex items-center justify-between gap-2"
            >
              <div>
                <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ a.reg_number }}</span>
                <p class="font-bold text-slate-800 dark:text-slate-200">{{ a.service_name }}</p>
                <p class="text-[10px] text-slate-400">{{ a.created_at }}</p>
              </div>
              <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                {{ a.status_label }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Audit Trail Log Table -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b pb-3 border-slate-100 dark:border-slate-700">
          <h2 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
            <History class="w-4 h-4 text-slate-600 dark:text-slate-400" />
            <span>Audit Log Keamanan Perangkat & Akun</span>
          </h2>
          <span class="text-xs text-slate-500 font-bold">{{ auditLogs.length }} Aktivitas</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs min-w-[620px]">
            <thead class="text-slate-400 border-b border-slate-100 dark:border-slate-700">
              <tr>
                <th class="py-2.5 pr-4 font-bold">Waktu</th>
                <th class="py-2.5 pr-4 font-bold">Aktivitas (Event)</th>
                <th class="py-2.5 pr-4 font-bold">Aktor</th>
                <th class="py-2.5 pr-4 font-bold">Keterangan / Alasan</th>
                <th class="py-2.5 font-bold">IP Address</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
              <tr v-if="auditLogs.length === 0">
                <td colspan="5" class="py-6 text-center text-slate-400 italic">
                  Belum ada catatan aktivitas keamanan untuk akun ini.
                </td>
              </tr>
              <tr v-for="log in auditLogs" :key="log.id" class="text-slate-600 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-900/40 transition">
                <td class="py-3 pr-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">{{ log.created_at }}</td>
                <td class="py-3 pr-4 whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1"
                    :class="getEventBadgeClass(log.event_type)"
                  >
                    {{ log.event_label || formatEventType(log.event_type) }}
                  </span>
                </td>
                <td class="py-3 pr-4 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                    {{ log.actor_label || formatActorType(log.actor_type) }}
                  </span>
                </td>
                <td class="py-3 pr-4 text-xs text-slate-700 dark:text-slate-300 max-w-xs break-words font-medium">
                  {{ log.reason || '-' }}
                </td>
                <td class="py-3 font-mono text-[11px] text-slate-400 whitespace-nowrap">{{ log.ip_address || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL: Cabut Perangkat -->
    <Teleport to="body">
      <div v-if="showRevokeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 space-y-4 border border-slate-200 dark:border-slate-800 shadow-2xl">
          <h3 class="text-base font-black text-slate-900 dark:text-white">Cabut Akses Perangkat Aktif</h3>
          <p class="text-xs text-slate-500 leading-relaxed">
            Perangkat pemohon akan langsung kehilangan akses dan cookie perangkat akan dibatalkan di server.
          </p>

          <form @submit.prevent="submitRevokeDevice" class="space-y-4">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Alasan Pencabutan (Wajib diisi untuk audit trail)
              </label>
              <textarea
                v-model="revokeForm.reason"
                rows="3"
                placeholder="Contoh: Permintaan pemohon ganti HP / dugaan penyalahgunaan..."
                class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-rose-500"
                required
              ></textarea>
              <p v-if="revokeForm.errors.reason" class="text-xs text-rose-500 font-semibold">{{ revokeForm.errors.reason }}</p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button
                type="button"
                @click="showRevokeModal = false"
                class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="revokeForm.processing"
                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition shadow-xs"
              >
                Cabut Perangkat Sekarang
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- MODAL: Ubah Status Akun -->
    <Teleport to="body">
      <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 space-y-4 border border-slate-200 dark:border-slate-800 shadow-2xl">
          <h3 class="text-base font-black text-slate-900 dark:text-white">Ubah Status Akun Pemohon</h3>

          <form @submit.prevent="submitStatusChange" class="space-y-4">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Pilih Status Baru</label>
              <select
                v-model="statusForm.account_status"
                class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
              >
                <option value="ACTIVE">ACTIVE (Aktif & Berhak Mengakses)</option>
                <option value="IDENTITY_PENDING">IDENTITY_PENDING (Menunggu Verifikasi Identitas)</option>
                <option value="SUSPENDED">SUSPENDED (Tangguhkan / Blokir Sementara)</option>
                <option value="LOCKED">LOCKED (Kunci Akun)</option>
              </select>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Alasan Perubahan Status</label>
              <textarea
                v-model="statusForm.reason"
                rows="3"
                placeholder="Catat dasar keputusan perubahan status..."
                class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
                required
              ></textarea>
              <p v-if="statusForm.errors.reason" class="text-xs text-rose-500 font-semibold">{{ statusForm.errors.reason }}</p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button
                type="button"
                @click="showStatusModal = false"
                class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="statusForm.processing"
                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-xs"
              >
                Simpan Status
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- MODAL: Verifikasi Identitas -->
    <Teleport to="body">
      <div v-if="showVerifyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 space-y-4 border border-slate-200 dark:border-slate-800 shadow-2xl">
          <h3 class="text-base font-black text-slate-900 dark:text-white">Verifikasi Identitas Pemohon</h3>
          <p class="text-xs text-slate-500 leading-relaxed">
            Menyetujui verifikasi akan mengubah status identitas menjadi <strong>VERIFIED</strong> dan mengaktifkan akun menjadi <strong>ACTIVE</strong> untuk pengajuan layanan online.
          </p>

          <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 space-y-1 text-xs">
            <p class="text-slate-500 font-bold text-[11px]">Nama User / Pemohon:</p>
            <p class="font-black text-slate-900 dark:text-white text-sm">{{ account.name }}</p>
            <p class="text-slate-500 font-bold text-[11px] pt-1">NIK Utuh (16 Digit):</p>
            <p class="font-mono font-black text-blue-600 dark:text-blue-400 text-sm tracking-widest select-all">{{ account.nik || account.masked_nik }}</p>
          </div>

          <form @submit.prevent="submitVerifyIdentity" class="space-y-4">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Keputusan</label>
              <select
                v-model="verifyForm.decision"
                @change="handleDecisionChange"
                class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="VERIFIED">TERIMA (VERIFIED) - Sah & Aktifkan Pengajuan</option>
                <option value="REJECTED">TOLAK (REJECTED) - Data Tidak Sesuai</option>
              </select>
            </div>

            <!-- Template Kalimat Penolakan (Dropdown) -->
            <div 
              v-if="verifyForm.decision === 'REJECTED'" 
              class="p-3.5 rounded-2xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 space-y-2 animate-in fade-in duration-150"
            >
              <div class="flex items-center justify-between">
                <label class="block text-xs font-black text-rose-900 dark:text-rose-200">
                  Pilih Template Alasan Penolakan:
                </label>
                <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400">Otomatis Isi</span>
              </div>
              <select
                v-model="selectedRejectTemplate"
                @change="applyRejectTemplate"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-rose-300 dark:border-rose-800 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-rose-500 font-medium cursor-pointer"
              >
                <option value="">-- Pilih Kalimat Penolakan Cepat --</option>
                <option v-for="(tpl, idx) in rejectionTemplates" :key="idx" :value="tpl">
                  {{ tpl }}
                </option>
              </select>
              <p class="text-[10.5px] text-rose-700/80 dark:text-rose-300/80 leading-tight">
                Memilih salah satu template di atas akan langsung mengisi kolom Catatan di bawah ini. Anda tetap dapat mengedit teksnya sesuai kebutuhan.
              </p>
            </div>

            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                  Catatan Petugas {{ verifyForm.decision === 'REJECTED' ? '(Alasan Penolakan)' : '(Opsional)' }}
                </label>
                <span v-if="verifyForm.decision === 'REJECTED'" class="text-[10px] font-bold text-rose-600 dark:text-rose-400">
                  *Dikirim ke email pemohon
                </span>
              </div>
              <textarea
                v-model="verifyForm.notes"
                rows="3"
                :placeholder="verifyForm.decision === 'REJECTED' ? 'Pilih template di atas atau ketik alasan penolakan di sini...' : 'Contoh: Sesuai dokumen fisik / SIAK terkonfirmasi...'"
                class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button
                type="button"
                @click="showVerifyModal = false"
                class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="verifyForm.processing"
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-xs"
              >
                Simpan Keputusan
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- MODAL: Hapus Akun Pemohon -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm" @click.self="showDeleteModal = false">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 space-y-4 border border-rose-200 dark:border-rose-900 shadow-2xl">
          <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto shadow-inner">
            <Trash2 class="w-6 h-6" />
          </div>

          <div class="text-center space-y-1">
            <h3 class="text-base font-black text-slate-900 dark:text-white">Hapus Akun Pemohon</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
              Apakah Anda yakin ingin menghapus akun pemohon <strong>{{ account.name }}</strong> (NIK: <span class="font-mono text-blue-600 font-bold">{{ account.nik || account.masked_nik }}</span>)?
            </p>
          </div>

          <div class="p-3 rounded-2xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 text-[11px] text-rose-800 dark:text-rose-300 space-y-1">
            <p class="font-bold">⚠️ Perhatian Tindakan Permanen:</p>
            <ul class="list-disc pl-4 space-y-0.5">
              <li>Sesi login dan pengikatan browser perangkat pemohon akan langsung dicabut.</li>
              <li>NIK dan alamat email akan dibebaskan kembali sehingga pemohon dapat mendaftar ulang jika diperlukan.</li>
              <li>Riwayat berkas permohonan yang telah diajukan tetap tersimpan di arsip antrian.</li>
            </ul>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="showDeleteModal = false"
              :disabled="deleteForm.processing"
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition"
            >
              Batal
            </button>
            <button
              type="button"
              @click="submitDeleteAccount"
              :disabled="deleteForm.processing"
              class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Trash2 class="w-3.5 h-3.5" />
              <span v-if="deleteForm.processing">Menghapus Akun...</span>
              <span v-else>Ya, Hapus Akun Ini</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  ArrowLeft,
  Smartphone,
  ShieldAlert,
  FileText,
  History,
  Trash2,
  UserCheck,
  CheckCircle2,
  Copy,
  Check,
} from '@lucide/vue'

const props = defineProps({
  account: Object,
  devices: Array,
  auditLogs: Array,
  applications: Array,
})

const showRevokeModal = ref(false)
const showStatusModal = ref(false)
const showVerifyModal = ref(false)
const showDeleteModal = ref(false)
const copiedField = ref(null)

const formatEventType = (event) => {
  const map = {
    registered: 'Pendaftaran Akun',
    email_verified: 'Email Berhasil Diverifikasi',
    identity_verification_verified: 'Identitas Diverifikasi (Sah)',
    identity_verification_rejected: 'Identitas Ditolak',
    account_status_changed: 'Status Akun Diubah',
    device_bound: 'Perangkat Baru Terikat',
    device_revoked: 'Akses Perangkat Dicabut',
    device_recovered: 'Perangkat Berhasil Dipulihkan',
    login_success_existing_device: 'Login Berhasil (Perangkat Terdaftar)',
    login_rejected_device_conflict: 'Login Ditolak (Konflik Perangkat)',
    login_failed_bad_password: 'Gagal Login (Password Salah)',
    otp_generated: 'Kode OTP Dikirim',
    otp_verified: 'OTP Berhasil Diverifikasi',
  }
  return map[event] || (event ? event.replace(/_/g, ' ') : '-')
}

const getEventBadgeClass = (event) => {
  if (['registered', 'email_verified', 'identity_verification_verified', 'login_success_existing_device', 'device_bound', 'device_recovered', 'otp_verified'].includes(event)) {
    return 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
  }
  if (['identity_verification_rejected', 'login_rejected_device_conflict', 'login_failed_bad_password', 'device_revoked'].includes(event)) {
    return 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'
  }
  return 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800'
}

const formatActorType = (actor) => {
  const map = {
    applicant: 'Pemohon',
    admin: 'Petugas / Admin',
    system: 'Sistem',
  }
  return map[actor] || actor || 'Sistem'
}

const copyText = async (text, field) => {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
    copiedField.value = field
    setTimeout(() => {
      copiedField.value = null
    }, 2000)
  } catch (e) {
    const el = document.createElement('textarea')
    el.value = text
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    copiedField.value = field
    setTimeout(() => {
      copiedField.value = null
    }, 2000)
  }
}

const deleteForm = useForm({})

const revokeForm = useForm({
  reason: '',
})

const statusForm = useForm({
  account_status: props.account.account_status,
  reason: '',
})

const verifyForm = useForm({
  decision: 'VERIFIED',
  notes: '',
})

// Rejection reason templates
const rejectionTemplates = [
  'Nama tidak sesuai dengan NIK pada KTP/KK, Perbaiki Penginputan Ejaan Nama, Harus Persis Nama yg tertera pada KTP/KK',
  'Nama tidak sesuai dengan NIK pada KTP/KK',
  'Perbaiki Penginputan Ejaan Nama, Harus Persis Nama yg tertera pada KTP/KK',
  'NIK tidak ditemukan dalam database kependudukan SIAK Disdukcapil',
  'Format NIK tidak valid atau tidak sesuai dengan data identitas pemohon',
  'Terdapat kesalahan penulisan gelar/singkatan yang tidak sesuai dengan dokumen resmi',
  'Identitas kependudukan terindikasi ganda atau sudah terdaftar di sistem',
]

const selectedRejectTemplate = ref('')

const applyRejectTemplate = () => {
  if (selectedRejectTemplate.value) {
    verifyForm.notes = selectedRejectTemplate.value
  }
}

const handleDecisionChange = () => {
  if (verifyForm.decision === 'REJECTED') {
    if (!verifyForm.notes && rejectionTemplates.length > 0) {
      selectedRejectTemplate.value = rejectionTemplates[0]
      verifyForm.notes = rejectionTemplates[0]
    }
  } else {
    selectedRejectTemplate.value = ''
    if (rejectionTemplates.includes(verifyForm.notes)) {
      verifyForm.notes = ''
    }
  }
}

const openRevokeDeviceModal = (device) => {
  revokeForm.reset()
  showRevokeModal.value = true
}

const openStatusModal = () => {
  statusForm.reset()
  statusForm.account_status = props.account.account_status
  showStatusModal.value = true
}

const openVerifyModal = () => {
  if (props.account.identity_status === 'VERIFIED') return
  verifyForm.reset()
  verifyForm.decision = 'VERIFIED'
  selectedRejectTemplate.value = ''
  showVerifyModal.value = true
}

const submitRevokeDevice = () => {
  revokeForm.post(route('admin.queue.applicants.revoke-device', props.account.id), {
    onSuccess: () => {
      showRevokeModal.value = false
    },
  })
}

const submitStatusChange = () => {
  statusForm.post(route('admin.queue.applicants.update-status', props.account.id), {
    onSuccess: () => {
      showStatusModal.value = false
    },
  })
}

const submitVerifyIdentity = () => {
  verifyForm.post(route('admin.queue.applicants.verify-identity', props.account.id), {
    onSuccess: () => {
      showVerifyModal.value = false
    },
  })
}

const submitDeleteAccount = () => {
  deleteForm.delete(route('admin.queue.applicants.destroy', props.account.id), {
    onSuccess: () => {
      showDeleteModal.value = false
    },
  })
}
</script>
