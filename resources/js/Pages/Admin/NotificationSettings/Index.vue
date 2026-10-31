<template>
  <Head title="Notifikasi & Integrasi Settings" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Notifikasi & Integrasi</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola pengaturan notifikasi email otomatis dan integrasi sistem CMS.</p>
        </div>
      </div>

      <!-- Flash Message -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold flex items-center justify-between">
        <span>✓ {{ $page.props.flash.success }}</span>
      </div>
      <div v-if="errors?.test_email || errors?.error" class="px-4 py-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs rounded-xl font-semibold">
        ✕ {{ errors.test_email || errors.error }}
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Columns: Main Notification Configuration Form -->
        <div class="lg:col-span-2 space-y-6">
          <form @submit.prevent="saveSettings" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-6">
            
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400">
                  <Bell class="w-5 h-5" />
                </div>
                <div>
                  <h2 class="text-sm font-bold text-gray-900 dark:text-zinc-100">Email Notifications — Pengaduan</h2>
                  <p class="text-[11px] text-gray-400">Pengaturan penerima dan event notifikasi otomatis pengaduan masuk.</p>
                </div>
              </div>

              <!-- Status Badge -->
              <span 
                class="px-3 py-1 rounded-xl text-xs font-bold border flex items-center gap-1.5"
                :class="[
                  form.complaint_notification_email_enabled 
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800/40' 
                    : 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'
                ]"
              >
                <span class="w-2 h-2 rounded-full" :class="form.complaint_notification_email_enabled ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                <span>{{ form.complaint_notification_email_enabled ? 'Notifikasi Aktif' : 'Notifikasi Nonaktif' }}</span>
              </span>
            </div>

            <!-- Enable Toggle -->
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/60 rounded-2xl border border-gray-100 dark:border-zinc-800">
              <div>
                <label class="text-xs font-bold text-gray-800 dark:text-zinc-200">Aktifkan Notifikasi Email Pengaduan</label>
                <p class="text-[10px] text-gray-400 mt-0.5">Sistem akan secara asinkron mengirim notifikasi saat terjadi event pengaduan.</p>
              </div>
              <button 
                type="button" 
                @click="form.complaint_notification_email_enabled = !form.complaint_notification_email_enabled"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                :class="form.complaint_notification_email_enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-zinc-700'"
              >
                <span 
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                  :class="form.complaint_notification_email_enabled ? 'translate-x-5' : 'translate-x-0'"
                />
              </button>
            </div>

            <!-- Primary Recipient Input -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-700 dark:text-zinc-300">
                Email Penerima Utama (Primary Recipient) <span class="text-rose-500">*</span>
              </label>
              <input 
                type="email" 
                v-model="form.complaint_notification_email_primary" 
                placeholder="contoh: dukcapilkabdompu@gmail.com"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none font-mono"
                :disabled="!permissions.can_manage"
              />
              <p class="text-[10px] text-gray-400">Email utama yang bertindak sebagai penerima alert notifikasi pengaduan.</p>
              <span v-if="errors?.complaint_notification_email_primary" class="text-[10px] text-rose-500 font-semibold">
                {{ errors.complaint_notification_email_primary }}
              </span>
            </div>

            <!-- Additional Recipients Input -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-700 dark:text-zinc-300">
                Email Penerima Tambahan (Multi-Recipient)
              </label>
              <input 
                type="text" 
                v-model="form.complaint_notification_email_additional" 
                placeholder="contoh: admin@dompukab.go.id, ppid@dompukab.go.id"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none font-mono"
                :disabled="!permissions.can_manage"
              />
              <p class="text-[10px] text-gray-400">Pisahkan beberapa alamat email dengan tanda koma (,).</p>
            </div>

            <!-- Event Trigger Controls -->
            <div class="space-y-3 pt-2 border-t border-gray-100 dark:border-zinc-800">
              <label class="text-xs font-bold text-gray-700 dark:text-zinc-300">Event Notifikasi Otomatis</label>
              
              <div class="space-y-2">
                <label class="flex items-center gap-2.5 cursor-pointer">
                  <input 
                    type="checkbox" 
                    v-model="form.complaint_notification_email_on_created" 
                    class="rounded text-primary-600 focus:ring-primary-500 dark:bg-zinc-800 border-gray-300 dark:border-zinc-700"
                    :disabled="!permissions.can_manage"
                  />
                  <span class="text-xs text-gray-700 dark:text-zinc-200 font-medium">
                    Kirim notifikasi saat **Pengaduan Baru** berhasil dikirim oleh masyarakat.
                  </span>
                </label>

                <label class="flex items-center gap-2.5 cursor-pointer">
                  <input 
                    type="checkbox" 
                    v-model="form.complaint_notification_email_on_status_changed" 
                    class="rounded text-primary-600 focus:ring-primary-500 dark:bg-zinc-800 border-gray-300 dark:border-zinc-700"
                    :disabled="!permissions.can_manage"
                  />
                  <span class="text-xs text-gray-700 dark:text-zinc-200 font-medium">
                    Kirim notifikasi saat **Status Pengaduan** diubah oleh petugas admin.
                  </span>
                </label>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-zinc-800">
              <button 
                v-if="permissions.can_test"
                type="button" 
                @click="triggerTestEmail"
                :disabled="testLoading"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-800 dark:text-zinc-200 text-xs font-bold rounded-xl transition flex items-center gap-2 disabled:opacity-50"
              >
                <Send v-if="!testLoading" class="w-3.5 h-3.5 text-primary-500" />
                <span v-else class="w-3.5 h-3.5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></span>
                <span>{{ testLoading ? 'Mengirim Uji Coba...' : 'Kirim Email Uji Coba' }}</span>
              </button>
              <div v-else></div>

              <button 
                v-if="permissions.can_manage"
                type="submit" 
                :disabled="saving"
                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2 disabled:opacity-50"
              >
                <Save v-if="!saving" class="w-3.5 h-3.5" />
                <span v-else class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span>{{ saving ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Right 1 Column: Read-Only SMTP Diagnostic Server Status -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 border-b border-gray-100 dark:border-zinc-800 pb-3">
              <Server class="w-4 h-4 text-gray-500" />
              <h3 class="text-xs font-bold text-gray-800 dark:text-zinc-200">Status Server SMTP (.env)</h3>
            </div>

            <div class="space-y-2.5 text-[11px] font-mono">
              <div class="flex justify-between py-1 border-b border-gray-50 dark:border-zinc-800/60">
                <span class="text-gray-400">Mailer Driver:</span>
                <span class="font-bold text-gray-700 dark:text-zinc-200 uppercase">{{ smtpInfo.mailer }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-gray-50 dark:border-zinc-800/60">
                <span class="text-gray-400">SMTP Host:</span>
                <span class="font-bold text-gray-700 dark:text-zinc-200">{{ smtpInfo.host }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-gray-50 dark:border-zinc-800/60">
                <span class="text-gray-400">SMTP Port:</span>
                <span class="font-bold text-gray-700 dark:text-zinc-200">{{ smtpInfo.port }} ({{ smtpInfo.encryption }})</span>
              </div>
              <div class="flex justify-between py-1 border-b border-gray-50 dark:border-zinc-800/60">
                <span class="text-gray-400">SMTP User:</span>
                <span class="font-bold text-gray-700 dark:text-zinc-200 truncate max-w-[140px]">{{ smtpInfo.username }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-gray-50 dark:border-zinc-800/60">
                <span class="text-gray-400">Password:</span>
                <span 
                  class="font-bold text-xs"
                  :class="smtpInfo.has_password ? 'text-emerald-500' : 'text-amber-500'"
                >
                  {{ smtpInfo.has_password ? '✔ Terkonfigurasi' : '⚠️ Kosong' }}
                </span>
              </div>
              <div class="flex justify-between py-1">
                <span class="text-gray-400">Pengirim (From):</span>
                <span class="font-bold text-gray-700 dark:text-zinc-200 truncate max-w-[140px]">{{ smtpInfo.from_address }}</span>
              </div>
            </div>

            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 rounded-xl text-[10px] text-amber-700 dark:text-amber-300">
              ℹ️ Kredensial server SMTP disimpan secara aman di environment server (`.env`) dan tidak disimpan di database.
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Table: Notification Audit & History Log -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
          <div class="flex items-center gap-2">
            <ShieldCheck class="w-4 h-4 text-primary-500" />
            <h3 class="text-xs font-bold text-gray-900 dark:text-zinc-100">Riwayat & Audit Log Pengiriman Notifikasi</h3>
          </div>
          <span class="text-[10px] text-gray-400 font-mono">Total Log: {{ logs.total }}</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800">
                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-gray-400">Tiket</th>
                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-gray-400">Penerima</th>
                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-gray-400">Tipe Event</th>
                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-gray-400">Waktu Kirim</th>
                <th class="px-4 py-2.5 text-[9px] font-bold uppercase tracking-wider text-gray-400">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60 font-mono text-[11px]">
              <tr v-if="logs.data.length === 0">
                <td colspan="6" class="px-4 py-6 text-center text-gray-400 italic">Belum ada riwayat pengiriman notifikasi.</td>
              </tr>
              <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30">
                <td class="px-4 py-3 font-bold text-primary-600">
                  <Link v-if="log.complaint" :href="route('admin.complaints.show', log.complaint.id)" class="hover:underline">
                    {{ log.complaint.ticket_number }}
                  </Link>
                  <span v-else>-</span>
                </td>
                <td class="px-4 py-3 text-gray-700 dark:text-zinc-200 select-all">
                  {{ log.recipient }}
                </td>
                <td class="px-4 py-3 text-gray-500 uppercase text-[10px]">
                  {{ log.notification_type }}
                </td>
                <td class="px-4 py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide"
                    :class="[
                      log.status === 'SENT' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : '',
                      log.status === 'PENDING' || log.status === 'SENDING' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : '',
                      log.status === 'FAILED' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' : '',
                    ]"
                  >
                    {{ log.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-400">
                  {{ formatDate(log.sent_at || log.created_at) }}
                </td>
                <td class="px-4 py-3 text-gray-400 truncate max-w-xs" :title="log.error_message">
                  {{ log.error_message || (log.status === 'SENT' ? 'Berhasil dikirim via SMTP' : 'Dalam antrean') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Bell, Save, Send, Server, ShieldCheck } from '@lucide/vue';

const props = defineProps({
  config: Object,
  smtpInfo: Object,
  logs: Object,
  permissions: Object,
  errors: Object,
});

const form = ref({
  complaint_notification_email_enabled: props.config?.enabled ?? false,
  complaint_notification_email_primary: props.config?.primary_email ?? '',
  complaint_notification_email_additional: props.config?.additional_emails ?? '',
  complaint_notification_email_on_created: props.config?.on_created ?? true,
  complaint_notification_email_on_status_changed: props.config?.on_status_changed ?? false,
});

const saving = ref(false);
const testLoading = ref(false);

const saveSettings = () => {
  saving.value = true;
  router.post(route('admin.notification-settings.update'), form.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    },
  });
};

const triggerTestEmail = () => {
  testLoading.value = true;
  router.post(route('admin.notification-settings.test'), {
    recipient: form.value.complaint_notification_email_primary,
  }, {
    preserveScroll: true,
    onFinish: () => {
      testLoading.value = false;
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>
