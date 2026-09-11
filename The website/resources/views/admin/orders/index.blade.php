@extends('layouts.admin')

@section('title', 'Manage Orders - TosLengSey Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-3xl text-white">Orders & Shipments</h1>
            <p class="text-xs text-slate-400 mt-1">Review web purchases, cashier POS receipts, and fulfillment status</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-4">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Order #, Name, Phone..." class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
            </div>

            <div>
                <select name="order_status" onchange="this.form.submit()" class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
                    <option value="">All Order Statuses</option>
                    <option value="pending" {{ request('order_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('order_status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ request('order_status') == 'processing' ? 'selected' : '' }}>Processing / Stringing</option>
                    <option value="shipped" {{ request('order_status') == 'shipped' ? 'selected' : '' }}>Shipped / Out for Delivery</option>
                    <option value="completed" {{ request('order_status') == 'completed' ? 'selected' : '' }}>Completed / Delivered</option>
                    <option value="cancelled" {{ request('order_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <select name="payment_status" onchange="this.form.submit()" class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
                    <option value="">All Payment Statuses</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending / Awaiting</option>
                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div>
                <select name="source" onchange="this.form.submit()" class="w-full bg-slate-950 text-white text-xs border border-slate-800 rounded-xl px-3 py-2.5 focus:outline-none focus:border-lime-400">
                    <option value="">All Sources (Web & POS)</option>
                    <option value="web" {{ request('source') == 'web' ? 'selected' : '' }}>Online Web Store</option>
                    <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>Cashier In-Store POS</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-800 bg-slate-950/40">
                        <th class="p-4">Order #</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Source</th>
                        <th class="p-4">Payment</th>
                        <th class="p-4">Order Status</th>
                        <th class="p-4 text-right">Total</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="p-4 font-bold font-mono text-white">
                                #{{ $order->order_number }}
                            </td>
                            <td class="p-4 text-slate-400">
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="p-4">
                                <p class="font-bold text-white">{{ $order->customer_name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $order->customer_phone }}</p>
                            </td>
                            <td class="p-4">
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $order->source === 'pos' ? 'bg-purple-500/20 text-purple-300' : 'bg-sky-500/20 text-sky-300' }}">
                                    {{ strtoupper($order->source) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $order->payment_badge }}">
                                    {{ strtoupper($order->payment_status) }}
                                </span>
                                <span class="block text-[10px] text-slate-500 uppercase mt-0.5">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $order->status_badge }}">
                                    {{ strtoupper($order->order_status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-white">
                                ${{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold transition">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">No matching orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
