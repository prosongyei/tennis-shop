<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Invoice #{{ $order->order_number }} - TosLengSey Badminton Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen p-4 sm:p-10 antialiased font-sans">
    <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-xl border border-slate-200">
        <!-- Print Controls -->
        <div class="no-print flex items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <a href="{{ route('orders.show', $order->order_number) }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">
                &larr; Back to Order
            </a>
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow-md transition">
                Print Invoice (PDF)
            </button>
        </div>

        <!-- Invoice Header -->
        <div class="flex items-start justify-between gap-6 pb-8 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <img src="{{ route('brand.logo') }}?v=2" alt="TosLengSey" class="w-10 h-10 rounded-full object-contain border border-slate-300">
                    <h1 class="text-2xl font-black tracking-tight text-slate-950">TOSLENGSEY TENNIS STORE</h1>
                </div>
                <p class="text-xs text-slate-600">#128 St. 2004, Sen Sok, Phnom Penh, Cambodia</p>
                <p class="text-xs text-slate-600">Phone: +855 12 888 999 • contact@toslengsey.com</p>
            </div>

            <div class="text-right">
                <span class="inline-block px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider {{ $order->is_paid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $order->is_paid ? 'PAID INVOICE' : 'UNPAID INVOICE' }}
                </span>
                <p class="text-lg font-mono font-black text-slate-950 mt-2">#{{ $order->order_number }}</p>
                <p class="text-xs text-slate-500 font-medium">Date: {{ $order->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <!-- Bill To Details -->
        <div class="grid grid-cols-2 gap-8 py-6 border-b border-slate-200 text-xs">
            <div>
                <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Customer / Deliver To:</span>
                <p class="font-bold text-slate-950 text-sm">{{ $order->customer_name }}</p>
                <p class="text-slate-600">{{ $order->customer_phone }}</p>
                <p class="text-slate-600 mt-1">{{ $order->delivery_address }}, {{ $order->province_city }}</p>
            </div>

            <div class="text-right">
                <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Payment Information:</span>
                <p class="font-bold text-slate-950 uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                <p class="text-slate-600">Status: {{ ucfirst($order->payment_status) }}</p>
                @if($order->bakong_hash)
                    <p class="text-[10px] text-slate-500 font-mono mt-1 break-all">Ref: {{ $order->bakong_hash }}</p>
                @endif
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="w-full text-xs text-left my-6">
            <thead>
                <tr class="border-b-2 border-slate-300 text-slate-500 uppercase tracking-wider">
                    <th class="py-2.5">Item Description</th>
                    <th class="py-2.5 text-center">Qty</th>
                    <th class="py-2.5 text-right">Unit Price</th>
                    <th class="py-2.5 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-3">
                            <span class="font-bold text-slate-950 text-sm block">{{ $item->product_name }}</span>
                            @if($item->variant_name)
                                <span class="text-slate-500 text-[11px]">Spec: {{ $item->variant_name }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-center font-bold">{{ $item->quantity }}</td>
                        <td class="py-3 text-right font-mono">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 text-right font-mono font-bold">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="border-t-2 border-slate-900 pt-4 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal</span>
                <span class="font-mono font-semibold">${{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-700">
                    <span>Discount</span>
                    <span class="font-mono font-bold">-${{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-slate-600">
                <span>Delivery Fee</span>
                <span class="font-mono font-semibold">${{ number_format($order->delivery_fee, 2) }}</span>
            </div>
            <div class="flex justify-between text-base font-black text-slate-950 pt-2 border-t border-slate-200">
                <span>Grand Total (USD)</span>
                <span class="font-mono">${{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-500">
                <span>KHR Equivalent (Rate 4,100)</span>
                <span class="font-mono font-semibold">≈ {{ number_format($order->total_amount * 4100) }} KHR</span>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="pt-8 mt-8 border-t border-slate-200 text-center text-[11px] text-slate-500 space-y-1">
            <p>Thank you for shopping at TosLengSey Tennis Store Cambodia!</p>
            <p>For inquiries or warranty support, contact us on Telegram: @TosLengSeyKh</p>
        </div>
    </div>
</body>
</html>
