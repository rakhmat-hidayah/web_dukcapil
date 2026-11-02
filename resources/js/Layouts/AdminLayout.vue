<template>
  <div :class="{ 'dark': isDark }" class="min-h-screen bg-gray-50 dark:bg-zinc-950 text-gray-900 dark:text-zinc-50 flex transition-colors duration-200">
    <!-- Mobile Backdrop Overlay -->
    <div 
      v-if="sidebarOpen" 
      @click="sidebarOpen = false" 
      class="fixed inset-0 bg-black/50 z-20 md:hidden transition-opacity"
    ></div>

    <!-- Sidebar for Desktop -->
    <aside 
      class="fixed inset-y-0 left-0 bg-white dark:bg-zinc-900 border-r border-gray-100 dark:border-zinc-800 z-30 transition-all duration-300 flex flex-col"
      :class="[
        sidebarOpen 
          ? 'translate-x-0 w-64' 
          : '-translate-x-full md:translate-x-0 md:w-20'
      ]"
    >
      <!-- Sidebar Header -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100 dark:border-zinc-800 overflow-hidden">
        <div class="flex items-center gap-2.5 px-2 py-1 rounded-xl dark:bg-white/95 dark:shadow-sm">
          <img src="/img/logo-dompu.png" @error="$event.target.src = '/images/logo-dompu.png'" alt="Logo Dompu" class="h-10 w-auto object-contain shrink-0" />
          <img v-if="sidebarOpen" src="/img/logo-dukcapil.png" @error="$event.target.src = '/images/logo-dukcapil.png'" alt="Logo Dukcapil" class="h-9 w-auto object-contain shrink-0" />
        </div>
        <button 
          @click="toggleSidebar"
          class="p-1 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 hidden md:block"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
          </svg>
        </button>
      </div>

      <!-- Navigation links -->
      <nav ref="sidebarNavRef" @scroll="handleSidebarScroll" class="flex-1 overflow-y-auto p-4 space-y-6 scrollbar-thin">
        <div v-for="(group, gIdx) in filteredMenuGroups" :key="gIdx" class="space-y-1">
          <p v-if="sidebarOpen" class="px-3 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500 mb-2">
            {{ group.title }}
          </p>
          <div class="space-y-1">
            <Link 
              v-for="(item, iIdx) in group.items" 
              :key="iIdx"
              :href="safeRoute(item.route)"
              preserve-scroll
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group"
              :class="[
                isActiveRoute(item.route) 
                  ? 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400' 
                  : 'text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-gray-50 dark:hover:bg-zinc-800/50'
              ]"
              :title="item.label"
            >
              <component :is="item.icon" class="w-5 h-5 shrink-0" />
              <span v-if="sidebarOpen" class="whitespace-nowrap transition-opacity duration-300">
                {{ item.label }}
              </span>
            </Link>
          </div>
        </div>
      </nav>

      <!-- Sidebar Footer -->
      <div class="p-4 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between gap-3 overflow-hidden">
        <Link
          :href="route('admin.security.index')"
          class="flex items-center gap-3 min-w-0 group hover:opacity-80 transition cursor-pointer"
          title="Kelola Keamanan Akun & 2FA"
        >
          <div class="w-9 h-9 bg-primary-600 rounded-full flex items-center justify-center text-white font-bold shrink-0 shadow-sm group-hover:scale-105 transition-transform">
            {{ $page.props.auth.user ? $page.props.auth.user.name.charAt(0) : 'A' }}
          </div>
          <div v-if="sidebarOpen" class="min-w-0 text-left">
            <p class="text-xs font-semibold text-gray-800 dark:text-zinc-200 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
              {{ $page.props.auth.user ? $page.props.auth.user.name : 'Administrator' }}
            </p>
            <p class="text-[10px] text-gray-400 dark:text-zinc-500 truncate flex items-center gap-1">
              <span>{{ $page.props.auth.roles[0] || 'Operator' }}</span>
              <span class="text-zinc-300 dark:text-zinc-700">•</span>
              <span class="text-blue-500 font-semibold underline">2FA</span>
            </p>
          </div>
        </Link>
        <Link 
          v-if="sidebarOpen"
          :href="route('admin.logout')" 
          method="post" 
          as="button"
          class="p-2 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-500 hover:text-red-600 dark:hover:text-red-400 rounded-xl transition"
          title="Keluar (Logout)"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
          </svg>
        </Link>
      </div>
    </aside>

    <!-- Main Wrapper -->
    <div 
      class="flex-1 flex flex-col min-w-0 transition-all duration-300 pl-0"
      :class="[sidebarOpen ? 'md:pl-64' : 'md:pl-20']"
    >
      <!-- Top navbar -->
      <header class="h-16 bg-white dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800 flex items-center justify-between px-6 sticky top-0 z-20">
        <!-- Toggle button mobile -->
        <button 
          @click="toggleSidebar"
          class="p-2 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-xl text-gray-500 dark:text-zinc-400 md:hidden"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>

        <div class="hidden md:flex items-center gap-2 text-xs text-gray-400">
          <span class="font-medium text-gray-500 dark:text-zinc-400">Portal CMS</span>
          <span>/</span>
          <span class="font-semibold text-primary-600 dark:text-primary-400 capitalize">
            {{ activePageName }}
          </span>
        </div>

        <!-- Right Side: User Menu & Notification -->
        <div class="flex items-center gap-3">
          <!-- Notification Bell -->
          <div class="relative">
            <button 
              @click="toggleNotifications"
              class="p-2 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 relative transition"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
              </svg>
              <!-- Indicator -->
              <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
            </button>

            <!-- Notifications Dropdown -->
            <div 
              v-if="notificationsOpen"
              class="absolute right-0 mt-3 w-80 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-2xl shadow-xl overflow-hidden z-40 py-2"
            >
              <div class="px-4 py-2 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center">
                <span class="font-bold text-xs">Notifikasi ({{ unreadCount }})</span>
                <button @click="markAllRead" class="text-[10px] text-primary-600 dark:text-primary-400 font-semibold hover:underline">
                  Tandai semua dibaca
                </button>
              </div>
              <div class="max-h-60 overflow-y-auto divide-y divide-gray-50 dark:divide-zinc-800">
                <div v-if="notifications.length === 0" class="p-4 text-center text-xs text-gray-400">
                  Tidak ada notifikasi baru
                </div>
                <div 
                  v-for="notif in notifications" 
                  :key="notif.id" 
                  @click="readAndRedirect(notif)"
                  class="p-3 hover:bg-gray-50 dark:hover:bg-zinc-800/50 flex gap-3 transition cursor-pointer"
                >
                  <div class="shrink-0 w-2 h-2 bg-primary-500 rounded-full mt-2" v-if="!notif.read_at"></div>
                  <div class="text-left flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-700 dark:text-zinc-200 truncate">
                      {{ notif.data.title }}
                    </p>
                    <p class="text-[10px] text-gray-500 dark:text-zinc-400 mt-0.5 line-clamp-2">
                      {{ notif.data.message }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Dark mode toggle -->
          <button 
            @click="toggleDark" 
            class="p-2 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 transition"
            title="Ganti Mode Gelap/Terang"
          >
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
          </button>
        </div>
      </header>

      <!-- Main Content Area -->
      <main class="flex-1 p-6 md:p-8">
        <slot />
      </main>
    </div>

    <!-- Toast Notification Banner -->
    <transition name="slide-fade">
      <div 
        v-if="toast" 
        class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-4 py-3.5 rounded-2xl shadow-xl border text-sm max-w-sm transition-all"
        :class="[
          toast.type === 'success' 
            ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-100 dark:border-emerald-900' 
            : 'bg-red-50 dark:bg-red-950/60 text-red-800 dark:text-red-300 border-red-100 dark:border-red-900'
        ]"
      >
        <div class="shrink-0">
          <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-emerald-500">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-red-500">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
          </svg>
        </div>
        <p class="font-medium text-xs leading-relaxed">{{ toast.message }}</p>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
usePage;
import axios from 'axios';
import { 
  LayoutDashboard, FileText, BellRing, ClipboardList, 
  FolderLock, Image, LibraryBig, Milestone, ShieldAlert, Globe, MapPin,
  Settings, UsersRound, Users, Network, Palette, BarChart3, Radio, Activity, Sliders,
  Map, BarChart2, DatabaseZap, MessageSquareWarning, ShieldCheck, ArrowDownUp, HelpCircle, Bell,
  FileCheck, CalendarDays, UserCheck, Clock, CheckCircle2, History, AlertTriangle, PackageCheck,
  FolderArchive, HardDrive, FileArchive
} from '@lucide/vue';

const page = usePage();

// Sidebar state
const sidebarOpen = ref(true);
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
  localStorage.setItem('admin-sidebar-open', sidebarOpen.value ? 'true' : 'false');
};

// Dark mode state
const isDark = ref(false);
const toggleDark = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('dark-mode', 'true');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('dark-mode', 'false');
  }
};

// Notifications state
const notificationsOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const toggleNotifications = () => {
  notificationsOpen.value = !notificationsOpen.value;
};

const fetchNotifications = async () => {
  try {
    const res = await axios.get(route('admin.notifications.index'));
    notifications.value = res.data.notifications;
    unreadCount.value = res.data.unreadCount;
  } catch (err) {
    console.error('Failed to fetch notifications', err);
  }
};

const markAllRead = async () => {
  try {
    await axios.post(route('admin.notifications.read-all'));
    fetchNotifications();
  } catch (err) {
    console.error('Failed to mark notifications read', err);
  }
};

const readAndRedirect = async (notif) => {
  try {
    if (!notif.read_at) {
      await axios.post(route('admin.notifications.read', notif.id));
    }
    notificationsOpen.value = false;
    fetchNotifications();
    
    if (notif.data.url) {
      router.visit(notif.data.url);
    }
  } catch (err) {
    console.error('Failed to read notification', err);
  }
};

// Toasts state
const toast = ref(null);

watch(() => page.props.flash, (newFlash) => {
  if (newFlash?.success) {
    showToast(newFlash.success, 'success');
  } else if (newFlash?.error) {
    showToast(newFlash.error, 'error');
  }
}, { immediate: true, deep: true });

const showToast = (message, type) => {
  toast.value = { message, type };
  setTimeout(() => {
    toast.value = null;
  }, 4000);
};

// Sidebar Nav Menu Mapping
const menuGroups = [
  {
    title: 'Dashboard',
    items: [
      { label: 'Ikhtisar', route: 'admin.dashboard', icon: LayoutDashboard },
    ]
  },
  {
    title: 'Profil Instansi (CMS)',
    items: [
      { label: 'Dashboard Profil', route: 'admin.profile.dashboard', icon: LayoutDashboard },
      { label: 'Penyusun Halaman Profil', route: 'admin.profile.builder', icon: FileText },
      { label: 'Direktori Pejabat & Pegawai', route: 'admin.profile.officials.index', icon: Users },
      { label: 'Editor Struktur Organisasi', route: 'admin.profile.org-chart.index', icon: Network },
    ]
  },
  {
    title: 'Konten & Media',
    items: [
      { label: 'Manajer Berkas & Media', route: 'admin.files.index', icon: FolderLock },
      { label: 'Menu Navigasi', route: 'admin.menus.index', icon: Radio },
      { label: 'Halaman Dinamis', route: 'admin.pages.index', icon: FileText },
      { label: 'Berita & Artikel', route: 'admin.news.index', icon: LibraryBig },
      { label: 'Pengumuman', route: 'admin.announcements.index', icon: BellRing },
      { label: 'Banner Slider', route: 'admin.banners.index', icon: Image },
      { label: 'Galeri Foto & Video', route: 'admin.gallery.index', icon: LibraryBig },
    ]
  },
  {
    title: 'Layanan Publik',
    items: [
      { label: 'Survei Kepuasan (IKM)', route: 'admin.surveys.index', icon: BarChart2 },
      { label: 'Daftar Pengaduan', route: 'admin.complaints.index', icon: MessageSquareWarning },
      { label: 'Kategori Pengaduan', route: 'admin.complaints.categories', icon: ClipboardList },
      { label: 'Persyaratan Layanan', route: 'admin.service-requirements.index', icon: FileText },
      { label: 'Inovasi Pelayanan', route: 'admin.innovations.index', icon: DatabaseZap },
      { label: 'Informasi Layanan (CMS)', route: 'admin.informasi-layanan.index', icon: HelpCircle },
      { label: 'Pusat Unduhan', route: 'admin.downloads.index', icon: Milestone },
      { label: 'Tanya Jawab (FAQ)', route: 'admin.faqs.index', icon: ClipboardList },
    ]
  },
  {
    title: 'Antrian Online KTP-el',
    items: [
      { label: 'Dashboard Antrian', route: 'admin.queue.dashboard', icon: LayoutDashboard },
      { label: 'Verifikasi Dokumen', route: 'admin.queue.applications.index', icon: FileCheck },
      { label: 'Jenis Layanan KTP-el', route: 'admin.queue.service-types.index', icon: FileText },
      { label: 'Jadwal & Kuota', route: 'admin.queue.schedules.index', icon: CalendarDays },
      { label: 'Pengaturan Antrian', route: 'admin.queue.settings.index', icon: Settings },
    ]
  },
  {
    title: 'Serah Terima KTP-el',
    items: [
      { label: 'Dashboard Serah Terima', route: 'admin.handover.dashboard', icon: LayoutDashboard },
      { label: 'Menunggu Ketersediaan KTP-el', route: 'admin.handover.pending', icon: Clock },
      { label: 'Siap Diambil', route: 'admin.handover.ready', icon: CheckCircle2 },
      { label: 'Riwayat Penyerahan', route: 'admin.handover.history', icon: History },
      { label: 'Kendala Pengambilan', route: 'admin.handover.exceptions', icon: AlertTriangle },
    ]
  },
  {
    title: 'Pengarsipan',
    items: [
      { label: 'Dashboard Pengarsipan', route: 'admin.archive.dashboard', icon: LayoutDashboard },
      { label: 'Permohonan Siap Diarsipkan', route: 'admin.archive.ready-applications', icon: FileCheck },
      { label: 'Paket Arsip ZIP', route: 'admin.archive.packages.index', icon: FolderArchive },
      { label: 'Riwayat Pengarsipan', route: 'admin.archive.history', icon: History },
      { label: 'Status Penyimpanan Permanen', route: 'admin.archive.permanent-storage', icon: HardDrive },
      { label: 'Pengaturan Pengarsipan', route: 'admin.archive.settings.index', icon: Settings },
    ]
  },
  {
    title: 'Layanan PPID',
    items: [
      { label: 'Halaman Statis PPID', route: 'admin.ppid.pages.index', icon: FileText },
      { label: 'Dokumen Publik PPID', route: 'admin.ppid.documents.index', icon: ShieldCheck },
      { label: 'Permohonan Informasi', route: 'admin.ppid.requests.index', icon: MessageSquareWarning },
    ]
  },
  {
    title: 'Demografi',
    items: [
      { label: 'Hierarki Wilayah', route: 'admin.demographics.hierarchy', icon: Map },
      { label: 'Dataset Kependudukan', route: 'admin.demographics.datasets', icon: DatabaseZap },
      { label: 'Dashboard Statistik', route: 'admin.demographics.dashboard', icon: BarChart2 },
    ]
  },
  {
    title: 'Pengaturan Akun',
    items: [
      { label: 'Keamanan Akun (2FA)', route: 'admin.security.index', icon: ShieldCheck },
    ]
  },
  {
    title: 'Analitik & Pengunjung',
    items: [
      { label: 'Analisis Pengunjung', route: 'admin.visitor-analytics.index', icon: Activity },
      { label: 'Retensi & Pembersihan Data', route: 'admin.visitor-analytics-retention.index', icon: DatabaseZap },
    ]
  },
  {
    title: 'Pengaturan Sistem',
    items: [
      { label: 'Notifikasi & Integrasi', route: 'admin.notification-settings.index', icon: Bell },
      { label: 'Kontak & Lokasi Kantor', route: 'admin.office-contact.index', icon: MapPin },
      { label: 'Pengguna & Peran', route: 'admin.users.index', icon: UsersRound },
      { label: 'Layanan SANAI (CMS)', route: 'admin.external-services.index', icon: Globe },
      { label: 'Kustomisasi Tema', route: 'admin.theme.index', icon: Palette },
      { label: 'Kunci API & Hak Akses', route: 'admin.api-keys.index', icon: BarChart3 },
      { label: 'Monitor Trafik API', route: 'admin.api-dashboard.index', icon: Activity },
      { label: 'Pengaturan API & Ketentuan', route: 'admin.api-settings.index', icon: Sliders },
      { label: 'Log Audit Aktivitas', route: 'admin.audit-logs.index', icon: ShieldAlert },
    ]
  }
];

const userRoles = computed(() => page.props.auth?.roles || []);
const isSuperAdmin = computed(() => userRoles.value.includes('Super Administrator') || userRoles.value.includes('Administrator Utama'));
const isWebsiteAdmin = computed(() => userRoles.value.includes('Website Administrator') || userRoles.value.includes('Administrator Website'));
const isContentEditor = computed(() => userRoles.value.includes('Content Editor') || userRoles.value.includes('Editor Konten'));
const isNewsOperator = computed(() => userRoles.value.includes('News Operator') || userRoles.value.includes('Operator Berita'));
const isPubOperator = computed(() => userRoles.value.includes('Publication Operator') || userRoles.value.includes('Operator Publikasi'));
const isMediaManager = computed(() => userRoles.value.includes('Media Manager') || userRoles.value.includes('Pengelola Media'));
const isComplaintOfficer = computed(() => userRoles.value.includes('Complaint Officer') || userRoles.value.includes('Petugas Pengaduan'));
const isPpidAdmin = computed(() => userRoles.value.includes('PPID Admin') || userRoles.value.includes('Admin PPID'));
const isQueueOfficer = computed(() => userRoles.value.includes('Queue Officer') || userRoles.value.includes('Petugas Verifikasi / Registrasi KTP-el'));
const isQueueVerifier = computed(() => userRoles.value.includes('Queue Verifier') || userRoles.value.includes('Petugas Verifikasi / Registrasi KTP-el'));
const isHandoverOfficer = computed(() => userRoles.value.includes('petugas_pengambilan_ktpel') || userRoles.value.includes('Petugas Pengambilan KTP-el'));
const isArchiveOperator = computed(() => userRoles.value.includes('operator_arsip') || userRoles.value.includes('Petugas Pengarsipan'));
const isKtpSupervisor = computed(() => userRoles.value.includes('Supervisor Layanan KTP-el') || userRoles.value.includes('supervisor_layanan_ktpel'));
const userPermissions = computed(() => page.props.auth?.permissions || []);

const filteredMenuGroups = computed(() => {
  if (isSuperAdmin.value) {
    return menuGroups;
  }

  // Website Administrator sees EVERYTHING except User & Roles management
  if (isWebsiteAdmin.value) {
    return menuGroups.map(group => ({
      ...group,
      items: group.items.filter(item => item.route !== 'admin.users.index')
    })).filter(group => group.items.length > 0);
  }

  return menuGroups.map(group => {
    const items = group.items.filter(item => {
      const r = item.route;

      // Always allow Account Security (2FA) page to all authenticated operators
      if (r === 'admin.security.index') {
        return true;
      }

      // Overview system dashboard is ONLY visible to Super Administrator and Website Administrator
      if (r === 'admin.dashboard') {
        return isSuperAdmin.value || isWebsiteAdmin.value;
      }

      // Content Editor allowed items
      if (isContentEditor.value) {
        if ([
          'admin.files.index',
          'admin.pages.index',
          'admin.news.index',
          'admin.announcements.index',
          'admin.banners.index',
          'admin.gallery.index',
          'admin.downloads.index',
          'admin.faqs.index',
        ].includes(r)) {
          return true;
        }
      }

      // News Operator allowed items
      if (isNewsOperator.value) {
        if (['admin.files.index', 'admin.news.index'].includes(r)) {
          return true;
        }
      }

      // Media Manager allowed items
      if (isMediaManager.value) {
        if (['admin.files.index'].includes(r)) {
          return true;
        }
      }

      // Publication Operator allowed items
      if (isPubOperator.value) {
        if (['admin.files.index', 'admin.downloads.index'].includes(r)) {
          return true;
        }
      }

      // Complaint Officer allowed items
      if (isComplaintOfficer.value) {
        if ([
          'admin.complaints.index',
          'admin.complaints.categories',
          'admin.surveys.index',
        ].includes(r)) {
          return true;
        }
      }

      // PPID Admin allowed items
      if (isPpidAdmin.value) {
        if (r && (r.startsWith('admin.ppid.') || r === 'admin.downloads.index')) {
          return true;
        }
      }

      // Supervisor Layanan KTP-el allowed items (All KTP-el modules active: Antrian, Serah Terima, Pengarsipan)
      if (isKtpSupervisor.value) {
        if (r && (r.startsWith('admin.queue.') || r.startsWith('admin.handover.') || r.startsWith('admin.archive.'))) {
          return true;
        }
      }

      // Queue Officer allowed items
      if (isQueueOfficer.value) {
        if (r && r.startsWith('admin.queue.')) {
          return true;
        }
      }

      // Queue Verifier allowed items
      if (isQueueVerifier.value) {
        if (['admin.queue.dashboard', 'admin.queue.applications.index', 'admin.queue.applications.show'].includes(r)) {
          return true;
        }
      }

      // Serah Terima KTP-el allowed items
      if (isHandoverOfficer.value || isQueueOfficer.value || userPermissions.value.some(p => p.startsWith('handover.'))) {
        if (r && r.startsWith('admin.handover.')) {
          return true;
        }
      }

      // Pengarsipan Permohonan Online allowed items
      if (isArchiveOperator.value || isQueueOfficer.value || userPermissions.value.some(p => p.startsWith('archive.'))) {
        if (r && r.startsWith('admin.archive.')) {
          if (r === 'admin.archive.settings.index') {
            return isSuperAdmin.value || isWebsiteAdmin.value || userPermissions.value.includes('archive.settings.manage');
          }
          return true;
        }
      }

      return false;
    });

    return { ...group, items };
  }).filter(group => group.items.length > 0);
});

const activePageName = computed(() => {
  const currentRouteName = route().current();
  if (currentRouteName === 'admin.dashboard') return 'Ikhtisar';
  if (currentRouteName?.startsWith('admin.handover.')) return 'Serah Terima KTP-el';
  if (currentRouteName?.startsWith('admin.queue.')) return 'Antrian Online KTP-el';
  if (currentRouteName?.startsWith('admin.archive.')) return 'Pengarsipan Permohonan';
  if (currentRouteName?.startsWith('admin.complaints.')) return 'Layanan Pengaduan';
  if (currentRouteName?.startsWith('admin.ppid.')) return 'Layanan PPID';
  if (currentRouteName?.startsWith('admin.news.')) return 'Berita & Artikel';
  if (currentRouteName?.startsWith('admin.profile.')) return 'Profil Instansi';
  if (currentRouteName?.startsWith('admin.visitor-analytics')) return 'Analisis Pengunjung';
  if (currentRouteName?.startsWith('admin.users.')) return 'Pengguna & Peran';
  if (currentRouteName?.startsWith('admin.theme.')) return 'Kustomisasi Tema';
  if (currentRouteName?.startsWith('admin.files.')) return 'Manajer Berkas';
  if (currentRouteName?.startsWith('admin.downloads.')) return 'Pusat Unduhan';
  if (currentRouteName?.startsWith('admin.audit-logs.')) return 'Log Audit Aktivitas';
  return 'Admin';
});

const safeRoute = (name, params) => {
  if (!name) return '#';
  try {
    return route().has(name) ? route(name, params) : '#';
  } catch (e) {
    return '#';
  }
};

const isActiveRoute = (routeName) => {
  if (!routeName) return false;
  try {
    return route().has(routeName) ? route().current(routeName) : false;
  } catch (e) {
    return false;
  }
};

// Sidebar scroll position preservation
const sidebarNavRef = ref(null);

const handleSidebarScroll = () => {
  if (sidebarNavRef.value) {
    sessionStorage.setItem('admin_sidebar_scroll', sidebarNavRef.value.scrollTop);
  }
};

const restoreSidebarScroll = () => {
  const savedScroll = sessionStorage.getItem('admin_sidebar_scroll');
  if (savedScroll !== null && sidebarNavRef.value) {
    sidebarNavRef.value.scrollTop = parseInt(savedScroll, 10);
  }
};

watch(() => page.url, () => {
  if (window.innerWidth < 768) {
    sidebarOpen.value = false;
  }
  nextTick(() => {
    restoreSidebarScroll();
  });
});

onMounted(() => {
  // Load sidebar config (default closed on mobile)
  if (window.innerWidth < 768) {
    sidebarOpen.value = false;
  } else if (localStorage.getItem('admin-sidebar-open') === 'false') {
    sidebarOpen.value = false;
  }
  
  // Load dark mode
  isDark.value = localStorage.getItem('dark-mode') === 'true' || 
                 (!('dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }

  // Load Notifications
  if (page.props.auth.user) {
    fetchNotifications();
  }

  // Restore sidebar scroll position
  nextTick(() => {
    restoreSidebarScroll();
  });
});
</script>

<style>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>
