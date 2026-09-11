@extends('layouts.app')

@section('title', 'Shopping Cart - TosLengSey Badminton Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="font-display font-black text-3xl sm:text-4xl text-white">Your Shopping Cart</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Review your badminton gear before proceeding to fast Bakong KHQR checkout.</p>
    </div>

    @if($cart->items->isEmpty())
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-16 text-center max-w-lg mx-auto">
            <div class="w-20 h-20 rounded-3xl bg-slate-800 text-slate-500 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="shopping-bag" class="w-10 h-10"></i>
            </div>
            <h2 class="font-display font-bold text-2xl text-white">Your cart is empty</h2>
            <p class="text-xs text-slate-400 mt-2 mb-6">
                Looks like you haven't added any racquets, shoes, or shuttles to your bag yet.
            </p>
            <a href="{{ route('shop.catalog') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-display font-bold text-sm shadow-xl shadow-sky-600/25 transition">
                <span>Start Shopping</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            <!-- Items Table -->
            <div class="lg:col-span-8 bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <span>Item Details</span>
                    <form action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-rose-400 hover:underline">Empty Cart</button>
                    </form>
                </div>

                <div class="divide-y divide-slate-800/80">
                    @foreach($cart->items as $item)
                        <div class="py-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <!-- Product thumbnail & title -->
                            <div class="flex items-center gap-4">
                                <a href="{{ route('shop.product', $item->product->slug) }}" class="w-20 h-20 rounded-2xl overflow-hidden bg-slate-950 shrink-0">
                                    <img src="{{ $item->product->image ?: 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=200' }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                </a>
                                <div>
                                    <span class="text-[10px] font-bold uppercase text-sky-400">{{ $item->product->brand ? $item->product->brand->name : 'Yonex' }}</span>
                                    <a href="{{ route('shop.product', $item->product->slug) }}">
                                        <h3 class="font-display font-bold text-white text-base hover:text-sky-400 transition">{{ $item->product->name }}</h3>
                                    </a>
                                    @if($item->variant)
                                        <p class="text-xs text-slate-400 mt-0.5">Spec: <span class="text-slate-200">{{ $item->variant->variant_name }}</span></p>
                                    @endif
                                    <p class="text-xs text-slate-500 font-mono mt-1">${{ number_format($item->unit_price, 2) }} each</p>
                                </div>
                            </div>

                            <!-- Quantity Stepper & Subtotal -->
                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center bg-slate-950 border border-slate-800 rounded-xl p-1">
                                    @csrf
                                    <button type="submit" name="quantity" value="{{ max(1, $item->quantity - 1) }}" class="w-8 h-8 rounded-lg bg-slate-900 text-white font-bold flex items-center justify-center hover:bg-slate-800 transition">-</button>
                                    <span class="w-10 text-center text-xs font-bold text-white">{{ $item->quantity }}</span>
                                    <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" class="w-8 h-8 rounded-lg bg-slate-900 text-white font-bold flex items-center justify-center hover:bg-slate-800 transition">+</button>
                                </form>

                                <div class="text-right">
                                    <p class="font-display font-black text-lg text-white font-mono">${{ number_format($item->subtotal, 2) }}</p>
                                </div>

                                <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-500 hover:text-rose-400 transition" title="Remove item">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-4 bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-6 sticky top-28">
                <h3 class="font-display font-bold text-xl text-white">Summary</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Items Subtotal</span>
                        <span class="font-semibold text-white font-mono">${{ number_format($cart->subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Delivery Fee</span>
                        <span class="text-slate-300">Calculated at checkout</span>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                        <span class="font-bold text-white">Estimated Total</span>
                        <div class="text-right">
                            <span class="font-display font-black text-2xl text-sky-400 font-mono">${{ number_format($cart->subtotal, 2) }}</span>
                            <p class="text-[11px] text-slate-500 font-mono">≈ {{ number_format($cart->subtotal * 4100) }} KHR</p>
                        </div>
                    </div>
                </div>

                <a href="{{ route('checkout.index') }}" class="w-full py-4 px-6 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-display font-bold text-base shadow-xl shadow-sky-600/25 hover:scale-[1.02] transition flex items-center justify-center gap-2">
                    <span>Proceed to Checkout</span>
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>

                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-600/20 text-red-500 flex items-center justify-center font-bold text-xs shrink-0">
                        KHQR
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Bakong Instant Payment</p>
                        <p class="text-[10px] text-slate-500">Scan & pay with any bank app in seconds</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
