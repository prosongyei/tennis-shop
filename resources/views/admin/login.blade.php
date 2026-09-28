<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Console Sign-In - TosLengSey Operations</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased font-sans">
    <div class="max-w-md w-full">
        <!-- Portal Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-sky-500/30 p-1.5 mx-auto flex items-center justify-center mb-3 shadow-lg shadow-sky-500/10 overflow-hidden">
                <img src="{{ route('brand.logo') }}?v=2" alt="TosLengSey Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white font-['Outfit']">STORE MANAGEMENT PORTAL</h1>
            <p class="text-xs text-slate-400 mt-1">TosLengSey Operations & Inventory Back-Office</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">
            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Administrator Email</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@badminton.com" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 pl-10 focus:outline-none focus:border-indigo-500 transition">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                    </div>
                    @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Administrative Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full bg-slate-950 text-white text-sm border border-slate-800 rounded-xl px-4 py-3 pl-10 pr-10 focus:outline-none focus:border-indigo-500 transition">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-white transition focus:outline-none" title="Show or hide password" aria-label="Toggle password visibility">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-950 text-indigo-500 focus:ring-indigo-500" checked>
                        <span>Keep me logged in</span>
                    </label>
                    <span class="text-slate-500">Restricted Access</span>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/20 hover:scale-[1.01] transition flex items-center justify-center gap-2 mt-2 cursor-pointer">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Access Executive Console</span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800 text-center text-xs text-slate-500 flex items-center justify-between">
                <a href="{{ route('home') }}" class="text-slate-400 hover:text-sky-400 transition flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Storefront
                </a>
                <span>Authorized Personnel Only</span>
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

        lucide.createIcons();
    </script>
</body>
</html>
