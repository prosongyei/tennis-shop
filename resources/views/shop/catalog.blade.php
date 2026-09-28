@extends('layouts.app')

@section('title', 'Badminton Equipment & Racquets Catalog - TosLengSey')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
            <a href="{{ route('home') }}" class="hover:text-sky-400">Home</a>
            <span>/</span>
            <span class="text-white font-medium">Equipment Catalog</span>
            @if($selectedCategory)
                <span>/</span>
                <span class="text-sky-400">{{ $selectedCategory->name }}</span>
            @endif
        </div>
        <h1 class="font-display font-black text-3xl sm:text-4xl text-white">
            {{ $selectedCategory ? $selectedCategory->name : ($selectedBrand ? $selectedBrand->name . ' Gear' : 'All Badminton Equipment') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Showing {{ $products->total() }} pro items with genuine Yonex, Victor, and Li-Ning authentication.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Filters Sidebar -->
        <aside class="lg:col-span-3 bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-6 sticky top-28">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <span class="font-display font-bold text-base text-white flex items-center gap-2">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-sky-400"></i> Filters
                </span>
                <a href="{{ route('shop.catalog') }}" class="text-xs text-slate-400 hover:text-sky-400">Reset</a>
            </div>

            <form action="{{ route('shop.catalog') }}" method="GET" class="space-y-6">
                <!-- Search within catalog -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Keyword Search</label>
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="e.g. Astrox, 65Z3, AS-50" class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 pl-8 focus:outline-none focus:border-sky-400">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-500 absolute left-2.5 top-3"></i>
                    </div>
                </div>

                <!-- Categories -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Category</label>
                    <div class="space-y-1.5 text-xs">
                        <label class="flex items-center gap-2.5 cursor-pointer py-1 hover:text-sky-400">
                            <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()" class="text-sky-600 bg-slate-950 border-slate-800 focus:ring-0">
                            <span>All Categories</span>
                        </label>
                        @foreach($categories as $category)
                            <label class="flex items-center justify-between cursor-pointer py-1 hover:text-sky-400">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="category" value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'checked' : '' }} onchange="this.form.submit()" class="text-sky-600 bg-slate-950 border-slate-800 focus:ring-0">
                                    <span>{{ $category->name }}</span>
                                </div>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $category->products_count }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Brands -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Brand</label>
                    <div class="space-y-1.5 text-xs">
                        <label class="flex items-center gap-2.5 cursor-pointer py-1 hover:text-sky-400">
                            <input type="radio" name="brand" value="" {{ !request('brand') ? 'checked' : '' }} onchange="this.form.submit()" class="text-sky-600 bg-slate-950 border-slate-800 focus:ring-0">
                            <span>All Brands</span>
                        </label>
                        @foreach($brands as $brand)
                            <label class="flex items-center justify-between cursor-pointer py-1 hover:text-sky-400">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="brand" value="{{ $brand->slug }}" {{ request('brand') === $brand->slug ? 'checked' : '' }} onchange="this.form.submit()" class="text-sky-600 bg-slate-950 border-slate-800 focus:ring-0">
                                    <span>{{ $brand->name }}</span>
                                </div>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $brand->products_count }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- In Stock Only -->
                <div class="pt-2 border-t border-slate-800">
                    <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer">
                        <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded bg-slate-950 border-slate-800 text-sky-600 focus:ring-0">
                        <span>Show In-Stock Only</span>
                    </label>
                </div>
            </form>
        </aside>

        <!-- Product Grid Main Column -->
        <main class="lg:col-span-9">
            <!-- Sort & Quick Controls -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span>Sorting:</span>
                    <form action="{{ route('shop.catalog') }}" method="GET" class="inline">
                        @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                        @if(request('brand')) <input type="hidden" name="brand" value="{{ request('brand') }}"> @endif
                        @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                        <select name="sort" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 text-white text-xs rounded-xl px-3 py-1.5 focus:outline-none focus:border-sky-400">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest Arrivals</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Top Featured</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                        </select>
                    </form>
                </div>

                @if(request('q') || request('category') || request('brand'))
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[11px] text-slate-500">Active Filters:</span>
                        @if(request('q'))
                            <span class="bg-slate-800 text-white text-[11px] px-2 py-0.5 rounded-lg flex items-center gap-1">
                                "{{ request('q') }}"
                            </span>
                        @endif
                        @if($selectedCategory)
                            <span class="bg-sky-500/10 text-sky-400 border border-sky-500/30 text-[11px] px-2 py-0.5 rounded-lg">
                                {{ $selectedCategory->name }}
                            </span>
                        @endif
                        @if($selectedBrand)
                            <span class="bg-blue-500/10 text-blue-400 border border-blue-500/30 text-[11px] px-2 py-0.5 rounded-lg">
                                {{ $selectedBrand->name }}
                            </span>
                        @endif
                        <a href="{{ route('shop.catalog') }}" class="text-[11px] text-rose-400 hover:underline">Clear all</a>
                    </div>
                @endif
            </div>

            <!-- Product Grid -->
            @if($products->isEmpty())
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800 text-slate-500 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="package-x" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-display font-bold text-xl text-white">No products found</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        No equipment matched your filters. Try selecting a different category or clearing search parameters.
                    </p>
                    <a href="{{ route('shop.catalog') }}" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-lg shadow-sky-600/25">
                        View All
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <div class="bg-slate-900/80 border border-slate-800 hover:border-slate-700 rounded-3xl overflow-hidden flex flex-col justify-between group transition hover:shadow-xl">
                            <div class="relative p-4 pb-0">
                                <div class="absolute top-6 left-6 z-10 flex flex-col gap-1.5">
                                    @if($product->has_discount)
                                        <span class="px-2.5 py-1 rounded-lg bg-rose-500 text-white font-black text-[10px] uppercase shadow-sm">
                                            -{{ $product->discount_percentage }}%
                                        </span>
                                    @endif
                                </div>

                                <!-- Wishlist Toggle Button -->
                                <button type="button" onclick="toggleWishlist({{ $product->id }}, this)" data-wishlist-id="{{ $product->id }}" class="absolute top-6 right-6 z-10 w-9 h-9 rounded-xl bg-slate-950/80 hover:bg-slate-900 border border-slate-700/80 {{ in_array($product->id, $wishlistIds ?? []) ? 'text-rose-500 fill-rose-500' : 'text-slate-400' }} hover:text-rose-400 flex items-center justify-center transition shadow-md backdrop-blur-sm" title="Save to Wishlist">
                                    <svg class="w-4 h-4 {{ in_array($product->id, $wishlistIds ?? []) ? 'fill-current' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                                    </svg>
                                </button>

                                <a href="{{ route('shop.product', $product->slug) }}" class="block overflow-hidden rounded-2xl bg-slate-950 aspect-square">
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                </a>
                            </div>

                            <div class="p-5 flex flex-col flex-1 justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                        <span>{{ $product->brand ? $product->brand->name : 'Yonex' }}</span>
                                        <span class="{{ $product->total_stock > 0 ? 'text-sky-400' : 'text-rose-400' }} font-semibold text-[11px]">
                                            {{ $product->total_stock > 0 ? 'In Stock (' . $product->total_stock . ')' : 'Sold Out' }}
                                        </span>
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

                                    <!-- Dual Action Buttons -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <form action="{{ route('cart.add') }}" method="POST" class="w-full ajax-add-to-cart-form">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" {{ $product->is_out_of_stock ? 'disabled' : '' }} class="w-full py-2.5 px-2 rounded-xl bg-slate-800 hover:bg-slate-700 disabled:opacity-40 text-slate-200 hover:text-white font-bold text-xs transition flex items-center justify-center gap-1.5" title="Add to cart and keep shopping">
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

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </main>
    </div>
</div>
@endsection
