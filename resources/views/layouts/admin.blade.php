<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Console - TosLengSey Badminton Store')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            500: '#0ea5e9',
                            600: '#0284c7',
                        },
                        bakong: '#E1232A'
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- QRious library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-display { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col md:flex-row antialiased">

    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-slate-900/90 border-r border-slate-800 shrink-0 flex flex-col justify-between p-5">
        <div>
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 mb-8 px-2">
                <div class="w-10 h-10 rounded-xl bg-white/10 p-1 flex items-center justify-center border border-slate-700">
                    <img src="{{ route('brand.logo') }}?v=2" alt="TosLengSey" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="font-display font-black text-xl text-white tracking-tight">TOSLENGSEY <span class="text-sky-400">OPS</span></span>
                    <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400">Back-Office Console</p>
                </div>
            </div>

            <!-- Quick POS Register Callout -->
            <a href="{{ route('pos.index') }}" target="_blank" class="flex items-center justify-between gap-3 px-4 py-3 mb-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 font-bold text-sm shadow-lg shadow-emerald-500/20 hover:scale-[1.02] transition">
                <span class="flex items-center gap-2">
                    <i data-lucide="calculator" class="w-5 h-5"></i>
                    POS Register Terminal
                </span>
                <span class="bg-slate-950 text-white text-[10px] font-black px-1.5 py-0.5 rounded">WORKSTATION</span>
            </a>

            <!-- Navigation Links -->
            <nav class="space-y-1.5 text-sm font-medium">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard Overview
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.products.*') ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <i data-lucide="package" class="w-4 h-4"></i> Products & Stock
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i> Orders & Shipments
                    </a>

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <i data-lucide="settings" class="w-4 h-4"></i> Settings & Gateway
                    </a>
                @endif

                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <i data-lucide="external-link" class="w-4 h-4"></i> View Customer Storefront
                </a>
            </nav>
        </div>

        <!-- User Info & Logout -->
        <div class="pt-6 border-t border-slate-800">
            <div class="flex items-center gap-3 px-2 mb-3">
                <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center font-bold text-indigo-400 text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                    <span class="text-[10px] uppercase font-bold text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded">
                        {{ strtoupper(auth()->user()->role) }}
                    </span>
                </div>
            </div>

            <form action="{{ url('/admin/logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-800/80 hover:bg-rose-500/20 text-rose-400 transition">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Exit Console
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Admin Workspace -->
    <div class="flex-1 flex flex-col min-w-0 overflow-auto">
        <!-- Top Flash Alerts -->
        @if(session('success'))
            <div class="m-6 mb-0 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-400">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="m-6 mb-0 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-400/60 hover:text-rose-400">&times;</button>
            </div>
        @endif

        <div class="p-6 md:p-10 flex-1">
            @yield('content')
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    @yield('scripts')
</body>
</html>
