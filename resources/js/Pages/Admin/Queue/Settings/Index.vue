<template>
  <Head title="Pengaturan Antrian Online KTP-el - Admin Disdukcapil" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            Pengaturan Sistem Antrian Online
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Konfigurasi jam operasional kantor, alamat fisik kedatangan, kontak helpdesk, serta notifikasi email.
          </p>
        </div>
      </div>

      <form @submit.prevent="submitSettings" class="space-y-6">
        <!-- MAINTENANCE / PEMELIHARAAN GROUP (SPECIAL FEATURE) -->
        <div
          class="rounded-3xl p-6 border shadow-sm space-y-5 transition-colors"
          :class="[
            form['queue.maintenance_mode']
              ? 'bg-amber-50/80 dark:bg-amber-950/30 border-amber-300 dark:border-amber-800 ring-2 ring-amber-500/20'
              : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'
          ]"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4" :class="form['queue.maintenance_mode'] ? 'border-amber-200 dark:border-amber-900/60' : 'border-slate-100 dark:border-slate-700'">
            <div class="flex items-center gap-3">
              <div
                class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold shadow-sm transition"
                :class="form['queue.maintenance_mode'] ? 'bg-amber-500 text-white animate-pulse' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400'"
              >
                <svg v-if="form['queue.maintenance_mode']" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-black text-slate-900 dark:text-white">
                    Mode Pemeliharaan (Maintenance) Layanan Online
                  </h3>
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                    :class="form['queue.maintenance_mode'] ? 'bg-rose-600 text-white' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'"
                  >
                    {{ form['queue.maintenance_mode'] ? '● MAINTENANCE AKTIF (LAYANAN DITUTUP SEMENTARA)' : '● LAYANAN ONLINE AKTIF (NORMAL)' }}
                  </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  Gunakan fitur ini untuk menutup sementara akses pendaftaran permohonan baru KTP-el bagi publik saat server sedang update atau perbaikan.
                </p>
              </div>
            </div>

            <!-- Toggle Switch -->
            <label class="relative inline-flex items-center cursor-pointer select-none">
              <input
                type="checkbox"
                v-model="form['queue.maintenance_mode']"
                class="sr-only peer"
              />
              <div class="w-14 h-8 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-amber-600"></div>
              <span class="ml-3 text-xs font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                {{ form['queue.maintenance_mode'] ? 'Matikan Layanan (Aktifkan Mode Maintenance)' : 'Layanan Normal' }}
              </span>
            </label>
          </div>

          <!-- Configuration Fields -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Judul Pengumuman Pemeliharaan (Ditampilkan pada Halaman Pendaftaran Publik)
              </label>
              <input
                v-model="form['queue.maintenance_title']"
                type="text"
                placeholder="Contoh: Layanan Pendaftaran Online KTP-el Sedang Dalam Pemeliharaan Sistem"
                class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500"
              />
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Pesan & Instruksi Rinci untuk Pemohon
              </label>
              <textarea
                v-model="form['queue.maintenance_message']"
                rows="3"
                placeholder="Tuliskan keterangan mengapa layanan ditutup sementara dan apa yang harus dilakukan pemohon..."
                class="w-full p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 leading-relaxed"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Perkiraan Waktu Selesai / Dibuka Kembali (Opsional)
              </label>
              <input
                v-model="form['queue.maintenance_estimated_end']"
                type="text"
                placeholder="Contoh: Hari ini pukul 14:00 WITA atau 08 Nov 2026"
                class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500"
              />
            </div>

            <div class="flex items-center p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/40 text-xs text-blue-900 dark:text-blue-200">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>Saat aktif, pendaftaran baru akan dialihkan ke halaman pemeliharaan resmi dengan kontak helpdesk WhatsApp dan informasi loket fisik.</span>
            </div>
          </div>
        </div>

        <!-- GENERAL GROUP -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Informasi Kantor & Kontak Layanan
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Alamat Fisik Kantor Disdukcapil (Instruksi Kehadiran Tiket)
              </label>
              <textarea
                v-model="form['queue.office_address']"
                rows="2"
                class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Nomor WhatsApp Helpdesk Pelayanan KTP-el
              </label>
              <input
                v-model="form['queue.helpdesk_phone']"
                type="text"
                placeholder="0823-4000-0000"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- SCHEDULE & OPERATIONAL GROUP -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Jadwal Operasional & Kebijakan Antrian
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Jam Mulai Loket
              </label>
              <input
                v-model="form['queue.open_time']"
                type="time"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Jam Tutup Loket
              </label>
              <input
                v-model="form['queue.close_time']"
                type="time"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Batas Hari Buka Jadwal (Hari ke Depan)
              </label>
              <input
                v-model.number="form['queue.registration_open_days_ahead']"
                type="number"
                min="1"
                max="60"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Batas Waktu Pembatalan (Jam Sebelum Jadwal)
              </label>
              <input
                v-model.number="form['queue.allow_cancellation_hours']"
                type="number"
                min="0"
                max="72"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Tenggat Perbaikan Dokumen (Hari)
              </label>
              <input
                v-model.number="form['queue.revision_deadline_days']"
                type="number"
                min="1"
                max="14"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Jeda Waktu Tunggu Draf Dokumen (Menit)
              </label>
              <input
                v-model.number="form['queue.draft_timeout_minutes']"
                type="number"
                min="5"
                max="1440"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p class="text-[10px] text-slate-400 mt-1">
                Batas waktu tunggu pemohon mengunggah berkas sebelum draf kedaluwarsa & dibersihkan otomatis.
              </p>
            </div>
          </div>
        </div>

        <!-- NOTIFICATION GROUP -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
          <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Notifikasi Otomatis (Email)
          </h3>
          <p class="text-xs text-slate-400">
            Kirimkan pemberitahuan otomatis kepada pemohon yang menyertakan alamat email pada saat pendaftaran.
          </p>

          <div class="space-y-3 pt-2">
            <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer">
              <input
                type="checkbox"
                v-model="form['queue.notify_on_submit']"
                class="rounded text-blue-600 focus:ring-blue-500"
              />
              <div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Notifikasi Pengajuan Berhasil Diterima</span>
                <span class="text-[11px] text-slate-400">Kirim email berisi nomor registrasi sementara dan tautan dashboard pelacakan.</span>
              </div>
            </label>

            <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer">
              <input
                type="checkbox"
                v-model="form['queue.notify_on_revision']"
                class="rounded text-blue-600 focus:ring-blue-500"
              />
              <div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Notifikasi Dokumen Memerlukan Perbaikan</span>
                <span class="text-[11px] text-slate-400">Kirim email berisi catatan petugas mengenai berkas yang perlu difoto / diunggah ulang.</span>
              </div>
            </label>

            <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer">
              <input
                type="checkbox"
                v-model="form['queue.notify_on_approved']"
                class="rounded text-blue-600 focus:ring-blue-500"
              />
              <div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Notifikasi Pengajuan Disetujui & Tiket Antrian Terbit</span>
                <span class="text-[11px] text-slate-400">Kirim email berisi nomor antrean (contoh: H-001), tanggal jadwal, dan tiket elektronik.</span>
              </div>
            </label>

            <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer">
              <input
                type="checkbox"
                v-model="form['queue.notify_on_rejected']"
                class="rounded text-blue-600 focus:ring-blue-500"
              />
              <div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Notifikasi Pengajuan Ditolak</span>
                <span class="text-[11px] text-slate-400">Kirim email pemberitahuan penolakan beserta alasan dari petugas verifikator.</span>
              </div>
            </label>

            <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer">
              <input
                type="checkbox"
                v-model="form['queue.notify_on_ready_for_pickup']"
                class="rounded text-blue-600 focus:ring-blue-500"
              />
              <div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Notifikasi KTP-el Siap Diambil</span>
                <span class="text-[11px] text-slate-400">Kirim email saat fisik kartu KTP-el selesai dicetak dan siap diambil di loket penyerahan.</span>
              </div>
            </label>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end">
          <button
            type="submit"
            :disabled="submitting"
            class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-blue-600/30 transition flex items-center gap-2"
          >
            <span v-if="submitting">Menyimpan Pengaturan...</span>
            <span v-else>Simpan Seluruh Pengaturan</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  settings: Object,
})

const getVal = (group, key, fallback = '') => {
  const item = props.settings?.[group]?.[key]
  return item ? item.value : fallback
}

const form = ref({
  'queue.maintenance_mode': getVal('maintenance', 'queue.maintenance_mode', '0') === '1',
  'queue.maintenance_title': getVal('maintenance', 'queue.maintenance_title', 'Layanan Pendaftaran Online KTP-el Sedang Dalam Pemeliharaan Sistem'),
  'queue.maintenance_message': getVal('maintenance', 'queue.maintenance_message', 'Mohon maaf, saat ini pendaftaran dan antrian online KTP-el Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu sedang dinonaktifkan sementara untuk pemeliharaan dan pembaruan sistem. Layanan akan segera dibuka kembali setelah proses selesai. Bagi masyarakat yang membutuhkan pelayanan mendesak, silakan datang langsung ke loket kantor Disdukcapil Kabupaten Dompu.'),
  'queue.maintenance_estimated_end': getVal('maintenance', 'queue.maintenance_estimated_end', ''),
  'queue.office_address': getVal('general', 'queue.office_address', 'Jl. Soekarno Hatta No. 9, Dompu - NTB 84211'),
  'queue.helpdesk_phone': getVal('general', 'queue.helpdesk_phone', '6282124824114'),
  'queue.open_time': getVal('schedule', 'queue.open_time', '08:00'),
  'queue.close_time': getVal('schedule', 'queue.close_time', '15:30'),
  'queue.registration_open_days_ahead': parseInt(getVal('schedule', 'queue.registration_open_days_ahead', '14')),
  'queue.allow_cancellation_hours': parseInt(getVal('policy', 'queue.allow_cancellation_hours', '12')),
  'queue.revision_deadline_days': parseInt(getVal('policy', 'queue.revision_deadline_days', '3')),
  'queue.draft_timeout_minutes': parseInt(getVal('policy', 'queue.draft_timeout_minutes', '15')),
  'queue.notify_on_submit': getVal('notification', 'queue.notify_on_submit', '1') === '1',
  'queue.notify_on_revision': getVal('notification', 'queue.notify_on_revision', '1') === '1',
  'queue.notify_on_approved': getVal('notification', 'queue.notify_on_approved', '1') === '1',
  'queue.notify_on_rejected': getVal('notification', 'queue.notify_on_rejected', '1') === '1',
  'queue.notify_on_ready_for_pickup': getVal('notification', 'queue.notify_on_ready_for_pickup', '1') === '1',
})

const submitting = ref(false)

const submitSettings = () => {
  submitting.value = true
  router.post(route('admin.queue.settings.update'), { settings: form.value }, {
    preserveScroll: true,
    onFinish: () => { submitting.value = false },
  })
}
</script>
