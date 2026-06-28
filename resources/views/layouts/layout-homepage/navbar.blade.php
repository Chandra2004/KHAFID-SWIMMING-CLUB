<nav id="navbar" class="fixed w-full z-50 transition-all duration-300 bg-white py-4">
    <div class="container mx-auto px-6 flex justify-between items-center">
        <a href="/" class="flex items-center gap-2">
            <img src="{{ url('/assets/ico/icon-bar.png') }}" class="w-[60px]">
        </a>

        <div class="hidden md:flex items-center space-x-8 text-sm font-medium text-ksc-blue">
            <a href="{{ url('/') }}"
                class="hover:text-ksc-accent {{ request()->is('/') ? 'text-ksc-accent' : '' }} transition">Home</a>
            <a href="{{ url('/about-us') }}"
                class="hover:text-ksc-accent {{ request()->is('about-us*') ? 'text-ksc-accent' : '' }} transition">Tentang
                Kami</a>
            <a href="{{ url('/coaches') }}"
                class="hover:text-ksc-accent {{ request()->is('coaches*') ? 'text-ksc-accent' : '' }} transition">Pelatih</a>
            <a href="{{ url('/events') }}"
                class="hover:text-ksc-accent {{ request()->is('events*') ? 'text-ksc-accent' : '' }} transition">Event</a>
            <a href="{{ url('/galleries') }}"
                class="hover:text-ksc-accent {{ request()->is('galleries*') ? 'text-ksc-accent' : '' }} transition">Galeri</a>
            <a href="{{ url('/facilities') }}"
                class="hover:text-ksc-accent {{ request()->is('facilities*') ? 'text-ksc-accent' : '' }} transition">Fasilitas</a>
            <a href="{{ url('/contact') }}"
                class="hover:text-ksc-accent {{ request()->is('contact*') ? 'text-ksc-accent' : '' }} transition">Kontak</a>
        </div>

        <div class="hidden md:flex items-center gap-4">
            @if ($user != null)
                <a href="{{ url('/dashboard') }}"
                    class="px-5 py-2.5 bg-ksc-accent hover:bg-yellow-500 text-slate-900 rounded-full font-bold text-sm transition transform hover:scale-105 shadow-lg">
                    Dashboard
                </a>
            @else
                <a href="{{ url('/login') }}" class="text-ksc-blue hover:text-ksc-accent font-medium text-sm transition">Masuk</a>
                <a href="{{ url('register') }}"
                    class="px-5 py-2.5 bg-ksc-accent hover:bg-yellow-500 text-slate-900 rounded-full font-bold text-sm transition transform hover:scale-105 shadow-lg">
                    Daftar
                </a>
            @endif
        </div>

        <button id="mobileMenuBtn" class="md:hidden text-ksc-blue p-2">
            <x-lucide-menu class="w-7 h-7" />
        </button>
    </div>
</nav>

<div id="mobileMenu"
        class="fixed inset-0 z-[60] bg-ksc-white/95 backdrop-blur-xl flex flex-col items-center justify-center gap-8 translate-x-full transition-transform duration-500 md:hidden">
        <button id="closeMenuBtn"
            class="absolute top-6 right-6 text-ksc-blue p-2 hover:bg-white/10 rounded-full transition">
            <x-lucide-x class="w-8 h-8" />
        </button>

        <a href="{{ url('/') }}"
            class="{{ request()->is('/') ? 'text-ksc-accent' : 'text-ksc-blue' }} text-2xl font-bold hover:text-ksc-accent transition">Home</a>
        <a href="{{ url('/about-us') }}"
            class="{{ request()->is('about-us*') ? 'text-ksc-accent' : 'text-ksc-blue' }} text-2xl font-bold hover:text-ksc-accent transition">Tentang
            Kami</a>
        <a href="{{ url('/coaches') }}"
            class="{{ request()->is('coaches*') ? 'text-ksc-accent' : 'text-ksc-blue' }} text-2xl font-bold hover:text-ksc-accent transition">Pelatih</a>
        <a href="{{ url('/events') }}"
            class="{{ request()->is('events*') ? 'text-ksc-accent' : 'text-ksc-blue' }} text-2xl font-bold hover:text-ksc-accent transition">Event</a>
        <a href="{{ url('/galleries') }}"
            class="{{ request()->is('galleries*') ? 'text-ksc-accent' : 'text-ksc-blue' }} text-2xl font-bold hover:text-ksc-accent transition">Galeri</a>
        <a href="{{ url('/facilities') }}"
            class="{{ request()->is('facilities*') ? 'text-ksc-accent' : 'text-ksc-blue' }} text-2xl font-bold hover:text-ksc-accent transition">Fasilitas</a>
        <a href="{{ url('/contact') }}"
            class="{{ request()->is('contact*') ? 'text-ksc-accent' : 'text-ksc-blue' }} text-2xl font-bold hover:text-ksc-accent transition">Kontak</a>

        <div class="flex flex-col items-center gap-4 mt-4 w-full px-12">
            @if ($user != null)
                <a href="{{ url('/dashboard') }}"
                class="w-full py-4 text-center bg-ksc-accent text-slate-900 font-bold rounded-2xl shadow-lg transform active:scale-95 transition">
                    Dashboard
                </a>
            @else
                <a href="{{ url('/login') }}"
                    class="w-full py-4 text-center border border-white/20 text-ksc-blue font-bold rounded-2xl hover:bg-white/10 transition">Masuk</a>
                <a href="{{ url('/register') }}"
                    class="w-full py-4 text-center bg-ksc-accent text-slate-900 font-bold rounded-2xl shadow-lg transform active:scale-95 transition">Daftar
                    Sekarang</a>
            @endif
        </div>
    </div>
