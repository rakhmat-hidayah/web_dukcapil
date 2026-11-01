<template>
  <Head title="User & Roles" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Title section -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Manajemen User & Roles</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Kelola operator website, editor konten, dan hak akses mereka.
          </p>
        </div>
        <button
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition"
        >
          <Plus class="w-4 h-4" />
          Tambah Operator
        </button>
      </div>

      <!-- Search Bar -->
      <div class="relative max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-zinc-500" />
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Cari username, nama, atau email..."
          class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-800 dark:text-zinc-200 placeholder-gray-400 dark:placeholder-zinc-500"
        />
      </div>

      <!-- Users Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Username / Nama</th>
                <th class="px-6 py-4">Email</th>
                <th class="px-6 py-4">Peran (Role)</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Login Terakhir</th>
                <th class="px-6 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr
                v-for="user in filteredUsers"
                :key="user.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition duration-150"
              >
                <!-- Username / Name -->
                <td class="px-6 py-4">
                  <div class="font-mono font-bold text-primary-600 dark:text-primary-400 text-xs">@{{ user.username }}</div>
                  <div class="text-gray-600 dark:text-zinc-400 font-medium mt-0.5">{{ user.name }}</div>
                </td>

                <!-- Email -->
                <td class="px-6 py-4 text-gray-500 dark:text-zinc-500 font-medium">
                  {{ user.email }}
                </td>

                <!-- Single Role Badge -->
                <td class="px-6 py-4">
                  <span
                    class="px-3 py-1 rounded-lg font-bold text-[11px] tracking-wide inline-block"
                    :class="getRoleClass(user.role)"
                  >
                    {{ getRoleTitle(user.role) }}
                  </span>
                </td>

                <!-- Active Status -->
                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold inline-flex items-center gap-1"
                    :class="user.is_active
                      ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                      : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>

                <!-- Last Login -->
                <td class="px-6 py-4 text-gray-400 dark:text-zinc-500 font-medium">
                  {{ user.last_login_at }}
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button
                      v-if="user.two_factor_enabled"
                      @click="openReset2faModal(user)"
                      class="p-1.5 hover:bg-amber-50 dark:hover:bg-amber-950/20 text-amber-600 hover:text-amber-700 dark:text-amber-400 rounded-lg transition flex items-center gap-1 text-[11px] font-bold"
                      title="Reset 2FA Operator"
                    >
                      <ShieldOff class="w-4 h-4" />
                    </button>
                    <button
                      @click="openEditModal(user)"
                      class="p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 rounded-lg transition"
                      title="Edit Operator"
                    >
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button
                      @click="confirmDeleteUser(user)"
                      class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition"
                      title="Hapus Operator"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <!-- Empty State -->
              <tr v-if="filteredUsers.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-zinc-500 text-xs">
                  <UserX class="w-8 h-8 mx-auto mb-2 opacity-40" />
                  Tidak ada operator ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Create / Edit Modal -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 relative text-left max-h-[90vh] overflow-y-auto">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 mb-4">
              {{ editingUser ? 'Edit Operator' : 'Tambah Operator Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">

              <!-- Username -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                  Username <span class="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  v-model="form.username"
                  required
                  placeholder="contoh: operator01"
                  autocomplete="off"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-primary-500"
                  @input="form.username = form.username.toLowerCase().replace(/[^a-z0-9_]/g, '')"
                />
                <p class="text-[10px] text-gray-400 mt-0.5">Huruf kecil, angka, underscore. 4–30 karakter.</p>
                <span v-if="form.errors.username" class="text-[10px] text-red-500 mt-0.5 block">{{ form.errors.username }}</span>
              </div>

              <!-- Name -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input
                  type="text"
                  v-model="form.name"
                  required
                  placeholder="Nama Lengkap Operator"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <span v-if="form.errors.name" class="text-[10px] text-red-500 mt-0.5 block">{{ form.errors.name }}</span>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Email <span class="text-rose-500">*</span></label>
                <input
                  type="email"
                  v-model="form.email"
                  required
                  placeholder="operator@dompukab.go.id"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <span v-if="form.errors.email" class="text-[10px] text-red-500 mt-0.5 block">{{ form.errors.email }}</span>
              </div>

              <!-- Password -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                  Kata Sandi
                  <span v-if="editingUser" class="text-[10px] text-gray-400 font-normal">(Kosongkan jika tidak diubah)</span>
                  <span v-else class="text-rose-500"> *</span>
                </label>
                <div class="relative">
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    v-model="form.password"
                    :required="!editingUser"
                    placeholder="Min. 8 karakter"
                    autocomplete="new-password"
                    class="w-full pl-3.5 pr-10 py-2.5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 transition focus:outline-none p-1"
                  >
                    <Eye v-if="!showPassword" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
                <span v-if="form.errors.password" class="text-[10px] text-red-500 mt-0.5 block">{{ form.errors.password }}</span>
              </div>

              <!-- Active Status (only visible on edit) -->
              <div v-if="editingUser" class="flex items-center gap-3">
                <label class="block text-xs font-semibold text-gray-500">Status Akun</label>
                <label class="flex items-center gap-2 cursor-pointer select-none">
                  <input type="checkbox" v-model="form.is_active" class="accent-primary-500 w-4 h-4 rounded" />
                  <span class="text-xs font-medium" :class="form.is_active ? 'text-emerald-600' : 'text-rose-500'">
                    {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </label>
              </div>

              <!-- Single Role Radio list -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Peran &amp; Hak Akses (Single Role)</label>
                <div class="grid grid-cols-1 gap-2 max-h-52 overflow-y-auto p-2 bg-gray-50 dark:bg-zinc-800/30 rounded-2xl border border-gray-100 dark:border-zinc-800 scrollbar-thin">
                  <label
                    v-for="role in roles"
                    :key="role"
                    class="flex items-start gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-zinc-800/60 rounded-xl cursor-pointer text-xs transition border border-transparent hover:border-gray-200 dark:hover:border-zinc-700"
                    :class="{ 'bg-primary-50/50 dark:bg-primary-950/20 border-primary-200 dark:border-primary-800/40': form.role === role || getRoleTitle(form.role) === getRoleTitle(role) }"
                  >
                    <input
                      type="radio"
                      :value="role"
                      v-model="form.role"
                      name="user_role"
                      class="accent-primary-500 mt-0.5 h-4 w-4 shrink-0 cursor-pointer"
                    />
                    <div class="flex-1">
                      <div class="font-bold text-gray-800 dark:text-zinc-200">{{ getRoleTitle(role) }}</div>
                      <div class="text-[10px] text-gray-400 dark:text-zinc-500 mt-0.5">
                        {{ getRoleDescription(role) }}
                      </div>
                    </div>
                  </label>
                </div>
                <span v-if="form.errors.role" class="text-[10px] text-red-500 mt-1 block">{{ form.errors.role }}</span>
              </div>

              <!-- Footer action buttons -->
              <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-6">
                <button
                  type="button"
                  @click="closeModal"
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition disabled:opacity-50"
                >
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Operator' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- Delete Confirmation Modal -->
      <transition name="fade">
        <div v-if="deleteModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 relative text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100 dark:border-rose-900/30">
              <Trash2 class="w-6 h-6" />
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 mb-2">Hapus Akun Operator?</h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mb-6">
              Apakah Anda yakin ingin menghapus operator
              <strong class="text-gray-800 dark:text-zinc-200 font-mono">@{{ userToDelete?.username }}</strong>
              ({{ userToDelete?.name }})? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex justify-center gap-3">
              <button
                type="button"
                @click="deleteModalOpen = false"
                class="px-4 py-2.5 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
              >
                Batal
              </button>
              <button
                type="button"
                @click="executeDelete"
                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 active:scale-95 transition"
              >
                Ya, Hapus Operator
              </button>
            </div>
          </div>
        </div>
      </transition>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Pencil, Trash2, Eye, EyeOff, Search, UserX } from '@lucide/vue';

const props = defineProps({
  users:  Array,
  roles:  Array,
  search: String,
});

const searchQuery     = ref(props.search || '');
const modalOpen       = ref(false);
const editingUser     = ref(null);
const deleteModalOpen = ref(false);
const userToDelete    = ref(null);
const showPassword    = ref(false);

// Client-side search filter (supplement to server-side)
const filteredUsers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return props.users;
  return props.users.filter(u =>
    u.username?.toLowerCase().includes(q) ||
    u.name?.toLowerCase().includes(q) ||
    u.email?.toLowerCase().includes(q)
  );
});

const form = useForm({
  username:  '',
  name:      '',
  email:     '',
  password:  '',
  role:      'Editor Konten',
  is_active: true,
});

const getRoleTitle = (role) => {
  if (!role) return '';
  const map = {
    'Super Administrator': 'Administrator Utama',
    'Website Administrator': 'Administrator Website',
    'Content Editor': 'Editor Konten',
    'News Operator': 'Operator Berita',
    'Publication Operator': 'Operator Publikasi',
    'Complaint Officer': 'Petugas Pengaduan',
    'Media Manager': 'Pengelola Media',
    'PPID Admin': 'Admin PPID',
    'Queue Officer': 'Petugas Verifikasi / Registrasi KTP-el',
    'Queue Verifier': 'Petugas Verifikasi / Registrasi KTP-el',
    'petugas_pengambilan_ktpel': 'Petugas Pengambilan KTP-el',
    'operator_arsip': 'Petugas Pengarsipan',
    'supervisor_layanan_ktpel': 'Supervisor Layanan KTP-el',
  };
  return map[role] || role;
};

const getRoleClass = (role) => {
  switch (role) {
    case 'Supervisor Layanan KTP-el':
      return 'bg-blue-50 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40 font-bold';
    case 'Administrator Utama':
    case 'Super Administrator':
      return 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-100 dark:border-red-900/30';
    case 'Administrator Website':
    case 'Website Administrator':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30';
    case 'Editor Konten':
    case 'Content Editor':
      return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30';
    case 'Operator Berita':
    case 'News Operator':
      return 'bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-400 border border-orange-100 dark:border-orange-900/30';
    case 'Pengelola Media':
    case 'Media Manager':
      return 'bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-400 border border-violet-100 dark:border-violet-900/30';
    case 'Operator Publikasi':
    case 'Publication Operator':
      return 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400 border border-sky-100 dark:border-sky-900/30';
    case 'Petugas Pengaduan':
    case 'Complaint Officer':
      return 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-100 dark:border-purple-900/30';
    case 'Admin PPID':
    case 'PPID Admin':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30';
    case 'Petugas Verifikasi / Registrasi KTP-el':
      return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30';
    case 'Petugas Pengambilan KTP-el':
      return 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border border-teal-100 dark:border-teal-900/30';
    case 'Petugas Pengarsipan':
      return 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400 border border-cyan-100 dark:border-cyan-900/30';
    default:
      return 'bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700';
  }
};

const getRoleDescription = (role) => {
  switch (role) {
    case 'Administrator Utama':
    case 'Super Administrator':
      return 'Akses penuh tanpa batas ke seluruh modul CMS, Manajemen Pengguna, dan Pengaturan Sistem.';
    case 'Administrator Website':
    case 'Website Administrator':
      return 'Akses operasional penuh ke seluruh modul CMS (Konten, Layanan, Pengaduan, PPID, Demografi), kecuali Manajemen Pengguna.';
    case 'Editor Konten':
    case 'Content Editor':
      return 'Mengelola Berita, Artikel, Galeri Album, Pengumuman, Banner Slider, Halaman Dinamis, dan File Manager.';
    case 'Operator Berita':
    case 'News Operator':
      return 'Mengelola pembuatan, penyuntingan, dan publikasi Berita & Artikel resmi instansi.';
    case 'Pengelola Media':
    case 'Media Manager':
      return 'Mengelola penyimpanan berkas, foto, dan media digital pada Media & File Manager.';
    case 'Operator Publikasi':
    case 'Publication Operator':
      return 'Mengelola berkas publikasi, dokumen resmi, dan dokumen di Download Center.';
    case 'Petugas Pengaduan':
    case 'Complaint Officer':
      return 'Mengelola Layanan Pengaduan Masyarakat, tindak lanjut aspirasi, dan Survei Kepuasan (IKM).';
    case 'Admin PPID':
    case 'PPID Admin':
      return 'Mengelola Layanan Keterbukaan Informasi Publik PPID, permohonan informasi, dan dokumen informasi berkala.';
    case 'Petugas Verifikasi / Registrasi KTP-el':
      return 'Memverifikasi berkas pendaftaran online, pemeriksaan fisik dokumen asli di loket, dan penerbitan jadwal/antrean KTP-el.';
    case 'Petugas Pengambilan KTP-el':
      return 'Pemeriksaan kesiapan fisik kartu, verifikasi identitas penerima KTP-el, foto bukti penyerahan dengan kamera, dan penanganan kendala pengambilan.';
    case 'Petugas Pengarsipan':
      return 'Mengelola arsip berkas permohonan selesai, pembuatan paket ZIP dengan verifikasi SHA-256, unduh arsip, dan konfirmasi penyimpanan permanen.';
    case 'Supervisor Layanan KTP-el':
      return 'Akses pengawasan menyeluruh ke semua modul KTP-el (Antrian & Verifikasi Dokumen, Serah Terima Fisik Kartu, dan Pengarsipan Permohonan ZIP).';
    default:
      return 'Akses operator standar sistem.';
  }
};

const openCreateModal = () => {
  editingUser.value = null;
  form.reset();
  form.role      = props.roles?.includes('Editor Konten') ? 'Editor Konten' : (props.roles?.[0] || 'Editor Konten');
  form.is_active = true;
  form.clearErrors();
  showPassword.value = false;
  modalOpen.value    = true;
};

const openEditModal = (user) => {
  editingUser.value = user;
  form.username  = user.username;
  form.name      = user.name;
  form.email     = user.email;
  form.password  = '';
  form.role      = user.role || 'Editor Konten';
  form.is_active = user.is_active;
  form.clearErrors();
  showPassword.value = false;
  modalOpen.value    = true;
};

const closeModal = () => {
  modalOpen.value = false;
};

const submit = () => {
  if (editingUser.value) {
    form.put(route('admin.users.update', editingUser.value.id), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('admin.users.store'), {
      onSuccess: () => closeModal(),
    });
  }
};

const confirmDeleteUser = (user) => {
  userToDelete.value  = user;
  deleteModalOpen.value = true;
};

const executeDelete = () => {
  if (userToDelete.value) {
    router.delete(route('admin.users.destroy', userToDelete.value.id), {
      onSuccess: () => {
        deleteModalOpen.value = false;
        userToDelete.value    = null;
      },
    });
  }
};
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
