@extends('layouts.app')

@section('title', 'My Profile - TosLengSey Badminton')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="font-display font-black text-3xl text-white">Account Settings</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Manage your contact information and default delivery destination</p>
    </div>

    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl space-y-6">
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- User Avatar & Role Badge -->
            <div class="flex items-center gap-4 pb-6 border-b border-slate-800">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-sky-500 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-sky-600/20">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-display font-bold text-xl text-white">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                    <span class="inline-block mt-1 text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-slate-800 text-sky-400 border border-slate-700">
                        Role: {{ strtoupper($user->role) }}
                    </span>
                </div>
            </div>

            <!-- Profile Details Form -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                    @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Phone (Telegram Contact) *</label>
                    <input type="tel" 
                           inputmode="numeric" 
                           pattern="[0-9]*" 
                           name="phone" 
                           value="{{ old('phone', preg_replace('/[^0-9]/', '', $user->phone ?? '')) }}" 
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Default Delivery Address</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="House #, Street name" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Province / City</label>
                    <input type="text" name="city" value="{{ old('city', $user->city ?? 'Phnom Penh') }}" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                </div>
            </div>

            <!-- Password Change Section (Optional) -->
            <div class="pt-4 border-t border-slate-800 space-y-4">
                <h4 class="font-display font-bold text-sm text-white">Change Password (Leave blank to keep current)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">New Password</label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:border-sky-400 transition">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-display font-black text-sm tracking-wide shadow-lg shadow-sky-600/20 transition">
                Save Profile Changes
            </button>
        </form>
    </div>
</div>
@endsection
