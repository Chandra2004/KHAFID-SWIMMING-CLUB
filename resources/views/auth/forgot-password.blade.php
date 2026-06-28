@extends('layouts.layout-auth.app')
@section('auth-section')
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-white mb-2 uppercase tracking-tight">Lupa Kata Sandi?</h1>
        <p class="text-slate-400">Jangan khawatir! Masukkan alamat email Anda yang terdaftar dan kami akan mengirimkan
            instruksi untuk mengatur ulang kata sandi.</p>
    </div>

    <form class="space-y-6" action="{{ url('/forgot-password/process') }}" method="POST">
        @csrf
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest pl-1">Alamat Email</label>
            <div class="relative">
                <x-lucide-mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" />
                <input name="email" type="email" value="{{ old('email') }}" placeholder="contoh@email.com"
                    class="w-full bg-white/5 border @error('email') border-red-500 @else border-white/10 @enderror rounded-2xl px-12 py-3.5 text-white placeholder:text-slate-600 outline-none focus:ring-2 focus:ring-ksc-blue focus:border-transparent transition-all hover:bg-white/10">
            </div>
            @error('email')
                <p class="text-red-500 text-xs mt-2 pl-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full py-4 bg-ksc-blue hover:bg-ksc-dark text-white rounded-2xl font-bold shadow-xl shadow-ksc-blue/20 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 group">
            <span>Kirim Instruksi</span>
            <i data-lucide="send" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-white/5 text-center">
        <p class="text-slate-400 text-sm mb-4">Ingat kata sandi Anda?</p>
        <a href="/login"
            class="inline-flex items-center gap-2 text-white font-bold hover:text-ksc-accent transition-colors group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
            Kembali ke Login
        </a>
    </div>
@endsection
