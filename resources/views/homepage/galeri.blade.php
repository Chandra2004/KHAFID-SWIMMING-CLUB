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
                            <div class="group relative overflow-hidden rounded-xl shadow-md cursor-pointer bg-white" 
                                 onclick="openLightbox(this)"
                                 data-photos='@json(array_map(fn($img) => asset($img), $item["foto_lainnya"]))'>
                                <img src="{{ asset($item['foto_event']) }}"
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

    <div id="lightbox" class="fixed inset-0 z-[100] bg-slate-900/95 hidden opacity-0 transition-all duration-300 flex-col items-center justify-center p-4 md:p-6" onclick="closeLightbox()">
        <!-- Close Button -->
        <button class="absolute top-6 right-6 text-white/80 hover:text-white transition-colors z-[110]" onclick="closeLightbox()">
            <i data-lucide="x" class="w-8 h-8"></i>
        </button>
        
        <!-- Lightbox Content Container -->
        <div class="max-w-5xl w-full flex flex-col gap-6" onclick="event.stopPropagation()">
            <!-- Main Image & Navigation View -->
            <div class="relative w-full h-[55vh] md:h-[65vh] bg-slate-950 rounded-3xl overflow-hidden shadow-2xl flex items-center justify-center group border border-slate-800">
                <img id="lightbox-img" src="" class="max-w-full max-h-full object-contain select-none">
                
                <!-- Left Control -->
                <button onclick="prevPhoto(event)" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-10 border border-white/10">
                    <i data-lucide="chevron-left" class="w-6 h-6"></i>
                </button>
                
                <!-- Right Control -->
                <button onclick="nextPhoto(event)" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-10 border border-white/10">
                    <i data-lucide="chevron-right" class="w-6 h-6"></i>
                </button>
            </div>
            
            <!-- Details & Thumbnails -->
            <div class="w-full flex flex-col gap-4 text-white">
                <div class="flex justify-between items-end px-1">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ksc-accent">Momen Terpilih</p>
                        <h4 id="lightbox-title" class="text-lg md:text-xl font-black uppercase tracking-tight"></h4>
                    </div>
                    <span id="lightbox-counter" class="text-xs font-black text-white/60 uppercase tracking-widest"></span>
                </div>
                
                <!-- Thumbnails Grid -->
                <div id="lightbox-thumbnails" class="flex gap-3 overflow-x-auto py-2 filter-container select-none scroll-smooth">
                    <!-- Thumbnails generated by JS -->
                </div>
            </div>
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

        let currentAlbumPhotos = [];
        let currentPhotoIndex = 0;

        function openLightbox(element) {
            const coverImg = element.querySelector('img').src;
            const title = element.querySelector('h4').innerText;
            
            // Get other photos from the data-photos attribute
            const otherPhotos = JSON.parse(element.getAttribute('data-photos') || '[]');
            
            // Combine cover image with other photos
            currentAlbumPhotos = [coverImg, ...otherPhotos];
            currentPhotoIndex = 0;

            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            
            // Render the main image and details
            updateLightboxView(title);
            
            // Render the thumbnails
            renderThumbnails(title);
            
            // Re-bind Lucide icons for arrows
            lucide.createIcons();

            lightbox.classList.remove('opacity-0');
            setTimeout(() => lightbox.classList.add('opacity-100'), 10);
            document.body.style.overflow = 'hidden';
        }

        function updateLightboxView(title) {
            const lImg = document.getElementById('lightbox-img');
            const lTitle = document.getElementById('lightbox-title');
            const lCounter = document.getElementById('lightbox-counter');

            lImg.src = currentAlbumPhotos[currentPhotoIndex];
            lTitle.innerText = title;
            lCounter.innerText = `${currentPhotoIndex + 1} / ${currentAlbumPhotos.length}`;
            
            // Highlight active thumbnail
            document.querySelectorAll('.lightbox-thumb').forEach((thumb, idx) => {
                if (idx === currentPhotoIndex) {
                    thumb.classList.add('border-ksc-accent', 'scale-105', 'active');
                    thumb.classList.remove('opacity-40', 'border-transparent');
                    thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    thumb.classList.remove('border-ksc-accent', 'scale-105', 'active');
                    thumb.classList.add('opacity-40', 'border-transparent');
                }
            });
        }

        function renderThumbnails(title) {
            const container = document.getElementById('lightbox-thumbnails');
            container.innerHTML = '';
            
            currentAlbumPhotos.forEach((photoUrl, idx) => {
                const thumb = document.createElement('div');
                thumb.className = `lightbox-thumb flex-none w-16 h-16 md:w-20 md:h-20 rounded-2xl overflow-hidden border-2 cursor-pointer transition-all duration-300 opacity-40 border-transparent hover:opacity-100`;
                
                const img = document.createElement('img');
                img.src = photoUrl;
                img.className = 'w-full h-full object-cover';
                
                thumb.appendChild(img);
                thumb.addEventListener('click', (e) => {
                    e.stopPropagation();
                    currentPhotoIndex = idx;
                    updateLightboxView(title);
                });
                
                container.appendChild(thumb);
            });
            
            // Initially highlight
            if (document.querySelectorAll('.lightbox-thumb')[0]) {
                document.querySelectorAll('.lightbox-thumb')[0].classList.add('border-ksc-accent', 'scale-105', 'active');
                document.querySelectorAll('.lightbox-thumb')[0].classList.remove('opacity-40', 'border-transparent');
            }
        }

        function prevPhoto(event) {
            event.stopPropagation();
            if (currentAlbumPhotos.length <= 1) return;
            currentPhotoIndex = (currentPhotoIndex - 1 + currentAlbumPhotos.length) % currentAlbumPhotos.length;
            const title = document.getElementById('lightbox-title').innerText;
            updateLightboxView(title);
        }

        function nextPhoto(event) {
            event.stopPropagation();
            if (currentAlbumPhotos.length <= 1) return;
            currentPhotoIndex = (currentPhotoIndex + 1) % currentAlbumPhotos.length;
            const title = document.getElementById('lightbox-title').innerText;
            updateLightboxView(title);
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('opacity-100');
            lightbox.classList.add('opacity-0');
            setTimeout(() => {
                lightbox.classList.add('hidden');
                lightbox.classList.remove('flex');
            }, 300);
            document.body.style.overflow = 'auto';
        }
    </script>
@endsection
