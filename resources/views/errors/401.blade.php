<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('/assets/ico/favicon-debug.ico') }}">
    <title>401 - Diperlukan Otentikasi | The Framework</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #0d1117;
            color: #c9d1d9;
        }

        .glow-auth {
            text-shadow: 0 0 30px rgba(168, 85, 247, 0.4);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-xl w-full text-center space-y-8">
        <div class="relative inline-block">
            <div class="absolute inset-0 bg-purple-500/20 blur-3xl rounded-full"></div>
            <i data-lucide="user-x" class="w-32 h-32 text-purple-500 relative mx-auto animate-pulse"></i>
        </div>

        <div class="space-y-4">
            <h1 class="text-8xl font-black text-white glow-auth tracking-tighter">401</h1>
            <h2 class="text-2xl font-bold text-slate-200">Otentikasi Diperlukan</h2>
            <p class="text-slate-400 leading-relaxed">
                Maaf, Anda harus login terlebih dahulu untuk mengakses halaman ini. Sesi Anda mungkin telah berakhir atau Anda mencoba mengakses area terproteksi tanpa identitas yang valid.
            </p>
        </div>

        <div class="flex items-center justify-center gap-4 pt-4">
            <a href="{{ url('/login') }}"
                class="px-8 py-3 bg-purple-500 text-white font-black rounded-full hover:bg-purple-600 transition-all flex items-center gap-2 shadow-lg shadow-purple-500/20">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                Login Sekarang
            </a>
            <a href="{{ url('/') }}"
                class="px-8 py-3 bg-slate-900 border border-slate-700 text-white font-black rounded-full hover:bg-slate-800 transition-all flex items-center gap-2">
                <i data-lucide="home" class="w-4 h-4"></i>
                Beranda
            </a>
        </div>

        <div class="text-xs text-slate-600 pt-8 font-mono">
            IP: {{ request()->ip() }} | {{ date('Y-m-d H:i:s') }}
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
