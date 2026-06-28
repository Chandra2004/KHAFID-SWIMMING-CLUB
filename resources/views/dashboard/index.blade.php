@extends('layouts.layout-dashboard.app')

@section('dashboard-section')
    <div class="px-4 md:px-10 py-10 space-y-10 pb-20">
        
        <!-- HEADER SECTION: Clean & Modern -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight uppercase">
                    Control <span class="text-blue-600">Panel</span>
                </h1>
                <p class="text-xs md:text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">
                    System Overview <span class="text-slate-300 mx-2">|</span> {{ Auth::user()->username }}
                </p>
            </div>
        </div>

        <!-- MAIN DYNAMIC GRID: Unified Layout -->
        <!-- This grid will automatically fill spaces as cards are shown/hidden by permissions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

            {{-- --- FINANCE GROUP --- --}}
            @can('dashboard.view-card.finance.self')
            <!-- Card Keuangan Self -->
            <div class="bg-slate-900 p-7 rounded-5xl shadow-xl shadow-slate-900/20 relative overflow-hidden group border border-slate-800">
                <x-lucide-user-check class="absolute -right-4 -bottom-4 w-32 h-32 text-white/5 group-hover:scale-110 transition-transform" />
                <p class="text-[10px] text-blue-400 font-black uppercase tracking-widest mb-1">Pengeluaran Saya</p>
                <h3 class="text-2xl font-black text-white">IDR {{ number_format($total_self, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center gap-2 text-[10px] font-bold text-slate-400">
                    <x-lucide-info class="w-4 h-4" />
                    <span>Biaya pendaftaran Anda</span>
                </div>
            </div>
            @endcan

            @can('dashboard.view-card.finance.lomba')
            <!-- Card Keuangan Lomba -->
            <div class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                        <x-lucide-trophy class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Lomba Terlaris</p>
                <h3 class="text-2xl font-black text-slate-900">IDR {{ number_format($total_lomba, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Pendapatan kategori tertinggi</p>
            </div>
            @endcan

            @can('dashboard.view-card.finance.event')
            <!-- Card Keuangan Event -->
            <div class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center">
                        <x-lucide-calendar class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Event Terbesar</p>
                <h3 class="text-2xl font-black text-slate-900">IDR {{ number_format($total_event, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Pendapatan event tertinggi</p>
            </div>
            @endcan

            @can('dashboard.view-card.finance.all')
            <!-- Card Keuangan All -->
            <div class="bg-blue-600 p-7 rounded-5xl shadow-xl shadow-blue-600/20 relative overflow-hidden group">
                <x-lucide-wallet class="absolute -right-4 -bottom-4 w-32 h-32 text-white/5 group-hover:scale-110 transition-transform" />
                <p class="text-[10px] text-blue-100 font-black uppercase tracking-widest mb-1">Total Saldo Klub</p>
                <h3 class="text-2xl font-black text-white">IDR {{ number_format($total_all, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center gap-2 text-[10px] font-bold text-blue-100">
                    <x-lucide-banknote class="w-4 h-4" />
                    <span>Akumulasi uang masuk</span>
                </div>
            </div>
            @endcan

            {{-- --- HISTORY & MESSAGE GROUP --- --}}
            @can('dashboard.view-card.history.self')
            <!-- Card Riwayat Lomba Self -->
            <div class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4">
                    <x-lucide-history class="w-6 h-6" />
                </div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Riwayat Saya</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($history_self) }} <span class="text-sm text-slate-400 font-bold ml-1 uppercase">Selesai</span></h3>
            </div>
            @endcan

            @can('dashboard.view-card.history.all')
            <!-- Card Riwayat Lomba All -->
            <div class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                <div class="w-12 h-12 bg-slate-50 text-slate-600 rounded-2xl flex items-center justify-center mb-4">
                    <x-lucide-layers class="w-6 h-6" />
                </div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Riwayat Klub</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($history_all) }} <span class="text-sm text-slate-400 font-bold ml-1 uppercase">Selesai</span></h3>
            </div>
            @endcan

            @can('dashboard.view-card.message.self')
            <!-- Card Pesan Masuk Self -->
            <div class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-4">
                    <x-lucide-mail class="w-6 h-6" />
                </div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Pesan Saya</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($message_self) }} <span class="text-xs text-emerald-500 uppercase tracking-widest ml-2">Baru</span></h3>
            </div>
            @endcan

            @can('dashboard.view-card.message.all')
            <!-- Card Pesan Masuk All -->
            <div class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-4">
                    <x-lucide-bell class="w-6 h-6" />
                </div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Pesan Sistem</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($message_all) }} <span class="text-xs text-rose-500 uppercase tracking-widest ml-2">Baru</span></h3>
            </div>
            @endcan

            {{-- --- ACCESS MANAGEMENT GROUP --- --}}
            @can('dashboard.view-card.users')
            <!-- Card Pengguna -->
            <a href="{{ route('management-user.index') }}" class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-xl transition-all flex flex-col justify-between group">
                <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 mb-4">
                    <x-lucide-users-2 class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Database User</p>
                    <h3 class="text-2xl font-black text-slate-900">{{ number_format($total_users) }} <span class="text-xs text-slate-400 uppercase">Total</span></h3>
                </div>
            </a>
            @endcan

            @can('dashboard.view-card.roles')
            <!-- Card Role -->
            <a href="{{ route('master.role') }}" class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:border-indigo-200 hover:shadow-xl transition-all flex flex-col justify-between group">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-4">
                    <x-lucide-shield-check class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Manajemen Role</p>
                    <h3 class="text-2xl font-black text-slate-900">{{ $total_roles }} <span class="text-xs text-slate-400 uppercase">Level</span></h3>
                </div>
            </a>
            @endcan

            @can('dashboard.view-card.permissions')
            <!-- Card Permission -->
            <a href="{{ route('master.permission') }}" class="bg-white p-7 rounded-5xl border border-slate-100 shadow-sm hover:border-amber-200 hover:shadow-xl transition-all flex flex-col justify-between group">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-4">
                    <x-lucide-key class="w-7 h-7" />
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Akses Sistem</p>
                    <h3 class="text-2xl font-black text-slate-900">{{ $total_permissions }} <span class="text-xs text-slate-400 uppercase">Hak</span></h3>
                </div>
            </a>
            @endcan
        </div>

        <!-- BREAKDOWN SECTION: Dynamic Role Mapping -->
        @can('dashboard.view-card.users')
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Breakdown User per Role</h2>
                <div class="h-px flex-1 bg-slate-100"></div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @php
                    $roleMap = [
                        'superadmin' => ['icon' => 'lucide-user-cog', 'bg' => 'bg-slate-900 text-white', 'border' => 'hover:border-slate-900'],
                        'admin'      => ['icon' => 'lucide-shield-check', 'bg' => 'bg-blue-100 text-blue-600', 'border' => 'hover:border-blue-600'],
                        'pelatih'    => ['icon' => 'lucide-graduation-cap', 'bg' => 'bg-indigo-100 text-indigo-600', 'border' => 'hover:border-indigo-600'],
                        'atlet'      => ['icon' => 'lucide-user', 'bg' => 'bg-emerald-100 text-emerald-600', 'border' => 'hover:border-emerald-600'],
                        'wali'       => ['icon' => 'lucide-users', 'bg' => 'bg-rose-100 text-rose-600', 'border' => 'hover:border-rose-600'],
                    ];
                @endphp

                @foreach($roles_data as $role)
                    @php
                        $config = $roleMap[$role->name] ?? [
                            'icon' => 'lucide-user-plus',
                            'bg' => 'bg-slate-100 text-slate-600',
                            'border' => 'hover:border-slate-400'
                        ];
                    @endphp
                    <div class="bg-white p-5 rounded-4xl border border-slate-100 shadow-sm flex flex-col items-center text-center group {{ $config['border'] }} transition-all">
                        <div class="w-10 h-10 {{ $config['bg'] }} rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <x-dynamic-component :component="$config['icon']" class="w-5 h-5" />
                        </div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ $role->name }}</p>
                        <h5 class="text-lg font-black text-slate-900">{{ number_format($role->users_count) }}</h5>
                    </div>
                @endforeach
            </div>
        </div>
        @endcan

    </div>
@endsection
