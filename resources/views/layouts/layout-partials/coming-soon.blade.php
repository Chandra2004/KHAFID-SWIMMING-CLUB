<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Coming Soon | Khafid Swimming Club' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .bg-animate { background: linear-gradient(-45deg, #0f172a, #1e293b, #0f172a, #1e3a8a); background-size: 400% 400%; animation: gradient 15s ease infinite; }
        @keyframes gradient { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .blob { position: absolute; width: 500px; height: 500px; background: rgba(30, 64, 175, 0.2); filter: blur(80px); border-radius: 50%; z-index: -1; animation: float 20s infinite alternate; }
        @keyframes float { from { transform: translate(-10%, -10%); } to { transform: translate(10%, 10%); } }
    </style>
</head>
<body class="bg-animate min-h-screen flex items-center justify-center p-6 overflow-hidden relative">
    <div class="blob top-0 left-0"></div>
    <div class="blob bottom-0 right-0" style="background: rgba(245, 158, 11, 0.1); animation-delay: -5s;"></div>

    <div class="max-w-4xl w-full text-center relative z-10">
        <div class="mb-12 animate-bounce">
            <img src="{{ url('/assets/ico/icon-bar.png') }}" alt="Logo KSC" class="h-24 mx-auto drop-shadow-2xl">
        </div>

        <h2 class="text-ksc-accent font-extrabold tracking-[0.2em] uppercase mb-4 text-sm md:text-base opacity-80">Under Construction</h2>
        <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-8 tracking-tight leading-tight">
            Sesuatu yang <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Luar Biasa</span><br>Sedang Disiapkan.
        </h1>
        
        <p class="text-slate-400 text-lg md:text-xl mb-12 max-w-2xl mx-auto leading-relaxed">
            Kami sedang bekerja keras untuk memberikan pengalaman terbaik bagi Anda. 
            Fitur ini akan segera hadir untuk mendukung prestasi renang Anda.
        </p>

        <!-- Countdown -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16 max-w-3xl mx-auto">
            <div class="glass p-6 rounded-[2.5rem] transform hover:scale-105 transition-all">
                <div id="days" class="text-4xl md:text-5xl font-extrabold text-white mb-1">00</div>
                <div class="text-slate-500 text-xs uppercase tracking-widest font-bold">Hari</div>
            </div>
            <div class="glass p-6 rounded-[2.5rem] transform hover:scale-105 transition-all">
                <div id="hours" class="text-4xl md:text-5xl font-extrabold text-white mb-1">00</div>
                <div class="text-slate-500 text-xs uppercase tracking-widest font-bold">Jam</div>
            </div>
            <div class="glass p-6 rounded-[2.5rem] transform hover:scale-105 transition-all">
                <div id="minutes" class="text-4xl md:text-5xl font-extrabold text-white mb-1">00</div>
                <div class="text-slate-500 text-xs uppercase tracking-widest font-bold">Menit</div>
            </div>
            <div class="glass p-6 rounded-[2.5rem] transform hover:scale-105 transition-all">
                <div id="seconds" class="text-4xl md:text-5xl font-extrabold text-white mb-1">00</div>
                <div class="text-slate-500 text-xs uppercase tracking-widest font-bold">Detik</div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-center gap-6">
            <a href="{{ url('/') }}" class="group px-8 py-4 bg-white text-slate-900 rounded-2xl font-bold flex items-center gap-3 hover:bg-blue-50 transition-all shadow-xl shadow-white/5">
                <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
                Kembali ke Beranda
            </a>
            <div class="flex items-center gap-4">
                <a href="#" class="glass p-4 rounded-xl text-white hover:text-blue-400 transition-colors">
                    <i data-lucide="instagram" class="w-6 h-6"></i>
                </a>
                <a href="#" class="glass p-4 rounded-xl text-white hover:text-blue-400 transition-colors">
                    <i data-lucide="facebook" class="w-6 h-6"></i>
                </a>
            </div>
        </div>
    </div>

    <footer class="absolute bottom-10 w-full text-center text-slate-500 text-sm font-medium tracking-wide">
        &copy; {{ date('Y') }} <span class="text-white">KHAFID SWIMMING CLUB</span>. All Rights Reserved.
    </footer>

    <script>
        lucide.createIcons();

        // Countdown Logic (Set target 30 days from now for demo)
        const targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 30);

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            const d = Math.floor(distance / (1000 * 60 * 60 * 24));
            const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').innerText = d.toString().padStart(2, '0');
            document.getElementById('hours').innerText = h.toString().padStart(2, '0');
            document.getElementById('minutes').innerText = m.toString().padStart(2, '0');
            document.getElementById('seconds').innerText = s.toString().padStart(2, '0');
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
</body>
</html>
