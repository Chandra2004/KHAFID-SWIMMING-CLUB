@extends('layouts.layout-auth.app')
@section('auth-section')
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-white mb-2 uppercase tracking-tight">Reset Kata Sandi</h1>
        <p class="text-slate-400">Masukkan kata sandi baru Anda di bawah ini.</p>
    </div>

    <form class="space-y-6" action="{{ url('/reset-password/' . $data['uid'] . '/process') }}" method="POST">
        @csrf
        <div class="space-y-2">
            <input name="token" type="hidden" value="{{ $data['token'] }}">
            <input name="email" type="hidden" value="{{ $data['email'] }}">
        </div>
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest pl-1">Kata Sandi Baru</label>
            <div class="relative group" x-data="{ show: false }">
                <x-lucide-lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 group-focus-within:text-ksc-blue transition-colors" />
                <input name="password" :type="show ? 'text' : 'password'" placeholder="********"
                    class="w-full bg-white/5 border @error('password') border-red-500 @else border-white/10 @enderror rounded-2xl px-12 py-3.5 text-white placeholder:text-slate-600 outline-none focus:ring-2 focus:ring-ksc-blue focus:border-transparent transition-all hover:bg-white/10"
                    required>
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
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest pl-1">Konfirmasi Kata Sandi Baru</label>
            <div class="relative group" x-data="{ show: false }">
                <x-lucide-lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 group-focus-within:text-ksc-blue transition-colors" />
                <input name="password_confirm" :type="show ? 'text' : 'password'" placeholder="********"
                    class="w-full bg-white/5 border @error('password_confirm') border-red-500 @else border-white/10 @enderror rounded-2xl px-12 py-3.5 text-white placeholder:text-slate-600 outline-none focus:ring-2 focus:ring-ksc-blue focus:border-transparent transition-all hover:bg-white/10"
                    required>
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                    <template x-if="!show">
                        <x-lucide-eye class="w-5 h-5" />
                    </template>
                    <template x-if="show">
                        <x-lucide-eye-off class="w-5 h-5" />
                    </template>
                </button>
            </div>
            @error('password_confirm')
                <p class="text-red-500 text-xs mt-2 pl-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full py-4 bg-ksc-blue hover:bg-ksc-dark text-white rounded-2xl font-bold shadow-xl shadow-ksc-blue/20 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 group">
            <span>Reset Kata Sandi</span>
            <i data-lucide="send" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
        </button>
    </form>
@endsection
