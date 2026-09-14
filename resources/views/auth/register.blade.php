@extends('layouts.app')

@section('title', 'Create Account - TosLengSey Badminton Store')

@section('content')
<div class="max-w-lg mx-auto px-4 py-16">
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-white/5 border border-sky-500/30 mx-auto flex items-center justify-center p-2 shadow-lg shadow-sky-500/10 mb-3">
                <img src="{{ route('brand.logo') }}" alt="TosLengSey Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="font-display font-black text-2xl text-white tracking-tight">Join TosLengSey Badminton</h1>
            <p class="text-xs text-slate-400 mt-1">Order authentic racquets, track shipments, and customize stringing</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="e.g. Sokha Chan">
                @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="you@email.com">
                    @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number (Digits Only)</label>
                    <input type="tel" 
                           inputmode="numeric" 
                           pattern="[0-9]*" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone') }}" 
                           required 
                           maxlength="15"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                           onpaste="setTimeout(() => { this.value = this.value.replace(/[^0-9]/g, ''); }, 0)"
                           class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" 
                           placeholder="012345678">
                    @error('phone') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="address" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Delivery Address</label>
                <input type="text" id="address" name="address" value="{{ old('address') }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition" placeholder="House #, Street, Khan / District">
                @error('address') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-sky-400 transition" placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-white transition focus:outline-none" title="Show or hide password" aria-label="Toggle password visibility">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-sky-400 transition" placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-white transition focus:outline-none" title="Show or hide password" aria-label="Toggle password confirmation visibility">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-display font-black text-sm tracking-wide shadow-lg shadow-sky-600/20 hover:scale-[1.01] transition mt-2 cursor-pointer">
                Create Free Account
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-800 text-center text-xs text-slate-400">
            Already have an account? <a href="{{ route('login') }}" class="text-sky-400 font-bold hover:underline">Sign In</a>
        </div>
    </div>
</div>

<script>
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

