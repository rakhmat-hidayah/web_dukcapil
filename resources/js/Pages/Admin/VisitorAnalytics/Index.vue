<template>
  <Head title="Analisis Pengunjung & Intelligence - Dukcapil Dompu" />

  <AdminLayout>
    <div class="space-y-6 text-left pb-12">
      <!-- Top Control Header Banner -->
      <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                Analisis &amp; Intelijen Pengunjung
              </span>
              <span class="text-xs text-blue-200/60 font-mono">| Deteksi Bot &amp; Pemindaian Keamanan Real-Time</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Lalu Lintas Pengunjung &amp; Deteksi Bot
            </h1>
            <p class="text-xs text-blue-100/70 mt-1 max-w-2xl leading-relaxed">
              Memisahkan pengunjung manusia asli dari Search Engine Bots, Social Media Crawlers, AI Crawlers, SEO Bots, dan Security Scanners secara otomatis.
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 shrink-0">
            <button 
              @click="refreshData" 
              :disabled="loading"
              class="px-4 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white text-xs font-bold rounded-2xl transition flex items-center gap-2 active:scale-95 cursor-pointer"
            >
              <RotateCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
              <span>Segarkan Data</span>
            </button>

            <a 
              :href="route('admin.visitor-analytics.export', filterForm)" 
              target="_blank"
              class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-2xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 active:scale-95"
            >
              <Download class="w-4 h-4" />
              <span>Ekspor CSV</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Summary Cards Grid (6 Cards) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Card 1: Human Visitors (Isolated) -->
        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Pengunjung Manusia</span>
            <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
              <UserCheck class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300">
            {{ formatNumber(summary.human_visitors) }}
          </p>
          <p class="text-[10px] text-gray-400 font-medium">Hanya manusia asli (Googlebot/Scan terpisah)</p>
        </div>

        <!-- Card 2: Unique Humans -->
        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Pengunjung Unik</span>
            <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl">
              <Users class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-black text-gray-800 dark:text-zinc-100">
            {{ formatNumber(summary.unique_humans) }}
          </p>
          <p class="text-[10px] text-gray-400 font-medium">Pengunjung unik manusia (UUID)</p>
        </div>

        <!-- Card 3: Online Visitors (Active Now) -->
        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Pengunjung Aktif</span>
            <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
              <Radio class="w-4 h-4 text-emerald-500 animate-pulse" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
              {{ formatNumber(summary.online_visitors || 0) }}
            </p>
            <span class="inline-flex items-center gap-1 text-[9px] font-extrabold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 px-2 py-0.5 rounded-full border border-emerald-300 dark:border-emerald-800">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
              Live
            </span>
          </div>
          <p class="text-[10px] text-gray-400 font-medium">Pengunjung aktif (5m terakhir)</p>
        </div>

        <!-- Card 4: Bot Visitors -->
        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Kunjungan Bot</span>
            <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
              <Bot class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-black text-gray-800 dark:text-zinc-100">
            {{ formatNumber(summary.bot_visitors) }}
          </p>
          <p class="text-[10px] text-gray-400 font-medium">Search Engine, Social &amp; AI Crawlers</p>
        </div>

        <!-- Card 5: Security Scans -->
        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Pemindaian Keamanan</span>
            <div class="p-2 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl">
              <ShieldAlert class="w-4 h-4" />
            </div>
          </div>
          <p class="text-2xl font-black text-rose-600 dark:text-rose-400">
            {{ formatNumber(summary.security_scans) }}
          </p>
          <p class="text-[10px] text-gray-400 font-medium">Percobaan probe (/wp-login, /.env, dll)</p>
        </div>

        <!-- Card 6: Top Bot -->
        <div class="p-5 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Top Bot Terbanyak</span>
            <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl">
              <Activity class="w-4 h-4" />
            </div>
          </div>
          <p class="text-base font-black text-gray-800 dark:text-zinc-100 truncate" :title="summary.top_bot">
            {{ summary.top_bot || 'Tidak Ada Bot' }}
          </p>
          <p class="text-[10px] text-gray-400 font-medium">Bot paling sering mengujungi hari ini</p>
        </div>
      </div>

      <!-- Traffic Category Tab Selector -->
      <div class="flex flex-wrap items-center gap-2 p-1.5 bg-gray-100 dark:bg-zinc-800/80 rounded-2xl w-fit border border-gray-200/60 dark:border-zinc-700/60">
        <button 
          @click="setTrafficType('human')"
          class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 cursor-pointer"
          :class="filterForm.traffic_type === 'human' ? 'bg-emerald-600 text-white shadow-md' : 'text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white'"
        >
          <UserCheck class="w-4 h-4" />
          <span>👤 Pengunjung Manusia (Default)</span>
        </button>

        <button 
          @click="setTrafficType('bots')"
          class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 cursor-pointer"
          :class="filterForm.traffic_type === 'bots' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white'"
        >
          <Bot class="w-4 h-4" />
          <span>🤖 Bot &amp; Perayap (Crawler)</span>
        </button>

        <button 
          @click="setTrafficType('security')"
          class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 cursor-pointer"
          :class="filterForm.traffic_type === 'security' ? 'bg-rose-600 text-white shadow-md' : 'text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white'"
        >
          <ShieldAlert class="w-4 h-4" />
          <span>🛡️ Pemindaian Keamanan</span>
        </button>

        <button 
          @click="setTrafficType('all')"
          class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 cursor-pointer"
          :class="filterForm.traffic_type === 'all' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-md' : 'text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white'"
        >
          <Globe class="w-4 h-4" />
          <span>🌐 All Traffic</span>
        </button>
      </div>

      <!-- Filter Controls & Search Bar -->
      <div class="p-4 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          <!-- Search -->
          <div class="lg:col-span-2 relative">
            <Search class="w-4 h-4 absolute left-3.5 top-3 text-gray-400" />
            <input 
              v-model="filterForm.search" 
              @keyup.enter="applyFilters"
              type="text" 
              placeholder="Cari IP Address, URL, Bot Name, atau Alasan Deteksi..." 
              class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            />
          </div>

          <!-- Date Filter -->
          <div>
            <input 
              v-model="filterForm.date" 
              @change="applyFilters"
              type="date" 
              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            />
          </div>

          <!-- Browser Filter -->
          <div>
            <select 
              v-model="filterForm.browser" 
              @change="applyFilters"
              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            >
              <option value="">Semua Browser</option>
              <option v-for="b in browserOptions" :key="b" :value="b">{{ b }}</option>
            </select>
          </div>

          <!-- Device Filter -->
          <div>
            <select 
              v-model="filterForm.device" 
              @change="applyFilters"
              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
            >
              <option value="">Semua Device</option>
              <option v-for="d in deviceOptions" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-zinc-800 text-xs">
          <div class="flex items-center gap-3">
            <!-- Status Filter -->
            <span class="text-gray-400 font-bold uppercase text-[10px]">Status Geo:</span>
            <button 
              @click="setStatusFilter('')" 
              class="px-2.5 py-1 rounded-xl text-[11px] font-bold transition"
              :class="!filterForm.status ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400'"
            >
              Semua
            </button>
            <button 
              @click="setStatusFilter('completed')" 
              class="px-2.5 py-1 rounded-xl text-[11px] font-bold transition"
              :class="filterForm.status === 'completed' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400'"
            >
              Completed
            </button>
            <button 
              @click="setStatusFilter('pending')" 
              class="px-2.5 py-1 rounded-xl text-[11px] font-bold transition"
              :class="filterForm.status === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400'"
            >
              Pending
            </button>
            <button 
              @click="setStatusFilter('failed')" 
              class="px-2.5 py-1 rounded-xl text-[11px] font-bold transition"
              :class="filterForm.status === 'failed' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400'"
            >
              Failed
            </button>
          </div>

          <button 
            v-if="hasActiveFilters" 
            @click="resetFilters"
            class="text-xs font-bold text-red-500 hover:underline flex items-center gap-1 cursor-pointer"
          >
            <X class="w-3.5 h-3.5" /> Reset Filter
          </button>
        </div>
      </div>

      <!-- Visitor Data Table -->
      <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-gray-50/80 dark:bg-zinc-800/80 border-b border-gray-100 dark:border-zinc-800 text-[10px] font-black uppercase tracking-wider text-gray-400 select-none">
                <th class="py-3.5 px-4 w-12 text-center">No</th>
                <th class="py-3.5 px-4">Klasifikasi & Intelijen</th>
                <th class="py-3.5 px-4">Tanggal & Waktu</th>
                <th class="py-3.5 px-4">IP Address</th>
                <th class="py-3.5 px-4">Browser, OS & Perangkat</th>
                <th class="py-3.5 px-4">Halaman Dikunjungi</th>
                <th class="py-3.5 px-4">Status Geolocation</th>
                <th class="py-3.5 px-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-gray-700 dark:text-zinc-300">
              <tr v-if="logs.data.length === 0">
                <td colspan="8" class="py-12 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <Activity class="w-8 h-8 text-gray-300" />
                    <p class="font-bold text-xs">Belum ada data kunjungan yang sesuai dengan filter ini.</p>
                  </div>
                </td>
              </tr>

              <tr v-for="(log, index) in logs.data" :key="log.id" class="hover:bg-gray-50/60 dark:hover:bg-zinc-800/50 transition group">
                <td class="py-3.5 px-4 text-center font-mono text-[11px] text-gray-400">
                  {{ (logs.current_page - 1) * logs.per_page + index + 1 }}
                </td>

                <!-- Klasifikasi & Intelijen Traffic -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <!-- Human Badge -->
                  <div v-if="log.is_human" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/50">
                    <UserCheck class="w-3 h-3 text-emerald-600" />
                    👤 HUMAN
                  </div>

                  <!-- Security Scanner Badge -->
                  <div v-else-if="log.visitor_category === 'security_scanner'" class="space-y-1">
                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200/50">
                      <ShieldAlert class="w-3 h-3 text-rose-600 animate-pulse" />
                      🛡️ SCANNER
                    </div>
                    <p class="text-[9px] text-rose-600 dark:text-rose-400 font-mono truncate max-w-[140px]" :title="log.detection_reason">
                      {{ log.bot_name || 'Security Probe' }}
                    </p>
                  </div>

                  <!-- Bot / Robot Badge -->
                  <div v-else class="space-y-1">
                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200/50">
                      <Bot class="w-3 h-3 text-indigo-600" />
                      🤖 ROBOT
                    </div>
                    <p class="text-[9px] text-indigo-600 dark:text-indigo-400 font-mono truncate max-w-[140px]" :title="log.detection_reason">
                      {{ log.bot_name || formatCategoryName(log.visitor_category) }}
                    </p>
                  </div>
                </td>

                <!-- Tanggal & Waktu -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <p class="font-bold text-gray-800 dark:text-zinc-100 text-xs">{{ log.visited_at }}</p>
                  <p class="text-[10px] text-gray-400 font-medium">{{ log.visited_time_ago }}</p>
                </td>

                <!-- Masked IP Address -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span class="font-mono text-xs font-bold text-slate-700 dark:text-zinc-300 bg-slate-100 dark:bg-zinc-800 px-2.5 py-1 rounded-xl">
                    {{ log.masked_ip }}
                  </span>
                </td>

                <!-- Browser, OS & Perangkat -->
                <td class="py-3.5 px-4">
                  <div class="space-y-1">
                    <p class="font-extrabold text-xs text-gray-800 dark:text-zinc-100 flex items-center gap-1.5">
                      <Globe class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                      {{ log.browser }} <span v-if="log.browser_version" class="text-[10px] text-gray-400 font-mono">v{{ log.browser_version }}</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5 text-[10px] text-gray-500 dark:text-zinc-400">
                      <span>OS: {{ log.operating_system }} <span v-if="log.operating_system_version">({{ log.operating_system_version }})</span></span>
                      <!-- Device Badge -->
                      <span 
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9.5px] font-bold border shrink-0"
                        :class="{
                          'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200 dark:border-sky-800': (log.device_type || '').toLowerCase() === 'desktop',
                          'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200 dark:border-purple-800': (log.device_type || '').toLowerCase() === 'mobile',
                          'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800': (log.device_type || '').toLowerCase() === 'tablet',
                          'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300 border-gray-200 dark:border-zinc-700': !['desktop','mobile','tablet'].includes((log.device_type || '').toLowerCase())
                        }"
                      >
                        <Laptop v-if="(log.device_type || '').toLowerCase() === 'desktop'" class="w-3 h-3 text-sky-600 dark:text-sky-400" />
                        <Smartphone v-else-if="(log.device_type || '').toLowerCase() === 'mobile'" class="w-3 h-3 text-purple-600 dark:text-purple-400" />
                        <Tablet v-else-if="(log.device_type || '').toLowerCase() === 'tablet'" class="w-3 h-3 text-amber-600 dark:text-amber-400" />
                        <Bot v-else class="w-3 h-3 text-gray-500" />
                        <span>{{ log.device_type || 'Desktop' }}</span>
                      </span>
                    </div>
                  </div>
                </td>

                <!-- Halaman Dikunjungi -->
                <td class="py-3.5 px-4 max-w-xs">
                  <a :href="log.url" target="_blank" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline truncate block" :title="log.url">
                    {{ formatUrlSnippet(log.url) }}
                  </a>
                  <p v-if="log.referrer" class="text-[10px] text-gray-400 truncate" :title="log.referrer">
                    Ref: {{ formatUrlSnippet(log.referrer) }}
                  </p>
                </td>

                <!-- Status Geolocation -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div v-if="log.geolocation.status === 'completed'" class="space-y-0.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[10px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                      <MapPin class="w-3 h-3" />
                      {{ log.geolocation.city || log.geolocation.country || 'Completed' }}
                    </span>
                    <p class="text-[9px] text-gray-400 font-medium">
                      {{ log.geolocation.province ? log.geolocation.province + ', ' : '' }}{{ log.geolocation.country }}
                    </p>
                  </div>

                  <div v-else-if="log.geolocation.status === 'pending'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[10px] font-black bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <RotateCw class="w-3 h-3 animate-spin text-amber-600" />
                    Pending (Queue)
                  </div>

                  <div v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[10px] font-black bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300" :title="log.geolocation.error_message">
                    <AlertTriangle class="w-3 h-3 text-red-600" />
                    Failed
                  </div>
                </td>

                <!-- Aksi -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <button 
                    @click="openDetailModal(log.id)"
                    class="px-3 py-1.5 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/40 dark:hover:bg-primary-900/60 text-primary-600 dark:text-primary-400 text-xs font-bold rounded-xl transition cursor-pointer flex items-center gap-1 mx-auto"
                  >
                    <Eye class="w-3.5 h-3.5" />
                    Detail
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <div v-if="logs.links && logs.links.length > 3" class="p-4 bg-gray-50/70 dark:bg-zinc-800/50 border-t border-gray-100 dark:border-zinc-800 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
          <p class="text-gray-500 dark:text-zinc-400 font-medium text-center md:text-left text-xs shrink-0">
            Menampilkan <span class="font-bold text-gray-800 dark:text-zinc-100">{{ formatNumber(logs.from || 0) }}</span> sampai <span class="font-bold text-gray-800 dark:text-zinc-100">{{ formatNumber(logs.to || 0) }}</span> dari <span class="font-bold text-gray-800 dark:text-zinc-100">{{ formatNumber(logs.total) }}</span> log visitor
          </p>

          <!-- Scrollable Pagination Buttons Container for Mobile & Touch Devices -->
          <div 
            ref="paginationRef" 
            class="w-full md:w-auto flex items-center justify-start sm:justify-center md:justify-end gap-1.5 overflow-x-auto max-w-full pb-2 md:pb-0 pt-1 px-1 scrollbar-thin"
          >
            <template v-for="(link, i) in logs.links" :key="i">
              <Link 
                v-if="link.url"
                :href="formatPageUrl(link.url)"
                preserve-scroll
                preserve-state
                class="min-w-[38px] h-9.5 px-3 rounded-xl font-bold transition text-xs flex items-center justify-center shrink-0 cursor-pointer active:scale-95"
                :class="[
                  link.active 
                    ? 'active-page-btn bg-primary-600 text-white shadow-md shadow-primary-600/30 ring-2 ring-primary-600/30 font-black' 
                    : 'bg-white dark:bg-zinc-900 text-gray-700 dark:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-800 border border-gray-200 dark:border-zinc-700 shadow-xs'
                ]"
              >
                <span v-html="cleanLabel(link.label)"></span>
              </Link>
              <span 
                v-else
                class="min-w-[38px] h-9.5 px-3 rounded-xl font-medium text-xs flex items-center justify-center shrink-0 text-gray-400 dark:text-zinc-600 bg-gray-100/50 dark:bg-zinc-800/30 border border-gray-100 dark:border-zinc-800/50 opacity-50 cursor-not-allowed"
                v-html="cleanLabel(link.label)"
              ></span>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- DETAIL VISITOR MODAL -->
    <transition name="fade">
      <div v-if="detailModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
          <!-- Modal Header -->
          <div class="p-5 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex justify-between items-center shrink-0">
            <div class="flex items-center gap-2.5">
              <div class="p-2 bg-blue-500/20 text-blue-300 rounded-xl border border-blue-400/30">
                <ShieldCheck class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black">Rincian Lengkap Visitor Intelligence</h3>
                <p class="text-[10px] text-blue-200/70 font-mono">Sensitif: Khusus Administrator & Super Administrator</p>
              </div>
            </div>
            <button @click="detailModalOpen = false" class="p-1.5 hover:bg-white/10 rounded-xl text-gray-400 hover:text-white transition">
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body -->
          <div v-if="detailLoading" class="p-12 text-center text-gray-400 space-y-3">
            <RotateCw class="w-8 h-8 animate-spin mx-auto text-primary-500" />
            <p class="text-xs font-bold">Memuat rincian data visitor & inteligensi bot...</p>
          </div>

          <div v-else-if="selectedVisitor" class="p-6 space-y-6 overflow-y-auto flex-1 text-xs">
            <!-- 0. INTELIJEN & BOT DETECTED -->
            <div class="p-4 rounded-2xl border space-y-2"
              :class="{
                'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-900': selectedVisitor.is_human,
                'bg-rose-50/70 border-rose-200 dark:bg-rose-950/40 dark:border-rose-900': selectedVisitor.visitor_category === 'security_scanner',
                'bg-indigo-50/70 border-indigo-200 dark:bg-indigo-950/40 dark:border-indigo-900': !selectedVisitor.is_human && selectedVisitor.visitor_category !== 'security_scanner'
              }"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider">Hasil Intelligence Analysis</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase"
                  :class="{
                    'bg-emerald-600 text-white': selectedVisitor.is_human,
                    'bg-rose-600 text-white': selectedVisitor.visitor_category === 'security_scanner',
                    'bg-indigo-600 text-white': !selectedVisitor.is_human && selectedVisitor.visitor_category !== 'security_scanner'
                  }"
                >
                  {{ selectedVisitor.is_human ? '👤 HUMAN VISITOR' : (selectedVisitor.visitor_category === 'security_scanner' ? '🛡️ SECURITY SCANNER' : '🤖 BOT / CRAWLER') }}
                </span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1">
                <div>
                  <p class="text-[10px] font-bold opacity-70">Nama Bot / System:</p>
                  <p class="font-extrabold">{{ selectedVisitor.bot_name || (selectedVisitor.is_human ? 'Legitimate Human' : 'Unknown Script') }}</p>
                </div>
                <div>
                  <p class="text-[10px] font-bold opacity-70">Kategori Traffic:</p>
                  <p class="font-extrabold uppercase">{{ formatCategoryName(selectedVisitor.visitor_category) }}</p>
                </div>
                <div class="sm:col-span-2">
                  <p class="text-[10px] font-bold opacity-70">Alasan Deteksi (Detection Reason):</p>
                  <p class="font-mono text-[11px] font-bold bg-white/70 dark:bg-zinc-900/60 p-2 rounded-xl border border-black/5 dark:border-white/10 select-all">
                    {{ selectedVisitor.detection_reason }}
                  </p>
                </div>
              </div>
            </div>

            <!-- 1. INFORMASI UMUM -->
            <div class="space-y-3">
              <h4 class="text-[11px] font-black uppercase tracking-wider text-primary-600 dark:text-primary-400 pb-1 border-b border-gray-100 dark:border-zinc-800 flex items-center gap-1.5">
                <Activity class="w-4 h-4" /> 1. Informasi Umum Kunjungan
              </h4>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-gray-50 dark:bg-zinc-800/60 p-4 rounded-2xl border border-gray-100 dark:border-zinc-800">
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Visitor UUID</p>
                  <p class="font-mono font-bold text-gray-800 dark:text-zinc-100 select-all truncate">{{ selectedVisitor.visitor_uuid }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Session ID</p>
                  <p class="font-mono font-bold text-gray-800 dark:text-zinc-100 select-all truncate">{{ selectedVisitor.session_id }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Waktu Akses</p>
                  <p class="font-bold text-gray-800 dark:text-zinc-100">{{ selectedVisitor.visited_at }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">IP Address Lengkap (Unmasked)</p>
                  <p class="font-mono font-black text-emerald-600 dark:text-emerald-400 text-xs bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-lg inline-block select-all">
                    {{ selectedVisitor.ip_address }}
                  </p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Browser & Versi</p>
                  <p class="font-bold text-gray-800 dark:text-zinc-100">{{ selectedVisitor.browser }} {{ selectedVisitor.browser_version }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Sistem Operasi (OS)</p>
                  <p class="font-bold text-gray-800 dark:text-zinc-100">{{ selectedVisitor.operating_system }} {{ selectedVisitor.operating_system_version }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Perangkat & Tipe</p>
                  <p class="font-bold text-gray-800 dark:text-zinc-100">{{ selectedVisitor.device_type }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">HTTP Method</p>
                  <p class="font-mono font-bold text-gray-800 dark:text-zinc-100">{{ selectedVisitor.method }}</p>
                </div>
              </div>
            </div>

            <!-- 2. HALAMAN & REFERER -->
            <div class="space-y-3">
              <h4 class="text-[11px] font-black uppercase tracking-wider text-primary-600 dark:text-primary-400 pb-1 border-b border-gray-100 dark:border-zinc-800 flex items-center gap-1.5">
                <Globe class="w-4 h-4" /> 2. URL Halaman & Rujukan (Referer)
              </h4>

              <div class="bg-gray-50 dark:bg-zinc-800/60 p-4 rounded-2xl border border-gray-100 dark:border-zinc-800 space-y-3">
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Current URL</p>
                  <a :href="selectedVisitor.url" target="_blank" class="font-mono text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline break-all block">
                    {{ selectedVisitor.url }}
                  </a>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Previous Referer</p>
                  <p class="font-mono text-xs text-gray-600 dark:text-zinc-300 break-all">
                    {{ selectedVisitor.referrer || 'Langsung (Direct / Bookmark)' }}
                  </p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Raw User Agent</p>
                  <p class="font-mono text-[10px] text-gray-500 dark:text-zinc-400 bg-white dark:bg-zinc-900 p-2 rounded-xl border border-gray-200 dark:border-zinc-700 break-all select-all">
                    {{ selectedVisitor.user_agent }}
                  </p>
                </div>
              </div>
            </div>

              <!-- 3. GEOLOCATION BACKGROUND QUEUE RESULT -->
              <div class="space-y-3">
                <h4 class="text-[11px] font-black uppercase tracking-wider text-primary-600 dark:text-primary-400 pb-1 border-b border-gray-100 dark:border-zinc-800 flex items-center gap-1.5">
                  <MapPin class="w-4 h-4" /> 3. Hasil Geolocation (Estimasi Berdasarkan IP)
                </h4>

                <!-- Case Completed -->
                <div v-if="selectedVisitor.geolocation && selectedVisitor.geolocation.status === 'completed'" class="bg-gray-50/90 dark:bg-zinc-900 p-5 rounded-2xl border border-gray-200/80 dark:border-zinc-800 space-y-4 shadow-sm">
                  
                  <div class="flex flex-wrap items-center justify-between border-b border-gray-200 dark:border-zinc-800 pb-3 gap-2">
                    <span class="text-xs font-black text-slate-800 dark:text-zinc-100 flex items-center gap-1.5 font-mono uppercase tracking-tight">
                      📍 ESTIMASI LOKASI BERDASARKAN IP
                    </span>

                    <!-- Confidence Level Indicator Badge -->
                    <span 
                      class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 border"
                      :class="getConfidenceInfo(selectedVisitor.geolocation).badgeClass"
                    >
                      <span class="w-2 h-2 rounded-full" :class="getConfidenceInfo(selectedVisitor.geolocation).dotClass"></span>
                      Confidence: {{ getConfidenceInfo(selectedVisitor.geolocation).text }}
                    </span>
                  </div>

                  <!-- Detailed IP Geolocation Fields -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5 text-xs font-mono">
                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">🌏 Negara</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ selectedVisitor.geolocation.country }} <template v-if="selectedVisitor.geolocation.country_code">({{ selectedVisitor.geolocation.country_code }})</template></span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">🏢 Provinsi</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ selectedVisitor.geolocation.province || '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">🏙️ Kabupaten/Kota</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ selectedVisitor.geolocation.city || '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">📮 Kecamatan</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ selectedVisitor.geolocation.district || '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">🏢 ASN</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100 truncate" :title="selectedVisitor.geolocation.asn">: {{ selectedVisitor.geolocation.asn || '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">📡 ISP</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100 truncate" :title="selectedVisitor.geolocation.isp">: {{ selectedVisitor.geolocation.isp || '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">🌐 IP Version</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ selectedVisitor.geolocation.ip_version || (selectedVisitor.ip_address?.includes(':') ? 'IPv6' : 'IPv4') }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">📍 Latitude</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ selectedVisitor.geolocation.latitude || '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">📍 Longitude</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ selectedVisitor.geolocation.longitude || '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">🕒 Timezone</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ selectedVisitor.geolocation.timezone || '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-2 sm:col-span-2">
                      <span class="text-gray-400 font-medium w-32 shrink-0">🎯 Confidence</span>
                      <span class="font-bold text-gray-800 dark:text-zinc-100">: {{ getConfidenceInfo(selectedVisitor.geolocation).desc }}</span>
                    </div>
                  </div>

                  <!-- Google Maps Link Box -->
                  <div v-if="selectedVisitor.geolocation.latitude && selectedVisitor.geolocation.longitude" class="pt-3 border-t border-gray-200 dark:border-zinc-800 space-y-2">
                    <span class="text-[10px] font-bold uppercase text-gray-400 font-mono block">🗺️ Google Maps:</span>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white dark:bg-zinc-800/80 p-3 rounded-xl border border-gray-200 dark:border-zinc-700">
                      <a 
                        :href="`https://maps.google.com/?q=${selectedVisitor.geolocation.latitude},${selectedVisitor.geolocation.longitude}`" 
                        target="_blank" 
                        class="text-xs font-mono font-bold text-primary-600 dark:text-primary-400 hover:underline break-all"
                      >
                        https://maps.google.com/?q={{ selectedVisitor.geolocation.latitude }},{{ selectedVisitor.geolocation.longitude }}
                      </a>

                      <a 
                        :href="`https://www.google.com/maps?q=${selectedVisitor.geolocation.latitude},${selectedVisitor.geolocation.longitude}`" 
                        target="_blank"
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm shrink-0"
                      >
                        <MapPin class="w-3.5 h-3.5" /> Buka Google Maps →
                      </a>
                    </div>
                  </div>
                </div>

              <!-- Case Pending -->
              <div v-else-if="selectedVisitor.geolocation && selectedVisitor.geolocation.status === 'pending'" class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 rounded-2xl flex items-center gap-3 text-amber-800 dark:text-amber-300">
                <RotateCw class="w-5 h-5 animate-spin text-amber-600 shrink-0" />
                <div>
                  <p class="font-bold text-xs">Geolocation sedang diproses di background queue...</p>
                  <p class="text-[10px] text-amber-700 dark:text-amber-400 mt-0.5">Pengunjung tidak perlu menunggu proses ini selesai. Hasil lokasi akan muncul secara otomatis begitu queue selesai.</p>
                </div>
              </div>

              <!-- Case Failed -->
              <div v-else class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 rounded-2xl space-y-2 text-red-800 dark:text-red-300">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <AlertTriangle class="w-5 h-5 text-red-600 shrink-0" />
                    <p class="font-bold text-xs">Lookup Geolocation Gagal / Timeout</p>
                  </div>
                  <button 
                    @click="retryGeolocation(selectedVisitor.id)"
                    class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white text-[10px] font-bold rounded-xl transition flex items-center gap-1 active:scale-95 cursor-pointer"
                  >
                    <RotateCw class="w-3 h-3" /> Kirim Ulang Queue
                  </button>
                </div>
                <p v-if="selectedVisitor.geolocation && selectedVisitor.geolocation.error_message" class="text-[10px] text-red-600 dark:text-red-400 font-mono bg-red-100/50 dark:bg-red-900/40 p-2 rounded-xl">
                  Error: {{ selectedVisitor.geolocation.error_message }}
                </p>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-4 bg-gray-50 dark:bg-zinc-800/80 border-t border-gray-100 dark:border-zinc-800 flex justify-end shrink-0">
            <button @click="detailModalOpen = false" class="px-5 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-800 dark:text-zinc-100 text-xs font-bold rounded-2xl transition cursor-pointer">
              Tutup Rincian
            </button>
          </div>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUpdated, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import { 
  Activity, Users, UserCheck, Eye, Radio, RotateCw, Download, Search, X, 
  Globe, Laptop, Smartphone, Tablet, Bot, MapPin, AlertTriangle, ShieldCheck, ShieldAlert 
} from '@lucide/vue';

const props = defineProps({
  summary: Object,
  logs: Object,
  filters: Object,
  browserOptions: Array,
  deviceOptions: Array,
});

const loading = ref(false);
const detailModalOpen = ref(false);
const detailLoading = ref(false);
const selectedVisitor = ref(null);

const filterForm = ref({
  search: props.filters?.search || '',
  traffic_type: props.filters?.traffic_type || 'human',
  date: props.filters?.date || '',
  browser: props.filters?.browser || '',
  device: props.filters?.device || '',
  status: props.filters?.status || '',
});

const hasActiveFilters = computed(() => {
  return filterForm.value.search || filterForm.value.date || filterForm.value.browser || filterForm.value.device || filterForm.value.status || filterForm.value.traffic_type !== 'human';
});

const applyFilters = () => {
  router.get(route('admin.visitor-analytics.index'), filterForm.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const setTrafficType = (typeVal) => {
  filterForm.value.traffic_type = typeVal;
  applyFilters();
};

const setStatusFilter = (statusVal) => {
  filterForm.value.status = statusVal;
  applyFilters();
};

const resetFilters = () => {
  filterForm.value = {
    search: '',
    traffic_type: 'human',
    date: '',
    browser: '',
    device: '',
    status: '',
  };
  applyFilters();
};

const refreshData = () => {
  loading.value = true;
  router.reload({
    onFinish: () => {
      loading.value = false;
    }
  });
};

const openDetailModal = async (id) => {
  detailModalOpen.value = true;
  detailLoading.value = true;
  selectedVisitor.value = null;

  try {
    const res = await axios.get(route('admin.visitor-analytics.show', id));
    selectedVisitor.value = res.data.data;
  } catch (err) {
    console.error('Failed to load visitor details', err);
  } finally {
    detailLoading.value = false;
  }
};

const retryGeolocation = async (id) => {
  try {
    await router.post(route('admin.visitor-analytics.retry-geolocation', id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        openDetailModal(id);
      }
    });
  } catch (err) {
    console.error('Failed to retry geolocation', err);
  }
};

const paginationRef = ref(null);

const scrollToActivePage = () => {
  nextTick(() => {
    if (!paginationRef.value) return;
    const activeBtn = paginationRef.value.querySelector('.active-page-btn');
    if (activeBtn) {
      activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
  });
};

onMounted(() => {
  scrollToActivePage();
});

onUpdated(() => {
  scrollToActivePage();
});

const formatPageUrl = (url) => {
  if (!url) return '#';
  let targetUrl = url;
  if (typeof window !== 'undefined' && window.location.protocol === 'https:' && targetUrl.startsWith('http://')) {
    targetUrl = targetUrl.replace(/^http:\/\//, 'https://');
  }
  return targetUrl;
};

const goToPage = (url) => {
  if (!url) return;
  let targetUrl = url;
  if (typeof window !== 'undefined' && window.location.protocol === 'https:' && targetUrl.startsWith('http://')) {
    targetUrl = targetUrl.replace(/^http:\/\//, 'https://');
  }

  router.visit(targetUrl, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      scrollToActivePage();
    }
  });
};

const cleanLabel = (label) => {
  if (!label) return '';
  return label
    .replace('&laquo; Previous', '&laquo; Prev')
    .replace('Previous', 'Prev')
    .replace('Next &raquo;', 'Next &raquo;');
};

const formatNumber = (num) => {
  if (!num) return '0';
  return new Intl.NumberFormat('id-ID').format(num);
};

const formatUrlSnippet = (url) => {
  if (!url) return '-';
  try {
    const parsed = new URL(url);
    return parsed.pathname + parsed.search;
  } catch (e) {
    return url.length > 40 ? url.substring(0, 40) + '...' : url;
  }
};

const formatCategoryName = (cat) => {
  if (!cat) return 'Human';
  const names = {
    'human': 'Human Visitor',
    'search_engine_bot': 'Search Engine Bot',
    'social_media_bot': 'Social Media Preview',
    'monitoring_bot': 'Uptime/Monitoring Bot',
    'ai_crawler': 'AI Crawler',
    'seo_bot': 'SEO & Analytics Bot',
    'security_scanner': 'Security Scanner Probe',
    'unknown_bot': 'Unknown Bot / Script',
  };
  return names[cat] || cat;
};

const getConfidenceInfo = (geo) => {
  const conf = (geo?.confidence || 'medium').toLowerCase();
  if (conf === 'high') {
    return {
      text: 'High',
      desc: 'High (GPS / WiFi Enterprise)',
      badgeClass: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
      dotClass: 'bg-emerald-500',
    };
  }
  if (conf === 'low') {
    return {
      text: 'Low',
      desc: 'Low (Mobile Gateway)',
      badgeClass: 'bg-orange-50 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300 border-orange-200 dark:border-orange-800',
      dotClass: 'bg-orange-500',
    };
  }
  if (conf === 'very_low') {
    return {
      text: 'Very Low',
      desc: 'Very Low (VPN / Proxy)',
      badgeClass: 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800',
      dotClass: 'bg-rose-500',
    };
  }
  return {
    text: 'Medium',
    desc: 'Medium (City-Level IP Geolocation)',
    badgeClass: 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
    dotClass: 'bg-amber-500',
  };
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
