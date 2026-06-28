@extends('layouts.layout-dashboard.app')

@section('dashboard-section')
    <div class="container mx-auto px-4 py-8">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 italic uppercase">Eksplorasi Event</h1>
                <p class="text-slate-500 text-sm mt-1">Temukan dan ikuti berbagai kegiatan menarik di KSC.</p>
            </div>

            {{-- Filter Section --}}
            <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                <span class="text-[10px] font-black uppercase text-slate-400 ml-3">Status:</span>
                <select id="statusFilter"
                    class="text-xs font-bold border-none bg-transparent focus:ring-0 cursor-pointer text-slate-700">
                    <option value="all">Semua Status</option>
                    <option value="berjalan">Berjalan</option>
                    <option value="ditunda">Ditunda</option>
                    <option value="ditutup">Ditutup</option>
                </select>
            </div>
        </div>

        {{-- Grid Events --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="eventGrid">
            @foreach ($events['data'] as $event)
                @if ($event['status_event'] === 'berjalan')
                    @if ($event['tipe_event'] === 'berbayar')
                        <div class="event-card group bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-slate-200 transition-all duration-300 overflow-hidden flex flex-col"
                            data-status="berjalan">
                            <div class="relative h-48 overflow-hidden">
                                @if ($event->banner_event == null)
                                        <img src="https://lh5.googleusercontent.com/proxy/t08n2HuxPfw8OpbutGWjekHAgxfPFv-pZZ5_-uTfhEGK8B5Lp-VN4VjrdxKtr8acgJA93S14m9NdELzjafFfy13b68pQ7zzDiAmn4Xg8LvsTw1jogn_7wStYeOx7ojx5h63Gliw"
                                            class="w-full h-full object-cover">
                                    @else
                                        <img src="{{ url('/file/banner-event/' . $event['banner_event']) }}"
                                            class="w-full h-full object-cover">
                                    @endif
                                <div class="absolute top-4 left-4 flex gap-2">
                                    <span
                                        class="px-3 py-1 bg-ksc-accent text-slate-900 text-[10px] font-black uppercase rounded-full shadow-lg">Berbayar</span>
                                    <span
                                        class="px-3 py-1 bg-emerald-500 text-white text-[10px] font-black uppercase rounded-full shadow-lg">Berjalan</span>
                                </div>
                            </div>

                            <div class="p-6 flex-grow">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="text-[10px] font-bold text-ksc-blue uppercase tracking-widest">
                                        {{ $event['category']['nama_kategori'] }}</p>
                                    <div class="flex items-center gap-1 text-slate-500">
                                        <i data-lucide="clock" class="w-3 h-3 text-ksc-blue"></i>
                                        <span
                                            class="text-[10px] font-black">{{ \Carbon\Carbon::parse($event['waktu_event'])->translatedFormat('H:m') }}
                                            WIB</span>
                                    </div>
                                </div>
                                <h3
                                    class="text-xl font-black text-slate-900 leading-tight mb-4 group-hover:text-ksc-blue transition-colors line-clamp-2">
                                    {{ $event['nama_event'] }}</h3>

                                <div class="mb-6 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                    <div class="flex justify-between text-[10px] font-black uppercase mb-1.5">
                                        <span class="text-slate-400">Kuota Peserta</span>
                                        <span
                                            class="text-ksc-blue">{{ $event['registrations_count'] }}/{{ $event['kuota_peserta'] }}
                                            Tersedia</span>
                                    </div>
                                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-ksc-blue h-full rounded-full"
                                            style="width: {{ ($event['registrations_count'] / $event['kuota_peserta']) * 100 }}%">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                    <div>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase italic">Investasi</p>
                                        <p class="text-lg font-black text-slate-900">{{ rupiah($event['biaya_event']) }}</p>
                                    </div>
                                    <a href="{{ url('/detail-event/' . $event['slug'] . '/' . $event['uid']) }}"
                                        class="w-12 h-12 bg-slate-900 text-white rounded-2xl flex items-center justify-center group-hover:bg-ksc-blue transition-all active:scale-90">
                                        <i data-lucide="arrow-right" class="w-6 h-6"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="event-card group bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-slate-200 transition-all duration-300 overflow-hidden flex flex-col"
                            data-status="berjalan">
                            <div class="relative h-48 overflow-hidden">
                                <img src="{{ url('/file/banner-event/' . $event['banner_event']) }}"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute top-4 left-4 flex gap-2">
                                    <span
                                        class="px-3 py-1 bg-ksc-accent text-slate-900 text-[10px] font-black uppercase rounded-full shadow-lg">Gratis</span>
                                    <span
                                        class="px-3 py-1 bg-emerald-500 text-white text-[10px] font-black uppercase rounded-full shadow-lg">Berjalan</span>
                                </div>
                            </div>

                            <div class="p-6 flex-grow">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="text-[10px] font-bold text-ksc-blue uppercase tracking-widest">
                                        {{ $event['category']['nama_kategori'] }}</p>
                                    <div class="flex items-center gap-1 text-slate-500">
                                        <i data-lucide="clock" class="w-3 h-3 text-ksc-blue"></i>
                                        <span
                                            class="text-[10px] font-black">{{ \Carbon\Carbon::parse($event['waktu_event'])->translatedFormat('H:m') }}
                                            WIB</span>
                                    </div>
                                </div>
                                <h3
                                    class="text-xl font-black text-slate-900 leading-tight mb-4 group-hover:text-ksc-blue transition-colors line-clamp-2">
                                    {{ $event['nama_event'] }}</h3>

                                <div class="mb-6 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                    <div class="flex justify-between text-[10px] font-black uppercase mb-1.5">
                                        <span class="text-slate-400">Kuota Peserta</span>
                                        <span
                                            class="text-ksc-blue">{{ $event['registrations_count'] }}/{{ $event['kuota_peserta'] }}
                                            Tersedia</span>
                                    </div>
                                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-ksc-blue h-full rounded-full"
                                            style="width: {{ ($event['registrations_count'] / $event['kuota_peserta']) * 100 }}%">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                    <div>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase italic">Investasi</p>
                                        <p class="text-lg font-black text-green-500">GRATIS</p>
                                    </div>
                                    <a href="{{ url('/detail-event/' . $event['slug'] . '/' . $event['uid']) }}"
                                        class="w-12 h-12 bg-slate-900 text-white rounded-2xl flex items-center justify-center group-hover:bg-ksc-blue transition-all active:scale-90">
                                        <i data-lucide="arrow-right" class="w-6 h-6"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                @if ($event['status_event'] === 'ditunda')
                    <div class="event-card group bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col"
                        data-status="ditunda">
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ url('/file/banner-event/' . $event['banner_event']) }}"
                                class="w-full h-full object-cover grayscale opacity-50">
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span
                                    class="px-3 py-1 bg-white text-slate-900 text-[10px] font-black uppercase rounded-full shadow-sm">{{ $event['tipe_event'] }}</span>
                                <span
                                    class="px-3 py-1 bg-orange-500 text-white text-[10px] font-black uppercase rounded-full shadow-lg">Ditunda</span>
                            </div>
                        </div>

                        <div class="p-6 flex-grow bg-slate-50/30">
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    {{ $event['category']['nama_kategori'] }}</p>
                                <div class="flex items-center gap-1 text-slate-400">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <span
                                        class="text-[10px] font-black">{{ \Carbon\Carbon::parse($event['waktu_event'])->translatedFormat('H:m') }}
                                        WIB</span>
                                </div>
                            </div>
                            <h3 class="text-xl font-black text-slate-400 leading-tight mb-4 line-clamp-2">
                                {{ $event['nama_event'] }}
                            </h3>

                            <div class="mb-6 opacity-40">
                                <div class="flex justify-between text-[10px] font-black uppercase mb-1.5 text-slate-400">
                                    <span>Kuota Peserta</span>
                                    <span>{{ $event['registrations_count'] }}/{{ $event['kuota_peserta'] }}
                                        Tersedia</span>
                                </div>
                                <div class="mb-6 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                    <div
                                        class="flex justify-between text-[10px] font-black uppercase mb-1.5 text-slate-600">
                                        <span>Status Kuota</span>
                                        <span>Ditunda</span>
                                    </div>
                                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-slate-500 h-full rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                                <span class="text-[10px] italic font-black text-orange-600 uppercase">Menunggu Jadwal</span>
                                <button disabled
                                    class="w-12 h-12 bg-slate-100 text-slate-300 rounded-2xl flex items-center justify-center cursor-not-allowed">
                                    <i data-lucide="lock" class="w-6 h-6"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($event['status_event'] === 'ditutup')
                    <div class="event-card group bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col"
                        data-status="ditutup">
                        <div class="relative h-48 overflow-hidden grayscale opacity-75">
                            <img src="{{ url('/file/banner-event/' . $event['banner_event']) }}"
                                class="w-full h-full object-cover">
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span
                                    class="px-3 py-1 bg-white text-slate-900 text-[10px] font-black uppercase rounded-full">{{ $event['tipe_event'] }}</span>
                                <span
                                    class="px-3 py-1 bg-slate-800 text-white text-[10px] font-black uppercase rounded-full">Ditutup</span>
                            </div>
                        </div>

                        <div class="p-6 flex-grow">
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    {{ $event['category']['nama_kategori'] }}</p>
                                <div class="flex items-center gap-1 text-slate-400">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <span class="text-[10px] font-black">Selesai</span>
                                </div>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 leading-tight mb-4 line-clamp-2">
                                {{ $event['nama_event'] }}</h3>

                            <div class="mb-6 bg-red-50 p-3 rounded-2xl border border-red-100">
                                <div class="flex justify-between text-[10px] font-black uppercase mb-1.5 text-red-600">
                                    <span>Status Kuota</span>
                                    <span>Penuh</span>
                                </div>
                                <div class="w-full bg-red-200 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-red-500 h-full rounded-full" style="width: 100%"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                <span class="text-[10px] font-black text-red-500 italic uppercase">Pendaftaran
                                    Berakhir</span>
                                <div
                                    class="w-12 h-12 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center">
                                    <i data-lucide="ban" class="w-6 h-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4 px-2 pb-10">
            <div class="text-left">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                    Halaman <span class="text-slate-900">{{ $events['current_page'] }}</span>
                    dari <span class="text-slate-900">{{ $events['last_page'] }}</span>
                    — Total <span class="text-ksc-blue">{{ $events['total'] }}</span> Event
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if ($events['current_page'] > 1)
                    <a href="{{ url('/' . $user['nama_role'] . '/dashboard/event/page/' . ($events['current_page'] - 1)) }}"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm active:scale-95">
                        <i data-lucide="chevron-left" class="w-4 h-4 text-slate-400"></i>
                        <span>Prev</span>
                    </a>
                @else
                    <div
                        class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-100 text-slate-300 rounded-xl font-bold text-xs cursor-not-allowed opacity-60">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        <span>Prev</span>
                    </div>
                @endif

                <div class="flex items-center bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-sm">
                    <span class="text-xs font-black text-slate-900">{{ $events['current_page'] }}</span>
                </div>

                @if ($events['current_page'] < $events['last_page'])
                    <a href="{{ url('/' . $user['nama_role'] . '/dashboard/event/page/' . ($events['current_page'] + 1)) }}"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm active:scale-95">
                        <span>Next</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                    </a>
                @else
                    <div
                        class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-100 text-slate-300 rounded-xl font-bold text-xs cursor-not-allowed opacity-60">
                        <span>Next</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusFilter = document.getElementById('statusFilter');
            const cards = document.querySelectorAll('.event-card');

            statusFilter.addEventListener('change', (e) => {
                const selectedStatus = e.target.value;

                cards.forEach(card => {
                    const cardStatus = card.getAttribute('data-status');

                    if (selectedStatus === 'all' || cardStatus === selectedStatus) {
                        card.style.display = 'flex';
                        // Animasi masuk simpel
                        card.classList.add('animate-in', 'fade-in', 'zoom-in', 'duration-300');
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection
