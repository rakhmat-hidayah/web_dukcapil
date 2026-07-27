<template>
  <Head title="User & Roles" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Title section -->
      <div class="flex justify-between items-center">
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

      <!-- Users Table Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-zinc-800">
                <th class="px-6 py-4">Nama Operator</th>
                <th class="px-6 py-4">Email</th>
                <th class="px-6 py-4">Peran (Roles)</th>
                <th class="px-6 py-4">Tgl Terdaftar</th>
                <th class="px-6 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
              <tr 
                v-for="user in users" 
                :key="user.id"
                class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/20 transition duration-150"
              >
                <!-- Name -->
                <td class="px-6 py-4 font-bold text-gray-800 dark:text-zinc-200">
                  {{ user.name }}
                </td>
                
                <!-- Email -->
                <td class="px-6 py-4 text-gray-600 dark:text-zinc-400 font-medium">
                  {{ user.email }}
                </td>

                <!-- Roles -->
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1.5">
                    <span 
                      v-for="role in user.roles" 
                      :key="role"
                      class="px-2.5 py-1 rounded-lg font-bold text-[11px] tracking-wide"
                      :class="getRoleClass(role)"
                    >
                      {{ role }}
                    </span>
                  </div>
                </td>

                <!-- Created At -->
                <td class="px-6 py-4 text-gray-400 dark:text-zinc-500 font-medium">
                  {{ user.created_at }}
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
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
            </tbody>
          </table>
        </div>
      </div>

      <!-- Create / Edit Modal -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 relative text-left">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 mb-4">
              {{ editingUser ? 'Edit Hak Akses Operator' : 'Tambah Operator Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Operator</label>
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
                <label class="block text-xs font-semibold text-gray-500 mb-1">Email Operator</label>
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
                  Kata Sandi <span v-if="editingUser" class="text-[10px] text-gray-400 font-normal">(Kosongkan jika tidak diubah)</span>
                </label>
                <input 
                  type="password" 
                  v-model="form.password" 
                  :required="!editingUser"
                  placeholder="Min. 8 karakter"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <span v-if="form.errors.password" class="text-[10px] text-red-500 mt-0.5 block">{{ form.errors.password }}</span>
              </div>

              <!-- Roles checkbox list -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Peran &amp; Hak Akses (Roles)</label>
                <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto p-2 bg-gray-50 dark:bg-zinc-800/30 rounded-2xl border border-gray-100 dark:border-zinc-800 scrollbar-thin">
                  <label 
                    v-for="role in roles" 
                    :key="role" 
                    class="flex items-start gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-zinc-800/60 rounded-xl cursor-pointer text-xs transition border border-transparent hover:border-gray-200 dark:hover:border-zinc-700"
                  >
                    <input 
                      type="checkbox" 
                      :value="role" 
                      v-model="form.roles" 
                      class="accent-primary-500 rounded border-gray-300 dark:border-zinc-700 mt-0.5 h-4 w-4 shrink-0" 
                    />
                    <div class="flex-1">
                      <div class="font-bold text-gray-800 dark:text-zinc-200">{{ role }}</div>
                      <div class="text-[10px] text-gray-400 dark:text-zinc-500 mt-0.5">
                        {{ getRoleDescription(role) }}
                      </div>
                    </div>
                  </label>
                </div>
                <span v-if="form.errors.roles" class="text-[10px] text-red-500 mt-1 block">{{ form.errors.roles }}</span>
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

      <!-- Custom Vue Confirmation Modal for Delete -->
      <transition name="fade">
        <div v-if="deleteModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 relative text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100 dark:border-rose-900/30">
              <Trash2 class="w-6 h-6" />
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 mb-2">Hapus Akun Operator?</h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mb-6">
              Apakah Anda yakin ingin menghapus operator <strong class="text-gray-800 dark:text-zinc-200">{{ userToDelete?.name }}</strong>? Tindakan ini tidak dapat dibatalkan.
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
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Pencil, Trash2 } from '@lucide/vue';

const props = defineProps({
  users: Array,
  roles: Array,
});

const modalOpen = ref(false);
const editingUser = ref(null);
const deleteModalOpen = ref(false);
const userToDelete = ref(null);

const form = useForm({
  name: '',
  email: '',
  password: '',
  roles: [],
});

const getRoleClass = (role) => {
  switch (role) {
    case 'Super Administrator':
      return 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-100 dark:border-red-900/30';
    case 'Website Administrator':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30';
    case 'Content Editor':
      return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30';
    case 'Complaint Officer':
      return 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-100 dark:border-purple-900/30';
    case 'PPID Admin':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30';
    default:
      return 'bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700';
  }
};

const getRoleDescription = (role) => {
  switch (role) {
    case 'Super Administrator':
      return 'Akses penuh tanpa batas ke seluruh modul CMS dan Pengaturan Sistem.';
    case 'Website Administrator':
      return 'Mengelola konten website, halaman, dan konfigurasi umum.';
    case 'Content Editor':
      return 'Mengelola Berita, Artikel, Galeri Album, Pengumuman, dan FAQ.';
    case 'Complaint Officer':
      return 'Mengelola Layanan Pengaduan Masyarakat & Survei Kepuasan (IKM).';
    case 'PPID Admin':
      return 'Mengelola Keterbukaan Informasi Publik PPID & Dokumen Publik.';
    default:
      return 'Akses operator standar sistem.';
  }
};

const openCreateModal = () => {
  editingUser.value = null;
  form.reset();
  form.clearErrors();
  modalOpen.value = true;
};

const openEditModal = (user) => {
  editingUser.value = user;
  form.name = user.name;
  form.email = user.email;
  form.password = '';
  form.roles = [...user.roles];
  form.clearErrors();
  modalOpen.value = true;
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
  userToDelete.value = user;
  deleteModalOpen.value = true;
};

const executeDelete = () => {
  if (userToDelete.value) {
    router.delete(route('admin.users.destroy', userToDelete.value.id), {
      onSuccess: () => {
        deleteModalOpen.value = false;
        userToDelete.value = null;
      }
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
