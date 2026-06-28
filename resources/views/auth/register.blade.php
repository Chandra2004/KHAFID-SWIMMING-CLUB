@extends('layouts.layout-auth.app')
@section('auth-section')
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-white mb-2 uppercase tracking-tight">Register</h1>
        <p class="text-slate-400">Lengkapi data diri Anda untuk memulai perjalanan prestasi.</p>
    </div>

    <form class="space-y-6" action="{{ url('/register/process') }}" method="POST">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 pl-1">Username</label>
            <div class="relative">
                <x-lucide-user class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" />
                <input name="username" value="{{ old('username') }}" type="text" placeholder="username123"
                    class="w-full bg-white/5 border @error('username') border-red-500 @else border-white/10 @enderror rounded-2xl px-12 py-3.5 text-white outline-none focus:ring-2 focus:ring-ksc-blue focus:border-transparent transition">
            </div>
            @error('username')
                <p class="text-red-500 text-xs mt-2 pl-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 pl-1">Alamat Email</label>
            <div class="relative">
                <x-lucide-mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" />
                <input name="email" value="{{ old('email') }}" type="email" placeholder="email@contoh.com"
                    class="w-full bg-white/5 border @error('email') border-red-500 @else border-white/10 @enderror rounded-2xl px-12 py-3.5 text-white outline-none focus:ring-2 focus:ring-ksc-blue focus:border-transparent transition">
            </div>
            @error('email')
                <p class="text-red-500 text-xs mt-2 pl-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid md:grid-cols-2 gap-6">
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
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-2 pl-1">Konfirmasi Sandi</label>
                <div class="relative group" x-data="{ show: false }">
                    <x-lucide-lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 group-focus-within:text-ksc-blue transition-colors" />
                    <input name="password_confirmation" :type="show ? 'text' : 'password'" placeholder="••••••••"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-12 py-3.5 text-white outline-none focus:ring-2 focus:ring-ksc-blue focus:border-transparent transition">
                    <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                        <template x-if="!show">
                            <x-lucide-eye class="w-5 h-5" />
                        </template>
                        <template x-if="show">
                            <x-lucide-eye-off class="w-5 h-5" />
                        </template>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2 py-2">
            <div class="flex items-start gap-3">
                <input name="terms" type="checkbox" id="terms"
                    class="mt-1 w-5 h-5 rounded border-white/10 bg-white/5 checked:bg-ksc-blue transition cursor-pointer @error('terms') border-red-500 @enderror">
                <label for="terms" class="text-sm text-slate-400 cursor-pointer hover:text-white transition">
                    Saya menyetujui <a href="{{ url('terms/syarat-ketentuan.md') }}" class="text-ksc-accent font-bold hover:underline">Syarat & Ketentuan</a>
                    serta Kebijakan Privasi KSC.
                </label>
            </div>
            @error('terms')
                <p class="text-red-500 text-xs mt-1 pl-8">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full py-5 bg-blue-600 hover:bg-green-500 text-white rounded-2xl font-bold shadow-2xl shadow-ksc-blue/30 transition transform hover:-translate-y-1 active:scale-[0.98] text-lg">
            Daftar Sekarang
        </button>
    </form>

    <div class="mt-10 pt-10 border-t border-white/5 text-center">
        <p class="text-slate-400">
            Sudah menjadi member?
            <a href="/login" class="text-ksc-accent font-bold hover:underline underline-offset-4">Masuk Di Sini</a>
        </p>
    </div>
@endsection
