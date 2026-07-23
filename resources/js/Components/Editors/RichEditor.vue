<template>
  <div 
    class="tiptap-rich-editor rounded-2xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden transition-all duration-200 shadow-sm focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500"
    :class="{ 'fixed inset-0 z-50 rounded-none border-none h-screen flex flex-col': isFullscreen }"
  >
    <!-- Top Sticky Toolbar -->
    <div class="flex flex-wrap items-center gap-1 p-2 bg-gray-50/80 dark:bg-zinc-800/80 border-b border-gray-200 dark:border-zinc-800/80 text-gray-700 dark:text-zinc-300 text-xs shrink-0 select-none">
      <!-- History Group -->
      <button type="button" @click="editor?.chain().focus().undo().run()" :disabled="!editor?.can().undo()" class="toolbar-btn" title="Undo (Ctrl+Z)">
        <Undo class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor?.chain().focus().redo().run()" :disabled="!editor?.can().redo()" class="toolbar-btn" title="Redo (Ctrl+Y)">
        <Redo class="w-3.5 h-3.5" />
      </button>

      <div class="h-4 w-px bg-gray-300 dark:bg-zinc-700 mx-1"></div>

      <!-- Formatting Group -->
      <button type="button" @click="editor?.chain().focus().toggleBold().run()" :class="{ 'is-active': editor?.isActive('bold') }" class="toolbar-btn" title="Bold (Ctrl+B)">
        <Bold class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor?.chain().focus().toggleItalic().run()" :class="{ 'is-active': editor?.isActive('italic') }" class="toolbar-btn" title="Italic (Ctrl+I)">
        <Italic class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor?.chain().focus().toggleUnderline().run()" :class="{ 'is-active': editor?.isActive('underline') }" class="toolbar-btn" title="Underline (Ctrl+U)">
        <UnderlineIcon class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor?.chain().focus().toggleStrike().run()" :class="{ 'is-active': editor?.isActive('strike') }" class="toolbar-btn" title="Strikethrough">
        <Strikethrough class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor?.chain().focus().toggleHighlight().run()" :class="{ 'is-active': editor?.isActive('highlight') }" class="toolbar-btn text-amber-500" title="Highlight Text">
        <Highlighter class="w-3.5 h-3.5" />
      </button>

      <div class="h-4 w-px bg-gray-300 dark:bg-zinc-700 mx-1"></div>

      <!-- Headings Dropdown -->
      <select 
        :value="activeHeading" 
        @change="setHeading($event.target.value)" 
        class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg px-2 py-1 text-xs font-bold focus:outline-none"
      >
        <option value="p">Teks Normal</option>
        <option value="h1">Judul Utama (H1)</option>
        <option value="h2">Sub Judul (H2)</option>
        <option value="h3">Bagian (H3)</option>
      </select>

      <div class="h-4 w-px bg-gray-300 dark:bg-zinc-700 mx-1"></div>

      <!-- Text Alignment -->
      <button type="button" @click="editor?.chain().focus().setTextAlign('left').run()" :class="{ 'is-active': editor?.isActive({ textAlign: 'left' }) }" class="toolbar-btn" title="Rata Kiri">
        <AlignLeft class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor?.chain().focus().setTextAlign('center').run()" :class="{ 'is-active': editor?.isActive({ textAlign: 'center' }) }" class="toolbar-btn" title="Rata Tengah">
        <AlignCenter class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor?.chain().focus().setTextAlign('right').run()" :class="{ 'is-active': editor?.isActive({ textAlign: 'right' }) }" class="toolbar-btn" title="Rata Kanan">
        <AlignRight class="w-3.5 h-3.5" />
      </button>

      <div class="h-4 w-px bg-gray-300 dark:bg-zinc-700 mx-1"></div>

      <!-- Lists Group -->
      <button type="button" @click="editor?.chain().focus().toggleBulletList().run()" :class="{ 'is-active': editor?.isActive('bulletList') }" class="toolbar-btn" title="Bullet List">
        <List class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()" :class="{ 'is-active': editor?.isActive('orderedList') }" class="toolbar-btn" title="Numbered List">
        <ListOrdered class="w-3.5 h-3.5" />
      </button>

      <div class="h-4 w-px bg-gray-300 dark:bg-zinc-700 mx-1"></div>

      <!-- Callout & Quotes -->
      <button type="button" @click="editor?.chain().focus().toggleBlockquote().run()" :class="{ 'is-active': editor?.isActive('blockquote') }" class="toolbar-btn" title="Kutipan (Quote)">
        <Quote class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="insertCallout('info')" class="toolbar-btn text-blue-600" title="Kotak Informasi">
        <Info class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="insertCallout('warning')" class="toolbar-btn text-amber-600" title="Kotak Peringatan">
        <AlertTriangle class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="insertCallout('success')" class="toolbar-btn text-emerald-600" title="Kotak Sukses">
        <CheckCircle2 class="w-3.5 h-3.5" />
      </button>

      <div class="h-4 w-px bg-gray-300 dark:bg-zinc-700 mx-1"></div>

      <!-- Table Group -->
      <button type="button" @click="insertTable" class="toolbar-btn" title="Sisipkan Tabel">
        <TableIcon class="w-3.5 h-3.5" />
      </button>

      <!-- Media Group -->
      <button type="button" @click="promptImageUrl" class="toolbar-btn text-purple-600" title="Sisipkan Gambar">
        <ImageIcon class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="promptYoutubeUrl" class="toolbar-btn text-red-600" title="Sisipkan Video YouTube">
        <Video class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="promptLink" :class="{ 'is-active': editor?.isActive('link') }" class="toolbar-btn text-cyan-600" title="Tautan Link">
        <LinkIcon class="w-3.5 h-3.5" />
      </button>

      <!-- Right Controls -->
      <div class="ml-auto flex items-center gap-1">
        <button type="button" @click="showHtmlView = !showHtmlView" :class="{ 'is-active': showHtmlView }" class="toolbar-btn" title="Lihat Kode HTML Raw">
          <Code class="w-3.5 h-3.5" />
        </button>
        <button type="button" @click="isFullscreen = !isFullscreen" class="toolbar-btn" :title="isFullscreen ? 'Keluar Fullscreen' : 'Layar Penuh (Fullscreen)'">
          <Minimize2 v-if="isFullscreen" class="w-3.5 h-3.5" />
          <Maximize2 v-else class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>

    <!-- Tiptap Floating Bubble Menu -->
    <BubbleMenu v-if="editor" :editor="editor" :tippy-options="{ duration: 150 }" class="bg-gray-900 text-white rounded-xl shadow-xl p-1 flex items-center gap-1 border border-gray-700">
      <button type="button" @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-gray-700': editor.isActive('bold') }" class="p-1 hover:bg-gray-800 rounded">
        <Bold class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-gray-700': editor.isActive('italic') }" class="p-1 hover:bg-gray-800 rounded">
        <Italic class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="editor.chain().focus().toggleUnderline().run()" :class="{ 'bg-gray-700': editor.isActive('underline') }" class="p-1 hover:bg-gray-800 rounded">
        <UnderlineIcon class="w-3.5 h-3.5" />
      </button>
      <button type="button" @click="promptLink" :class="{ 'bg-gray-700': editor.isActive('link') }" class="p-1 hover:bg-gray-800 rounded">
        <LinkIcon class="w-3.5 h-3.5" />
      </button>
    </BubbleMenu>

    <!-- HTML Source Editor View -->
    <div v-if="showHtmlView" class="p-4 bg-zinc-950 text-emerald-400 font-mono text-xs flex-1 overflow-y-auto min-h-[220px]">
      <textarea 
        :value="editor?.getHTML()" 
        @input="onHtmlInput($event.target.value)" 
        class="w-full h-full bg-transparent border-none focus:outline-none font-mono resize-none"
        rows="10"
      ></textarea>
    </div>

    <!-- Main Visual Editor Container -->
    <div v-else class="p-4 sm:p-6 overflow-y-auto flex-1 text-left" :style="{ minHeight: minHeight, maxHeight: isFullscreen ? '100%' : maxHeight }">
      <EditorContent :editor="editor" class="prose dark:prose-invert max-w-none focus:outline-none" />
    </div>

    <!-- Bottom Status & Character Counter Bar -->
    <div class="px-4 py-2 bg-gray-50/80 dark:bg-zinc-800/60 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between text-[11px] text-gray-400 dark:text-zinc-500 font-medium shrink-0">
      <div class="flex items-center gap-3">
        <span class="flex items-center gap-1 font-bold text-gray-600 dark:text-zinc-300">
          <Sparkles class="w-3 h-3 text-primary-500 animate-pulse" /> Editor Visual Tiptap
        </span>
        <span class="hidden sm:inline">Ketik <kbd class="px-1 py-0.5 bg-gray-200 dark:bg-zinc-700 rounded text-[9px] font-mono text-gray-700 dark:text-zinc-300">/</kbd> untuk perintah pintas</span>
      </div>

      <div class="flex items-center gap-4 font-mono">
        <span>{{ characterCount }} Karakter</span>
        <span>{{ wordCount }} Kata</span>
        <span>~{{ readingTime }} Mnt Baca</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent, BubbleMenu } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import UnderlineExtension from '@tiptap/extension-underline';
import LinkExtension from '@tiptap/extension-link';
import ImageExtension from '@tiptap/extension-image';
import YoutubeExtension from '@tiptap/extension-youtube';
import TableExtension from '@tiptap/extension-table';
import TableRowExtension from '@tiptap/extension-table-row';
import TableCellExtension from '@tiptap/extension-table-cell';
import TableHeaderExtension from '@tiptap/extension-table-header';
import PlaceholderExtension from '@tiptap/extension-placeholder';
import CharacterCountExtension from '@tiptap/extension-character-count';
import HighlightExtension from '@tiptap/extension-highlight';
import TextAlignExtension from '@tiptap/extension-text-align';

import { 
  Undo, Redo, Bold, Italic, Underline as UnderlineIcon, Strikethrough, Highlighter,
  AlignLeft, AlignCenter, AlignRight, List, ListOrdered, Quote, Info, AlertTriangle, CheckCircle2,
  Table as TableIcon, Image as ImageIcon, Video, Link as LinkIcon, Code, Maximize2, Minimize2, Sparkles
} from '@lucide/vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Tulis uraian konten di sini atau ketik / untuk perintah cepat...' },
  minHeight: { type: String, default: '220px' },
  maxHeight: { type: String, default: '500px' },
});

const emit = defineEmits(['update:modelValue']);

const showHtmlView = ref(false);
const isFullscreen = ref(false);

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    UnderlineExtension,
    LinkExtension.configure({ openOnClick: false, HTMLAttributes: { class: 'text-primary-600 dark:text-primary-400 font-bold underline' } }),
    ImageExtension.configure({ inline: true, allowBase64: true }),
    YoutubeExtension.configure({ width: 640, height: 360 }),
    TableExtension.configure({ resizable: true }),
    TableRowExtension,
    TableCellExtension,
    TableHeaderExtension,
    PlaceholderExtension.configure({ placeholder: props.placeholder }),
    CharacterCountExtension,
    HighlightExtension,
    TextAlignExtension.configure({ types: ['heading', 'paragraph'] }),
  ],
  onUpdate: () => {
    emit('update:modelValue', editor.value.getHTML());
  },
});

watch(() => props.modelValue, (val) => {
  const isSame = editor.value.getHTML() === val;
  if (!isSame) {
    editor.value.commands.setContent(val, false);
  }
});

const activeHeading = computed(() => {
  if (editor.value?.isActive('heading', { level: 1 })) return 'h1';
  if (editor.value?.isActive('heading', { level: 2 })) return 'h2';
  if (editor.value?.isActive('heading', { level: 3 })) return 'h3';
  return 'p';
});

const setHeading = (val) => {
  if (val === 'p') editor.value.chain().focus().setParagraph().run();
  else if (val === 'h1') editor.value.chain().focus().toggleHeading({ level: 1 }).run();
  else if (val === 'h2') editor.value.chain().focus().toggleHeading({ level: 2 }).run();
  else if (val === 'h3') editor.value.chain().focus().toggleHeading({ level: 3 }).run();
};

const insertCallout = (type) => {
  let colors = 'bg-blue-50 text-blue-900 border-blue-200 dark:bg-blue-950/40 dark:text-blue-200 dark:border-blue-900/40';
  let title = 'INFORMASI';
  if (type === 'warning') {
    colors = 'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-950/40 dark:text-amber-200 dark:border-amber-900/40';
    title = 'PERINGATAN';
  } else if (type === 'success') {
    colors = 'bg-emerald-50 text-emerald-900 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:border-emerald-900/40';
    title = 'SUKSES / CATATAN';
  }

  const html = `<div class="p-4 rounded-2xl border ${colors} my-4 font-sans"><strong class="block text-xs uppercase tracking-wider mb-1">${title}</strong><p>Tuliskan detail informasi di sini...</p></div>`;
  editor.value.chain().focus().insertContent(html).run();
};

const insertTable = () => {
  editor.value.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
};

const promptImageUrl = () => {
  const url = window.prompt('Masukkan URL Gambar (atau tautan image CDN):');
  if (url) {
    editor.value.chain().focus().setImage({ src: url }).run();
  }
};

const promptYoutubeUrl = () => {
  const url = window.prompt('Masukkan URL Video YouTube (contoh: https://www.youtube.com/watch?v=...):');
  if (url) {
    editor.value.chain().focus().setYoutubeVideo({ src: url }).run();
  }
};

const promptLink = () => {
  const previousUrl = editor.value.getAttributes('link').href;
  const url = window.prompt('Masukkan URL Tautan Link:', previousUrl);
  if (url === null) return;
  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }
  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const onHtmlInput = (html) => {
  editor.value.commands.setContent(html, false);
  emit('update:modelValue', html);
};

const characterCount = computed(() => editor.value?.storage.characterCount.characters() || 0);
const wordCount = computed(() => editor.value?.storage.characterCount.words() || 0);
const readingTime = computed(() => Math.max(1, Math.ceil(wordCount.value / 200)));

onBeforeUnmount(() => {
  editor.value?.destroy();
});
</script>

<style>
/* Tiptap Custom Styles */
.tiptap-rich-editor .ProseMirror p.is-editor-empty:first-child::before {
  color: #a1a1aa;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
.tiptap-rich-editor .toolbar-btn {
  padding: 0.375rem;
  border-radius: 0.5rem;
  transition: all 0.15s ease;
}
.tiptap-rich-editor .toolbar-btn:hover {
  background-color: rgba(156, 163, 175, 0.15);
}
.tiptap-rich-editor .toolbar-btn.is-active {
  background-color: rgba(2, 116, 203, 0.15);
  color: #0274cb;
  font-weight: bold;
}
.tiptap-rich-editor table {
  border-collapse: collapse;
  margin: 1rem 0;
  width: 100%;
}
.tiptap-rich-editor th, .tiptap-rich-editor td {
  border: 1px solid #e5e7eb;
  padding: 0.5rem 0.75rem;
}
.tiptap-rich-editor th {
  background-color: #f9fafb;
  font-weight: 700;
}
.dark .tiptap-rich-editor th, .dark .tiptap-rich-editor td {
  border-color: #27272a;
}
.dark .tiptap-rich-editor th {
  background-color: #18181b;
}
</style>
