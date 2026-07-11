@extends('layouts.layout-homepage.app')

@section('homepage-section')
    <section class="relative h-[85vh] flex items-center bg-hero-fasilitas bg-cover bg-center bg-fixed">
    <div class="container mx-auto px-6 relative z-10 pt-20">
        <div class="max-w-3xl">
            <div class="inline-block px-4 py-1.5 bg-ksc-accent/20 backdrop-blur-md border border-ksc-accent/30 rounded-full text-ksc-accent font-bold text-[10px] md:text-xs mb-6 uppercase tracking-widest">
                Premium Facilities
            </div>
            <h1 class="text-3xl md:text-6xl font-bold text-white leading-tight mb-6">
                Standar <span class="text-ksc-accent">Nasional</span><br>Untuk Kenyamanan Anda.
            </h1>
            <p class="text-slate-300 text-sm md:text-xl leading-relaxed">
                Kami percaya bahwa fasilitas yang baik akan mempercepat progres atlet dan memberikan kenyamanan bagi orang tua.
            </p>
        </div>
    </div>
</section>

<!-- Facilitiy Showcase -->
<section class="py-24">
    <div class="container mx-auto px-6 space-y-24">

        <!-- Olympic Pool -->
        <div class="flex flex-col lg:flex-row items-center gap-16 reveal">
            <div class="w-full lg:w-1/2">
                <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl">
                    <img src="{{ url('images/gambar_renang_26.webp') }}" class="w-full h-full object-cover transform hover:scale-105 transition duration-700">
                </div>
            </div>
            <div class="w-full lg:w-1/2">
                <span class="text-ksc-accent font-bold tracking-widest text-xs uppercase mb-4 block underline decoration-2 underline-offset-8 decoration-ksc-blue">Olympic Standard</span>
                <h2 class="text-4xl font-bold text-slate-900 mb-6">Kolam Renang Utama (Olympic Size)</h2>
                <p class="text-slate-600 text-lg mb-8 leading-relaxed">
                    Kolam berukuran 25 x 15 meter dengan kedalaman standar kompetisi nasional. Dilengkapi dengan sistem filtrasi canggih yang menjamin kualitas air tetap prima setiap saat.
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 p-4 bg-white rounded-2xl shadow-sm border border-slate-100">
                        <i data-lucide="droplets" class="text-ksc-blue"></i>
                        <span class="text-sm font-bold text-slate-700">Crystal Clear Water</span>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-white rounded-2xl shadow-sm border border-slate-100">
                        <i data-lucide="thermometer" class="text-ksc-blue"></i>
                        <span class="text-sm font-bold text-slate-700">Temperature Control</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kids Area -->
        <div class="flex flex-col lg:flex-row-reverse items-center gap-16 reveal">
            <div class="w-full lg:w-1/2">
                <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl">
                    <img src="https://images.unsplash.com/photo-1519315901367-f34ff9154487?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover transform hover:scale-105 transition duration-700">
                </div>
            </div>
            <div class="w-full lg:w-1/2">
                <span class="text-ksc-accent font-bold tracking-widest text-xs uppercase mb-4 block underline decoration-2 underline-offset-8 decoration-ksc-blue">Kids Development</span>
                <h2 class="text-4xl font-bold text-slate-900 mb-6">Kolam Pembinaan & Anak</h2>
                <p class="text-slate-600 text-lg mb-8 leading-relaxed">
                    Area khusus untuk pemula dan anak-anak dengan kedalaman yang aman dan air yang jernih. Dirancang untuk menumbuhkan rasa percaya diri anak dalam air.
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 p-4 bg-white rounded-2xl shadow-sm border border-slate-100">
                        <i data-lucide="shield-check" class="text-ksc-blue"></i>
                        <span class="text-sm font-bold text-slate-700">Max Safety</span>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-white rounded-2xl shadow-sm border border-slate-100">
                        <i data-lucide="smile" class="text-ksc-blue"></i>
                        <span class="text-sm font-bold text-slate-700">Fun Learning Env</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Facilities Grid -->
        <div class="grid md:grid-cols-3 gap-8 reveal">
            <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-50 feature-card">
                <div class="w-14 h-14 bg-ksc-light text-ksc-blue rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="square-parking" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Large parking area </h3>
                <p class="text-slate-500 text-sm leading-relaxed">Tempat parkir yang sangat luas, jadi tidak khawatir kehabisan tempat untuk memarkirkan kendaran.</p>
            </div>
            <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-50 feature-card">
                <div class="w-14 h-14 bg-ksc-light text-ksc-blue rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="coffee" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Lounge & Cafe</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Tempat nyaman bagi orang tua untuk menunggu sambil melihat anak berlatih melalui area pandang.</p>
            </div>
            <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-50 feature-card">
                <div class="w-14 h-14 bg-ksc-light text-ksc-blue rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="shower-head" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Clean Locker Rooms</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Ruang ganti yang luas, bersih, dan dilengkapi loker untuk meletakkan barang.</p>
            </div>
        </div>

    </div>
</section>

@endsection


