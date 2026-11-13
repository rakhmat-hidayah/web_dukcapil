<template>
  <Head title="Verifikasi Pendaftar Baru & Akun Pemohon - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Verifikasi Pendaftar Baru & Akun Pemohon
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Pemeriksaan dan verifikasi identitas pendaftar baru KTP-el, validasi NIK, pemantauan perangkat aktif (One Account One Browser), serta pelepasan sesi.
          </p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div 
          @click="filterByTab('', '')"
          class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs cursor-pointer hover:border-blue-400 transition"
          :class="{ 'ring-2 ring-blue-500': !accountStatus && !identityStatus }"
        >
          <p class="text-xs text-slate-500 font-bold">Total Pendaftar</p>
          <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ stats.total }}</p>
        </div>

        <div 
          @click="filterByTab('', 'PENDING')"
          class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 shadow-xs cursor-pointer hover:border-amber-400 transition"
          :class="{ 'ring-2 ring-amber-500': identityStatus === 'PENDING' }"
        >
          <div class="flex items-center justify-between">
            <p class="text-xs text-amber-700 dark:text-amber-300 font-bold">Menunggu Verifikasi</p>
            <span v-if="stats.identity_pending > 0" class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
          </div>
          <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ stats.identity_pending }}</p>
        </div>

        <div 
          @click="filterByTab('ACTIVE', 'VERIFIED')"
          class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs cursor-pointer hover:border-emerald-400 transition"
          :class="{ 'ring-2 ring-emerald-500': accountStatus === 'ACTIVE' && identityStatus === 'VERIFIED' }"
        >
          <p class="text-xs text-emerald-600 font-bold">Terverifikasi & Aktif</p>
          <p class="text-2xl font-black text-emerald-600 mt-1">{{ stats.active }}</p>
        </div>

        <div 
          @click="filterByTab('SUSPENDED', '')"
          class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs cursor-pointer hover:border-rose-400 transition"
          :class="{ 'ring-2 ring-rose-500': accountStatus === 'SUSPENDED' }"
        >
          <p class="text-xs text-rose-600 font-bold">Ditangguhkan</p>
          <p class="text-2xl font-black text-rose-600 mt-1">{{ stats.suspended }}</p>
        </div>
      </div>

      <!-- Quick Filter Pills & Search -->
      <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs space-y-3">
        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            @click="filterByTab('', '')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="!accountStatus && !identityStatus ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          >
            Semua Pendaftar ({{ stats.total }})
          </button>
          <button
            type="button"
            @click="filterByTab('', 'PENDING')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
            :class="identityStatus === 'PENDING' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-100/70 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 hover:bg-amber-200'"
          >
            <span v-if="stats.identity_pending > 0" class="w-2 h-2 rounded-full bg-amber-400"></span>
            <span>Menunggu Verifikasi ({{ stats.identity_pending }})</span>
          </button>
          <button
            type="button"
            @click="filterByTab('ACTIVE', 'VERIFIED')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="accountStatus === 'ACTIVE' && identityStatus === 'VERIFIED' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          >
            Terverifikasi Aktif ({{ stats.active }})
          </button>
          <button
            type="button"
            @click="filterByTab('SUSPENDED', '')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="accountStatus === 'SUSPENDED' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          >
            Ditangguhkan ({{ stats.suspended }})
          </button>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-1">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Cari nama pemohon, email, atau 16 digit NIK..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <select
            v-model="accountStatus"
            @change="applyFilters"
            class="px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
          >
            <option value="">Semua Status Akun</option>
            <option value="ACTIVE">ACTIVE</option>
            <option value="IDENTITY_PENDING">IDENTITY_PENDING</option>
            <option value="EMAIL_PENDING">EMAIL_PENDING</option>
            <option value="SUSPENDED">SUSPENDED</option>
            <option value="LOCKED">LOCKED</option>
          </select>

          <select
            v-model="identityStatus"
            @change="applyFilters"
            class="px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none"
          >
            <option value="">Semua Status Identitas</option>
            <option value="VERIFIED">VERIFIED</option>
            <option value="PENDING">PENDING</option>
            <option value="REJECTED">REJECTED</option>
          </select>

          <button
            type="button"
            @click="applyFilters"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition shadow-xs"
          >
            Cari
          </button>
        </div>
      </div>

      <!-- Accounts Table -->
      <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="p-4 font-bold">Foto & Pemohon (Nama & NIK)</th>
                <th class="p-4 font-bold">Kontak (Email & No. HP)</th>
                <th class="p-4 font-bold">Status Akun</th>
                <th class="p-4 font-bold">Status Identitas</th>
                <th class="p-4 font-bold">Perangkat Aktif (1 Browser)</th>
                <th class="p-4 font-bold">Terdaftar</th>
                <th class="p-4 font-bold text-right">Aksi Verifikasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
              <tr v-if="accounts.data.length === 0">
                <td colspan="7" class="p-8 text-center text-slate-400">
                  Tidak ada data pendaftar pemohon yang sesuai kriteria pencarian.
                </td>
              </tr>
              <tr v-for="acc in accounts.data" :key="acc.id" class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition">
                <td class="p-4">
                  <div class="flex items-start gap-3">
                    <!-- Selfie Thumbnail / Privacy Status -->
                    <div class="shrink-0 pt-0.5">
                      <div
                        v-if="acc.has_selfie && acc.selfie_url"
                        class="relative group cursor-pointer"
                        @click="openSelfiePreviewModal(acc)"
                        title="Klik untuk melihat foto selfie pendaftar"
                      >
                        <div class="w-12 h-12 rounded-2xl overflow-hidden bg-slate-900 border-2 border-slate-200 dark:border-slate-700 shadow-xs relative">
                          <img
                            :src="acc.selfie_url"
                            :alt="acc.name"
                            class="w-full h-full object-cover transition duration-300 filter blur-xs group-hover:blur-none"
                            loading="lazy"
                          />
                          <div class="absolute inset-0 bg-slate-950/20 group-hover:bg-transparent transition flex items-center justify-center">
                            <Eye class="w-3.5 h-3.5 text-white/90 drop-shadow-md group-hover:opacity-0 transition" />
                          </div>
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-xs" title="Foto Selfie Tersedia">
                          <Camera class="w-2.5 h-2.5" />
                        </span>
                      </div>

                      <!-- Badge if deleted / destroyed pasca-verifikasi -->
                      <div
                        v-else-if="acc.is_selfie_destroyed"
                        class="w-12 h-12 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 flex flex-col items-center justify-center text-center p-1"
                        :title="acc.selfie_deleted_at ? `Foto selfie telah dimusnahkan secara aman pada ${acc.selfie_deleted_at}` : 'Foto biometrik telah dimusnahkan pasca verifikasi (UU PDP)'"
                      >
                        <ShieldCheck class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <span class="text-[7px] font-black text-emerald-700 dark:text-emerald-300 leading-none mt-1">DIMUSNAHKAN</span>
                      </div>

                      <!-- Placeholder if no selfie -->
                      <div
                        v-else
                        class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400"
                        title="Tanpa foto selfie"
                      >
                        <User class="w-5 h-5 opacity-50" />
                      </div>
                    </div>

                    <!-- Name and NIK -->
                    <div class="min-w-0 flex-1">
                      <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Nama User</p>
                      <p class="font-black text-slate-900 dark:text-white text-sm truncate">{{ acc.name }}</p>
                      <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[10px] font-bold text-slate-400">NIK:</span>
                        <span class="font-mono text-xs text-blue-600 dark:text-blue-400 font-bold tracking-wider select-all">{{ acc.nik || acc.masked_nik }}</span>
                      </div>
                      <div v-if="acc.has_selfie" class="mt-1">
                        <button
                          type="button"
                          @click="openSelfiePreviewModal(acc)"
                          class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                        >
                          <Eye class="w-3 h-3" />
                          <span>Lihat Foto Selfie</span>
                        </button>
                      </div>
                      <div v-else-if="acc.is_selfie_destroyed" class="mt-0.5">
                        <span class="inline-flex items-center gap-1 text-[9px] font-semibold text-emerald-600 dark:text-emerald-400">
                          <span>✓ Berkas Dimusnahkan (UU PDP)</span>
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="p-4">
                  <span class="font-mono text-slate-700 dark:text-slate-300 font-medium block">{{ acc.email || acc.masked_email }}</span>
                  <div v-if="acc.formatted_phone || acc.phone" class="flex items-center gap-1.5 mt-1 text-[11px] font-mono text-emerald-600 dark:text-emerald-400 font-semibold">
                    <span class="text-xs">📱</span>
                    <span>{{ acc.formatted_phone || acc.phone }}</span>
                  </div>
                </td>
                <td class="p-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-black"
                    :class="{
                      'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': acc.account_status === 'ACTIVE',
                      'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': acc.account_status === 'IDENTITY_PENDING' || acc.account_status === 'EMAIL_PENDING',
                      'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300': acc.account_status === 'SUSPENDED' || acc.account_status === 'LOCKED',
                    }"
                  >
                    {{ acc.account_status_label || acc.account_status }}
                  </span>
                </td>
                <td class="p-4">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-black"
                    :class="{
                      'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': acc.identity_status === 'VERIFIED',
                      'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': acc.identity_status === 'PENDING',
                      'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300': acc.identity_status === 'REJECTED',
                    }"
                  >
                    {{ acc.identity_status_label || acc.identity_status }}
                  </span>
                </td>
                <td class="p-4">
                  <div v-if="acc.active_device_label" class="space-y-0.5">
                    <p class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                      <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                      <span>{{ acc.active_device_label }}</span>
                    </p>
                    <p class="text-[10px] text-slate-400">Aktif: {{ acc.active_device_seen }}</p>
                  </div>
                  <span v-else class="text-slate-400 text-[11px] italic">Belum terikat browser</span>
                </td>
                <td class="p-4 text-slate-500 whitespace-nowrap">
                  {{ acc.created_at }}
                </td>
                <td class="p-4 text-right">
                  <div class="inline-flex items-center gap-2">
                    <!-- Quick Verify button for PENDING applicants -->
                    <button
                      v-if="acc.identity_status === 'PENDING'"
                      type="button"
                      @click="openQuickVerifyModal(acc)"
                      class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-xs flex items-center gap-1 cursor-pointer"
                    >
                      <UserCheck class="w-3.5 h-3.5" />
                      <span>Verifikasi</span>
                    </button>

                    <!-- Disabled button if already verified -->
                    <button
                      v-else-if="acc.identity_status === 'VERIFIED'"
                      type="button"
                      disabled
                      title="Identitas pemohon sudah terverifikasi (Non-aktif)"
                      class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700/50 text-slate-400 dark:text-slate-500 font-bold text-[11px] flex items-center gap-1 cursor-not-allowed border border-slate-200 dark:border-slate-700 opacity-80"
                    >
                      <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
                      <span>Terverifikasi</span>
                    </button>

                    <Link
                      :href="route('admin.queue.applicants.show', acc.id)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white font-bold text-xs transition cursor-pointer"
                    >
                      <span>Detail</span>
                      <ChevronRight class="w-3.5 h-3.5" />
                    </Link>

                    <button
                      type="button"
                      @click="openDeleteModal(acc)"
                      title="Hapus Akun Pemohon"
                      class="p-1.5 rounded-xl text-rose-500 hover:text-white hover:bg-rose-600 transition cursor-pointer"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="accounts.links && accounts.links.length > 3" class="p-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-1">
          <Link
            v-for="(link, idx) in accounts.links"
            :key="idx"
            :href="link.url || '#'"
            v-html="link.label"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="link.active ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
          />
        </div>
      </div>
    </div>

    <!-- Quick Verify Modal -->
    <Teleport to="body">
      <div 
        v-if="selectedApplicantForVerify"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
        @click.self="selectedApplicantForVerify = null"
      >
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white">
              Verifikasi Identitas Pendaftar Baru
            </h3>
            <p class="text-xs text-slate-500 mt-1">
              Validasi kesesuaian identitas pemohon kependudukan berdasarkan data Disdukcapil.
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 space-y-1 text-xs">
            <p class="text-slate-500 font-bold text-[11px]">Nama User / Pemohon:</p>
            <p class="font-black text-slate-900 dark:text-white text-sm">{{ selectedApplicantForVerify.name }}</p>
            <p class="text-slate-500 font-bold text-[11px] pt-1">NIK Utuh (16 Digit):</p>
            <p class="font-mono font-black text-blue-600 dark:text-blue-400 text-sm tracking-widest select-all">{{ selectedApplicantForVerify.nik || selectedApplicantForVerify.masked_nik }}</p>
          </div>

          <!-- Selfie Photo Display in Quick Verify Modal -->
          <div v-if="selectedApplicantForVerify.has_selfie && selectedApplicantForVerify.selfie_url" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2.5">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                <Camera class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                <span>Foto Selfie Wajah Pendaftar</span>
              </span>
              <button
                type="button"
                @click="openSelfiePreviewModal(selectedApplicantForVerify)"
                class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer"
              >
                <Eye class="w-3 h-3" />
                <span>Perbesar Foto</span>
              </button>
            </div>

            <div class="flex items-center justify-center">
              <div
                @click="openSelfiePreviewModal(selectedApplicantForVerify)"
                class="relative rounded-2xl overflow-hidden border-2 border-slate-300 dark:border-slate-600 shadow-md group cursor-pointer max-w-[190px] w-full aspect-square bg-black"
                title="Klik untuk memperbesar foto selfie"
              >
                <img
                  :src="selectedApplicantForVerify.selfie_url"
                  :alt="selectedApplicantForVerify.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                />
                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                  <Eye class="w-4 h-4" />
                  <span>Lihat Penuh</span>
                </div>
              </div>
            </div>

            <!-- Auto Delete Notice -->
            <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/60 text-[10.5px] text-amber-900 dark:text-amber-200 flex items-start gap-2 leading-relaxed">
              <ShieldAlert class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
              <span>
                <strong>Ketentuan Privasi:</strong> Berkas foto selfie ini akan <strong>otomatis dihapus permanen dari server</strong> saat Anda menyimpan keputusan verifikasi (UU Perlindungan Data Pribadi).
              </span>
            </div>
          </div>

          <!-- If Selfie already destroyed -->
          <div v-else-if="selectedApplicantForVerify.is_selfie_destroyed" class="p-3 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-900/60 flex items-center gap-2.5 text-[11px] text-emerald-800 dark:text-emerald-300">
            <ShieldCheck class="w-4 h-4 text-emerald-600 shrink-0" />
            <span>Foto selfie biometrik pendaftar telah <strong>dimusnahkan secara aman</strong> dari server storage.</span>
          </div>

          <!-- Email notification info -->
          <div class="p-3 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 flex items-center gap-2.5 text-[11px] text-blue-800 dark:text-blue-200">
            <Mail class="w-4 h-4 shrink-0 text-blue-600 dark:text-blue-400" />
            <span>Pemberitahuan hasil verifikasi akun akan otomatis dikirimkan ke email pemohon.</span>
          </div>

          <form @submit.prevent="submitQuickVerify" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Keputusan Verifikasi
              </label>
              <select
                v-model="quickVerifyForm.decision"
                @change="handleDecisionChange"
                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="VERIFIED">VERIFIED (Setujui & Aktifkan Akun)</option>
                <option value="REJECTED">REJECTED (Tolak Identitas)</option>
              </select>
            </div>

            <!-- Template Kalimat Penolakan (Dropdown) -->
            <div 
              v-if="quickVerifyForm.decision === 'REJECTED'" 
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

            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                  Catatan Verifikasi {{ quickVerifyForm.decision === 'REJECTED' ? '(Alasan Penolakan)' : '(Opsional)' }}
                </label>
                <span v-if="quickVerifyForm.decision === 'REJECTED'" class="text-[10px] font-bold text-rose-600 dark:text-rose-400">
                  *Dikirim via email
                </span>
              </div>
              <textarea
                v-model="quickVerifyForm.notes"
                rows="3"
                :placeholder="quickVerifyForm.decision === 'REJECTED' ? 'Pilih template di atas atau ketik alasan penolakan di sini...' : 'Contoh: Telah sesuai dokumen SIAK / fisik KTP-el'"
                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
              <button
                type="button"
                @click="selectedApplicantForVerify = null"
                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs transition"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="quickVerifyForm.processing"
                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm disabled:opacity-50"
              >
                {{ quickVerifyForm.processing ? 'Menyimpan...' : 'Simpan Verifikasi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Modal Hapus Akun Pemohon -->
    <Teleport to="body">
      <div 
        v-if="selectedApplicantForDelete"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
        @click.self="selectedApplicantForDelete = null"
      >
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-rose-200 dark:border-rose-900 space-y-4">
          <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto shadow-inner">
            <Trash2 class="w-6 h-6" />
          </div>

          <div class="text-center space-y-1">
            <h3 class="text-base font-black text-slate-900 dark:text-white">
              Hapus Akun Pemohon
            </h3>
            <p class="text-xs text-slate-500">
              Apakah Anda yakin ingin menghapus akun pemohon <strong>{{ selectedApplicantForDelete.name }}</strong> (NIK: <span class="font-mono text-blue-600 font-bold">{{ selectedApplicantForDelete.nik || selectedApplicantForDelete.masked_nik }}</span>)?
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

          <div class="flex items-center justify-end gap-2 pt-2">
            <button
              type="button"
              @click="selectedApplicantForDelete = null"
              :disabled="deleteForm.processing"
              class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs transition"
            >
              Batal
            </button>
            <button
              type="button"
              @click="submitDeleteApplicant"
              :disabled="deleteForm.processing"
              class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Trash2 class="w-3.5 h-3.5" />
              <span v-if="deleteForm.processing">Menghapus...</span>
              <span v-else>Ya, Hapus Akun Ini</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Dedicated Fullscreen Photo Preview Modal -->
    <Teleport to="body">
      <div 
        v-if="selectedApplicantForPhotoPreview"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
        @click.self="selectedApplicantForPhotoPreview = null"
      >
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl p-5 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-sm font-black text-slate-900 dark:text-white">
                Foto Selfie Wajah Pendaftar
              </h3>
              <p class="text-[11px] text-slate-500">
                {{ selectedApplicantForPhotoPreview.name }} (NIK: <span class="font-mono text-blue-600 font-bold">{{ selectedApplicantForPhotoPreview.nik || selectedApplicantForPhotoPreview.masked_nik }}</span>)
              </p>
            </div>
            <button
              type="button"
              @click="selectedApplicantForPhotoPreview = null"
              class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="w-full max-h-[65vh] rounded-2xl overflow-hidden bg-black flex items-center justify-center border border-slate-200 dark:border-slate-800 shadow-inner">
            <img
              :src="selectedApplicantForPhotoPreview.selfie_url"
              :alt="selectedApplicantForPhotoPreview.name"
              class="max-w-full max-h-[65vh] object-contain"
            />
          </div>

          <div class="p-2.5 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 text-[11px] text-blue-900 dark:text-blue-200 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
              <ShieldCheck class="w-4 h-4 text-blue-600" />
              <span>Foto otomatis dimusnahkan pasca verifikasi akun (UU PDP).</span>
            </span>
            <button
              type="button"
              @click="selectedApplicantForPhotoPreview = null"
              class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  ChevronRight,
  UserCheck,
  Mail,
  Trash2,
  CheckCircle2,
  Camera,
  Eye,
  ShieldCheck,
  ShieldAlert,
  User,
  X
} from '@lucide/vue'

const props = defineProps({
  accounts: Object,
  filters: Object,
  stats: Object,
})

const searchQuery = ref(props.filters.search || '')
const accountStatus = ref(props.filters.account_status || '')
const identityStatus = ref(props.filters.identity_status || '')

// Selfie photo preview modal
const selectedApplicantForPhotoPreview = ref(null)
const openSelfiePreviewModal = (acc) => {
  selectedApplicantForPhotoPreview.value = acc
}

const applyFilters = () => {
  router.get(
    route('admin.queue.applicants.index'),
    {
      search: searchQuery.value,
      account_status: accountStatus.value,
      identity_status: identityStatus.value,
    },
    { preserveState: true }
  )
}

const filterByTab = (accStatus, idStatus) => {
  accountStatus.value = accStatus
  identityStatus.value = idStatus
  applyFilters()
}

// Quick verify modal
const selectedApplicantForVerify = ref(null)
const quickVerifyForm = useForm({
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
    quickVerifyForm.notes = selectedRejectTemplate.value
  }
}

const handleDecisionChange = () => {
  if (quickVerifyForm.decision === 'REJECTED') {
    if (!quickVerifyForm.notes && rejectionTemplates.length > 0) {
      selectedRejectTemplate.value = rejectionTemplates[0]
      quickVerifyForm.notes = rejectionTemplates[0]
    }
  } else {
    selectedRejectTemplate.value = ''
    if (rejectionTemplates.includes(quickVerifyForm.notes)) {
      quickVerifyForm.notes = ''
    }
  }
}

const openQuickVerifyModal = (acc) => {
  selectedApplicantForVerify.value = acc
  quickVerifyForm.reset()
  quickVerifyForm.decision = 'VERIFIED'
  selectedRejectTemplate.value = ''
}

const submitQuickVerify = () => {
  if (!selectedApplicantForVerify.value) return
  quickVerifyForm.post(route('admin.queue.applicants.verify-identity', selectedApplicantForVerify.value.id), {
    onSuccess: () => {
      selectedApplicantForVerify.value = null
    },
  })
}

// Delete applicant account modal & submit
const selectedApplicantForDelete = ref(null)
const deleteForm = useForm({})

const openDeleteModal = (acc) => {
  selectedApplicantForDelete.value = acc
}

const submitDeleteApplicant = () => {
  if (!selectedApplicantForDelete.value) return
  deleteForm.delete(route('admin.queue.applicants.destroy', selectedApplicantForDelete.value.id), {
    onSuccess: () => {
      selectedApplicantForDelete.value = null
    },
  })
}
</script>
