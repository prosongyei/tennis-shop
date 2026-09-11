@extends('layouts.app')

@section('title', 'Track Order Status - TosLengSey Badminton Store')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="text-center max-w-xl mx-auto mb-10">
        <div class="w-14 h-14 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 mx-auto flex items-center justify-center text-2xl shadow-lg shadow-sky-500/10 mb-4">
            <i data-lucide="truck" class="w-7 h-7"></i>
        </div>
        <h1 class="font-display font-black text-3xl sm:text-4xl text-white">Live Order Tracking</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-2">Check live stringing status, packing progress, and delivery updates for your badminton gear.</p>
    </div>

    <!-- Search Form Card -->
    <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl mb-10">
        <form action="{{ route('orders.track') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-6">
                <label for="order_number" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Order Number</label>
                <div class="relative">
                    <input type="text" id="order_number" name="order_number" value="{{ request('order_number') }}" placeholder="e.g. ORD-20260905-XXXX" class="w-full bg-slate-950 text-white text-sm border border-slate-700/80 rounded-xl px-4 py-3 pl-10 uppercase font-mono focus:outline-none focus:border-sky-400 transition placeholder:text-slate-500">
                    <i data-lucide="hash" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                </div>
            </div>

            <div class="md:col-span-4">
                <label for="phone" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Phone Number</label>
                <div class="relative">
                    <input type="text" id="phone" name="phone" value="{{ request('phone') }}" placeholder="e.g. 012 345 678" class="w-full bg-slate-950 text-white text-sm border border-slate-700/80 rounded-xl px-4 py-3 pl-10 focus:outline-none focus:border-sky-400 transition placeholder:text-slate-500">
                    <i data-lucide="phone" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                </div>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-lg shadow-sky-600/25 transition flex items-center justify-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Track</span>
                </button>
            </div>
        </form>

        <p class="text-[11px] text-slate-500 mt-3 flex items-center gap-1.5">
            <i data-lucide="info" class="w-3.5 h-3.5 text-sky-400"></i>
            <span>You can search by either your <strong>Order Number</strong>, your registered <strong>Phone Number</strong>, or both.</span>
        </p>
    </div>

    <!-- Quick Access for Authenticated Customers -->
    @if(isset($userRecentOrders) && $userRecentOrders->isNotEmpty() && !isset($order))
        <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-6 sm:p-8 mb-10">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <i data-lucide="package-check" class="w-5 h-5 text-sky-400"></i>
                    <h3 class="font-display font-bold text-white text-base">Your Recent Purchases</h3>
                </div>
                <a href="{{ route('orders.my') }}" class="text-xs text-sky-400 hover:underline font-bold flex items-center gap-1">
                    View All Orders &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($userRecentOrders as $recent)
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col justify-between space-y-3 hover:border-slate-700 transition">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-xs text-white">#{{ $recent->order_number }}</span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full border font-bold {{ $recent->status_badge }}">
                                {{ ucfirst($recent->order_status) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 line-clamp-1">
                            {{ $recent->items->pluck('product_name')->join(', ') }}
                        </p>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-800 text-xs">
                            <span class="font-mono font-bold text-white">${{ number_format($recent->total_amount, 2) }}</span>
                            <a href="{{ route('orders.track', ['order_number' => $recent->order_number]) }}" class="px-2.5 py-1 rounded-lg bg-sky-600/20 text-sky-400 hover:bg-sky-600 hover:text-white transition font-semibold text-[11px]">
                                Track Live
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Multiple Matching Orders from Phone Lookup -->
    @if(isset($matchingOrders) && $matchingOrders->count() > 1)
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sm:p-8 mb-10">
            <h3 class="font-display font-bold text-white text-base mb-4 flex items-center gap-2">
                <i data-lucide="layers" class="w-5 h-5 text-sky-400"></i>
                <span>Found {{ $matchingOrders->count() }} Orders for Your Contact Info</span>
            </h3>
            <div class="divide-y divide-slate-800">
                @foreach($matchingOrders as $match)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono font-bold text-sm text-white">#{{ $match->order_number }}</span>
                                <span class="text-[11px] px-2 py-0.5 rounded-full border font-semibold {{ $match->status_badge }}">
                                    {{ ucfirst($match->order_status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $match->created_at->format('M d, Y - h:i A') }} • {{ $match->items->count() }} item(s)</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-sky-400 text-sm">${{ number_format($match->total_amount, 2) }}</span>
                            <a href="{{ route('orders.track', ['order_number' => $match->order_number]) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-sky-600 text-white text-xs font-semibold transition">
                                View Status
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Live Order Progress & Details -->
    @if(isset($order))
        <div class="space-y-8">
            <!-- Header Summary Card -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="font-display font-black text-2xl text-white">Order #{{ $order->order_number }}</h2>
                            <span class="text-xs px-2.5 py-1 rounded-full border font-bold {{ $order->status_badge }}">
                                {{ strtoupper($order->order_status) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Placed on {{ $order->created_at->format('F d, Y - h:i A') }} by {{ $order->customer_name }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        @if(!$order->is_paid && $order->payment_method === 'khqr')
                            <a href="{{ route('payment.khqr', $order->order_number) }}" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-600/20 transition">
                                <i data-lucide="qr-code" class="w-4 h-4"></i> Pay KHQR
                            </a>
                        @endif
                        <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs flex items-center gap-1.5 transition">
                            <i data-lucide="printer" class="w-4 h-4"></i> Invoice
                        </a>
                    </div>
                </div>

                <!-- Fulfillment Progress Stepper -->
                <div class="pt-8">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-6 text-center sm:text-left">Live Delivery Progress</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-center">
                        @php
                            $steps = [
                                'pending' => '1. Order Placed',
                                'confirmed' => '2. Confirmed',
                                'processing' => '3. Stringing & Packing',
                                'shipped' => '4. In Transit',
                                'delivered' => '5. Delivered',
                            ];
                            $statuses = array_keys($steps);
                            $currentIndex = array_search($order->order_status, $statuses);
                            if ($currentIndex === false) $currentIndex = ($order->order_status === 'completed') ? 4 : 0;
                        @endphp

                        @foreach($steps as $key => $label)
                            @php
                                $stepIndex = array_search($key, $statuses);
                                $isPassed = $stepIndex <= $currentIndex;
                                $isCurrent = $stepIndex === $currentIndex;
                            @endphp
                            <div class="space-y-2">
                                <div class="w-11 h-11 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs transition {{ $isPassed ? 'bg-sky-500 text-white font-black shadow-lg shadow-sky-500/25' : 'bg-slate-800 text-slate-500' }}">
                                    @if($isPassed) <i data-lucide="check" class="w-5 h-5"></i> @else {{ $stepIndex + 1 }} @endif
                                </div>
                                <p class="text-xs font-bold {{ $isCurrent ? 'text-sky-400' : ($isPassed ? 'text-white' : 'text-slate-500') }}">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Items and Delivery Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Ordered Items -->
                <div class="lg:col-span-7 bg-slate-900/70 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h3 class="font-display font-bold text-base text-white pb-3 border-b border-slate-800">Order Items ({{ $order->items->count() }})</h3>
                    <div class="divide-y divide-slate-800">
                        @foreach($order->items as $item)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-12 h-12 rounded-xl bg-slate-950 overflow-hidden shrink-0 border border-slate-800">
                                        <img src="{{ $item->product?->image ?: 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=200' }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-xs sm:text-sm">{{ $item->product_name }}</h4>
                                        @if($item->variant_name)
                                            <p class="text-[11px] text-slate-400 font-medium">Tension/Spec: {{ $item->variant_name }}</p>
                                        @endif
                                        <p class="text-[11px] text-slate-400 font-mono">${{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }}</p>
                                    </div>
                                </div>
                                <span class="font-display font-bold text-white font-mono text-sm">${{ number_format($item->subtotal, 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Cost Breakdown -->
                    <div class="pt-4 border-t border-slate-800 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-mono text-white">${{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-sky-400">
                                <span>Discount</span>
                                <span class="font-mono">-${{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-400">
                            <span>Delivery Fee</span>
                            <span class="font-mono text-white">${{ number_format($order->delivery_fee, 2) }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-800 flex justify-between text-sm font-bold text-white">
                            <span>Total Amount</span>
                            <span class="text-sky-400 font-mono text-base font-black">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recipient & Delivery Address -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 space-y-3">
                        <h4 class="font-display font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-sky-400"></i> Delivery Address
                        </h4>
                        <div class="text-xs text-slate-300 space-y-1">
                            <p class="font-bold text-white text-sm">{{ $order->customer_name }}</p>
                            <p class="text-slate-400">{{ $order->customer_phone }}</p>
                            <p class="mt-2">{{ $order->delivery_address }}</p>
                            <p class="text-slate-400 font-semibold">{{ $order->province_city }} ({{ ucfirst($order->delivery_method) }})</p>
                        </div>
                    </div>

                    <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 space-y-3">
                        <h4 class="font-display font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-4 h-4 text-sky-400"></i> Payment Details
                        </h4>
                        <div class="text-xs space-y-2">
                            <div class="flex items-center justify-between text-slate-300">
                                <span class="text-slate-400">Method:</span>
                                <span class="font-bold uppercase text-white">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300">
                                <span class="text-slate-400">Payment Status:</span>
                                <span class="px-2 py-0.5 rounded-full border text-[11px] font-bold {{ $order->payment_badge }}">
                                    {{ strtoupper($order->payment_status) }}
                                </span>
                            </div>
                            @if($order->bakong_hash)
                                <div class="pt-2 border-t border-slate-800">
                                    <span class="text-slate-400 text-[10px]">Bakong Transaction:</span>
                                    <p class="font-mono text-[10px] text-sky-400 break-all">{{ $order->bakong_hash }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
