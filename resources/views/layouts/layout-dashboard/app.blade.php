<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('/assets/ico/favicon.ico') }}" type="image/x-icon">
    <title>{{ $title ?? 'Dashboard' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- TinyMCE Editor --}}
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    },
                    colors: {
                        ksc: {
                            blue: '#1e40af',
                            dark: '#1e3a8a',
                            accent: '#f59e0b',
                            light: '#eff6ff',
                            slate: '#0f172a'
                        }
                    },
                    borderRadius: {
                        '4xl': '2rem',
                        '5xl': '2.5rem',
                    },
                    zIndex: {
                        '2000': '2000',
                        '2010': '2010',
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        .sidebar-item-active {
            background: linear-gradient(90deg, rgba(30, 64, 175, 0.08) 0%, rgba(30, 64, 175, 0) 100%);
            border-left: 4px solid #1e40af;
            color: #1e40af;
            font-weight: 700;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .sidebar {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Animation for content */
        .page-fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }
    </style>
    @livewireStyles
</head>

<body class="bg-[#F8FAFC] text-slate-700 font-sans antialiased overflow-x-hidden">
    @include('layouts.layout-partials.notification')

    <div class="flex min-h-screen relative overflow-hidden">
        <!-- SIDEBAR -->
        <aside id="sidebar"
            class="sidebar fixed inset-y-0 left-0 z-[40] w-72 bg-white border-r border-slate-200/60 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 shadow-2xl lg:shadow-none">
            <div class="h-full flex flex-col p-8">
                <!-- LOGO SECTION -->
                <div class="flex items-center justify-between mb-10 px-2">
                    <a href="/" class="flex justify-center w-full">
                        <img src="{{ asset('assets/ico/icon-bar.png') }}" class="h-20 w-auto" alt="Logo">
                    </a>
                    <button id="closeSidebar"
                        class="lg:hidden p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition-all">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <!-- NAVIGATION MENU -->
                <nav class="flex-1 space-y-1 overflow-y-auto custom-scrollbar pr-2">
                    @include('layouts.layout-dashboard.navbar')
                </nav>

                <!-- FOOTER SIDEBAR / LOGOUT -->
                <div class="pt-8 mt-8 border-t border-slate-100">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="flex items-center w-full gap-3 px-5 py-4 text-rose-500 hover:bg-rose-50 rounded-2xl transition-all group font-black uppercase text-[11px] tracking-widest">
                            <x-lucide-log-out class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                            <span>Keluar Sistem</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 h-screen relative overflow-hidden">

            <!-- HEADER -->
            <header
                class="h-20 flex-shrink-0 glass-effect border-b border-slate-200/60 flex items-center justify-between px-4 md:px-10 sticky top-0 z-[30]">
                <div class="flex items-center gap-3 md:gap-6">
                    <button id="toggleSidebar"
                        class="lg:hidden p-3 bg-white hover:bg-slate-50 text-slate-900 rounded-xl transition-all shadow-sm border border-slate-100">
                        <x-lucide-menu class="w-6 h-6" />
                    </button>
                    <div class="hidden sm:block">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            <h1 class="text-sm md:text-base font-black text-slate-900 tracking-tight uppercase">
                                Monitoring <span class="text-blue-600">Center</span>
                            </h1>
                        </div>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-[0.2em] leading-none">
                            Swimming Club Management System
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 md:gap-6">
                    <!-- NOTIFICATION BELL -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="h-12 w-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-blue-600 hover:shadow-xl hover:shadow-blue-500/10 transition-all relative border border-slate-100 group">
                            <x-lucide-bell class="w-5 h-5 group-hover:rotate-12 transition" />
                            @if ($totalUnreadNotifications > 0)
                                <span
                                    class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white shadow-sm"></span>
                            @endif
                        </button>

                        <!-- DROPDOWN BELL -->
                        <div x-show="open" @click.away="open = false" x-cloak
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="absolute top-full right-0 mt-4 w-80 md:w-96 bg-white rounded-3xl shadow-2xl border border-slate-100 z-50 overflow-hidden ring-1 ring-slate-900/5">

                            <div
                                class="px-6 py-5 border-b border-slate-50 bg-slate-50/30 flex justify-between items-center">
                                <div>
                                    <h3 class="font-black text-slate-900 text-xs uppercase tracking-widest">
                                        Pemberitahuan</h3>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Informasi Aktivitas
                                        Terbaru</p>
                                </div>
                                <span
                                    class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black">{{ $totalUnreadNotifications }}
                                    Baru</span>
                            </div>

                            <div class="max-h-[450px] overflow-y-auto custom-scrollbar">
                                @forelse($unreadNotifications as $notification)
                                    <div
                                        class="group flex items-start gap-4 p-5 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0">
                                        <div
                                            class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex-shrink-0 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-sm shadow-blue-100">
                                            <x-lucide-bell-ring class="w-5 h-5" />
                                        </div>
                                        <div class="flex-grow">
                                            <p class="text-sm text-slate-900 font-black leading-tight mb-1">
                                                {{ $notification->title }}</p>
                                            <div class="text-[11px] text-slate-500 leading-relaxed line-clamp-2">
                                                {!! $notification->message !!}
                                            </div>
                                            <p
                                                class="text-[9px] text-slate-300 font-bold uppercase mt-2 tracking-widest">
                                                {{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-20 px-8 text-center bg-slate-50/20">
                                        <div
                                            class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-slate-100">
                                            <x-lucide-bell-off class="w-8 h-8 text-slate-200" />
                                        </div>
                                        <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest">Semua
                                            Sunyi</h4>
                                        <p class="text-[10px] text-slate-300 font-bold mt-1">Belum ada pemberitahuan
                                            baru untuk Anda</p>
                                    </div>
                                @endforelse
                            </div>

                            <a href="{{ url('/dashboard/notifications') }}"
                                class="block py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 hover:text-slate-900 transition border-t border-slate-50">
                                Lihat Semua Aktivitas
                            </a>
                        </div>
                    </div>

                    <!-- USER PROFILE DROPDOWN -->
                    <div x-data="{ open: false }" class="relative">
                        <div @click="open = !open"
                            class="flex items-center gap-3 pl-3 md:pl-6 border-l border-slate-200 cursor-pointer group">
                            <div class="text-right hidden md:block">
                                <p
                                    class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition tracking-tighter">
                                    {{ $user->username }}</p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                                    {{ $user->getRoleNames()->first() }}
                                </p>
                            </div>
                            <div class="relative">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=1e293b&color=fff&bold=true"
                                    class="h-11 w-11 rounded-2xl border-2 border-transparent group-hover:border-blue-600 transition-all shadow-lg shadow-slate-200"
                                    alt="Avatar">
                                <span
                                    class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2 border-white"></span>
                            </div>
                        </div>

                        <!-- DROPDOWN PROFIL -->
                        <div x-show="open" @click.away="open = false" x-cloak
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="absolute top-full right-0 mt-4 w-64 bg-white rounded-3xl shadow-2xl border border-slate-100 z-50 overflow-hidden ring-1 ring-slate-900/5">

                            <div class="px-6 py-6 border-b border-slate-50 bg-slate-50/30">
                                <p class="text-xs font-black text-slate-900 uppercase tracking-widest truncate">
                                    {{ $user->nama_lengkap }}</p>
                                <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-tighter">
                                    {{ $user->email }}</p>
                            </div>

                            <div class="p-2">
                                <a href="{{ url('/dashboard/my-profile') }}"
                                    class="flex items-center gap-3 px-4 py-3.5 text-[11px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:text-blue-600 rounded-2xl transition-all">
                                    <x-lucide-user class="w-4 h-4" />
                                    <span>Detail Profil</span>
                                </a>

                                <a href="{{ url('/dashboard/settings') }}"
                                    class="flex items-center gap-3 px-4 py-3.5 text-[11px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:text-blue-600 rounded-2xl transition-all">
                                    <x-lucide-settings class="w-4 h-4" />
                                    <span>Pengaturan</span>
                                </a>
                            </div>

                            <div class="h-px bg-slate-50 mx-2"></div>

                            <div class="p-2">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center w-full gap-3 px-4 py-3.5 text-[11px] font-black uppercase tracking-widest text-rose-500 hover:bg-rose-50 rounded-2xl transition-all">
                                        <x-lucide-power class="w-4 h-4" />
                                        <span>Keluar Sesi</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN SECTION AREA -->
            <section class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/50 page-fade-in relative">
                <div class="max-w-[1920px] mx-auto">
                    @yield('dashboard-section')
                </div>
            </section>
        </div>
    </div>

    <!-- MOBILE BACKDROP -->
    <div id="sidebarBackdrop"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[35] hidden lg:hidden transition-opacity duration-500 opacity-0">
    </div>

    <script>
        // Enhanced Sidebar Logic
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            backdrop.classList.remove('hidden');
            setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
            sidebar.classList.remove('-translate-x-full');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => backdrop.classList.add('hidden'), 500);
            document.body.style.overflow = '';
        }

        toggleBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        backdrop?.addEventListener('click', closeSidebar);

        // Auto-close on desktop resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024 && !sidebar.classList.contains('-translate-x-full')) {
                closeSidebar();
            }
        });

        // Close on navigation (for SPAs or mobile nav clicks)
        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) closeSidebar();
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    @livewireScripts
</body>

</html>
