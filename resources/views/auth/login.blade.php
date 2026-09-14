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

        <!-- Demo Accounts Quick Fill Helper -->
        <div class="mb-5 p-3.5 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sky-300 font-bold flex items-center gap-1.5">
                    <i data-lucide="key" class="w-3.5 h-3.5 text-sky-400"></i> Fast Demo Login
                </span>
                <span class="text-slate-400 text-[10px]">Click to auto-fill</span>
            </div>
            <div class="grid grid-cols-3 gap-1.5">
                <button type="button" onclick="fillLoginDemo('customer@badminton.com', 'password123')" class="px-2 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-medium transition text-center cursor-pointer border border-slate-700/60 hover:border-sky-400">
                    Customer
                </button>
                <button type="button" onclick="fillLoginDemo('cashier@badminton.com', 'password123')" class="px-2 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-300 text-[11px] font-medium transition text-center cursor-pointer border border-slate-700/60 hover:border-emerald-400">
                    Cashier
                </button>
                <button type="button" onclick="fillLoginDemo('admin@badminton.com', 'password123')" class="px-2 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-300 text-[11px] font-medium transition text-center cursor-pointer border border-slate-700/60 hover:border-indigo-400">
                    Admin
                </button>
            </div>
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
                    <input type="password" id="password" name="password" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 pl-10 pr-10 focus:outline-none focus:border-sky-400 transition" placeholder="••••••••">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                    <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-white transition focus:outline-none" title="Show or hide password" aria-label="Toggle password visibility">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-800 text-sky-500 focus:ring-0" checked>
                    <span>Remember me on this device</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-display font-black text-sm tracking-wide shadow-lg shadow-sky-600/20 hover:scale-[1.01] transition flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Sign In</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-800 text-center text-xs text-slate-400">
            Don't have an account? <a href="{{ route('register') }}" class="text-sky-400 font-bold hover:underline">Create an account</a>
        </div>
    </div>
</div>

<script>
    function fillLoginDemo(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.innerHTML = `<i data-lucide="${isPassword ? 'eye-off' : 'eye'}" class="w-4 h-4"></i>`;
        if (window.lucide) {
            lucide.createIcons();
        }
    }
</script>
@endsection
