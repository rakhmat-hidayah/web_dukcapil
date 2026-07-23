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
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" v-if="categories.length">
        <div
          v-for="cat in categories" :key="cat.id"
          @click="form.complaint_category_id = cat.id"
          class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col gap-2"
          :class="form.complaint_category_id === cat.id
            ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/30'
            : 'border-gray-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-gray-300'"
        >
          <span class="text-2xl">{{ cat.icon }}</span>
          <p class="text-xs font-extrabold text-gray-800 dark:text-zinc-100 leading-snug">{{ cat.name }}</p>
          <p class="text-[9px] text-gray-400 leading-relaxed line-clamp-2">{{ cat.description }}</p>
        </div>
      </div>

      <!-- Main form card -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-8 shadow-sm">
        <form @submit.prevent="submitForm" class="space-y-5" enctype="multipart/form-data">

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
                  required 
                  type="text" 
                  :disabled="form.is_anonymous"
                  placeholder="Nama sesuai KTP"
                  class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" 
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
              required 
              type="text" 
              maxlength="255"
              placeholder="Tulis ringkasan masalah secara singkat dan jelas"
              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" 
            />
            <p v-if="errors.subject" class="text-[9px] text-red-500 font-semibold mt-0.5">{{ errors.subject }}</p>
          </div>

          <!-- Message -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Uraian Pengaduan *</label>
            <textarea 
              v-model="form.message" 
              required 
              rows="6" 
              minlength="20" 
              maxlength="5000"
              placeholder="Jelaskan kronologi, lokasi, dan fakta pendukung pengaduan Anda..."
              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            ></textarea>
            <div class="flex justify-between mt-1">
              <p v-if="errors.message" class="text-[9px] text-red-500 font-semibold mt-0.5">{{ errors.message }}</p>
              <p class="text-[9px] text-gray-400 ml-auto">{{ form.message.length }}/5000</p>
            </div>
          </div>

          <!-- Attachment -->
          <div>
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Lampiran Dokumen Pendukung (opsional)</label>
            <input type="file" @change="onFile" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
              class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-600 hover:file:bg-primary-100 transition" />
            <p class="text-[9px] text-gray-400 mt-1">Format: PDF, JPG, PNG, DOC. Maks. 5MB.</p>
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
let selectedFile  = null;
const submitting  = ref(false);
const errors      = ref({});

const num1 = computed(() => props.captcha?.num1 || 5);
const num2 = computed(() => props.captcha?.num2 || 3);

const refreshCaptcha = () => {
  router.reload({ only: ['captcha'] });
};

const onFile = (e) => { selectedFile = e.target.files[0] || null; };

const submitForm = () => {
  errors.value = {};
  submitting.value = true;

  const data = new FormData();
  Object.entries(form).forEach(([k, v]) => {
    if (v !== null && v !== undefined) data.append(k, v);
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
