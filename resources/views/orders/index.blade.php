@extends('layouts.app')

@section('title', 'My Order History - TosLengSey Badminton Store')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display font-black text-3xl text-white">My Purchase History</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Review all your past badminton equipment orders, live delivery tracking, and digital invoices.</p>
        </div>
        <a href="{{ route('orders.track') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-sky-400 font-bold text-xs transition">
            <i data-lucide="truck" class="w-4 h-4"></i> Live Tracking Hub
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-16 text-center max-w-lg mx-auto shadow-2xl">
            <div class="w-16 h-16 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="package" class="w-8 h-8"></i>
            </div>
            <h3 class="font-display font-bold text-xl text-white">No orders placed yet</h3>
            <p class="text-xs text-slate-400 mt-1 mb-6">
                You haven't placed any orders yet. Ready to pick your new tournament racquet or court shoes?
            </p>
            <a href="{{ route('shop.catalog') }}" class="px-6 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-lg shadow-sky-600/25 transition inline-block">
                Browse Racquets & Shoes
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-slate-900/70 border border-slate-800 rounded-3xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-slate-700 transition shadow-lg">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="font-mono font-bold text-base text-white">#{{ $order->order_number }}</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full border font-semibold {{ $order->status_badge }}">
                                {{ ucfirst($order->order_status) }}
                            </span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full border font-semibold {{ $order->payment_badge }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-400">
                            Placed on {{ $order->created_at->format('M d, Y - h:i A') }} • {{ $order->items->count() }} item(s)
                        </p>

                        <div class="text-xs text-slate-300">
                            {{ $order->items->pluck('product_name')->take(2)->join(', ') }}
                            @if($order->items->count() > 2)
                                <span class="text-slate-500">+{{ $order->items->count() - 2 }} more</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between md:justify-end gap-6 border-t md:border-t-0 pt-4 md:pt-0 border-slate-800">
                        <div class="text-left md:text-right">
                            <span class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">Total</span>
                            <p class="font-display font-black text-xl text-sky-400 font-mono">
                                ${{ number_format($order->total_amount, 2) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(!$order->is_paid && $order->payment_method === 'khqr')
                                <a href="{{ route('payment.khqr', $order->order_number) }}" class="px-3.5 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-red-600/20">
                                    <i data-lucide="qr-code" class="w-4 h-4"></i> Pay
                                </a>
                            @endif

                            <a href="{{ route('orders.track', ['order_number' => $order->order_number]) }}" class="px-3.5 py-2 rounded-xl bg-sky-600/20 hover:bg-sky-600 text-sky-400 hover:text-white text-xs font-bold transition flex items-center gap-1">
                                <i data-lucide="truck" class="w-3.5 h-3.5"></i> Track
                            </a>

                            <a href="{{ route('orders.show', $order->order_number) }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition">
                                Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
