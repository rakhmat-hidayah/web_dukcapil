<template>
  <Head title="Kategori Pengaduan" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">Kategori Pengaduan</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola tipe klasifikasi tiket pengaduan masyarakat.</p>
        </div>
        <button 
          @click="openModal(null)"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5"
        >
          <Plus class="w-3.5 h-3.5" /> Tambah Kategori
        </button>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold">
        ✓ {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 text-xs rounded-xl font-semibold">
        ✕ {{ $page.props.flash.error }}
      </div>

      <!-- Categories table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/60 border-b border-gray-100 dark:border-zinc-800">
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400 w-16">Icon</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Nama Kategori</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Slug</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Deskripsi</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Urutan</th>
                <th class="px-5 py-3 text-[9px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                <th class="px-5 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/60">
              <tr v-if="categories.length === 0">
                <td colspan="7" class="px-5 py-8 text-center text-gray-400 italic">Belum ada kategori.</td>
              </tr>
              <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                <td class="px-5 py-3 text-center text-lg">{{ cat.icon || '📝' }}</td>
                <td class="px-5 py-3 font-bold text-gray-700 dark:text-zinc-200">{{ cat.name }}</td>
                <td class="px-5 py-3 text-gray-400 font-mono">{{ cat.slug }}</td>
                <td class="px-5 py-3 text-gray-400 max-w-xs truncate">{{ cat.description || '-' }}</td>
                <td class="px-5 py-3 font-mono">{{ cat.sort_order }}</td>
                <td class="px-5 py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-bold uppercase font-mono"
                    :class="cat.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400'"
                  >
                    {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-5 py-3 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button @click="openModal(cat)" class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><SquarePen class="w-3.5 h-3.5" /></button>
                    <button @click="confirmDelete(cat)" class="p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><Trash2 class="w-3.5 h-3.5" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Category Form Modal -->
    <div v-if="modal.open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="modal.open = false">
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl p-6 w-full max-w-md shadow-2xl space-y-4">
        <h3 class="font-black text-sm text-gray-900 dark:text-zinc-50">
          {{ modal.editing ? 'Edit Kategori Pengaduan' : 'Tambah Kategori Pengaduan' }}
        </h3>

        <form @submit.prevent="submitForm" class="space-y-3">
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Kategori *</label>
            <input v-model="form.name" required type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
          </div>

          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Slug *</label>
            <input v-model="form.slug" required type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
          </div>

          <!-- Icon Emoji Field with Categorized Preset Palette -->
          <div class="p-3 bg-gray-50/70 dark:bg-zinc-800/50 rounded-2xl border border-gray-150 dark:border-zinc-800 space-y-2.5">
            <div class="flex items-center justify-between">
              <div>
                <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400">Icon Kategori (Emoji / Simbol)</label>
                <p class="text-[10px] text-gray-400 mt-0.5">Klik salah satu pilihan icon di bawah atau ketik langsung emoji pilihan Anda.</p>
              </div>
              <span class="hidden sm:inline-block text-[9px] text-gray-400 font-mono">
                Pintasan: <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-zinc-700 rounded text-[9px] font-mono text-gray-700 dark:text-zinc-300">Win + .</kbd>
              </span>
            </div>

            <div class="flex items-start gap-3">
              <div class="flex flex-col items-center shrink-0">
                <input 
                  v-model="form.icon" 
                  type="text" 
                  class="w-12 h-12 bg-white dark:bg-zinc-900 border-2 border-primary-500/50 rounded-2xl text-xl font-bold text-center focus:ring-2 focus:ring-primary-500 focus:outline-none shadow-sm hover:border-primary-500 transition" 
                  title="Klik untuk ketik atau tempel emoji custom"
                />
                <span class="text-[9px] text-gray-400 font-bold mt-1">Terpilih</span>
              </div>

              <!-- Categorized Emojis Palette -->
              <div class="flex-1 space-y-2 max-h-36 overflow-y-auto pr-1 scrollbar-thin">
                <div v-for="cat in emojiCollection" :key="cat.name" class="space-y-1">
                  <span class="text-[9px] font-extrabold uppercase tracking-wider text-primary-600 dark:text-primary-400 block">{{ cat.name }}</span>
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <button 
                      v-for="item in cat.items" 
                      :key="item.emoji"
                      type="button"
                      @click="form.icon = item.emoji"
                      class="h-7 px-2 flex items-center gap-1 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200/80 dark:border-zinc-700/80 hover:bg-primary-50 dark:hover:bg-primary-950/40 hover:border-primary-400 hover:scale-105 active:scale-95 transition"
                      :class="{ 'border-primary-600 bg-primary-50 dark:bg-primary-950/60 ring-2 ring-primary-500/30 font-bold shadow-xs': form.icon === item.emoji }"
                      :title="`Pilih ${item.label} (${item.emoji})`"
                    >
                      <span class="text-sm">{{ item.emoji }}</span>
                      <span class="text-[10px] text-gray-600 dark:text-zinc-300">{{ item.label }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Urutan Sortir</label>
              <input v-model="form.sort_order" type="number" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Warna Badge (Hex)</label>
              <div class="flex items-center gap-2 mt-1">
                <input v-model="form.color" type="color" class="w-8 h-8 rounded-lg border-0 cursor-pointer" />
                <span class="text-xs font-mono text-gray-500 dark:text-zinc-400">{{ form.color }}</span>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Deskripsi Singkat</label>
            <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
          </div>

          <div class="flex items-center gap-3 py-1">
            <input id="cat_is_active" type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-primary-600" />
            <label for="cat_is_active" class="text-xs text-gray-500 dark:text-zinc-400 cursor-pointer">Kategori aktif (bisa dipilih masyarakat)</label>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-gray-150 dark:border-zinc-800">
            <button type="button" @click="modal.open = false" class="px-4 py-2 text-xs font-bold border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 transition">Batal</button>
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-black rounded-xl transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, SquarePen, Trash2 } from '@lucide/vue';

const props = defineProps({
  categories: Array,
});

const emojiCollection = [
  {
    name: 'Pencatatan Sipil (Perkawinan, Kelahiran, Kematian, dll.)',
    items: [
      { emoji: '💍', label: 'Perkawinan' },
      { emoji: '👰', label: 'Pengantin' },
      { emoji: '💒', label: 'Pemberkatan' },
      { emoji: '💔', label: 'Perceraian' },
      { emoji: '👶', label: 'Akta Kelahiran' },
      { emoji: '🍼', label: 'Bayi / Balita' },
      { emoji: '🕊️', label: 'Akta Kematian' },
      { emoji: '⚰️', label: 'Pemakaman' },
      { emoji: '📜', label: 'Kutipan Akta' },
      { emoji: '⚖️', label: 'Pengesahan Anak' },
    ]
  },
  {
    name: 'Pendaftaran Penduduk (KTP, KK, KIA, Pindah Datang)',
    items: [
      { emoji: '🪪', label: 'KTP-el' },
      { emoji: '👨‍👩‍👧‍👦', label: 'Kartu Keluarga' },
      { emoji: '🆔', label: 'IKD (Digital)' },
      { emoji: '🧒', label: 'KIA (Kartu Anak)' },
      { emoji: '👤', label: 'Biodata Penduduk' },
      { emoji: '👥', label: 'Anggota Keluarga' },
      { emoji: '🏠', label: 'Pindah Domisili' },
      { emoji: '🚚', label: 'Pindah Datang' },
      { emoji: '✈️', label: 'Luar Daerah / LN' },
    ]
  },
  {
    name: 'Dokumen, Formulir & Administrasi',
    items: [
      { emoji: '📝', label: 'Formulir / Catatan' },
      { emoji: '📋', label: 'Persyaratan' },
      { emoji: '📄', label: 'Surat Keterangan' },
      { emoji: '📑', label: 'Legalisir Berkas' },
      { emoji: '📁', label: 'Arsip Berkas' },
      { emoji: '🗂️', label: 'Map Dokumen' },
      { emoji: '🖨️', label: 'Cetak Dokumen' },
      { emoji: '🔍', label: 'Validasi / Cek Data' },
    ]
  },
  {
    name: 'Layanan & Saluran Publik',
    items: [
      { emoji: '🏛️', label: 'Kantor Dukcapil' },
      { emoji: '🏢', label: 'Kecamatan' },
      { emoji: '🚐', label: 'Layanan Keliling' },
      { emoji: '📱', label: 'Layanan Online / WA' },
      { emoji: '🌐', label: 'Portal SANAI' },
      { emoji: '⏱️', label: 'Layanan Cepat' },
      { emoji: '🆓', label: 'Layanan Gratis' },
      { emoji: 'ℹ️', label: 'Pusat Informasi' },
      { emoji: '🤝', label: 'Konsultasi Petugas' },
      { emoji: '🚨', label: 'Pengaduan Darurat' },
    ]
  },
];

const modal = reactive({
  open: false,
  editing: null,
});

const form = reactive({
  name: '',
  slug: '',
  icon: '📝',
  color: '#6366f1',
  description: '',
  sort_order: 0,
  is_active: true,
});

const openModal = (cat) => {
  modal.editing = cat;
  if (cat) {
    Object.assign(form, {
      name: cat.name,
      slug: cat.slug,
      icon: cat.icon || '📝',
      color: cat.color || '#6366f1',
      description: cat.description || '',
      sort_order: cat.sort_order || 0,
      is_active: !!cat.is_active,
    });
  } else {
    Object.assign(form, {
      name: '',
      slug: '',
      icon: '📝',
      color: '#6366f1',
      description: '',
      sort_order: 0,
      is_active: true,
    });
  }
  modal.open = true;
};

const submitForm = () => {
  if (modal.editing) {
    router.put(route('admin.complaints.categories.update', modal.editing.id), form, {
      onSuccess: () => {
        modal.open = false;
      }
    });
  } else {
    router.post(route('admin.complaints.categories.store'), form, {
      onSuccess: () => {
        modal.open = false;
      }
    });
  }
};

const confirmDelete = (cat) => {
  if (confirm(`Hapus kategori "${cat.name}"?`)) {
    router.delete(route('admin.complaints.categories.destroy', cat.id));
  }
};
</script>
