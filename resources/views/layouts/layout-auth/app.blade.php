<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('/assets/ico/favicon.ico') }}" type="image/x-icon">
    <title>{{ $title ?? 'Khafid Swimming Club (KSC) - Official Website' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        ksc: { blue: '#1e40af', accent: '#f59e0b' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#0f172a] min-h-screen flex items-center justify-center p-6 relative overflow-x-hidden py-20">
    @include('layouts.layout-partials.notification')
    <div class="absolute top-0 right-0 w-96 h-96 bg-ksc-blue/20 rounded-full blur-[100px] translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-ksc-accent/10 rounded-full blur-[100px] -translate-x-1/2 translate-y-1/2"></div>

    <div class="w-full max-w-2xl relative z-10">
        <div class="text-center mb-10">
            <a href="/" class="inline-flex items-center gap-2">
                <div class="bg-white p-2 rounded-full">
                    <img src="{{ asset('/assets/ico/icon-bar.png') }}" class="w-[80px]">
                </div>
            </a>
        </div>

        <div class="bg-white/10 backdrop-blur-xl border border-white/10 p-8 md:p-12 rounded-[3rem] shadow-2xl">
            @yield('auth-section')
        </div>

        <div class="text-center mt-8 flex items-center justify-center gap-6 text-sm">
            <a href="{{ url('/terms/bantuan.md') }}" class="text-slate-500 hover:text-slate-300 transition">Bantuan</a>
            <span class="text-slate-700">•</span>
            <a href="{{ url('/terms/privasi.md') }}" class="text-slate-500 hover:text-slate-300 transition">Privasi</a>
        </div>
    </div>
</body>
</html>
