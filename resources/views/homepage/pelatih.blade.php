@extends('layouts.layout-homepage.app')

@section('homepage-section')
    <section class="relative h-[50vh] md:h-[60vh] flex items-center bg-hero-pelatih bg-cover bg-center">
        <div class="container mx-auto px-6 relative z-10 pt-20">
            <div class="max-w-3xl">
                <div
                    class="inline-block px-4 py-1.5 bg-ksc-accent/20 backdrop-blur-md border border-ksc-accent/30 rounded-full text-ksc-accent font-bold text-[10px] md:text-xs mb-6 uppercase tracking-widest">
                    Expert Coaching Team
                </div>
                <h1 class="text-4xl md:text-7xl font-bold text-white leading-tight mb-6">
                    Mengenal Para <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-ksc-accent to-yellow-200">Arsitek
                        Juara.</span>
                </h1>
                <p class="text-slate-300 text-base md:text-xl leading-relaxed">
                    Dibalik setiap medali, ada dedikasi pelatih profesional yang membimbing dengan teknik, disiplin, dan
                    strategi tepat.
                </p>
            </div>
        </div>
    </section>

    <!-- Stats Bar -->
    {{-- <div class="container mx-auto px-6 -mt-16 relative z-20">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12 grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="text-center border-r border-slate-100 last:border-0">
                <span class="block text-3xl md:text-4xl font-bold text-ksc-blue mb-1">{{ count($mentors) }}+</span>
                <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Pelatih Ahli</span>
            </div>
            <div class="text-center border-r border-slate-100 last:border-0">
                <span class="block text-3xl md:text-4xl font-bold text-ksc-blue mb-1">12</span>
                <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Lisensi Nasional</span>
            </div>
            <div class="text-center border-r border-slate-100 last:border-0">
                <span class="block text-3xl md:text-4xl font-bold text-ksc-blue mb-1">3</span>
                <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Mantan Atlet Nasional</span>
            </div>
            <div class="text-center last:border-0">
                <span class="block text-3xl md:text-4xl font-bold text-ksc-blue mb-1">200+</span>
                <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Atlet Juara</span>
            </div>
        </div>
    </div> --}}

    <!-- Filtering & Search -->
    <section class="pt-24 pb-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                {{-- <div class="flex items-center gap-3 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto" id="filter-bar">
                    <button onclick="goToSlide(0)"
                        class="filter-btn px-6 py-2.5 bg-ksc-blue text-white rounded-xl text-sm font-bold shadow-lg shadow-ksc-blue/20 whitespace-nowrap active">Semua</button>
                    <button onclick="goToSlide(1)"
                        class="filter-btn px-6 py-2.5 bg-white text-slate-600 hover:bg-slate-100 rounded-xl text-sm font-bold border border-slate-200 transition whitespace-nowrap">Head
                        Coach</button>
                    <button onclick="goToSlide(2)"
                        class="filter-btn px-6 py-2.5 bg-white text-slate-600 hover:bg-slate-100 rounded-xl text-sm font-bold border border-slate-200 transition whitespace-nowrap">Junior
                        Dev</button>
                    <button onclick="goToSlide(3)"
                        class="filter-btn px-6 py-2.5 bg-white text-slate-600 hover:bg-slate-100 rounded-xl text-sm font-bold border border-slate-200 transition whitespace-nowrap">Conditioning</button>
                </div> --}}
            </div>
        </div>
    </section>

    <!-- Coaches Grid (Swiper Slide Implementation) -->
    <section class="pb-24">
        <div class="container mx-auto px-6">
            <div class="swiper swiper-coach">
                <div class="swiper-wrapper">

                    <!-- Slide 1: Semua -->
                    <div class="swiper-slide">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                            @foreach ($mentors as $mentor)
                                <div
                                    class="bg-white rounded-[2rem] p-4 shadow-xl shadow-slate-200/50 coach-card-hover group reveal">
                                    <div class="aspect-[4/5] rounded-[1.5rem] overflow-hidden relative mb-6">
                                        <img src="{{ url($mentor['foto_profil']) }}"
                                            class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700">
                                        <div
                                            class="absolute inset-x-0 bottom-0 p-6 bg-gradient-to-t from-ksc-blue to-transparent opacity-0 group-hover:opacity-100 transition duration-500">
                                            <div class="flex justify-center gap-3">
                                                <a href="https://wa.me/{{ $mentor['no_telepon'] }}"
                                                    class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-ksc-accent transition"><i
                                                        data-lucide="phone" class="w-5 h-5"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-2 pb-2 text-center md:text-left">
                                        <h3
                                            class="text-xl font-bold text-slate-900 mb-1 group-hover:text-ksc-blue transition">
                                            {{ $mentor['nama_lengkap'] }}</h3>
                                        <p class="text-ksc-accent font-bold uppercase tracking-widest text-[10px] mb-4">Coach</p>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <!-- Slide 2: Head Coach -->
                    <div class="swiper-slide">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                            <!-- Budi (Head) -->
                            <div class="bg-white rounded-[2rem] p-4 shadow-xl shadow-slate-200/50 coach-card-hover group">
                                <div class="aspect-[4/5] rounded-[1.5rem] overflow-hidden relative mb-6">
                                    <img src="https://images.unsplash.com/photo-1552058544-f2b08422138a?q=80&w=1998&auto=format&fit=crop"
                                        class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700">
                                </div>
                                <div class="px-2 pb-2 text-center md:text-left">
                                    <h3 class="text-xl font-bold text-slate-900 mb-1">Budi Santoso</h3>
                                    <p class="text-ksc-accent font-bold uppercase tracking-widest text-[10px] mb-4">Head
                                        Coach & Founder</p>
                                </div>
                            </div>
                            <!-- Reza (Head) -->
                            <div class="bg-white rounded-[2rem] p-4 shadow-xl shadow-slate-200/50 coach-card-hover group">
                                <div class="aspect-[4/5] rounded-[1.5rem] overflow-hidden relative mb-6">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=2070&auto=format&fit=crop"
                                        class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700">
                                </div>
                                <div class="px-2 pb-2 text-center md:text-left">
                                    <h3 class="text-xl font-bold text-slate-900 mb-1">Reza Kurniawan</h3>
                                    <p class="text-ksc-accent font-bold uppercase tracking-widest text-[10px] mb-4">
                                        Technical Coach</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3: Junior Dev -->
                    <div class="swiper-slide">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                            <!-- Sarah (Junior) -->
                            <div class="bg-white rounded-[2rem] p-4 shadow-xl shadow-slate-200/50 coach-card-hover group">
                                <div class="aspect-[4/5] rounded-[1.5rem] overflow-hidden relative mb-6">
                                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=1976&auto=format&fit=crop"
                                        class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700">
                                </div>
                                <div class="px-2 pb-2 text-center md:text-left">
                                    <h3 class="text-xl font-bold text-slate-900 mb-1">Sarah Wijaya</h3>
                                    <p class="text-ksc-accent font-bold uppercase tracking-widest text-[10px] mb-4">Junior
                                        Dev Specialist</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 4: Conditioning -->
                    <div class="swiper-slide">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                            <!-- Andi (Conditioning) -->
                            <div class="bg-white rounded-[2rem] p-4 shadow-xl shadow-slate-200/50 coach-card-hover group">
                                <div class="aspect-[4/5] rounded-[1.5rem] overflow-hidden relative mb-6">
                                    <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=1974&auto=format&fit=crop"
                                        class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700">
                                </div>
                                <div class="px-2 pb-2 text-center md:text-left">
                                    <h3 class="text-xl font-bold text-slate-900 mb-1">Andi Pratama</h3>
                                    <p class="text-ksc-accent font-bold uppercase tracking-widest text-[10px] mb-4">S&C
                                        Coach</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>




    <!-- Join CTA -->
    <section class="py-24 bg-ksc-blue relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-[100px] -mr-20 -mt-20"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
                <div class="lg:w-2/3">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Ingin Bergabung Sebagai Pelatih?</h2>
                    <p class="text-blue-100 text-lg md:text-xl leading-relaxed">
                        Kami selalu mencari talenta hebat yang memiliki minat tinggi dalam mencetak bibit unggul atlet
                        renang. Jadilah bagian dari ekosistem juara kami!
                    </p>
                </div>
                <div class="lg:w-1/3 flex justify-center lg:justify-end">
                    <a href="/coming-soon"
                        class="px-10 py-5 bg-ksc-accent hover:bg-yellow-500 text-slate-900 rounded-2xl font-bold text-lg transition transform hover:scale-105 shadow-2xl flex items-center gap-3">
                        Kirim CV Sekarang <i data-lucide="mail" class="w-6 h-6"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
