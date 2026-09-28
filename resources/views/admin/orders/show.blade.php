@extends('layouts.admin')

@section('title', 'Order #' . $order->order_number . ' - TosLengSey Admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-display font-black text-2xl sm:text-3xl text-white">Order #{{ $order->order_number }}</h1>
                <span class="text-xs px-2.5 py-1 rounded-full border font-bold {{ $order->status_badge }}">
                    {{ strtoupper($order->order_status) }}
                </span>
                <span class="text-xs px-2.5 py-1 rounded-full border font-bold {{ $order->payment_badge }}">
                    {{ strtoupper($order->payment_status) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Source: <strong class="text-white uppercase">{{ $order->source }}</strong> • Placed on {{ $order->created_at->format('M d, Y - h:i A') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-bold text-xs hover:bg-slate-800 transition">
                Print Tax Invoice
            </a>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-slate-400 hover:text-white">
                &larr; Back to Orders
            </a>
        </div>
    </div>

    <!-- Fulfillment Action Bar -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-900/80 border border-slate-800 rounded-3xl p-6">
        <!-- Update Fulfillment Status Form -->
        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="space-y-3">
            @csrf
            @method('PATCH')
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Update Order Fulfillment Status</label>
            <div class="flex gap-2">
                <select name="order_status" class="flex-1 bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
                    <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>Confirmed (Ready for stringing)</option>
                    <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing / Stringing</option>
                    <option value="ready_pickup" {{ $order->order_status === 'ready_pickup' ? 'selected' : '' }}>Ready for Store Pickup</option>
                    <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped / Handed to Delivery</option>
                    <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-xs transition">
                    Update Status
                </button>
            </div>
        </form>

        <!-- Payment Verification & Cancellation -->
        <div class="space-y-3 flex flex-col justify-end">
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Payment Actions</label>
            <div class="flex items-center gap-2">
                @if(!$order->is_paid)
                    <form action="{{ route('admin.orders.verify-payment', $order->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition flex items-center justify-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Mark Payment as Verified Paid
                        </button>
                    </form>
                @endif

                @if($order->can_be_cancelled)
                    <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Cancel this order and return all items to stock?');" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-rose-500/10 border border-rose-500/30 hover:bg-rose-500/20 text-rose-400 font-bold text-xs transition">
                            Cancel & Restore Stock
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Items & Customer Info Split -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Purchased Items (8 cols) -->
        <div class="lg:col-span-8 bg-slate-900/80 border border-slate-800 rounded-3xl p-6 space-y-4">
            <h3 class="font-display font-bold text-base text-white pb-3 border-b border-slate-800">Order Items</h3>

            <div class="divide-y divide-slate-800">
                @foreach($order->items as $item)
                    <div class="py-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-slate-950 overflow-hidden shrink-0 border border-slate-800">
                                <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">{{ $item->product_name }}</h4>
                                <p class="text-xs text-slate-400">
                                    SKU: <span class="font-mono text-slate-300">{{ $item->sku }}</span>
                                    @if($item->variant_name)
                                        • Spec: {{ $item->variant_name }}
                                    @endif
                                </p>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">${{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }}</p>
                            </div>
                        </div>
                        <span class="font-display font-bold text-white font-mono">${{ number_format($item->subtotal, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Financial Summary -->
            <div class="pt-4 border-t border-slate-800 space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal</span>
                    <span class="font-mono text-white">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-lime-400">
                        <span>Discount</span>
                        <span class="font-mono">-${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-400">
                    <span>Delivery Fee</span>
                    <span class="font-mono text-white">${{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                <div class="pt-2 border-t border-slate-800 flex justify-between text-base font-bold text-white">
                    <span>Grand Total</span>
                    <span class="font-display font-black text-lime-400 font-mono">${{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Customer & Payment Metadata (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Customer Card -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 space-y-3 text-xs">
                <h4 class="font-display font-bold text-sm text-white uppercase tracking-wider">Customer Contact</h4>
                <div class="space-y-1 text-slate-300">
                    <p class="font-bold text-white text-sm">{{ $order->customer_name }}</p>
                    <p class="text-slate-400">Phone: {{ $order->customer_phone }}</p>
                    @if($order->customer_email)
                        <p class="text-slate-400">Email: {{ $order->customer_email }}</p>
                    @endif
                    <p class="mt-2 text-slate-300">Address: {{ $order->delivery_address }}</p>
                    <p class="text-slate-400">Province: {{ $order->province_city }} ({{ ucfirst($order->delivery_method) }})</p>
                    @if($order->customer_note)
                        <p class="mt-2 p-2 rounded-xl bg-slate-950 border border-slate-800 text-lime-400">
                            <strong>Note:</strong> {{ $order->customer_note }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Payment Hash / Proof Screenshot -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 space-y-3 text-xs">
                <h4 class="font-display font-bold text-sm text-white uppercase tracking-wider">Payment Verification</h4>
                <div class="space-y-2 text-slate-300">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Method:</span>
                        <span class="font-bold uppercase text-white">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                    </div>

                    @if($order->bakong_hash)
                        <div>
                            <span class="text-slate-400">Bakong Transaction Hash:</span>
                            <p class="font-mono text-[10px] text-emerald-400 break-all select-all bg-slate-950 p-2 rounded-lg border border-slate-800 mt-1">
                                {{ $order->bakong_hash }}
                            </p>
                        </div>
                    @endif

                    @if($order->payment_proof_image)
                        <div class="pt-2 border-t border-slate-800">
                            <span class="text-slate-400 font-bold block mb-2">Customer Uploaded Slip:</span>
                            <a href="{{ asset('storage/' . $order->payment_proof_image) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-800 bg-slate-950">
                                <img src="{{ asset('storage/' . $order->payment_proof_image) }}" alt="Slip" class="w-full h-48 object-cover">
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
