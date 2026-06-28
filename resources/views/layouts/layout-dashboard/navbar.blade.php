{{-- Homepage --}}
<div class="mb-6">
    <a href="{{ url('/') }}"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group hover:bg-slate-50">
        <x-lucide-home class="w-5 h-5 text-slate-400 group-hover:text-slate-600 transition-colors" />
        <span class="text-sm font-medium text-slate-500 group-hover:text-slate-900 transition-colors">Homepage</span>
    </a>
</div>

{{-- Main Menu --}}
@can('dashboard.view')
    <div class="space-y-1 mb-6">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Main Menu</p>

        <a href="{{ url('/dashboard') }}"
            class="{{ request()->is('dashboard') ? 'bg-slate-100/80 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group">
            <x-lucide-layout-dashboard
                class="w-5 h-5 {{ request()->is('dashboard') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
            <span class="text-sm {{ request()->is('dashboard') ? 'font-bold' : 'font-medium' }}">Dashboard</span>
        </a>
    </div>
@endcan

{{-- Management Sections --}}
@canany(['users.view', 'roles.view', 'permissions.view', 'clubs.view'])
    <div class="space-y-1 mb-6">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Akses & Institusi</p>

        @canany(['users.view', 'roles.view', 'permissions.view'])
            <div x-data="{ open: {{ request()->is('*/management-*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="flex items-center justify-between w-full px-4 py-3 text-slate-500 rounded-xl transition-all duration-200 group hover:bg-slate-50"
                    x-bind:class="open ? 'text-slate-900' : ''">
                    <div class="flex items-center gap-3">
                        <x-lucide-shield-check class="w-5 h-5 text-slate-400 group-hover:text-slate-600" />
                        <span class="text-sm font-medium">Manajemen Akses</span>
                    </div>
                    <x-lucide-chevron-down class="w-4 h-4 text-slate-300 transition-transform duration-200"
                        x-bind:class="open ? 'rotate-180 text-slate-600' : ''" />
                </button>

                <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l border-slate-100 pl-4">
                    @can('users.view')
                        <a href="{{ url('/dashboard/management-user') }}"
                            class="{{ request()->is('*/management-user*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                            Pengguna
                        </a>
                    @endcan
                    @can('roles.view')
                        <a href="{{ url('/dashboard/management-role') }}"
                            class="{{ request()->is('*/management-role*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                            Role
                        </a>
                    @endcan
                    @can('permissions.view')
                        <a href="{{ url('/dashboard/management-permission') }}"
                            class="{{ request()->is('*/management-permission*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-blue-600' }} block py-2 text-xs transition">
                            Permission
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        @can('clubs.view')
            <a href="{{ url('/dashboard/management-club') }}"
                class="{{ request()->is('*/management-club*') ? 'bg-slate-100/80 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group">
                <x-lucide-building-2
                    class="w-5 h-5 {{ request()->is('*/management-club*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                <span class="text-sm {{ request()->is('*/management-club*') ? 'font-bold' : 'font-medium' }}">Manajemen
                    Klub</span>
            </a>
        @endcan
    </div>
@endcanany

{{-- Master Data --}}
@canany(['master-galeri.view', 'master-keuangan.view', 'master-gaya.view', 'master-parameter.view', 'master-event.view',
    'master-lomba.view', 'master-pendaftaran.view', 'master-pendaftaran.view.self', 'master-result.view', 'master-result.detail.self', 'master-result.detail.team', 'master-history-pendaftaran.view',
    'master-history-pendaftaran.view.self'])
    <div class="space-y-1 mb-6">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Master Data & Event</p>

        <div x-data="{ open: {{ request()->is('*/master/*') || request()->is('*/history-pendaftaran') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="flex items-center justify-between w-full px-4 py-3 text-slate-500 rounded-xl transition-all duration-200 group hover:bg-slate-50"
                x-bind:class="open ? 'text-slate-900' : ''">
                <div class="flex items-center gap-3">
                    <x-lucide-database class="w-5 h-5 text-slate-400 group-hover:text-slate-600" />
                    <span class="text-sm font-medium">Event & Lomba</span>
                </div>
                <x-lucide-chevron-down class="w-4 h-4 text-slate-300 transition-transform duration-200"
                    x-bind:class="open ? 'rotate-180 text-slate-600' : ''" />
            </button>

            <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l border-slate-100 pl-4">
                @can('master-galeri.view')
                    <a href="{{ route('master.gallery') }}"
                        class="{{ request()->routeIs('master.gallery*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Galeri
                    </a>
                @endcan
                @can('master-keuangan.view')
                    <a href="{{ route('master.finance') }}"
                        class="{{ request()->routeIs('master.finance*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Keuangan
                    </a>
                @endcan
                @can('master-gaya.view')
                    <a href="{{ route('master.style') }}"
                        class="{{ request()->routeIs('master.style*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Gaya Renang
                    </a>
                @endcan
                @can('master-parameter.view')
                    <a href="{{ route('master.parameter') }}"
                        class="{{ request()->is('*/master/parameter*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Parameter
                    </a>
                @endcan
                @can('master-event.view')
                    <a href="{{ route('master.event') }}"
                        class="{{ request()->is('*/master/event*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Event
                    </a>
                @endcan
                @can('master-lomba.view')
                    <a href="{{ route('master.lomba') }}"
                        class="{{ request()->is('*/master/lomba*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Nomor Lomba
                    </a>
                @endcan
                @if (auth()->user()->can('master-pendaftaran.view') || auth()->user()->can('master-pendaftaran.view.self'))
                    <a href="{{ route('master.pendaftaran') }}"
                        class="{{ request()->is('*/master/pendaftaran*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        {{ auth()->user()->can('master-pendaftaran.view') ? 'Pendaftaran' : 'Daftar Lomba' }}
                    </a>
                @endif
                @if (auth()->user()->can('master-result.view') || auth()->user()->can('master-result.detail.self') || auth()->user()->can('master-result.detail.team'))
                    <a href="{{ url('/dashboard/result-event') }}"
                        class="{{ request()->is('*/result-event*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Hasil Pertandingan
                    </a>
                @endif
                @canany(['master-history-pendaftaran.view', 'master-history-pendaftaran.view.self', 'master-history-pendaftaran.view.all'])
                    <a href="{{ url('/dashboard/history-pendaftaran') }}"
                        class="{{ request()->is('*/history-pendaftaran*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Histori Pendaftaran
                    </a>
                @endcanany
            </div>
        </div>
    </div>
@endcanany

{{-- Report & Export --}}
@can('report-data.view')
    <div class="space-y-1 mb-6">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Analisis & Laporan</p>

        <a href="{{ route('report.index') }}"
            class="{{ request()->routeIs('report.index') ? 'bg-slate-100/80 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group">
            <x-lucide-file-bar-chart
                class="w-5 h-5 {{ request()->routeIs('report.index') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
            <span class="text-sm {{ request()->routeIs('report.index') ? 'font-bold' : 'font-medium' }}">Laporan &
                Export</span>
        </a>
    </div>
@endcan

{{-- Settings --}}
@canany(['setting-document.view'])
    <div class="space-y-1 mb-6">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Pengaturan</p>

        <div x-data="{ open: {{ request()->is('*/setting/*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="flex items-center justify-between w-full px-4 py-3 text-slate-500 rounded-xl transition-all duration-200 group hover:bg-slate-50"
                x-bind:class="open ? 'text-slate-900' : ''">
                <div class="flex items-center gap-3">
                    <x-lucide-settings class="w-5 h-5 text-slate-400 group-hover:text-slate-600" />
                    <span class="text-sm font-medium">Setting</span>
                </div>
                <x-lucide-chevron-down class="w-4 h-4 text-slate-300 transition-transform duration-200"
                    x-bind:class="open ? 'rotate-180 text-slate-600' : ''" />
            </button>

            <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1 border-l border-slate-100 pl-4">
                @can('setting-document.view')
                    <a href="{{ route('setting.document') }}"
                        class="{{ request()->routeIs('setting.document*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} block py-2 text-xs transition">
                        Document Format
                    </a>
                @endcan
            </div>
        </div>
    </div>
@endcanany

{{-- Account --}}
@canany(['notifications.view', 'my-profile.view'])
    <div class="space-y-1">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Personal</p>

        @can('notifications.view')
            <a href="{{ url('/dashboard/notifications') }}"
                class="{{ request()->is('*/notifications') ? 'bg-slate-100/80 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group">
                <x-lucide-bell
                    class="w-5 h-5 {{ request()->is('*/notifications') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                <span class="text-sm {{ request()->is('*/notifications') ? 'font-bold' : 'font-medium' }}">Notifikasi</span>
            </a>
        @endcan

        @can('my-profile.view')
            <a href="{{ url('/dashboard/my-profile') }}"
                class="{{ request()->is('*/my-profile') ? 'bg-slate-100/80 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group">
                <x-lucide-user-circle
                    class="w-5 h-5 {{ request()->is('*/my-profile') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                <span class="text-sm {{ request()->is('*/my-profile') ? 'font-bold' : 'font-medium' }}">Profil Saya</span>
            </a>
        @endcan
    </div>
@endcanany
