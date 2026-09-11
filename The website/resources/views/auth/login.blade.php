@extends('layouts.app')

@section('title', 'Sign In - TosLengSey Badminton')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-white/5 border border-sky-500/30 mx-auto flex items-center justify-center p-2 shadow-lg shadow-sky-500/10 mb-3">
                <img src="{{ route('brand.logo') }}" alt="TosLengSey Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="font-display font-black text-2xl text-white tracking-tight">Customer Sign In</h1>
            <p class="text-xs text-slate-400 mt-1">Access your racquet stringing preferences and order history</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                <div class="relative">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 pl-10 focus:outline-none focus:border-sky-400 transition" placeholder="you@example.com">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                </div>
                @error('email')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 pl-10 focus:outline-none focus:border-sky-400 transition" placeholder="••••••••">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                </div>
                @error('password')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-800 text-sky-500 focus:ring-0">
                    <span>Remember me on this device</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-display font-black text-sm tracking-wide shadow-lg shadow-sky-600/20 hover:scale-[1.01] transition flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Sign In</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-800 text-center text-xs text-slate-400">
            Don't have an account? <a href="{{ route('register') }}" class="text-sky-400 font-bold hover:underline">Create an account</a>
        </div>
    </div>
</div>
@endsection
