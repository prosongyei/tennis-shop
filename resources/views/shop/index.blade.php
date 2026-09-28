@extends('layouts.app')

@section('title', 'TosLengSey - Authentic Badminton Racquets, Court Shoes & Pro Gear')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden bg-gradient-to-b from-slate-900 via-slate-950 to-slate-950 border-b border-slate-900">
    <!-- Ambient glow decorative blobs in athletic blue -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 left-10 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-24 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Hero Left Copy -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-400 text-xs font-bold uppercase tracking-wider">
                    <i data-lucide="award" class="w-3.5 h-3.5 text-sky-400"></i>
                    <span>Authorized Yonex, Victor & Li-Ning Retailer</span>
                </div>

                <h1 class="font-display font-black text-4xl sm:text-6xl lg:text-7xl text-white tracking-tight leading-[1.08]">
                    CHAMPIONSHIP RACQUETS & <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-blue-500">PRO COURT GEAR.</span>
                </h1>

                <p class="text-slate-300 text-base sm:text-lg max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Cambodia's trusted destination for 100% authentic tournament racquets, court shoes, and accessories. Experience computerized 6-point electronic stringing calibrated up to 32 lbs.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                    <a href="{{ route('shop.catalog') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-display font-bold text-base shadow-xl shadow-sky-600/25 hover:scale-105 transition">
                        <span>Explore Pro Racquets</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>

                    <a href="{{ route('shop.catalog', ['category' => 'badminton-shoes']) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-white font-semibold text-base transition">
                        <span>Power Cushion Shoes</span>
                    </a>
                </div>

                <!-- Live stats counter -->
                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-800 max-w-lg mx-auto lg:mx-0 text-center lg:text-left">
                    <div>
                        <p class="font-display font-black text-2xl text-white">100%</p>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Genuine Origin</p>
                    </div>
                    <div>
                        <p class="font-display font-black text-2xl text-sky-400">6-Point</p>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Electronic Stringing</p>
                    </div>
                    <div>
                        <p class="font-display font-black text-2xl text-blue-400">Fast</p>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nationwide Dispatch</p>
                    </div>
                </div>
            </div>

            <!-- Hero Right Dynamic Product Showcase Carousel -->
            <div class="lg:col-span-5 relative" id="hero-carousel-container">
                <div class="relative mx-auto max-w-md rounded-3xl overflow-hidden border border-slate-800 bg-slate-900/80 shadow-2xl p-4 glow-blue group">
                    <!-- Slides Container -->
                    <div class="relative w-full h-96 rounded-2xl overflow-hidden bg-slate-950">
                        @foreach($featuredProducts->take(5) as $idx => $prod)
                            <div class="hero-slide absolute inset-0 transition-all duration-700 ease-out {{ $idx === 0 ? 'opacity-100 scale-100 z-10 pointer-events-auto' : 'opacity-0 scale-95 z-0 pointer-events-none' }}" data-slide-index="{{ $idx }}">
                                <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/25 to-transparent"></div>

                                <!-- Slide Badges -->
                                <div class="absolute top-3.5 left-3.5 flex flex-col gap-1.5 z-20">
                                    <span class="px-3 py-1 rounded-full bg-sky-600/95 text-white font-black text-[10px] uppercase tracking-wider backdrop-blur-md shadow-md w-max">
                                        {{ $prod->brand ? $prod->brand->name : 'PRO TOUR' }}
                                    </span>
                                    @if($prod->has_discount)
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500 text-white font-bold text-[10px] uppercase tracking-wider shadow-sm w-max">
                                            -{{ $prod->discount_percentage }}% OFF
                                        </span>
                                    @endif
                                </div>

                                <!-- Floating Product Info Card -->
                                <div class="absolute bottom-3.5 left-3.5 right-3.5 bg-slate-950/90 backdrop-blur-md border border-slate-800 p-4 rounded-2xl flex items-center justify-between shadow-2xl z-20">
                                    <div class="pr-2 min-w-0">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-sky-400 block mb-0.5">
                                            Featured Gear • #{{ $idx + 1 }} of {{ min(5, $featuredProducts->count()) }}
                                        </span>
                                        <h3 class="font-display font-bold text-white text-sm truncate hover:text-sky-300 transition">
                                            {{ $prod->name }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <p class="text-xs text-sky-400 font-bold font-mono">
                                                ${{ number_format($prod->effective_price, 2) }}
                                            </p>
                                            @if($prod->has_discount)
                                                <span class="text-[11px] line-through text-slate-500 font-normal font-mono">
                                                    ${{ number_format($prod->price, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route('shop.product', $prod->slug) }}" class="p-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white shadow-lg shadow-sky-600/30 transition shrink-0 hover:scale-105" title="View {{ $prod->name }}">
                                        <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Carousel Controls & Indicators -->
                    <div class="flex items-center justify-between mt-3 px-1">
                        <!-- Navigation Arrows -->
                        <div class="flex items-center gap-1.5">
                            <button type="button" id="hero-carousel-prev" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer" title="Previous product">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <button type="button" id="hero-carousel-next" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer" title="Next product">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <!-- Dot Indicators -->
                        <div class="flex items-center gap-1.5" id="hero-carousel-dots">
                            @foreach($featuredProducts->take(5) as $idx => $prod)
                                <button type="button" class="hero-dot h-2 rounded-full transition-all duration-300 cursor-pointer {{ $idx === 0 ? 'w-6 bg-sky-400' : 'w-2 bg-slate-700 hover:bg-slate-500' }}" data-target-index="{{ $idx }}" aria-label="Go to slide {{ $idx + 1 }}"></button>
                            @endforeach
                        </div>

                        <!-- Auto-cycle Status Indicator -->
                        <div class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Live Showcase</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Brand Logos Carousel / Bar -->
<div class="border-b border-slate-900 bg-slate-950/60 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-[11px] font-bold tracking-widest text-slate-500 uppercase mb-6">Authorized Dealer & Official Warranty</p>
        <div class="flex flex-wrap items-center justify-center gap-8 sm:gap-16 opacity-75 hover:opacity-100 transition">
            @foreach($brands as $brand)
                <a href="{{ route('shop.catalog', ['brand' => $brand->slug]) }}" class="text-lg sm:text-xl font-display font-black text-slate-400 hover:text-sky-400 tracking-wider transition uppercase">
                    {{ $brand->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Category Highlights -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
        <div>
            <span class="text-xs font-bold text-sky-400 uppercase tracking-widest">Gear by Category</span>
            <h2 class="font-display font-black text-3xl sm:text-4xl text-white mt-1">Tournament Equipment</h2>
        </div>
        <a href="{{ route('shop.catalog') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sky-400 hover:underline">
            <span>View All Categories</span>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($categories as $category)
            <a href="{{ route('shop.catalog', ['category' => $category->slug]) }}" class="group p-5 rounded-2xl bg-slate-900/60 hover:bg-slate-800/80 border border-slate-800 hover:border-sky-500/40 transition-all text-center flex flex-col items-center justify-center hover:-translate-y-1 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-slate-800 group-hover:bg-sky-500/10 text-sky-400 flex items-center justify-center mb-3 transition">
                    @if($category->slug === 'badminton-shoes' || str_contains($category->slug, 'shoe'))
                        <!-- Authentic Athletic Court Shoe Icon -->
                        <svg class="w-6 h-6 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.5 17h19a1.5 1.5 0 0 0 1.5-1.5v-1a4 4 0 0 0-3-3.87l-6-1.63L11 6a3 3 0 0 0-3-3H4a2 2 0 0 0-2 2v10.5A1.5 1.5 0 0 0 2.5 17z"/>
                            <path d="M6 17v3a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3"/>
                            <line x1="8" y1="8" x2="12" y2="8"/>
                            <line x1="9" y1="11" x2="13" y2="11"/>
                        </svg>
                    @elseif($category->slug === 'badminton-rackets' || str_contains($category->slug, 'racket'))
                        <!-- Strung Badminton Racket Icon -->
                        <svg class="w-6 h-6 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="15" cy="9" r="6"/>
                            <line x1="15" y1="3" x2="15" y2="15"/>
                            <line x1="9" y1="9" x2="21" y2="9"/>
                            <path d="m10.5 13.5-7 7a1.5 1.5 0 0 0 2 2l7-7"/>
                        </svg>
                    @elseif($category->slug === 'shuttlecocks' || str_contains($category->slug, 'shuttle'))
                        <!-- Badminton Shuttlecock Feather Icon -->
                        <svg class="w-6 h-6 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 16a4 4 0 0 0 8 0"/>
                            <path d="M7 16 4 4l6 3 4-3 4 3 4-3-3 12"/>
                            <line x1="10" y1="16" x2="10" y2="7"/>
                            <line x1="14" y1="16" x2="14" y2="7"/>
                        </svg>
                    @elseif($category->slug === 'strings-tension' || str_contains($category->slug, 'string'))
                        <!-- String Weave & Tension Grid Icon -->
                        <svg class="w-6 h-6 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="4"/>
                            <line x1="3" y1="9" x2="21" y2="9"/>
                            <line x1="3" y1="15" x2="21" y2="15"/>
                            <line x1="9" y1="3" x2="9" y2="21"/>
                            <line x1="15" y1="3" x2="15" y2="21"/>
                        </svg>
                    @elseif($category->slug === 'bags-backpacks' || str_contains($category->slug, 'bag'))
                        <!-- Tournament Badminton Bag Icon -->
                        <svg class="w-6 h-6 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 10a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/>
                            <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                            <line x1="8" y1="15" x2="16" y2="15"/>
                        </svg>
                    @else
                        <!-- Grips & Accessories Icon -->
                        <svg class="w-6 h-6 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="m14.5 9.5-5 5"/>
                            <path d="M12 3v18"/>
                            <path d="M3 12h18"/>
                        </svg>
                    @endif
                </div>
                <h4 class="font-display font-bold text-sm text-white group-hover:text-sky-400 transition">{{ $category->name }}</h4>
                <p class="text-[11px] text-slate-400 mt-1">{{ $category->products_count }} items</p>
            </a>
        @endforeach
    </div>
</div>

<!-- Featured Products Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-10">
        <div>
            <span class="text-xs font-bold text-sky-400 uppercase tracking-widest">Tournament Pro Series</span>
            <h2 class="font-display font-black text-3xl sm:text-4xl text-white mt-1">Featured Racquets & Footwear</h2>
        </div>
        <a href="{{ route('shop.catalog', ['category' => 'badminton-rackets']) }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-bold text-sky-400 hover:underline">
            <span>Explore Racquets</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($featuredProducts as $product)
            <div class="bg-slate-900/80 border border-slate-800 hover:border-slate-700 rounded-3xl overflow-hidden flex flex-col justify-between group transition-all hover:shadow-2xl hover:shadow-sky-500/5">
                <div class="relative p-4 pb-0">
                    <!-- Badges -->
                    <div class="absolute top-6 left-6 z-10 flex flex-col gap-1.5">
                        @if($product->has_discount)
                            <span class="px-2.5 py-1 rounded-lg bg-rose-500 text-white font-black text-[11px] tracking-wider uppercase shadow-sm">
                                -{{ $product->discount_percentage }}%
                            </span>
                        @endif
                        @if($product->is_featured)
                            <span class="px-2.5 py-1 rounded-lg bg-sky-600 text-white font-bold text-[11px] tracking-wider uppercase shadow-sm">
                                PRO TOUR
                            </span>
                        @endif
                    </div>

                    <!-- Wishlist Toggle Button -->
                    <button type="button" onclick="toggleWishlist({{ $product->id }}, this)" data-wishlist-id="{{ $product->id }}" class="absolute top-6 right-6 z-10 w-9 h-9 rounded-xl bg-slate-950/80 hover:bg-slate-900 border border-slate-700/80 {{ in_array($product->id, $wishlistIds ?? []) ? 'text-rose-500 fill-rose-500' : 'text-slate-400' }} hover:text-rose-400 flex items-center justify-center transition shadow-md backdrop-blur-sm" title="Save to Wishlist">
                        <svg class="w-4 h-4 {{ in_array($product->id, $wishlistIds ?? []) ? 'fill-current' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                        </svg>
                    </button>

                    <!-- Product Image -->
                    <a href="{{ route('shop.product', $product->slug) }}" class="block overflow-hidden rounded-2xl bg-slate-950/60 aspect-square">
                        <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    </a>
                </div>

                <div class="p-5 flex flex-col flex-1 justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span>{{ $product->brand ? $product->brand->name : 'Yonex' }}</span>
                            <span class="{{ $product->total_stock > 0 ? 'text-sky-400' : 'text-rose-400' }} font-semibold">{{ $product->total_stock > 0 ? 'In Stock' : 'Pre-order' }}</span>
                        </div>
                        <a href="{{ route('shop.product', $product->slug) }}">
                            <h3 class="font-display font-bold text-white text-base hover:text-sky-400 transition line-clamp-2 leading-snug">
                                {{ $product->name }}
                            </h3>
                        </a>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-800/80">
                        <div class="flex items-baseline justify-between mb-3">
                            <div>
                                <p class="font-display font-black text-xl text-white font-mono">
                                    ${{ number_format($product->effective_price, 2) }}
                                </p>
                                @if($product->has_discount)
                                    <p class="text-xs text-slate-500 line-through font-mono">
                                        ${{ number_format($product->price, 2) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons: Add to Cart (keep shopping) & Buy Now (instant checkout) -->
                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('cart.add') }}" method="POST" class="w-full ajax-add-to-cart-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" {{ $product->is_out_of_stock ? 'disabled' : '' }} class="w-full py-2.5 px-2 rounded-xl bg-slate-800 hover:bg-slate-700 disabled:opacity-40 text-slate-200 hover:text-white font-bold text-xs transition flex items-center justify-center gap-1.5" title="Add to cart & keep shopping">
                                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-sky-400"></i>
                                    <span>Add</span>
                                </button>
                            </form>

                            <form action="{{ route('cart.add') }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="buy_now" value="1">
                                <button type="submit" {{ $product->is_out_of_stock ? 'disabled' : '' }} class="w-full py-2.5 px-2 rounded-xl bg-sky-600 hover:bg-sky-500 disabled:opacity-40 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition flex items-center justify-center gap-1.5" title="Buy now with instant checkout">
                                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                    <span>Buy</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Professional Racquet Stringing Lounge -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800 rounded-3xl p-8 sm:p-12 relative overflow-hidden shadow-2xl">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-8 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/30 text-xs font-bold">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    <span>Certified Master Stringing Service</span>
                </div>
                <h2 class="font-display font-black text-3xl sm:text-4xl text-white">
                    Professional Racquet <span class="text-sky-400">Stringing Lounge</span>
                </h2>
                <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-2xl">
                    Every racquet purchase can be custom-strung by our certified stringers on computerized 6-point constant-pull electronic stringing machines. Calibrated to within 0.1 lbs for pinpoint tension retention, sweet-spot responsiveness, and smash power.
                </p>
                <div class="pt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-semibold text-slate-300">
                    <div class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <i data-lucide="sliders" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span>Tension 20 - 32 lbs</span>
                    </div>
                    <div class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <i data-lucide="layers" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span>Yonex BG80, BG66UM, Aerobite</span>
                    </div>
                    <div class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <i data-lucide="clock" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span>Same-Day Stringing Service</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 flex justify-center">
                <div class="w-full max-w-sm p-6 rounded-3xl bg-slate-950 border border-slate-800 text-center shadow-2xl relative overflow-hidden">
                    <div class="w-14 h-14 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center text-xl mx-auto mb-3 shadow-lg shadow-sky-500/10">
                        <i data-lucide="activity" class="w-7 h-7"></i>
                    </div>
                    <h4 class="font-display font-bold text-white text-base">BWF Tournament Standard</h4>
                    <p class="text-xs text-slate-400 mt-1">4-knot tournament pattern with pre-stretch calibration</p>
                    <div class="mt-4 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Racquet Orders:</span>
                        <span class="text-sky-400 font-bold uppercase">Free Stringing Included</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    const prevBtn = document.getElementById('hero-carousel-prev');
    const nextBtn = document.getElementById('hero-carousel-next');
    const container = document.getElementById('hero-carousel-container');

    if (!slides.length) return;

    let currentIndex = 0;
    let autoInterval = null;

    function showSlide(index) {
        if (index < 0) {
            index = slides.length - 1;
        } else if (index >= slides.length) {
            index = 0;
        }

        slides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.remove('opacity-0', 'scale-95', 'pointer-events-none', 'z-0');
                slide.classList.add('opacity-100', 'scale-100', 'pointer-events-auto', 'z-10');
            } else {
                slide.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto', 'z-10');
                slide.classList.add('opacity-0', 'scale-95', 'pointer-events-none', 'z-0');
            }
        });

        dots.forEach((dot, i) => {
            if (i === index) {
                dot.className = 'hero-dot h-2 rounded-full transition-all duration-300 cursor-pointer w-6 bg-sky-400';
            } else {
                dot.className = 'hero-dot h-2 rounded-full transition-all duration-300 cursor-pointer w-2 bg-slate-700 hover:bg-slate-500';
            }
        });

        currentIndex = index;
    }

    function nextSlide() {
        showSlide(currentIndex + 1);
    }

    function prevSlide() {
        showSlide(currentIndex - 1);
    }

    function startAutoPlay() {
        stopAutoPlay();
        autoInterval = setInterval(nextSlide, 3500);
    }

    function stopAutoPlay() {
        if (autoInterval) {
            clearInterval(autoInterval);
            autoInterval = null;
        }
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function (e) {
            e.preventDefault();
            nextSlide();
            startAutoPlay();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function (e) {
            e.preventDefault();
            prevSlide();
            startAutoPlay();
        });
    }

    dots.forEach(dot => {
        dot.addEventListener('click', function (e) {
            e.preventDefault();
            const idx = parseInt(this.getAttribute('data-target-index'));
            showSlide(idx);
            startAutoPlay();
        });
    });

    if (container) {
        container.addEventListener('mouseenter', stopAutoPlay);
        container.addEventListener('mouseleave', startAutoPlay);
    }

    startAutoPlay();
});
</script>
@endsection
