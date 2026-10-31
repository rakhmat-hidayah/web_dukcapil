<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code', '500') - @yield('title', 'Internal Server Error') | Disdukcapil Kabupaten Dompu</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                screens: {
                    'xs': '375px',
                    'sm': '640px',
                    'md': '768px',
                    'lg': '1024px',
                    'xl': '1280px',
                    '2xl': '1536px',
                },
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc8fc',
                            400: '#36affa',
                            500: '#0c92eb',
                            600: '#0274cb',
                            700: '#035ca6',
                            800: '#074e89',
                            900: '#0c4272',
                            950: '#082a4d',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                        'pulse-glow': 'pulseGlow 3s ease-in-out infinite',
                        'spin-slow': 'spin 12s linear infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        pulseGlow: {
                            '0%, 100%': { opacity: '0.4', transform: 'scale(1)' },
                            '50%': { opacity: '0.7', transform: 'scale(1.04)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            touch-action: manipulation;
        }

        .dark body {
            background-color: #081a2e;
            color: #f1f5f9;
        }

        /* Fully Fluid Web Theme Card System */
        .web-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: 0 10px 30px -5px rgba(2, 116, 203, 0.08);
        }

        .dark .web-card {
            background: rgba(15, 30, 54, 0.8);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .web-card-inner {
            background: #ffffff;
            border: 1px solid #f1f5f9;
        }

        .dark .web-card-inner {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .gradient-heading {
            background: linear-gradient(135deg, #0274cb 0%, #0284c7 60%, #0369a1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dark .gradient-heading {
            background: linear-gradient(135deg, #ffffff 0%, #38bdf8 60%, #0284c7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .gradient-badge-error {
            background: #fff1f2;
            border: 1px solid #ffe4e6;
            color: #e11d48;
        }

        .dark .gradient-badge-error {
            background: rgba(225, 29, 72, 0.15);
            border-color: rgba(225, 29, 72, 0.3);
            color: #fda4af;
        }

        /* Prevent Text Selection on Buttons */
        button, a {
            -webkit-tap-highlight-color: transparent;
        }

        /* SVG Safeguard against unstyled sizing explosion */
        svg {
            max-width: 100%;
        }
        .text-sky-200 svg, header svg, main .gradient-badge-error svg, main .web-card svg, main a svg, main button svg {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="antialiased flex flex-col justify-between selection:bg-primary-600 selection:text-white relative min-h-screen">

    <!-- Top Running Banner Header (Fully Responsive Complaint Contacts Bar) -->
    <div class="bg-gradient-to-r from-primary-900 via-primary-800 to-primary-950 text-white text-[10.5px] xs:text-[11px] sm:text-[11.5px] font-medium py-2 px-3 sm:px-6 lg:px-8 border-b border-primary-700/50">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1.5 sm:gap-4 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 sm:gap-6">
                <!-- Official Email Complaint -->
                <span class="flex items-center gap-1.5 text-sky-200">
                    <svg class="w-3.5 h-3.5 text-sky-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Email Pengaduan: <strong class="text-white font-semibold">dukcapilkabdompu@gmail.com</strong></span>
                </span>
            </div>
        </div>
    </div>

    <!-- Mesh Background Gradient & Shapes -->
    <div class="fixed top-0 left-0 right-0 h-96 bg-gradient-to-b from-sky-100/60 via-blue-50/30 to-transparent dark:from-sky-950/40 dark:via-blue-950/20 dark:to-transparent pointer-events-none"></div>
    <div class="fixed top-20 right-10 w-72 sm:w-96 h-72 sm:h-96 bg-primary-500/10 rounded-full blur-[100px] pointer-events-none animate-pulse-glow"></div>
    <div class="fixed bottom-10 left-10 w-72 sm:w-[30rem] h-72 sm:h-[30rem] bg-sky-400/10 rounded-full blur-[120px] pointer-events-none animate-pulse-glow" style="animation-delay: 1.5s;"></div>

    @php
        $isAdminRoute = request()->is('admin*') || str_contains(request()->url(), '/admin');
        $backUrl = $isAdminRoute ? '/admin/dashboard' : '/';
        $backLabel = $isAdminRoute ? 'Dashboard Admin' : 'Beranda Utama';
    @endphp

    <!-- Main Navigation Header (Dual Logo Brand - Fully Responsive) -->
    <header class="w-full z-20 sticky top-0 bg-white/98 dark:bg-zinc-900/98 backdrop-blur border-b border-gray-100 dark:border-zinc-800/80 transition-colors shadow-sm">
        <div class="max-w-7xl mx-auto px-3 xs:px-4 sm:px-8 py-2 sm:py-2.5 flex items-center justify-between gap-2 sm:gap-4">
            
            <!-- Dual Logo: Logo Kabupaten Dompu & Logo Disdukcapil Dompu -->
            <a href="{{ $backUrl }}" class="flex items-center gap-1.5 xs:gap-2.5 select-none group shrink-0 px-2 xs:px-2.5 py-1 rounded-xl transition-all bg-white dark:bg-zinc-800 shadow-sm border border-gray-200/80 dark:border-zinc-700">
                <!-- Logo 1: Logo Daerah Kabupaten Dompu -->
                <img 
                    src="/images/logo-dompu.png" 
                    alt="Logo Kabupaten Dompu" 
                    class="h-7 xs:h-8 sm:h-9 w-auto object-contain transition-transform duration-200 group-hover:scale-105"
                />

                <!-- Thin Vertical Divider -->
                <div class="h-4 xs:h-5 sm:h-6 w-px bg-gray-200 dark:bg-zinc-700"></div>

                <!-- Logo 2: Logo Official Disdukcapil Dompu -->
                <img 
                    src="/images/logo-dukcapil.png" 
                    alt="Logo Disdukcapil Dompu" 
                    class="h-8 xs:h-9 sm:h-10 w-auto object-contain transition-transform duration-200 group-hover:scale-105"
                />
            </a>

            <!-- Agency Title & Action -->
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden md:block text-right">
                    <h1 class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900 dark:text-white uppercase leading-tight">
                        Disdukcapil Kabupaten Dompu
                    </h1>
                    <p class="text-[10px] sm:text-xs text-primary-600 dark:text-sky-400 font-semibold">
                        Portal Layanan Publik Kependudukan
                    </p>
                </div>
                
                <a href="{{ $backUrl }}" class="inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300 font-bold text-xs hover:bg-primary-100 transition shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>{{ $backLabel }}</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content (Hero Section - Responsive Grid) -->
    <main class="flex-1 flex items-center justify-center p-3 xs:p-4 sm:p-6 lg:p-8 z-10 my-auto">
        <div class="max-w-6xl w-full mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 lg:gap-12 items-center">
            
            <!-- Left Column: Premium Server & Network Illustration (Responsive Sizing) -->
            <div class="lg:col-span-5 flex justify-center order-2 lg:order-1">
                <div class="relative w-full max-w-[220px] xs:max-w-[260px] sm:max-w-[320px] lg:max-w-[360px] aspect-square flex items-center justify-center">
                    
                    <!-- Soft Radial Glow -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary-400/20 to-sky-300/30 dark:from-primary-600/20 dark:to-sky-500/20 rounded-full blur-2xl sm:blur-3xl transform scale-90"></div>

                    <!-- Main Server SVG Graphic -->
                    <div class="relative z-10 animate-float w-full h-full flex items-center justify-center p-2 sm:p-4">
                        @yield('illustration')
                    </div>
                </div>
            </div>

            <!-- Right Column: Status & Public Guidance & Actions (Responsive Typography & Spacing) -->
            <div class="lg:col-span-7 space-y-4 sm:space-y-6 order-1 lg:order-2 text-center lg:text-left">
                
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1 sm:py-1.5 rounded-full gradient-badge-error text-[10px] sm:text-xs font-extrabold tracking-wider uppercase shadow-sm">
                    <svg class="w-3.5 sm:w-4 h-3.5 sm:h-4 text-rose-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>@yield('badge', 'SERVER ERROR')</span>
                </div>

                <!-- Error Code & Title -->
                <div class="space-y-0.5 sm:space-y-1">
                    <h2 class="text-5xl xs:text-6xl sm:text-7xl lg:text-8xl font-black tracking-tight gradient-heading leading-none font-mono">
                        @yield('code', '500')
                    </h2>
                    <h3 class="text-xl xs:text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        @yield('title', 'Internal Server Error')
                    </h3>
                </div>

                <!-- Main Description -->
                <p class="text-xs xs:text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-xl mx-auto lg:mx-0 font-normal">
                    @yield('message', 'Mohon maaf, terjadi gangguan pada sistem saat memproses permintaan Anda. Tim kami telah menerima notifikasi dan sedang melakukan penanganan.')
                </p>

                <!-- Replacement Public Guidance Card (Responsive Layout) -->
                <div class="web-card web-card-inner p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl text-left space-y-3 sm:space-y-4 max-w-xl mx-auto lg:mx-0 shadow-sm border border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2.5 sm:pb-3">
                        <div class="flex items-center gap-2 text-primary-700 dark:text-sky-300 font-bold text-xs sm:text-sm">
                            <svg class="w-4 sm:w-5 h-4 sm:h-5 text-primary-600 dark:text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Panduan Bantuan & Alternatif Layanan</span>
                        </div>
                    </div>

                    <!-- Useful Service Alternatives (Grid 1 col on mobile, 2 cols on tablet/desktop) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 text-xs">
                        <!-- Box 1: Operating Hours -->
                        <div class="p-2.5 sm:p-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 space-y-0.5">
                            <span class="block text-[9.5px] sm:text-[10px] font-extrabold uppercase text-slate-400">Jam Layanan Loket Kantor</span>
                            <p class="font-bold text-slate-800 dark:text-slate-200 text-[10.5px] sm:text-[11px] leading-tight">
                                Senin - Kamis: 08:00 - 16:00 WITA<br>
                                Jumat: 08:00 - 16:30 WITA
                            </p>
                        </div>

                        <!-- Box 2: Alamat Kantor -->
                        <div class="p-2.5 sm:p-3 rounded-xl sm:rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 space-y-0.5">
                            <span class="block text-[9.5px] sm:text-[10px] font-extrabold uppercase text-slate-400">Alamat Kantor Resmi</span>
                            <p class="font-bold text-slate-800 dark:text-slate-200 text-[10.5px] sm:text-[11px] leading-tight truncate" title="Jl. Soekarno-Hatta No. 09 Dompu, NTB">
                                Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211
                            </p>
                        </div>
                    </div>

                    <!-- Step Guidance -->
                    <div class="space-y-1.5 pt-0.5 text-xs text-slate-600 dark:text-slate-300">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500 shrink-0"></span>
                            <span class="text-[11px] sm:text-xs">Segarkan halaman browser Anda dalam 1-3 menit.</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Responsive Full-Width on Mobile, Inline on Desktop) -->
                <div class="flex flex-col xs:flex-row flex-wrap items-stretch xs:items-center justify-center lg:justify-start gap-2.5 sm:gap-3.5 pt-1 sm:pt-2">
                    <!-- Primary Button: Kembali ke Beranda / Dashboard Admin -->
                    <a href="{{ $backUrl }}" class="w-full xs:w-auto inline-flex items-center justify-center gap-2 sm:gap-2.5 px-5 sm:px-6 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 text-white font-bold text-xs sm:text-sm shadow-lg shadow-primary-600/25 hover:shadow-primary-600/40 active:scale-95 transition-all duration-200">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Kembali ke {{ $backLabel }}</span>
                    </a>

                    <!-- Secondary Button: Muat Ulang Halaman -->
                    <button onclick="window.location.reload()" class="w-full xs:w-auto inline-flex items-center justify-center gap-2 sm:gap-2.5 px-4 sm:px-5 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs sm:text-sm shadow-sm active:scale-95 transition-all duration-200">
                        <svg class="w-4 h-4 text-primary-600 dark:text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Muat Ulang Halaman</span>
                    </button>
                </div>

                <!-- Status Metadata Card (Grid 2 cols on mobile, 4 cols on tablet/desktop) -->
                <div class="pt-3 sm:pt-4 border-t border-slate-200/80 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 text-left max-w-xl mx-auto lg:mx-0">
                    <div class="p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-slate-100/70 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                        <span class="block text-[9px] sm:text-[10px] text-slate-400 dark:text-slate-400 uppercase font-semibold">Status Code</span>
                        <span class="text-xs font-mono font-bold text-rose-600 dark:text-rose-400">@yield('code', '500')</span>
                    </div>
                    <div class="p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-slate-100/70 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                        <span class="block text-[9px] sm:text-[10px] text-slate-400 dark:text-slate-400 uppercase font-semibold">Request ID</span>
                        <span class="text-[9.5px] sm:text-[10px] font-mono font-bold text-primary-700 dark:text-sky-300 truncate block" title="REQ-{{ date('Ymd') }}-{{ strtoupper(substr(md5(microtime()), 0, 6)) }}">
                            REQ-{{ date('Ymd') }}-{{ strtoupper(substr(md5(microtime()), 0, 6)) }}
                        </span>
                    </div>
                    <div class="p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-slate-100/70 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                        <span class="block text-[9px] sm:text-[10px] text-slate-400 dark:text-slate-400 uppercase font-semibold">Waktu Error</span>
                        <span class="text-[9.5px] sm:text-[10px] font-mono font-bold text-slate-700 dark:text-slate-200">
                            {{ date('d/m/Y H:i') }} WITA
                        </span>
                    </div>
                    <div class="p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-slate-100/70 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                        <span class="block text-[9px] sm:text-[10px] text-slate-400 dark:text-slate-400 uppercase font-semibold">Environment</span>
                        <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            Production
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer Section (Responsive Flex Layout) -->
    <footer class="w-full z-20 py-3 sm:py-4 px-3 sm:px-6 bg-white/90 dark:bg-slate-900/90 border-t border-slate-200/80 dark:border-slate-800 backdrop-blur-md">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">
                &copy; {{ date('Y') }} Website Resmi <strong class="text-slate-800 dark:text-slate-200">Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu</strong>.
            </p>
            <div class="flex items-center gap-2 text-[11px] sm:text-xs font-bold text-primary-700 dark:text-sky-300 bg-primary-50 dark:bg-primary-950/60 px-3 py-1 rounded-full border border-primary-200 dark:border-primary-800 shrink-0">
                <span>"Dukcapil Prima, Dompu Maju"</span>
            </div>
        </div>
    </footer>

</body>
</html>
