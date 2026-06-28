@extends('layouts.layout-homepage.app')

@section('homepage-section')
<section class="pt-32 pb-24">
    <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-6">Ayo <span class="text-ksc-blue">Berdiskusi</span> Dengan Kami</h1>
            <p class="text-slate-600 text-lg leading-relaxed">Punya pertanyaan seputar pendaftaran, jadwal latihan, atau program atlet profesional? Tim kami siap membantu Anda.</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 mb-24">
            <a href="https://wa.me/6281234567890" class="bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 contact-card flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="message-circle" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">WhatsApp</h3>
                <p class="text-slate-500 text-sm mb-4">Konsultasi cepat via chat</p>
                <span class="text-ksc-blue font-bold">+62 812-3456-7890</span>
            </a>
            <a href="mailto:info@ksc-club.com" class="bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 contact-card flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-blue-100 text-ksc-blue rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="mail" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Email Official</h3>
                <p class="text-slate-500 text-sm mb-4">Untuk keperluan administratif</p>
                <span class="text-ksc-blue font-bold">khafid.swimmingclub16@gmail.com</span>
            </a>
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 contact-card flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-orange-100 text-ksc-accent rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="map-pin" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Lokasi Pool</h3>
                <p class="text-slate-500 text-sm mb-4">Kunjungi kami langsung</p>
                <span class="text-ksc-blue font-bold text-sm">Jl. Bypass Krian No.KM.30 Sidomukti, Kraton, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur
                    61262</span>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row">
            <div class="lg:w-1/2 p-8 md:p-12">
                <h2 class="text-3xl font-bold text-slate-900 mb-8">Kirim Pesan</h2>
                <form class="space-y-6" action="{{ url('/contact/process') }}" method="POST">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                            <input name="nama_lengkap" value="{{ old('nama_lengkap') }}" type="text" placeholder="Contoh: Budi Santoso" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 focus:ring-2 focus:ring-ksc-blue outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                            <input name="email" value="{{ old('email') }}" type="email" placeholder="email@anda.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 focus:ring-2 focus:ring-ksc-blue outline-none transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Subjek</label>
                        <select name="subjek" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 focus:ring-2 focus:ring-ksc-blue outline-none transition">
                            <option disabled selected>pilih opsi</option>
                            <option value="Pendaftaran Member Baru">Pendaftaran Member Baru</option>
                            <option value="Pendaftaran Kursus Renang Anak">Pendaftaran Kursus Renang Anak</option>
                            <option value="Pendaftaran Kursus Renang Dewasa">Pendaftaran Kursus Renang Dewasa</option>
                            <option value="Program Atlet Profesional">Program Atlet Profesional</option>
                            <option value="Informasi Jadwal Latihan">Informasi Jadwal Latihan</option>
                            <option value="Lamaran Kerja (Pelatih/Staff)">Lamaran Kerja (Pelatih/Staff)</option>
                            <option value="Kerja Sama & Sponsorship">Kerja Sama & Sponsorship</option>
                            <option value="Masukan & Saran">Masukan & Saran</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pesan Anda</label>
                        <textarea name="pesan" rows="4" placeholder="Tuliskan detail pertanyaan Anda di sini..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 focus:ring-2 focus:ring-ksc-blue outline-none transition">{{ old('pesan') }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-4 bg-ksc-blue hover:bg-ksc-dark text-white rounded-xl font-bold shadow-lg shadow-ksc-blue/30 transition transform hover:-translate-y-1">Kirim Sekarang</button>
                </form>
            </div>
            <div class="lg:w-1/2 bg-slate-200 min-h-[400px]">
                <!-- Link map placeholder -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.5750565817048!2d112.57597569999999!3d-7.401417550000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7809a962c78bdf%3A0xf98856b79a2fc9a3!2sJl.%20Bypass%20Krian%20No.KM.%2030%2C%20Sidomukti%2C%20Kraton%2C%20Kec.%20Krian%2C%20Kabupaten%20Sidoarjo%2C%20Jawa%20Timur%2061262!5e0!3m2!1sen!2sid!4v1771477330831!5m2!1sen!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</section>


@endsection
   
