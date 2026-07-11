@extends('layouts.layout-homepage.app')

@section('homepage-section')
    <section class="relative h-[85vh] flex items-center bg-hero-about bg-cover bg-center bg-fixed">
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 to-slate-900/40"></div>
        <div class="container mx-auto px-6 relative z-10 pt-20">
            <div class="max-w-4xl">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 bg-ksc-blue/30 backdrop-blur-md border border-white/20 rounded-full text-ksc-accent font-bold text-sm mb-6 uppercase tracking-[0.2em]">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-ksc-accent opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-ksc-accent"></span>
                    </span>
                    Selamat Datang di
                </div>
                <h1 class="text-6xl md:text-8xl font-black text-white leading-none mb-6 drop-shadow-2xl">
                    KHAFID <br>
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-ksc-accent via-yellow-200 to-ksc-accent">SWIMMING
                        CLUB</span>
                </h1>
                <p class="text-slate-200 text-lg md:text-xl mb-10 leading-relaxed max-w-2xl font-light">
                    Tempat terbaik bagi masyarakat dari berbagai kalangan untuk menguasai seni berenang dan meningkatkan
                    kebugaran melalui program yang terarah.
                </p>
                <div
                    class="flex items-center gap-4 bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10 w-fit">
                    <div class="h-10 w-1 bg-ksc-accent rounded-full shadow-[0_0_15px_rgba(255,255,255,0.5)]"></div>
                    <p class="text-white font-medium italic text-lg text-shadow">"Muridku adalah prioritasku, latihanku
                        adalah ibadahku."</p>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-24 bg-white overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-16">

                <div class="w-full lg:w-1/2 relative">
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-ksc-accent/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-ksc-blue/10 rounded-full blur-3xl"></div>

                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-white">

                        <img src="{{ url('/images/gambar_renang_21.webp') }}"
                            class="w-full object-cover transform hover:scale-105 transition duration-700">
                    </div>

                    <div
                        class="absolute -bottom-6 -right-6 bg-white p-6 rounded-xl shadow-xl border border-slate-100 hidden md:block">
                        <div class="flex items-center gap-4">
                            <div class="bg-green-100 p-3 rounded-full text-green-600">
                                <x-lucide-trophy class="w-8 h-8" />
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-slate-900">50+</h4>
                                <p class="text-sm text-slate-500 font-medium">Medali</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/2">
                    <h2 class="text-4xl font-bold text-slate-900 mb-6 leading-tight">Khafid Swimming Club
                    </h2>
                    <p class="text-slate-600 text-lg mb-8 leading-relaxed">
                        adalah perkumpulan renang yang didirikan pada tahun 2016 untuk memberikan
                        wadah pembelajaran berenang bagi masyarakat. Klub ini berfokus pada pengembangan keterampilan
                        renang, mulai dari teknik dasar hingga kelas lanjutan. Dengan dukungan pelatih berpengalaman dan
                        kerja keras, klub telah berhasil mencetak atlet yang berprestasi di berbagai kejuaraan daerah,
                        sambil menanamkan nilai-nilai sportivitas dan disiplin. KSC terus berkomitmen untuk mengembangkan
                        kemampuan anggotanya dan memperluas jangkauan pembelajaran renang.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="event" class="py-24 bg-slate-50 relative">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-4xl font-bold text-slate-900">Agenda Kompetisi</h2>
                    <p class="text-slate-500 mt-2">Jangan lewatkan kesempatan untuk bersinar.</p>
                </div>
                <a href="{{ url('/events') }}"
                    class="hidden md:flex items-center gap-2 text-ksc-blue font-bold hover:gap-3 transition-all">Lihat
                    Semua
                    Event <x-lucide-arrow-right class="w-4 h-4" /></a>
            </div>

            <div class="grid lg:grid-cols-2 gap-8 mb-12">
                {{-- bagian dibawah ini yang dilooping --}}
                @foreach ($events as $event)
                    <div class="rounded-3xl overflow-hidden relative shadow-2xl cursor-pointer group h-[400px] md:h-[500px]"
                        onclick="openModal('modal-featured')">
                        <img src=" {{ asset($event['banner_event']) }}"
                            class="w-full h-full object-cover transition duration-700 group-hover:scale-105 brightness-[0.6]">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>

                        <div class="absolute bottom-0 left-0 p-6 md:p-10 w-full">
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                @if ($event['tipe_event'] === 'gratis')
                                    <span
                                        class="inline-block bg-blue-500 text-[10px] text-white md:text-xs font-bold px-3 py-1 rounded tracking-wider uppercase">{{ $event['tipe_event'] }}</span>
                                @else
                                    <span
                                        class="inline-block bg-orange-500 text-[10px] text-white md:text-xs font-bold px-3 py-1 rounded tracking-wider uppercase">{{ $event['tipe_event'] }}</span>
                                @endif
                                <span
                                    class="inline-block bg-zinc-800 text-white text-[10px] md:text-xs font-bold px-3 py-1 rounded tracking-wider uppercase">{{ $event['kategori'] }}</span>

                                @if ($event['status_event'] === 'berjalan')
                                    <span
                                        class="inline-block bg-green-500 text-white text-[10px] md:text-xs font-bold px-3 py-1 rounded tracking-wider uppercase">Berjalan</span>
                                @elseif ($event['status_event'] === 'mendatang')
                                    <span
                                        class="inline-block bg-blue-500 text-white text-[10px] md:text-xs font-bold px-3 py-1 rounded tracking-wider uppercase">Mendatang</span>
                                @elseif ($event['status_event'] === 'selesai')
                                    <span
                                        class="inline-block bg-slate-500 text-white text-[10px] md:text-xs font-bold px-3 py-1 rounded tracking-wider uppercase">Selesai</span>
                                @elseif ($event['status_event'] === 'dibatalkan')
                                    <span
                                        class="inline-block bg-rose-500 text-white text-[10px] md:text-xs font-bold px-3 py-1 rounded tracking-wider uppercase">Dibatalkan</span>
                                @else
                                    <span
                                        class="inline-block bg-slate-800 text-white text-[10px] md:text-xs font-bold px-3 py-1 rounded tracking-wider uppercase">{{ $event['status_event'] }}</span>
                                @endif

                            </div>

                            <h3 class="text-2xl md:text-4xl font-bold text-white mb-4 leading-tight">
                                {{ $event['nama_event'] }}
                            </h3>
                            <div
                                class="flex flex-wrap items-center gap-4 md:gap-6 text-white/80 font-medium mb-8 text-sm md:text-base">
                                <span class="flex items-center gap-2"><x-lucide-calendar
                                        class="w-4 h-4 text-ksc-accent" />{{ \Carbon\Carbon::parse($event['tanggal_event'])->translatedFormat('d M Y') }}</span>
                                <span class="flex items-center gap-2"><x-lucide-clock
                                        class="w-4 h-4 text-ksc-accent" />{{ \Carbon\Carbon::parse($event['waktu_event'])->translatedFormat('H:i') }}</span>
                                <span class="flex items-center gap-2"><x-lucide-map-pin
                                        class="w-4 h-4 text-ksc-accent" /> {{ $event['lokasi_event'] }}</span>
                            </div>
                            <a href="{{ url('/detail-event') . '/' . $event['slug'] . '/' . $event['uid'] }}"
                                class="bg-white text-slate-900 font-bold py-2.5 px-6 rounded-full hover:bg-ksc-accent hover:text-white transition shadow-lg inline-flex items-center gap-2 text-sm">
                                Lihat Detail <x-lucide-arrow-up-right class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @endforeach
                {{-- sampai sini --}}
            </div>
    </section>

    <section id="gallery" class="py-24 bg-slate-900 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20 pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-ksc-blue rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-ksc-accent rounded-full blur-[120px]">
            </div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-ksc-accent font-bold tracking-wider text-sm uppercase">Dokumentasi</span>
                <h2 class="text-4xl font-bold text-white mt-2">Momen Terbaik di Air</h2>
                <p class="text-slate-400 mt-4">Setiap detik, setiap kayuhan, setiap kemenangan terekam di sini.</p>
            </div>


            <div class="masonry-grid">
                {{-- ini di looping --}}
                @foreach ($galleries as $gallery)
                    <div class="break-inside-avoid relative group rounded-xl overflow-hidden cursor-pointer">
                        <img src="{{ asset($gallery['foto_event']) }}"
                            class="w-full h-auto object-cover transform transition duration-500 group-hover:scale-110">
                        <div
                            class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition duration-300 flex items-end p-6">
                            <p
                                class="text-white font-bold opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition duration-300">
                                {{ $gallery['nama_foto'] }}</p>
                        </div>
                    </div>
                @endforeach
                {{-- sampai sini --}}
            </div>







            <div class="text-center mt-12">
                <a href="{{ url('galleries') }}"
                    class="inline-flex items-center gap-2 border border-slate-600 text-slate-300 px-6 py-3 rounded-full hover:bg-white hover:text-slate-900 transition font-medium">
                    <x-lucide-image class="w-5 h-5" /> Lihat Galeri Selengkapnya
                </a>
            </div>
        </div>
    </section>
@endsection
