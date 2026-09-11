@extends('layouts.app')

@section('title', $product->name . ' - TosLengSey Badminton Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-400 mb-6">
        <a href="{{ route('home') }}" class="hover:text-sky-400">Home</a>
        <span>/</span>
        <a href="{{ route('shop.catalog') }}" class="hover:text-sky-400">Shop</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('shop.catalog', ['category' => $product->category->slug]) }}" class="hover:text-sky-400">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-white font-medium truncate">{{ $product->name }}</span>
    </div>

    <!-- Main Product Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sm:p-10 mb-16">
        <!-- Left Column: Gallery -->
        <div class="lg:col-span-6 space-y-4">
            <div class="overflow-hidden rounded-3xl bg-slate-950 border border-slate-800 aspect-square relative group">
                <img id="main-product-image" src="{{ $product->primary_image_url }}" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=80';" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">

                @if($product->has_discount)
                    <div class="absolute top-6 left-6 px-3 py-1.5 rounded-xl bg-rose-500 text-white font-black text-xs uppercase tracking-wider shadow-lg">
                        Save {{ $product->discount_percentage }}%
                    </div>
                @endif
            </div>

            <!-- Features & Guarantees -->
            <div class="grid grid-cols-3 gap-3 pt-2">
                <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 text-center">
                    <i data-lucide="award" class="w-5 h-5 text-sky-400 mx-auto mb-1"></i>
                    <p class="text-[11px] font-bold text-white">100% Genuine</p>
                    <p class="text-[9px] text-slate-500">BWF Tournament Grade</p>
                </div>
                <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 text-center">
                    <i data-lucide="truck" class="w-5 h-5 text-blue-400 mx-auto mb-1"></i>
                    <p class="text-[11px] font-bold text-white">Fast Dispatch</p>
                    <p class="text-[9px] text-slate-500">Phnom Penh & Express</p>
                </div>
                <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 text-center">
                    <i data-lucide="credit-card" class="w-5 h-5 text-indigo-400 mx-auto mb-1"></i>
                    <p class="text-[11px] font-bold text-white">KHQR & Cards</p>
                    <p class="text-[9px] text-slate-500">Bakong, Visa, Master</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Product Info & Buy -->
        <div class="lg:col-span-6 flex flex-col justify-between space-y-6">
            <div>
                <!-- Brand, Wishlist & Stock -->
                <div class="flex items-center justify-between pb-2">
                    <span class="text-xs font-black uppercase tracking-widest text-sky-400 bg-sky-500/10 border border-sky-500/20 px-3 py-1 rounded-full">
                        {{ $product->brand->name ?? 'TosLengSey' }}
                    </span>

                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-400">SKU: <span class="font-mono text-slate-200">{{ $product->sku }}</span></span>
                        <!-- Wishlist Toggle -->
                        <button type="button" onclick="toggleWishlist({{ $product->id }}, this)" data-wishlist-id="{{ $product->id }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-rose-500/40 {{ in_array($product->id, $wishlistIds ?? []) ? 'text-rose-500 fill-rose-500' : 'text-slate-400' }} hover:text-rose-400 transition flex items-center gap-1 text-xs font-bold" title="Save to Wishlist">
                            <svg class="w-4 h-4 {{ in_array($product->id, $wishlistIds ?? []) ? 'fill-current' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                            </svg>
                            <span>Wishlist</span>
                        </button>
                    </div>
                </div>

                <h1 class="font-display font-black text-3xl sm:text-4xl text-white tracking-tight mt-2">
                    {{ $product->name }}
                </h1>

                <!-- Price & Stock Status -->
                <div class="mt-4 flex flex-wrap items-baseline gap-4">
                    <span class="font-display font-black text-3xl sm:text-4xl text-white font-mono">
                        ${{ number_format($product->effective_price, 2) }}
                    </span>

                    @if($product->has_discount)
                        <span class="text-lg text-slate-500 line-through font-mono">
                            ${{ number_format($product->price, 2) }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-bold">
                            Save {{ $product->discount_percentage }}%
                        </span>
                    @endif

                    <div class="ml-auto">
                        @if($product->is_out_of_stock)
                            <span class="px-3 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-bold">Sold Out</span>
                        @elseif($product->is_low_stock)
                            <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-bold">Only {{ $product->total_stock }} Left in Stock</span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold">In Stock & Ready to Ship</span>
                        @endif
                    </div>
                </div>

                <!-- Short Description -->
                <p class="text-slate-300 text-sm leading-relaxed mt-4 pt-4 border-t border-slate-800/80">
                    {{ $product->description }}
                </p>

                <!-- Add to Cart & Buy Now Form -->
                <form action="{{ route('cart.add') }}" method="POST" id="product-purchase-form" class="mt-8 space-y-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="buy_now" id="buy-now-input" value="0">

                    <!-- Variants Selector (If Any) -->
                    @if($product->variants->isNotEmpty())
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Select Grip / Weight / Spec</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                @foreach($product->variants as $index => $variant)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="variant_id" value="{{ $variant->id }}" {{ $index === 0 ? 'checked' : '' }} class="peer sr-only">
                                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 peer-checked:border-sky-400 peer-checked:bg-sky-400/10 transition text-center">
                                            <p class="text-xs font-bold text-white peer-checked:text-sky-400">{{ $variant->variant_name }}</p>
                                            <p class="text-[10px] text-slate-500 mt-0.5 font-mono">${{ number_format($variant->effective_price, 2) }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Quantity Selector + Dual Action Buttons -->
                    <div class="space-y-3 pt-4 border-t border-slate-800">
                        <div class="flex items-center gap-4">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 shrink-0">Quantity</label>
                            <div class="flex items-center bg-slate-950 border border-slate-800 rounded-2xl p-1">
                                <button type="button" onclick="adjustQty(-1)" class="w-10 h-10 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold flex items-center justify-center transition">-</button>
                                <input type="number" id="buy-quantity" name="quantity" value="1" min="1" max="{{ max(1, $product->total_stock) }}" class="w-12 bg-transparent text-center text-sm font-bold text-white focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" onclick="adjustQty(1)" class="w-10 h-10 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold flex items-center justify-center transition">+</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <!-- 1. Add to Shopping Cart -->
                            <button type="button" onclick="submitAddToCart(false)" {{ $product->is_out_of_stock ? 'disabled' : '' }} class="w-full py-4 px-5 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-slate-600 disabled:bg-slate-950 disabled:opacity-40 text-white font-display font-bold text-sm transition flex items-center justify-center gap-2.5 shadow-lg">
                                <i data-lucide="shopping-cart" class="w-4 h-4 text-sky-400"></i>
                                <span>Add to Cart</span>
                            </button>

                            <!-- 2. Buy Now -->
                            <button type="button" onclick="submitAddToCart(true)" {{ $product->is_out_of_stock ? 'disabled' : '' }} class="w-full py-4 px-5 rounded-2xl bg-sky-600 hover:bg-sky-500 disabled:bg-slate-800 disabled:text-slate-600 text-white font-display font-bold text-sm shadow-xl shadow-sky-600/25 hover:scale-[1.01] transition flex items-center justify-center gap-2.5">
                                <i data-lucide="zap" class="w-4 h-4"></i>
                                <span>{{ $product->is_out_of_stock ? 'Sold Out' : 'Buy Now (Instant)' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Technical Specifications Table -->
    @if(!empty($product->specifications))
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 mb-16">
            <h3 class="font-display font-bold text-2xl text-white mb-6 flex items-center gap-3">
                <i data-lucide="cpu" class="w-6 h-6 text-sky-400"></i> Technical Specifications
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                @foreach($product->specifications as $key => $val)
                    <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-center justify-between">
                        <span class="text-slate-400 font-medium uppercase tracking-wider text-xs">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="text-white font-semibold">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <div class="mb-16">
            <h3 class="font-display font-bold text-2xl text-white mb-6">Related Badminton Gear</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-4 flex flex-col justify-between hover:border-slate-700 transition">
                        <a href="{{ route('shop.product', $related->slug) }}" class="block aspect-square rounded-2xl overflow-hidden bg-slate-950 mb-3">
                            <img src="{{ $related->image ?: 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=600' }}" alt="{{ $related->name }}" class="w-full h-full object-cover hover:scale-105 transition">
                        </a>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-500">{{ $related->brand ? $related->brand->name : 'Yonex' }}</span>
                            <a href="{{ route('shop.product', $related->slug) }}">
                                <h4 class="font-display font-bold text-sm text-white hover:text-sky-400 transition line-clamp-1">{{ $related->name }}</h4>
                            </a>
                            <p class="font-display font-black text-white mt-2 font-mono">${{ number_format($related->effective_price, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
    function adjustQty(delta) {
        const input = document.getElementById('buy-quantity');
        let current = parseInt(input.value) || 1;
        current = Math.max(1, current + delta);
        input.value = current;
    }

    function submitAddToCart(isBuyNow) {
        const form = document.getElementById('product-purchase-form');
        const buyNowInput = document.getElementById('buy-now-input');
        if (isBuyNow) {
            buyNowInput.value = '1';
            form.submit();
        } else {
            buyNowInput.value = '0';
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
                if (data.success) {
                    refreshCartBadge(data.cart_count);
                    showNotificationToast(
                        'Added to Bag!',
                        data.message + ' You can continue shopping or view your cart anytime.',
                        '{{ route("cart.index") }}',
                        'View Cart & Checkout'
                    );
                } else if (data.message) {
                    alert(data.message);
                }
            })
            .catch(() => form.submit());
        }
    }
</script>
@endsection
