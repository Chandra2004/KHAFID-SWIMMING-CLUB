@extends('layouts.layout-dashboard.app')

@section('dashboard-section')
    <div class="p-4 md:p-8">
        <div class="mb-8">
            <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight italic">Riwayat Pendaftaran</h2>
            <p class="text-sm text-slate-500 font-medium">Pantau status pendaftaran dan pembayaran event yang Anda ikuti.</p>
        </div>

        <div class="grid grid-cols-1 gap-6">
            @forelse ($registrations as $reg)
                <div
                    class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 overflow-hidden group">
                    <div class="flex flex-col md:flex-row">
                        <!-- Event Banner Preview -->
                        <div class="md:w-64 h-48 md:h-auto relative overflow-hidden">
                            <img src="{{ url('/file/banner-event/' . $reg['event']['banner_event']) }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                alt="{{ $reg['event']['nama_event'] }}">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-4">
                                <span
                                    class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase rounded-full border border-white/30 tracking-widest">
                                    {{ $reg['event']['category']['nama_kategori'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Registration Details -->
                        <div class="flex-1 p-6 md:p-8 flex flex-col justify-between">
                            <div>
                                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-ksc-blue/10 group-hover:text-ksc-blue transition-colors">
                                            <i data-lucide="hash" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">
                                                Nomor Registrasi</p>
                                            <p class="text-sm font-black text-slate-900 tracking-tight">
                                                {{ $reg['nomor_pendaftaran'] }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @php
                                            $statusLabel = match ($reg['status']) {
                                                'menunggu' => [
                                                    'Kuning',
                                                    'text-amber-600 bg-amber-50 border-amber-100',
                                                    'clock',
                                                ],
                                                'diterima' => [
                                                    'Diterima',
                                                    'text-emerald-600 bg-emerald-50 border-emerald-100',
                                                    'check-circle',
                                                ],
                                                'ditolak' => [
                                                    'Ditolak',
                                                    'text-rose-600 bg-rose-50 border-rose-100',
                                                    'x-circle',
                                                ],
                                                default => [
                                                    'Unknown',
                                                    'text-slate-400 bg-slate-50 border-slate-100',
                                                    'help-circle',
                                                ],
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border {{ $statusLabel[1] }}">
                                            <i data-lucide="{{ $statusLabel[2] }}" class="w-3 h-3"></i>
                                            {{ $reg['status'] === 'menunggu' ? 'Sedang Diproses' : $statusLabel[0] }}
                                        </span>
                                    </div>
                                </div>

                                <h3
                                    class="text-xl md:text-2xl font-black text-slate-900 leading-tight mb-2 group-hover:text-ksc-blue transition-colors">
                                    {{ $reg['event']['nama_event'] }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-slate-500 mb-6">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-4 h-4 text-ksc-blue"></i>
                                        <span
                                            class="text-xs font-bold">{{ \Carbon\Carbon::parse($reg['event']['tanggal_event'])->translatedFormat('d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-ksc-blue"></i>
                                        <span
                                            class="text-xs font-bold line-clamp-1">{{ $reg['event']['lokasi_event'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Details (Payment & Date) -->
                            <div class="pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    @if ($reg['event']['tipe_event'] === 'berbayar' && $reg['payment'])
                                        <div>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                                Status Pembayaran</p>
                                            @php
                                                $payStatus = match ($reg['payment']['status_pembayaran']) {
                                                    'menunggu' => ['Pending', 'text-amber-500'],
                                                    'diterima' => ['Lunas', 'text-emerald-500'],
                                                    'ditolak' => ['Gagal', 'text-rose-500'],
                                                    default => ['-', 'text-slate-400'],
                                                };
                                            @endphp
                                            <div class="flex items-center gap-1.5">
                                                <div
                                                    class="w-1.5 h-1.5 rounded-full {{ $payStatus[1] === 'text-amber-500' ? 'bg-amber-500' : ($payStatus[1] === 'text-emerald-500' ? 'bg-emerald-500' : 'bg-rose-500') }}">
                                                </div>
                                                <span
                                                    class="text-xs font-black uppercase {{ $payStatus[1] }}">{{ $payStatus[0] }}</span>
                                            </div>
                                        </div>
                                        <div class="w-px h-8 bg-slate-100"></div>
                                        <div>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                                Total Biaya</p>
                                            <p class="text-xs font-black text-slate-900 tracking-tight">
                                                {{ rupiah($reg['event']['biaya_event']) }}</p>
                                        </div>
                                    @else
                                        <div>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                                Tipe Event</p>
                                            <span class="text-xs font-black uppercase text-emerald-500">GRATIS</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Mendaftar
                                        Pada</p>
                                    <p class="text-xs font-bold text-slate-600 italic">
                                        {{ \Carbon\Carbon::parse($reg['tanggal_registrasi'])->translatedFormat('d M Y, H:i') }}
                                        WIB
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div
                            class="bg-slate-50/50 p-4 md:w-20 flex items-center justify-center border-t md:border-t-0 md:border-l border-slate-100 overflow-hidden">
                            <a href="{{ url('/detail-event/' . $reg['event']['slug'] . '/' . $reg['event']['uid']) }}"
                                class="w-12 h-12 bg-white text-slate-900 rounded-2xl flex items-center justify-center shadow-sm hover:bg-ksc-blue hover:text-white transition-all transform active:scale-90 group/btn">
                                <i data-lucide="arrow-right"
                                    class="w-6 h-6 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="bg-white rounded-[3rem] border border-slate-100 p-12 text-center shadow-sm overflow-hidden relative">
                    <div
                        class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-slate-50 rounded-full -translate-y-1/2 blur-3xl opacity-50">
                    </div>
                    <div class="relative">
                        <div
                            class="w-24 h-24 bg-slate-100 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-slate-300">
                            <i data-lucide="clipboard-list" class="w-12 h-12"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">Belum Ada Pendaftaran
                        </h3>
                        <p class="text-slate-500 font-medium max-w-sm mx-auto mb-8">Anda belum mendaftar di event manapun.
                            Ayo temukan event seru dan bergabung sekarang!</p>
                        <a href="{{ url('/' . $user['nama_role'] . '/dashboard/event') }}"
                            class="inline-flex items-center gap-3 bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-ksc-blue transition-all active:scale-95 shadow-xl shadow-slate-200">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            Eksplorasi Event
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
