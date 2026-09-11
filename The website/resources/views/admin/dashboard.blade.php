@extends('layouts.admin')

@section('title', 'Admin Dashboard - TosLengSey Badminton Store')

@section('content')
<div class="space-y-8">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-3xl text-white">Business Dashboard</h1>
            <p class="text-xs text-slate-400 mt-1">Real-time revenue, Bakong payments, and inventory status</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('pos.index') }}" class="px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                <span>Open Cashier POS</span>
            </a>
            <a href="{{ route('admin.products.create') }}" class="px-5 py-3 rounded-2xl bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-xs shadow-lg shadow-lime-500/20 flex items-center gap-2 transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add New Product</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Revenue</span>
                <p class="font-display font-black text-2xl text-lime-400 font-mono">${{ number_format($totalRevenue, 2) }}</p>
                <span class="text-[11px] text-slate-500 font-mono">Today: ${{ number_format($todayRevenue, 2) }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-lime-400/10 text-lime-400 flex items-center justify-center">
                <i data-lucide="dollar-sign" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Orders Count Card -->
        <div class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Orders</span>
                <p class="font-display font-black text-2xl text-white font-mono">{{ $totalOrders }}</p>
                <span class="text-[11px] text-emerald-400 font-semibold">{{ $todayOrders }} placed today</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i data-lucide="shopping-bag" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Pending Fulfillment Card -->
        <div class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Orders</span>
                <p class="font-display font-black text-2xl text-amber-400 font-mono">{{ $pendingOrders }}</p>
                <span class="text-[11px] text-slate-500">Requires processing</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Low Stock Alert Card -->
        <div class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Low Stock Alerts</span>
                <p class="font-display font-black text-2xl {{ $lowStockProducts > 0 ? 'text-rose-400' : 'text-slate-400' }} font-mono">{{ $lowStockProducts }}</p>
                <span class="text-[11px] text-slate-500">{{ $totalProducts }} total active products</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Middle Split: Recent Orders & Low Stock Warning -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Recent Orders Table (8 cols) -->
        <div class="lg:col-span-8 bg-slate-900/80 border border-slate-800 rounded-3xl p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="font-display font-bold text-lg text-white">Recent Store & POS Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-lime-400 font-bold hover:underline">View All &rarr;</a>
            </div>

            @if($recentOrders->isEmpty())
                <p class="text-xs text-slate-500 py-8 text-center">No orders recorded yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-800">
                                <th class="pb-3">Order #</th>
                                <th class="pb-3">Customer</th>
                                <th class="pb-3">Source</th>
                                <th class="pb-3">Payment</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Amount</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($recentOrders as $ord)
                                <tr>
                                    <td class="py-3.5 font-bold font-mono text-white">
                                        #{{ $ord->order_number }}
                                    </td>
                                    <td class="py-3.5 text-slate-300">
                                        {{ $ord->customer_name }}
                                    </td>
                                    <td class="py-3.5">
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $ord->source === 'pos' ? 'bg-purple-500/20 text-purple-300' : 'bg-sky-500/20 text-sky-300' }}">
                                            {{ strtoupper($ord->source) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $ord->payment_badge }}">
                                            {{ strtoupper($ord->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $ord->status_badge }}">
                                            {{ strtoupper($ord->order_status) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-right font-mono font-bold text-white">
                                        ${{ number_format($ord->total_amount, 2) }}
                                    </td>
                                    <td class="py-3.5 text-right">
                                        <a href="{{ route('admin.orders.show', $ord->id) }}" class="text-lime-400 font-bold hover:underline">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Low Stock Items & Top Selling (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Low Stock Panel -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="font-display font-bold text-sm text-white flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400"></i> Low Inventory Attention
                    </h3>
                </div>

                @if($lowStockItems->isEmpty())
                    <p class="text-xs text-slate-500 text-center py-4">All equipment stock is healthy!</p>
                @else
                    <div class="space-y-3">
                        @foreach($lowStockItems as $lsi)
                            <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-3 text-xs">
                                <div>
                                    <p class="font-bold text-white line-clamp-1">{{ $lsi->name }}</p>
                                    <p class="text-[10px] text-slate-500 font-mono">SKU: {{ $lsi->sku }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 font-black font-mono">
                                        {{ $lsi->stock_quantity }} left
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Top Products -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 space-y-4">
                <h3 class="font-display font-bold text-sm text-white flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-4 h-4 text-lime-400"></i> Top Selling Gear
                </h3>
                <div class="space-y-2.5">
                    @forelse($topProducts as $tp)
                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-800/60 last:border-0">
                            <span class="text-slate-300 font-medium truncate max-w-[180px]">{{ $tp->product_name }}</span>
                            <span class="font-mono font-bold text-lime-400">{{ $tp->total_sold }} sold</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-4">No sales data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
