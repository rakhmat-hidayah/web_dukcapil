<template>
  <Head title="Manajemen Menu Navigasi" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Menu Navigasi</h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Susun menu utama (Header), tautan kaki (Footer), dan menu pintasan secara visual.
          </p>
        </div>
        <button 
          @click="openAddMenuModal"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
        >
          <Plus class="w-4 h-4" />
          Menu Baru
        </button>
      </div>

      <!-- Main Menu Manager Content Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Menu list selection sidebar (Col Span 1) -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm h-fit space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Pilih Menu untuk Diedit</h3>
          
          <div class="space-y-2 flex flex-col">
            <div v-if="!menus || menus.length === 0" class="text-xs text-gray-400 p-2">
              Belum ada menu navigasi terdaftar.
            </div>
            <button 
              v-for="menu in menus" 
              :key="menu.id"
              @click="selectMenu(menu.id)"
              class="w-full text-left px-4 py-3 rounded-2xl text-xs font-bold transition flex justify-between items-center"
              :class="[
                selectedMenu && selectedMenu.id === menu.id 
                  ? 'bg-primary-50 dark:bg-primary-950/20 text-primary-600 dark:text-primary-400' 
                  : 'hover:bg-gray-50 dark:hover:bg-zinc-800/50 text-gray-600 dark:text-zinc-300'
              ]"
            >
              <span>{{ menu.name }}</span>
              <span class="text-[9px] font-semibold text-gray-400 uppercase font-mono">{{ menu.location }}</span>
            </button>
          </div>
        </div>

        <!-- Menu items tree builder (Col Span 2) -->
        <div class="md:col-span-2 space-y-6">
          <div v-if="!selectedMenu" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-12 shadow-sm text-center text-gray-400 text-xs">
            Pilih menu dari panel kiri untuk mulai menyusun tautan navigasi.
          </div>

          <div v-if="selectedMenu" class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-6">
            <!-- Selected menu header info -->
            <div class="flex justify-between items-center border-b border-gray-100 dark:border-zinc-800/60 pb-4">
              <div>
                <h4 class="font-bold text-gray-800 dark:text-zinc-200 text-sm">Struktur Menu: {{ selectedMenu.name }}</h4>
                <p class="text-[10px] text-gray-400 mt-0.5 uppercase tracking-wider font-semibold font-mono">Lokasi: {{ selectedMenu.location }}</p>
              </div>
              <div class="flex gap-2">
                <button 
                  @click="openAddItemModal"
                  class="flex items-center gap-1 px-3 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-[10px] font-bold rounded-lg transition"
                >
                  <Plus class="w-3.5 h-3.5" />
                  Tambah Link
                </button>
                <button 
                  @click="deleteMenu(selectedMenu)"
                  class="p-1.5 hover:bg-red-50 text-red-500 border border-red-100 dark:border-red-950/20 rounded-lg"
                  title="Hapus Menu"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Items list hierarchy -->
            <div class="space-y-3">
              <div v-if="menuItems.length === 0" class="text-center text-gray-400 py-6 text-xs">
                Belum ada link di dalam menu ini. Klik "Tambah Link" untuk menambahkan.
              </div>

              <!-- Loop top level items -->
              <div 
                v-for="item in menuItems.filter(i => !i.parent_id)" 
                :key="item.id"
                class="border border-gray-100 dark:border-zinc-800 rounded-2xl p-4 bg-gray-50/50 dark:bg-zinc-900/30 space-y-3 text-xs"
              >
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-3">
                    <Move class="w-3.5 h-3.5 text-gray-300" />
                    <span class="font-bold text-gray-800 dark:text-zinc-200">{{ item.label }}</span>
                    <span class="text-[10px] text-gray-400 font-mono">{{ item.url }}</span>
                  </div>
                  <div class="flex gap-1.5">
                    <button @click="openEditItemModal(item)" class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded text-gray-500">
                      <Edit class="w-3.5 h-3.5" />
                    </button>
                    <button @click="deleteItem(item.id)" class="p-1 hover:bg-red-50 text-red-500 rounded">
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>

                <!-- Children nested items -->
                <div v-if="menuItems.some(i => i.parent_id === item.id)" class="pl-8 space-y-2 border-l border-gray-200/80 dark:border-zinc-800">
                  <div 
                    v-for="sub in menuItems.filter(i => i.parent_id === item.id)"
                    :key="sub.id"
                    class="flex justify-between items-center py-2 px-3 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-xl"
                  >
                    <div class="flex items-center gap-2">
                      <span class="font-semibold text-gray-700 dark:text-zinc-300">{{ sub.label }}</span>
                      <span class="text-[9px] text-gray-400 font-mono">{{ sub.url }}</span>
                    </div>
                    <div class="flex gap-1">
                      <button @click="openEditItemModal(sub)" class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded text-gray-500">
                        <Edit class="w-3 h-3" />
                      </button>
                      <button @click="deleteItem(sub.id)" class="p-1 hover:bg-red-50 text-red-500 rounded">
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Menu Container Modal -->
      <transition name="fade">
        <div v-if="addMenuModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Buat Menu Navigasi Baru</h3>

            <form @submit.prevent="submitMenu" class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Menu</label>
                <input 
                  type="text" 
                  v-model="menuForm.name" 
                  required
                  placeholder="Contoh: Menu Utama / Menu Footer"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Lokasi Tampilan</label>
                <select 
                  v-model="menuForm.location"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                >
                  <option value="header">Header (Navigasi Atas)</option>
                  <option value="footer">Footer (Navigasi Bawah)</option>
                  <option value="sidebar">Sidebar (Menu Samping)</option>
                </select>
              </div>

              <!-- Footer Buttons -->
              <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-6">
                <button 
                  type="button" 
                  @click="addMenuModalOpen = false" 
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  :disabled="menuForm.processing"
                  class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
                >
                  {{ menuForm.processing ? 'Menyimpan...' : 'Buat Menu' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- Add/Edit Menu Item Modal -->
      <transition name="fade">
        <div v-if="addItemModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl w-full max-w-sm shadow-2xl p-6 text-left relative">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">
              {{ isEditingItem ? 'Edit Link Menu' : 'Tambah Link Menu Baru' }}
            </h3>

            <form @submit.prevent="submitItem" class="space-y-4">
              <!-- Label -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Label Link (Teks Menu)</label>
                <input 
                  type="text" 
                  v-model="itemForm.label" 
                  required
                  placeholder="Contoh: Berita / Hubungi Kami"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- URL -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tujuan Tautan URL</label>
                <input 
                  type="text" 
                  v-model="itemForm.url" 
                  required
                  placeholder="Contoh: /news atau https://example.com"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                />
              </div>

              <!-- Target & Parent -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Target Buka</label>
                  <select 
                    v-model="itemForm.target"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option value="_self">Tab Sama (_self)</option>
                    <option value="_blank">Tab Baru (_blank)</option>
                  </select>
                </div>

                <div v-if="!isEditingItem">
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Menu Induk (Sub-menu)</label>
                  <select 
                    v-model="itemForm.parent_id"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option :value="null">Pilih Link Induk (None)</option>
                    <option v-for="top in menuItems.filter(i => !i.parent_id)" :key="top.id" :value="top.id">
                      {{ top.label }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Footer Buttons -->
              <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800/60 mt-6">
                <button 
                  type="button" 
                  @click="addItemModalOpen = false" 
                  class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  :disabled="itemForm.processing"
                  class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl active:scale-95 transition"
                >
                  {{ itemForm.processing ? 'Menyimpan...' : 'Simpan Link' }}
                </button>
              </div>
            </form>
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
import { Plus, Edit, Trash2, Move } from '@lucide/vue';

const props = defineProps({
  menus: Array,
  selectedMenu: Object,
});

const menuItems = computed(() => {
  if (!props.selectedMenu) return [];
  return props.selectedMenu.all_items || props.selectedMenu.allItems || [];
});

const addMenuModalOpen = ref(false);
const addItemModalOpen = ref(false);
const isEditingItem = ref(false);
const editItemId = ref(null);

const menuForm = useForm({
  name: '',
  location: 'header',
});

const itemForm = useForm({
  label: '',
  url: '',
  target: '_self',
  parent_id: null,
  icon: '',
});

const selectMenu = (id) => {
  router.get(route('admin.menus.index'), { menu_id: id }, { preserveState: true });
};

const openAddMenuModal = () => {
  menuForm.reset();
  addMenuModalOpen.value = true;
};

const submitMenu = () => {
  menuForm.post(route('admin.menus.store'), {
    onSuccess: () => {
      addMenuModalOpen.value = false;
    }
  });
};

const deleteMenu = (menu) => {
  if (confirm(`Apakah Anda yakin ingin menghapus seluruh menu "${menu.name}" beserta link di dalamnya?`)) {
    router.delete(route('admin.menus.destroy', menu.id));
  }
};

const openAddItemModal = () => {
  isEditingItem.value = false;
  editItemId.value = null;
  itemForm.reset();
  addItemModalOpen.value = true;
};

const openEditItemModal = (item) => {
  isEditingItem.value = true;
  editItemId.value = item.id;
  itemForm.label = item.label;
  itemForm.url = item.url;
  itemForm.target = item.target;
  itemForm.parent_id = item.parent_id;
  itemForm.icon = item.icon || '';
  addItemModalOpen.value = true;
};

const submitItem = () => {
  if (isEditingItem.value) {
    itemForm.put(route('admin.menus.item.update', editItemId.value), {
      onSuccess: () => {
        addItemModalOpen.value = false;
      }
    });
  } else {
    itemForm.post(route('admin.menus.item.store', props.selectedMenu.id), {
      onSuccess: () => {
        addItemModalOpen.value = false;
      }
    });
  }
};

const deleteItem = (itemId) => {
  if (confirm('Apakah Anda yakin ingin menghapus item menu ini?')) {
    router.delete(route('admin.menus.item.destroy', itemId));
  }
};
</script>
