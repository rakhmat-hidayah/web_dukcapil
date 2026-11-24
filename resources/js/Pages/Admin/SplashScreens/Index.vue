<template>
  <AdminLayout title="Splash Screen">
    <div class="max-w-6xl mx-auto space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <h1 class="text-xl font-extrabold text-gray-900 dark:text-zinc-50">🖼️ Splash Screen</h1>
          <p class="text-sm text-gray-500 dark:text-zinc-400 mt-0.5">
            Kelola popup pembuka yang tampil saat pengunjung pertama membuka halaman beranda.
          </p>
        </div>
        <button
          @click="openCreate"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition flex items-center gap-2 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          Tambah Splash Screen
        </button>
      </div>

      <!-- Flash Message -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 shrink-0" />
        {{ $page.props.flash.success }}
      </div>

      <!-- Info Box -->
      <div class="p-4 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/50 rounded-2xl text-sm text-blue-800 dark:text-blue-300 space-y-1">
        <p class="font-bold flex items-center gap-1.5"><Info class="w-4 h-4" /> Cara Kerja Splash Screen</p>
        <ul class="list-disc list-inside space-y-0.5 text-blue-700 dark:text-blue-400 text-xs ml-1">
          <li><strong>Sekali Per Sesi</strong> — tampil sekali selama browser dibuka, hilang setelah ditutup.</li>
          <li><strong>Sekali Per Hari</strong> — tampil sekali per hari per perangkat, lalu tidak muncul lagi hari itu.</li>
          <li><strong>Selalu Tampil</strong> — tampil setiap kali halaman dibuka (cocok untuk pengumuman penting).</li>
          <li>Jika ada beberapa splash aktif di waktu yang sama, hanya yang <strong>urutan terkecil</strong> yang ditampilkan.</li>
        </ul>
      </div>

      <!-- Empty State -->
      <div v-if="splashScreens.length === 0" class="text-center py-16 text-gray-400 dark:text-zinc-500">
        <Layers class="w-10 h-10 mx-auto mb-3 opacity-40" />
        <p class="font-semibold">Belum ada splash screen</p>
        <p class="text-sm mt-1">Klik tombol "Tambah Splash Screen" untuk membuat yang pertama.</p>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <div
          v-for="item in splashScreens"
          :key="item.id"
          class="bg-white dark:bg-zinc-900 border rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center gap-4"
          :class="item.is_currently_active
            ? 'border-emerald-300 dark:border-emerald-800'
            : 'border-gray-100 dark:border-zinc-800'"
        >
          <!-- Preview Thumbnail (for image type) -->
          <div v-if="item.content_type === 'image' && item.image_path" class="shrink-0">
            <img :src="`/storage/${item.image_path}`" class="w-20 h-16 object-cover rounded-xl border border-gray-100 dark:border-zinc-700" />
          </div>
          <!-- Content Type Icon -->
          <div v-else class="shrink-0 w-14 h-14 rounded-xl flex items-center justify-center text-2xl"
            :class="{
              'bg-blue-50 dark:bg-blue-950/40': item.content_type === 'ikm_widget',
              'bg-amber-50 dark:bg-amber-950/40': item.content_type === 'announcement',
              'bg-purple-50 dark:bg-purple-950/40': item.content_type === 'custom',
              'bg-gray-50 dark:bg-zinc-800': item.content_type === 'image',
            }"
          >
            {{ contentTypeEmoji(item.content_type) }}
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-50 truncate">{{ item.title }}</h3>
              <!-- Status Badge -->
              <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                :class="{
                  'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': item.status_label === 'Aktif',
                  'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': item.status_label === 'Terjadwal',
                  'bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400': item.status_label === 'Nonaktif',
                  'bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400': item.status_label === 'Kedaluwarsa',
                }"
              >{{ item.status_label }}</span>
              <span class="px-2 py-0.5 bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 rounded text-[10px] font-bold uppercase tracking-wider">
                {{ contentTypeLabel(item.content_type) }}
              </span>
            </div>
            <p v-if="item.subtitle" class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5 truncate">{{ item.subtitle }}</p>
            <div class="flex items-center gap-3 mt-1.5 text-[11px] text-gray-400 dark:text-zinc-500 flex-wrap">
              <span>⚡ {{ frequencyLabel(item.show_frequency) }}</span>
              <span v-if="item.start_date || item.end_date">
                📅 {{ item.start_date ? formatDate(item.start_date) : '∞' }} — {{ item.end_date ? formatDate(item.end_date) : '∞' }}
              </span>
              <span v-else>📅 Tanpa batas waktu</span>
              <span>🔢 Urutan: {{ item.sort_order }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 shrink-0">
            <!-- Toggle Active -->
            <button
              @click="toggleActive(item)"
              class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
              :class="item.is_active
                ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800'
                : 'bg-gray-50 hover:bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700'"
            >
              {{ item.is_active ? '✅ Aktif' : '⭕ Nonaktif' }}
            </button>
            <!-- Edit -->
            <button @click="openEdit(item)" class="p-2 rounded-lg bg-gray-50 dark:bg-zinc-800 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-gray-500 hover:text-blue-600 transition cursor-pointer border border-gray-200 dark:border-zinc-700">
              <Pencil class="w-4 h-4" />
            </button>
            <!-- Delete -->
            <button @click="confirmDelete(item)" class="p-2 rounded-lg bg-gray-50 dark:bg-zinc-800 hover:bg-red-50 dark:hover:bg-red-950/40 text-gray-500 hover:text-red-600 transition cursor-pointer border border-gray-200 dark:border-zinc-700">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== MODAL FORM ===== -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-100 dark:border-zinc-800">
          <div class="p-6 border-b border-gray-100 dark:border-zinc-800 flex items-center justify-between">
            <h2 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">
              {{ isEditing ? '✏️ Edit Splash Screen' : '➕ Tambah Splash Screen' }}
            </h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-700 dark:hover:text-zinc-200 cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-5">
            <!-- Title -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Judul Splash Screen *</label>
              <input v-model="form.title" type="text" required placeholder="cth: Survei Kepuasan Masyarakat 2026"
                class="w-full px-4 py-2.5 border border-gray-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>

            <!-- Subtitle -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Subjudul / Deskripsi Singkat</label>
              <input v-model="form.subtitle" type="text" placeholder="Deskripsi singkat (opsional)"
                class="w-full px-4 py-2.5 border border-gray-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>

            <!-- Content Type -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-2">Jenis Konten *</label>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <label v-for="type in contentTypes" :key="type.value"
                  class="flex flex-col items-center gap-1.5 p-3 rounded-xl border cursor-pointer transition text-center"
                  :class="form.content_type === type.value
                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/40'
                    : 'border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-primary-300'"
                >
                  <input type="radio" v-model="form.content_type" :value="type.value" class="sr-only" />
                  <span class="text-2xl">{{ type.emoji }}</span>
                  <span class="text-[11px] font-bold text-gray-700 dark:text-zinc-300">{{ type.label }}</span>
                </label>
              </div>
            </div>

            <!-- Custom Content (for announcement / custom) -->
            <div v-if="form.content_type === 'announcement' || form.content_type === 'custom'">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">
                {{ form.content_type === 'custom' ? 'Konten HTML' : 'Teks Pengumuman' }} *
              </label>
              <textarea v-model="form.custom_content" rows="5" :placeholder="form.content_type === 'custom' ? '<p>Konten HTML...</p>' : 'Isi pengumuman yang ingin ditampilkan...'"
                class="w-full px-4 py-2.5 border border-gray-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none font-mono"
              ></textarea>
            </div>

            <!-- Image Upload (for image type) -->
            <div v-if="form.content_type === 'image'">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Upload Gambar</label>
              <input type="file" ref="imageInput" accept="image/*" @change="onImageChange"
                class="w-full text-sm text-gray-700 dark:text-zinc-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-300 hover:file:bg-primary-100 cursor-pointer" />
              <div v-if="imagePreview || (isEditing && editItem?.image_path)" class="mt-2">
                <img :src="imagePreview || `/storage/${editItem?.image_path}`" class="h-32 rounded-xl object-cover border border-gray-200 dark:border-zinc-700" />
              </div>
            </div>

            <!-- Button Label & URL -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Label Tombol Aksi</label>
                <input v-model="form.button_label" type="text" placeholder="cth: Beri Penilaian Sekarang"
                  class="w-full px-4 py-2.5 border border-gray-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">URL Tombol Aksi</label>
                <input v-model="form.button_url" type="url" placeholder="https://..."
                  class="w-full px-4 py-2.5 border border-gray-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
              </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.button_external" class="rounded" />
              <span class="text-xs text-gray-600 dark:text-zinc-400">Buka URL di tab baru</span>
            </label>

            <!-- Show Frequency -->
            <div>
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-2">Frekuensi Tampil *</label>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <label v-for="freq in frequencies" :key="freq.value"
                  class="flex items-start gap-2.5 p-3 rounded-xl border cursor-pointer transition"
                  :class="form.show_frequency === freq.value
                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/40'
                    : 'border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-primary-300'"
                >
                  <input type="radio" v-model="form.show_frequency" :value="freq.value" class="mt-0.5 shrink-0" />
                  <div>
                    <p class="text-xs font-bold text-gray-800 dark:text-zinc-200">{{ freq.label }}</p>
                    <p class="text-[10px] text-gray-500 dark:text-zinc-500 mt-0.5">{{ freq.desc }}</p>
                  </div>
                </label>
              </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Tanggal Mulai Aktif</label>
                <input v-model="form.start_date" type="date"
                  class="w-full px-4 py-2.5 border border-gray-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                <p class="text-[10px] text-gray-400 mt-1">Kosongkan = mulai sekarang</p>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Tanggal Selesai Aktif</label>
                <input v-model="form.end_date" type="date"
                  class="w-full px-4 py-2.5 border border-gray-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                <p class="text-[10px] text-gray-400 mt-1">Kosongkan = tanpa batas waktu</p>
              </div>
            </div>

            <!-- Sort Order & Active -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Urutan (Sort Order)</label>
                <input v-model.number="form.sort_order" type="number" min="0" placeholder="0"
                  class="w-full px-4 py-2.5 border border-gray-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                <p class="text-[10px] text-gray-400 mt-1">Angka kecil = prioritas lebih tinggi</p>
              </div>
              <div class="flex items-center pt-6">
                <label class="flex items-center gap-3 cursor-pointer">
                  <div class="relative">
                    <input type="checkbox" v-model="form.is_active" class="sr-only peer" />
                    <div class="w-11 h-6 bg-gray-200 dark:bg-zinc-700 peer-checked:bg-primary-600 rounded-full transition-colors"></div>
                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                  </div>
                  <span class="text-sm font-bold text-gray-700 dark:text-zinc-300">{{ form.is_active ? '✅ Aktif' : '⭕ Nonaktif' }}</span>
                </label>
              </div>
            </div>

            <!-- Error -->
            <div v-if="errors && Object.keys(errors).length" class="p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 rounded-xl text-xs text-red-700 dark:text-red-400 space-y-1">
              <p v-for="(err, field) in errors" :key="field"><strong>{{ field }}:</strong> {{ Array.isArray(err) ? err[0] : err }}</p>
            </div>

            <!-- Footer Buttons -->
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="closeModal" class="px-5 py-2 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 text-sm font-bold rounded-xl transition cursor-pointer">
                Batal
              </button>
              <button type="submit" :disabled="submitting" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white text-sm font-bold rounded-xl transition cursor-pointer flex items-center gap-2">
                <span v-if="submitting">Menyimpan...</span>
                <span v-else>{{ isEditing ? 'Simpan Perubahan' : 'Tambahkan' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete Confirm Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl w-full max-w-sm border border-gray-100 dark:border-zinc-800 p-6 space-y-4">
          <div class="text-center">
            <div class="text-4xl mb-3">🗑️</div>
            <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">Hapus Splash Screen?</h3>
            <p class="text-sm text-gray-500 dark:text-zinc-400 mt-1">
              "<strong>{{ deleteTarget?.title }}</strong>" akan dihapus permanen.
            </p>
          </div>
          <div class="flex gap-3">
            <button @click="showDeleteModal = false" class="flex-1 py-2 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 text-sm font-bold rounded-xl transition cursor-pointer">Batal</button>
            <button @click="deleteItem" class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl transition cursor-pointer">Hapus</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Plus, Pencil, Trash2, X, Info, Layers, CheckCircle2 } from '@lucide/vue'

const props = defineProps({
  splashScreens: Array,
})

// ---- Modal State ----
const showModal = ref(false)
const isEditing = ref(false)
const editItem = ref(null)
const submitting = ref(false)
const errors = ref({})
const imagePreview = ref(null)
const imageInput = ref(null)
const imageFile = ref(null)

const showDeleteModal = ref(false)
const deleteTarget = ref(null)

const defaultForm = () => ({
  title: '',
  subtitle: '',
  content_type: 'ikm_widget',
  custom_content: '',
  button_label: '',
  button_url: '',
  button_external: false,
  show_frequency: 'once_per_day',
  start_date: '',
  end_date: '',
  is_active: true,
  sort_order: 0,
})

const form = reactive(defaultForm())

function openCreate() {
  Object.assign(form, defaultForm())
  isEditing.value = false
  editItem.value = null
  imagePreview.value = null
  imageFile.value = null
  errors.value = {}
  showModal.value = true
}

function openEdit(item) {
  Object.assign(form, {
    title: item.title || '',
    subtitle: item.subtitle || '',
    content_type: item.content_type || 'ikm_widget',
    custom_content: item.custom_content || '',
    button_label: item.button_label || '',
    button_url: item.button_url || '',
    button_external: item.button_external || false,
    show_frequency: item.show_frequency || 'once_per_day',
    start_date: item.start_date ? item.start_date.substring(0, 10) : '',
    end_date: item.end_date ? item.end_date.substring(0, 10) : '',
    is_active: item.is_active,
    sort_order: item.sort_order || 0,
  })
  isEditing.value = true
  editItem.value = item
  imagePreview.value = null
  imageFile.value = null
  errors.value = {}
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function onImageChange(e) {
  const file = e.target.files[0]
  if (!file) return
  imageFile.value = file
  const reader = new FileReader()
  reader.onload = (ev) => { imagePreview.value = ev.target.result }
  reader.readAsDataURL(file)
}

function submitForm() {
  submitting.value = true
  errors.value = {}

  const data = new FormData()
  Object.entries(form).forEach(([k, v]) => {
    if (v !== null && v !== undefined && v !== '') data.append(k, v)
  })
  if (imageFile.value) data.append('image', imageFile.value)

  if (isEditing.value) {
    data.append('_method', 'PUT')
    router.post(route('admin.splash-screens.update', editItem.value.id), data, {
      onError: (e) => { errors.value = e },
      onFinish: () => { submitting.value = false },
      onSuccess: () => { showModal.value = false },
    })
  } else {
    router.post(route('admin.splash-screens.store'), data, {
      onError: (e) => { errors.value = e },
      onFinish: () => { submitting.value = false },
      onSuccess: () => { showModal.value = false },
    })
  }
}

function toggleActive(item) {
  router.post(route('admin.splash-screens.toggle', item.id))
}

function confirmDelete(item) {
  deleteTarget.value = item
  showDeleteModal.value = true
}

function deleteItem() {
  router.delete(route('admin.splash-screens.destroy', deleteTarget.value.id), {
    onSuccess: () => { showDeleteModal.value = false },
  })
}

// ---- Helpers ----
const contentTypes = [
  { value: 'ikm_widget', emoji: '📊', label: 'Widget IKM' },
  { value: 'announcement', emoji: '📢', label: 'Pengumuman' },
  { value: 'image', emoji: '🖼️', label: 'Gambar' },
  { value: 'custom', emoji: '⚙️', label: 'HTML Kustom' },
]

const frequencies = [
  { value: 'once_per_session', label: 'Sekali Per Sesi', desc: 'Muncul 1x selama browser dibuka' },
  { value: 'once_per_day', label: 'Sekali Per Hari', desc: 'Muncul 1x per hari per perangkat' },
  { value: 'always', label: 'Selalu Tampil', desc: 'Muncul setiap buka halaman' },
]

function contentTypeEmoji(type) {
  return contentTypes.find(t => t.value === type)?.emoji || '📋'
}

function contentTypeLabel(type) {
  return contentTypes.find(t => t.value === type)?.label || type
}

function frequencyLabel(freq) {
  return frequencies.find(f => f.value === freq)?.label || freq
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
