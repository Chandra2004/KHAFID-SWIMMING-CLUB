<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <link rel="shortcut icon" href="{{ url('/assets/ico/favicon.ico') }}" type="image/x-icon">
    <title>{{ $title ?? 'Khafid Swimming Club (KSC) - Official Website' }}</title>

    {{-- SEO Meta Tags --}}
    @if (request()->is('detail-event/*/*'))
        @include('layouts.layout-partials.meta-detail')
    @else
        @include('layouts.layout-partials.meta-homepage')
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        ksc: {
                            white: '#ffffff',
                            blue: '#1e40af',
                            dark: '#1e3a8a',
                            accent: '#f59e0b',
                            light: '#eff6ff',
                        }
                    },
                    backgroundImage: {
                        'hero-pattern': "linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(30, 58, 138, 0.6)), url('{{ url('/images/gambar_renang_2.webp') }}')",
                        'hero-about': "linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(30, 58, 138, 0.6)), url('{{ url('/images/gambar_renang_23.webp') }}')",
                        'hero-pelatih': "linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(30, 58, 138, 0.7)), url('{{ url('/images/gambar_renang_18.webp') }}')",
                        'hero-lomba': "linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(30, 58, 138, 0.7)), url('{{ url('/images/gambar_renang_20.webp') }}')",
                        'hero-fasilitas': "linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(30, 58, 138, 0.7)), url('{{ url('/images/gambar_renang_26.webp') }}')",
                        'hero-gallery': "linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(30, 58, 138, 0.7)), url('{{ url('/images/gambar_renang_12.webp') }}')",
                    }
                }
            }
        }
    </script>

    <style>
        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Utilitas untuk Masonry Gallery yang rapi */
        .masonry-grid {
            column-count: 1;
            column-gap: 1rem;
        }

        @media (min-width: 640px) {
            .masonry-grid {
                column-count: 2;
            }
        }

        @media (min-width: 1024px) {
            .masonry-grid {
                column-count: 4;
            }
        }

        .break-inside-avoid {
            break-inside: avoid;
            margin-bottom: 1rem;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .event-card-hover {
            transition: all 0.3s ease;
        }
        .event-card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-700 font-sans antialiased">
    @include('layouts.layout-partials.notification')
    @include('layouts.layout-homepage.navbar')
    @yield('homepage-section')
    @include('layouts.layout-homepage.footer')

    <script src="{{ asset('js/previewImage.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    @livewireScripts
</body>

</html>
