<template>
  <Head :title="isEdit ? 'Edit Berita' : 'Tambah Berita Baru'" />

  <AdminLayout>
    <div class="space-y-6 text-left max-w-5xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center border-b border-gray-100 dark:border-zinc-800 pb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-50 tracking-tight">
            {{ isEdit ? 'Edit Berita' : 'Buat Artikel Berita Baru' }}
          </h1>
          <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
            Kelola artikel publikasi, informasi layanan kependudukan, dan foto pendukung.
          </p>
        </div>
        <Link 
          :href="route('admin.news.index')"
          class="px-4 py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold rounded-xl transition"
        >
          ← Kembali
        </Link>
      </div>

      <!-- Server & Client Error Notification Banner -->
      <div v-if="form.errors && Object.keys(form.errors).length > 0" class="p-4 bg-rose-50 dark:bg-rose-950/60 border-2 border-rose-300 dark:border-rose-800 rounded-2xl flex items-start gap-3 shadow-md">
        <AlertTriangle class="w-6 h-6 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" />
        <div class="space-y-1 text-xs">
          <h4 class="font-black text-rose-800 dark:text-rose-200 uppercase tracking-wide">⚠️ Gagal Menyimpan Berita</h4>
          <ul class="list-disc list-inside text-rose-700 dark:text-rose-300 space-y-0.5 font-semibold">
            <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
          </ul>
        </div>
      </div>

      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Form Card (Col Span 2) -->
        <div class="md:col-span-2 space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Konten Utama Berita</h3>

            <!-- Title -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Judul Artikel Berita</label>
              <input 
                type="text" 
                v-model="form.title" 
                required
                placeholder="Tulis judul berita yang menarik..."
                class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <!-- Excerpt -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Ringkasan Singkat (Excerpt)</label>
              <textarea 
                v-model="form.excerpt" 
                rows="2"
                placeholder="Tulis ringkasan singkat berita untuk feed halaman utama (opsional)..."
                class="w-full px-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              ></textarea>
            </div>

            <!-- Visual Tiptap Rich Editor -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-semibold text-gray-500">Isi Berita Lengkap *</label>
                <span class="text-[10px] text-primary-600 dark:text-primary-400 font-semibold flex items-center gap-1 bg-primary-50 dark:bg-primary-950/50 px-2.5 py-1 rounded-lg border border-primary-200 dark:border-primary-900/40">
                  📁 Foto disisipkan otomatis tersimpan di Media Manager ➔ Folder "Berita"
                </span>
              </div>
              <RichEditor v-model="form.content" placeholder="Tuliskan isi berita, laporan kegiatan, atau pengumuman publik di sini..." min-height="380px" />
            </div>
          </div>

          <!-- SEO Settings Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 dark:border-zinc-800 pb-3">
              <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">SEO Meta Tags</h3>

              <button 
                type="button" 
                @click="generateSeoAuto"
                class="px-3 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-[11px] font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm active:scale-95"
                title="Generate otomatis Meta Title dan Meta Description dari Judul & Isi Berita"
              >
                <Sparkles class="w-3.5 h-3.5" />
                <span>Generate SEO Otomatis</span>
              </button>
            </div>
            
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-semibold text-gray-500">Meta Title Tag</label>
                <span class="text-[10px] font-mono" :class="form.meta_title.length > 70 ? 'text-amber-500 font-bold' : 'text-gray-400'">
                  {{ form.meta_title.length }} / 70 Karakter
                </span>
              </div>
              <input 
                type="text" 
                v-model="form.meta_title" 
                placeholder="Judul SEO jika berbeda dari judul berita..."
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-semibold text-gray-500">Meta Description Tag</label>
                <span class="text-[10px] font-mono" :class="form.meta_description.length > 160 ? 'text-amber-500 font-bold' : 'text-gray-400'">
                  {{ form.meta_description.length }} / 160 Karakter
                </span>
              </div>
              <textarea 
                v-model="form.meta_description" 
                rows="3"
                placeholder="Deskripsi ringkas yang muncul di hasil pencarian Google & preview sosmed..."
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Sidebar options (Col Span 1) -->
        <div class="space-y-6">
          <!-- Publish controls -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Publish Setting</h3>

            <!-- Status selector -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Status Rilis</label>
              <select 
                v-model="form.status"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              >
                <option value="draft">Draft (Simpan Internal)</option>
                <option value="published">Diterbitkan (Online)</option>
                <option value="scheduled">Dijadwalkan (Otomatis)</option>
              </select>
            </div>

            <!-- Published At -->
            <div v-if="form.status === 'scheduled' || form.status === 'published'">
              <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal & Jam Tayang</label>
              <input 
                type="datetime-local" 
                v-model="form.published_at"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              />
            </div>

            <!-- Switches -->
            <div class="space-y-3 pt-2">
              <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.is_featured" class="accent-primary-500 rounded border-gray-300" />
                <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300">Sematkan sebagai Utama (Featured)</span>
              </label>
              <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" v-model="form.is_breaking" class="accent-primary-500 rounded border-gray-300" />
                <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300">Kabar Kilat (Breaking News)</span>
              </label>
            </div>

            <!-- Publication History Info (CMS Admin Only) -->
            <div v-if="isEdit && news" class="p-3 bg-gray-50 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 rounded-2xl space-y-1.5 text-[11px]">
              <div class="flex items-center justify-between text-gray-500">
                <span>Dipublikasikan:</span>
                <strong class="text-gray-800 dark:text-zinc-200">{{ formatDateTime(news.published_at) || 'Belum Dipublikasi' }}</strong>
              </div>
              <div class="flex items-center justify-between text-gray-500">
                <span>Edit Terakhir:</span>
                <strong class="text-gray-800 dark:text-zinc-200">{{ formatDateTime(news.updated_at) }}</strong>
              </div>
              <div class="flex items-center justify-between text-gray-500">
                <span>Editor Terakhir:</span>
                <strong class="text-primary-600 dark:text-primary-400 font-bold">{{ news.last_updated_user ? news.last_updated_user.name : (news.author ? news.author.name : 'Admin Humas') }}</strong>
              </div>
            </div>

            <!-- Submit buttons -->
            <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex flex-col gap-2">
              <button 
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/10 active:scale-95 transition disabled:opacity-50"
              >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Berita' }}
              </button>
              <Link 
                :href="route('admin.news.index')"
                class="w-full text-center py-2 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 text-xs font-bold rounded-xl transition"
              >
                Batal
              </Link>
            </div>
          </div>

          <!-- Category & Tags card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Taksonomi</h3>

            <!-- Category -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori Rubrik</label>
              <select 
                v-model="form.news_category_id"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
              >
                <option :value="null">Pilih Kategori...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>

            <!-- Tag checklist & custom input -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tags Label / Topik Berita</label>

              <!-- Custom Tag Input -->
              <input 
                type="text" 
                v-model="form.custom_tags" 
                placeholder="Ketik topik berita (koma untuk pemisah), contoh: DAK, Pelayanan, Dompu"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none mb-2"
              />

              <!-- Checklist of existing tags (if available) -->
              <div v-if="tags && tags.length > 0" class="p-3 bg-gray-50 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 rounded-xl max-h-32 overflow-y-auto scrollbar-thin flex flex-wrap gap-1.5">
                <label 
                  v-for="tag in tags" 
                  :key="tag.id" 
                  class="flex items-center gap-1.5 px-2 py-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg cursor-pointer text-[10px]"
                >
                  <input type="checkbox" :value="tag.id" v-model="form.tag_ids" class="accent-primary-500 rounded border-gray-300 w-3 h-3" />
                  <span class="font-medium text-gray-700 dark:text-zinc-300">#{{ tag.name }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Thumbnail Upload Card -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Gambar Cover (Thumbnail)</h3>
            
            <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-zinc-800 rounded-2xl p-4 text-center">
              <img 
                v-if="imagePreview" 
                :src="imagePreview" 
                class="w-full max-h-40 object-cover rounded-xl mb-3 border border-gray-100 dark:border-zinc-800"
              />
              <Image class="w-8 h-8 text-gray-300 mb-2" v-if="!imagePreview" />
              
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleFileUpload" 
                class="hidden" 
                accept="image/*"
              />
              <div class="flex flex-wrap justify-center gap-2">
                <button 
                  type="button"
                  @click="$refs.fileInput.click()"
                  class="px-3 py-1.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 text-gray-600 dark:text-zinc-300 text-[10px] font-bold rounded-lg transition"
                >
                  {{ imagePreview ? 'Ganti Gambar' : 'Pilih File Gambar' }}
                </button>

                <button 
                  v-if="imagePreview"
                  type="button"
                  @click="removeThumbnail"
                  class="flex items-center gap-1 px-3 py-1.5 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 hover:bg-red-100 border border-red-200 dark:border-red-900/40 text-[10px] font-bold rounded-lg transition"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                  <span>Hapus Cover</span>
                </button>
              </div>
              <p class="text-[9px] text-gray-400 mt-2">Maksimal 10MB (Format: JPG, PNG, WebP)</p>
            </div>
          </div>

          <!-- === HERO IMAGE SETTINGS PANEL === -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2">
              <Image class="w-3.5 h-3.5 text-primary-500" />
              Pengaturan Hero Image
            </h3>

            <!-- Caption -->
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider block">
                Caption Foto (Opsional)
              </label>
              <textarea
                v-model="form.thumbnail_caption"
                rows="2"
                placeholder="Contoh: Dokumentasi kegiatan pelayanan Disdukcapil bersama Kodim 1614 Dompu di Desa Nusajaya."
                class="w-full px-3 py-2 text-[11px] bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent dark:text-zinc-200 resize-none leading-relaxed"
                maxlength="500"
              ></textarea>
              <p class="text-[9px] text-gray-400">{{ (form.thumbnail_caption || '').length }}/500 karakter. Muncul di bawah foto berita.</p>
            </div>

            <!-- Alt Text -->
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider block">
                Alt Text / Teks Alternatif (SEO)
              </label>
              <input
                v-model="form.thumbnail_alt"
                type="text"
                placeholder="Contoh: Pelayanan adminduk Disdukcapil Dompu"
                class="w-full px-3 py-2 text-[11px] bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent dark:text-zinc-200"
                maxlength="255"
              />
              <p class="text-[9px] text-gray-400">Digunakan oleh screen reader & mesin pencari (SEO).</p>
            </div>

            <!-- Multi-device Hero Image Preview -->
            <div v-if="imagePreview" class="space-y-2">
              <span class="text-[10px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider block">Preview Hero Image</span>

              <!-- Device Tabs -->
              <div class="flex items-center gap-1 bg-gray-100 dark:bg-zinc-800 p-0.5 rounded-xl text-[10px] w-fit">
                <button
                  type="button"
                  v-for="d in ['desktop','tablet','mobile']"
                  :key="d"
                  @click="heroPreviewDevice = d"
                  :class="heroPreviewDevice === d ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-zinc-50 shadow-sm' : 'text-gray-500 dark:text-zinc-400 hover:text-gray-700'"
                  class="px-2.5 py-1 rounded-lg font-bold transition capitalize"
                >{{ d === 'desktop' ? '🖥' : d === 'tablet' ? '📱' : '📲' }} {{ d }}</button>
              </div>

              <!-- Preview Container -->
              <div
                class="overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800/50 transition-all duration-200 mx-auto"
                :class="{
                  'w-full': heroPreviewDevice === 'desktop',
                  'max-w-[320px]': heroPreviewDevice === 'tablet',
                  'max-w-[200px]': heroPreviewDevice === 'mobile',
                }"
              >
                <img
                  :src="imagePreview"
                  :alt="form.thumbnail_alt || form.title || 'Preview Hero Image'"
                  class="w-full object-cover"
                  style="aspect-ratio: 16/9;"
                />
                <div v-if="form.thumbnail_caption" class="px-2 py-1.5 text-[9px] text-gray-400 italic text-center leading-tight truncate">
                  📷 {{ form.thumbnail_caption }}
                </div>
              </div>
            </div>
          </div>

          <!-- Enterprise Social Sharing, Open Graph & Share Preview System -->
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4">

            <!-- Header Row -->
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-gray-900 dark:text-zinc-50 flex items-center gap-2">
                  <Share2 class="w-4 h-4 text-primary-500" />
                  Pratinjau Share Sosmed & SEO
                </h3>
                <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-0.5">
                  Real-time preview saat berita dibagikan ke media sosial.
                </p>
              </div>

              <!-- Controls: Device + Dark Mode + Test -->
              <div class="flex items-center gap-2 flex-wrap">
                <div class="flex items-center gap-0.5 bg-gray-100 dark:bg-zinc-800 p-0.5 rounded-xl text-[10px]">
                  <button type="button" @click="viewDevice = 'mobile'" :class="viewDevice === 'mobile' ? 'bg-white dark:bg-zinc-700 font-bold shadow' : 'text-gray-500'" class="px-2 py-1.5 rounded-lg transition flex flex-col items-center gap-0.5">
                    <span>📱</span><span>Mobile</span>
                  </button>
                  <button type="button" @click="viewDevice = 'tablet'" :class="viewDevice === 'tablet' ? 'bg-white dark:bg-zinc-700 font-bold shadow' : 'text-gray-500'" class="px-2 py-1.5 rounded-lg transition flex flex-col items-center gap-0.5">
                    <span>💻</span><span>Tablet</span>
                  </button>
                  <button type="button" @click="viewDevice = 'desktop'" :class="viewDevice === 'desktop' ? 'bg-white dark:bg-zinc-700 font-bold shadow' : 'text-gray-500'" class="px-2 py-1.5 rounded-lg transition flex flex-col items-center gap-0.5">
                    <span>🖥️</span><span>Desktop</span>
                  </button>
                </div>
                <button type="button" @click="previewDarkMode = !previewDarkMode"
                  class="px-2.5 py-1.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-[10px] font-bold rounded-xl border border-gray-200 dark:border-zinc-700 transition">
                  {{ previewDarkMode ? '🌙 Dark' : '☀️ Light' }}
                </button>
                <a v-if="form.title" :href="testShareUrl" target="_blank"
                  class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold rounded-xl transition flex items-center gap-1 shadow">
                  <ExternalLink class="w-3 h-3" /> Test Share
                </a>
              </div>
            </div>

            <!-- SEO Score + Readiness Badges Row -->
            <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-gray-50 dark:bg-zinc-800/60 rounded-2xl border border-gray-200/70 dark:border-zinc-700/60">
              <div class="flex flex-wrap gap-1.5">
                <span :class="seoScore >= 70 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                  class="px-2 py-1 rounded-full border text-[10px] font-bold flex items-center gap-1">
                  {{ seoScore >= 70 ? '🟢' : '🟡' }} WhatsApp
                </span>
                <span :class="seoScore >= 70 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                  class="px-2 py-1 rounded-full border text-[10px] font-bold flex items-center gap-1">
                  {{ seoScore >= 70 ? '🟢' : '🟡' }} Facebook
                </span>
                <span :class="(form.excerpt || form.meta_description) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                  class="px-2 py-1 rounded-full border text-[10px] font-bold flex items-center gap-1">
                  {{ (form.excerpt || form.meta_description) ? '🟢' : '🟡' }} Telegram
                </span>
                <span :class="form.title ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200'"
                  class="px-2 py-1 rounded-full border text-[10px] font-bold flex items-center gap-1">
                  {{ form.title ? '🟢' : '🔴' }} X / LinkedIn
                </span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-semibold text-gray-500">Skor SEO:</span>
                <span :class="seoScore >= 80 ? 'bg-emerald-600' : seoScore >= 50 ? 'bg-amber-500' : 'bg-red-500'"
                  class="px-3 py-1 text-white rounded-xl text-xs font-black shadow-sm">{{ seoScore }}%</span>
              </div>
            </div>

            <!-- Warnings -->
            <div v-if="seoWarnings.length > 0" class="space-y-1.5">
              <div v-for="(warn, wIdx) in seoWarnings" :key="wIdx"
                class="text-[11px] font-semibold text-amber-700 dark:text-amber-300 flex items-start gap-1.5 bg-amber-50 dark:bg-amber-950/40 p-2.5 rounded-xl border border-amber-200 dark:border-amber-900/40">
                <AlertTriangle class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" />
                <span>{{ warn }}</span>
              </div>
            </div>

            <!-- Platform Tab Buttons -->
            <div class="flex flex-wrap gap-1.5">
              <button type="button" @click="activeSocialTab = 'whatsapp'"
                :class="activeSocialTab === 'whatsapp' ? 'bg-emerald-600 text-white ring-2 ring-emerald-400/30' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'"
                class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5">
                <MessageCircle class="w-3.5 h-3.5" /> WhatsApp
              </button>
              <button type="button" @click="activeSocialTab = 'facebook'"
                :class="activeSocialTab === 'facebook' ? 'bg-blue-600 text-white ring-2 ring-blue-400/30' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'"
                class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Facebook
              </button>
              <button type="button" @click="activeSocialTab = 'telegram'"
                :class="activeSocialTab === 'telegram' ? 'bg-sky-500 text-white ring-2 ring-sky-400/30' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'"
                class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5">
                <Send class="w-3.5 h-3.5" /> Telegram
              </button>
              <button type="button" @click="activeSocialTab = 'twitter'"
                :class="activeSocialTab === 'twitter' ? 'bg-zinc-900 text-white ring-2 ring-zinc-400/20' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'"
                class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                X (Twitter)
              </button>
              <button type="button" @click="activeSocialTab = 'linkedin'"
                :class="activeSocialTab === 'linkedin' ? 'bg-sky-700 text-white ring-2 ring-sky-400/30' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'"
                class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                LinkedIn
              </button>
              <button type="button" @click="activeSocialTab = 'googlechat'"
                :class="activeSocialTab === 'googlechat' ? 'bg-emerald-700 text-white ring-2 ring-emerald-400/30' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'"
                class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5">
                <MessageSquare class="w-3.5 h-3.5" /> Google Chat
              </button>
              <button type="button" @click="activeSocialTab = 'inspector'"
                :class="activeSocialTab === 'inspector' ? 'bg-purple-600 text-white ring-2 ring-purple-400/30' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'"
                class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5">
                <Code class="w-3.5 h-3.5" /> Inspector OG
              </button>
            </div>

            <!-- Preview Card Container -->
            <div :class="[previewDarkMode ? 'dark bg-zinc-950' : 'bg-gray-100', viewDeviceClass]"
              class="p-4 rounded-2xl border border-gray-200 dark:border-zinc-800 transition-all duration-300 mx-auto">

              <!-- A. WhatsApp Preview -->
              <div v-if="activeSocialTab === 'whatsapp'" class="max-w-sm mx-auto">
                <div class="bg-[#efeae2] dark:bg-[#111b21] p-3 rounded-2xl shadow-sm">
                  <div class="bg-white dark:bg-[#202c33] rounded-xl overflow-hidden shadow border border-gray-200/80 dark:border-zinc-700">
                    <div class="relative aspect-[1.91/1] bg-gray-200 dark:bg-zinc-800 overflow-hidden">
                      <img :src="previewImageSrc" class="w-full h-full object-cover" alt="Preview" />
                      <span class="absolute top-2 left-2 px-2 py-0.5 bg-black/60 text-white text-[9px] font-mono rounded">{{ selectedCategoryName }}</span>
                    </div>
                    <div class="p-3 space-y-1">
                      <p class="font-bold text-[11px] text-gray-900 dark:text-zinc-100 line-clamp-2 leading-snug">{{ previewTitle }}</p>
                      <p class="text-[10px] text-gray-500 dark:text-zinc-400 line-clamp-2 leading-tight">{{ previewDesc }}</p>
                      <p class="text-[9px] text-gray-400 font-mono pt-0.5">dukcapil.dompukab.go.id</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- B. Facebook Preview -->
              <div v-else-if="activeSocialTab === 'facebook'" class="max-w-md mx-auto bg-white dark:bg-[#242526] rounded-2xl border border-gray-200 dark:border-zinc-700 overflow-hidden shadow-md">
                <div class="p-3 flex items-center gap-2 border-b border-gray-100 dark:border-zinc-800">
                  <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">D</div>
                  <div>
                    <p class="font-bold text-xs text-gray-900 dark:text-zinc-100">Disdukcapil Kab. Dompu</p>
                    <p class="text-[9px] text-gray-400">Baru saja • 🌐</p>
                  </div>
                </div>
                <div class="px-3 py-2 text-[11px] text-gray-800 dark:text-zinc-200 line-clamp-2">{{ previewDesc }}</div>
                <img :src="previewImageSrc" class="w-full aspect-[1.91/1] object-cover" />
                <div class="p-3 bg-gray-50 dark:bg-[#18191a] space-y-0.5 border-t border-gray-100 dark:border-zinc-800">
                  <p class="text-[9px] text-gray-400 font-mono uppercase">DUKCAPIL.DOMPUKAB.GO.ID</p>
                  <p class="font-extrabold text-xs text-gray-900 dark:text-zinc-100 line-clamp-1">{{ previewTitle }}</p>
                </div>
              </div>

              <!-- C. Telegram Preview -->
              <div v-else-if="activeSocialTab === 'telegram'" class="max-w-md mx-auto bg-[#17212b] p-3 rounded-2xl text-white shadow-md space-y-2">
                <div class="bg-[#242f3d] p-3 rounded-xl border-l-4 border-sky-400 space-y-2">
                  <p class="text-[10px] font-bold text-sky-400">dukcapil.dompukab.go.id</p>
                  <p class="font-bold text-xs text-white leading-snug">{{ previewTitle }}</p>
                  <p class="text-[11px] text-gray-300 line-clamp-3 leading-relaxed">{{ previewDesc }}</p>
                  <img :src="previewImageSrc" class="w-full rounded-xl aspect-[1.91/1] object-cover mt-1" />
                </div>
              </div>

              <!-- D. X Twitter Preview -->
              <div v-else-if="activeSocialTab === 'twitter'" class="max-w-md mx-auto bg-black text-white p-4 rounded-3xl border border-zinc-800 shadow-md space-y-3">
                <div class="flex items-center gap-2 text-xs">
                  <span class="font-bold">Dukcapil Dompu</span>
                  <span class="text-gray-500">@dukcapildompu • 1m</span>
                </div>
                <p class="text-xs text-gray-200 line-clamp-2">{{ previewDesc }}</p>
                <div class="rounded-2xl border border-zinc-800 overflow-hidden">
                  <img :src="previewImageSrc" class="w-full aspect-[1.91/1] object-cover" />
                  <div class="p-3 bg-zinc-900 space-y-0.5">
                    <p class="text-[9px] text-gray-400 font-mono">🌐 dukcapil.dompukab.go.id</p>
                    <p class="font-bold text-xs text-white line-clamp-1">{{ previewTitle }}</p>
                  </div>
                </div>
              </div>

              <!-- E. LinkedIn Preview -->
              <div v-else-if="activeSocialTab === 'linkedin'" class="max-w-md mx-auto bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-md">
                <img :src="previewImageSrc" class="w-full aspect-[1.91/1] object-cover" />
                <div class="p-3 bg-gray-50 dark:bg-zinc-800/80 border-t border-gray-100 dark:border-zinc-800 space-y-0.5">
                  <p class="font-bold text-xs text-gray-900 dark:text-zinc-100 line-clamp-1">{{ previewTitle }}</p>
                  <p class="text-[10px] text-gray-500 dark:text-zinc-400 font-mono">dukcapil.dompukab.go.id</p>
                </div>
              </div>

              <!-- F. Google Chat Preview -->
              <div v-else-if="activeSocialTab === 'googlechat'" class="max-w-md mx-auto bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 p-3 rounded-2xl shadow-md space-y-2">
                <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">🟢 Google Chat Open Graph Card</p>
                <div class="border border-gray-100 dark:border-zinc-800 rounded-xl p-3 bg-gray-50 dark:bg-zinc-800/50 space-y-2">
                  <p class="font-bold text-xs text-gray-900 dark:text-zinc-100">{{ previewTitle }}</p>
                  <img :src="previewImageSrc" class="w-full rounded-xl aspect-[1.91/1] object-cover" />
                  <p class="text-[10px] text-gray-500 dark:text-zinc-400 line-clamp-2">{{ previewDesc }}</p>
                </div>
              </div>

              <!-- G. Metadata Inspector -->
              <div v-else-if="activeSocialTab === 'inspector'" class="p-4 bg-zinc-950 text-emerald-400 font-mono text-[11px] rounded-2xl overflow-x-auto leading-relaxed border border-zinc-800 select-all">
                <p class="text-zinc-500 mb-2">// Open Graph Meta Tags yang akan dirender server</p>
                <p>&lt;meta property="og:type" content="article"&gt;</p>
                <p>&lt;meta property="og:title" content="<span class="text-yellow-300">{{ previewTitle }}</span>"&gt;</p>
                <p>&lt;meta property="og:description" content="<span class="text-sky-300">{{ previewDesc }}</span>"&gt;</p>
                <p>&lt;meta property="og:image" content="<span class="text-pink-300">{{ previewImageSrc }}</span>"&gt;</p>
                <p>&lt;meta property="og:image:width" content="1200"&gt;</p>
                <p>&lt;meta property="og:image:height" content="630"&gt;</p>
                <p>&lt;meta property="og:url" content="<span class="text-emerald-300">{{ previewUrl }}</span>"&gt;</p>
                <p>&lt;meta name="twitter:card" content="summary_large_image"&gt;</p>
                <p>&lt;link rel="canonical" href="<span class="text-emerald-300">{{ previewUrl }}</span>"&gt;</p>
                <p class="mt-3 text-sky-400">&lt;script type="application/ld+json"&gt;</p>
                <pre class="text-sky-300 text-[10px] pl-4 whitespace-pre-wrap">{{ inspectorSchemaJson }}</pre>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Modal Notifikasi Gagal Simpan Berita / Gambar Terlalu Besar -->
      <transition name="fade">
        <div v-if="showSubmitErrorModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showSubmitErrorModal = false">
          <div class="bg-white dark:bg-zinc-900 border border-rose-200 dark:border-zinc-800 rounded-3xl w-full max-w-md shadow-2xl p-6 text-left relative space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-zinc-800 pb-3">
              <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 font-bold">
                <AlertTriangle class="w-6 h-6" />
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-zinc-50">Gagal Menyimpan Berita</h3>
                <p class="text-[10px] text-gray-400">Pemberitahuan Sistem Ukuran File & Validasi</p>
              </div>
            </div>

            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 rounded-2xl border border-rose-200 dark:border-rose-900/50 space-y-2 text-xs">
              <p class="text-rose-700 dark:text-rose-300 font-bold leading-relaxed whitespace-pre-line">
                {{ submitErrorMessage }}
              </p>
            </div>

            <div class="flex justify-end pt-2">
              <button @click="showSubmitErrorModal = false" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-md active:scale-95 transition">
                Saya Mengerti & Perbaiki
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichEditor from '@/Components/Editors/RichEditor.vue';
import { 
  Image, Camera, Link as LinkIcon, Trash2, Loader2, Video,
  Share2, MessageCircle, Send, MessageSquare, Code, ExternalLink, AlertTriangle, CheckCircle2, Globe, Sparkles
} from '@lucide/vue';
import axios from 'axios';

const props = defineProps({
  news: Object,
  categories: Array,
  tags: Array,
});

const submitErrorMessage = ref('');
const showSubmitErrorModal = ref(false);

const isEdit = computed(() => !!props.news);
const fileInput = ref(null);
const inlinePhotoInput = ref(null);
const editorContainer = ref(null);
const isUploadingInlinePhoto = ref(false);
const isEditorLoading = ref(true);
const imagePreview = ref(props.news?.thumbnail ? (props.news.thumbnail.startsWith('http') || props.news.thumbnail.startsWith('/uploads/') ? props.news.thumbnail : `/uploads/${props.news.thumbnail.replace(/^\/?(storage|uploads)\//, '')}`) : null);

// Social Preview State
const activeSocialTab = ref('whatsapp');
const viewDevice = ref('desktop');
const previewDarkMode = ref(false);

const viewDeviceClass = computed(() => {
  if (viewDevice.value === 'mobile') return 'max-w-sm';
  if (viewDevice.value === 'tablet') return 'max-w-xl';
  return 'max-w-3xl';
});

const previewTitle = computed(() => form.meta_title || form.title || 'Judul Artikel Berita');
const previewDesc = computed(() => form.meta_description || form.excerpt || 'Ringkasan singkat artikel berita publikasi layanan kependudukan Disdukcapil Dompu...');
const previewSlug = computed(() => form.title ? form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') : 'judul-berita');
const previewUrl = computed(() => `https://dukcapil.dompukab.go.id/news/${previewSlug.value}`);
const testShareUrl = computed(() => route('public.news.show', previewSlug.value));

const previewImageSrc = computed(() => {
  if (imagePreview.value) return imagePreview.value;
  if (inlineImages.value && inlineImages.value.length > 0) return inlineImages.value[0];
  return '/android-chrome-512x512.png';
});

const selectedCategoryName = computed(() => {
  if (!form.news_category_id || !props.categories) return 'BERITA';
  const cat = props.categories.find(c => c.id === form.news_category_id);
  return cat ? cat.name.toUpperCase() : 'BERITA';
});

// Computed SEO Score & Warnings
const seoWarnings = computed(() => {
  const warn = [];
  if (!form.title) warn.push('Judul berita belum diisi.');
  else if (form.title.length < 20) warn.push('Judul berita terlalu pendek (< 20 karakter). Rekomendasi 40 - 70 karakter.');

  if (!form.excerpt && !form.meta_description) warn.push('Ringkasan / Meta Description belum diisi.');
  if (!form.thumbnail && inlineImages.value.length === 0) warn.push('Thumbnail cover tidak diunggah (menggunakan banner generator otomatis).');
  return warn;
});

const seoScore = computed(() => {
  let score = 100;
  if (!form.title) score -= 30;
  if (!form.excerpt && !form.meta_description) score -= 30;
  if (!form.thumbnail) score -= 20;
  if (!form.content) score -= 20;
  return Math.max(0, score);
});

const inspectorSchemaJson = computed(() => {
  return JSON.stringify({
    "@context": "https://schema.org",
    "@type": "NewsArticle",
    "headline": previewTitle.value,
    "description": previewDesc.value,
    "image": [previewImageSrc.value],
    "author": { "@type": "Person", "name": "Admin" },
    "publisher": { "@type": "Organization", "name": "Disdukcapil Kabupaten Dompu" }
  }, null, 2);
});

// Auto Generate SEO Meta Title & Description from Article Content
const generateSeoAuto = () => {
  if (form.title && form.title.trim()) {
    let cleanTitle = form.title.trim();
    if (cleanTitle.length > 70) {
      cleanTitle = cleanTitle.substring(0, 67) + '...';
    }
    form.meta_title = cleanTitle;
  }

  let sourceText = '';
  if (form.excerpt && form.excerpt.trim()) {
    sourceText = form.excerpt.trim();
  } else if (form.content && form.content.trim()) {
    sourceText = form.content.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
  }

  if (sourceText) {
    if (sourceText.length > 155) {
      const truncated = sourceText.substring(0, 155);
      const lastSpace = truncated.lastIndexOf(' ');
      form.meta_description = (lastSpace > 120 ? truncated.substring(0, lastSpace) : truncated) + '...';
    } else {
      form.meta_description = sourceText;
    }
  }
};

let editorInstance = null;

const form = useForm({
  title: props.news?.title || '',
  excerpt: props.news?.excerpt || '',
  content: props.news?.content || '',
  status: props.news?.status || 'draft',
  news_category_id: props.news?.news_category_id || null,
  is_featured: props.news?.is_featured || false,
  is_breaking: props.news?.is_breaking || false,
  meta_title: props.news?.meta_title || '',
  meta_description: props.news?.meta_description || '',
  published_at: props.news?.published_at ? props.news.published_at.substring(0, 16) : '',
  tag_ids: props.news?.tag_ids || [],
  custom_tags: props.news?.tags ? props.news.tags.map(t => t.name).join(', ') : '',
  thumbnail: null,
  remove_thumbnail: false,
  thumbnail_caption: props.news?.thumbnail_caption || '',
  thumbnail_alt: props.news?.thumbnail_alt || '',
});

// Hero Image Settings Panel - Device Preview
const heroPreviewDevice = ref('desktop');

// Extract inline images from content HTML for preview list
const inlineImages = computed(() => {
  if (!form.content) return [];
  const regex = /<img[^>]+src=["']([^"']+)["']/gi;
  const matches = [];
  let match;
  while ((match = regex.exec(form.content)) !== null) {
    matches.push(match[1]);
  }
  return matches;
});

// Insert HTML at editor cursor position (CKEditor 5)
const insertHtmlAtCursor = (htmlToInsert) => {
  if (editorInstance) {
    editorInstance.model.change(writer => {
      const viewFragment = editorInstance.data.processor.toView(htmlToInsert);
      const modelFragment = editorInstance.data.toModel(viewFragment);
      editorInstance.model.insertContent(modelFragment);
    });
    // Sync back to form
    form.content = editorInstance.getData();
  } else {
    form.content += '\n' + htmlToInsert;
  }
};

const handleInlinePhotoUpload = async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  isUploadingInlinePhoto.value = true;
  const formData = new FormData();
  formData.append('image', file);

  try {
    const res = await axios.post(route('admin.news.upload-image'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    if (res.data && res.data.url) {
      const imgTag = `<p><img src="${res.data.url}" alt="Foto Berita" class="rounded-2xl max-w-full my-4 shadow-sm" /></p>`;
      insertHtmlAtCursor(imgTag);
    }
  } catch (err) {
    alert('Gagal mengunggah foto. Pastikan ukuran file < 10MB.');
  } finally {
    isUploadingInlinePhoto.value = false;
    if (inlinePhotoInput.value) inlinePhotoInput.value.value = '';
  }
};

const promptInsertImageUrl = () => {
  const url = prompt('Masukkan URL Gambar (misal: https://example.com/foto.jpg):');
  if (url && url.trim()) {
    const imgTag = `<p><img src="${url.trim()}" alt="Foto Berita" class="rounded-2xl max-w-full my-4 shadow-sm" /></p>`;
    insertHtmlAtCursor(imgTag);
  }
};

const removeInlineImage = (imgUrl) => {
  if (confirm('Apakah Anda yakin ingin menghapus foto ini dari isi berita?')) {
    const escapedUrl = imgUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`<p>\\s*<img[^>]+src=["']${escapedUrl}["'][^>]*>\\s*<\\/p>|<img[^>]+src=["']${escapedUrl}["'][^>]*>`, 'gi');
    const newContent = form.content.replace(regex, '');
    form.content = newContent;
    if (editorInstance) {
      editorInstance.setData(newContent);
    }
  }
};

const extractYouTubeId = (url) => {
  const regExp = /(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/;
  const match = url.match(regExp);
  return (match && match[1]) ? match[1] : null;
};

const promptInsertYouTubeVideo = () => {
  const url = prompt('Masukkan Link Video YouTube (contoh: https://youtu.be/FhfNcfnjNQ0 atau https://www.youtube.com/watch?v=...):');
  if (!url || !url.trim()) return;

  const ytId = extractYouTubeId(url.trim());
  if (ytId) {
    const oembedHtml = `<figure class="media"><oembed url="https://www.youtube.com/watch?v=${ytId}"></oembed></figure><p></p>`;
    insertHtmlAtCursor(oembedHtml);
  } else {
    alert('Format URL YouTube tidak valid. Mohon periksa kembali link video YouTube Anda.');
  }
};

const extractGoogleDriveId = (url) => {
  const match = url.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
  if (match && match[1]) return match[1];
  if (/^[a-zA-Z0-9_-]{25,}$/.test(url.trim())) return url.trim();
  return null;
};

const promptInsertGoogleDriveVideo = () => {
  const url = prompt('Masukkan Link Video Google Drive (contoh: https://drive.google.com/file/d/1KY02_9ADneYBth.../view?usp=drive_link):');
  if (!url || !url.trim()) return;

  const driveId = extractGoogleDriveId(url.trim());
  if (driveId) {
    const oembedHtml = `<figure class="media"><oembed url="https://drive.google.com/file/d/${driveId}/preview"></oembed></figure><p></p>`;
    insertHtmlAtCursor(oembedHtml);
  } else {
    alert('Format URL Google Drive tidak valid. Mohon periksa kembali link video Google Drive Anda.');
  }
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    // Client-side file size validation (10 MB max)
    if (file.size > 10 * 1024 * 1024) {
      submitErrorMessage.value = `⚠️ File Gambar Cover Terlalu Besar!\nUkuran file (${(file.size / (1024 * 1024)).toFixed(2)} MB) melebihi batas maksimal 10 MB.\n\nSilakan kompres atau pilih gambar yang lebih kecil sebelum mengunggah.`;
      showSubmitErrorModal.value = true;
      if (fileInput.value) fileInput.value.value = '';
      return;
    }

    form.thumbnail = file;
    form.remove_thumbnail = false;
    const reader = new FileReader();
    reader.onload = (event) => {
      imagePreview.value = event.target.result;

      // Validate image dimensions for Open Graph standards (Requirement 6)
      const img = new Image();
      img.onload = () => {
        if (img.width < 1200 || img.height < 630) {
          alert(`⚠️ Perhatian: Dimensi gambar (${img.width} x ${img.height} px) di bawah rekomendasi minimal 1200 x 630 px.\nPreview WhatsApp & Facebook mungkin tidak optimal.`);
        }
      };
      img.src = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeThumbnail = () => {
  imagePreview.value = null;
  form.thumbnail = null;
  form.remove_thumbnail = true;
  if (fileInput.value) fileInput.value.value = '';
};

const submit = () => {
  // Sync editor content before submitting
  if (editorInstance) {
    form.content = editorInstance.getData();
  }

  // Pre-validate inline base64 images inside editor content
  if (form.content && form.content.length > 5 * 1024 * 1024) {
    const contentSizeMb = (form.content.length / (1024 * 1024)).toFixed(2);
    submitErrorMessage.value = `⚠️ Foto di Dalam Isi Berita Terlalu Besar!\nUkuran foto yang Anda sisipkan/tempelkan langsung di dalam editor adalah ${contentSizeMb} MB (melebihi batas aman server 5 MB).\n\nSolusi: Mohon hapus foto yang Anda tempelkan langsung, lalu gunakan tombol "Unggah Foto" pada toolbar editor agar gambar otomatis tersimpan di server secara terpisah.`;
    showSubmitErrorModal.value = true;
    return;
  }

  const inertiaOptions = {
    forceFormData: true,
    onError: (errors) => {
      console.error('Submit validation errors:', errors);
      let msg = 'Gagal menyimpan berita. Silakan periksa kembali isian formulir Anda.';
      if (errors.thumbnail) {
        msg = `Gagal Simpan (Gambar Cover): ${errors.thumbnail}`;
      } else if (errors.content) {
        msg = `Gagal Simpan (Isi Berita): ${errors.content}`;
      } else if (errors.title) {
        msg = `Gagal Simpan (Judul): ${errors.title}`;
      } else if (Object.keys(errors).length > 0) {
        msg = `Gagal Simpan: ${Object.values(errors).join(' | ')}`;
      }
      submitErrorMessage.value = msg;
      showSubmitErrorModal.value = true;
    }
  };

  if (isEdit.value) {
    form.transform((data) => ({
      ...data,
      _method: 'PUT'
    })).post(route('admin.news.update', props.news.id), inertiaOptions);
  } else {
    form.post(route('admin.news.store'), inertiaOptions);
  }
};

// Load CKEditor 5 from CDN and initialise
onMounted(() => {
  const loadCKEditor = () => {
    return new Promise((resolve, reject) => {
      if (window.ClassicEditor) { resolve(); return; }
      const script = document.createElement('script');
      script.src = 'https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js';
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  };

  loadCKEditor().then(() => {
    if (!window.ClassicEditor || !editorContainer.value) {
      isEditorLoading.value = false;
      return;
    }

    window.ClassicEditor.create(editorContainer.value, {
      toolbar: [
        'undo', 'redo', '|',
        'heading', '|',
        'bold', 'italic', '|',
        'bulletedList', 'numberedList', 'outdent', 'indent', '|',
        'link', 'blockQuote', 'insertTable', 'mediaEmbed',
      ],
      heading: {
        options: [
          { model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph' },
          { model: 'heading1', view: 'h1', title: 'Judul 1', class: 'ck-heading_heading1' },
          { model: 'heading2', view: 'h2', title: 'Judul 2', class: 'ck-heading_heading2' },
          { model: 'heading3', view: 'h3', title: 'Judul 3', class: 'ck-heading_heading3' },
        ],
      },
      mediaEmbed: {
        previewsInData: false,
      },
      initialData: form.content || '',
      placeholder: 'Tulis lengkap isi berita kependudukan di sini...',
    }).then(editor => {
      editorInstance = editor;
      isEditorLoading.value = false;

      // Keep form.content in sync as user types
      editor.model.document.on('change:data', () => {
        form.content = editor.getData();
      });
    }).catch(err => {
      isEditorLoading.value = false;
      console.error('CKEditor init error:', err);
    });
  }).catch(err => {
    isEditorLoading.value = false;
    console.error('Failed to load CKEditor 5 CDN script:', err);
  });
});

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const dateFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  const timeFormatted = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
  return `${dateFormatted} ${timeFormatted} WITA`;
};

onBeforeUnmount(() => {
  if (editorInstance) {
    editorInstance.destroy().catch(() => {});
    editorInstance = null;
  }
});
</script>

<style>
/* CKEditor container styling */
.ck-editor-wrapper .ck.ck-editor {
  border-radius: 1rem;
  overflow: hidden;
  border: none !important;
}
.ck-editor-wrapper .ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content {
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}
.dark .ck-editor-wrapper .ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content {
  background: #18181b;
  border-bottom-color: #3f3f46;
}
.ck-editor-wrapper .ck.ck-editor__main .ck-editor__editable {
  min-height: 420px;
  font-size: 0.875rem;
  line-height: 1.8;
  padding: 1.5rem;
  background: #f9fafb;
}
.dark .ck-editor-wrapper .ck.ck-editor__main .ck-editor__editable {
  background: #18181b;
  color: #e4e4e7;
}
.ck-editor-wrapper .ck.ck-toolbar {
  background: #f9fafb !important;
}
.dark .ck-editor-wrapper .ck.ck-toolbar {
  background: #18181b !important;
  border-color: #3f3f46 !important;
}
</style>
