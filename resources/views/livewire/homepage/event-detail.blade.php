<div>
    {{-- HERO SECTION --}}
    <section class="relative h-[80vh] flex items-center justify-center text-center overflow-hidden bg-slate-900">
        {{-- Banner Background --}}
        @if ($event['banner_event'])
            <img src="{{ url($event['banner_event']) }}"
                class="absolute inset-0 w-full h-full object-cover animate-scale-slow" alt="{{ $event['nama_event'] }}">
        @else
            <div class="absolute inset-0 bg-linear-to-br from-slate-800 to-slate-900"></div>
        @endif

        {{-- Gradient Overlays --}}
        <div class="absolute inset-0 bg-linear-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
        <div class="absolute inset-0 bg-blue-950/20 backdrop-blur-[1px]"></div>

        <div class="container mx-auto px-6 z-10">
            <div class="max-w-4xl mx-auto">
                {{-- Badge Kategori & Tipe --}}
                <div class="flex justify-center items-center gap-3 mb-6 animate-fade-in-up">
                    <span
                        class="px-4 py-1.5 bg-white/10 backdrop-blur-md text-white border border-white/20 text-[10px] font-black uppercase tracking-widest rounded-full">
                        {{ $event['kategori']['nama_kategori'] ?? 'General' }}
                    </span>
                    <span
                        class="px-4 py-1.5 bg-ksc-accent text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg shadow-ksc-accent/20">
                        {{ $this->event['biaya_event'] > 0 ? 'Berbayar' : 'Gratis' }}
                    </span>
                </div>

                <h1
                    class="text-4xl md:text-7xl font-black text-white leading-tight mb-8 drop-shadow-2xl animate-fade-in-up delay-100">
                    {{ $event['nama_event'] }}
                </h1>

                {{-- Author & Date Info --}}
                <div class="flex flex-wrap justify-center items-center gap-6 text-white/90 animate-fade-in-up delay-200">
                    <div class="flex items-center gap-2 group">
                        <div
                            class="w-10 h-10 rounded-full bg-ksc-accent/20 flex items-center justify-center border border-ksc-accent/30">
                            <x-lucide-user class="w-5 h-5 text-ksc-accent" />
                        </div>
                        <div class="text-left">
                            <p class="text-[9px] uppercase font-bold text-slate-400">Organized by</p>
                            <p class="text-sm font-black">{{ $event['author'] ?? 'Admin KSC' }}</p>
                        </div>
                    </div>
                    <div class="h-8 w-px bg-white/20 hidden md:block"></div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center border border-blue-500/30">
                            <x-lucide-calendar class="w-5 h-5 text-blue-400" />
                        </div>
                        <span
                            class="text-lg font-bold">{{ \Carbon\Carbon::parse($event['tanggal_event'])->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <section class="py-24 bg-white relative">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row gap-16">
                {{-- Left Side: Details --}}
                <div class="lg:w-2/3 space-y-12">
                    {{-- Deskripsi --}}
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-1 w-ksc-blue bg-ksc-blue rounded-full"></div>
                            <h2 class="text-3xl font-black text-slate-900 italic uppercase">Tentang Event</h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-slate-600 leading-relaxed font-medium">
                            {!! $event['deskripsi'] !!}
                        </div>
                    </div>

                    {{-- Lokasi & Waktu --}}
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="p-8 bg-slate-50 rounded-4xl border border-slate-100 group hover:border-ksc-blue/20 transition-colors">
                            <div class="w-12 h-12 bg-ksc-blue/10 rounded-2xl flex items-center justify-center text-ksc-blue mb-6 group-hover:scale-110 transition-transform">
                                <x-lucide-map-pin class="w-6 h-6" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-2 uppercase italic">Lokasi Event</h3>
                            <p class="text-slate-600 font-bold">{{ $event['lokasi_event'] }}</p>
                        </div>

                        <div class="p-8 bg-slate-50 rounded-4xl border border-slate-100 group hover:border-ksc-accent/20 transition-colors">
                            <div class="w-12 h-12 bg-ksc-accent/10 rounded-2xl flex items-center justify-center text-ksc-accent mb-6 group-hover:scale-110 transition-transform">
                                <x-lucide-clock class="w-6 h-6" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-2 uppercase italic">Waktu Pelaksanaan</h3>
                            <p class="text-slate-600 font-bold">Pukul {{ \Carbon\Carbon::parse($event['waktu_event'])->format('H:i') }} WIB - Selesai</p>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Registration Card --}}
                <div class="lg:w-1/3">
                    <div class="sticky top-32">
                        <div class="bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden p-8 md:p-10 ring-1 ring-slate-100">
                            <div class="space-y-4 mb-8">
                                <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <x-lucide-users class="w-5 h-5 text-ksc-blue" />
                                    <div class="text-sm">
                                        <p class="font-black text-slate-900 uppercase italic">Terbatas</p>
                                        <p class="text-slate-500 font-bold tracking-tight">Kuota Maksimal {{ $event['total_quota'] }} Peserta</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <x-lucide-award class="w-5 h-5 text-ksc-accent" />
                                    <div class="text-sm">
                                        <p class="font-black text-slate-900 uppercase italic">Sertifikat & Medali</p>
                                        <p class="text-slate-500 font-bold tracking-tight">Untuk semua pemenang lomba</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Registration Button Section --}}
                            <div class="space-y-4">
                                @if (session()->has('success'))
                                    <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl text-sm font-bold flex items-center gap-2 border border-emerald-100 animate-fade-in">
                                        <x-lucide-check-circle class="w-5 h-5" /> {{ session('success') }}
                                    </div>
                                @endif

                                @if (session()->has('error'))
                                    <div class="p-4 bg-rose-50 text-rose-700 rounded-2xl text-sm font-bold flex items-center gap-2 border border-rose-100 animate-fade-in">
                                        <x-lucide-alert-circle class="w-5 h-5" /> {{ session('error') }}
                                    </div>
                                @endif

                                @if ($event['status_event'] == 'berjalan')
                                    @if ($user)
                                        @php
                                            $totalCats = count($event['eventCategories'] ?? []);
                                            $regCount = count($registeredCategoryUids ?? []);
                                            $isAllRegistered = ($totalCats > 0 && $regCount >= $totalCats);
                                        @endphp

                                        @if ($regCount > 0)
                                            <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl text-[10px] font-black flex items-center justify-center gap-2 border border-emerald-100 mb-4 uppercase tracking-widest italic">
                                                <x-lucide-check-circle class="w-4 h-4" /> Anda sudah terdaftar di {{ $regCount }} kategori
                                            </div>
                                        @endif

                                        @can('master-pendaftaran.create.self')
                                            <button
                                                wire:click="closeCreateModal"
                                                class="w-full py-5 bg-ksc-blue hover:bg-ksc-dark text-white rounded-2xl font-black shadow-xl shadow-blue-200 transition transform hover:-translate-y-1 flex items-center justify-center gap-3 uppercase tracking-tighter italic">
                                                {{ $regCount > 0 ? 'DAFTAR LAGI / TAMBAH LOMBA' : 'DAFTAR SEKARANG' }} <x-lucide-arrow-right class="w-5 h-5" />
                                            </button>
                                        @else
                                            <div class="w-full py-5 bg-amber-50 text-amber-700 rounded-2xl font-bold flex items-center justify-center gap-2 border border-amber-100 text-xs text-center px-4">
                                                <x-lucide-lock class="w-4 h-4" /> AKUN ANDA TIDAK MEMILIKI AKSES PENDAFTARAN
                                            </div>
                                        @endcan
                                    @else
                                        <div class="space-y-3">
                                            <a href="{{ route('login') }}"
                                                class="w-full py-5 bg-blue-600 hover:bg-green-500 text-white rounded-2xl font-black shadow-xl shadow-slate-200 transition transform hover:-translate-y-1 flex items-center justify-center gap-3 text-center">
                                                MASUK UNTUK DAFTAR <x-lucide-log-in class="w-5 h-5" />
                                            </a>
                                            <p class="text-[9px] text-center text-slate-400 font-bold uppercase tracking-widest italic">
                                                Belum punya akun? <a href="{{ route('register') }}" class="text-ksc-blue underline">Daftar Member</a>
                                            </p>
                                        </div>
                                    @endif
                                @elseif($event['status_event'] == 'mendatang')
                                    <button disabled
                                        class="w-full py-5 bg-blue-100 text-blue-500 rounded-2xl font-black cursor-not-allowed flex items-center justify-center gap-3 italic uppercase border-2 border-blue-200">
                                        Pendaftaran Belum Dibuka <x-lucide-calendar-clock class="w-5 h-5" />
                                    </button>
                                @elseif($event['status_event'] == 'dibatalkan')
                                    <button disabled
                                        class="w-full py-5 bg-red-100 text-red-500 rounded-2xl font-black cursor-not-allowed flex items-center justify-center gap-3 italic uppercase border-2 border-red-200">
                                        Event Dibatalkan <x-lucide-ban class="w-5 h-5" />
                                    </button>
                                @else
                                    <button disabled
                                        class="w-full py-5 bg-slate-200 text-slate-400 rounded-2xl font-black cursor-not-allowed flex items-center justify-center gap-3 italic uppercase">
                                        Pendaftaran Ditutup <x-lucide-lock class="w-5 h-5" />
                                    </button>
                                @endif

                                <button data-modal-target="share-modal" data-modal-toggle="share-modal"
                                    class="w-full py-3 border-2 border-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-50 transition flex items-center justify-center gap-2">
                                    <x-lucide-share-2 class="w-4 h-4" /> Bagikan Event
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Create Manual Modal (PASTED FROM DASHBOARD) --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-[200] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeCreateModal"></div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-5xl relative z-50 border border-slate-100 flex flex-col max-h-[95vh]">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase italic">{{ auth()->user()->can('master-pendaftaran.create') ? 'Pendaftaran Manual' : 'Form Pendaftaran Lomba' }}</h3>
                    <button wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-8 space-y-6 overflow-y-auto flex-1 custom-scrollbar">
                    {{-- Validation Errors --}}
                    @if (!empty($create_errors))
                        <div class="bg-rose-50 border border-rose-200 rounded-[2rem] p-8 mb-4 flex flex-col md:flex-row items-start gap-6 animate-in fade-in slide-in-from-top-4 duration-300">
                            <div class="w-14 h-14 bg-rose-500 text-white rounded-2xl flex items-center justify-center shrink-0 shadow-xl shadow-rose-200">
                                <x-lucide-alert-octagon class="w-8 h-8" />
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-lg font-black text-rose-900 uppercase tracking-tighter leading-tight">Pendaftaran Gagal Diproses</h4>
                                        <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mt-1">Ditemukan kendala pada persyaratan lomba</p>
                                    </div>
                                    <button wire:click="$set('create_errors', [])" class="p-2 hover:bg-rose-100 rounded-xl transition text-rose-400">
                                        <x-lucide-x class="w-5 h-5" />
                                    </button>
                                </div>

                                <div class="mt-6 space-y-3">
                                    @foreach($create_errors as $err)
                                        <div class="flex items-start gap-3 bg-white/60 p-4 rounded-2xl border border-rose-100/50 shadow-sm group">
                                            <div class="w-6 h-6 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center shrink-0 mt-0.5 group-hover:scale-110 transition">
                                                <x-lucide-x class="w-3.5 h-3.5" />
                                            </div>
                                            <span class="text-xs font-bold text-rose-700 leading-relaxed uppercase tracking-tight">{{ $err }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-8 flex justify-start">
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-rose-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition shadow-lg shadow-rose-200">
                                        Lengkapi di Dashboard <x-lucide-external-link class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(auth()->user()->can('master-pendaftaran.create'))
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cari
                                & Pilih Peserta <span class="text-rose-500">*</span></label>

                            <div class="space-y-3 p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <!-- Search Input -->
                                <div class="relative">
                                    <x-lucide-search
                                        class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input type="text" wire:key="user-search-input"
                                        wire:model.live.debounce.300ms="userSearch"
                                        placeholder="Ketik nama atau email peserta untuk mencari..."
                                        class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 border-b-2 focus:border-blue-500 outline-none transition shadow-sm placeholder:font-medium placeholder:text-slate-400">
                                </div>

                                <!-- User Checkboxes List -->
                                <div class="space-y-2 pr-2 overflow-y-auto custom-scrollbar" style="max-height: 200px;">
                                    @forelse($availableUsers as $usr)
                                        <label
                                            class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all {{ in_array($usr->uid, $create_user_uids) ? 'border-blue-500 bg-blue-50/50 shadow-sm' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50' }}">
                                            <div class="relative flex items-center justify-center shrink-0">
                                                <input type="checkbox" wire:model.live="create_user_uids"
                                                    value="{{ $usr->uid }}"
                                                    class="peer w-5 h-5 rounded-md border-2 border-slate-300 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer appearance-none checked:bg-blue-600 checked:border-blue-600">
                                                <x-lucide-check
                                                    class="w-3 h-3 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4
                                                    class="text-xs font-black text-slate-900 uppercase tracking-tight truncate">
                                                    {{ $usr->profile?->full_name ?: $usr->username }}</h4>
                                                <p class="text-[10px] font-bold text-slate-500 truncate">
                                                    {{ $usr->email }}</p>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="py-4 text-center text-slate-400 font-medium text-[10px]">-- Tidak ada
                                            peserta yang cocok --</div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- PREVIEW DATA PESERTA (SLIDER) --}}
                            @if (count($create_user_uids) > 0)
                                @php
                                    $selectedUsersPreview = \App\Models\User::with('profile')
                                        ->whereIn('uid', $create_user_uids)
                                        ->get();
                                @endphp
                                @if ($selectedUsersPreview->count() > 0)
                                    <div class="mt-4 p-6 bg-blue-50/60 border border-blue-100 rounded-2xl shadow-sm relative">
                                        <div class="flex justify-between items-center mb-4 border-b border-blue-100 pb-2">
                                            <h4 class="text-xs font-black text-blue-500 uppercase tracking-widest">Preview
                                                Data Atlet ({{ $selectedUsersPreview->count() }} Terpilih)</h4>

                                            @if ($selectedUsersPreview->count() > 1)
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        wire:click="prevSlide"
                                                        type="button"
                                                        class="w-6 h-6 rounded-full bg-white border border-blue-200 text-blue-500 flex items-center justify-center hover:bg-blue-50 transition"><x-lucide-chevron-left
                                                            class="w-4 h-4" /></button>
                                                    <span
                                                        class="text-[10px] font-black text-blue-600 w-8 text-center">{{ $activeSlide + 1 }}/{{ $selectedUsersPreview->count() }}</span>
                                                    <button
                                                        wire:click="nextSlide"
                                                        type="button"
                                                        class="w-6 h-6 rounded-full bg-white border border-blue-200 text-blue-500 flex items-center justify-center hover:bg-blue-50 transition"><x-lucide-chevron-right
                                                            class="w-4 h-4" /></button>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="relative overflow-hidden min-h-[150px]">
                                            @foreach ($selectedUsersPreview as $index => $userPreview)
                                                @if ($activeSlide === $index)
                                                    <div wire:key="slide-preview-{{ $userPreview->uid }}" class="animate-in fade-in slide-in-from-right-4 duration-500">
                                                        <div class="flex items-center gap-4">
                                                            @if ($userPreview->profile?->profile_picture)
                                                                <img src="{{ asset($userPreview->profile->profile_picture) }}"
                                                                    class="w-14 h-14 rounded-2xl object-cover shadow-sm border border-blue-200">
                                                            @else
                                                                <div
                                                                    class="w-14 h-14 bg-white text-blue-400 rounded-2xl flex items-center justify-center border border-blue-200">
                                                                    <x-lucide-user class="w-6 h-6" /></div>
                                                            @endif
                                                            <div>
                                                                <p class="text-lg font-black text-slate-900 leading-none mt-1">
                                                                    {{ $userPreview->profile?->full_name ?: $userPreview->username }}
                                                                </p>
                                                                <p class="text-[10px] font-bold text-slate-500 mt-0.5">
                                                                    {{ $userPreview->email }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="space-y-4 mt-4">
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Tempat, Tgl Lahir</label>
                                                                    <p class="text-xs font-bold text-slate-800">
                                                                        {{ $userPreview->profile?->birth_place ?: '-' }},
                                                                        {{ $userPreview->profile?->birth_date ? \Carbon\Carbon::parse($userPreview->profile->birth_date)->format('d M Y') : '-' }}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Jenis Kelamin</label>
                                                                    <p class="text-xs font-bold text-slate-800 uppercase">{{ $userPreview->profile?->gender ?: '-' }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">No. Telp / WA</label>
                                                                    <p class="text-xs font-bold text-slate-800">{{ $userPreview->profile?->phone_number ?: '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Klub Renang</label>
                                                                    <p class="text-xs font-bold text-slate-800 uppercase">{{ $userPreview->profile?->club?->name ?: 'INDEPENDENT' }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">NIK / No. Identitas</label>
                                                                    <p class="text-xs font-bold text-slate-800">{{ $userPreview->profile?->identity_number ?: '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Tinggi & Berat Badan</label>
                                                                    <p class="text-xs font-bold text-slate-800">{{ $userPreview->profile?->height ? $userPreview->profile->height . ' cm' : '-' }} / {{ $userPreview->profile?->weight ? $userPreview->profile->weight . ' kg' : '-' }}</p>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Alamat Lengkap</label>
                                                                <p class="text-xs font-bold text-slate-800">{{ $userPreview->profile?->address ?: '-' }}</p>
                                                            </div>
                                                            @if($userPreview->profile?->medical_history)
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-rose-400 uppercase tracking-widest">Riwayat Medis</label>
                                                                    <p class="text-xs font-bold text-rose-600">{{ $userPreview->profile->medical_history }}</p>
                                                                </div>
                                                            @endif
                                                            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-blue-100/50">
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Foto KTP / Identitas</label>
                                                                    @if ($userPreview->profile?->identity_photo)
                                                                        <a href="{{ route('document.view', ['type' => 'ktp', 'filename' => basename($userPreview->profile->identity_photo) ?: 'none']) }}"
                                                                            target="_blank"
                                                                            class="block w-full h-16 bg-white rounded-xl overflow-hidden border border-blue-100 hover:border-blue-400 transition relative group">
                                                                            <img src="{{ route('document.view', ['type' => 'ktp', 'filename' => basename($userPreview->profile->identity_photo) ?: 'none']) }}"
                                                                                class="w-full h-full object-cover">
                                                                            <div class="absolute inset-0 bg-slate-900/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                                                <x-lucide-external-link class="w-4 h-4 text-white" />
                                                                            </div>
                                                                        </a>
                                                                    @else
                                                                        <div class="w-full h-16 bg-white border border-dashed border-blue-200 rounded-xl flex items-center justify-center text-slate-400 text-[10px] font-bold">Belum Ada</div>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Foto Akta Kelahiran</label>
                                                                    @if ($userPreview->profile?->birth_certificate_photo)
                                                                        <a href="{{ route('document.view', ['type' => 'akta', 'filename' => basename($userPreview->profile->birth_certificate_photo) ?: 'none']) }}"
                                                                            target="_blank"
                                                                            class="block w-full h-16 bg-white rounded-xl overflow-hidden border border-blue-100 hover:border-blue-400 transition relative group">
                                                                            <img src="{{ route('document.view', ['type' => 'akta', 'filename' => basename($userPreview->profile->birth_certificate_photo) ?: 'none']) }}"
                                                                                class="w-full h-full object-cover">
                                                                            <div class="absolute inset-0 bg-slate-900/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                                                <x-lucide-external-link class="w-4 h-4 text-white" />
                                                                            </div>
                                                                        </a>
                                                                    @else
                                                                        <div class="w-full h-16 bg-white border border-dashed border-blue-200 rounded-xl flex items-center justify-center text-slate-400 text-[10px] font-bold">Belum Ada</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-blue-200">
                                <x-lucide-user-check class="w-6 h-6" />
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-blue-900 uppercase tracking-tighter leading-tight">Konfirmasi Peserta</h4>
                                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mt-1">Anda mendaftar sebagai: <span class="text-blue-600 font-black italic">{{ auth()->user()->profile?->full_name ?: auth()->user()->username }}</span></p>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mt-8 border-t border-slate-100 pt-8">

                        {{-- LEFT COLUMN: PILIH KATEGORI LOMBA --}}
                        <div class="lg:col-span-3">
                            <div class="mb-4">
                                <h2 class="text-xl font-black text-slate-900 tracking-tighter uppercase italic">Pilih Kategori</h2>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pilih Nomor Lomba (Bisa pilih lebih dari satu)</p>
                            </div>

                            <div class="space-y-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <!-- Search Input -->
                                <div class="relative">
                                    <x-lucide-search
                                        class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input type="text" wire:key="lomba-search-input"
                                        wire:model.live.debounce.300ms="lombaSearch"
                                        placeholder="Ketik nama lomba atau event untuk mencari..."
                                        class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 outline-none transition shadow-sm placeholder:font-medium placeholder:text-slate-400">
                                </div>

                                <!-- Event Checkboxes List -->
                                <div class="space-y-4 pr-4 pb-6 overflow-y-auto custom-scrollbar"
                                    style="max-height: 450px;">
                                    @forelse($availableCategories->groupBy('event_uid') as $eventUid => $cats)
                                        @php $evt = $cats->first()->event; @endphp
                                        <div
                                            class="sticky top-0 bg-blue-500 z-20 py-3 pl-3 border-b border-slate-100 mb-2 rounded-lg">
                                            <span
                                                class="text-[14px] font-black text-white uppercase tracking-widest italic">{{ $evt ? $evt->name : 'Tanpa Event' }}</span>
                                        </div>
                                        @foreach ($cats as $cat)
                                            @php $isRegistered = in_array($cat->uid, $registeredCategoryUids); @endphp
                                            <label
                                                class="flex items-start gap-4 p-4 border rounded-2xl cursor-pointer transition-all {{ in_array($cat->uid, $create_event_categories) ? 'border-blue-500 bg-blue-50/50 shadow-sm' : ($isRegistered ? 'bg-slate-50 opacity-60 cursor-not-allowed border-slate-100' : 'border-slate-200 hover:border-green-300 hover:bg-slate-50') }}">
                                                <div class="relative flex items-center justify-center shrink-0 mt-1">
                                                    <input type="checkbox" wire:model.live="create_event_categories"
                                                        value="{{ $cat->uid }}"
                                                        {{ $isRegistered ? 'disabled checked' : '' }}
                                                        class="peer w-6 h-6 rounded-md border-2 border-slate-300 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer appearance-none checked:bg-green-600 checked:border-green-600">
                                                    <x-lucide-check
                                                        class="w-4 h-4 text-white absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity" />
                                                </div>
                                                <div class="flex-1">
                                                    <h4
                                                        class="text-sm font-black text-slate-900 uppercase tracking-tight">
                                                        {{ $cat->acara_name }}</h4>
                                                    <div
                                                        class="flex flex-wrap items-center gap-3 mt-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                        <span class="flex items-center gap-1" title="Tanggal Pelaksanaan"><x-lucide-calendar
                                                                class="w-3 h-3" />
                                                            {{ $event['tanggal_event'] ? \Carbon\Carbon::parse($event['tanggal_event'])->format('d M Y') : 'TBA' }}
                                                        </span>
                                                        <span class="flex items-center gap-1" title="Waktu Pelaksanaan"><x-lucide-clock
                                                                class="w-3 h-3" />
                                                            {{ $cat->start_time ? \Carbon\Carbon::parse($cat->start_time)->format('H:i') : 'TBA' }} WIB</span>
                                                        <span class="flex items-center gap-1" title="Lokasi"><x-lucide-map-pin
                                                                class="w-3 h-3" /> {{ $cat->location ?: '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-black {{ $cat->type === 'paid' ? 'text-slate-900' : 'text-emerald-500' }} italic tracking-tighter">
                                                        @if ($cat->type === 'paid')
                                                            Rp{{ number_format($cat->registration_fee, 0, ',', '.') }}
                                                        @else
                                                            GRATIS
                                                        @endif
                                                    </p>
                                                    @if($isRegistered)
                                                        <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Terdaftar</span>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    @empty
                                        <div
                                            class="py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl">
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">--
                                                Tidak ada lomba yang cocok --</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: PEMBAYARAN & KONFIRMASI --}}
                        <div class="lg:col-span-2 relative">
                            <div class="bg-slate-50/50 border border-slate-100 rounded-3xl p-6 sticky top-0">
                                <div class="mb-6">
                                    <h2 class="text-xl font-black text-slate-900 tracking-tighter uppercase italic">Ringkasan</h2>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Selesaikan Pendaftaran Anda</p>
                                </div>

                                @php
                                    $totalBiaya = 0;
                                    $isPaidEventSelected = false;
                                    foreach ($create_event_categories as $selUid) {
                                        $selCat = \App\Models\EventCategory::where('uid', $selUid)->first();
                                        if ($selCat && $selCat->type === 'paid') {
                                            $totalBiaya += $selCat->registration_fee;
                                            $isPaidEventSelected = true;
                                        }
                                    }
                                @endphp

                                @if (count($create_event_categories) === 0)
                                    <div
                                        class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
                                        <div
                                            class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <x-lucide-shopping-cart class="w-8 h-8" />
                                        </div>
                                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Belum Ada Pilihan</h3>
                                        <p class="text-xs font-bold text-slate-400 mt-2">Pilih minimal satu lomba di samping untuk melanjutkan.</p>
                                    </div>
                                @elseif(!$isPaidEventSelected)
                                    {{-- JIKA HANYA LOMBA GRATIS --}}
                                    <div
                                        class="bg-white border border-emerald-100 rounded-2xl p-8 text-center shadow-sm relative overflow-hidden">
                                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-white z-0">
                                        </div>
                                        <div class="relative z-10">
                                            <div
                                                class="w-16 h-16 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                                <x-lucide-gift class="w-8 h-8" />
                                            </div>
                                            <h3
                                                class="text-lg font-black text-emerald-600 uppercase tracking-tighter italic mb-1">
                                                Pendaftaran Gratis</h3>
                                            <p
                                                class="text-[10px] font-bold text-emerald-600/70 uppercase tracking-widest">
                                                Tidak ada tagihan untuk pendaftaran ini.</p>
                                        </div>
                                    </div>
                                @else
                                    {{-- JIKA ADA LOMBA BERBAYAR --}}
                                    <div
                                        class="bg-ksc-blue rounded-3xl p-6 text-white shadow-xl shadow-blue-900/20 relative overflow-hidden mb-6">
                                        <div
                                            class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl">
                                        </div>
                                        <div class="flex justify-between items-start mb-6 relative z-10">
                                            <div>
                                                <p
                                                    class="text-[10px] font-black text-blue-200 uppercase tracking-widest mb-1">
                                                    Total Biaya</p>
                                                <p
                                                    class="text-3xl font-black text-amber-400 italic tracking-tighter leading-none">
                                                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
                                            </div>
                                            <div
                                                class="px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-lg">
                                                <span
                                                    class="text-[10px] font-black text-white tracking-widest uppercase">Invoice</span>
                                            </div>
                                        </div>
                                        <div
                                            class="pt-4 border-t border-white/10 flex justify-between items-end relative z-10">
                                            <div>
                                                <p
                                                    class="text-[9px] font-bold text-blue-200 uppercase tracking-widest mb-0.5">
                                                    Metode Terpilih</p>
                                                <p class="text-xs font-black text-white uppercase tracking-widest">
                                                    {{ $create_payment_method === 'cash' ? 'Tunai (Cash)' : 'Transfer Bank' }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p
                                                    class="text-[9px] font-bold text-blue-200 uppercase tracking-widest mb-0.5">
                                                    Status</p>
                                                <p class="text-xs font-black text-white uppercase tracking-widest">
                                                    {{ $create_status }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Metode
                                                Pembayaran</label>
                                            <select wire:model.live="create_payment_method"
                                                class="w-full px-4 py-3.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-700 focus:ring-4 focus:ring-blue-100 outline-none transition uppercase tracking-widest">
                                                <option value="transfer">💳 Transfer Bank</option>
                                                @can('master-pendaftaran.pay.cash')
                                                    <option value="cash">💵 Tunai (Cash)</option>
                                                @endcan
                                            </select>
                                        </div>

                                        @if ($create_payment_method === 'transfer')
                                            <div class="animate-in fade-in slide-in-from-top-2 duration-300 space-y-4">
                                                <div class="p-6 bg-amber-50/50 rounded-3xl border border-amber-200 shadow-inner">
                                                    <label class="block text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                                        <x-lucide-info class="w-4 h-4" /> Detail Tujuan Pembayaran
                                                    </label>
                                                    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
                                                        @if($event['qr_code'])
                                                            <div class="w-full p-4 bg-white flex justify-center border-b border-slate-50">
                                                                <a href="{{ asset($event['qr_code']) }}" target="_blank" class="group relative block cursor-zoom-in" title="Klik untuk memperbesar">
                                                                    <img src="{{ asset($event['qr_code']) }}" class="max-h-64 w-auto object-contain rounded-xl shadow-md transition duration-300 group-hover:scale-[1.02]">
                                                                    <div class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                                                                        <div class="bg-white/20 backdrop-blur-md p-2 rounded-lg border border-white/30">
                                                                            <x-lucide-maximize-2 class="w-5 h-5 text-white" />
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @endif
                                                        <div class="p-5 flex items-center justify-between gap-4">
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">{{ $event['bank'] }}</p>
                                                                <p class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1.5">{{ $event['rekening'] }}</p>
                                                                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-tight italic">a/n {{ $event['atas_nama'] }}</p>
                                                            </div>
                                                            <button type="button"
                                                                onclick="navigator.clipboard.writeText('{{ $event['rekening'] }}'); alert('Nomor rekening berhasil disalin!');"
                                                                class="flex flex-col items-center gap-1 p-3 hover:bg-amber-50 text-amber-600 rounded-2xl transition group">
                                                                <x-lucide-copy class="w-5 h-5 group-hover:scale-110 transition" />
                                                                <span class="text-[8px] font-black uppercase tracking-tighter">Salin</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Unggah Bukti Transfer</label>
                                                    <div class="relative group cursor-pointer">
                                                        <input type="file" id="payment_proof_input" wire:model="create_payment_proof"
                                                            accept="image/*"
                                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required onchange="previewSingleImage(this, 'preview-payment-proof', 'placeholder-payment-proof')">
                                                        
                                                        <!-- Tambahkan wire:ignore agar DOM tidak di-reset oleh Livewire saat re-render -->
                                                        <div wire:ignore
                                                            class="w-full h-32 bg-white border-2 border-dashed border-slate-200 group-hover:border-blue-400 group-hover:bg-blue-50/30 rounded-2xl flex flex-col items-center justify-center transition-all relative z-10 overflow-hidden">
                                                            
                                                            <img id="preview-payment-proof" src="" class="absolute inset-0 w-full h-full object-cover opacity-75 hidden">
                                                            
                                                            <div id="placeholder-payment-proof" class="flex flex-col items-center justify-center pointer-events-none z-10">
                                                                <x-lucide-camera
                                                                    class="w-8 h-8 text-slate-300 group-hover:text-blue-400 transition-colors mb-2" />
                                                                <span
                                                                    class="text-[10px] font-black text-slate-400 group-hover:text-blue-500 uppercase tracking-widest transition-colors">Pilih Foto Bukti</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @error('create_payment_proof')
                                                    <span
                                                        class="text-xs font-bold text-rose-500 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        @endif
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 border-t border-slate-50 flex justify-end gap-3 bg-slate-50/30">
                    <button wire:click="closeCreateModal"
                        class="px-8 py-3.5 bg-white text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-slate-200 hover:bg-slate-50 transition shadow-sm">Batal</button>
                    <button type="button" x-data x-on:click="
                            let method = @this.get('create_payment_method');
                            let fileInput = document.getElementById('payment_proof_input');
                            
                            if (method === 'transfer') {
                                if (!fileInput || (!fileInput.files[0] && !@this.get('create_payment_proof'))) {
                                    alert('Peringatan: Harap pilih foto bukti transfer terlebih dahulu.');
                                    return;
                                }
                            }
                            @this.call('saveManualRegistration');
                        "
                        wire:loading.attr="disabled"
                        wire:target="saveManualRegistration, create_payment_proof"
                        class="px-8 py-3.5 bg-ksc-blue text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-blue-100 hover:bg-blue-700 transition transform hover:-translate-y-1 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="saveManualRegistration, create_payment_proof">Simpan Pendaftaran</span>
                        <span wire:loading wire:target="saveManualRegistration, create_payment_proof" class="flex items-center gap-2">
                            <x-lucide-loader-2 class="w-4 h-4 animate-spin" />
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- PREMIUM SHARE MODAL (CLIENT-SIDE) --}}
    <div id="share-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-[300] justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-[3rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.14)] border border-slate-100 overflow-hidden">
                {{-- Decorative Background Element --}}
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 bg-ksc-blue/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 bg-ksc-accent/5 rounded-full blur-3xl"></div>

                {{-- Header --}}
                <div class="relative flex items-center justify-between p-8 pb-4">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 italic uppercase tracking-tighter leading-tight">
                            Bagikan Event
                        </h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">Ajak temanmu untuk ikut serta!</p>
                    </div>
                    <button type="button" class="text-slate-400 bg-slate-50 hover:bg-slate-100 hover:text-slate-900 rounded-2xl text-sm w-12 h-12 inline-flex justify-center items-center transition-all duration-300" data-modal-hide="share-modal">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                {{-- Body --}}
                <div class="relative p-8 pt-4">
                    <div class="bg-slate-50/80 backdrop-blur-sm rounded-3xl p-5 mb-8 border border-slate-100/50 shadow-inner">
                        <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest mb-1.5 flex items-center gap-2">
                            <x-lucide-info class="w-3 h-3" /> Nama Event
                        </p>
                        <p class="text-sm font-black text-slate-800 uppercase tracking-tight">{{ $event['nama_event'] }}</p>
                    </div>

                    {{-- Share Cards --}}
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <a href="https://api.whatsapp.com/send?text=Ayo ikut event {{ urlencode($event['nama_event']) }}! Cek detailnya di sini: {{ urlencode(url()->current()) }}" 
                           target="_blank" 
                           class="flex flex-col items-center gap-4 p-6 bg-white border border-emerald-100 rounded-[2.5rem] hover:bg-emerald-50 hover:border-emerald-200 transition-all duration-300 group shadow-sm hover:shadow-md hover:shadow-emerald-100/50">
                            <div class="w-14 h-14 bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-200 group-hover:scale-110 group-active:scale-95 transition-all duration-300">
                                <i class="fab fa-whatsapp text-3xl"></i>
                            </div>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">WhatsApp</span>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                           target="_blank" 
                           class="flex flex-col items-center gap-4 p-6 bg-white border border-blue-100 rounded-[2.5rem] hover:bg-blue-50 hover:border-blue-200 transition-all duration-300 group shadow-sm hover:shadow-md hover:shadow-blue-100/50">
                            <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-xl shadow-blue-200 group-hover:scale-110 group-active:scale-95 transition-all duration-300">
                                <i class="fab fa-facebook-f text-2xl"></i>
                            </div>
                            <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Facebook</span>
                        </a>
                    </div>

                    {{-- Copy Link Area --}}
                    <div x-data="{ 
                        url: '{{ url()->current() }}',
                        copied: false,
                        copy() {
                            navigator.clipboard.writeText(this.url);
                            this.copied = true;
                            setTimeout(() => { this.copied = false }, 2000);
                        }
                    }" class="relative group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 flex items-center gap-2">
                            <x-lucide-link class="w-3 h-3" /> Tautan Langsung
                        </label>
                        <div class="relative bg-slate-900 rounded-3xl p-2 pl-6 flex items-center justify-between shadow-2xl shadow-slate-200">
                            <div class="overflow-hidden">
                                <p class="text-[10px] font-bold text-slate-400 truncate pr-4" x-text="url"></p>
                            </div>
                            <button @click="copy()" 
                                :class="copied ? 'bg-emerald-500 text-white shadow-emerald-200' : 'bg-white text-slate-900 hover:bg-slate-100'"
                                class="whitespace-nowrap px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2 min-w-[110px] justify-center">
                                <template x-if="!copied">
                                    <span class="flex items-center gap-2">Salin</span>
                                </template>
                                <template x-if="copied">
                                    <span class="flex items-center gap-2"><x-lucide-check class="w-3.5 h-3.5" /> Tersalin!</span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Footer Info --}}
                <div class="p-8 pt-0 text-center">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">© 2026 Khafid Swimming Club</p>
                </div>
            </div>
        </div>
    </div>

</div>

@script
<script>
    $wire.on('open-invoice-url', (event) => {
        const url = event.url || (Array.isArray(event) ? event[0].url : null);
        if (url) {
            const a = document.createElement('a');
            a.href = url;
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    });

    $wire.on('reload-page', () => {
        setTimeout(() => {
            window.location.reload();
        }, 20000); // Give time for new tab to open and notification to appear
    });
</script>
@endscript
