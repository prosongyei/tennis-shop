@extends('layouts.app')

@section('title', 'My Saved Wishlist - TosLengSey Badminton')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
        <a href="{{ route('home') }}" class="hover:text-sky-400 transition">Home</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600"></i>
        <span class="text-white font-semibold">Wishlist</span>
    </nav>

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center shadow-lg shadow-rose-500/10">
                    <i data-lucide="heart" class="w-5 h-5 fill-rose-500 text-rose-500"></i>
                </div>
                <div>
                    <h1 class="font-display font-black text-2xl sm:text-3xl text-white tracking-tight">Saved Gear Wishlist</h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-0.5">Keep track of racquets, court shoes, and accessories you love</p>
                </div>
            </div>
        </div>

        @if($products->isNotEmpty())
            <div class="flex items-center gap-3">
                <a href="{{ route('shop.catalog') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white font-semibold text-xs transition flex items-center gap-2">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Continue Shopping
                </a>
                <form action="{{ route('wishlist.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your wishlist?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-950 hover:bg-rose-500/10 border border-slate-800 hover:border-rose-500/30 text-slate-400 hover:text-rose-400 font-semibold text-xs transition flex items-center gap-2">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Clear All
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Product Grid or Empty State -->
    @if($products->isEmpty())
        <div class="text-center py-20 bg-slate-900/40 border border-slate-800/80 rounded-3xl p-8 max-w-xl mx-auto shadow-2xl">
            <div class="w-20 h-20 rounded-full bg-slate-950 border border-slate-800 text-slate-600 flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i data-lucide="heart-off" class="w-10 h-10"></i>
            </div>
            <h2 class="font-display font-black text-2xl text-white">Your Wishlist is Empty</h2>
            <p class="text-xs sm:text-sm text-slate-400 max-w-sm mx-auto mt-2 leading-relaxed">
                Found a racquet or shoes you're eyeing? Tap the heart icon on any gear item to save it here for easy ordering later!
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('shop.catalog') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs tracking-wide shadow-lg shadow-sky-600/20 transition flex items-center justify-center gap-2">
                    <i data-lucide="grid" class="w-4 h-4"></i> Browse All
                </a>
                <a href="{{ route('shop.catalog', ['category' => 'badminton-rackets']) }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-slate-200 font-semibold text-xs transition flex items-center justify-center gap-2">
                    <i data-lucide="crosshair" class="w-4 h-4 text-sky-400"></i> View Racquets
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="bg-slate-900/70 border border-slate-800 hover:border-slate-700 rounded-3xl overflow-hidden group hover:shadow-2xl hover:shadow-sky-500/5 transition flex flex-col justify-between">
                    <div>
                        <!-- Image Container with Heart button & Badge -->
                        <div class="relative aspect-square bg-slate-950/60 p-6 flex items-center justify-center overflow-hidden">
                            <a href="{{ route('shop.product', $product->slug) }}" class="w-full h-full flex items-center justify-center">
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';" class="max-h-full max-w-full object-contain group-hover:scale-105 transition duration-300">
                            </a>

                            <!-- Brand Badge -->
                            <div class="absolute top-3 left-3">
                                <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg bg-slate-900/90 text-slate-300 border border-slate-700 backdrop-blur-md">
                                    {{ $product->brand->name ?? 'TosLengSey' }}
                                </span>
                            </div>

                            <!-- Remove from Wishlist Button -->
                            <button type="button" onclick="toggleWishlist({{ $product->id }}, this)" class="absolute top-3 right-3 w-9 h-9 rounded-xl bg-slate-900/90 hover:bg-rose-500 border border-slate-700 hover:border-rose-400 text-rose-400 hover:text-white flex items-center justify-center transition shadow-lg group/btn" title="Remove from wishlist">
                                <i data-lucide="heart" class="w-4 h-4 fill-current"></i>
                            </button>

                            <!-- Stock Status Overlay if Out of Stock -->
                            @if($product->is_out_of_stock)
                                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center">
                                    <span class="text-xs font-black uppercase tracking-wider px-3 py-1.5 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        Sold Out
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Product Content Details -->
                        <div class="p-5">
                            <p class="text-[10px] font-bold text-sky-400 uppercase tracking-wider">{{ $product->category->name ?? 'Badminton Gear' }}</p>
                            <h3 class="font-display font-bold text-white text-base mt-1 line-clamp-2 group-hover:text-sky-300 transition">
                                <a href="{{ route('shop.product', $product->slug) }}">{{ $product->name }}</a>
                            </h3>

                            <!-- Price Display -->
                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="font-display font-black text-xl text-white font-mono">${{ number_format($product->effective_price, 2) }}</span>
                                @if($product->has_discount)
                                    <span class="text-xs text-slate-500 line-through font-mono">${{ number_format($product->price, 2) }}</span>
                                    <span class="text-[10px] font-bold text-rose-400 font-mono">-{{ $product->discount_percentage }}%</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons: Add to Cart & Buy Now -->
                    <div class="p-5 pt-0 grid grid-cols-2 gap-2">
                        <!-- Add to Cart (statically adds and shows notification so they can keep shopping) -->
                        <form action="{{ route('cart.add') }}" method="POST" class="w-full ajax-add-to-cart-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" {{ $product->is_out_of_stock ? 'disabled' : '' }} class="w-full py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-slate-800 text-white font-bold text-xs transition flex items-center justify-center gap-1.5" title="Add to cart and keep shopping">
                                <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-sky-400"></i>
                                <span>Add to Cart</span>
                            </button>
                        </form>

                        <!-- Buy Now (instantly goes to checkout) -->
                        <form action="{{ route('cart.add') }}" method="POST" class="w-full">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" {{ $product->is_out_of_stock ? 'disabled' : '' }} class="w-full py-2.5 px-3 rounded-xl bg-sky-600 hover:bg-sky-500 disabled:opacity-40 disabled:hover:bg-sky-600 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition flex items-center justify-center gap-1.5" title="Buy now with instant checkout">
                                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                <span>Buy Now</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
