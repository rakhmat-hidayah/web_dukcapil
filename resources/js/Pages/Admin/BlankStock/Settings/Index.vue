<template>
  <Head title="Pengaturan Persediaan Blangko - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6 max-w-4xl mx-auto">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-zinc-900 to-slate-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-zinc-800">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-zinc-800 text-sky-400 border border-zinc-700 flex items-center justify-center shrink-0">
            <Settings class="w-6 h-6" />
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Pengaturan Persediaan Blangko
            </h1>
            <p class="text-xs sm:text-sm text-zinc-300 mt-1">
              Konfigurasi ambang batas peringatan stok menipis, status ketersediaan publik, dan validasi bukti kerusakan operator.
            </p>
          </div>
        </div>
      </div>

      <!-- Settings Form -->
      <form @submit.prevent="saveSettings" class="space-y-6">
        <!-- Section 1: Ambang Batas Peringatan Stok -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="border-b border-gray-100 dark:border-zinc-800 pb-3">
            <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider">
              1. Ambang Batas Peringatan Stok Internal
            </h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
              Batas minimal kuantiti untuk memicu alert peringatan di dashboard pimpinan dan operator
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Batas Minimum Persediaan Menipis (Keping) *
              </label>
              <input
                v-model.number="form.min_stock_alert"
                type="number"
                min="1"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-mono font-bold text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-sky-500"
              />
              <span class="text-[10px] text-gray-400 block mt-1">Dashboard akan menampilkan peringatan kuning jika stok di bawah angka ini.</span>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Batas Kritis / Stok Habis (Keping) *
              </label>
              <input
                v-model.number="form.critical_stock_alert"
                type="number"
                min="0"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-mono font-bold text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-sky-500"
              />
              <span class="text-[10px] text-gray-400 block mt-1">Alert merah bahaya akan aktif jika stok berada pada atau di bawah angka ini.</span>
            </div>
          </div>
        </div>

        <!-- Section 2: Kebijakan Tampilan Informasi Publik -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="border-b border-gray-100 dark:border-zinc-800 pb-3">
            <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider">
              2. Kriteria Status Ketersediaan Publik
            </h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
              Aturan penentuan status label yang dilihat masyarakat di halaman informasi ketersediaan blangko
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Ambang Batas Status "PERSEDIAAN TERBATAS" *
              </label>
              <input
                v-model.number="form.stock_threshold_limited"
                type="number"
                min="1"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-mono font-bold text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-sky-500"
              />
              <span class="text-[10px] text-gray-400 block mt-1">Jika stok di bawah angka ini, publik melihat status PERSEDIAAN TERBATAS.</span>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                Ambang Batas Status "STOK KOSONG" *
              </label>
              <input
                v-model.number="form.stock_threshold_empty"
                type="number"
                min="0"
                required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50/50 dark:bg-zinc-800/50 text-xs font-mono font-bold text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-sky-500"
              />
              <span class="text-[10px] text-gray-400 block mt-1">Jika stok sama dengan atau di bawah angka ini, publik melihat status STOK KOSONG.</span>
            </div>
          </div>

          <div class="pt-3 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between">
            <div>
              <span class="text-xs font-bold text-gray-800 dark:text-zinc-200 block">Tampilkan Informasi Blangko di Website Publik</span>
              <span class="text-[11px] text-gray-400">Izinkan masyarakat melihat status ketersediaan blangko di portal layanan</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.public_info_enabled" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            </label>
          </div>
        </div>

        <!-- Section 3: Prosedur & Validasi Operator -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="border-b border-gray-100 dark:border-zinc-800 pb-3">
            <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider">
              3. Prosedur & Pengendalian Operator Cetak
            </h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
              Kebijakan pelaporan pemakaian dan pembuktian kerusakan kartu fisik
            </p>
          </div>

          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <span class="text-xs font-bold text-gray-800 dark:text-zinc-200 block">Wajib Unggah Foto Bukti Kartu Rusak</span>
                <span class="text-[11px] text-gray-400">Operator wajib mengambil foto fisik kartu rusak sebelum laporan dapat disimpan</span>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="form.require_damage_proof" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
              </label>
            </div>

            <div class="flex items-center justify-between border-t border-gray-100 dark:border-zinc-800 pt-3">
              <div>
                <span class="text-xs font-bold text-gray-800 dark:text-zinc-200 block">Izinkan Operator Menyimpan Draf Laporan</span>
                <span class="text-[11px] text-gray-400">Draf dapat disimpan sementara selama sesi cetak berjalan tanpa memotong saldo sistem</span>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="form.allow_operator_draft" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
              </label>
            </div>

            <div class="flex items-center justify-between border-t border-gray-100 dark:border-zinc-800 pt-3">
              <div>
                <span class="text-xs font-bold text-gray-800 dark:text-zinc-200 block">Kirim Peringatan Otomatis Saat Persediaan Menipis</span>
                <span class="text-[11px] text-gray-400">Sistem memicu notifikasi internal ke Bendahara Barang dan Admin</span>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="form.notify_on_low_stock" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
              </label>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-8 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 disabled:opacity-50 text-white font-black text-xs shadow-lg shadow-sky-600/30 transition cursor-pointer active:scale-95"
          >
            <span v-if="form.processing">Menyimpan Konfigurasi...</span>
            <span v-else>Simpan Perubahan Pengaturan</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Settings } from '@lucide/vue';

const props = defineProps({
  stock: {
    type: Object,
    required: true,
  },
  settings: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  min_stock_alert: props.stock.min_stock_alert || 100,
  critical_stock_alert: props.stock.critical_stock_alert || 20,
  stock_threshold_limited: Number(props.settings.stock_threshold_limited || 100),
  stock_threshold_empty: Number(props.settings.stock_threshold_empty || 0),
  public_info_enabled: props.settings.public_info_enabled === 'true' || props.settings.public_info_enabled === true || props.settings.public_info_enabled === '1',
  require_damage_proof: props.settings.require_damage_proof === 'true' || props.settings.require_damage_proof === true || props.settings.require_damage_proof === '1',
  allow_operator_draft: props.settings.allow_operator_draft === 'true' || props.settings.allow_operator_draft === true || props.settings.allow_operator_draft === '1',
  notify_on_low_stock: props.settings.notify_on_low_stock === 'true' || props.settings.notify_on_low_stock === true || props.settings.notify_on_low_stock === '1',
});

const saveSettings = () => {
  form.post(route('admin.blank-stocks.settings.update'));
};
</script>
