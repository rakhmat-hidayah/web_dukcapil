<template>
  <Head title="Theme Customizer" />

  <AdminLayout>
    <div class="space-y-6 text-left">
      <!-- Title section -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">Theme Customizer</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Ubah warna utama, font, logo, favicon, dan elemen visual website tanpa memodifikasi kode.
        </p>
      </div>

      <!-- Tabs navigation -->
      <div class="flex border-b border-gray-200 dark:border-zinc-800 gap-6">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="pb-3 text-xs font-bold uppercase tracking-wider border-b-2 transition"
          :class="[
            activeTab === tab.id 
              ? 'border-primary-600 text-primary-600 dark:text-primary-400' 
              : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Form Card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
        <form @submit.prevent="submit" class="space-y-8">
          
          <!-- Tab 1: Colors -->
          <div v-show="activeTab === 'colors'" class="space-y-6">
            <h3 class="text-sm font-bold text-gray-800 dark:text-zinc-200 border-b border-gray-50 dark:border-zinc-800 pb-2 mb-4">
              Skema Warna (Primary, Secondary, Accent)
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
              <!-- Primary Color -->
              <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider">Warna Utama (Primary)</h4>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Standard (500)</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="form.settings.primary_500" class="w-10 h-8 rounded-lg cursor-pointer border-0 p-0" />
                    <input type="text" v-model="form.settings.primary_500" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-lg border border-gray-200 dark:border-zinc-700" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Gelap (700)</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="form.settings.primary_700" class="w-10 h-8 rounded-lg cursor-pointer border-0 p-0" />
                    <input type="text" v-model="form.settings.primary_700" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-lg border border-gray-200 dark:border-zinc-700" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Sangat Gelap (950)</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="form.settings.primary_950" class="w-10 h-8 rounded-lg cursor-pointer border-0 p-0" />
                    <input type="text" v-model="form.settings.primary_950" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-lg border border-gray-200 dark:border-zinc-700" />
                  </div>
                </div>
              </div>

              <!-- Secondary Color -->
              <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider">Warna Kedua (Secondary)</h4>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Standard (500)</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="form.settings.secondary_500" class="w-10 h-8 rounded-lg cursor-pointer border-0 p-0" />
                    <input type="text" v-model="form.settings.secondary_500" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-lg border border-gray-200 dark:border-zinc-700" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Gelap (700)</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="form.settings.secondary_700" class="w-10 h-8 rounded-lg cursor-pointer border-0 p-0" />
                    <input type="text" v-model="form.settings.secondary_700" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-lg border border-gray-200 dark:border-zinc-700" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Sangat Gelap (950)</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="form.settings.secondary_950" class="w-10 h-8 rounded-lg cursor-pointer border-0 p-0" />
                    <input type="text" v-model="form.settings.secondary_950" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-lg border border-gray-200 dark:border-zinc-700" />
                  </div>
                </div>
              </div>

              <!-- Accent Color -->
              <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider">Warna Aksen (Accent)</h4>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Standard (500)</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="form.settings.accent_500" class="w-10 h-8 rounded-lg cursor-pointer border-0 p-0" />
                    <input type="text" v-model="form.settings.accent_500" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-lg border border-gray-200 dark:border-zinc-700" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Gelap (700)</label>
                  <div class="flex gap-2">
                    <input type="color" v-model="form.settings.accent_700" class="w-10 h-8 rounded-lg cursor-pointer border-0 p-0" />
                    <input type="text" v-model="form.settings.accent_700" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-zinc-800 text-xs rounded-lg border border-gray-200 dark:border-zinc-700" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 2: Typography -->
          <div v-show="activeTab === 'typography'" class="space-y-6">
            <h3 class="text-sm font-bold text-gray-800 dark:text-zinc-200 border-b border-gray-50 dark:border-zinc-800 pb-2 mb-4">
              Tipografi & Font
            </h3>
            
            <div class="max-w-sm">
              <label class="block text-xs font-semibold text-gray-500 mb-1">Font Utama Website</label>
              <select v-model="form.settings.font_family" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500">
                <option value="Inter">Inter (SaaS Standard)</option>
                <option value="Outfit">Outfit (Premium Rounded)</option>
                <option value="Plus Jakarta Sans">Plus Jakarta Sans (Modern Clean)</option>
                <option value="Roboto">Roboto (Google Standard)</option>
                <option value="Playfair Display">Playfair Display (Elegant Serif)</option>
              </select>
              <p class="text-[10px] text-gray-400 mt-1">Font dimuat dinamis dari Google Fonts.</p>
            </div>
          </div>

          <!-- Tab 3: Brand assets -->
          <div v-show="activeTab === 'brand'" class="space-y-6">
            <h3 class="text-sm font-bold text-gray-800 dark:text-zinc-200 border-b border-gray-50 dark:border-zinc-800 pb-2 mb-4">
              Identitas Visual
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Site Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Website / Judul Utama</label>
                <input type="text" v-model="form.settings.site_title" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500" />
              </div>
              
              <!-- Site Tagline -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tagline</label>
                <input type="text" v-model="form.settings.site_tagline" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500" />
              </div>

              <!-- Logo light -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Logo Website (Mode Terang)</label>
                <div class="flex items-center gap-4">
                  <div class="w-16 h-16 bg-gray-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <img v-if="form.settings.site_logo" :src="form.settings.site_logo" class="object-contain max-w-full max-h-full" />
                    <span v-else class="text-[9px] text-gray-400">Belum ada</span>
                  </div>
                  <input type="file" @input="form.site_logo = $event.target.files[0]" class="text-xs" accept="image/*" />
                </div>
              </div>

              <!-- Logo dark -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Logo Website (Mode Gelap)</label>
                <div class="flex items-center gap-4">
                  <div class="w-16 h-16 bg-zinc-950 rounded-2xl flex items-center justify-center border border-zinc-800 overflow-hidden">
                    <img v-if="form.settings.site_logo_dark" :src="form.settings.site_logo_dark" class="object-contain max-w-full max-h-full" />
                    <span v-else class="text-[9px] text-gray-400">Belum ada</span>
                  </div>
                  <input type="file" @input="form.site_logo_dark = $event.target.files[0]" class="text-xs" accept="image/*" />
                </div>
              </div>

              <!-- Favicon -->
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Favicon (.ico, .png)</label>
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-gray-100 dark:bg-zinc-800 rounded-xl flex items-center justify-center border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <img v-if="form.settings.site_favicon" :src="form.settings.site_favicon" class="object-contain max-w-full max-h-full" />
                    <span v-else class="text-[9px] text-gray-400">Belum ada</span>
                  </div>
                  <input type="file" @input="form.site_favicon = $event.target.files[0]" class="text-xs" accept="image/*" />
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 4: Footer & Contact -->
          <div v-show="activeTab === 'footer'" class="space-y-6">
            <h3 class="text-sm font-bold text-gray-800 dark:text-zinc-200 border-b border-gray-50 dark:border-zinc-800 pb-2 mb-4">
              Kontak, Social Media & Informasi Footer
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">WhatsApp Quick Chat</label>
                <input type="text" v-model="form.settings.contact_whatsapp" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Email Kontak</label>
                <input type="email" v-model="form.settings.contact_email" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Instagram URL</label>
                <input type="url" v-model="form.settings.social_instagram" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Facebook URL</label>
                <input type="url" v-model="form.settings.social_facebook" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Alamat Kantor</label>
                <input type="text" v-model="form.settings.footer_address" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Penjelasan Singkat Footer (Tentang Instansi)</label>
                <textarea v-model="form.settings.footer_about" rows="3" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500"></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Teks Hak Cipta (Copyright)</label>
                <input type="text" v-model="form.settings.footer_copyright" class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500" />
              </div>
            </div>
          </div>

          <!-- Form Submit Button -->
          <div class="flex justify-end pt-6 border-t border-gray-100 dark:border-zinc-800/60 mt-8">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition disabled:opacity-50"
            >
              {{ form.processing ? 'Menyimpan Perubahan...' : 'Simpan Konfigurasi Tema' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  settings: Array,
});

const tabs = [
  { id: 'colors', label: 'Skema Warna' },
  { id: 'typography', label: 'Font & Tipografi' },
  { id: 'brand', label: 'Identitas & Logo' },
  { id: 'footer', label: 'Sosmed & Footer' },
];

const activeTab = ref('colors');

const defaultThemeDefaults = {
  primary_500: '#0e91eb',
  primary_700: '#035ca3',
  primary_950: '#082a4a',
  secondary_500: '#78716c',
  secondary_700: '#44403c',
  secondary_950: '#0c0a09',
  accent_500: '#ca8a04',
  accent_700: '#854d0e',
};

const initialSettings = { ...defaultThemeDefaults };
if (props.settings && Array.isArray(props.settings)) {
  props.settings.forEach(s => {
    if (s.value !== null && s.value !== undefined && s.value !== '') {
      initialSettings[s.key] = s.value;
    }
  });
}

const form = useForm({
  settings: initialSettings,
  
  // File uploads
  site_logo: null,
  site_logo_dark: null,
  site_favicon: null,
  site_hero_bg: null,
});

const submit = () => {
  // Use Inertia post to support multipart file uploads (Laravel PUT request can fail with multipart data)
  // Laravel supports method spoofing using _method=POST, or we can use POST directly as configured in web.php
  form.post(route('admin.theme.update'), {
    preserveScroll: true,
    onSuccess: () => {
      // Refresh local forms settings values with new values from page props
      props.settings.forEach(s => {
        form.settings[s.key] = s.value;
      });
    }
  });
};
</script>
