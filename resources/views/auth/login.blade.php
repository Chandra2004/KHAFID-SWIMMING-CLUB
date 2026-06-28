@extends('layouts.layout-auth.app')
@section('auth-section')

    <div class="mb-10">
        <h1 class="text-3xl font-bold text-white mb-2 uppercase tracking-tight">Selamat Datang</h1>
        <p class="text-slate-400">Masuk untuk mengelola profil dan jadwal latihan Anda.</p>
    </div>

    <form class="space-y-5" action="{{ url('/login/process') }}" method="POST">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 pl-1">Email /
                Username</label>
            <div class="relative">
                <x-lucide-user class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" />
                <input name="email" value="{{ old('email') }}" type="text" placeholder="Masukkan email atau username"
                    class="w-full bg-white/5 border @error('email') border-red-500 @else border-white/10 @enderror rounded-2xl px-12 py-3.5 text-white outline-none focus:ring-2 focus:ring-ksc-blue focus:border-transparent transition">
            </div>
            @error('email')
                <p class="text-red-500 text-xs mt-2 pl-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 pl-1">Kata Sandi</label>
            <div class="relative group" x-data="{ show: false }">
                <x-lucide-lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 group-focus-within:text-ksc-blue transition-colors" />
                <input name="password" :type="show ? 'text' : 'password'" placeholder="••••••••"
                    class="w-full bg-white/5 border @error('password') border-red-500 @else border-white/10 @enderror rounded-2xl px-12 py-3.5 text-white outline-none focus:ring-2 focus:ring-ksc-blue focus:border-transparent transition">
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                    <template x-if="!show">
                        <x-lucide-eye class="w-5 h-5" />
                    </template>
                    <template x-if="show">
                        <x-lucide-eye-off class="w-5 h-5" />
                    </template>
                </button>
            </div>
            @error('password')
                <p class="text-red-500 text-xs mt-2 pl-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-xs pb-2">
            <div class="flex items-center gap-3">
                <input type="hidden" name="remember" value="0">
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="remember" value="1" class="sr-only peer">
                    <div class="w-9 h-5 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-slate-400 after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-ksc-blue peer-checked:after:bg-white"></div>
                    <span class="ms-3 text-slate-400 group-hover:text-white transition">Ingat saya</span>
                </label>
            </div>
            <a href="/forgot-password" class="text-ksc-accent hover:text-yellow-500 font-bold transition">Lupa kata
                sandi?</a>
        </div>

        <button type="submit"
            class="w-full py-4 bg-blue-600 hover:bg-green-500 text-white rounded-2xl font-bold shadow-xl shadow-ksc-blue/20 transition transform hover:-translate-y-1 block text-center">Masuk
            Sekarang</button>
    </form>

    <div class="mt-8 pt-8 border-t border-white/5 text-center">
        <p class="text-slate-400 text-sm">
            Belum punya akun?
            <a href="/register" class="text-ksc-accent font-bold hover:underline underline-offset-4">Daftar Di Sini</a>
        </p>
    </div>

    <div class="text-center mt-8">
        <a href="/"
            class="text-slate-500 hover:text-white text-sm flex items-center justify-center gap-2 transition group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition"></i> Kembali ke Beranda
        </a>
    </div>
@endsection
