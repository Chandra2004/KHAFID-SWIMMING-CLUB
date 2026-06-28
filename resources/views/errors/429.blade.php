<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('/assets/ico/favicon-debug.ico') }}">
    <title>429 - Terlalu Banyak Permintaan | The Framework</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #0d1117;
            color: #c9d1d9;
        }

        .glow-429 {
            text-shadow: 0 0 30px rgba(20, 184, 166, 0.4);
        }
    </style>
</head>

<body
    class="min-h-screen flex items-center justify-center p-6 bg-[radial-gradient(circle_at_50%_50%,_#0f172a_0%,_#0d1117_100%)]">
    <div class="max-w-2xl w-full text-center space-y-8">
        <div class="space-y-4">
            <h1
                class="text-[12rem] font-black text-white glow-429 leading-none tracking-tighter opacity-10 select-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                429</h1>

            <div class="relative">
                <i data-lucide="traffic-cone" class="w-24 h-24 text-teal-500 mx-auto mb-6 animate-bounce"></i>
                <h2 class="text-4xl font-bold text-white tracking-tight italic uppercase">Slow Down!</h2>
                <p class="text-slate-400 text-lg mt-4 max-w-md mx-auto">
                    Terlalu banyak permintaan dalam waktu singkat. Server kami membutuhkan waktu sejenak untuk bernapas. Silakan tunggu beberapa menit.
                </p>
            </div>
        </div>

        <div class="bg-teal-500/5 border border-teal-500/20 rounded-2xl p-6 backdrop-blur-sm max-w-sm mx-auto">
            <div class="text-xs font-black text-teal-500 uppercase tracking-widest mb-2 leading-none">Status: Rate Limited</div>
            <div class="text-sm font-medium text-slate-300">Tindakan Anda telah dibatasi sementara untuk menjaga stabilitas sistem.</div>
        </div>

        <div class="flex items-center justify-center gap-4 pt-4">
            <button onclick="location.reload()"
                class="px-8 py-3 bg-teal-500 text-white font-black rounded-full hover:bg-teal-600 transition-all flex items-center gap-2 shadow-lg shadow-teal-500/20">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Coba Lagi
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
