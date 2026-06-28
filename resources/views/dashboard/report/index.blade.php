@extends('layouts.layout-dashboard.app')

@section('dashboard-section')
    @can('report-data.view')
        <div class="p-4 md:p-8 overflow-y-auto">
            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 leading-tight uppercase tracking-tight">Pusat Laporan &
                        Ekspor
                    </h2>
                    <p class="text-sm text-slate-500 font-medium">Analisis data pertumbuhan klub dan performa event swimming
                        club
                    </p>
                </div>
                <div class="flex items-center gap-3">
                </div>
            </div>

            {{-- Statistical Summary (Primary) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-100/50 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50/50 rounded-full -mr-12 -mt-12 transition-transform group-hover:scale-125"></div>
                    <div class="relative">
                        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-ksc-blue mb-4 shadow-inner">
                            <x-lucide-users class="w-6 h-6" />
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Anggota</p>
                        <h4 class="text-3xl font-black text-slate-900 tracking-tighter">{{ number_format($totalAnggota) }}</h4>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-50 flex items-center gap-2 text-[10px] font-bold text-slate-400">
                        <span class="text-blue-600">Database</span> Terdaftar
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-100/50 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50/50 rounded-full -mr-12 -mt-12 transition-transform group-hover:scale-125"></div>
                    <div class="relative">
                        <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 mb-4 shadow-inner">
                            <x-lucide-trophy class="w-6 h-6" />
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Event</p>
                        <h4 class="text-3xl font-black text-slate-900 tracking-tighter">{{ number_format($totalEvent) }}</h4>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-50 flex items-center gap-2 text-[10px] font-bold text-slate-400">
                        Kejuaraan Terselenggara
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-100/50 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50/50 rounded-full -mr-12 -mt-12 transition-transform group-hover:scale-125"></div>
                    <div class="relative">
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-4 shadow-inner">
                            <x-lucide-swords class="w-6 h-6" />
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Nomor Lomba</p>
                        <h4 class="text-3xl font-black text-slate-900 tracking-tighter">{{ number_format($totalLomba) }}</h4>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-50 flex items-center gap-2 text-[10px] font-bold text-slate-400">
                        Kategori & Nomor Lomba
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-100/50 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50/50 rounded-full -mr-12 -mt-12 transition-transform group-hover:scale-125"></div>
                    <div class="relative">
                        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-4 shadow-inner">
                            <x-lucide-banknote class="w-6 h-6" />
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Pendapatan</p>
                        <h4 class="text-3xl font-black text-slate-900 tracking-tighter">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-50 flex items-center gap-2 text-[10px] font-bold text-slate-400">
                        <span class="text-amber-600">Confirmed</span> (Omset)
                    </div>
                </div>
            </div>

            {{-- Registration Status Breakdown (Mini Cards) --}}
            <div class="mb-10">
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2 ml-1">
                    <div class="w-1.5 h-1.5 bg-slate-400 rounded-full"></div>
                    Status Pendaftaran (All Time)
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    {{-- Total Pendaftaran --}}
                    <div class="bg-slate-900 p-4 rounded-3xl border border-slate-800 shadow-lg flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center text-white">
                            <x-lucide-clipboard-list class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-tighter">Total</p>
                            <p class="text-xl font-black text-white leading-none">{{ number_format($regStats['total']) }}</p>
                        </div>
                    </div>
                    
                    {{-- Confirmed --}}
                    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-md flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                            <x-lucide-check-circle-2 class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Confirmed</p>
                            <p class="text-xl font-black text-slate-900 leading-none">{{ number_format($regStats['confirmed']) }}</p>
                        </div>
                    </div>

                    {{-- Pending --}}
                    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-md flex items-center gap-4">
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                            <x-lucide-clock class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Pending</p>
                            <p class="text-xl font-black text-slate-900 leading-none">{{ number_format($regStats['pending']) }}</p>
                        </div>
                    </div>

                    {{-- Rejected --}}
                    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-md flex items-center gap-4">
                        <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                            <x-lucide-user-x class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Rejected</p>
                            <p class="text-xl font-black text-slate-900 leading-none">{{ number_format($regStats['rejected']) }}</p>
                        </div>
                    </div>

                    {{-- Cancelled --}}
                    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-md flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400">
                            <x-lucide-x-circle class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Cancelled</p>
                            <p class="text-xl font-black text-slate-900 leading-none">{{ number_format($regStats['cancelled']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter & Export Section --}}
                {{-- Center: Filter Configuration (Full Width) --}}
                <div class="xl:col-span-3 max-w-4xl mx-auto w-full">
                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl p-8 md:p-12">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-slate-200">
                                <x-lucide-file-text class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">Konfigurasi Laporan</h3>
                                <p class="text-sm text-slate-400 font-bold mt-1 uppercase tracking-widest italic">Pilih jenis data dan target event untuk diunduh</p>
                            </div>
                        </div>

                        <form id="reportForm" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label
                                    class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Jenis
                                    Dokumen Laporan</label>
                                <select id="report_type"
                                    class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-ksc-blue p-4 outline-none transition appearance-none cursor-pointer shadow-sm uppercase tracking-wider">
                                    <option value="pendaftaran">1. Data Pendaftaran Event (Rekapitulasi)</option>
                                    <option value="buku_acara">2. Buku Acara (Program Book)</option>
                                    <option value="buku_hasil">3. Buku Hasil Lomba (Official Result)</option>
                                    <option value="pendaftaran_klub">4. Data Pendaftaran Klub (Invoice/Tagihan)</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Filter
                                    Target Event</label>
                                <select id="event_uid"
                                    class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-ksc-blue p-4 outline-none transition appearance-none cursor-pointer shadow-sm uppercase tracking-wider">
                                    <option value="all">Sertakan Seluruh Event (Dari Awal - Akhir)</option>
                                    @foreach ($events as $event)
                                        <option value="{{ $event['uid'] }}"
                                            {{ ($filters['event_uid'] ?? '') == $event['uid'] ? 'selected' : '' }}>
                                            {{ $event['nama_event'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2 pt-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <button type="button" onclick="handleReportAction('pdf')"
                                        class="group relative overflow-hidden bg-rose-600 hover:bg-rose-700 text-white font-black text-xs uppercase tracking-[0.2em] rounded-3xl px-8 py-6 shadow-2xl shadow-rose-100 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-4">
                                        <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                                        <x-lucide-file-text class="w-6 h-6" />
                                        <span>Download PDF (Print Ready)</span>
                                    </button>
                                    
                                    <button type="button" onclick="handleReportAction('excel')"
                                        class="group relative overflow-hidden bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-[0.2em] rounded-3xl px-8 py-6 shadow-2xl shadow-emerald-100 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-4">
                                        <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                                        <x-lucide-file-spreadsheet class="w-6 h-6" />
                                        <span>Download Excel (Data Raw)</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-12 pt-8 border-t border-slate-50">
                            <div class="flex items-start gap-4 text-slate-400 italic">
                                <x-lucide-shield-check class="w-5 h-5 shrink-0" />
                                <p class="text-[10px] font-bold leading-relaxed">
                                    Sistem akan melakukan kompilasi data secara real-time. Pastikan seluruh pendaftaran sudah diverifikasi 
                                    oleh admin pusat sebelum melakukan penarikan data untuk menjaga akurasi laporan keuangan dan teknis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notification Toast --}}
        <div id="notificationToast" class="fixed top-5 right-5 z-[200] transform translate-x-[120%] transition-transform duration-500">
            <div class="bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border border-slate-700/50 backdrop-blur-xl">
                <div id="notificationIcon" class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg">
                    <x-lucide-info class="w-6 h-6" />
                </div>
                <div class="pr-4">
                    <h5 id="notificationTitle" class="text-sm font-black uppercase tracking-[0.1em] mb-0.5">Notification</h5>
                    <p id="notificationMessage" class="text-[11px] text-slate-400 font-bold leading-tight"></p>
                </div>
                <button onclick="hideNotification()" class="p-2 hover:bg-white/10 rounded-lg transition text-slate-500 hover:text-white">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </div>
        </div>

        <script>
            function showNotification(title, message, type = 'info') {
                const toast = document.getElementById('notificationToast');
                const titleEl = document.getElementById('notificationTitle');
                const messageEl = document.getElementById('notificationMessage');
                const iconEl = document.getElementById('notificationIcon');

                titleEl.innerText = title;
                messageEl.innerText = message;

                if (type === 'error') {
                    iconEl.className = 'w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center text-white shadow-red-500/20';
                } else if (type === 'success') {
                    iconEl.className = 'w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-emerald-500/20';
                } else {
                    iconEl.className = 'w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center text-white shadow-blue-500/20';
                }

                toast.classList.remove('translate-x-[120%]');

                setTimeout(() => {
                    hideNotification();
                }, 5000);
            }

            function hideNotification() {
                const toast = document.getElementById('notificationToast');
                toast.classList.add('translate-x-[120%]');
            }

            function refreshPreview() {
                const iframe = document.getElementById('mainPreviewIframe');
                if (iframe.src) {
                    const currentSrc = iframe.src;
                    iframe.src = ''; // Clear source to show loading effect
                    setTimeout(() => {
                        iframe.src = currentSrc;
                    }, 50);
                }
            }

            function handleReportAction(action) {
                const type = document.getElementById('report_type').value;
                const eventUid = document.getElementById('event_uid').value;

                let url = '';
                if (type === 'buku_acara') {
                    url = `{{ url('/dashboard/report/export/buku-acara') }}/${eventUid}`;
                } else if (type === 'buku_hasil') {
                    url = `{{ url('/dashboard/report/export/buku-hasil') }}/${eventUid}`;
                } else if (type === 'pendaftaran') {
                    url = `{{ url('/dashboard/report/export/pendaftaran') }}?event_uid=${eventUid}`;
                } else {
                    url = `{{ url('/dashboard/report/export/process') }}?type=${type}&event_uid=${eventUid}`;
                }

                if (action === 'pdf') {
                    showNotification('Ekspor PDF', 'Dokumen sedang disiapkan untuk diunduh.', 'success');
                    window.open(url, '_blank');
                } else if (action === 'excel') {
                    showNotification('Ekspor Excel', 'Data sedang diproses dalam format Spreadsheet.', 'success');
                    window.open(url + (url.includes('?') ? '&' : '?') + 'format=excel', '_blank');
                }
            }
        </script>
    @else
        @include('layouts.layout-partials.coming-soon')
    @endcan
@endsection
