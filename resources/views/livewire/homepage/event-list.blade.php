<div>
    {{-- Search Bar --}}
    <section class="relative pt-40 pb-24 bg-slate-950 overflow-hidden">
        <div class="absolute inset-0 bg-grid opacity-10"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-gradient-to-b from-blue-600/20 to-transparent blur-3xl"></div>

        <div class="container mx-auto px-6 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold uppercase tracking-widest mb-8 animate-fade-in">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                Arena Kompetisi KSC
            </div>
            <h1 class="text-5xl md:text-7xl font-black text-white mb-8 leading-tight">
                Temukan <span class="text-blue-500 italic">Passion</span> & <br>
                Raih <span class="relative">
                    <span class="relative z-10 text-yellow-400">Podium Juara</span>
                    <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 318 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 10C55.5 4 115 1.5 174 1.5C233 1.5 292.5 4 317 10" stroke="#FACC15" stroke-width="3" stroke-linecap="round"/></svg>
                </span>
            </h1>

            {{-- Search Bar --}}
            <div class="relative max-w-3xl mx-auto group">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-[2.5rem] blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                <div class="relative flex items-center bg-slate-900 border border-slate-800 rounded-[2.5rem] p-2">
                    <div class="pl-6 pr-4">
                        <x-lucide-search class="w-6 h-6 text-slate-500" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama event atau lokasi kompetisi..."
                        class="w-full bg-transparent border-none text-white text-sm font-medium focus:ring-0 placeholder:text-slate-600 py-4">
                    <div class="pr-6" wire:loading wire:target="search">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-white/20 border-t-white"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Events Grid --}}
    <section class="py-32 bg-white relative min-h-[600px]">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-10" wire:loading.class="opacity-50 transition-opacity">
                @forelse ($events as $event)
                    <div class="event-card group bg-white rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden flex flex-col h-full ring-1 ring-slate-100">
                        {{-- Banner Area --}}
                        <a href="{{ url('/detail-event/' . $event['slug'] . '/' . $event['uid']) }}">
                            <div class="relative h-64 overflow-hidden">
                                @if ($event['banner_event'])
                                    <img src="{{ url($event['banner_event']) }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                                        <x-lucide-image class="w-12 h-12 text-slate-300" />
                                    </div>
                                @endif

                                <div class="absolute top-6 left-6 flex flex-col gap-2">
                                    <span class="px-4 py-1.5 glass-effect rounded-full text-[10px] font-black uppercase tracking-widest text-slate-900 shadow-sm">
                                        {{ $event['kategori'] }}
                                    </span>
                                    @if($event['status_event'] === 'berjalan')
                                        <span class="px-4 py-1.5 bg-emerald-500 text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/30">
                                            Open Registration
                                        </span>
                                    @elseif($event['status_event'] === 'mendatang')
                                        <span class="px-4 py-1.5 bg-blue-500 text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/30">
                                            Upcoming
                                        </span>
                                    @elseif($event['status_event'] === 'dibatalkan')
                                        <span class="px-4 py-1.5 bg-rose-500 text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-500/30">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="px-4 py-1.5 bg-slate-800 text-white rounded-full text-[10px] font-black uppercase tracking-widest">
                                            {{ $event['status_event'] }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Cost Badge --}}
                                <div class="absolute bottom-6 right-6">
                                    <div class="px-5 py-2 {{ $event['biaya_event'] > 0 ? 'bg-white' : 'bg-ksc-accent' }} rounded-2xl shadow-xl flex items-center gap-2 border border-white/50">
                                        <span class="text-[10px] font-black uppercase {{ $event['biaya_event'] > 0 ? 'text-slate-400' : 'text-slate-900' }}">
                                            {{ $event['biaya_event'] > 0 ? 'Investasi' : 'Free' }}
                                        </span>
                                        @if($event['biaya_event'] > 0)
                                            <span class="text-sm font-black text-slate-900 italic">Rp{{ number_format($event['biaya_event'], 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>

                        {{-- Content Area --}}
                        <div class="p-10 flex-grow flex flex-col">
                            <div class="flex items-center gap-3 mb-6 text-slate-400">
                                <div class="flex items-center gap-1.5">
                                    <x-lucide-calendar class="w-4 h-4 text-blue-500" />
                                    <span class="text-[11px] font-bold uppercase tracking-wider">
                                        {{ \Carbon\Carbon::parse($event['tanggal_mulai'])->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                                <div class="w-1 h-1 bg-slate-300 rounded-full"></div>
                                <div class="flex items-center gap-1.5">
                                    <x-lucide-map-pin class="w-4 h-4 text-rose-500" />
                                    <span class="text-[11px] font-bold uppercase tracking-wider truncate max-w-[120px]">
                                        {{ $event['lokasi_event'] }}
                                    </span>
                                </div>
                            </div>

                            <a href="{{ url('/detail-event/' . $event['slug'] . '/' . $event['uid']) }}">
                                <h3 class="text-2xl font-black text-slate-900 leading-tight mb-6 group-hover:text-blue-600 transition-colors line-clamp-2 italic uppercase">
                                    {{ $event['nama_event'] }}
                                </h3>
                            </a>

                            {{-- Features / Info --}}
                            <div class="flex flex-wrap gap-4 mb-10 mt-auto">
                                <div class="flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                                    <x-lucide-layout class="w-4 h-4 text-blue-500" />
                                    <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $event['jumlah_lintasan'] ?? 8 }} LINTASAN</span>
                                </div>
                                <div class="flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                                    <x-lucide-users class="w-4 h-4 text-blue-500" />
                                    <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $event['registrations_count'] }} PESERTA</span>
                                </div>
                            </div>

                            <a href="{{ url('/detail-event/' . $event['slug'] . '/' . $event['uid']) }}"
                                class="w-full hover:bg-green-500 py-5 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-3 group/btn shadow-xl shadow-slate-200 hover:shadow-blue-500/30">
                                Lihat Detail <x-lucide-arrow-right class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" />
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="lg:col-span-3 py-20 text-center">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-lucide-calendar-x class="w-10 h-10 text-slate-300" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-400 uppercase tracking-widest">Belum Ada Event Ditemukan</h3>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-24">
                {{ $events->links('vendor.livewire.tailwind') }}
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('livewire:navigated', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</div>
