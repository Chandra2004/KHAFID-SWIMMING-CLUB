@extends('layouts.layout-homepage.app')
@section('homepage-section')
    <section class="relative h-[85vh] flex items-center bg-hero-about bg-cover bg-center bg-fixed">
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 to-slate-900/40"></div>
        <div class="container mx-auto px-6 relative z-10 pt-20">
            <div class="max-w-4xl">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 bg-ksc-blue/30 backdrop-blur-md border border-white/20 rounded-full text-ksc-accent font-bold text-sm mb-6 uppercase tracking-[0.2em]">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-ksc-accent opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-ksc-accent"></span>
                    </span>
                    Selamat Datang di Tentang
                </div>
                <h1 class="text-6xl md:text-8xl font-black text-white leading-none mb-6 drop-shadow-2xl">
                    KHAFID <br>
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-ksc-accent via-yellow-200 to-ksc-accent">SWIMMING
                        CLUB</span>
                </h1>
                <p class="text-slate-200 text-lg md:text-xl mb-10 leading-relaxed max-w-2xl font-light">
                    Perkumpulan renang ini bernama “KHAFID SWIMMING CLUB” (KSC), yang berasal dari kata “Khafid”
                    (memelihara), “Swimming” (berenang), dan “Club” (perkumpulan). Diharapkan KSC menjadi tempat bagi
                    masyarakat dari berbagai kalangan untuk belajar berenang dan meningkatkan keterampilan berenang.
                </p>

            </div>
        </div>
    </section>

    <section class="py-24 bg-white relative overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row gap-20 items-start">
                <div class="lg:w-1/2 relative reveal">
                    <div class="relative group">
                        <div
                            class="absolute -inset-4 bg-ksc-blue/5 rounded-[40px] transform -rotate-2 group-hover:rotate-0 transition duration-500">
                        </div>
                        <div class="relative rounded-[32px] overflow-hidden shadow-2xl border-8 border-white">
                            <img src="{{ url('images/gambar_renang_1.webp') }}"
                                class="w-full h-[600px] object-cover transition duration-700 group-hover:scale-105">
                            <div
                                class="absolute bottom-6 left-6 bg-white/95 backdrop-blur-md p-6 rounded-2xl shadow-xl border border-slate-100 reveal-delay-1">
                                <span class="block text-5xl font-black text-ksc-blue leading-none">2016</span>
                                <span
                                    class="text-[10px] text-slate-500 font-black uppercase tracking-[0.2em] mt-2 block">Tahun
                                    Pendirian</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:w-1/2">

                    <h2 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-8 leading-tight">
                        Perjalanan
                        <br><span class="text-ksc-blue">Khafid Swimming Club</span>
                    </h2>

                    <div
                        class="prose prose-slate prose-lg max-w-none text-slate-600 leading-relaxed space-y-6 text-justify">
                        <p>
                            Klub Renang ini didirikan pada tahun 2016 dan disahkan oleh Ketua Umum Pengurus Kabupaten
                            Akuatik Indonesia Sidoarjo, yang saat ini dikenal sebagai <strong>Pengurus Kabupaten
                                 Akuatik Indonesia</strong>. Berawal dari kepedulian beberapa pelatih, antara lain
                            Galih Purnama Wulan, S.T., Mochammad Khafid, S.Pd., dan Ahkmad Zainal Fanani, S.T., serta para
                            pecinta olahraga renang terhadap kurangnya wadah pembelajaran renang yang terarah bagi anak-anak
                            dan pemula di lingkungan sekitar, terbentuklah Perkumpulan Renang <strong>KHAFID SWIMMING CLUB
                                (KSC)</strong>.
                        </p>

                        <p>
                            Pada awal berdirinya, kegiatan latihan dilakukan secara sederhana dengan jumlah anggota yang
                            masih terbatas, dengan fokus utama pada pembelajaran dasar berenang, seperti teknik pernapasan,
                            pengenalan air, serta dasar-dasar gaya renang. Seiring berjalannya waktu, minat masyarakat
                            terhadap olahraga renang semakin meningkat.
                        </p>

                        <p>
                            Klub mulai menyusun program latihan yang lebih terstruktur, membagi kelompok berdasarkan usia
                            dan kemampuan, serta meningkatkan kualitas kepelatihan. Dari yang awalnya hanya berfokus pada
                            pembelajaran pemula, klub mulai mengembangkan kelas tingkat lanjutan dan pembinaan prestasi.
                            Kerja keras, disiplin, serta dukungan orang tua dan berbagai pihak membuahkan hasil.
                        </p>

                        <p>
                            Atlet-atlet klub mulai mengikuti kejuaraan tingkat kecamatan dan kabupaten. Prestasi demi
                            prestasi pun mulai diraih, sehingga nama klub semakin dikenal di tingkat daerah. Dengan semangat
                            pembinaan berkelanjutan, klub terus berkembang menjadi wadah yang tidak hanya mengajarkan teknik
                            berenang, tetapi juga menanamkan nilai sportivitas, disiplin, kerja keras, dan mental juara.
                        </p>

                        <p>
                            Hingga saat ini, klub telah berhasil mencetak atlet-atlet yang mampu bersaing dan meraih
                            prestasi di berbagai kejuaraan daerah, serta terus berkomitmen untuk berkembang menuju tingkat
                            yang lebih tinggi.
                        </p>
                    </div>

                    <div class="mt-10 grid grid-cols-2 gap-6 border-t border-slate-100 pt-10">
                        <div class="flex gap-4">
                            <div
                                class="w-12 h-12 bg-blue-50 text-ksc-blue rounded-xl flex items-center justify-center shrink-0">
                                <i data-lucide="award" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-900">Resmi</h5>
                                <p class="text-xs text-slate-500">Terdaftar di Aquatik Indonesia Sidoarjo.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="w-12 h-12 bg-blue-50 text-ksc-blue rounded-xl flex items-center justify-center shrink-0">
                                <i data-lucide="trending-up" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-900">Progresif</h5>
                                <p class="text-xs text-slate-500">Dari pemula hingga kelas prestasi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <section class="py-24 bg-slate-900 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-ksc-blue rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-ksc-accent rounded-full blur-[100px]"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-20 reveal">
                <span class="text-ksc-accent font-bold tracking-[0.3em] text-sm uppercase italic block mb-3">Core
                    Goals</span>
                <h2 class="text-4xl md:text-5xl font-black text-white leading-tight">Tujuan Utama <span
                        class="text-ksc-accent italic">KSC</span></h2>
                <p class="text-slate-400 mt-6 text-lg">Komitmen kami dalam membangun generasi perenang yang tangguh, sehat,
                    dan berprestasi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div
                    class="bg-white/5 backdrop-blur-sm border border-white/10 p-8 rounded-[2.5rem] hover:bg-white/10 transition-all duration-500 group">
                    <div
                        class="w-14 h-14 bg-ksc-blue/20 text-ksc-accent rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-[360deg] transition-all duration-700">
                        <i data-lucide="zap" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Pengembangan Skill</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Meningkatkan kemampuan dan keterampilan berenang
                        anggota secara teknik, fisik, dan mental.</p>
                </div>

                <div
                    class="bg-white/5 backdrop-blur-sm border border-white/10 p-8 rounded-[2.5rem] hover:bg-white/10 transition-all duration-500 group">
                    <div
                        class="w-14 h-14 bg-ksc-blue/20 text-ksc-accent rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-[360deg] transition-all duration-700">
                        <i data-lucide="trophy" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Pembinaan Atlet</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Membina atlet renang sejak usia dini hingga mampu
                        berprestasi di tingkat daerah, nasional, maupun internasional.</p>
                </div>

                <div
                    class="bg-white/5 backdrop-blur-sm border border-white/10 p-8 rounded-[2.5rem] hover:bg-white/10 transition-all duration-500 group">
                    <div
                        class="w-14 h-14 bg-ksc-blue/20 text-ksc-accent rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-[360deg] transition-all duration-700">
                        <i data-lucide="shield-check" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Karakter Juara</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Menanamkan nilai sportivitas, disiplin, kerja keras,
                        dan tanggung jawab dalam setiap sesi latihan.</p>
                </div>

                <div
                    class="bg-white/5 backdrop-blur-sm border border-white/10 p-8 rounded-[2.5rem] hover:bg-white/10 transition-all duration-500 group">
                    <div
                        class="w-14 h-14 bg-ksc-blue/20 text-ksc-accent rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-[360deg] transition-all duration-700">
                        <i data-lucide="smile" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Lingkungan Kondusif</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Menciptakan lingkungan latihan yang aman, nyaman, dan
                        kondusif untuk perkembangan optimal setiap atlet.</p>
                </div>

                <div
                    class="bg-white/5 backdrop-blur-sm border border-white/10 p-8 rounded-[2.5rem] hover:bg-white/10 transition-all duration-500 group">
                    <div
                        class="w-14 h-14 bg-ksc-blue/20 text-ksc-accent rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-[360deg] transition-all duration-700">
                        <i data-lucide="heart-pulse" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Kesehatan Tubuh</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Meningkatkan kebugaran dan kesehatan anggota melalui
                        program latihan yang teratur dan terarah.</p>
                </div>

                <div
                    class="bg-white/5 backdrop-blur-sm border border-white/10 p-8 rounded-[2.5rem] hover:bg-white/10 transition-all duration-500 group">
                    <div
                        class="w-14 h-14 bg-ksc-blue/20 text-ksc-accent rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-[360deg] transition-all duration-700">
                        <i data-lucide="medal" class="w-7 h-7"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Partisipasi Aktif</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Mendorong keikutsertaan dalam kejuaraan sebagai sarana
                        evaluasi dan pengembangan mental bertanding.</p>
                </div>

                <div class="lg:col-span-3 flex justify-center mt-4">
                    <div
                        class="bg-gradient-to-r from-ksc-blue/40 to-ksc-blue/20 backdrop-blur-sm border border-white/10 p-8 rounded-[2.5rem] hover:bg-white/10 transition-all duration-500 group max-w-2xl w-full">
                        <div class="flex flex-col md:flex-row items-center gap-6">
                            <div
                                class="w-14 h-14 bg-ksc-accent text-slate-900 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <i data-lucide="users" class="w-7 h-7"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-white mb-2 text-center md:text-left">Sinergi Tiga Pilar
                                </h4>
                                <p class="text-slate-400 text-sm leading-relaxed text-center md:text-left">Membangun kerja
                                    sama yang baik antara pelatih, atlet, dan orang tua demi mendukung kemajuan prestasi
                                    yang berkelanjutan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-white relative overflow-hidden">
    </section>

    <section class="py-24 bg-white relative overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-stretch">
                <div
                    class="bg-slate-50 p-10 md:p-14 rounded-[3rem] border border-slate-100 flex flex-col items-center text-center shadow-sm">
                    <div
                        class="w-20 h-20 bg-ksc-blue text-white rounded-[2rem] flex items-center justify-center mb-8 shadow-lg shadow-ksc-blue/20">
                        <i data-lucide="eye" class="w-10 h-10"></i>
                    </div>
                    <span class="text-ksc-blue font-black tracking-[0.3em] text-xs uppercase mb-4">Our Vision</span>
                    <h3 class="text-3xl md:text-4xl font-black text-slate-900 mb-6">Visi Kami</h3>
                    <p class="text-slate-600 text-xl leading-relaxed italic font-light">
                        "Menjadi klub renang yang unggul, berprestasi, dan berkarakter, serta mampu melahirkan atlet renang
                        yang kompetitif di tingkat daerah, nasional, hingga internasional."
                    </p>
                </div>

                <div class="bg-ksc-blue p-10 md:p-14 rounded-[3rem] shadow-2xl text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
                        <i data-lucide="target" class="w-64 h-64 -mr-20 -mt-20"></i>
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div
                                class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                                <i data-lucide="target" class="w-6 h-6 text-ksc-accent"></i>
                            </div>
                            <h3 class="text-2xl md:text-3xl font-bold">Misi Strategis</h3>
                        </div>

                        <ul class="space-y-6">
                            @php
                                $misi_items = [
                                    'Menyelenggarakan program latihan yang terstruktur, disiplin, dan berkelanjutan.',
                                    'Mengembangkan potensi atlet secara optimal melalui pembinaan teknik, fisik, mental, dan strategi lomba.',
                                    'Menanamkan nilai sportivitas, disiplin, kerja keras, dan tanggung jawab dalam setiap kegiatan klub.',
                                    'Menyediakan pelatih yang profesional dan kompeten di bidang olahraga renang.',
                                    'Mendorong partisipasi aktif dalam berbagai kejuaraan untuk meningkatkan pengalaman dan prestasi atlet.',
                                    'Membangun lingkungan latihan yang positif, aman, dan suportif bagi seluruh anggota.',
                                    'Menjalin kerja sama dengan orang tua, sekolah, dan institusi olahraga untuk mendukung perkembangan atlet.',
                                ];
                            @endphp

                            @foreach ($misi_items as $misi)
                                <li class="flex items-start gap-4 group">
                                    <div class="mt-1.5 shrink-0">
                                        <div
                                            class="w-5 h-5 rounded-full bg-ksc-accent flex items-center justify-center group-hover:scale-125 transition-transform">
                                            <i data-lucide="check" class="w-3 h-3 text-slate-900 font-bold"></i>
                                        </div>
                                    </div>
                                    <span class="text-blue-50 text-base md:text-lg leading-snug font-medium">
                                        {{ $misi }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-white overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-end mb-16">
                <div>
                    <span class="text-ksc-blue font-black tracking-widest text-xs uppercase mb-3 block">Expert
                        Instructors</span>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-slate-900">Mentor & Coach <br>Profesional</h2>
                </div>
                <a href="{{ url('/coaches') }}"
                    class="mt-6 md:mt-0 px-6 py-3 bg-slate-900 text-white rounded-full font-bold flex items-center gap-3 hover:bg-ksc-blue transition-colors">
                    Lihat Pelatih <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>

            <div class="swiper coachSwiper pb-16">
                <div class="swiper-wrapper">
                    @foreach ($mentors as $mentor)
                        <div class="swiper-slide group">
                            <div class="relative mb-6">
                                <div class="aspect-[4/5] rounded-[32px] overflow-hidden shadow-lg border border-slate-100">
                                    <img src="{{ asset($mentor['foto_profil']) }}"
                                        class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-700 group-hover:scale-110">
                                </div>
                                <div
                                    class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-[80%] bg-white p-4 rounded-2xl shadow-xl text-center border border-slate-50 group-hover:-translate-y-2 transition-transform duration-500">
                                    <h3 class="text-lg font-bold text-slate-900 leading-tight">
                                        {{ $mentor['nama_lengkap'] }}
                                    </h3>
                                    <p class="text-ksc-blue font-black uppercase tracking-tighter text-[10px] mt-1">
                                        Coach</p>
                                    <div
                                        class="flex justify-center gap-3 mt-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="https://www.wa.me/{{ $mentor['no_telepon'] }}" class="text-slate-400 hover:text-green-500 transition"><i
                                                data-lucide="phone" class="w-4 h-4"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination !-bottom-2"></div>
            </div>
        </div>
    </section>

    <section class="py-20 container mx-auto px-6 mb-20">
        <div class="relative bg-ksc-blue rounded-[50px] overflow-hidden p-12 md:p-20 text-center shadow-2xl">
            <div class="absolute top-0 right-0 w-64 h-64 bg-ksc-accent/20 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mb-32 blur-3xl"></div>

            <div class="relative z-10">
                <h2 class="text-4xl md:text-6xl font-black text-white mb-6">Mulai Keajaiban di Dalam Air</h2>
                <p class="text-blue-100 text-lg mb-12 max-w-2xl mx-auto font-light leading-relaxed">
                    Jangan lewatkan kesempatan untuk bergabung dengan komunitas renang paling suportif di Sidoarjo. Kuota
                    terbatas setiap musimnya!
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-6">
                    <a href="/register"
                        class="px-12 py-5 bg-ksc-accent text-slate-900 rounded-full font-black text-lg hover:scale-105 transition-all shadow-xl shadow-yellow-500/20 flex items-center justify-center gap-3">
                        <i data-lucide="user-plus" class="w-6 h-6"></i>
                        Daftar Sekarang
                    </a>
                    <a href="/coming-soon"
                        class="px-12 py-5 bg-white/10 border border-white/20 text-white rounded-full font-bold text-lg hover:bg-white/20 transition-all backdrop-blur-md flex items-center justify-center gap-3">
                        <i data-lucide="message-circle" class="w-6 h-6"></i>
                        Konsultasi Gratis
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
