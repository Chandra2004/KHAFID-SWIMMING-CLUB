@extends('layouts.layout-homepage.app')

<style>
    /* Global Style untuk Gallery */
    .filter-container {
        scrollbar-width: none; 
        -ms-overflow-style: none;
    }
    .filter-container::-webkit-scrollbar {
        display: none;
    }

    /* Active State Link */
    .filter-link.active {
        color: #1e40af; /* ksc-blue */
        border-bottom: 3px solid #f59e0b; /* ksc-accent */
    }

    /* Masonry Grid */
    .masonry-grid {
        column-count: 1;
        column-gap: 1.5rem;
    }
    @media (min-width: 640px) { .masonry-grid { column-count: 2; } }
    @media (min-width: 1024px) { .masonry-grid { column-count: 3; } }

    .masonry-item {
        break-inside: avoid;
        margin-bottom: 1.5rem;
    }

    .animate-spin-slow {
        animation: spin 6s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>

@section('homepage-section')
    <section class="relative min-h-[55vh] flex items-center bg-hero-gallery bg-cover bg-center">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        
        <div class="container mx-auto px-6 relative z-10 py-20">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 reveal">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-ksc-accent text-white mb-6 shadow-lg">
                        <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-white">Archive Collection</span>
                    </div>

                    <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter leading-[0.85] uppercase">
                        The <br><span class="text-ksc-accent">Gallery</span>
                    </h1>
                    
                    <p class="mt-8 text-white text-lg md:text-xl font-light leading-relaxed max-w-xl">
                        Arsip visual perjalanan <span class="font-bold text-ksc-accent uppercase">KSC</span>. Menangkap setiap percikan air, semangat, dan medali dalam kualitas tinggi.
                    </p>
                </div>

                <div class="hidden md:block text-right">
                    <span class="text-[10px] font-black tracking-[0.5em] uppercase text-white/40 rotate-90 inline-block origin-right">
                        EST. 2024 — KSC MEDIA
                    </span>
                </div>
            </div>
        </div>
    </section>

    <nav class="sticky top-[72px] z-40 bg-white border-b border-slate-200">
        <div class="container mx-auto px-6">
            <div class="filter-container flex items-center gap-8 overflow-x-auto py-5 snap-x">
                <a href="{{ url('/galleries') }}" 
                   class="filter-link flex-none text-xs uppercase tracking-widest font-black transition-all pb-2 snap-center {{ !$activeEvent ? 'active' : 'text-slate-400 hover:text-slate-600' }}">
                    Semua Momen
                </a>

                @foreach ($events as $event)
                    <a href="{{ url('/galleries?event=' . $event['uid']) }}" 
                       class="filter-link flex-none text-xs uppercase tracking-widest font-black transition-all pb-2 snap-center {{ $activeEvent == $event['uid'] ? 'active' : 'text-slate-400 hover:text-slate-600' }}">
                        {{ $event['nama_event'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </nav>

    <section class="py-16 bg-slate-50 min-h-[60vh] flex flex-col">
        <div class="container mx-auto px-6 flex-grow flex flex-col">
            
            @if(count($galleries) > 0)
                <div class="masonry-grid">
                    @foreach ($galleries as $item)
                        <div class="masonry-item reveal">
                            <div class="group relative overflow-hidden rounded-xl shadow-md cursor-pointer bg-white" onclick="openLightbox(this)">
                                <img src="{{ url('/file/gallery/' . $item['foto_event']) }}" 
                                     alt="{{ $item['nama_event'] }}" 
                                     class="w-full h-auto transition-transform duration-700 group-hover:scale-105"
                                     loading="lazy">
                                
                                <div class="absolute inset-0 bg-ksc-blue/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
                                    <div class="text-white">
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-ksc-accent">Dokumentasi</p>
                                        <h4 class="font-bold text-sm leading-tight">{{ $item['nama_event'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex-grow flex flex-col items-center justify-center py-20 text-center">
                    <div class="reveal">
                        <div class="mb-6">
                            <i data-lucide="image-off" class="w-16 h-16 text-slate-300 mx-auto animate-pulse"></i>
                        </div>
                        <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tighter">Momen Belum Tersedia</h2>
                        <p class="text-slate-500 max-w-sm mx-auto mt-2 font-medium">
                            Tim kami sedang menyiapkan dokumentasi terbaik untuk kategori ini. Mohon menunggu sebentar lagi!
                        </p>
                        <div class="mt-8">
                            <a href="{{ url('/galleries') }}" class="px-8 py-3 bg-ksc-blue text-white text-xs font-bold rounded-lg hover:bg-ksc-dark transition-colors shadow-lg">
                                Lihat Semua Foto
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($pagination['last_page'] > 1)
                <div class="mt-16 flex items-center justify-center gap-4">
                    @if ($pagination['current_page'] > 1)
                        <a href="{{ url('/galleries?page=' . ($pagination['current_page'] - 1) . ($activeEvent ? '&event='.$activeEvent : '')) }}" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-ksc-blue hover:text-white transition-all shadow-sm">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </a>
                    @endif

                    <div class="px-6 py-2 bg-white border border-slate-200 rounded-lg font-bold text-xs text-slate-600 shadow-sm">
                        {{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}
                    </div>

                    @if ($pagination['current_page'] < $pagination['last_page'])
                        <a href="{{ url('/galleries?page=' . ($pagination['current_page'] + 1) . ($activeEvent ? '&event='.$activeEvent : '')) }}" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-ksc-blue hover:text-white transition-all shadow-sm">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <div id="lightbox" class="fixed inset-0 z-[100] bg-slate-900/95 hidden opacity-0 transition-all duration-300 flex-col items-center justify-center p-6" onclick="closeLightbox()">
        <button class="absolute top-10 right-10 text-white" onclick="closeLightbox()">
            <i data-lucide="x" class="w-8 h-8"></i>
        </button>
        <div class="max-w-5xl w-full flex flex-col items-center" onclick="event.stopPropagation()">
            <img id="lightbox-img" src="" class="max-w-full max-h-[80vh] rounded-lg shadow-2xl mb-4">
            <h4 id="lightbox-title" class="text-white text-xl font-bold"></h4>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            // Reveal animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('opacity-100', 'translate-y-0');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.reveal').forEach(el => {
                el.classList.add('opacity-0', 'translate-y-10', 'transition-all', 'duration-700');
                observer.observe(el);
            });

            // Scroll active nav to center
            const activeNav = document.querySelector('.filter-link.active');
            if (activeNav) activeNav.scrollIntoView({ behavior: 'smooth', inline: 'center' });
        });

        function openLightbox(element) {
            const img = element.querySelector('img');
            const title = element.querySelector('h4').innerText;
            const lightbox = document.getElementById('lightbox');
            const lImg = document.getElementById('lightbox-img');
            const lTitle = document.getElementById('lightbox-title');
            
            lImg.src = img.src;
            lTitle.innerText = title;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            setTimeout(() => lightbox.classList.add('opacity-100'), 10);
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('opacity-100');
            setTimeout(() => {
                lightbox.classList.add('hidden');
                lightbox.classList.remove('flex');
            }, 300);
            document.body.style.overflow = 'auto';
        }
    </script>
@endsection
