@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' - TosLengSey Badminton Store')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-display font-black text-2xl sm:text-3xl text-white">Order #{{ $order->order_number }}</h1>
                <span class="text-xs px-2.5 py-1 rounded-full border font-bold {{ $order->status_badge }}">
                    {{ strtoupper($order->order_status) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Placed on {{ $order->created_at->format('F d, Y - h:i A') }}</p>
        </div>

        <div class="flex items-center gap-3">
            @if(!$order->is_paid && $order->payment_method === 'khqr')
                <a href="{{ route('payment.khqr', $order->order_number) }}" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-red-600/20">
                    <i data-lucide="qr-code" class="w-4 h-4"></i> Complete KHQR Payment
                </a>
            @endif

            <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i> Print Invoice
            </a>

            @if($order->can_be_cancelled)
                <form action="{{ route('orders.cancel', $order->order_number) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order? Items will be restored to inventory.');">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 hover:bg-rose-500/20 text-rose-400 font-bold text-xs">
                        Cancel Order
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($order->is_paid)
        <!-- High-Confidence Payment Confirmed Banner -->
        <div class="mb-8 p-5 sm:p-6 rounded-3xl bg-emerald-950/40 border border-emerald-500/40 flex items-start sm:items-center gap-4 shadow-xl shadow-emerald-500/10">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                <i data-lucide="badge-check" class="w-7 h-7 text-emerald-400"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2.5">
                    <h3 class="font-display font-black text-lg text-emerald-400">Payment Confirmed & Order Placed!</h3>
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">PAID & VERIFIED</span>
                </div>
                <p class="text-xs text-slate-300 mt-1">
                    Your payment was successfully received and verified{{ $order->paid_at ? ' on ' . $order->paid_at->format('M d, Y - h:i A') : '' }}. Your order is confirmed and our team is now preparing your badminton equipment!
                </p>
            </div>
        </div>
    @elseif($order->payment_method === 'khqr')
        <!-- Awaiting Merchant Verification -->
        <div class="mb-8 p-5 rounded-3xl bg-amber-950/40 border border-amber-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/30">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-white">Awaiting Payment Confirmation</h4>
                    <p class="text-xs text-slate-400">Our store manager has been notified on Telegram. Once verified, your status will update automatically.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('payment.khqr', $order->order_number) }}" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-lg shadow-red-600/20">
                    <i data-lucide="qr-code" class="w-4 h-4"></i> View QR / Upload Slip
                </a>
            </div>
        </div>
    @endif

    <!-- Fulfillment Status Steps -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sm:p-8 mb-8">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-6">Fulfillment Progress</h3>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 relative text-center">
            @php
                $steps = [
                    'pending' => '1. Order Placed',
                    'confirmed' => ($order->is_paid ? '2. Confirmed & Paid' : '2. Confirmed'),
                    'processing' => '3. Packing & Stringing',
                    'shipped' => '4. In Transit',
                    'completed' => '5. Delivered',
                ];
                $statuses = array_keys($steps);
                $currentIndex = array_search($order->order_status, $statuses);
                if ($currentIndex === false) $currentIndex = ($order->order_status === 'delivered') ? 4 : 0;
            @endphp

            @foreach($steps as $key => $label)
                @php
                    $stepIndex = array_search($key, $statuses);
                    $isPassed = $stepIndex <= $currentIndex;
                    $isCurrent = $stepIndex === $currentIndex;
                @endphp
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs transition {{ $isPassed ? 'bg-sky-500 text-white font-black shadow-lg shadow-sky-500/25' : 'bg-slate-800 text-slate-500' }}">
                        @if($isPassed) <i data-lucide="check" class="w-5 h-5"></i> @else {{ $stepIndex + 1 }} @endif
                    </div>
                    <p class="text-xs font-bold {{ $isCurrent ? 'text-sky-400' : ($isPassed ? 'text-white' : 'text-slate-500') }}">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Order Items & Details -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Items Table -->
        <div class="lg:col-span-8 bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-4">
            <h3 class="font-display font-bold text-lg text-white pb-3 border-b border-slate-800">Purchased Items</h3>

            <div class="divide-y divide-slate-800">
                @foreach($order->items as $item)
                    <div class="py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-slate-950 overflow-hidden shrink-0">
                                <img src="{{ $item->product?->image ?: 'https://images.unsplash.com/photo-1613918108466-292b78a8ef95?w=200' }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">{{ $item->product_name }}</h4>
                                @if($item->variant_name)
                                    <p class="text-xs text-slate-400">Spec: {{ $item->variant_name }}</p>
                                @endif
                                <p class="text-xs text-slate-500 font-mono">${{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }}</p>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="font-display font-bold text-white font-mono">${{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cost Summary -->
            <div class="pt-4 border-t border-slate-800 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span>Subtotal</span>
                    <span class="font-mono text-white font-semibold">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex items-center justify-between text-sky-400">
                        <span>Discount Applied</span>
                        <span class="font-mono font-semibold">-${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between text-slate-400">
                    <span>Delivery Fee</span>
                    <span class="font-mono text-white font-semibold">${{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-sm">
                    <span class="font-display font-bold text-white">Grand Total</span>
                    <div class="text-right">
                        <span class="font-display font-black text-xl text-sky-400 font-mono">${{ number_format($order->total_amount, 2) }}</span>
                        <p class="text-[10px] text-slate-500 font-mono">≈ {{ number_format($order->total_amount * 4100) }} KHR</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipient & Payment Status Info -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Delivery Destination -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-3">
                <h4 class="font-display font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-sky-400"></i> Delivery Address
                </h4>
                <div class="text-xs text-slate-300 space-y-1">
                    <p class="font-bold text-white text-sm">{{ $order->customer_name }}</p>
                    <p class="text-slate-400">{{ $order->customer_phone }}</p>
                    <p class="mt-2">{{ $order->delivery_address }}</p>
                    <p class="text-slate-400 font-semibold">{{ $order->province_city }} ({{ ucfirst($order->delivery_method) }})</p>
                    @if($order->customer_note)
                        <p class="mt-2 text-[11px] text-sky-300 bg-sky-500/10 p-2 rounded-xl border border-sky-500/20">
                            <strong>Note:</strong> {{ $order->customer_note }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-3">
                <h4 class="font-display font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4 text-red-500"></i> Payment Details
                </h4>
                <div class="text-xs text-slate-300 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Method:</span>
                        <span class="font-bold uppercase text-white">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Status:</span>
                        <span class="px-2 py-0.5 rounded-full border text-[11px] font-bold {{ $order->payment_badge }}">
                            {{ strtoupper($order->payment_status) }}
                        </span>
                    </div>

                    @if($order->bakong_hash)
                        <div class="pt-2 border-t border-slate-800">
                            <span class="text-slate-400 text-[10px]">{{ $order->payment_method === 'credit_card' ? 'Card Transaction Ref:' : 'Bakong Transaction Hash:' }}</span>
                            <p class="font-mono text-[10px] text-sky-400 break-all select-all mt-0.5">{{ $order->bakong_hash }}</p>
                        </div>
                    @endif

                    @if($order->paid_at)
                        <div class="text-slate-400 text-[11px]">
                            Verified on {{ $order->paid_at->format('M d, Y - h:i A') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
