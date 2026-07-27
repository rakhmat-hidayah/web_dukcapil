<template>
  <Head title="Sampaikan Pengaduan & Aspirasi" />

  <PublicLayout>
    <div class="space-y-8 text-left max-w-3xl mx-auto">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight">Sampaikan Pengaduan</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Isi formulir di bawah dengan lengkap dan jujur. Setiap pengaduan akan diproses oleh petugas kami.
          Anda akan mendapatkan <strong>nomor tiket</strong> untuk memantau progres penanganan.
        </p>
      </div>

      <!-- Category grid -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="block text-[11px] font-extrabold uppercase tracking-wider text-gray-700 dark:text-zinc-300">
            Pilih Kategori Pengaduan <span class="text-red-500">*</span>
          </label>
          <span v-if="!form.complaint_category_id" class="text-[10px] text-amber-600 font-bold">Wajib Dipilih</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" v-if="categories.length">
          <div
            v-for="cat in categories" :key="cat.id"
            @click="form.complaint_category_id = cat.id; if(errors.complaint_category_id) delete errors.complaint_category_id"
            class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col gap-2 relative"
            :class="form.complaint_category_id === cat.id
              ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/30 ring-2 ring-primary-500/20'
              : (errors.complaint_category_id ? 'border-red-300 bg-red-50/20' : 'border-gray-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-gray-300')"
          >
            <div class="flex items-center justify-between">
              <span class="text-2xl">{{ cat.icon }}</span>
              <span v-if="form.complaint_category_id === cat.id" class="text-xs font-black text-primary-600 bg-primary-100 dark:bg-primary-900/60 px-2 py-0.5 rounded-full">✓ Terpilih</span>
            </div>
            <p class="text-xs font-extrabold text-gray-800 dark:text-zinc-100 leading-snug">{{ cat.name }}</p>
            <p class="text-[9px] text-gray-400 leading-relaxed line-clamp-2">{{ cat.description }}</p>
          </div>
        </div>
        <p v-if="errors.complaint_category_id" class="text-xs text-red-500 font-bold mt-1.5 flex items-center gap-1">
          ⚠️ {{ errors.complaint_category_id }}
        </p>
      </div>

      <!-- Main form card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
        <form novalidate @submit.prevent="submitForm" class="space-y-5" enctype="multipart/form-data">

          <!-- Global Validation Error Banner -->
          <div v-if="Object.keys(errors).length > 0" class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 rounded-2xl text-xs space-y-1.5 text-rose-700 dark:text-rose-300 shadow-sm">
            <p class="font-black text-rose-800 dark:text-rose-200 flex items-center gap-1.5">
              <span>⚠️</span> Harap periksa kembali isian Anda:
            </p>
            <ul class="list-disc list-inside space-y-0.5 font-semibold text-[11px] pl-1">
              <li v-for="(err, key) in errors" :key="key">{{ Array.isArray(err) ? err.join(', ') : err }}</li>
            </ul>
          </div>

          <!-- Anonymous toggle -->
          <div class="flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-2xl">
            <input id="is_anonymous" type="checkbox" v-model="form.is_anonymous"
              class="w-4 h-4 accent-amber-500" />
            <label for="is_anonymous" class="text-xs font-semibold text-amber-800 dark:text-amber-300 cursor-pointer">
              Kirim sebagai <strong>Anonim</strong> — Identitas Anda tidak akan dicatat dalam sistem.
            </label>
          </div>

          <!-- Identity (hidden when anonymous) -->
          <transition name="fade">
            <div v-if="!form.is_anonymous" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Lengkap *</label>
                <input 
                  v-model="form.submitter_name" 
                  type="text" 
                  :disabled="form.is_anonymous"
                  placeholder="Nama sesuai KTP"
                  class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                  :class="errors.submitter_name ? 'border-red-400 bg-red-50/20' : 'border-gray-200 dark:border-zinc-700'"
                />
                <p v-if="errors.submitter_name" class="text-[9px] text-red-500 font-semibold mt-0.5">{{ errors.submitter_name }}</p>
              </div>
              <div>
                <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nomor HP / WhatsApp</label>
                <input 
                  v-model="form.submitter_phone" 
                  type="text" 
                  placeholder="628xx..." 
                  class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Alamat Email</label>
                <input 
                  v-model="form.submitter_email" 
                  type="email" 
                  placeholder="email@contoh.com" 
                  class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                />
              </div>
            </div>
          </transition>

          <!-- Subject -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Judul / Pokok Pengaduan *</label>
            <input 
              v-model="form.subject" 
              type="text" 
              maxlength="255"
              placeholder="Tulis ringkasan masalah secara singkat dan jelas"
              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" 
              :class="errors.subject ? 'border-red-400 bg-red-50/20' : 'border-gray-200 dark:border-zinc-700'"
            />
            <p v-if="errors.subject" class="text-[9px] text-red-500 font-semibold mt-0.5">{{ errors.subject }}</p>
          </div>

          <!-- Message -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Uraian Pengaduan *</label>
            <textarea 
              v-model="form.message" 
              rows="6" 
              maxlength="5000"
              placeholder="Jelaskan kronologi, lokasi, dan fakta pendukung pengaduan Anda (minimal 20 karakter)..."
              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              :class="errors.message ? 'border-red-400 bg-red-50/20' : 'border-gray-200 dark:border-zinc-700'"
            ></textarea>
            <div class="flex justify-between items-center mt-1">
              <p v-if="errors.message" class="text-[9px] text-red-500 font-semibold">{{ errors.message }}</p>
              <p v-else class="text-[9px] text-amber-600 dark:text-amber-400 font-medium">Minimal 20 karakter agar uraian jelas.</p>
              <p class="text-[9px] text-gray-400 ml-auto">{{ form.message.length }}/5000</p>
            </div>
          </div>

          <!-- Attachment -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Lampiran Dokumen Pendukung (Opsional)</label>
            <div 
              @click="$refs.attachmentInput.click()"
              class="border-2 border-dashed border-gray-200 dark:border-zinc-700 hover:border-primary-500 rounded-2xl p-4 text-center cursor-pointer bg-gray-50/60 dark:bg-zinc-800/40 hover:bg-primary-50/30 transition group relative"
            >
              <input 
                type="file" 
                ref="attachmentInput" 
                @change="onFile" 
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                class="hidden" 
              />
              
              <!-- Selected File Preview -->
              <div v-if="fileDetails" class="space-y-3">
                <!-- Image Thumbnail Preview if Image -->
                <div v-if="imagePreviewUrl" class="relative group/img overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-700 bg-black/5 dark:bg-black/40 flex items-center justify-center p-2 max-h-48">
                  <img :src="imagePreviewUrl" alt="Preview Lampiran" class="max-h-40 rounded-lg object-contain shadow-sm" />
                </div>

                <!-- File Info Badge & Remove Button -->
                <div class="flex items-center justify-between gap-3 p-3 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-xl shadow-xs text-left">
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-950/60 text-primary-600 flex items-center justify-center shrink-0 font-bold text-[10px] uppercase font-mono">
                      {{ fileDetails.isImage ? 'IMG' : 'DOC' }}
                    </div>
                    <div class="min-w-0">
                      <p class="text-xs font-extrabold text-gray-800 dark:text-zinc-100 truncate">{{ fileDetails.name }}</p>
                      <p class="text-[10px] font-medium text-gray-400 font-mono">{{ fileDetails.size }}</p>
                    </div>
                  </div>
                  <button 
                    type="button" 
                    @click.stop="clearFile" 
                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/60 transition shrink-0 flex items-center gap-1 cursor-pointer"
                  >
                    <X class="w-3.5 h-3.5" />
                    Hapus
                  </button>
                </div>
              </div>

              <!-- Default Prompt -->
              <div v-else class="py-2 flex flex-col items-center justify-center">
                <UploadCloud class="w-5 h-5 text-gray-400 group-hover:text-primary-600 mb-1 transition" />
                <p class="text-xs font-bold text-gray-700 dark:text-zinc-300">
                  <span class="text-primary-600 hover:underline">Klik untuk mengunggah dokumen pendukung</span> atau tarik berkas ke sini
                </p>
                <p class="text-[9px] text-gray-400 mt-0.5">Format: PDF, JPG, PNG, DOC (Maksimal 10MB)</p>
              </div>
            </div>
            <p v-if="errors.attachment" class="text-xs text-red-500 font-bold mt-1.5 flex items-center gap-1">⚠️ {{ errors.attachment }}</p>
          </div>

          <!-- Math CAPTCHA -->
          <div class="p-5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-zinc-700 rounded-2xl space-y-3">
            <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Verifikasi Anti-Robot</p>
            <p class="text-sm font-extrabold text-gray-800 dark:text-zinc-100">
              Berapa hasil dari: <span class="text-primary-600 font-black">{{ num1 }} + {{ num2 }}</span> = ?
            </p>
            <div class="flex items-center gap-3">
              <input v-model.number="form.captcha_answer" type="number" required
                placeholder="Jawaban"
                class="w-32 px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none" />
              <button type="button" @click="refreshCaptcha"
                class="text-[9px] text-primary-600 font-bold hover:underline">
                Ganti soal
              </button>
            </div>
            <p v-if="errors.captcha_answer" class="text-[9px] text-red-500 font-semibold mt-0.5">{{ errors.captcha_answer }}</p>
          </div>

          <!-- Submit -->
          <div class="pt-2">
            <button type="submit" :disabled="submitting"
              class="w-full py-3 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white text-sm font-black rounded-2xl shadow-lg shadow-primary-500/20 transition active:scale-[0.98] disabled:opacity-50">
              {{ submitting ? 'Mengirim Pengaduan...' : '📨 Kirim Pengaduan Sekarang' }}
            </button>
            <p class="text-center text-[9px] text-gray-400 mt-3">
              Sudah punya tiket?
              <Link :href="route('public.complaint.track')" class="text-primary-600 font-bold hover:underline">
                Lacak status pengaduan →
              </Link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { UploadCloud, X } from '@lucide/vue';

const attachmentInput = ref(null);

const props = defineProps({ 
  categories: Array,
  captcha: Object,
});
const page  = usePage();

// Form state
const form = reactive({
  submitter_name:        '',
  submitter_phone:       '',
  submitter_email:       '',
  is_anonymous:          false,
  complaint_category_id: null,
  subject:               '',
  message:               '',
  captcha_answer:        '',
  captcha_hash:          '',
});
let selectedFile      = null;
const imagePreviewUrl = ref(null);
const fileDetails     = ref(null);
const submitting      = ref(false);
const errors          = ref({});

const num1 = computed(() => props.captcha?.num1 || 5);
const num2 = computed(() => props.captcha?.num2 || 3);

const refreshCaptcha = () => {
  router.reload({ only: ['captcha'] });
};

const onFile = (e) => { 
  const file = e.target.files[0];
  if (!file) {
    clearFile();
    return;
  }

  selectedFile = file;
  fileDetails.value = {
    name: file.name,
    size: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
    type: file.type,
    isImage: file.type.startsWith('image/'),
  };

  if (file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = (event) => {
      imagePreviewUrl.value = event.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    imagePreviewUrl.value = null;
  }
};

const clearFile = () => {
  selectedFile = null;
  imagePreviewUrl.value = null;
  fileDetails.value = null;
  if (attachmentInput.value) {
    attachmentInput.value.value = '';
  }
};

const submitForm = () => {
  errors.value = {};

  if (!form.complaint_category_id) {
    errors.value.complaint_category_id = 'Silakan pilih salah satu Kategori Pengaduan terlebih dahulu.';
  }
  if (!form.is_anonymous && (!form.submitter_name || !form.submitter_name.trim())) {
    errors.value.submitter_name = 'Nama lengkap wajib diisi (atau centang Kirim sebagai Anonim).';
  }
  if (!form.subject || !form.subject.trim()) {
    errors.value.subject = 'Judul / pokok pengaduan wajib diisi.';
  }
  if (!form.message || form.message.trim().length < 20) {
    errors.value.message = 'Uraian pengaduan wajib diisi minimal 20 karakter agar kronologi jelas.';
  }
  if (form.captcha_answer === '' || form.captcha_answer === null || form.captcha_answer === undefined) {
    errors.value.captcha_answer = 'Jawaban verifikasi anti-robot wajib diisi.';
  }

  if (Object.keys(errors.value).length > 0) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  submitting.value = true;



  const data = new FormData();
  Object.entries(form).forEach(([k, v]) => {
    if (k === 'is_anonymous') {
      data.append(k, v ? '1' : '0');
    } else if (v !== null && v !== undefined) {
      data.append(k, v);
    }
  });
  data.set('captcha_hash', props.captcha?.hash || '');
  if (selectedFile) data.append('attachment', selectedFile);

  router.post(route('public.complaint.store'), data, {
    forceFormData: true,
    onError: (errs) => { 
      errors.value = errs; 
      submitting.value = false; 
    },
    onFinish: () => { 
      submitting.value = false; 
    },
  });
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
