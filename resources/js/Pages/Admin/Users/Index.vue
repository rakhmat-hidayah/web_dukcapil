<template>
  <Head title="Manajemen Pengguna & Peran - Disdukcapil Kab. Dompu" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Page Header & Action Controls -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-400/20 mb-2">
            <ShieldCheck class="w-3.5 h-3.5" />
            <span>Kontrol Otorisasi & Hak Akses Berbasis Peran (RBAC)</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-zinc-50 tracking-tight">
            Manajemen Pengguna & Peran
          </h1>
          <p class="text-xs sm:text-sm text-gray-500 dark:text-zinc-400 mt-1">
            Kelola akun operator, tetapkan peran per modul, serta konfigurasi hak akses fitur website Disdukcapil Dompu.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <button
            v-if="activeTab === 'users'"
            @click="openCreateModal"
            class="flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/20 active:scale-95 transition cursor-pointer"
          >
            <Plus class="w-4 h-4 stroke-[2.5]" />
            <span>Tambah Operator Baru</span>
          </button>

          <button
            v-if="activeTab === 'roles'"
            @click="openCreateRoleModal"
            class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 active:scale-95 transition cursor-pointer"
          >
            <Plus class="w-4 h-4 stroke-[2.5]" />
            <span>Tambah Peran Baru</span>
          </button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-2 border-b border-gray-200 dark:border-zinc-800">
        <button
          type="button"
          @click="activeTab = 'users'"
          class="pb-3 px-3 text-xs sm:text-sm font-extrabold flex items-center gap-2 border-b-2 transition cursor-pointer"
          :class="activeTab === 'users'
            ? 'border-primary-600 text-primary-600 dark:text-primary-400'
            : 'border-transparent text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-200'"
        >
          <Users class="w-4 h-4" />
          <span>Daftar Operator / Pengguna</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300">
            {{ filteredUsers.length }}
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'roles'"
          class="pb-3 px-3 text-xs sm:text-sm font-extrabold flex items-center gap-2 border-b-2 transition cursor-pointer"
          :class="activeTab === 'roles'
            ? 'border-primary-600 text-primary-600 dark:text-primary-400'
            : 'border-transparent text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-200'"
        >
          <Shield class="w-4 h-4" />
          <span>Master Peran &amp; Hak Akses Modul Web</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300">
            {{ roles.length }}
          </span>
        </button>
      </div>

      <!-- ========================================================================= -->
      <!-- TAB 1: DAFTAR OPERATOR / PENGGUNA                                         -->
      <!-- ========================================================================= -->
      <div v-if="activeTab === 'users'" class="space-y-4">
        <!-- Search Bar -->
        <div class="relative max-w-md">
          <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-zinc-500" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari username, nama lengkap, atau email..."
            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-800 dark:text-zinc-200 placeholder-gray-400 dark:placeholder-zinc-500 shadow-2xs"
          />
        </div>

        <!-- Users Table Card -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-gray-50 dark:bg-zinc-800/40 text-gray-500 dark:text-zinc-400 uppercase tracking-wider font-extrabold text-[11px] border-b border-gray-100 dark:border-zinc-800">
                  <th class="px-6 py-4">Username / Nama</th>
                  <th class="px-6 py-4">Email</th>
                  <th class="px-6 py-4">Peran (Role) Sesuai Modul</th>
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
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-mono font-bold text-primary-600 dark:text-primary-400 text-xs">
                      @{{ user.username }}
                    </div>
                    <div class="text-gray-700 dark:text-zinc-300 font-bold mt-0.5">
                      {{ user.name }}
                    </div>
                  </td>

                  <!-- Email -->
                  <td class="px-6 py-4 text-gray-500 dark:text-zinc-400 font-medium whitespace-nowrap">
                    {{ user.email }}
                  </td>

                  <!-- Roles Badges (Multi-role support & clickable quick action) -->
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1.5 items-center max-w-sm">
                      <span
                        v-for="r in (user.roles?.length ? user.roles : [user.role])"
                        :key="r"
                        @click="openUserRolesModal(user)"
                        class="px-2.5 py-1 rounded-lg font-bold text-[11px] tracking-wide inline-flex items-center gap-1 cursor-pointer hover:opacity-80 active:scale-95 transition shadow-2xs"
                        :class="getRoleClass(r)"
                        title="Klik untuk ubah/tambah/kurangi peran operator ini"
                      >
                        <Shield class="w-3 h-3 opacity-60" />
                        <span>{{ getRoleTitle(r) }}</span>
                      </span>
                    </div>
                  </td>

                  <!-- Active Status -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2.5 py-1 rounded-lg text-[11px] font-bold inline-flex items-center gap-1"
                      :class="user.is_active
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40'
                        : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40'"
                    >
                      <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                      {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>

                  <!-- Last Login -->
                  <td class="px-6 py-4 text-gray-400 dark:text-zinc-500 font-medium whitespace-nowrap">
                    {{ user.last_login_at }}
                  </td>

                  <!-- Actions -->
                  <td class="px-6 py-4 text-right whitespace-nowrap">
                    <div class="flex justify-end items-center gap-1.5">
                      <!-- Action 1: Ubah / Tambah / Kurangi Peran -->
                      <button
                        type="button"
                        @click="openUserRolesModal(user)"
                        class="p-2 hover:bg-blue-50 dark:hover:bg-blue-950/30 text-blue-600 hover:text-blue-700 dark:text-blue-400 rounded-xl transition cursor-pointer"
                        title="Ubah / Tambah / Kurangi Peran Operator"
                      >
                        <ShieldCheck class="w-4 h-4" />
                      </button>

                      <!-- Action 2: Reset 2FA -->
                      <button
                        v-if="user.two_factor_enabled"
                        type="button"
                        @click="openReset2faModal(user)"
                        class="p-2 hover:bg-amber-50 dark:hover:bg-amber-950/20 text-amber-600 hover:text-amber-700 dark:text-amber-400 rounded-xl transition cursor-pointer"
                        title="Reset 2FA Operator"
                      >
                        <ShieldOff class="w-4 h-4" />
                      </button>

                      <!-- Action 3: Edit User Data -->
                      <button
                        type="button"
                        @click="openEditModal(user)"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 rounded-xl transition cursor-pointer"
                        title="Edit Profil &amp; Password Operator"
                      >
                        <Pencil class="w-4 h-4" />
                      </button>

                      <!-- Action 4: Delete User -->
                      <button
                        type="button"
                        @click="confirmDeleteUser(user)"
                        class="p-2 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 hover:text-red-600 dark:hover:text-red-400 rounded-xl transition cursor-pointer"
                        title="Hapus Akun Operator"
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
      </div>

      <!-- ========================================================================= -->
      <!-- TAB 2: MASTER PERAN & HAK AKSES MODUL WEB                                 -->
      <!-- ========================================================================= -->
      <div v-if="activeTab === 'roles'" class="space-y-4">
        <!-- Roles List Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="role in roles"
            :key="role.id"
            class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4 flex flex-col justify-between hover:border-gray-200 dark:hover:border-zinc-700 transition"
          >
            <div class="space-y-3">
              <!-- Top Row: Role Title & Badge -->
              <div class="flex items-start justify-between gap-2">
                <div>
                  <div class="flex items-center gap-2">
                    <h3 class="text-sm font-black text-gray-900 dark:text-zinc-100 tracking-tight">
                      {{ role.title || role.name }}
                    </h3>
                  </div>
                  <span v-if="role.name !== role.title" class="text-[10px] font-mono text-gray-400 dark:text-zinc-500 block mt-0.5">
                    {{ role.name }}
                  </span>
                </div>

                <span
                  class="px-2 py-0.5 rounded-lg text-[10px] font-bold whitespace-nowrap"
                  :class="role.is_protected ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40'"
                >
                  {{ role.is_protected ? 'Peran Sistem' : 'Peran Kustom' }}
                </span>
              </div>

              <!-- Role Stats & Descriptions -->
              <p class="text-xs text-gray-500 dark:text-zinc-400 leading-relaxed min-h-[36px]">
                {{ getRoleDescription(role.title || role.name) }}
              </p>

              <!-- Modules summary badges -->
              <div class="space-y-1.5 pt-2 border-t border-gray-100 dark:border-zinc-800/60">
                <div class="flex items-center justify-between text-[11px] font-bold text-gray-600 dark:text-zinc-300">
                  <span class="flex items-center gap-1">
                    <ShieldCheck class="w-3.5 h-3.5 text-primary-500" />
                    <span>Cakupan Hak Akses Modul:</span>
                  </span>
                  <span class="font-mono text-primary-600 dark:text-primary-400">
                    {{ role.permissions.length }} Izin
                  </span>
                </div>

                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="m in getRoleUnlockedModules(role)"
                    :key="m.id"
                    class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                    :class="m.badge_color"
                  >
                    {{ m.name }}
                  </span>
                  <span v-if="role.permissions.length === 0" class="text-[10px] text-gray-400 italic">
                    Belum ada hak akses modul yang dipilih
                  </span>
                </div>
              </div>
            </div>

            <!-- Card Bottom Footer & Actions -->
            <div class="pt-3 border-t border-gray-100 dark:border-zinc-800/60 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-400 dark:text-zinc-500 flex items-center gap-1.5">
                <Users class="w-3.5 h-3.5" />
                <span>{{ role.users_count }} Operator</span>
              </span>

              <div class="flex items-center gap-1.5">
                <!-- Action: Edit / Configure Role Permissions -->
                <button
                  type="button"
                  @click="openEditRoleModal(role)"
                  class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                  title="Ubah Nama &amp; Hak Akses Modul"
                >
                  <Pencil class="w-3.5 h-3.5" />
                  <span>Ubah Izin</span>
                </button>

                <!-- Action: Delete Custom Role (Only for non-protected roles) -->
                <button
                  v-if="!role.is_protected"
                  type="button"
                  @click="confirmDeleteRole(role)"
                  class="p-1.5 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition cursor-pointer"
                  title="Hapus Peran Kustom"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- MODAL: KELOLA PERAN OPERATOR (UBAH / TAMBAH / KURANGI PERAN PENGGUNA)     -->
      <!-- ========================================================================= -->
      <transition name="fade">
        <div v-if="userRolesModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 relative text-left max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-zinc-800">
              <div>
                <h3 class="text-base font-black text-gray-900 dark:text-zinc-100 tracking-tight">
                  Atur &amp; Kelola Peran Operator
                </h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                  Ubah, tambah, atau kurangi peran untuk operator
                  <strong class="text-primary-600 font-mono">@{{ selectedUser?.username }}</strong>
                  ({{ selectedUser?.name }}).
                </p>
              </div>
              <button
                type="button"
                @click="userRolesModalOpen = false"
                class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-800 cursor-pointer"
              >
                &times;
              </button>
            </div>

            <form @submit.prevent="submitUserRoles" class="space-y-4">
              <div class="space-y-2">
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300">
                  Pilih Peran Operator Sesuai Tugas &amp; Modul:
                </label>
                <div class="grid grid-cols-1 gap-2.5 max-h-72 overflow-y-auto p-2 bg-gray-50/70 dark:bg-zinc-800/40 rounded-2xl border border-gray-200/60 dark:border-zinc-700/60 scrollbar-thin">
                  <label
                    v-for="r in roles"
                    :key="r.id"
                    class="flex items-start gap-3 p-3 rounded-xl border transition cursor-pointer select-none"
                    :class="userRolesForm.roles.includes(r.name)
                      ? 'bg-primary-50/70 dark:bg-primary-950/30 border-primary-300 dark:border-primary-700/60'
                      : 'bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 hover:border-gray-300'"
                  >
                    <input
                      type="checkbox"
                      :value="r.name"
                      v-model="userRolesForm.roles"
                      class="accent-primary-600 h-4 w-4 mt-0.5 rounded cursor-pointer"
                    />
                    <div class="flex-1">
                      <div class="flex items-center gap-2">
                        <span class="font-extrabold text-xs text-gray-900 dark:text-zinc-100">
                          {{ r.title || r.name }}
                        </span>
                        <span class="text-[9px] font-mono px-1.5 py-0.2 rounded bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400">
                          {{ r.permissions.length }} izin modul
                        </span>
                      </div>
                      <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-0.5 leading-snug">
                        {{ getRoleDescription(r.title || r.name) }}
                      </p>
                    </div>
                  </label>
                </div>
                <span v-if="userRolesForm.errors.roles" class="text-xs text-red-500 mt-1 block">
                  {{ userRolesForm.errors.roles }}
                </span>
              </div>

              <!-- Footer Buttons -->
              <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800">
                <button
                  type="button"
                  @click="userRolesModalOpen = false"
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="userRolesForm.processing"
                  class="px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/20 active:scale-95 transition disabled:opacity-50 cursor-pointer"
                >
                  {{ userRolesForm.processing ? 'Menyimpan Peran...' : 'Simpan Perubahan Peran' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- ========================================================================= -->
      <!-- MODAL: MASTER PERAN & HAK AKSES MODUL (TAMBAH / UBAH PERAN)               -->
      <!-- ========================================================================= -->
      <transition name="fade">
        <div v-if="roleModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-3xl shadow-2xl p-6 relative text-left max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-zinc-800">
              <div>
                <h3 class="text-base font-black text-gray-900 dark:text-zinc-100 tracking-tight">
                  {{ editingRole ? 'Ubah Hak Akses Peran: ' + editingRole.name : 'Tambah Peran Baru' }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                  Pilih modul dan fitur website yang boleh diakses oleh peran ini.
                </p>
              </div>
              <button
                type="button"
                @click="roleModalOpen = false"
                class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-800 cursor-pointer"
              >
                &times;
              </button>
            </div>

            <form @submit.prevent="submitRole" class="space-y-5">
              <!-- Role Name -->
              <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1">
                  Nama Peran <span class="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  v-model="roleForm.name"
                  :readonly="editingRole?.is_protected"
                  required
                  placeholder="Contoh: Petugas Front Office"
                  class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-800 dark:text-zinc-200"
                  :class="{ 'opacity-70 cursor-not-allowed': editingRole?.is_protected }"
                />
                <p v-if="editingRole?.is_protected" class="text-[10px] text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1 font-semibold">
                  <Lock class="w-3 h-3" />
                  <span>Nama peran bawaan sistem ini dilindungi, namun Anda bebas menyesuaikan izin modul di bawah.</span>
                </p>
                <span v-if="roleForm.errors.name" class="text-xs text-red-500 mt-1 block">
                  {{ roleForm.errors.name }}
                </span>
              </div>

              <!-- Module Selection Controls -->
              <div class="space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-zinc-800">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-zinc-300">
                      Izin Modul &amp; Fitur Web
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                      {{ roleForm.permissions.length }} Izin Dipilih
                    </span>
                  </div>

                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      @click="selectAllPermissions"
                      class="text-[11px] font-bold text-primary-600 hover:text-primary-500 cursor-pointer"
                    >
                      Pilih Semua
                    </button>
                    <span class="text-gray-300 dark:text-zinc-700">|</span>
                    <button
                      type="button"
                      @click="clearAllPermissions"
                      class="text-[11px] font-bold text-gray-500 hover:text-gray-700 dark:text-zinc-400 cursor-pointer"
                    >
                      Hapus Pilihan
                    </button>
                  </div>
                </div>

                <!-- Accordion / Grouped Modules -->
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1 scrollbar-thin">
                  <div
                    v-for="mod in Object.values(modules)"
                    :key="mod.id"
                    class="p-4 rounded-2xl bg-gray-50/70 dark:bg-zinc-800/40 border border-gray-200/80 dark:border-zinc-700/60 space-y-3"
                  >
                    <!-- Module Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-zinc-700/40">
                      <div>
                        <div class="flex items-center gap-2">
                          <h4 class="text-xs font-black text-gray-900 dark:text-zinc-100">
                            {{ mod.name }}
                          </h4>
                          <span
                            class="px-2 py-0.2 rounded-md text-[9px] font-bold"
                            :class="mod.badge_color"
                          >
                            {{ getModuleSelectedCount(mod) }} / {{ Object.keys(mod.permissions).length }} aktif
                          </span>
                        </div>
                        <p class="text-[10px] text-gray-400 dark:text-zinc-500 mt-0.5">
                          {{ mod.description }}
                        </p>
                      </div>

                      <button
                        type="button"
                        @click="toggleModulePermissions(mod)"
                        class="text-[11px] font-bold text-primary-600 hover:text-primary-500 whitespace-nowrap cursor-pointer"
                      >
                        {{ isModuleAllSelected(mod) ? 'Batal Pilih' : 'Pilih Semua Modul Ini' }}
                      </button>
                    </div>

                    <!-- Module Permissions Checkbox Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                      <label
                        v-for="(pInfo, pKey) in mod.permissions"
                        :key="pKey"
                        class="flex items-start gap-2.5 p-2 rounded-xl bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 hover:border-primary-200 dark:hover:border-primary-800 transition cursor-pointer select-none"
                      >
                        <input
                          type="checkbox"
                          :value="pKey"
                          v-model="roleForm.permissions"
                          class="accent-primary-600 h-4 w-4 mt-0.5 rounded cursor-pointer"
                        />
                        <div class="min-w-0">
                          <div class="text-[11px] font-bold text-gray-800 dark:text-zinc-200 truncate">
                            {{ pInfo.title }}
                          </div>
                          <div class="text-[10px] text-gray-400 dark:text-zinc-500 leading-tight mt-0.5">
                            {{ pInfo.desc }}
                          </div>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer Buttons -->
              <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800">
                <button
                  type="button"
                  @click="roleModalOpen = false"
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="roleForm.processing"
                  class="px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/20 active:scale-95 transition disabled:opacity-50 cursor-pointer"
                >
                  {{ roleForm.processing ? 'Menyimpan...' : (editingRole ? 'Simpan Perubahan Peran' : 'Buat Peran Baru') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- ========================================================================= -->
      <!-- MODAL: TAMBAH / EDIT DATA PENGGUNA                                        -->
      <!-- ========================================================================= -->
      <transition name="fade">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
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
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 transition focus:outline-none p-1 cursor-pointer"
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

              <!-- Roles Selection (Multi-Role Support) -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">
                  Pilih Peran Operator:
                </label>
                <div class="grid grid-cols-1 gap-2 max-h-52 overflow-y-auto p-2 bg-gray-50 dark:bg-zinc-800/30 rounded-2xl border border-gray-100 dark:border-zinc-800 scrollbar-thin">
                  <label
                    v-for="r in roles"
                    :key="r.id"
                    class="flex items-start gap-2.5 p-2.5 hover:bg-gray-100 dark:hover:bg-zinc-800/60 rounded-xl cursor-pointer text-xs transition border border-transparent hover:border-gray-200 select-none"
                    :class="{ 'bg-primary-50/50 dark:bg-primary-950/20 border-primary-200': form.roles?.includes(r.name) }"
                  >
                    <input
                      type="checkbox"
                      :value="r.name"
                      v-model="form.roles"
                      class="accent-primary-500 mt-0.5 h-4 w-4 shrink-0 cursor-pointer"
                    />
                    <div class="flex-1">
                      <div class="font-bold text-gray-800 dark:text-zinc-200">{{ r.title || r.name }}</div>
                      <div class="text-[10px] text-gray-400 dark:text-zinc-500 mt-0.5">
                        {{ getRoleDescription(r.title || r.name) }}
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
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition disabled:opacity-50 cursor-pointer"
                >
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Operator' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- ========================================================================= -->
      <!-- MODAL: HAPUS OPERATOR                                                     -->
      <!-- ========================================================================= -->
      <transition name="fade">
        <div v-if="deleteModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
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
                class="px-4 py-2.5 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer"
              >
                Batal
              </button>
              <button
                type="button"
                @click="executeDelete"
                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 active:scale-95 transition cursor-pointer"
              >
                Ya, Hapus Operator
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- ========================================================================= -->
      <!-- MODAL: HAPUS PERAN KUSTOM                                                 -->
      <!-- ========================================================================= -->
      <transition name="fade">
        <div v-if="deleteRoleModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 relative text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100 dark:border-rose-900/30">
              <Trash2 class="w-6 h-6" />
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100 mb-2">Hapus Peran Kustom?</h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 mb-6">
              Apakah Anda yakin ingin menghapus peran
              <strong class="text-gray-800 dark:text-zinc-200 font-bold">{{ roleToDelete?.name }}</strong>?
              Peran ini akan dihapus permanen dari sistem.
            </p>
            <div class="flex justify-center gap-3">
              <button
                type="button"
                @click="deleteRoleModalOpen = false"
                class="px-4 py-2.5 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer"
              >
                Batal
              </button>
              <button
                type="button"
                @click="executeDeleteRole"
                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 active:scale-95 transition cursor-pointer"
              >
                Ya, Hapus Peran
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
import { 
  Plus, Pencil, Trash2, Eye, EyeOff, Search, UserX, 
  Shield, ShieldCheck, ShieldOff, Users, Lock
} from '@lucide/vue';

const props = defineProps({
  users:   { type: Array, default: () => [] },
  roles:   { type: Array, default: () => [] },
  modules: { type: Object, default: () => ({}) },
  search:  { type: String, default: '' },
});

const activeTab = ref('users');
const searchQuery = ref(props.search || '');

// User Modals State
const modalOpen = ref(false);
const editingUser = ref(null);
const deleteModalOpen = ref(false);
const userToDelete = ref(null);
const showPassword = ref(false);

// User Roles Direct Action Modal State
const userRolesModalOpen = ref(false);
const selectedUser = ref(null);

// Role Master Modals State
const roleModalOpen = ref(false);
const editingRole = ref(null);
const deleteRoleModalOpen = ref(false);
const roleToDelete = ref(null);

// Form for User Create / Edit
const form = useForm({
  username:  '',
  name:      '',
  email:     '',
  password:  '',
  role:      'Editor Konten',
  roles:     ['Editor Konten'],
  is_active: true,
});

// Form for Direct User Roles Sync
const userRolesForm = useForm({
  roles: [],
});

// Form for Master Role Create / Edit
const roleForm = useForm({
  name: '',
  permissions: [],
});

// Client-side search filter
const filteredUsers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return props.users;
  return props.users.filter(u =>
    u.username?.toLowerCase().includes(q) ||
    u.name?.toLowerCase().includes(q) ||
    u.email?.toLowerCase().includes(q) ||
    u.roles?.some(r => r.toLowerCase().includes(q))
  );
});

// User Modal Handlers
const openCreateModal = () => {
  editingUser.value = null;
  form.reset();
  form.roles = ['Editor Konten'];
  form.is_active = true;
  form.clearErrors();
  showPassword.value = false;
  modalOpen.value = true;
};

const openEditModal = (user) => {
  editingUser.value = user;
  form.username  = user.username;
  form.name      = user.name;
  form.email     = user.email;
  form.password  = '';
  form.roles     = user.roles?.length ? [...user.roles] : [user.role || 'Editor Konten'];
  form.role      = user.role || 'Editor Konten';
  form.is_active = user.is_active;
  form.clearErrors();
  showPassword.value = false;
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
      },
    });
  }
};

// User Roles Management Handlers
const openUserRolesModal = (user) => {
  selectedUser.value = user;
  userRolesForm.roles = user.roles?.length ? [...user.roles] : [user.role || 'Editor Konten'];
  userRolesForm.clearErrors();
  userRolesModalOpen.value = true;
};

const submitUserRoles = () => {
  if (!selectedUser.value) return;
  userRolesForm.put(route('admin.users.sync-roles', selectedUser.value.id), {
    onSuccess: () => {
      userRolesModalOpen.value = false;
      selectedUser.value = null;
    },
  });
};

// Role Master Handlers
const openCreateRoleModal = () => {
  editingRole.value = null;
  roleForm.reset();
  roleForm.name = '';
  roleForm.permissions = [];
  roleForm.clearErrors();
  roleModalOpen.value = true;
};

const openEditRoleModal = (role) => {
  editingRole.value = role;
  roleForm.name = role.name;
  roleForm.permissions = [...(role.permissions || [])];
  roleForm.clearErrors();
  roleModalOpen.value = true;
};

const selectAllPermissions = () => {
  const all = [];
  Object.values(props.modules).forEach(m => {
    Object.keys(m.permissions).forEach(p => all.push(p));
  });
  roleForm.permissions = [...new Set(all)];
};

const clearAllPermissions = () => {
  roleForm.permissions = [];
};

const isModuleAllSelected = (mod) => {
  const modPerms = Object.keys(mod.permissions);
  return modPerms.length > 0 && modPerms.every(p => roleForm.permissions.includes(p));
};

const toggleModulePermissions = (mod) => {
  const modPerms = Object.keys(mod.permissions);
  if (isModuleAllSelected(mod)) {
    // Deselect all from this module
    roleForm.permissions = roleForm.permissions.filter(p => !modPerms.includes(p));
  } else {
    // Select all from this module
    roleForm.permissions = [...new Set([...roleForm.permissions, ...modPerms])];
  }
};

const getModuleSelectedCount = (mod) => {
  const modPerms = Object.keys(mod.permissions);
  return modPerms.filter(p => roleForm.permissions.includes(p)).length;
};

const getRoleUnlockedModules = (role) => {
  const unlocked = [];
  const perms = role.permissions || [];
  Object.values(props.modules).forEach(m => {
    const hasAny = Object.keys(m.permissions).some(p => perms.includes(p));
    if (hasAny) {
      unlocked.push(m);
    }
  });
  return unlocked;
};

const submitRole = () => {
  if (editingRole.value) {
    roleForm.put(route('admin.roles.update', editingRole.value.id), {
      onSuccess: () => {
        roleModalOpen.value = false;
        editingRole.value = null;
      },
    });
  } else {
    roleForm.post(route('admin.roles.store'), {
      onSuccess: () => {
        roleModalOpen.value = false;
      },
    });
  }
};

const confirmDeleteRole = (role) => {
  roleToDelete.value = role;
  deleteRoleModalOpen.value = true;
};

const executeDeleteRole = () => {
  if (!roleToDelete.value) return;
  router.delete(route('admin.roles.destroy', roleToDelete.value.id), {
    onSuccess: () => {
      deleteRoleModalOpen.value = false;
      roleToDelete.value = null;
    },
  });
};

const openReset2faModal = (user) => {
  if (confirm(`Reset 2FA (Two-Factor Authentication) untuk operator @${user.username}?`)) {
    router.post(route('admin.users.admin-reset-2fa', user.id));
  }
};

// Role styling and mapping helpers
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
    'bendahara_barang': 'Bendahara Barang',
    'operator_cetak_ktpel': 'Operator Cetak KTP-el',
    'supervisor_persediaan_blangko': 'Supervisor Persediaan Blangko',
  };
  return map[role] || role;
};

const getRoleClass = (role) => {
  switch (role) {
    case 'Supervisor Layanan KTP-el':
      return 'bg-blue-50 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40';
    case 'Administrator Utama':
    case 'Super Administrator':
      return 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-200 dark:border-red-900/30';
    case 'Administrator Website':
    case 'Website Administrator':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200 dark:border-blue-900/30';
    case 'Bendahara Barang':
      return 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40';
    case 'Operator Cetak KTP-el':
      return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/40';
    case 'Supervisor Persediaan Blangko':
      return 'bg-teal-50 text-teal-800 dark:bg-teal-950/50 dark:text-teal-300 border border-teal-200 dark:border-teal-800/40';
    case 'Editor Konten':
    case 'Content Editor':
      return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30';
    case 'Operator Berita':
    case 'News Operator':
      return 'bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-400 border border-orange-200 dark:border-orange-900/30';
    case 'Pengelola Media':
    case 'Media Manager':
      return 'bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-400 border border-violet-200 dark:border-violet-900/30';
    case 'Operator Publikasi':
    case 'Publication Operator':
      return 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400 border border-sky-200 dark:border-sky-900/30';
    case 'Petugas Pengaduan':
    case 'Complaint Officer':
      return 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200 dark:border-purple-900/30';
    case 'Admin PPID':
    case 'PPID Admin':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30';
    case 'Petugas Verifikasi / Registrasi KTP-el':
      return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/30';
    case 'Petugas Pengambilan KTP-el':
      return 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border border-teal-200 dark:border-teal-900/30';
    case 'Petugas Pengarsipan':
      return 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-900/30';
    default:
      return 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700';
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
    case 'Bendahara Barang':
      return 'Pengelolaan penuh persediaan blangko KTP-el: pencatatan BAST penerimaan baru, stok opname, dan usulan koreksi saldo.';
    case 'Operator Cetak KTP-el':
      return 'Pencatatan hasil pencetakan KTP-el harian, kartu rusak, dan pelaporan berkala sesi operator cetak.';
    case 'Supervisor Persediaan Blangko':
      return 'Pengawasan persediaan blangko, verifikasi laporan operator, pengesahan berita acara opname, dan koreksi saldo.';
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
      return 'Akses operator terkonfigurasi sesuai modul sistem yang dipilih.';
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
