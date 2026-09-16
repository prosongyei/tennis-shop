<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TosLengSey - Authentic Badminton Racquets & Gear')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
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
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        accent: {
                            400: '#38bdf8',
                            500: '#0284c7',
                            600: '#2563eb',
                        },
                        dark: {
                            800: '#0f172a',
                            900: '#090d16',
                            950: '#040711',
                        },
                        bakong: '#E1232A'
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- QRious QR Code Generator Library for Fast Local Rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

    <style>
        html, body { max-width: 100%; overflow-x: hidden; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-display { font-family: 'Outfit', sans-serif; }
        .glass-nav {
            background: rgba(11, 15, 25, 0.90);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-blue {
            box-shadow: 0 0 25px rgba(2, 132, 199, 0.25);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased selection:bg-sky-500 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-blue-700 via-sky-600 to-indigo-700 text-white text-xs font-semibold py-2 px-4 text-center tracking-wide flex items-center justify-center gap-2 shadow-sm">
        <i data-lucide="award" class="w-3.5 h-3.5 text-sky-200"></i>
        <span>TOSLENGSEY BADMINTON STORE: 100% GENUINE RACQUETS & TOURNAMENT GEAR | FREE DELIVERY OVER $50 IN PHNOM PENH</span>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-50 glass-nav transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-2 h-20 min-w-0">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 group min-w-0 shrink">
                    <img src="{{ route('brand.logo') }}?v=2" alt="TosLengSey Logo" class="w-8 h-8 sm:w-10 sm:h-10 shrink-0 rounded-full object-contain shadow-md shadow-sky-500/20 border border-sky-400/40 group-hover:scale-105 transition bg-slate-900 p-0.5">
                    <div class="min-w-0">
                        <span class="font-display font-black text-lg sm:text-2xl tracking-tight text-white group-hover:text-sky-400 transition block leading-tight truncate">TosLengSey</span>
                        <p class="hidden sm:block text-[8px] uppercase font-bold tracking-tight text-slate-400 whitespace-nowrap">Authentic Badminton Store</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-4 lg:gap-6 text-sm font-semibold text-slate-300">
                    <a href="{{ route('shop.catalog') }}" class="hover:text-sky-400 transition whitespace-nowrap {{ request()->routeIs('shop.catalog') && !request('category') ? 'text-sky-400' : '' }}">All</a>
                    <a href="{{ route('shop.catalog', ['category' => 'badminton-rackets']) }}" class="hover:text-sky-400 transition whitespace-nowrap {{ request('category') === 'badminton-rackets' ? 'text-sky-400' : '' }}">Rackets</a>
                    <a href="{{ route('shop.catalog', ['category' => 'badminton-shoes']) }}" class="hover:text-sky-400 transition whitespace-nowrap {{ request('category') === 'badminton-shoes' ? 'text-sky-400' : '' }}">Shoes</a>
                    <a href="{{ route('shop.catalog', ['category' => 'shuttlecocks']) }}" class="hover:text-sky-400 transition whitespace-nowrap {{ request('category') === 'shuttlecocks' ? 'text-sky-400' : '' }}">Shuttlecocks</a>
                    <a href="{{ route('orders.track') }}" class="hover:text-sky-400 transition whitespace-nowrap flex items-center gap-1 {{ request()->routeIs('orders.track') ? 'text-sky-400' : '' }}">
                        <i data-lucide="truck" class="w-4 h-4 text-sky-400"></i> Track Order
                    </a>
                    <a href="{{ route('orders.my') }}" class="hover:text-sky-400 transition whitespace-nowrap flex items-center gap-1 {{ request()->routeIs('orders.my') ? 'text-sky-400' : '' }}">
                        <i data-lucide="package-check" class="w-4 h-4 text-sky-400"></i> My Orders
                    </a>
                </nav>

                <!-- Search, User, Wishlist, Cart Action Buttons -->
                <div class="flex items-center gap-1 sm:gap-2.5 shrink-0">
                    <!-- Quick Search Bar -->
                    <form action="{{ route('shop.catalog') }}" method="GET" class="hidden lg:flex items-center relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search Yonex, Victor, shoes..." class="bg-slate-900/90 text-xs text-white border border-slate-700/80 rounded-full pl-9 pr-4 py-2 w-44 focus:w-56 focus:outline-none focus:border-sky-400 transition-all placeholder:text-slate-500">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none"></i>
                    </form>

                    <!-- Wishlist Link -->
                    <a href="{{ route('wishlist.index') }}" class="relative hidden sm:flex p-2 sm:p-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white transition items-center gap-1.5" title="View Saved Wishlist">
                        <i data-lucide="heart" class="w-5 h-5 text-rose-400"></i>
                        <span id="nav-wishlist-badge" class="bg-rose-500 text-white font-bold text-[11px] px-1.5 py-0.5 rounded-full min-w-[19px] text-center {{ ($wishlistCount ?? 0) > 0 ? '' : 'hidden' }}">
                            {{ $wishlistCount ?? 0 }}
                        </span>
                    </a>

                    <!-- Cart Link -->
                    <a href="{{ route('cart.index') }}" class="relative p-2 sm:p-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5" title="View Shopping Cart">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-sky-400"></i>
                        <span id="nav-cart-badge" class="bg-sky-500 text-white font-bold text-[11px] px-1.5 py-0.5 rounded-full min-w-[19px] text-center">
                            {{ auth()->check() && auth()->user()->cart ? auth()->user()->cart->total_quantity : 0 }}
                        </span>
                    </a>

                    <!-- User Account / Login -->
                    @auth
                        <details class="relative" id="user-menu-details">
                            <summary class="flex items-center gap-1 sm:gap-2 p-1 sm:p-1.5 sm:pl-3 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-200 text-sm font-medium transition cursor-pointer list-none select-none">
                                <span class="hidden sm:inline max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <div class="w-8 h-8 rounded-lg bg-sky-600 text-white font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </summary>
                            <!-- Customer Dropdown Menu -->
                            <div class="absolute right-0 top-full pt-1.5 w-56 z-50">
                                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-2">
                                    <div class="px-3 py-2 border-b border-slate-800">
                                        <p class="text-xs text-slate-400">Logged in as</p>
                                        <p class="text-sm font-bold text-white truncate">{{ auth()->user()->email }}</p>
                                    </div>

                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 transition mt-1">
                                            <i data-lucide="layout-dashboard" class="w-4 h-4 text-indigo-400"></i> Admin Console
                                        </a>
                                        <a href="{{ route('pos.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition mt-1">
                                            <i data-lucide="monitor" class="w-4 h-4 text-emerald-400"></i> POS Cashier Terminal
                                        </a>
                                        <div class="my-1 border-t border-slate-800"></div>
                                    @elseif(auth()->user()->isCashier())
                                        <a href="{{ route('pos.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition mt-1">
                                            <i data-lucide="monitor" class="w-4 h-4 text-emerald-400"></i> POS Cashier Terminal
                                        </a>
                                        <div class="my-1 border-t border-slate-800"></div>
                                    @endif

                                    <a href="{{ route('orders.my') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white mt-1">
                                        <i data-lucide="package" class="w-4 h-4 text-sky-400"></i> My Orders
                                    </a>

                                    <a href="{{ route('orders.track') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white">
                                        <i data-lucide="truck" class="w-4 h-4 text-sky-400"></i> Track Deliveries
                                    </a>

                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition mt-1 cursor-pointer">
                                            <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-sm font-semibold text-slate-200 hover:text-white transition">
                            <i data-lucide="user" class="w-4 h-4 text-sky-400"></i> Sign In
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-2.5 sm:px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs sm:text-sm font-bold shadow-lg shadow-sky-600/20 hover:scale-[1.02] transition whitespace-nowrap">
                            Sign Up
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-sky-500/10 border border-sky-500/30 text-sky-400 px-4 py-3 rounded-2xl flex items-center justify-between text-sm">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-sky-400 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-sky-400/60 hover:text-sky-400">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-2xl flex items-center justify-between text-sm">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-400/60 hover:text-rose-400">&times;</button>
            </div>
        </div>
    @endif

    <!-- Main Content Container -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-900 mt-24 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <!-- Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ route('brand.logo') }}?v=2" alt="TosLengSey Logo" class="w-10 h-10 rounded-full object-contain border border-sky-400/40 bg-slate-900 p-0.5">
                        <span class="font-display font-black text-xl text-white">TosLengSey <span class="text-sky-400">PRO</span></span>
                    </div>
                    <p class="text-xs leading-relaxed text-slate-400">
                        Cambodia's premier badminton racket and court gear store. 100% Genuine Yonex, Victor, and Li-Ning racquets with computerized electronic stringing.
                    </p>
                    <div class="flex items-center gap-2 pt-2">
                        <span class="bg-sky-500/10 text-sky-400 border border-sky-500/30 px-2.5 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Authorized Badminton Equipment Retailer
                        </span>
                    </div>
                </div>

                <!-- Quick Categories -->
                <div>
                    <h4 class="font-display font-bold text-white text-base mb-4">Badminton Gear</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('shop.catalog', ['category' => 'badminton-rackets']) }}" class="hover:text-sky-400 transition">Attacking Racquets (Head Heavy)</a></li>
                        <li><a href="{{ route('shop.catalog', ['category' => 'badminton-rackets']) }}" class="hover:text-sky-400 transition">Speed & Control Racquets</a></li>
                        <li><a href="{{ route('shop.catalog', ['category' => 'badminton-shoes']) }}" class="hover:text-sky-400 transition">Power Cushion Court Shoes</a></li>
                        <li><a href="{{ route('shop.catalog', ['category' => 'shuttlecocks']) }}" class="hover:text-sky-400 transition">Tournament Feather Shuttles</a></li>
                        <li><a href="{{ route('shop.catalog', ['category' => 'strings-tension']) }}" class="hover:text-sky-400 transition">Yonex BG80 & BG66 Ultimax Strings</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h4 class="font-display font-bold text-white text-base mb-4">Customer Support</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('orders.track') }}" class="hover:text-sky-400 transition">Track Delivery Status</a></li>
                        <li><a href="{{ route('orders.my') }}" class="hover:text-sky-400 transition">Customer Order History</a></li>
                        <li><a href="{{ route('cart.index') }}" class="hover:text-sky-400 transition">Shopping Cart & Checkout</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-sky-400 transition">Customer Account Login</a></li>
                        <li><a href="{{ route('pos.login') }}" class="hover:text-emerald-400 transition font-medium">Cashier POS Terminal</a></li>
                        <li><a href="{{ route('admin.login') }}" class="hover:text-indigo-400 transition font-medium">Store Admin Console</a></li>
                        <li><span class="text-slate-500">Same-Day Delivery across Phnom Penh</span></li>
                        <li><span class="text-slate-500">1-2 Days Express Nationwide Shipping</span></li>
                    </ul>
                </div>

                <!-- Store Contact -->
                <div>
                    <h4 class="font-display font-bold text-white text-base mb-4">Contact & Location</h4>
                    <ul class="space-y-3 text-xs">
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-sky-400 shrink-0 mt-0.5"></i>
                            <span>#128 St. 2004, Sen Sok, Phnom Penh, Cambodia</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i data-lucide="phone" class="w-4 h-4 text-sky-400 shrink-0"></i>
                            <span>+855 12 888 999 / +855 98 777 666</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i data-lucide="clock" class="w-4 h-4 text-sky-400 shrink-0"></i>
                            <span>Monday - Sunday: 8:00 AM - 9:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-900 mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <p>&copy; {{ date('Y') }} TosLengSey Badminton Store. All rights reserved.</p>
                    <span class="text-slate-700">|</span>
                    <a href="{{ route('pos.login') }}" class="hover:text-emerald-400 transition">Cashier POS</a>
                    <span class="text-slate-700">•</span>
                    <a href="{{ route('admin.login') }}" class="hover:text-indigo-400 transition">Admin Console</a>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <span class="text-slate-400 font-semibold">Accepted Payments:</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300 font-medium">Bakong KHQR</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300 font-medium">Visa</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300 font-medium">MasterCard</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300 font-medium">JCB</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300 font-medium">Cash on Delivery</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global Floating Toast Container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full px-4 sm:px-0"></div>

    <script>
        lucide.createIcons();

        // Customer user menu outside-click listener
        document.addEventListener('click', function(e) {
            const details = document.getElementById('user-menu-details');
            if (!details) return;
            if (details.contains(e.target)) return;
            details.removeAttribute('open');
        });

        // Update cart badge dynamically via API
        function refreshCartBadge(newCount) {
            const badge = document.getElementById('nav-cart-badge');
            if (!badge) return;
            if (newCount !== undefined) {
                badge.innerText = newCount;
                return;
            }
            fetch('{{ route("cart.count") }}')
                .then(res => res.json())
                .then(data => {
                    badge.innerText = data.count || 0;
                })
                .catch(() => {});
        }
        document.addEventListener('DOMContentLoaded', () => refreshCartBadge());

        // Toast Notification Trigger
        function showNotificationToast(title, subtitle, actionUrl, actionText) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto p-4 rounded-2xl bg-slate-900/95 border border-sky-500/40 text-white shadow-2xl backdrop-blur-xl flex items-start gap-3.5 transform transition-all duration-300 translate-y-4 opacity-0';
            
            toast.innerHTML = `
                <div class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-black text-white">${title}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-2">${subtitle}</p>
                    ${actionUrl ? `
                        <div class="mt-2.5 flex items-center gap-2">
                            <a href="${actionUrl}" class="text-[11px] font-bold px-3 py-1 rounded-lg bg-sky-600 hover:bg-sky-500 text-white transition inline-flex items-center gap-1 shadow-md">
                                ${actionText || 'View'} &rarr;
                            </a>
                            <span class="text-[10px] text-slate-500">or keep shopping</span>
                        </div>
                    ` : ''}
                </div>
                <button type="button" class="text-slate-500 hover:text-white text-xs p-1" onclick="this.parentElement.remove()">&times;</button>
            `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('translate-y-4', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 4500);
        }

        // Wishlist Toggle Function
        function toggleWishlist(productId, btnElement) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`/wishlist/toggle/${productId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update header badge
                    const badge = document.getElementById('nav-wishlist-badge');
                    if (badge) {
                        badge.innerText = data.count;
                        if (data.count > 0) {
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }

                    // Update all buttons referencing this product
                    document.querySelectorAll(`[data-wishlist-id="${productId}"]`).forEach(btn => {
                        const icon = btn.querySelector('svg') || btn.querySelector('i');
                        if (data.added) {
                            btn.classList.add('text-rose-500', 'fill-rose-500');
                            btn.classList.remove('text-slate-400');
                            if (icon) icon.classList.add('fill-current');
                        } else {
                            btn.classList.remove('text-rose-500', 'fill-rose-500');
                            btn.classList.add('text-slate-400');
                            if (icon) icon.classList.remove('fill-current');
                        }
                    });

                    // Toast feedback
                    showNotificationToast(
                        data.added ? 'Saved to Wishlist!' : 'Removed from Wishlist',
                        data.message,
                        '{{ route("wishlist.index") }}',
                        'View Wishlist'
                    );

                    // If currently on wishlist page and item was removed, remove the card smoothly
                    if (window.location.pathname.includes('/wishlist') && !data.added && btnElement) {
                        const card = btnElement.closest('.rounded-3xl');
                        if (card) {
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.95)';
                            setTimeout(() => card.remove(), 250);
                        }
                    }
                }
            })
            .catch(err => console.error('Wishlist error:', err));
        }

        // Global AJAX Add to Cart Handler
        document.addEventListener('submit', function(e) {
            const form = e.target.closest('.ajax-add-to-cart-form');
            if (!form) return;

            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalContent = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="animate-spin text-xs">⏳</span> Adding...';
            }

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                    lucide.createIcons();
                }

                if (data.success) {
                    refreshCartBadge(data.cart_count);
                    showNotificationToast(
                        'Added to Cart!',
                        data.message + ' You can continue shopping or view your bag.',
                        '{{ route("cart.index") }}',
                        'View Cart & Checkout'
                    );
                } else if (data.message) {
                    alert(data.message);
                }
            })
            .catch(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }
                form.submit(); // fallback to normal submit if fetch fails
            });
        });

        // Flash message toast trigger from server if present
        @if(session('cart_toast'))
            document.addEventListener('DOMContentLoaded', function() {
                const toastData = @json(session('cart_toast'));
                showNotificationToast(
                    'Added to Cart!',
                    toastData.message + ' Keep browsing or checkout whenever you are ready.',
                    '{{ route("cart.index") }}',
                    'View Cart & Pay'
                );
            });
        @endif
    </script>
    @yield('scripts')
</body>
</html>
